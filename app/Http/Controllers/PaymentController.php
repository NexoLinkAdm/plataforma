<?php

namespace App\Http\Controllers;

// Importações Completas
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
// Note que não precisamos mais de Order ou TransactionApproved aqui, pois o webhook cuidará disso.

class PaymentController extends Controller
{
    public function processPayment(Request $request): JsonResponse
    {
        Log::info('Dados recebidos (Fluxo Direto)', ['payload' => $request->all()]);

        // Validação flexível que já funciona
        $paymentMethod = $request->input('formData.payment_method_id');
        $baseRules = [ 'formData.payment_method_id' => 'required|string', 'formData.transaction_amount' => 'required|numeric|min:0.01', 'formData.payer.email' => 'required|email', 'service_id' => 'required|integer|exists:services,id', ];
        $cardRules = [ 'formData.token' => 'required|string|min:32', 'formData.issuer_id' => 'required|string', 'formData.installments' => 'required|integer|min:1', 'formData.payer.identification.type' => 'required|string', 'formData.payer.identification.number' => 'required|string', ];
        $rules = !in_array($paymentMethod, ['pix', 'bolbradesco']) ? array_merge($baseRules, $cardRules) : $baseRules;
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dados de pagamento inválidos.', 'errors' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();
        $formData = $validatedData['formData'];
        $serviceId = $validatedData['service_id'];

        try {
            $service = Service::findOrFail($serviceId);

            if (empty($service->user->mp_access_token)) {
                return response()->json(['message' => 'O vendedor não está habilitado para pagamentos.'], 409);
            }
            
            // Usa o Access Token da CRIADORA - arquitetura correta
            MercadoPagoConfig::setAccessToken($service->user->mp_access_token);
            $client = new PaymentClient();

            $transactionAmount = round((float)$formData['transaction_amount'], 2);

            $paymentRequest = [
                "transaction_amount" => $transactionAmount,
                "description" => $service->title,
                "payer" => [ "email" => $formData['payer']['email'] ],
                "external_reference" => "service_{$service->id}_" . time(),
            ];

            // REMOVEMOS a application_fee. O dinheiro vai 100% para a criadora.
            
            if (isset($formData['token'])) $paymentRequest['token'] = trim($formData['token']);
            if (isset($formData['installments'])) $paymentRequest['installments'] = (int)$formData['installments'];
            if (isset($formData['issuer_id'])) $paymentRequest['issuer_id'] = (int)$formData['issuer_id'];
            if (isset($formData['payer']['identification'])) { $paymentRequest['payer']['identification'] = [ "type" => $formData['payer']['identification']['type'], "number" => preg_replace('/\D/', '', $formData['payer']['identification']['number']) ]; }
            if (isset($paymentMethod)) $paymentRequest['payment_method_id'] = $paymentMethod;

            Log::info('Requisição de pagamento (Direto para Criadora):', $paymentRequest);
            $payment = $client->create($paymentRequest);
            Log::info('Pagamento criado na API:', ['id' => $payment->id, 'status' => $payment->status]);
            
            // Lógica Pós-Criação do Pagamento
            User::firstOrCreate(['email' => $formData['payer']['email']], ['name' => 'Cliente', 'password' => Hash::make(Str::random(12)), 'role' => 'client']);
            
            // Apenas CRIA um registro inicial. O webhook será o responsável final por atualizar.
            Transaction::updateOrCreate(
                ['mp_payment_id' => $payment->id],
                [
                    'service_id' => $service->id,
                    'user_id' => $service->user_id,
                    'status' => $payment->status,
                    'buyer_email' => $formData['payer']['email'],
                    'amount_cents' => round((float)$payment->transaction_amount * 100),
                    'metadata' => (array)$payment,
                ]
            );

            // Responde para o frontend para ele renderizar o status (Pix/Boleto) ou redirecionar.
            return response()->json([
                'status' => $payment->status,
                'payment_id' => $payment->id
            ]);

        } catch (MPApiException $e) {
            $errorContent = is_string($c = $e->getApiResponse()->getContent()) ? json_decode($c, true) : (array) $c;
            Log::error('MP API Error:', ['error' => $errorContent]);
            $errorMessage = $errorContent['message'] ?? 'Pagamento recusado.';
            if (isset($errorContent['cause'][0]['description'])) {
                 $errorMessage = $errorContent['cause'][0]['description'];
            }
            return response()->json(['error' => true, 'message' => $errorMessage], 400); // <-- Retorno JSON
        } catch (\Exception $e) {
            Log::error('Erro geral no pagamento: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => true, 'message' => 'Ocorreu um erro interno.'], 500); // <-- Retorno JSON
        }
    }
}
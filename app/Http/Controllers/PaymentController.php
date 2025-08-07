<?php

namespace App\Http\Controllers;

use App\Events\TransactionApproved;
use App\Models\Order;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;


//versão de teste para faze boloteos e pix funcionar a primeria tentativa
class PaymentController extends Controller
{
    public function processPayment(Request $request): JsonResponse
    {
        Log::info('Dados recebidos no processPayment', ['payload' => $request->all()]);
        
        // --- MUDANÇA CIRÚRGICA 1: VALIDAÇÃO FLEXÍVEL ---
        $paymentMethod = $request->input('formData.payment_method_id');
        $baseRules = [
            'formData.payment_method_id' => 'required|string',
            'formData.transaction_amount' => 'required|numeric|min:0.01',
            'formData.payer.email' => 'required|email',
            'service_id' => 'required|integer|exists:services,id',
        ];
        $cardRules = [
            'formData.token' => 'required|string|min:32',
            'formData.issuer_id' => 'required|string',
            'formData.installments' => 'required|integer|min:1',
            'formData.payer.identification.type' => 'required|string',
            'formData.payer.identification.number' => 'required|string',
        ];
        $rules = !in_array($paymentMethod, ['pix', 'bolbradesco']) ? array_merge($baseRules, $cardRules) : $baseRules;

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => 'Dados de pagamento inválidos.', 'errors' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();
        $formData = $validatedData['formData'];
        $serviceId = $validatedData['service_id'];
        // --- FIM DA MUDANÇA CIRÚRGICA 1 ---

        try {
            $service = Service::findOrFail($serviceId);

            // Usa o Access Token da PLATAFORMA (base funcional)
            MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));
            $client = new PaymentClient();
            
            $transactionAmount = round((float)$formData['transaction_amount'], 2);
            $applicationFee = round(config('mercadopago.application_fee_cents', 0) / 100, 2);

            $paymentRequest = [
                "transaction_amount" => $transactionAmount,
                "description" => $service->title,
                "application_fee" => $applicationFee,
                "payer" => [ "email" => $formData['payer']['email'] ],
                "external_reference" => "service_{$service->id}_" . time(),
                // Usando 'metadata' para o collector_id para evitar erro de parâmetro inválido
                "metadata" => [
                    "collector_id" => (string)$service->user->mp_user_id
                ]
            ];

            if (isset($formData['token'])) $paymentRequest['token'] = trim($formData['token']);
            if (isset($formData['installments'])) $paymentRequest['installments'] = (int)$formData['installments'];
            if (isset($formData['issuer_id'])) $paymentRequest['issuer_id'] = (int)$formData['issuer_id'];
            if (isset($formData['payer']['identification'])) $paymentRequest['payer']['identification'] = [ "type" => $formData['payer']['identification']['type'], "number" => preg_replace('/\D/', '', $formData['payer']['identification']['number']) ];
            if (isset($paymentMethod)) $paymentRequest['payment_method_id'] = $paymentMethod;
            
            Log::info('Requisição de pagamento (Plataforma como pagador):', $paymentRequest);
            $payment = $client->create($paymentRequest);
            Log::info('Pagamento criado na API:', ['id' => $payment->id, 'status' => $payment->status]);

            // --- MUDANÇA CIRÚRGICA 2: LÓGICA DE PÓS-PAGAMENTO ---
            $clientUser = User::firstOrCreate(['email' => $formData['payer']['email']], ['name' => 'Cliente', 'password' => Hash::make(Str::random(12)), 'role' => 'client']);
            
            $transaction = Transaction::updateOrCreate(
                ['mp_payment_id' => $payment->id],
                [
                    'service_id' => $service->id,
                    'user_id' => $service->user_id,
                    'status' => $payment->status,
                    'buyer_email' => $formData['payer']['email'],
                    'amount_cents' => round((float)$payment->transaction_amount * 100),
                    'fee_cents' => round($applicationFee * 100),
                    'metadata' => (array)$payment,
                ]
            );

            // Para Pix/Boleto, retorna os dados para a Status Screen
            if (in_array($payment->status, ['pending', 'in_process'])) {
                return response()->json(['status' => $payment->status, 'payment_id' => $payment->id]);
            }
            // Para pagamentos aprovados, cria o pedido e dispara o e-mail
            if ($transaction->status === 'approved') {
                Order::updateOrCreate(['transaction_id' => $transaction->id], ['client_id' => $clientUser->id, 'creator_id' => $service->user_id]);
                if(!$transaction->confirmation_email_sent){
                    $transaction->update(['confirmation_email_sent' => true]);
                    event(new TransactionApproved($transaction));
                }
            }
            // --- FIM DA MUDANÇA CIRÚRGICA 2 ---

            return response()->json(['status' => $payment->status, 'payment_id' => $payment->id]);

        } catch (MPApiException $e) {
            $errorContent = is_string($c = $e->getApiResponse()->getContent()) ? json_decode($c, true) : (array) $c;
            Log::error('MP API Error:', ['error' => $errorContent]);
            return response()->json(['message' => $errorContent['message'] ?? 'Pagamento recusado.'], 400);
        } catch (\Exception $e) {
            Log::error('Erro geral no pagamento: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Ocorreu um erro interno.'], 500);
        }
    }
}
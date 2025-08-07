<?php

namespace App\Http\Controllers;

use App\Events\TransactionApproved;
use App\Models\Order;
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

class PaymentController extends Controller
{
    public function processPayment(Request $request): JsonResponse
    {
        Log::info('Dados recebidos no processPayment', ['payload' => $request->all()]);

        // Validação Flexível (já está correta e funcional)
        $paymentMethod = $request->input('formData.payment_method_id');
        $baseRules = [ 'formData.payment_method_id' => 'required|string', 'formData.transaction_amount' => 'required|numeric|min:0.01', 'formData.payer.email' => 'required|email', 'service_id' => 'required|integer|exists:services,id', ];
        $cardRules = [ 'formData.token' => 'required|string|min:32', 'formData.issuer_id' => 'required|string', 'formData.installments' => 'required|integer|min:1', 'formData.payer.identification.type' => 'required|string', 'formData.payer.identification.number' => 'required|string', ];
        $rules = !in_array($paymentMethod, ['pix', 'bolbradesco']) ? array_merge($baseRules, $cardRules) : $baseRules;
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dados de pagamento inválidos.'], 422);
        }
        $validatedData = $validator->validated();
        $formData = $validatedData['formData'];
        $serviceId = $validatedData['service_id'];

        try {
            $service = Service::findOrFail($serviceId);
            
            // 1. USA O ACCESS TOKEN DA PLATAFORMA - A ARQUITETURA MAIS ESTÁVEL
            MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));
            $client = new PaymentClient();

            $transactionAmount = round((float)$formData['transaction_amount'], 2);
            
            // 2. REQUISIÇÃO SIMPLIFICADA: É uma venda direta da plataforma.
            $paymentRequest = [
                "transaction_amount" => $transactionAmount,
                "description" => $service->title . " (ID Serviço: " . $service->id . ")",
                "payer" => [ "email" => $formData['payer']['email'] ],
                "external_reference" => "service_{$service->id}_" . time(),
            ];
            
            // Removemos 'application_fee' e 'metadata' da requisição principal.
            
            if (isset($formData['token'])) $paymentRequest['token'] = trim($formData['token']);
            if (isset($formData['installments'])) $paymentRequest['installments'] = (int)$formData['installments'];
            if (isset($formData['issuer_id'])) $paymentRequest['issuer_id'] = (int)$formData['issuer_id'];
            if (isset($formData['payer']['identification'])) { $paymentRequest['payer']['identification'] = [ "type" => $formData['payer']['identification']['type'], "number" => preg_replace('/\D/', '', $formData['payer']['identification']['number']) ]; }
            if (isset($paymentMethod)) $paymentRequest['payment_method_id'] = $paymentMethod;

            Log::info('Requisição de pagamento (Plataforma como Vendedora):', $paymentRequest);
            $payment = $client->create($paymentRequest);
            Log::info('Pagamento criado na API:', ['id' => $payment->id, 'status' => $payment->status]);
            
            // 3. REGISTRA A LÓGICA DO SPLIT NO NOSSO BANCO DE DADOS
            $platformFeeCents = config('mercadopago.application_fee_cents', 0);
            $clientUser = User::firstOrCreate(['email' => $formData['payer']['email']], ['name' => 'Cliente', 'password' => Hash::make(Str::random(12)), 'role' => 'client']);
            
            $transaction = Transaction::updateOrCreate(
                ['mp_payment_id' => $payment->id],
                [
                    'service_id' => $service->id,
                    'user_id' => $service->user_id, // A criadora que prestou o serviço
                    'status' => $payment->status,
                    'buyer_email' => $formData['payer']['email'],
                    'amount_cents' => round((float)$payment->transaction_amount * 100),
                    'fee_cents' => $platformFeeCents, // <-- Salvamos a nossa comissão aqui para o futuro
                    'metadata' => (array)$payment,
                ]
            );

            // ... (Resto da lógica de Pedido e E-mail, que já está correta)

            if (in_array($payment->status, ['pending', 'in_process'])) {
                return response()->json([ 'status' => $payment->status, 'payment_id' => $payment->id ]);
            }
            if ($transaction->status === 'approved') {
                Order::updateOrCreate(['transaction_id' => $transaction->id], ['client_id' => $clientUser->id, 'creator_id' => $service->user_id]);
                if(!$transaction->confirmation_email_sent){ $transaction->update(['confirmation_email_sent' => true]); event(new TransactionApproved($transaction)); }
                Log::info("Evento de e-mail disparado para Tx ID: {$transaction->id}");
            }

            return response()->json(['status' => $payment->status, 'payment_id' => $payment->id]);

        } catch (MPApiException $e) { /*...*/ } catch (\Exception $e) { /*...*/ }
    }
}
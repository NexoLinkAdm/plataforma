<?php

namespace App\Http\Controllers;

// Adicione os Models que serão usados
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
        Log::info('Dados recebidos (Fluxo Direto)', ['payload' => $request->all()]);

        // Validação flexível (já está correta)
        $paymentMethod = $request->input('formData.payment_method_id');
        $baseRules = [ 'formData.payment_method_id' => 'required|string', /* ... outros campos base ... */ ];
        $cardRules = [ 'formData.token' => 'required|string|min:32', /* ... outros campos de cartão ... */ ];
        // ... (resto da lógica de validação que já temos)

        try {
            $service = Service::findOrFail($serviceId);

            if (empty($service->user->mp_access_token)) {
                return response()->json(['message' => 'O vendedor não está habilitado.'], 409);
            }
            
            // LÓGICA CORRETA: Usa o Access Token da CRIADORA.
            MercadoPagoConfig::setAccessToken($service->user->mp_access_token);
            $client = new PaymentClient();

            $transactionAmount = round((float)$formData['transaction_amount'], 2);

            $paymentRequest = [
                "transaction_amount" => $transactionAmount,
                "description" => $service->title,
                "payer" => [ "email" => $formData['payer']['email'] ],
                "external_reference" => "service_{$service->id}_" . time(),
                // NÃO HÁ application_fee
            ];

            // Adiciona campos dinâmicos (cartão, etc)
            // ... (a lógica que já temos para adicionar token, installments, etc)
            
            Log::info('Requisição de pagamento (Direto para a Criadora):', $paymentRequest);
            $payment = $client->create($paymentRequest);
            Log::info('Pagamento criado na API:', ['id' => $payment->id, 'status' => $payment->status]);

            // --- LÓGICA DE REGISTRO E PÓS-PAGAMENTO ---
            
            // 1. Encontra ou cria a conta do cliente
            $clientUser = User::firstOrCreate(['email' => $formData['payer']['email']], ['name' => 'Cliente', 'password' => Hash::make(Str::random(12)), 'role' => 'client']);
            
            // 2. Salva a transação para o histórico da criadora
            $transaction = Transaction::updateOrCreate(
                ['mp_payment_id' => $payment->id],
                [
                    'service_id' => $service->id,
                    'user_id' => $service->user_id, // A criadora que vendeu
                    'status' => $payment->status,
                    'buyer_email' => $formData['payer']['email'],
                    'amount_cents' => round((float)$payment->transaction_amount * 100),
                    // 'fee_cents' não existe mais
                    'metadata' => (array)$payment,
                ]
            );

            // Responde para o frontend
            if (in_array($payment->status, ['pending', 'in_process'])) {
                return response()->json([ 'status' => $payment->status, 'payment_id' => $payment->id ]);
            }
            if ($transaction->status === 'approved') {
                // ... (lógica de criar Pedido (Order) e disparar E-mail) ...
            }
            return response()->json(['status' => $payment->status, 'payment_id' => $payment->id]);

        } catch (MPApiException $e) { /* ... */ } 
          catch (\Exception $e) { /* ... */ }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

class PaymentController extends Controller
{
    /**
     * Processa o pagamento recebido do Checkout Brick.
     */
    public function processPayment(Request $request)
    {
        // Valida os dados essenciais recebidos do frontend
        $validated = $request->validate([
            'formData.token' => 'required|string',
            'formData.issuer_id' => 'required|string',
            'formData.payment_method_id' => 'required|string',
            'formData.transaction_amount' => 'required|numeric',
            'formData.installments' => 'required|integer',
            'formData.payer.email' => 'required|email',
            'service_id' => 'required|integer|exists:services,id',
        ]);

        try {
            $service = Service::findOrFail($validated['service_id']);
            $formData = $validated['formData'];

            // Define o token de acesso da CRIADORA para esta operação
            MercadoPagoConfig::setAccessToken($service->user->mp_access_token);

            // Cria o cliente de pagamento
            $client = new PaymentClient();

            // Cria a requisição de pagamento
            $paymentRequest = [
                "transaction_amount" => $formData['transaction_amount'],
                "token" => $formData['token'],
                "description" => $service->title,
                "installments" => $formData['installments'],
                "payment_method_id" => $formData['payment_method_id'],
                "issuer_id" => (int)$formData['issuer_id'],
                "payer" => [
                    "email" => $formData['payer']['email']
                ],
                // A MÁGICA DO SPLIT ACONTECE AQUI
                "application_fee" => config('mercadopago.application_fee_cents') / 100,
            ];

            // Cria o pagamento
            $payment = $client->create($paymentRequest);

            // TODO: Salvar a transação no banco de dados. Faremos isso no Módulo 5.

            return response()->json([
                'status' => $payment->status,
                'payment_id' => $payment->id,
            ]);

        } catch (MPApiException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Pagamento recusado pelo Mercado Pago.',
                'details' => json_decode($e->getApiResponse()->getContent())
            ], 400);
        } catch (\Exception $e) {
            \Log::error('Erro no processamento do pagamento: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Ocorreu um erro interno. Tente novamente.'
            ], 500);
        }
    }
}
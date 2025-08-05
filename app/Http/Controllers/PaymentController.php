<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        \Log::info('Dados recebidos no processPayment:', $request->all());

        $validated = $request->validate([
            'formData.token' => 'nullable|string',
            'formData.payment_method_id' => 'required|string',
            'formData.transaction_amount' => 'required|numeric',
            'formData.payer.email' => 'required|email',
            'service_id' => 'required|integer|exists:services,id',
            'formData.issuer_id' => 'nullable|string',
            'formData.installments' => 'nullable|integer',
        ]);

        try {
            $service = Service::findOrFail($validated['service_id']);
            $formData = $validated['formData'];

            MercadoPagoConfig::setAccessToken($service->user->mp_access_token);
            $client = new PaymentClient();

            // --- CORREÇÃO DE PRECISÃO E FORMATAÇÃO ---
            // A API é sensível a problemas com floats.
            // Arredondamos para 2 casas decimais para garantir consistência.
            $transactionAmount = round($formData['transaction_amount'], 2);
            $applicationFee = round(config('mercadopago.application_fee_cents') / 100, 2);
            // --- FIM DA CORREÇÃO ---

            $paymentRequest = [
                "transaction_amount" => $transactionAmount,
                "description" => $service->title,
                "payment_method_id" => $formData['payment_method_id'],
                "payer" => ["email" => $formData['payer']['email']],
                "application_fee" => $applicationFee,
            ];

            if (!empty($formData['token'])) $paymentRequest['token'] = $formData['token'];
            if (!empty($formData['installments'])) $paymentRequest['installments'] = (int)$formData['installments'];
            if (!empty($formData['issuer_id'])) $paymentRequest['issuer_id'] = (int)$formData['issuer_id'];

            \Log::info('Requisição de pagamento (Formatada):', $paymentRequest);
            $payment = $client->create($paymentRequest);
            \Log::info('Pagamento criado:', ['id' => $payment->id, 'status' => $payment->status]);

            return response()->json([
                'status' => $payment->status,
                'payment_id' => $payment->id,
            ]);

        } catch (MPApiException $e) {
            $errorDetails = $e->getApiResponse()->getContent();
            \Log::error('MP API Error:', ['error' => $errorDetails, 'request' => $request->all()]);

            $errorMessage = $errorDetails['message'] ?? 'Pagamento recusado.';
            if(isset($errorDetails['cause'][0]['description'])) {
                $errorMessage = $errorDetails['cause'][0]['description'];
            }

            return response()->json([
                'error' => true,
                'message' => $errorMessage,
                'details' => $errorDetails
            ], 400);
        } catch (\Exception $e) {
            \Log::error('Erro no processamento: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['error' => true, 'message' => 'Ocorreu um erro interno.'], 500);
        }
    }
}
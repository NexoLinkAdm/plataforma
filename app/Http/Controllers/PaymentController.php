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

        // Validação robusta dos dados vindos do frontend
        $validated = $request->validate([
            'formData.token' => 'required|string|min:32',
            'formData.payment_method_id' => 'required|string',
            'formData.transaction_amount' => 'required|numeric|min:0.01',
            'formData.payer.email' => 'required|email',
            'formData.payer.identification.type' => 'required|string|in:CPF,CNPJ',
            'formData.payer.identification.number' => 'required|string',
            'service_id' => 'required|integer|exists:services,id',
            'formData.issuer_id' => 'nullable|string',
            'formData.installments' => 'required|integer|min:1',
        ]);

        try {
            $service = Service::findOrFail($validated['service_id']);
            $formData = $validated['formData'];

            // Usa o Access Token da criadora para receber o pagamento
            MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));
            $client = new PaymentClient();

            // Formata os valores para evitar erros de precisão
            $transactionAmount = round((float) $formData['transaction_amount'], 2);
            $applicationFee = round(config('mercadopago.application_fee_cents', 0) / 100, 2);

            $paymentRequest = [
                "transaction_amount" => $transactionAmount,
                "token" => trim($formData['token']),
                "description" => $service->title,
                "installments" => (int) $formData['installments'],
                "payment_method_id" => $formData['payment_method_id'],
                "application_fee" => $applicationFee, // Nossa comissão
                "payer" => [
                    "email" => $formData['payer']['email'],
                    "identification" => [
                        "type" => $formData['payer']['identification']['type'],
                        "number" => preg_replace('/\D/', '', $formData['payer']['identification']['number']),
                    ]
                ],
                "external_reference" => "service_{$service->id}_" . time(),
            ];

            if (!empty($formData['issuer_id'])) {
                $paymentRequest['issuer_id'] = (int) $formData['issuer_id'];
            }

            \Log::info('Requisição de pagamento (Formatada):', $paymentRequest);
            $payment = $client->create($paymentRequest);
            \Log::info('Pagamento criado:', ['id' => $payment->id, 'status' => $payment->status]);

            return response()->json(['status' => $payment->status, 'payment_id' => $payment->id]);

        } catch (MPApiException $e) {
            $errorDetails = $e->getApiResponse()->getContent();
            \Log::error('MP API Error:', ['error' => $errorDetails, 'request' => $request->all()]);

            // Tratamento de erro específico para token inválido/expirado
            if (isset($errorDetails['cause'][0]['code']) && $errorDetails['cause'][0]['code'] === 2006) {
                return response()->json([
                    'error' => true,
                    'message' => 'O token do cartão expirou. Por favor, tente novamente.',
                    'code' => 'token_expired'
                ], 400);
            }

            $errorMessage = $errorDetails['cause'][0]['description'] ?? $errorDetails['message'] ?? 'Pagamento recusado.';
            return response()->json(['error' => true, 'message' => $errorMessage, 'details' => $errorDetails], 400);
        } catch (\Exception $e) {
            \Log::error('Erro no processamento: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['error' => true, 'message' => 'Ocorreu um erro interno.'], 500);
        }
    }
}
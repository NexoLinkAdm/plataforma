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
            'formData.token' => 'required|string|min:32',
            'formData.payment_method_id' => 'required|string',
            'formData.transaction_amount' => 'required|numeric|min:0.01',
            'formData.payer.email' => 'required|email',
            'formData.payer.identification.type' => 'required|string|in:CPF,CNPJ',
            'formData.payer.identification.number' => 'required|string|min:11',
            'service_id' => 'required|integer|exists:services,id',
            'formData.issuer_id' => 'nullable|string',
            'formData.installments' => 'required|integer|min:1|max:24',
        ]);

        try {
            $service = Service::findOrFail($validated['service_id']);
            $formData = $validated['formData'];
            MercadoPagoConfig::setAccessToken($service->user->mp_access_token);
            $client = new PaymentClient();

            $transactionAmount = round((float)$formData['transaction_amount'], 2);
            $applicationFee = round(config('mercadopago.application_fee_cents', 1000) / 100, 2);

            $paymentRequest = [
                "transaction_amount" => $transactionAmount,
                "token" => trim($formData['token']),
                "description" => $service->title ?? 'Serviço',
                "installments" => (int)$formData['installments'],
                "payment_method_id" => $formData['payment_method_id'],
                "application_fee" => $applicationFee,
                "payer" => [
                    "email" => $formData['payer']['email'],
                    "identification" => [
                        "type" => $formData['payer']['identification']['type'],
                        "number" => preg_replace('/\D/', '', $formData['payer']['identification']['number']),
                    ]
                ],
                "statement_descriptor" => substr($service->title ?? 'Servico', 0, 22),
                "external_reference" => "service_{$service->id}_" . time(),
            ];

            if (!empty($formData['issuer_id']) && in_array($formData['payment_method_id'], ['visa', 'master', 'amex', 'elo'])) {
                $paymentRequest['issuer_id'] = (int)$formData['issuer_id'];
            }

            \Log::info('Requisição de pagamento:', $paymentRequest);
            $payment = $client->create($paymentRequest);
            \Log::info('Pagamento criado:', ['id' => $payment->id, 'status' => $payment->status]);

            return response()->json(['status' => $payment->status, 'payment_id' => $payment->id]);

        } catch (MPApiException $e) {
            $errorDetails = $e->getApiResponse()->getContent();
            \Log::error('MP API Error:', ['error' => $errorDetails, 'request' => $request->all()]);

            if (isset($errorDetails['error']) && $errorDetails['error'] === 'bad_request') {
                foreach (($errorDetails['cause'] ?? []) as $cause) {
                    if ($cause['code'] === 2006) {
                        return response()->json(['error' => true, 'message' => 'O token do cartão expirou. Por favor, insira os dados do cartão novamente.', 'code' => 'token_expired', 'retry' => true], 400);
                    }
                }
            }
            $errorMessage = $errorDetails['cause'][0]['description'] ?? $errorDetails['message'] ?? 'Pagamento recusado.';
            return response()->json(['error' => true, 'message' => $errorMessage, 'details' => $errorDetails], 400);
        } catch (\Exception $e) {
            \Log::error('Erro no processamento: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['error' => true, 'message' => 'Ocorreu um erro interno.'], 500);
        }
    }
}
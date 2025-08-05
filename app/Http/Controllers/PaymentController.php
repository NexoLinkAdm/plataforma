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

        // Validação agora inclui a identificação do pagador, que é crucial
        $validated = $request->validate([
            'formData.token' => 'required|string',
            'formData.payment_method_id' => 'required|string',
            'formData.transaction_amount' => 'required|numeric',
            'formData.payer.email' => 'required|email',
            'formData.payer.identification.type' => 'required|string',
            'formData.payer.identification.number' => 'required|string',
            'service_id' => 'required|integer|exists:services,id',
            'formData.issuer_id' => 'required|string',
            'formData.installments' => 'required|integer',
        ]);

        try {
            $service = Service::findOrFail($validated['service_id']);
            $formData = $validated['formData'];

            MercadoPagoConfig::setAccessToken($service->user->mp_access_token);
            $client = new PaymentClient();

            // Arredondamento para 2 casas decimais para evitar erros de float
            $transactionAmount = round($formData['transaction_amount'], 2);
            $applicationFee = round(config('mercadopago.application_fee_cents') / 100, 2);

            // --- CORREÇÃO: SINCRONIZAÇÃO COMPLETA DO PAGADOR ---
            $paymentRequest = [
                "transaction_amount" => $transactionAmount,
                "token" => $formData['token'],
                "description" => $service->title,
                "installments" => (int)$formData['installments'],
                "payment_method_id" => $formData['payment_method_id'],
                "issuer_id" => (int)$formData['issuer_id'],
                "application_fee" => $applicationFee,
                "payer" => [
                    "email" => $formData['payer']['email'],
                    // Adiciona a identificação do pagador à requisição final
                    "identification" => [
                        "type" => $formData['payer']['identification']['type'],
                        "number" => $formData['payer']['identification']['number'],
                    ]
                ],
            ];
            // --- FIM DA CORREÇÃO ---

            \Log::info('Requisição de pagamento (Sincronizada):', $paymentRequest);
            $payment = $client->create($paymentRequest);
            \Log::info('Pagamento criado:', ['id' => $payment->id, 'status' => $payment->status]);

            return response()->json([
                'status' => $payment->status,
                'payment_id' => $payment->id,
            ]);

        } catch (MPApiException $e) {
            $errorDetails = $e->getApiResponse()->getContent();
            \Log::error('MP API Error:', ['error' => $errorDetails, 'request' => $request->all()]);
            $errorMessage = $errorDetails['cause'][0]['description'] ?? ($errorDetails['message'] ?? 'Pagamento recusado.');
            return response()->json(['error' => true, 'message' => $errorMessage, 'details' => $errorDetails], 400);
        } catch (\Exception $e) {
            \Log::error('Erro no processamento: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['error' => true, 'message' => 'Ocorreu um erro interno.'], 500);
        }
    }
}
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
            'formData.payer.identification.type' => 'nullable|string',
            'formData.payer.identification.number' => 'nullable|string',
        ]);

        try {
            $service = Service::findOrFail($validated['service_id']);
            $formData = $validated['formData'];
            MercadoPagoConfig::setAccessToken($service->user->mp_access_token);
            $client = new PaymentClient();

            $paymentRequest = [
                "transaction_amount" => $formData['transaction_amount'],
                "description" => $service->title,
                "payment_method_id" => $formData['payment_method_id'],
                "payer" => ["email" => $formData['payer']['email']],
                "application_fee" => config('mercadopago.application_fee_cents') / 100,
            ];

            if (!empty($formData['token'])) $paymentRequest['token'] = $formData['token'];
            if (!empty($formData['installments'])) $paymentRequest['installments'] = $formData['installments'];
            if (!empty($formData['issuer_id'])) $paymentRequest['issuer_id'] = (int)$formData['issuer_id'];
            if (!empty($formData['payer']['identification'])) {
                $paymentRequest['payer']['identification'] = [
                    'type' => $formData['payer']['identification']['type'],
                    'number' => $formData['payer']['identification']['number']
                ];
            }

            \Log::info('Requisição de pagamento:', $paymentRequest);
            $payment = $client->create($paymentRequest);
            \Log::info('Pagamento criado:', ['id' => $payment->id, 'status' => $payment->status]);

            return response()->json(['status' => $payment->status, 'payment_id' => $payment->id, 'status_detail' => $payment->status_detail ?? null]);
        } catch (MPApiException $e) {
            $errorDetails = json_decode($e->getApiResponse()->getContent(), true);
            \Log::error('MP API Error:', ['error' => $errorDetails, 'request' => $request->all()]);
            return response()->json(['error' => true, 'message' => 'Pagamento recusado.', 'details' => $errorDetails], 400);
        } catch (\Exception $e) {
            \Log::error('Erro no processamento: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json(['error' => true, 'message' => 'Ocorreu um erro interno.'], 500);
        }
    }
}
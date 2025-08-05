<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
// NÃO PRECISAMOS MAIS DE: use MercadoPago\SDK;
// ADICIONAMOS:
use MercadoPago\MercadoPagoConfig;

class CheckoutController extends Controller
{
    /**
     * Exibe a página de checkout para um serviço específico.
     */
        public function show(Service $service)
    {
        if (!$service->is_active) {
            abort(404);
        }

        $creatorAccessToken = $service->user->mp_access_token;
        if (!$creatorAccessToken) {
            return back()->with('error', 'A criadora deste serviço não está com a conta de pagamentos conectada.');
        }

        try {
            MercadoPagoConfig::setAccessToken($creatorAccessToken);

            $client = new PreferenceClient();
            $application_fee = config('mercadopago.application_fee_cents') / 100;

            $preference = $client->create([
                "items" => [
                    [
                        "id" => $service->id,
                        "title" => $service->title,
                        "description" => "Serviço UGC",
                        "quantity" => 1,
                        "unit_price" => $service->price_in_cents / 100,
                        "currency_id" => "BRL",
                    ]
                ],
                "marketplace_fee" => $application_fee,
                "external_reference" => "service_{$service->id}_" . time(),
                "back_urls" => [
                    'success' => route('checkout.status'),
                    'failure' => route('checkout.status'),
                    'pending' => route('checkout.status'),
                ],
                "auto_return" => "approved",
                // --- A CORREÇÃO ---
                "payment_methods" => [
                    "excluded_payment_methods" => [],
                    "excluded_payment_types" => [],
                    "installments" => 12
                ]
            ]);

        } catch (MPApiException $e) {
            \Log::error('MP API Error on Checkout', ['service_id' => $service->id, 'creator_id' => $service->user->id, 'error_message' => $e->getApiResponse()->getContent()]);
            return redirect()->route('home')->with('error', 'Não foi possível iniciar o pagamento. A configuração da criadora pode estar incompleta.');
        } catch (\Exception $e) {
            \Log::critical('General Error on Checkout', ['message' => $e->getMessage()]);
            return redirect()->route('home')->with('error', 'Ocorreu um erro inesperado. Nossa equipe já foi notificada.');
        }

        $publicKey = config('mercadopago.public_key');
        $preferenceId = $preference->id;

        return view('services.show_public', compact('service', 'publicKey', 'preferenceId'));
    }

    /**
     * Lida com o retorno do Mercado Pago após o pagamento.
     */
    public function status(Request $request)
    {
        return view('checkout.status', [
            'status' => $request->get('status'),
            'payment_id' => $request->get('payment_id'),
            'external_reference' => $request->get('external_reference'),
        ]);
    }
}
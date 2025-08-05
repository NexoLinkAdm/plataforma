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
    if (!$service->is_active || !$service->user->hasMercadoPagoConnected()) {
        return redirect()->route('home')->with('error', 'Este serviço não está disponível no momento.');
    }

    try {
        // Define o token de acesso da CRIADORA para esta requisição.
        MercadoPagoConfig::setAccessToken($service->user->mp_access_token);

        $client = new PreferenceClient();
        $application_fee = config('mercadopago.application_fee_cents') / 100;

        // Cria a preferência com uma estrutura de payment_methods explícita.
        $preference = $client->create([
            "items" => [
                [
                    "id" => $service->id,
                    "title" => $service->title,
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
            // --- REFORÇO DA ESTRUTURA DE PAGAMENTOS ---
            // A documentação do Brick com PreferenceId espera que a configuração
            // de pagamentos venha da preferência, e não do JS.
            "payment_methods" => [
                "excluded_payment_methods" => [], // Não exclui nenhum método específico
                "excluded_payment_types" => [],   // Não exclui nenhum TIPO (ex: 'ticket' para boleto)
                "installments" => 12, // Permite parcelamento
                "default_payment_method_id" => null,
                "default_installments" => null,
            ]
        ]);

    } catch (MPApiException $e) {
        \Log::error('MP API Error on Checkout', [
            'service_id' => $service->id,
            'error_message' => json_decode($e->getApiResponse()->getContent())
        ]);
        return redirect()->route('home')->with('error', 'Não foi possível iniciar o pagamento. A configuração da criadora pode estar incompleta.');
    } catch (\Exception $e) {
        \Log::critical('General Error on Checkout', ['message' => $e->getMessage()]);
        return redirect()->route('home')->with('error', 'Ocorreu um erro inesperado.');
    }

    // A Public Key é sempre a da PLATAFORMA, pois é ela quem renderiza o frontend.
    // O access_token da criadora já foi usado para criar a preferência.
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
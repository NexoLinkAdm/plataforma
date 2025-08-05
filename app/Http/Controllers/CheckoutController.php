<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Preference\PreferenceItemRequest;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\SDK;

class CheckoutController extends Controller
{
    /**
     * Exibe a página de checkout para um serviço específico.
     */
    public function show(Service $service)
    {
        // Garante que o serviço está ativo
        if (!$service->is_active) {
            abort(404);
        }

        try {
            // Define o token de acesso da criadora para esta operação
            SDK::setAccessToken($service->user->mp_access_token);

            // Cria o cliente de preferência
            $client = new PreferenceClient();

            // Cria o item da preferência
            $item = new PreferenceItemRequest();
            $item->id = $service->id;
            $item->title = $service->title;
            $item->description = "Serviço de UGC: " . $service->description;
            $item->quantity = 1;
            $item->unit_price = $service->price_in_cents / 100; // Preço deve ser em float
            $item->currency_id = "BRL";

            // Define a comissão da plataforma
            $application_fee = config('mercadopago.application_fee_cents') / 100;

            // Cria a preferência
            $preference = $client->create([
                "items" => [$item],
                "marketplace_fee" => $application_fee, // A comissão
                "external_reference" => "service_{$service->id}_" . time(), // Referência única
                "back_urls" => [
                    'success' => route('checkout.status'),
                    'failure' => route('checkout.status'),
                    'pending' => route('checkout.status'),
                ],
                "auto_return" => "approved", // Retorna automaticamente em caso de sucesso
            ]);

        } catch (MPApiException $e) {
            // Lida com erros da API do Mercado Pago
            // dd("Erro de API do MP", $e->getApiResponse()->getContent());
            return back()->with('error', 'Não foi possível iniciar o pagamento. Verifique se a conta do criador está configurada corretamente.');
        } catch (\Exception $e) {
            // Lida com outros erros
            // dd("Erro geral", $e->getMessage());
            return back()->with('error', 'Ocorreu um erro inesperado. Tente novamente mais tarde.');
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
        // Aqui vamos criar uma tabela de transações para registrar o resultado.
        // Por enquanto, vamos apenas exibir o status.
        return view('checkout.status', [
            'status' => $request->get('status'),
            'payment_id' => $request->get('payment_id'),
            'external_reference' => $request->get('external_reference'),
        ]);
    }
}
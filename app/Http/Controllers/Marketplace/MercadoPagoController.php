<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use MercadoPago\Client\OAuth\OAuthClient; // <-- FIX: Importa a classe que estava faltando.
use MercadoPago\Exceptions\MPApiException;

class MercadoPagoController extends Controller
{
    /**
     * Redireciona a criadora para a tela de autorização do Mercado Pago.
     */
    public function redirectToOAuth()
    {
        $url = sprintf(
            "https://auth.mercadopago.com.br/authorization?client_id=%s&response_type=code&platform_id=mp&redirect_uri=%s",
            config('mercadopago.client_id'),
            config('mercadopago.oauth_redirect_uri')
        );

        return Redirect::to($url);
    }

    /**
     * Lida com o callback do Mercado Pago após a autorização.
     */
    public function handleOAuthCallback(Request $request)
    {
        // Verifica se o Mercado Pago retornou um erro
        if ($request->has('error')) {
            Log::error('Mercado Pago OAuth Error', [
                'error' => $request->error,
                'description' => $request->error_description
            ]);
            return redirect()->route('dashboard')
                ->with('status', 'Falha ao conectar com o Mercado Pago. Tente novamente.');
        }

        // Verifica se o código de autorização foi recebido
        $authorizationCode = $request->query('code');
        if (!$authorizationCode) {
            return redirect()->route('dashboard')
                ->with('status', 'Código de autorização inválido.');
        }

        try {
            // Troca o código de autorização por um token de acesso
            $client = new OAuthClient();
            $response = $client->create([
                "client_secret" => config('mercadopago.client_secret'),
                "client_id" => config('mercadopago.client_id'),
                "grant_type" => "authorization_code",
                "code" => $authorizationCode,
                "redirect_uri" => config('mercadopago.oauth_redirect_uri'),
            ]);

            // Atualiza os dados da criadora logada
            $user = Auth::user();
            $user->update([
                'mp_user_id' => $response->user_id,
                'mp_access_token' => $response->access_token,
                'mp_refresh_token' => $response->refresh_token,
                'mp_token_expires_at' => now()->addSeconds($response->expires_in),
                'mp_connected_at' => now(),
            ]);

            return redirect()->route('dashboard')
                ->with('status', 'Sua conta do Mercado Pago foi conectada com sucesso!');

        } catch (MPApiException $exception) {
            Log::error('MPApiException ao obter token OAuth', [
                'status' => $exception->getApiResponse()->getStatusCode(),
                'response' => $exception->getApiResponse()->getContent(),
            ]);
            return redirect()->route('dashboard')
                ->with('status', 'Ocorreu um erro técnico ao conectar com o Mercado Pago.');
        } catch (\Exception $e) {
            Log::critical('Erro genérico no callback do Mercado Pago', ['exception' => $e->getMessage()]);
            return redirect()->route('dashboard')
                ->with('status', 'Ocorreu um erro inesperado. A equipe técnica foi notificada.');
        }
    }
}
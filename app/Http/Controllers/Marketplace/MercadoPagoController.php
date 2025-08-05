<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str; // <-- NOVO: Importa o helper de String para gerar o verifier.
use MercadoPago\Client\OAuth\OAuthClient;
use MercadoPago\Client\OAuth\OAuthCreateRequest;
use MercadoPago\Exceptions\MPApiException;

class MercadoPagoController extends Controller
{
    /**
     * Redireciona a criadora para a tela de autorização do Mercado Pago.
     */
    public function redirectToOAuth()
    {
        // --- INÍCIO DA LÓGICA PKCE ---
        // 1. Gera um "segredo" aleatório e seguro.
        $codeVerifier = Str::random(128);

        // 2. Salva o segredo na sessão para recuperá-lo no callback.
        session()->put('pkce_code_verifier', $codeVerifier);

        // 3. Gera a "prova" a partir do segredo (hash SHA-256 e encode base64url).
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
        // --- FIM DA LÓGICA PKCE ---

        // 4. Adiciona os parâmetros PKCE à URL de autorização.
        $url = sprintf(
            "https://auth.mercadopago.com.br/authorization?client_id=%s&response_type=code&platform_id=mp&redirect_uri=%s&code_challenge=%s&code_challenge_method=S256",
            config('mercadopago.client_id'),
            config('mercadopago.oauth_redirect_uri'),
            $codeChallenge // <-- NOVO
        );

        return Redirect::to($url);
    }

    /**
     * Lida com o callback do Mercado Pago após a autorização.
     */
    public function handleOAuthCallback(Request $request)
    {
        if ($request->has('error')) {
            Log::error('Mercado Pago OAuth Error', ['error' => $request->error, 'description' => $request->error_description]);
            return redirect()->route('dashboard')->with('status', 'Falha ao conectar com o Mercado Pago. Tente novamente.');
        }

        $authorizationCode = $request->query('code');
        if (!$authorizationCode) {
            return redirect()->route('dashboard')->with('status', 'Código de autorização inválido.');
        }

        // --- LÓGICA PKCE: RECUPERAÇÃO DO VERIFIER ---
        // Recupera e remove o segredo da sessão.
        $codeVerifier = session()->pull('pkce_code_verifier');
        if (!$codeVerifier) {
            Log::error('PKCE Error: code_verifier não encontrado na sessão.');
            return redirect()->route('dashboard')->with('status', 'Sua sessão expirou durante a conexão. Por favor, tente novamente.');
        }
        // --- FIM DA RECUPERAÇÃO ---

        try {
            $client = new OAuthClient();
            $oauthRequest = new OAuthCreateRequest();
            $oauthRequest->client_secret = config('mercadopago.client_secret');
            $oauthRequest->client_id = config('mercadopago.client_id');
            $oauthRequest->grant_type = 'authorization_code';
            $oauthRequest->code = $authorizationCode;
            $oauthRequest->redirect_uri = config('mercadopago.oauth_redirect_uri');
            $oauthRequest->code_verifier = $codeVerifier; // <-- NOVO: Envia o segredo para validação.

            $response = $client->create($oauthRequest);

            $user = Auth::user();
            $user->update([
                'mp_user_id' => $response->user_id,
                'mp_access_token' => $response->access_token,
                'mp_refresh_token' => $response->refresh_token,
                'mp_token_expires_at' => now()->addSeconds($response->expires_in),
                'mp_connected_at' => now(),
            ]);

            return redirect()->route('dashboard')->with('status', 'Sua conta do Mercado Pago foi conectada com sucesso!');

        } catch (MPApiException $exception) {
            Log::error('MPApiException ao obter token OAuth', [
                'status' => $exception->getApiResponse()->getStatusCode(),
                'response' => $exception->getApiResponse()->getContent(),
            ]);
            return redirect()->route('dashboard')->with('status', 'Ocorreu um erro técnico ao conectar com o Mercado Pago.');
        } catch (\Exception $e) {
            Log::critical('Erro genérico no callback do Mercado Pago', ['exception' => $e->getMessage()]);
            return redirect()->route('dashboard')->with('status', 'Ocorreu um erro inesperado. A equipe técnica foi notificada.');
        }
    }
}
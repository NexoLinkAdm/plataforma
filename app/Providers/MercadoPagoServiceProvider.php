<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use MercadoPago\SDK;

class MercadoPagoServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // O método register() é para vincular coisas no container de serviço.
        // Não devemos inicializar serviços aqui, apenas registrá-los.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // O método boot() é executado após todos os outros service providers
        // serem registrados. Aqui é o lugar seguro para inicializar o SDK.

        // Verificamos se as credenciais essenciais estão definidas.
        $accessToken = config('mercadopago.access_token');

        if ($accessToken) {
            // Inicializamos o SDK do Mercado Pago com o access token da PLATAFORMA.
            // Este token será o padrão, mas poderá ser trocado dinamicamente
            // para usar o token da criadora ao processar um pagamento específico.
            SDK::setAccessToken($accessToken);
        }
    }
}
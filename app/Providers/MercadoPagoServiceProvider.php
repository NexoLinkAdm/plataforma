<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\SDK;

class MercadoPagoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $accessToken = config('mercadopago.access_token');

        if ($accessToken) {
            SDK::setAccessToken($accessToken);

        }
    }
}

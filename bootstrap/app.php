<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middlewares globais podem ser registrados aqui se necessário.
        // Por enquanto, não precisamos mexer.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Tratamento de exceções globais pode ser configurado aqui.
    })
    ->withProviders([
        // ESTA É A FORMA CORRETA DE REGISTRAR PROVIDERS CUSTOMIZADOS
        // NO LARAVEL 12.29+
        \App\Providers\MercadoPagoServiceProvider::class,
        \App\Providers\AuthServiceProvider::class,
    ])
    ->create();
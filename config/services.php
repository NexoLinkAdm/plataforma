<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Credenciais da Aplicação (Plataforma)
    |--------------------------------------------------------------------------
    |
    | Estas são as credenciais da sua aplicação (marketplace) no Mercado Pago.
    | Elas são usadas para operações da plataforma, como o fluxo OAuth.
    |
    */
    'app_name' => env('MERCADO_PAGO_APP_NAME', 'Laravel Marketplace'),
    'public_key' => env('MERCADO_PAGO_PUBLIC_KEY'),
    'access_token' => env('MERCADO_PAGO_ACCESS_TOKEN'),
    'client_id' => env('MERCADO_PAGO_CLIENT_ID'),
    'client_secret' => env('MERCADO_PAGO_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Configurações de Split de Pagamento
    |--------------------------------------------------------------------------
    |
    | Defina a comissão padrão da plataforma em centavos.
    | Exemplo: 1000 = R$ 10,00.
    | Isso pode ser sobrescrito por serviço ou plano no futuro.
    |
    */
    'application_fee_cents' => 1000, // Comissão de R$ 10,00 como exemplo

    /*
    |--------------------------------------------------------------------------
    | URL de Redirecionamento OAuth
    |--------------------------------------------------------------------------
    |
    | A URL para a qual o Mercado Pago irá redirecionar o usuário após
    | a autorização OAuth. Deve corresponder à configurada no painel.
    |
    */
    'oauth_redirect_uri' => env('APP_URL', 'http://localhost') . '/oauth/callback',

];

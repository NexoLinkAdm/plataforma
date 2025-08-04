<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Manutenção
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Autoload (vendor)
require __DIR__.'/vendor/autoload.php';

// Bootstrap e inicialização da app
/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

// Handle da requisição
$response = $app->handle(
    $request = Request::capture()
);

$response->send();

$app->terminate($request, $response);

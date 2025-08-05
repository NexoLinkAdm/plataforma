<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Marketplace\MercadoPagoController;
use App\Http\Controllers\ServiceController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rotas para gerenciamento de serviços (protegidas por autenticação)
    Route::middleware(['auth'])->group(function () {
        Route::resource('servicos', ServiceController::class)->except(['show']);
    });
});

// Rotas para o fluxo de conexão do Mercado Pago (protegidas por autenticação)
Route::middleware(['auth'])->group(function () {
    Route::get('/conectar-mercadopago', [MercadoPagoController::class, 'redirectToOAuth'])->name('mp.connect');
    Route::get('/oauth/callback', [MercadoPagoController::class, 'handleOAuthCallback'])->name('mp.callback');
});

// Rota pública para visualização do serviço (não precisa de login)
Route::get('/servico/{service:slug}', [ServiceController::class, 'show'])->name('service.show.public');


// ... rotas de auth
require __DIR__ . '/auth.php';

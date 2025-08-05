<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Marketplace\MercadoPagoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Públicas
|--------------------------------------------------------------------------
|
| Rotas acessíveis para qualquer visitante, logado ou não.
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Página pública de um serviço, acessada via slug
Route::get('/servico/{service:slug}', [ServiceController::class, 'show'])->name('service.show.public');


/*
|--------------------------------------------------------------------------
| Rotas Autenticadas
|--------------------------------------------------------------------------
|
| Rotas que exigem que o usuário (criadora) esteja logado e com o
| e-mail verificado.
|
*/

Route::middleware(['auth'])->group(function () {

    // Dashboard Principal
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Gerenciamento de Perfil (do Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Gerenciamento de Serviços (CRUD)
    Route::resource('servicos', ServiceController::class)->except(['show']);

    // Conexão com Mercado Pago (OAuth)
    Route::get('/conectar-mercadopago', [MercadoPagoController::class, 'redirectToOAuth'])->name('mp.connect');
    Route::get('/oauth/callback', [MercadoPagoController::class, 'handleOAuthCallback'])->name('mp.callback');

});


/*
|--------------------------------------------------------------------------
| Rotas de Autenticação do Breeze
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

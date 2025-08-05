<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Marketplace\MercadoPagoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController; // <-- Adicionar este import


/*
|--------------------------------------------------------------------------
| Rotas Públicas (Acessíveis a todos)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

Route::get('/servico/{service:slug}', [CheckoutController::class, 'show'])->name('service.show.public');
Route::get('/checkout/status', [CheckoutController::class, 'status'])->name('checkout.status');


/*
|--------------------------------------------------------------------------
| Rotas Protegidas (Exigem Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Perfil do Usuário
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Conexão Mercado Pago
    Route::get('/conectar-mercadopago', [MercadoPagoController::class, 'redirectToOAuth'])->name('mp.connect');
    Route::get('/oauth/callback', [MercadoPagoController::class, 'handleOAuthCallback'])->name('mp.callback');

    // Gerenciamento de Serviços (CRUD)
    // A rota 'index' está aqui para ser acessada pelo menu de navegação.
    Route::get('/servicos', [ServiceController::class, 'index'])->name('servicos.index');
    Route::get('/servicos/criar', [ServiceController::class, 'create'])->name('servicos.create');
    Route::post('/servicos', [ServiceController::class, 'store'])->name('servicos.store');
    Route::get('/servicos/{service}/edit', [ServiceController::class, 'edit'])->name('servicos.edit');
    Route::put('/servicos/{service}', [ServiceController::class, 'update'])->name('servicos.update');
    Route::delete('/servicos/{service}', [ServiceController::class, 'destroy'])->name('servicos.destroy');

});

require __DIR__.'/auth.php';
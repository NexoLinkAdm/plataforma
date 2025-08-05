<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Marketplace\MercadoPagoController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Públicas (Acessíveis a todos)
|--------------------------------------------------------------------------
|
| Estas rotas não exigem que o usuário esteja logado.
| O middleware 'web' (que inclui sessão e CSRF) é aplicado automaticamente.
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Exibe a página de vendas/checkout de um serviço
Route::get('/servico/{service:slug}', [CheckoutController::class, 'show'])->name('service.show.public');

// Recebe o status de retorno do Mercado Pago (para pagamentos como Boleto/Pix)
Route::get('/checkout/status', [CheckoutController::class, 'status'])->name('checkout.status');

// Processa o pagamento via AJAX vindo do Checkout Brick (protegido por CSRF)
Route::post('/process-payment', [PaymentController::class, 'processPayment'])->name('payment.process');


/*
|--------------------------------------------------------------------------
| Rotas Protegidas (Exigem que a Criadora esteja logada)
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
    Route::get('/servicos', [ServiceController::class, 'index'])->name('servicos.index');
    Route::get('/servicos/criar', [ServiceController::class, 'create'])->name('servicos.create');
    Route::post('/servicos', [ServiceController::class, 'store'])->name('servicos.store');
    Route::get('/servicos/{service}/edit', [ServiceController::class, 'edit'])->name('servicos.edit');
    Route::put('/servicos/{service}', [ServiceController::class, 'update'])->name('servicos.update');
    Route::delete('/servicos/{service}', [ServiceController::class, 'destroy'])->name('servicos.destroy');

});

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação do Breeze
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
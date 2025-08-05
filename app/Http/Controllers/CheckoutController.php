<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Exibe a página de checkout para um serviço específico.
     */
    public function show(Service $service)
    {
        // Valida se o serviço pode ser vendido
        if (!$service->is_active || !$service->user->hasMercadoPagoConnected()) {
            return redirect()->route('home')->with('error', 'Este serviço não está disponível no momento.');
        }

        // A Public Key para o frontend é sempre a da PLATAFORMA.
        $publicKey = config('mercadopago.public_key');

        return view('services.show_public', compact('service', 'publicKey'));
    }

    /**
     * Lida com o retorno do Mercado Pago (para redirecionamentos de alguns métodos de pagamento).
     */
    public function status(Request $request)
    {
        // Esta view será mais utilizada quando implementarmos Boleto/Pix.
        return view('checkout.status', [
            'status' => $request->get('status'),
            'payment_id' => $request->get('payment_id'),
            'external_reference' => $request->get('external_reference'),
        ]);
    }
}
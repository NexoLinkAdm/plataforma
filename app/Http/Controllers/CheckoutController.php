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
        if (!$service->is_active || !$service->user->hasMercadoPagoConnected()) {
            return redirect()->route('home')->with('error', 'Este serviço não está disponível no momento.');
        }

        // A Public Key é sempre a da PLATAFORMA
        $publicKey = config('mercadopago.public_key');

        return view('services.show_public', compact('service', 'publicKey'));
    }

    /**
     * Lida com o retorno do Mercado Pago após o pagamento (redirecionamento).
     */
    public function status(Request $request)
    {
        return view('checkout.status', [
            'status' => $request->get('status'),
            'payment_id' => $request->get('payment_id'),
            'external_reference' => $request->get('external_reference'),
        ]);
    }
}
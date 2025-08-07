<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function show(Service $service)
    {
        if (!$service->user->hasMercadoPagoConnected() || empty($service->user->mp_public_key)) {
            // Se a criadora não estiver conectada OU não tiver a public_key salva, o serviço não pode ser vendido.
            return redirect()->route('home')->with('error', 'O vendedor deste serviço não está configurado para receber pagamentos.');
        }

        // --- MUDANÇA CRÍTICA ---
        // Pega a Public Key da CRIADORA, e não a da plataforma.
        $publicKey = $service->user->mp_public_key;
        // --- FIM DA MUDANÇA ---

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
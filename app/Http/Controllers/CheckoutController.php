<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse; // Importa para a tipagem de retorno
use Illuminate\View\View;               // Importa para a tipagem de retorno

class CheckoutController extends Controller
{
    /**
     * Exibe a página de checkout para um serviço específico.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Service $service)
    {
        // Validação: Garante que a criadora está apta a vender este serviço
        if (!$service->user->hasMercadoPagoConnected() || empty($service->user->mp_public_key)) {
            
            // Redireciona para a página inicial se a configuração do vendedor estiver incompleta.
            return redirect('/')->with('error', 'O vendedor deste serviço não está configurado corretamente para receber pagamentos.');
        }

        // Pega a Public Key da CRIADORA (essencial para a arquitetura correta do split).
        $publicKey = $service->user->mp_public_key;
        
        // Retorna a view do checkout, passando o serviço e a chave pública da criadora.
        return view('services.show_public', compact('service', 'publicKey'));
    }

    /**
     * Lida com o retorno do Mercado Pago (para redirecionamentos de alguns métodos de pagamento).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function status(Request $request): View
    {
        // Exibe a página de status final para o cliente.
        return view('checkout.status', [
            'status' => $request->get('status'),
            'payment_id' => $request->get('payment_id'),
            'external_reference' => $request->get('external_reference'),
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard principal da criadora.
     */
    public function __invoke()
    {
        $user = Auth::user();

        // Conta quantos serviços a criadora já tem
        $servicesCount = $user->services()->count();

        // Retorna a view com os dados necessários
        return view('dashboard', [
            'servicesCount' => $servicesCount,
        ]);
    }
}
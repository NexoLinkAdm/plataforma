<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; // Usaremos o Gate para uma verificação simples

class ServiceController extends Controller
{
    public function index()
    {
        $services = Auth::user()->services()->latest()->paginate(10);
        return view('services.index', compact('services'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(StoreServiceRequest $request)
    {
        Auth::user()->services()->create($request->validated());
        return redirect()->route('servicos.index')->with('status', 'Serviço criado com sucesso!');
    }

    public function show(Service $service)
    {
        return view('services.show_public', compact('service'));
    }

    /**
     * LÓGICA SIMPLIFICADA PARA 'EDITAR'
     */
    public function edit(Service $service)
    {
        // Verificação manual: o ID do usuário logado é o mesmo do dono do serviço?
        if (Auth::id() !== $service->user_id) {
            // Se não for, aborte a operação com um erro 403.
            abort(403, 'Acesso Negado');
        }

        // Se a verificação passar, mostre a view.
        return view('services.edit', compact('service'));
    }

    /**
     * LÓGICA SIMPLIFICADA PARA 'ATUALIZAR'
     */
    public function update(StoreServiceRequest $request, Service $service)
    {
        // Verificação manual.
        if (Auth::id() !== $service->user_id) {
            abort(403, 'Acesso Negado');
        }

        $service->update($request->validated());
        return redirect()->route('servicos.index')->with('status', 'Serviço atualizado com sucesso!');
    }

    /**
     * LÓGICA SIMPLIFICADA PARA 'EXCLUIR'
     */
    public function destroy(Service $service)
    {
        // Verificação manual.
        if (Auth::id() !== $service->user_id) {
            abort(403, 'Acesso Negado');
        }

        $service->delete();
        return redirect()->route('servicos.index')->with('status', 'Serviço excluído com sucesso!');
    }
}
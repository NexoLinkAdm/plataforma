<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    // ... os métodos index, create, store, show permanecem como antes ...

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
     * Mostra o formulário para editar o serviço.
     */
    public function edit(Service $service)
    {
        // Lógica de autorização manual e direta
        if (Auth::id() !== $service->user_id) {
            abort(403, 'Acesso não autorizado.');
        }

        return view('services.edit', compact('service'));
    }

    /**
     * Atualiza o serviço no banco de dados.
     */
    public function update(StoreServiceRequest $request, Service $service)
    {
        // Lógica de autorização manual e direta
        if (Auth::id() !== $service->user_id) {
            abort(403, 'Acesso não autorizado.');
        }

        $service->update($request->validated());
        return redirect()->route('servicos.index')->with('status', 'Serviço atualizado com sucesso!');
    }

    /**
     * Remove o serviço do banco de dados.
     */
    public function destroy(Service $service)
    {
        // Lógica de autorização manual e direta
        if (Auth::id() !== $service->user_id) {
            abort(403, 'Acesso não autorizado.');
        }

        $service->delete();
        return redirect()->route('servicos.index')->with('status', 'Serviço excluído com sucesso!');
    }
}
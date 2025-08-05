<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Models\Service;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ServiceController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $services = auth()->user()->services()->latest()->paginate(10);
        return view('services.index', compact('services'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(StoreServiceRequest $request)
    {
        // Esta é a forma mais segura e idiomática do Laravel.
        // O `services()` é o relacionamento que definimos no Model User.
        // O `create()` neste contexto automaticamente adiciona o `user_id` correto.
        auth()->user()->services()->create($request->validated());

        return redirect()->route('servicos.index')->with('status', 'Serviço criado com sucesso!');
    }

    public function show(Service $service)
    {
        return view('services.show_public', compact('service'));
    }

    public function edit(Service $service)
    {
        $this->authorize('update', $service);
        return view('services.edit', compact('service'));
    }

    public function update(StoreServiceRequest $request, Service $service)
    {
        $this->authorize('update', $service);
        $service->update($request->validated());
        return redirect()->route('servicos.index')->with('status', 'Serviço atualizado com sucesso!');
    }

    public function destroy(Service $service)
    {
        $this->authorize('delete', $service);
        $service->delete();
        return redirect()->route('servicos.index')->with('status', 'Serviço excluído com sucesso!');
    }
}

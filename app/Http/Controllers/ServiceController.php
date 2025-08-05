<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest; // <-- MUITO IMPORTANTE: Usar a nova request
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class ServiceController extends Controller
{   
    use AuthorizesRequests;

    public function index()
    {
        $services = Auth::user()->services()->latest()->paginate(10);
        return view('services.index', compact('services'));
    }

    public function create()
    {
        return view('services.create');
    }

    // Agora usa a StoreServiceRequest para validar
    public function store(StoreServiceRequest $request)
    {
        Auth::user()->services()->create($request->validated());
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

    // Agora usa a StoreServiceRequest para validar
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
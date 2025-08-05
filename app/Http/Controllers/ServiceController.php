<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    // Ação: Listar todos os serviços da criadora logada
    public function index()
    {
        $services = Auth::user()->services()->latest()->paginate(10);
        return view('services.index', compact('services'));
    }

    // Ação: Mostrar o formulário para criar um novo serviço
    public function create()
    {
        return view('services.create');
    }

    // Ação: Salvar o novo serviço no banco
    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());

        Auth::user()->services()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price_in_cents' => $validated['price_in_cents'],
            'delivery_time_days' => $validated['delivery_time_days'],
            'revisions_limit' => $validated['revisions_limit'],
            'slug' => Str::slug($validated['title']), // Gerar slug
        ]);

        return redirect()->route('servicos.index')->with('status', 'Serviço criado com sucesso!');
    }

    // Ação: Mostrar a página PÚBLICA de um serviço
    public function show(Service $service)
    {
        // Esta view será criada no próximo módulo, antes do checkout.
        return view('services.show_public', compact('service'));
    }

    // Ação: Mostrar o formulário para editar um serviço
    public function edit(Service $service)
    {
        $this->authorize('update', $service); // Garante que a criadora só edite o seu
        return view('services.edit', compact('service'));
    }

    // Ação: Atualizar o serviço no banco
    public function update(Request $request, Service $service)
    {
        $this->authorize('update', $service);
        $validated = $request->validate($this->validationRules($service->id));

        $service->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price_in_cents' => $validated['price_in_cents'],
            'delivery_time_days' => $validated['delivery_time_days'],
            'revisions_limit' => $validated['revisions_limit'],
            'slug' => Str::slug($validated['title']), // Atualiza o slug
        ]);

        return redirect()->route('servicos.index')->with('status', 'Serviço atualizado com sucesso!');
    }

    // Ação: Excluir um serviço
    public function destroy(Service $service)
    {
        $this->authorize('delete', $service);
        $service->delete();
        return redirect()->route('servicos.index')->with('status', 'Serviço excluído com sucesso!');
    }

    // Centraliza as regras de validação para reuso
    private function validationRules($serviceId = null)
    {
        // O slug único precisa ignorar o próprio ID ao editar
        $slugRule = 'unique:services,slug';
        if ($serviceId) {
            $slugRule .= ',' . $serviceId;
        }

        return [
            'title' => 'required|string|max:100|' . $slugRule,
            'description' => 'required|string|max:5000',
            'price_in_cents' => 'required|integer|min:500', // Mínimo de R$ 5,00
            'delivery_time_days' => 'required|integer|min:1',
            'revisions_limit' => 'required|integer|min:0',
        ];
    }
}
@if ($errors->any())
    <div class="mb-4">
        <div class="font-medium text-red-600 dark:text-red-400">{{ __('Opa! Algo deu errado.') }}</div>
        <ul class="mt-3 list-disc list-inside text-sm text-red-600 dark:text-red-400">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-4">
    <div>
        <x-input-label for="title" :value="__('Título do Serviço')" />
        <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $service->title)" required autofocus autocomplete="off" />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Descrição Completa')" />
        <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="5" required>{{ old('description', $service->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <x-input-label for="price_in_cents" :value="__('Preço (R$)')" />
            {{-- MUDANÇA: type="text" e inputmode="decimal" para aceitar vírgula no celular --}}
            <x-text-input id="price_in_cents" class="block mt-1 w-full" type="text" inputmode="decimal" name="price_in_cents" :value="old('price_in_cents', $service->price_in_cents ? number_format($service->price_in_cents / 100, 2, ',', '.') : '')" required />
            <x-input-error :messages="$errors->get('price_in_cents')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="delivery_time_days" :value="__('Prazo de Entrega (dias)')" />
            <x-text-input id="delivery_time_days" class="block mt-1 w-full" type="number" name="delivery_time_days" :value="old('delivery_time_days', $service->delivery_time_days)" required />
            <x-input-error :messages="$errors->get('delivery_time_days')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="revisions_limit" :value="__('Nº de Revisões')" />
            <x-text-input id="revisions_limit" class="block mt-1 w-full" type="number" name="revisions_limit" :value="old('revisions_limit', $service->revisions_limit ?? 1)" required />
            <x-input-error :messages="$errors->get('revisions_limit')" class="mt-2" />
        </div>
    </div>
</div>

<div class="mt-8 flex justify-end">
    <a href="{{ route('servicos.index') }}" class="text-sm text-gray-700 dark:text-gray-400 hover:underline inline-flex items-center px-4 py-2 mr-4">Cancelar</a>
    <x-primary-button>
        {{ $service->exists ? 'Atualizar Serviço' : 'Salvar Serviço' }}
    </x-primary-button>
</div>

{{-- NÃO PRECISAMOS MAIS DE JAVASCRIPT AQUI! O BACKEND FAZ TUDO. --}}
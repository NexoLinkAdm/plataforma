@if ($errors->any())
    <div class="mb-4">
        <ul class="list-disc list-inside text-sm text-red-600">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-4">
    <div>
        <x-input-label for="title" :value="__('Título do Serviço')" />
        <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $service->title)" required autofocus />
    </div>

    <div>
        <x-input-label for="description" :value="__('Descrição Completa')" />
        <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="5" required>{{ old('description', $service->description) }}</textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <x-input-label for="price_in_cents" :value="__('Preço (em R$)')" />
            <x-text-input id="price_in_cents" class="block mt-1 w-full" type="number" step="0.01" name="price_in_cents" :value="old('price_in_cents', $service->price_in_cents ? $service->price_in_cents / 100 : '')" required />
            <small class="text-gray-500">Use ponto para centavos. Ex: 250.50</small>
        </div>
        <div>
            <x-input-label for="delivery_time_days" :value="__('Prazo de Entrega (dias)')" />
            <x-text-input id="delivery_time_days" class="block mt-1 w-full" type="number" name="delivery_time_days" :value="old('delivery_time_days', $service->delivery_time_days)" required />
        </div>
        <div>
            <x-input-label for="revisions_limit" :value="__('Nº de Revisões')" />
            <x-text-input id="revisions_limit" class="block mt-1 w-full" type="number" name="revisions_limit" :value="old('revisions_limit', $service->revisions_limit)" required />
        </div>
    </div>
</div>

<div class="mt-6 flex justify-end">
    <a href="{{ route('servicos.index') }}" class="mr-4 inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300">Cancelar</a>
    <x-primary-button>
        {{ __('Salvar Serviço') }}
    </x-primary-button>
</div>

<script>
    // Converte o valor em R$ para centavos antes de enviar o formulário
    document.querySelector('form').addEventListener('submit', function(e) {
        const priceInput = document.getElementById('price_in_cents');
        if (priceInput.value) {
            priceInput.value = Math.round(parseFloat(priceInput.value) * 100);
        }
    });
</script>
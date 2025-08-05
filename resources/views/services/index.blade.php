<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Meus Serviços') }}
            </h2>
            <a href="{{ route('servicos.create') }}" class="inline-block bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700">
                Criar Novo Serviço
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if(session('status'))
                        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ session('status') }}
                        </div>
                    @endif

                    @forelse($services as $service)
                        <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                            <div>
                                <h3 class="font-bold text-lg">{{ $service->title }}</h3>
                                <p class="text-sm text-gray-500">Preço: R$ {{ number_format($service->price_in_cents / 100, 2, ',', '.') }}</p>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('service.show.public', $service) }}" target="_blank" class="text-green-500 hover:text-green-700">Ver Link</a>
                                <a href="{{ route('servicos.edit', $service) }}" class="text-blue-500 hover:text-blue-700">Editar</a>
                                <form action="{{ route('servicos.destroy', $service) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este serviço?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700">Excluir</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p>Você ainda não criou nenhum serviço. <a href="{{ route('servicos.create') }}" class="text-blue-500">Crie o seu primeiro agora!</a></p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
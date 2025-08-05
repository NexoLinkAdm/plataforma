<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"> 
                   <div class="p-6 text-gray-900 dark:text-gray-100">
                        {{ __("You're logged in!") }}
                        {{-- Bloco de Conexão com Mercado Pago --}}
                        <div class="p-6 text-gray-900 dark:text-gray-100 border-t border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold mb-2">Monetização</h3>

                            @if (Auth::user()->hasMercadoPagoConnected())
                                <div class="flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400"
                                    role="alert">
                                    <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                    </svg>
                                    <span class="sr-only">Info</span>
                                    <div>
                                        <span class="font-medium">Sua conta do Mercado Pago está conectada!</span> Você já
                                        pode criar serviços e receber pagamentos.
                                        <br><small>Conectada em:
                                            {{ Auth::user()->mp_connected_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                </div>
                            @else
                                <p class="mb-4">Para começar a vender seus serviços UGC, você precisa conectar sua conta do
                                    Mercado Pago. Nós cuidaremos do split de pagamentos automaticamente.</p>
                                <a href="{{ route('mp.connect') }}"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Conectar com Mercado Pago
                                </a>
                            @endif
                        </div>

                </div>
            </div>
        </div> <!-- max-w-7xl -->
    </div> <!-- py-12 -->
</x-app-layout>
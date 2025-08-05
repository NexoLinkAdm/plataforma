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

                    {{-- NOVO: Checklist de Início Rápido --}}
                    <div class="mb-8 p-6 border border-blue-200 dark:border-gray-700 rounded-lg bg-blue-50 dark:bg-gray-900/50">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Seu Guia de Início Rápido</h3>
                        <ol class="space-y-4">
                            {{-- Passo 1: Conta Conectada --}}
                            <li class="flex items-center text-green-600 dark:text-green-400">
                                <span class="flex items-center justify-center w-8 h-8 bg-green-200 dark:bg-green-900 rounded-full -ms-4 me-4">
                                    <svg class="w-3.5 h-3.5 text-green-600 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                                    </svg>
                                </span>
                                <span class="font-medium">Passo 1: Conta Criada</span>
                            </li>

                            {{-- Passo 2: Conectar Mercado Pago --}}
                            @if (Auth::user()->hasMercadoPagoConnected())
                                <li class="flex items-center text-green-600 dark:text-green-400">
                                    <span class="flex items-center justify-center w-8 h-8 bg-green-200 dark:bg-green-900 rounded-full -ms-4 me-4">
                                        <svg class="w-3.5 h-3.5 text-green-600 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                                        </svg>
                                    </span>
                                    <span class="font-medium">Passo 2: Conta de Pagamento Conectada</span>
                                </li>
                            @else
                                <li class="flex items-center text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center justify-center w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full -ms-4 me-4">
                                        <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 1v5h-5M2 19v-5h5m10-4a8 8 0 0 1-14.947 3.97M1 10a8 8 0 0 1 14.947-3.97"/>
                                        </svg>
                                    </span>
                                    <div class="w-full flex justify-between items-center">
                                        <span class="font-medium">Passo 2: Conectar Conta de Pagamento</span>
                                        <a href="{{ route('mp.connect') }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">Conectar Agora</a>
                                    </div>
                                </li>
                            @endif

                            {{-- Passo 3: Criar Primeiro Serviço --}}
                            @if ($servicesCount > 0)
                                <li class="flex items-center text-green-600 dark:text-green-400">
                                     <span class="flex items-center justify-center w-8 h-8 bg-green-200 dark:bg-green-900 rounded-full -ms-4 me-4">
                                        <svg class="w-3.5 h-3.5 text-green-600 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                                        </svg>
                                    </span>
                                    <div class="w-full flex justify-between items-center">
                                        <span class="font-medium">Passo 3: Primeiro Serviço Criado!</span>
                                        <a href="{{ route('servicos.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">Gerenciar Serviços</a>
                                    </div>
                                </li>
                            @else
                                <li class="flex items-center text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center justify-center w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full -ms-4 me-4">
                                         <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 1v5h-5M2 19v-5h5m10-4a8 8 0 0 1-14.947 3.97M1 10a8 8 0 0 1 14.947-3.97"/>
                                        </svg>
                                    </span>
                                    <div class="w-full flex justify-between items-center">
                                        <span class="font-medium">Passo 3: Crie seu Primeiro Serviço</span>
                                        @if (Auth::user()->hasMercadoPagoConnected())
                                            <a href="{{ route('servicos.create') }}" class="inline-flex items-center px-3 py-1.5 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 animate-pulse">Criar Agora</a>
                                        @else
                                             <button disabled class="inline-flex items-center px-3 py-1.5 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed">Criar Agora</button>
                                             <p class="text-xs text-right ms-2">Conecte sua conta de pagamento para habilitar.</p>
                                        @endif
                                    </div>
                                </li>
                            @endif
                        </ol>
                    </div>

                    {{-- Link para a área de serviços --}}
                    <div class="text-center mt-4 border-t dark:border-gray-700 pt-6">
                        <a href="{{ route('servicos.index') }}" class="text-blue-500 dark:text-blue-400 hover:underline font-semibold">
                            Ir para o Gerenciador de Serviços →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
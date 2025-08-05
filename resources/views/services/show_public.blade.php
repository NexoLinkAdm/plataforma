<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $service->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-2xl p-8 bg-white rounded-lg shadow-md text-center">
        
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            Página Pública do Serviço (Placeholder)
        </h1>
        <p class="text-gray-600 mb-6">Esta será a página de vendas do seu serviço.</p>

        <div class="border-t border-b py-6 my-6">
            <h2 class="text-2xl font-semibold text-gray-900">{{ $service->title }}</h2>
            <p class="mt-4 text-gray-700 text-left whitespace-pre-wrap">{{ $service->description }}</p>
            <div class="mt-4 text-3xl font-bold text-blue-600">
                R$ {{ number_format($service->price_in_cents / 100, 2, ',', '.') }}
            </div>
        </div>
        
        <div class="mt-8">
            <button class="w-full bg-gray-400 text-white font-bold py-3 px-6 rounded-lg cursor-not-allowed">
                Botão de Comprar (Será ativado no Módulo 4)
            </button>
            <a href="{{ url()->previous() }}" class="inline-block mt-4 text-sm text-gray-500 hover:underline">
                ← Voltar
            </a>
        </div>

    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status do Pagamento</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-lg p-8 bg-white rounded-lg shadow-md text-center">

        @if($status == 'approved')
            <div class="mx-auto w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mt-4">Pagamento Aprovado!</h1>
            <p class="text-gray-600 mt-2">Obrigado pela sua compra! A criadora já foi notificada para iniciar seu trabalho.</p>
        @elseif($status == 'pending')
            <div class="mx-auto w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mt-4">Pagamento Pendente</h1>
            <p class="text-gray-600 mt-2">Seu pagamento está sendo processado. Avisaremos assim que for aprovado.</p>
        @else
            <div class="mx-auto w-20 h-20 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mt-4">Pagamento Falhou</h1>
            <p class="text-gray-600 mt-2">Ocorreu um problema ao processar seu pagamento. Por favor, tente novamente.</p>
        @endif

        <div class="text-sm text-gray-500 mt-8 border-t pt-4">
            <p>ID do Pagamento: {{ $payment_id ?? 'N/A' }}</p>
            <p>Referência: {{ $external_reference ?? 'N/A' }}</p>
        </div>

    </div>
</body>
</html>
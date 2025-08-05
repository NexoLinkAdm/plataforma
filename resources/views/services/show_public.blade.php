<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - {{ $service->title }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://sdk.mercadopago.com/js/v2"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="container w-full max-w-lg mx-auto bg-white rounded-lg shadow-lg p-8">
        <div class="service-info text-center border-b pb-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800">{{ $service->title }}</h1>
            <p class="text-gray-500">por {{ $service->user->name }}</p>
            <div class="text-3xl font-bold text-blue-600 mt-4">R$ {{ number_format($service->price_in_cents / 100, 2, ',', '.') }}</div>
        </div>
        <div id="paymentBrick_container"><p class="text-center text-gray-500 p-8">Carregando...</p></div>
        <div id="payment_error_container" class="hidden mt-4 p-3 bg-red-100 text-red-700 rounded-md text-sm">
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                <span id="error_message"></span>
            </div>
            <button id="retry_button" class="mt-2 px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 hidden">Tentar Novamente</button>
        </div>
        <div id="loading_overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg text-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div><p class="text-gray-700">Processando...</p></div>
        </div>
    </div>
    <script>
        const mp = new MercadoPago('{{ $publicKey }}', { locale: 'pt-BR' });
        let paymentBrickController = null;
        const showError = (message, canRetry = false) => {
            const errorContainer = document.getElementById('payment_error_container'), errorMessage = document.getElementById('error_message'), retryButton = document.getElementById('retry_button');
            errorMessage.textContent = message; errorContainer.classList.remove('hidden');
            canRetry ? (retryButton.classList.remove('hidden'), retryButton.onclick = () => { errorContainer.classList.add('hidden'); retryButton.classList.add('hidden'); renderPaymentBrick(mp.bricks()); }) : retryButton.classList.add('hidden');
        };
        const hideLoading = () => document.getElementById('loading_overlay').classList.add('hidden');
        const renderPaymentBrick = async (bricksBuilder) => {
            try {
                if (paymentBrickController) { paymentBrickController.unmount(); paymentBrickController = null; }
                const settings = {
                    initialization: { amount: {{ $service->price_in_cents / 100 }} },
                    customization: { paymentMethods: { creditCard: 'all', debitCard: 'all', ticket: 'all', mercadoPago: 'all' }, visual: { style: { theme: 'default' } } },
                    callbacks: {
                        onReady: () => console.log('Brick pronto.'),
                        onError: (error) => showError('Erro ao carregar o formulário. Recarregue a página.', true),
                        onSubmit: async ({ selectedPaymentMethod, formData }) => {
                            if (!formData.token || formData.token.length < 32) return showError('Erro ao processar dados. Tente novamente.', true), Promise.reject();
                            document.getElementById('loading_overlay').classList.remove('hidden'); document.getElementById('payment_error_container').classList.add('hidden');
                            try {
                                const response = await fetch("{{ route('payment.process') }}", {
                                    method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                                    body: JSON.stringify({ formData, service_id: {{ $service->id }} })
                                });
                                const data = await response.json();
                                if (!response.ok) { hideLoading(); data.code === 'token_expired' ? showError(data.message, true) : showError(data.message || 'Pagamento recusado.'); throw new Error(data.message); }
                                window.location.href = `/checkout/status?status=${data.status}&payment_id=${data.payment_id}`;
                            } catch (error) {
                                hideLoading(); if (!document.getElementById('payment_error_container').classList.contains('hidden')) return Promise.reject();
                                showError('Erro de conexão. Tente novamente.', true); return Promise.reject();
                            }
                        },
                    }
                };
                paymentBrickController = await bricksBuilder.create('payment', 'paymentBrick_container', settings);
            } catch (error) { showError('Erro ao carregar o formulário. Recarregue a página.', true); }
        };
        renderPaymentBrick(mp.bricks());
        window.addEventListener('beforeunload', () => { if (paymentBrickController) paymentBrickController.unmount(); });
    </dcript>
</body>
</html>
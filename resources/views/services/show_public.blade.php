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
        <div id="paymentBrick_container"><p class="text-center text-gray-500 p-8">Carregando formulário...</p></div>
        <div id="payment_error_container" class="hidden mt-4 p-3 bg-red-100 text-red-700 rounded-md text-sm">
            <span id="error_message"></span>
            <button id="retry_button" class="ml-4 px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 hidden">Tentar Novamente</button>
        </div>
        <div id="loading_overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg text-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div><p class="text-gray-700">Processando...</p></div>
        </div>
    </div>
    <script>
        const mp = new MercadoPago('{{ $publicKey }}', { locale: 'pt-BR' });
        let paymentBrickController = null;
        const errorContainer = document.getElementById('payment_error_container');
        const errorMessageSpan = document.getElementById('error_message');
        const retryButton = document.getElementById('retry_button');
        const loadingOverlay = document.getElementById('loading_overlay');

        const showError = (message, canRetry = false) => {
            errorMessageSpan.textContent = message;
            errorContainer.classList.remove('hidden');
            retryButton.classList.toggle('hidden', !canRetry);
            if(canRetry) {
                retryButton.onclick = () => {
                    errorContainer.classList.add('hidden');
                    renderPaymentBrick();
                };
            }
        };

        const renderPaymentBrick = async () => {
            document.getElementById('paymentBrick_container').innerHTML = '<p class="text-center text-gray-500 p-8">Carregando formulário...</p>';

            if (paymentBrickController) {
                paymentBrickController.unmount();
            }

            try {
                const settings = {
                    initialization: { amount: {{ $service->price_in_cents / 100 }} },
                    customization: { visual: { style: { theme: 'default' } } },
                    callbacks: {
                        onReady: () => console.log('Brick está pronto.'),
                        onError: (error) => showError('Erro ao carregar o pagamento. Tente novamente.', true),
                        onSubmit: async ({ selectedPaymentMethod, formData }) => {
                            loadingOverlay.classList.remove('hidden');
                            errorContainer.classList.add('hidden');
                            
                            try {
                                const response = await fetch("{{ route('payment.process') }}", {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                                    body: JSON.stringify({ formData, service_id: {{ $service->id }} })
                                });

                                const data = await response.json();
                                loadingOverlay.classList.add('hidden');
                                
                                if (!response.ok) {
                                    showError(data.message || 'Pagamento recusado.', data.code === 'token_expired');
                                    throw new Error(data.message);
                                }
                                
                                window.location.href = `/checkout/status?status=${data.status}&payment_id=${data.payment_id}`;

                            } catch (error) {
                                loadingOverlay.classList.add('hidden');
                                console.error('Falha ao processar:', error);
                                if (!errorContainer.classList.contains('hidden')) return Promise.reject();
                                showError('Erro de conexão. Tente novamente.', true);
                                return Promise.reject();
                            }
                        },
                    }
                };
                paymentBrickController = await mp.bricks().create('payment', 'paymentBrick_container', settings);
            } catch (e) {
                showError('Erro fatal ao carregar o pagamento. Tente novamente.', true);
            }
        };

        document.addEventListener('DOMContentLoaded', renderPaymentBrick);
    </script>
</body>
</html>
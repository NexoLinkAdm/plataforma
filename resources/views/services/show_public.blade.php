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
        <div id="payment_error_container" class="hidden mt-4 p-3 bg-red-100 text-red-700 rounded-md text-sm"></div>
    </div>

    <script>
        const mp = new MercadoPago('{{ $publicKey }}', { locale: 'pt-BR' });
        const renderPaymentBrick = async (bricksBuilder) => {
            const settings = {
                initialization: { amount: {{ $service->price_in_cents / 100 }} },
                customization: {
                    paymentMethods: {
                        creditCard: 'all',
                        debitCard: 'all',
                        ticket: 'all',
                        mercadoPago: 'all'
                    }
                },
                callbacks: {
                    onReady: () => console.log('Brick está pronto.'),
                    onError: (error) => {
                        console.error('Erro no Brick:', error);
                        const errorContainer = document.getElementById('payment_error_container');
                        errorContainer.innerText = 'Ocorreu um erro ao carregar. Recarregue a página.';
                        errorContainer.classList.remove('hidden');
                    },
                    onSubmit: async ({ selectedPaymentMethod, formData }) => {
                        try {
                            const response = await fetch("{{ route('payment.process') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({ formData, service_id: {{ $service->id }} })
                            });
                            const data = await response.json();
                            if (!response.ok) {
                                const errorContainer = document.getElementById('payment_error_container');
                                errorContainer.innerText = data.message || 'Pagamento recusado.';
                                errorContainer.classList.remove('hidden');
                                throw new Error(data.message);
                            }
                            window.location.href = `/checkout/status?status=${data.status}&payment_id=${data.payment_id}`;
                        } catch (error) {
                            console.error('Falha ao processar:', error);
                            return Promise.reject();
                        }
                    },
                }
            };
            window.paymentBrickController = await bricksBuilder.create('payment', 'paymentBrick_container', settings);
        };
        renderPaymentBrick(mp.bricks());
    </script>
</body>
</html>
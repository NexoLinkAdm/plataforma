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
        <div id="payment_error_container" class="hidden mt-4 p-3 bg-red-100 text-red-700 rounded-md text-sm"></div>
    </div>
    <div id="loading_overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg text-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div><p>Processando...</p></div>
    </div>
    <script>
        const mp = new MercadoPago('{{ $publicKey }}', { locale: 'pt-BR' });

        async function renderPaymentBrick() {
            const bricksBuilder = mp.bricks();

            // --- INÍCIO DA CORREÇÃO DEFINITIVA ---
            // A configuração agora é a mais explícita possível, definindo
            // os métodos de pagamento diretamente na inicialização.
            const settings = {
                initialization: {
                    amount: {{ $service->price_in_cents / 100 }},
                },
                customization: {
                    paymentMethods: {
                        creditCard: 'all',
                        debitCard: 'all',
                        ticket: 'all',      // Habilita Boleto
                        bankTransfer: 'all', // Habilita Pix
                        mercadoPago: 'all', // Habilita Saldo em Conta e Cartões Salvos
                    }
                },
                // --- FIM DA CORREÇÃO DEFINITIVA ---
                callbacks: {
                    onReady: () => {
                        console.log('Brick está pronto.');
                        document.getElementById('payment_error_container').classList.add('hidden');
                    },
                    onError: (error) => {
                        console.error('Erro na renderização ou operação do Brick:', error);
                        document.getElementById('payment_error_container').textContent = 'Erro ao carregar o pagamento. Tente recarregar a página.';
                        document.getElementById('payment_error_container').classList.remove('hidden');
                    },
                    onSubmit: async ({ selectedPaymentMethod, formData }) => {
                        document.getElementById('loading_overlay').classList.remove('hidden');

                        try {
                            const response = await fetch("{{ route('payment.process') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    formData: formData,
                                    service_id: {{ $service->id }}
                                })
                            });

                            const data = await response.json();
                            document.getElementById('loading_overlay').classList.add('hidden');

                            if (!response.ok) {
                                document.getElementById('payment_error_container').textContent = data.message || 'Pagamento recusado. Verifique os dados.';
                                document.getElementById('payment_error_container').classList.remove('hidden');
                                throw new Error(data.message);
                            }
                            
                            window.location.href = `/checkout/status?status=${data.status}&payment_id=${data.payment_id}`;

                        } catch (error) {
                            document.getElementById('loading_overlay').classList.add('hidden');
                            console.error('Falha no processamento:', error);
                            return Promise.reject();
                        }
                    },
                }
            };

            // Remove a complexidade do retry. Apenas renderiza uma vez.
            // Se falhar, o usuário deve recarregar a página para um novo fluxo limpo.
            window.paymentBrickController = await bricksBuilder.create('payment', 'paymentBrick_container', settings);
        }

        document.addEventListener('DOMContentLoaded', renderPaymentBrick);
    </script>
</body>
</html>
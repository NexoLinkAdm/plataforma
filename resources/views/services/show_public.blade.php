<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar: {{ $service->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://sdk.mercadopago.com/js/v2"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-xl bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-8">
            <header class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Finalize seu Pagamento</h1>
                <p class="text-gray-500 mt-1">Plataforma Segura via Mercado Pago</p>
            </header>

            {{-- Resumo do Pedido --}}
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mb-8 border border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="font-semibold text-gray-800 dark:text-gray-200">{{ $service->title }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">por {{ $service->user->name }}</p>
                    </div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">
                        R$ {{ number_format($service->price_in_cents / 100, 2, ',', '.') }}
                    </div>
                </div>
            </div>

            {{-- Container onde o Brick será renderizado --}}
            <div id="paymentBrick_container"></div>
            {{-- Mensagem de erro para o usuário --}}
            <div id="payment_error_container" class="text-red-500 text-center text-sm mt-4"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            // As chaves são passadas do backend (controller) para a view
            const publicKey = '{{ $publicKey ?? '' }}';
            const preferenceId = '{{ $preferenceId ?? '' }}';

            if (!publicKey || !preferenceId) {
                document.getElementById('paymentBrick_container').innerText = 'Erro: Não foi possível carregar as informações de pagamento. Tente novamente.';
                return;
            }

            const mp = new MercadoPago(publicKey, { locale: 'pt-BR' });
            const bricksBuilder = mp.bricks();

            // Configurações do Brick
            const settings = {
                initialization: {
                    amount: {{ $service->price_in_cents / 100 }}, // Valor total a ser pago
                    preferenceId: preferenceId,
                },
                customization: {
                    visual: {
                        brand: "{{ config('mercadopago.app_name', 'Sua Plataforma') }}", // Nome da sua plataforma
                        style: {
                            theme: 'default', // 'default', 'dark', 'bootstrap'
                        }
                    },
                },
                callbacks: {
                    onReady: () => {
                        /*
                         * Callback chamado quando o Brick estiver pronto.
                         * Ex: Habilitar o botão de pagamento
                         */
                        console.log('Brick está pronto.');
                    },
                    onSubmit: ({ selectedPaymentMethod, formData }) => {
                        /*
                         * Callback chamado quando o usuário clica no botão de pagar.
                         * O Brick cuida do envio dos dados do formulário para o Mercado Pago.
                         * A Promise vazia indica ao Brick para seguir com seu fluxo padrão.
                         */
                        console.log('Formulário enviado.');
                        return new Promise(() => {});
                    },
                    onError: (error) => {
                        /*
                         * Callback chamado para todos os erros que ocorrem no Brick.
                         * Ex: Cartão recusado, dados inválidos, etc.
                         */
                        console.error('Erro no Brick:', error);
                        const errorContainer = document.getElementById('payment_error_container');
                        if (error.message) {
                            errorContainer.innerText = error.message;
                        } else {
                            errorContainer.innerText = 'Ocorreu um erro ao processar o pagamento. Verifique os dados e tente novamente.';
                        }
                    },
                },
            };

            // `renderPaymentBrick` é uma função async, então usamos `await`
            try {
                window.paymentBrickController = await bricksBuilder.create('payment', 'paymentBrick_container', settings);
                console.log('Brick renderizado com sucesso.');
            } catch (error) {
                console.error('Erro fatal ao renderizar o Brick:', error);
                document.getElementById('paymentBrick_container').innerText = 'Não foi possível carregar o formulário de pagamento. Por favor, recarregue a página.';
            }
        });
    </script>
</body>
</html>
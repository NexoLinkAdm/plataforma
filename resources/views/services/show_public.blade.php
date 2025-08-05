<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar: {{ $service->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- SDK do Mercado Pago --}}
    <script src="https://sdk.mercadopago.com/js/v2"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-2xl bg-white rounded-lg shadow-md">

        <div class="p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">
                {{ $service->title }}
            </h1>
            <p class="text-gray-600 mb-6">Oferecido por: <span class="font-semibold">{{ $service->user->name }}</span></p>

            <div class="border-t border-b py-6 my-6">
                <p class="mt-4 text-gray-700 text-left whitespace-pre-wrap">{{ $service->description }}</p>
                <ul class="text-sm text-gray-600 mt-4 space-y-2">
                    <li><strong>Prazo de Entrega:</strong> {{ $service->delivery_time_days }} dias</li>
                    <li><strong>Revisões Inclusas:</strong> {{ $service->revisions_limit }}</li>
                </ul>
                <div class="mt-4 text-4xl font-bold text-blue-600">
                    R$ {{ number_format($service->price_in_cents / 100, 2, ',', '.') }}
                </div>
            </div>
        </div>

        {{-- O Checkout Brick será renderizado aqui --}}
        <div class="px-8 pb-8">
            <div id="paymentBrick_container"></div>
        </div>

    </div>

    <script>
        // Inicializa o SDK do Mercado Pago com a Public Key da plataforma
        const mp = new MercadoPago('{{ $publicKey }}', {
            locale: 'pt-BR'
        });
        const bricksBuilder = mp.bricks();

        // Configurações do Brick de Pagamento
        const settings = {
            initialization: {
                amount: {{ $service->price_in_cents / 100 }}, // O valor total da compra
                preferenceId: '{{ $preferenceId }}', // O ID da preferência gerado no backend
            },
            customization: {
                visual: {
                    style: {
                        theme: 'default', // pode ser 'dark', 'bootstrap' ou 'default'
                    }
                },
                paymentMethods: {
                    maxInstallments: 12,
                }
            },
            callbacks: {
                onReady: () => {
                    // Callback quando o Brick estiver pronto
                },
                onSubmit: ({ selectedPaymentMethod, formData }) => {
                    // O Brick cuida do envio, não precisamos de um fetch aqui.
                    // A Promise vazia indica ao Brick para seguir seu fluxo padrão.
                    return new Promise(() => {});
                },
                onError: (error) => {
                    console.error('Erro no checkout:', error);
                },
            },
        };

        // Renderiza o Brick
        bricksBuilder.create('payment', 'paymentBrick_container', settings)
            .then(() => console.log('Payment Brick renderizado com sucesso.'))
            .catch(error => console.error('Erro ao renderizar Payment Brick:', error));
    </script>
</body>
</html>
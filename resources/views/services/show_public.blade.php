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
        const publicKey = '{{ $publicKey ?? '' }}';
        const preferenceId = '{{ $preferenceId ?? '' }}';

        if (!publicKey || !preferenceId) {
            document.getElementById('paymentBrick_container').innerText = 'Erro: Falha ao carregar informações de pagamento (ID: P01).';
            return;
        }

        const mp = new MercadoPago(publicKey, { locale: 'pt-BR' });

        try {
            await mp.bricks().create('payment', 'paymentBrick_container', {
                initialization: {
                    // --- INÍCIO DA CORREÇÃO ---
                    // A API do Brick exige AMBOS os parâmetros:
                    // 1. O valor total da transação.
                    amount: {{ $service->price_in_cents / 100 }},
                    // 2. O ID da preferência que contém os detalhes (split, etc).
                    preferenceId: preferenceId,
                    // --- FIM DA CORREÇÃO ---
                },
                customization: {
                    visual: {
                        brand: "{{ config('mercadopago.app_name', 'Sua Plataforma') }}",
                    }
                },
                callbacks: {
                    onError: (error) => {
                        console.error('Erro no Brick:', error);
                        const errorContainer = document.getElementById('payment_error_container');
                        errorContainer.innerText = 'Ocorreu um erro. Verifique os dados e tente novamente.';
                    },
                    onReady: () => console.log('Brick pronto.'),
                    onSubmit: () => console.log('Formulário enviado.'),
                },
            });
            console.log('Brick renderizado com sucesso.');
        } catch (e) {
            console.error('Erro fatal ao renderizar o Brick:', e);
            document.getElementById('paymentBrick_container').innerText = 'Erro fatal ao carregar o formulário de pagamento (ID: P02).';
        }
    });
</script>
</body>
</html>
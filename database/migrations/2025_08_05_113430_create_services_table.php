<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            // Relacionamento com a criadora (User)
            $table->foreignId('user_id')
                  ->comment('ID da criadora (usuária) a qual o serviço pertence')
                  ->constrained() // Adiciona a chave estrangeira para a tabela 'users'
                  ->cascadeOnDelete(); // Se a criadora for deletada, seus serviços também serão

            // Detalhes do Serviço
            $table->string('title', 100)->comment('Título principal do serviço. Ex: "3 Vídeos UGC para TikTok"');
            $table->string('slug')->unique()->comment('URL amigável gerada a partir do título');
            $table->text('description')->comment('Descrição detalhada do que está incluso no serviço');

            // Atributos Comerciais
            $table->unsignedInteger('price_in_cents')->comment('Preço em centavos para evitar problemas de precisão com floats');
            $table->unsignedInteger('delivery_time_days')->comment('Prazo de entrega do serviço em dias');
            $table->unsignedTinyInteger('revisions_limit')->default(1)->comment('Número de revisões que o cliente pode solicitar');

            // Controle de Status e Timestamps
            $table->boolean('is_active')->default(true)->comment('Controla se o serviço está visível e disponível para compra');
            $table->timestamps();

            // --- OTIMIZAÇÕES DE PERFORMANCE ---
            // Adiciona índices nas colunas que serão frequentemente usadas em cláusulas WHERE.
            // Isso acelera drasticamente as consultas de busca e filtro.
            $table->index('user_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
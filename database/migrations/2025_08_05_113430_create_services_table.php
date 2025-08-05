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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Link com a criadora
            $table->string('title', 100); // Título do serviço. Ex: "3 Vídeos UGC para TikTok"
            $table->string('slug')->unique(); // URL amigável. Ex: "3-videos-ugc-para-tiktok"
            $table->text('description'); // Descrição detalhada do que está incluso
            $table->unsignedInteger('price_in_cents'); // Preço em centavos para evitar problemas com float
            $table->unsignedInteger('delivery_time_days'); // Prazo de entrega em dias
            $table->unsignedTinyInteger('revisions_limit')->default(1); // Quantidade de revisões inclusas
            $table->boolean('is_active')->default(true); // Permite que a criadora ative/desative o serviço
            $table->timestamps();
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
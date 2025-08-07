<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('mp_payment_id')->unique()->comment('ID do pagamento no Mercado Pago');
            
            $table->foreignId('service_id')->constrained()->comment('Serviço que foi vendido');
            $table->foreignId('user_id')->constrained()->comment('Criadora que recebeu o pagamento');
            
            $table->string('status')->index()->comment('Status do pagamento: pending, approved, etc.');
            $table->string('buyer_email')->nullable()->comment('E-mail do cliente');
            $table->unsignedInteger('amount_cents')->comment('Valor total da transação em centavos');

            $table->text('metadata')->nullable()->comment('JSON com detalhes da resposta da API');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
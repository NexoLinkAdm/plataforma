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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('mp_user_id')->nullable()->after('id')->comment('ID do usuário no Mercado Pago');
            $table->text('mp_access_token')->nullable()->after('password')->comment('Token de acesso para operar em nome do usuário');
            $table->text('mp_refresh_token')->nullable()->after('mp_access_token')->comment('Token para renovar o acesso');
            $table->timestamp('mp_token_expires_at')->nullable()->after('mp_refresh_token')->comment('Data de expiração do token de acesso');
            $table->timestamp('mp_connected_at')->nullable()->after('mp_token_expires_at')->comment('Data em que a conta foi conectada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mp_user_id',
                'mp_access_token',
                'mp_refresh_token',
                'mp_token_expires_at',
                'mp_connected_at',
            ]);
        });
    }
};
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
        Schema::table('vendas_acessorios', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->after('observacoes') // Ou a posição desejada
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null') // Ou 'restrict'
                  ->comment('Usuário do sistema que registrou a venda');
        });
    }
    
    public function down(): void
    {
        Schema::table('vendas_acessorios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
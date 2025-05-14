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
        Schema::table('estoque', function (Blueprint $table) {
            // 1. Remove o índice único existente na coluna 'nome'
            // O nome padrão do índice é 'nome_da_tabela_nome_da_coluna_unique'
            $table->dropUnique(['nome']);

            // 2. Adiciona um novo índice único composto
            // A combinação de 'nome' e 'modelo_compativel' deve ser única
            $table->unique(['nome', 'modelo_compativel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estoque', function (Blueprint $table) {
            // 1. Remove o índice único composto ao reverter
            $table->dropUnique(['nome', 'modelo_compativel']);

            // 2. Re-adiciona o índice único original na coluna 'nome'
            $table->unique('nome');
        });
    }
};
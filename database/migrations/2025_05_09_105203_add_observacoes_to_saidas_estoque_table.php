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
        Schema::table('saidas_estoque', function (Blueprint $table) {
            // Adiciona a nova coluna 'observacoes' como TEXT e permite valores NULL
            $table->text('observacoes')->nullable()->after('quantidade');
            // Use 'text' para um campo de texto mais longo, 'string' se for curto.
            // 'nullable()' porque o campo é opcional no seu formulário.
            // 'after('quantidade')' é opcional, define a posição da coluna.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saidas_estoque', function (Blueprint $table) {
            // Remove a coluna 'observacoes' se a migração for desfeita
            $table->dropColumn('observacoes');
        });
    }
};
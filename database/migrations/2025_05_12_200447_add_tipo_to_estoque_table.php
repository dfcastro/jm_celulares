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
            // Adiciona a nova coluna 'tipo'
            // Pode ser nullable() se alguns itens não tiverem um tipo definido imediatamente
            // after() define a posição da coluna na tabela (opcional, mas organiza)
            $table->string('tipo')->nullable()->after('numero_serie');
            // Opções de valores para o campo tipo: 'PECA_REPARO', 'ACESSORIO_VENDA', 'AMBOS'
            // Se preferir um conjunto fixo de opções, você pode usar enum:
            // $table->enum('tipo', ['PECA_REPARO', 'ACESSORIO_VENDA', 'GERAL'])->default('GERAL')->after('numero_serie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estoque', function (Blueprint $table) {
            // Remove a coluna 'tipo' se a migration for revertida
            $table->dropColumn('tipo');
        });
    }
};
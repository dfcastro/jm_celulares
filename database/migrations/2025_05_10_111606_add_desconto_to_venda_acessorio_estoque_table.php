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
        Schema::table('venda_acessorio_estoque', function (Blueprint $table) {
            // Adiciona a coluna 'desconto' do tipo decimal, com precisão de 10 dígitos e 2 casas decimais
            // Permite valores nulos e define um valor padrão de 0.00
            $table->decimal('desconto', 10, 2)->default(0.00)->after('preco_unitario_venda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venda_acessorio_estoque', function (Blueprint $table) {
            // Remove a coluna 'desconto' se a migração for revertida
            $table->dropColumn('desconto');
        });
    }
};
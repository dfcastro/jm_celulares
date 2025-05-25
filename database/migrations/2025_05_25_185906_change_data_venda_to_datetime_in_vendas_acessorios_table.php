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
            // Altera o tipo da coluna 'data_venda' para datetime
            // O método change() requer o pacote doctrine/dbal: execute `composer require doctrine/dbal` se ainda não tiver.
            $table->dateTime('data_venda')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendas_acessorios', function (Blueprint $table) {
            // Reverte o tipo da coluna para date se a migração for desfeita
            // CUIDADO: Isso pode causar perda de dados da hora se você reverter após ter dados com hora.
            $table->date('data_venda')->change();
        });
    }
};
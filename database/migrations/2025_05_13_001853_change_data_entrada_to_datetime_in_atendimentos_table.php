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
        Schema::table('atendimentos', function (Blueprint $table) {
            // Altera a coluna data_entrada para o tipo DATETIME
            // O método change() requer o pacote doctrine/dbal: composer require doctrine/dbal
            $table->dateTime('data_entrada')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            // Reverte para DATE se a migração for desfeita
            // CUIDADO: Isso pode causar perda de dados da hora se você reverter após ter dados com hora.
            $table->date('data_entrada')->change();
        });
    }
};
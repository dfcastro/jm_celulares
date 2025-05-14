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
            // Altera o tipo da coluna 'data_saida' para datetime
            $table->dateTime('data_saida')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saidas_estoque', function (Blueprint $table) {
            // Reverte o tipo da coluna para date se a migração for desfeita
            $table->date('data_saida')->change();
        });
    }
};
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
        Schema::table('entradas_estoque', function (Blueprint $table) {
            // Altera o tipo da coluna 'data_entrada' para datetime
            $table->dateTime('data_entrada')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entradas_estoque', function (Blueprint $table) {
            // Reverte o tipo da coluna para date se a migração for desfeita
            $table->date('data_entrada')->change();
        });
    }
};
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
            // Adiciona a nova coluna 'numero_serie' como string e permite valores NULL (opcional)
            // Definimos um comprimento razoável para o número de série
            $table->string('numero_serie', 100)->nullable()->after('modelo_compativel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estoque', function (Blueprint $table) {
            // Remove a coluna 'numero_serie' se a migração for desfeita
            $table->dropColumn('numero_serie');
        });
    }
};

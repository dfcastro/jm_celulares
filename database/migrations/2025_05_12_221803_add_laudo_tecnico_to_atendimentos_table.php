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
            // Adiciona a nova coluna 'laudo_tecnico'
            // TEXT permite armazenar textos mais longos
            // nullable() torna o campo opcional
            // after('observacoes') posiciona a coluna (opcional)
            $table->text('laudo_tecnico')->nullable()->after('observacoes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            // Remove a coluna 'laudo_tecnico' se a migration for revertida
            $table->dropColumn('laudo_tecnico');
        });
    }
};
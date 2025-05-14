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
            // Renomeia a coluna 'celular' para 'descricao_aparelho'
            // Certifique-se que o tipo da coluna original 'celular' era string e compatível
            // Se a coluna 'celular' armazenava números de telefone, você pode querer manter ela
            // e adicionar uma NOVA coluna 'descricao_aparelho'.
            // Assumindo que 'celular' já era para a descrição do aparelho:
            $table->renameColumn('celular', 'descricao_aparelho');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            // Reverte o nome da coluna se a migração for desfeita
            $table->renameColumn('descricao_aparelho', 'celular');
        });
    }
};
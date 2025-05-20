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
        Schema::create('movimentacoes_caixa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caixa_id')->constrained('caixas')->onDelete('cascade')->comment('Sessão de caixa à qual a movimentação pertence');
            $table->foreignId('usuario_id')->constrained('users')->comment('Usuário que registrou a movimentação');
            $table->enum('tipo', ['ENTRADA', 'SAIDA'])->comment('Tipo da movimentação');
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->string('forma_pagamento')->nullable()->comment('Dinheiro, Cartão Crédito, Cartão Débito, PIX, etc.');
            // Para referenciar a Venda, OS, etc. que originou a movimentação (opcional, mas útil)
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->string('referencia_tipo')->nullable(); // Ex: App\Models\VendaAcessorio
            $table->timestamp('data_movimentacao')->useCurrent();
            $table->text('observacoes')->nullable();
            $table->timestamps();

            // Índice para as colunas de referência polimórfica (se usadas)
            $table->index(['referencia_id', 'referencia_tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimentacoes_caixa');
    }
};
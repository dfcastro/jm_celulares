<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venda_acessorio_estoque', function (Blueprint $table) {
            $table->foreignId('venda_acessorio_id')->constrained('vendas_acessorios')->onDelete('cascade');
            $table->foreignId('estoque_id')->constrained('estoque')->onDelete('cascade');
            $table->integer('quantidade');
            $table->decimal('preco_unitario_venda', 10, 2)->nullable();
            $table->primary(['venda_acessorio_id', 'estoque_id']); // Chave primária composta
            // Sem timestamps aqui
        });
    }
    public function down(): void { Schema::dropIfExists('venda_acessorio_estoque'); }
};
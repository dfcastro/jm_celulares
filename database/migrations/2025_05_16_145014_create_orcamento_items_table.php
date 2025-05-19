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
        Schema::create('orcamento_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained('orcamentos')->onDelete('cascade'); // Se o orçamento for excluído, seus itens também são.

            $table->enum('tipo_item', ['peca', 'servico']);
            $table->foreignId('estoque_id')->nullable()->constrained('estoque')->onDelete('set null'); // Se for uma peça do estoque. 'set null' se a peça for removida do estoque.
            $table->string('descricao_item_manual')->nullable(); // Para descrever um serviço ou uma peça não catalogada.

            $table->integer('quantidade');
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('subtotal_item', 10, 2);
            // Não precisa de timestamps aqui, a menos que você queira rastrear quando cada item foi adicionado/modificado.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orcamento_items');
    }
};
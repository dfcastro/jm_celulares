<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atendimento_servicos', function (Blueprint $table) {
            $table->id(); // Chave primária auto-incremental
            $table->foreignId('atendimento_id')
                  ->constrained('atendimentos') // Define a chave estrangeira para a tabela 'atendimentos'
                  ->onDelete('cascade'); // Se um atendimento for excluído, seus serviços detalhados também serão
            $table->string('descricao_servico'); // Descrição do serviço realizado
            $table->integer('quantidade')->default(1); // Quantidade deste serviço (geralmente 1)
            $table->decimal('valor_unitario', 10, 2); // Valor por unidade do serviço
            $table->decimal('subtotal_servico', 10, 2); // Calculado: quantidade * valor_unitario
            // $table->timestamps(); // Opcional: se quiser rastrear created_at e updated_at para cada item de serviço
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atendimento_servicos');
    }
};
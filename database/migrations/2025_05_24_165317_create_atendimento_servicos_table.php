<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('atendimento_servicos')) { // <--- ADICIONA ESTA VERIFICAÇÃO
            Schema::create('atendimento_servicos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('atendimento_id')
                      ->constrained('atendimentos')
                      ->onDelete('cascade');
                $table->string('descricao_servico');
                $table->integer('quantidade')->default(1);
                $table->decimal('valor_unitario', 10, 2);
                $table->decimal('subtotal_servico', 10, 2);
                // $table->timestamps(); // Descomente se você adicionou timestamps
            });
        }
    }

    public function down(): void
    {
        // Opcional: Adicionar uma verificação aqui também, embora menos crítico
        // if (Schema::hasTable('atendimento_servicos')) {
        //     Schema::dropIfExists('atendimento_servicos');
        // }
        Schema::dropIfExists('atendimento_servicos');
    }
};
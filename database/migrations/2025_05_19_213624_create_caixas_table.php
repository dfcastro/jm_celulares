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
        Schema::create('caixas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_abertura_id')->constrained('users')->comment('Usuário que abriu o caixa');
            $table->foreignId('usuario_fechamento_id')->nullable()->constrained('users')->comment('Usuário que fechou o caixa');
            $table->timestamp('data_abertura')->comment('Data e hora da abertura do caixa');
            $table->timestamp('data_fechamento')->nullable()->comment('Data e hora do fechamento do caixa');
            $table->decimal('saldo_inicial', 10, 2)->comment('Valor com que o caixa foi aberto');
            $table->decimal('saldo_final_calculado', 10, 2)->nullable()->comment('Saldo calculado pelo sistema no fechamento (entradas - saídas + inicial)');
            $table->decimal('saldo_final_informado', 10, 2)->nullable()->comment('Saldo real contado pelo usuário no fechamento');
            $table->decimal('diferenca', 10, 2)->nullable()->comment('Diferença entre saldo calculado e informado');
            $table->enum('status', ['Aberto', 'Fechado'])->default('Aberto')->comment('Status atual do caixa');
            $table->text('observacoes_abertura')->nullable();
            $table->text('observacoes_fechamento')->nullable();
            $table->timestamps(); // created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caixas');
    }
};
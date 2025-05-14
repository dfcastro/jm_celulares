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
            // Valor cobrado pela mão de obra/serviços (sem incluir peças)
            $table->decimal('valor_servico', 10, 2)->nullable()->default(0.00)->after('laudo_tecnico');
            // Desconto aplicado sobre o valor do serviço
            $table->decimal('desconto_servico', 10, 2)->nullable()->default(0.00)->after('valor_servico');
            // Poderíamos ter um valor_total_pago aqui também se quisesse controlar pagamentos parciais/totais do serviço
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            $table->dropColumn(['valor_servico', 'desconto_servico']);
        });
    }
};
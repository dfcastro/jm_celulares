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
            // Adiciona a coluna 'forma_pagamento' após 'desconto_servico' (ou onde preferir)
            // Tornamos nullable porque um atendimento pode não ter sido pago ainda,
            // ou a forma de pagamento pode ser registrada apenas no momento do pagamento.
            $table->string('forma_pagamento')->nullable()->after('desconto_servico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            $table->dropColumn('forma_pagamento');
        });
    }
};
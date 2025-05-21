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
            $table->string('status_pagamento')->default('Pendente')->after('status'); // Ou o local que preferir
            // Definindo alguns status de pagamento comuns. Ajuste conforme sua necessidade.
            // Ex: Pendente, Pago, Parcialmente Pago, Cancelado, Não Aplicável (para garantias, por exemplo)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            $table->dropColumn('status_pagamento');
        });
    }
};
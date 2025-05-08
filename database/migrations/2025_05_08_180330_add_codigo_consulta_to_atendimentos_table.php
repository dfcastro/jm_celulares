<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            $table->string('codigo_consulta')->unique()->nullable()->after('observacoes');
            // 'unique()' garante que cada código de consulta seja único
            // 'nullable()' permite que o código seja gerado posteriormente, se necessário
            // 'after('observacoes')' coloca a coluna após 'observacoes'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            $table->dropColumn('codigo_consulta');
        });
    }
};
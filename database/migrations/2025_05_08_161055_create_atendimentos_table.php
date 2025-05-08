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
        Schema::create('atendimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('celular');
            $table->text('problema_relatado');
            $table->date('data_entrada');
            $table->string('status')->default('Em diagnóstico');
            $table->foreignId('tecnico_id')->nullable()->constrained('users'); // Adicionando a chave estrangeira para o técnico
            $table->date('data_conclusao')->nullable();
            $table->text('observacoes')->nullable();
            $table->string('codigo_consulta')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('atendimentos');
    }
};
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
        Schema::create('saidas_estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estoque_id')->constrained('estoque');
            $table->foreignId('atendimento_id')->nullable()->constrained('atendimentos');
            $table->date('data_saida');
            $table->integer('quantidade');
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
        Schema::dropIfExists('saidas_estoque');
    }
};
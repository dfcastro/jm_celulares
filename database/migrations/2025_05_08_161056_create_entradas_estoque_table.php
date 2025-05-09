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
        Schema::create('entradas_estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estoque_id')->constrained('estoque');
            $table->date('data_entrada');
            $table->integer('quantidade');
            $table->decimal('valor_unitario')->nullable();
            $table->string('observacoes')->nullable();
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
        Schema::dropIfExists('entradas_estoque');
    }
};
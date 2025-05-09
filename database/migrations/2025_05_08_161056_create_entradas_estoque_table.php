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
    // database/migrations/2025_05_08_161056_create_entradas_estoque_table.php

public function up()
{
    Schema::create('entradas_estoque', function (Blueprint $table) {
        $table->id();
        $table->foreignId('estoque_id')->constrained('estoque');
        $table->date('data_entrada');
        // Adicionando ->default(0) para garantir um valor inicial não nulo
        $table->integer('quantidade')->default(0);
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
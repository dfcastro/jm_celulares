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
        Schema::table('estoque', function (Blueprint $table) {
            $table->string('modelo_compativel')->nullable()->after('nome');
            // 'nullable()' permite que este campo fique vazio
            // 'after('nome')' indica que a coluna será adicionada após a coluna 'nome'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('estoque', function (Blueprint $table) {
            $table->dropColumn('modelo_compativel');
        });
    }
};
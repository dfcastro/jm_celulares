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
    Schema::table('estoque', function (Blueprint $table) {
        // Adiciona a coluna 'marca' depois da coluna 'tipo' (ou onde preferir)
        $table->string('marca')->nullable()->after('tipo');
    });
}

public function down(): void
{
    Schema::table('estoque', function (Blueprint $table) {
        $table->dropColumn('marca');
    });
}
};

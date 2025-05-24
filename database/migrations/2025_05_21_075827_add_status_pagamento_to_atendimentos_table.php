// database/migrations/2025_05_21_075827_add_status_pagamento_to_atendimentos_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Schema::table('atendimentos', function (Blueprint $table) {
        //     $table->string('status_pagamento')->default('Pendente')->after('status');
        // });
    }

    public function down(): void
    {
        // Schema::table('atendimentos', function (Blueprint $table) {
        //     $table->dropColumn('status_pagamento');
        // });
    }
};
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
        Schema::table('clientes', function (Blueprint $table) {
            // Adiciona as novas colunas de endereço
            $table->string('cep', 10)->nullable()->after('endereco'); // CEP com máscara, 10 caracteres
            $table->string('logradouro')->nullable()->after('cep'); // Rua/Avenida
            $table->string('numero', 10)->nullable()->after('logradouro'); // Número do imóvel
            $table->string('complemento')->nullable()->after('numero'); // Complemento (apartamento, bloco, etc.)
            $table->string('bairro')->nullable()->after('complemento'); // Bairro
            $table->string('cidade')->nullable()->after('bairro'); // Cidade
            $table->string('estado', 2)->nullable()->after('cidade'); // Estado (sigla, ex: SP, MG)

            // Opcional: Remover a coluna 'endereco' antiga se não for mais usada
            // CUIDADO: Isso apagará os dados existentes no campo 'endereco'.
            // $table->dropColumn('endereco');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Remove as novas colunas se a migração for revertida
            $table->dropColumn('cep');
            $table->dropColumn('logradouro');
            $table->dropColumn('numero');
            $table->dropColumn('complemento');
            $table->dropColumn('bairro');
            $table->dropColumn('cidade');
            $table->dropColumn('estado');

            // Opcional: Re-adicionar a coluna 'endereco' antiga se ela foi removida no up()
            // $table->string('endereco')->nullable()->after('cpf_cnpj');
        });
    }
};
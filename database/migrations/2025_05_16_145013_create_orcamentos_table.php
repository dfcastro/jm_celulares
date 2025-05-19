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
        Schema::create('orcamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null'); // Cliente pode ser nulo inicialmente ou se o cliente for excluído
            $table->string('nome_cliente_avulso')->nullable();
            $table->string('telefone_cliente_avulso')->nullable();
            $table->string('email_cliente_avulso')->nullable();

            $table->string('descricao_aparelho');
            $table->text('problema_relatado_cliente');

            $table->string('status')->default('Em Elaboração'); // Ex: Em Elaboração, Aguardando Aprovação, Aprovado, Reprovado, Cancelado, Convertido em OS
            $table->date('data_emissao');
            $table->date('data_validade')->nullable();
            $table->integer('validade_dias')->nullable(); // Alternativa ou complemento à data_validade

            $table->decimal('valor_total_servicos', 10, 2)->default(0.00);
            $table->decimal('valor_total_pecas', 10, 2)->default(0.00);
            $table->decimal('sub_total', 10, 2)->default(0.00); // Soma de serviços e peças
            $table->enum('desconto_tipo', ['percentual', 'fixo'])->nullable();
            $table->decimal('desconto_valor', 10, 2)->nullable();
            $table->decimal('valor_final', 10, 2)->default(0.00);

            $table->string('tempo_estimado_servico')->nullable();
            $table->text('observacoes_internas')->nullable(); // Observações para a equipe
            $table->text('termos_condicoes')->nullable();    // Termos visíveis ao cliente

            $table->foreignId('criado_por_id')->nullable()->constrained('users')->onDelete('set null'); // Usuário do sistema que criou
            $table->foreignId('aprovado_por_id')->nullable()->constrained('users')->onDelete('set null'); // Usuário que registrou a aprovação
            $table->timestamp('data_aprovacao')->nullable();
            $table->timestamp('data_reprovacao')->nullable();
            $table->timestamp('data_cancelamento')->nullable(); // Adicionado para registrar cancelamento

            $table->foreignId('atendimento_id_convertido')->nullable()->constrained('atendimentos')->onDelete('set null'); // ID do atendimento gerado

            $table->timestamps(); // created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orcamentos');
    }
};
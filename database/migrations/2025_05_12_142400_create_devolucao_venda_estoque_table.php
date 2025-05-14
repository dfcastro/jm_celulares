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
            Schema::create('devolucao_venda_estoque', function (Blueprint $table) {
                $table->foreignId('devolucao_venda_id')->constrained('devolucoes_vendas')->onDelete('cascade'); // Removido ->index()
                $table->foreignId('estoque_id')->constrained('estoque')->onDelete('cascade'); // Removido ->index()
                // Quantidade devolvida deste item específico nesta devolução
                $table->integer('quantidade_devolvida');
                // Valor pelo qual este item foi devolvido (pode ser o preço unitário original)
                $table->decimal('valor_unitario_devolvido', 10, 2)->nullable();

                // Define uma chave primária composta para garantir unicidade de item por devolução
                $table->primary(['devolucao_venda_id', 'estoque_id']);

                // Não precisamos de timestamps para esta tabela pivô, pois eles já estão na tabela `devolucoes_vendas`.
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('devolucao_venda_estoque');
        }
    };

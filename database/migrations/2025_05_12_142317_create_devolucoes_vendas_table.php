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
        Schema::create('devolucoes_vendas', function (Blueprint $table) {
            $table->id();
            // Chave estrangeira para a venda original
            $table->foreignId('venda_acessorio_id')->constrained('vendas_acessorios')->onDelete('cascade');
            // Valor total da devolução (pode ser parcial)
            $table->decimal('valor_devolvido', 10, 2)->default(0);
            // Data da devolução
            $table->timestamp('data_devolucao')->useCurrent(); // Usa timestamp para data e hora exatas
            // Observações sobre a devolução
            $table->text('observacoes')->nullable();
            $table->timestamps(); // created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devolucoes_vendas');
    }
};
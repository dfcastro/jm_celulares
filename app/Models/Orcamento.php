<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Orcamento extends Model
{
    use HasFactory;

    protected $table = 'orcamentos';

    protected $fillable = [
        'cliente_id',
        'nome_cliente_avulso',
        'telefone_cliente_avulso',
        'email_cliente_avulso',
        'descricao_aparelho',
        'problema_relatado_cliente',
        'status',
        'data_emissao',
        'data_validade',
        'validade_dias',
        'valor_total_servicos',
        'valor_total_pecas',
        'sub_total',
        'desconto_tipo',
        'desconto_valor',
        'valor_final',
        'tempo_estimado_servico',
        'observacoes_internas',
        'termos_condicoes',
        'criado_por_id',
        'aprovado_por_id',
        'data_aprovacao',
        'data_reprovacao',
        'data_cancelamento',
        'atendimento_id_convertido',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'data_validade' => 'date',
        'data_aprovacao' => 'datetime',
        'data_reprovacao' => 'datetime',
        'data_cancelamento' => 'datetime',
        'valor_total_servicos' => 'decimal:2',
        'valor_total_pecas' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'desconto_valor' => 'decimal:2',
        'valor_final' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }

    public function aprovadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por_id');
    }

    public function atendimentoConvertido(): BelongsTo
    {
        return $this->belongsTo(Atendimento::class, 'atendimento_id_convertido');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(OrcamentoItem::class);
    }

    // Método para calcular o valor final (exemplo, pode ser mais complexo)
    public function calcularValorFinal()
    {
        $subtotal = $this->valor_total_servicos + $this->valor_total_pecas;
        $this->sub_total = $subtotal;
        $valorDescontoCalculado = 0;

        if ($this->desconto_valor && $this->desconto_valor > 0) {
            if ($this->desconto_tipo === 'percentual') {
                $valorDescontoCalculado = ($subtotal * $this->desconto_valor) / 100;
            } elseif ($this->desconto_tipo === 'fixo') {
                $valorDescontoCalculado = $this->desconto_valor;
            }
        }
        // Garante que o desconto não seja maior que o subtotal
        $valorDescontoCalculado = min($valorDescontoCalculado, $subtotal);

        $this->valor_final = $subtotal - $valorDescontoCalculado;
        // Não salvamos aqui, apenas calculamos. O save será feito no controller.
    }

    public static function getPossibleStatuses(): array
    {
        return ['Em Elaboração', 'Aguardando Aprovação', 'Aprovado', 'Reprovado', 'Cancelado', 'Convertido em OS'];
    }
}
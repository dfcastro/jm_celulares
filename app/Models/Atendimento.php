<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Atendimento extends Model
{
    use HasFactory;
    protected $fillable = [
        'cliente_id',
        //'celular',
        'descricao_aparelho',
        'problema_relatado',
        'data_entrada',
        'status',
        'tecnico_id',
        'data_conclusao',
        'observacoes',
        'codigo_consulta',
        'laudo_tecnico',
        'valor_servico',    // <<<<<<<<<<<<<< ADICIONADO
        'desconto_servico', // <<<<<<<<<<<<<< ADICIONADO
    ];

    protected $casts = [
        'data_entrada' => 'datetime',
        'data_conclusao' => 'datetime',
        'valor_servico' => 'decimal:2',    // <<<<<<<<<<<<<< ADICIONADO
        'desconto_servico' => 'decimal:2', // <<<<<<<<<<<<<< ADICIONADO
    ];

    // ... seus relacionamentos (cliente, tecnico, saidasEstoque) ...
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function saidasEstoque(): HasMany
    {
        return $this->hasMany(SaidaEstoque::class);
    }
    public static function getPossibleStatuses(): array
    {
        return ['Em diagnóstico', 'Aguardando peça', 'Em manutenção', 'Pronto para entrega', 'Entregue', 'Cancelado', 'Reprovado'];
    }
}

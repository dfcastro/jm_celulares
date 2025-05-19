<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrcamentoItem extends Model
{
    use HasFactory;

    protected $table = 'orcamento_items';

    public $timestamps = false; // Não teremos created_at/updated_at nesta tabela

    protected $fillable = [
        'orcamento_id',
        'tipo_item',
        'estoque_id',
        'descricao_item_manual',
        'quantidade',
        'valor_unitario',
        'subtotal_item',
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'valor_unitario' => 'decimal:2',
        'subtotal_item' => 'decimal:2',
    ];

    public function orcamento(): BelongsTo
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function estoque(): BelongsTo // Se for uma peça
    {
        return $this->belongsTo(Estoque::class, 'estoque_id');
    }
}
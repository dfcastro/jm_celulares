<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon; // Importar Carbon

class MovimentacaoCaixa extends Model
{
    use HasFactory;

    protected $table = 'movimentacoes_caixa';

    protected $fillable = [
        'caixa_id',
        'usuario_id',
        'tipo',
        'descricao',
        'valor',
        'forma_pagamento',
        'referencia_id',
        'referencia_tipo',
        'data_movimentacao',
        'observacoes',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_movimentacao' => 'datetime',
    ];

    /**
     * Caixa ao qual esta movimentação pertence.
     */
    public function caixa(): BelongsTo
    {
        return $this->belongsTo(Caixa::class);
    }

    /**
     * Usuário que registrou a movimentação.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento polimórfico para a referência (Venda, OS, etc.).
     */
    public function referencia()
    {
        return $this->morphTo();
    }

    /**
     * Formata a data da movimentação.
     */
    public function getDataMovimentacaoFormatadaAttribute(): ?string
    {
        return $this->data_movimentacao ? Carbon::parse($this->data_movimentacao)->format('d/m/Y H:i:s') : null;
    }
}
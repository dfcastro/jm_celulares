<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class VendaAcessorio extends Model
{
    use HasFactory;

    protected $table = 'vendas_acessorios';

    protected $fillable = [
        'cliente_id',       // Para o cliente que compra
        'user_id',          // Para o usuário do sistema que registra a venda (NOVO AQUI)
        'data_venda',
        'valor_total',
        'forma_pagamento',
        'observacoes',
    ];

    protected $casts = [
        'data_venda' => 'datetime', // Ajuste para 'date' se sua coluna for apenas DATE
        'valor_total' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    // Adicione este relacionamento para o usuário que registrou a venda
    public function usuarioRegistrou(): BelongsTo // Ou simplesmente user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function itensEstoque(): BelongsToMany
    {
        return $this->belongsToMany(Estoque::class, 'venda_acessorio_estoque', 'venda_acessorio_id', 'estoque_id')
            ->withPivot('quantidade', 'preco_unitario_venda', 'desconto')
            ->withTimestamps();
    }

    public function getDataVendaFormatadaAttribute()
    {
        if ($this->data_venda) {
            return Carbon::parse($this->data_venda)->format('d/m/Y H:i');
        }
        return null;
    }
}
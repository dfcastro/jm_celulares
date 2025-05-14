<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany; // Importe esta linha
use App\Models\Cliente;
use App\Models\Estoque;
use App\Models\DevolucaoVenda; // Importe o novo modelo DevolucaoVenda

class VendaAcessorio extends Model
{
    use HasFactory;

    protected $table = 'vendas_acessorios';

    protected $fillable = [
        'cliente_id',
        'data_venda',
        'valor_total',
        'forma_pagamento',
        'observacoes',
    ];

    protected $casts = [
        'data_venda' => 'datetime',
        'valor_total' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function itensVendidos(): BelongsToMany
    {
        return $this->belongsToMany(Estoque::class, 'venda_acessorio_estoque', 'venda_acessorio_id', 'estoque_id')
                    ->withPivot('quantidade', 'preco_unitario_venda', 'desconto');
    }

    /**
     * Define o relacionamento com DevolucaoVenda.
     */
    public function devolucoesVendas(): HasMany // Adicione este método
    {
        return $this->hasMany(DevolucaoVenda::class, 'venda_acessorio_id');
    }
}
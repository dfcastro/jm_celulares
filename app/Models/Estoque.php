<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Estoque extends Model
{
    use HasFactory;
    protected $table = 'estoque';
    protected $fillable = [
        'nome',
        'modelo_compativel',
        'numero_serie',
        'tipo',
        'marca', // <<<<<<<<<<<<<< ADICIONE ESTA LINHA
        'quantidade',
        'preco_custo',
        'preco_venda',
        'estoque_minimo',
    ];

    public function entradasEstoque(): HasMany
    {
        return $this->hasMany(EntradaEstoque::class);
    }

    public function saidasEstoque(): HasMany
    {
        return $this->hasMany(SaidaEstoque::class);
    }

    public function devolucoesVendas(): BelongsToMany
    {
        return $this->belongsToMany(DevolucaoVenda::class, 'devolucao_venda_estoque', 'estoque_id', 'devolucao_venda_id')
                    ->withPivot('quantidade_devolvida', 'valor_unitario_devolvido');
    }
}
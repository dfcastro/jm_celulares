<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estoque extends Model
{
    use HasFactory;
    protected $table = 'estoque'; // Especifica o nome da tabela
    protected $fillable = [
        'nome',
        'modelo_compativel', // Adicionando o novo campo
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
}
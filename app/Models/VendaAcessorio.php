<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Precisamos para o relacionamento com Cliente
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Precisamos para o relacionamento Many-to-Many com Estoque
use App\Models\Cliente; // Precisamos importar o modelo Cliente
use App\Models\Estoque; // Precisamos importar o modelo Estoque

class VendaAcessorio extends Model
{
    use HasFactory;

    // Define o nome da tabela no banco de dados associada a este modelo
    protected $table = 'vendas_acessorios';

    // Define quais atributos podem ser preenchidos em massa (fillable)
    // Isso protege contra atribuição massiva inesperada de campos
    protected $fillable = [
        'cliente_id',
        'data_venda',
        'valor_total',
        'forma_pagamento',
        'observacoes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    // Define como certos atributos devem ser convertidos para tipos PHP
    // ao serem recuperados do banco de dados.
    protected $casts = [
        'data_venda' => 'datetime', // Converte a coluna 'data_venda' para uma instância Carbon (objeto de data/hora)
        'valor_total' => 'decimal:2', // Converte 'valor_total' para um float/string representando um decimal com 2 casas (ajuste '2' conforme sua migração)
        // As colunas 'created_at' e 'updated_at' são geralmente castadas para datetime por padrão no Laravel
    ];

    // Define o relacionamento "pertence a" com o modelo Cliente.
    // Uma venda de acessório pode pertencer a um cliente.
    // O Eloquent assume a chave estrangeira 'cliente_id' por padrão.
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    // Define o relacionamento "pertence a muitos" com o modelo Estoque.
    // Uma venda de acessório envolve muitos itens de estoque (acessórios vendidos).
    // Este relacionamento é feito através da tabela pivô 'venda_acessorio_estoque'.
    public function itensVendidos(): BelongsToMany
    {
        // O primeiro argumento é o modelo com o qual você está se relacionando (Estoque)
        // O segundo argumento é o nome da tabela pivô que conecta os dois modelos
        // O terceiro argumento é o nome da chave estrangeira na tabela pivô que se refere a ESTE modelo (VendaAcessorio)
        // O quarto argumento é o nome da chave estrangeira na tabela pivô que se refere ao modelo relacionado (Estoque)
        return $this->belongsToMany(Estoque::class, 'venda_acessorio_estoque', 'venda_acessorio_id', 'estoque_id')
                    // O método withPivot especifica colunas ADICIONAIS da tabela pivô
                    // que você deseja que estejam disponíveis ao acessar os modelos relacionados.
                    ->withPivot('quantidade', 'preco_unitario_venda'); // Incluímos a quantidade vendida e o preço unitário da venda do pivô.
    }
}
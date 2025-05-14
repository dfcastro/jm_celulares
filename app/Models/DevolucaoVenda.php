<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Importe esta linha

class DevolucaoVenda extends Model
{
    use HasFactory;

    protected $table = 'devolucoes_vendas';

    protected $fillable = [
        'venda_acessorio_id',
        'valor_devolvido',
        'data_devolucao',
        'observacoes',
    ];

    protected $casts = [
        'data_devolucao' => 'datetime',
        'valor_devolvido' => 'decimal:2',
    ];

    /**
     * Define o relacionamento com a VendaAcessorio.
     */
    public function vendaAcessorio(): BelongsTo
    {
        return $this->belongsTo(VendaAcessorio::class);
    }

    /**
     * Define o relacionamento com os itens de Estoque devolvidos nesta devolução.
     * Usa a tabela pivô 'devolucao_venda_estoque'.
     */
    public function itensDevolvidos(): BelongsToMany // Adicione este método
    {
        return $this->belongsToMany(Estoque::class, 'devolucao_venda_estoque', 'devolucao_venda_id', 'estoque_id')
                    ->withPivot('quantidade_devolvida', 'valor_unitario_devolvido'); // Importante para acessar os dados da tabela pivô
    }
}
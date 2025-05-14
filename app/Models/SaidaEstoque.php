<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Certifique-se de que BelongsTo está importado
use App\Models\Atendimento; // Importar o modelo Atendimento

class SaidaEstoque extends Model
{
    use HasFactory;

    protected $table = 'saidas_estoque';

    protected $fillable = [
        'estoque_id',
        'atendimento_id',
        'quantidade',
        'data_saida',
        'observacoes', // Adicionar aqui também após a migração
    ];

     /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data_saida' => 'datetime', // Converte a coluna data_saida para um objeto datetime (Carbon)
        // Opcional: 'created_at' => 'datetime', // Laravel geralmente faz isso por padrão, mas explicitar não faz mal
        // Opcional: 'updated_at' => 'datetime', // Opcional
    ];
    // Relacionamento com Estoque
    public function estoque(): BelongsTo
    {
        return $this->belongsTo(Estoque::class);
    }

    // NOVO: Relacionamento com Atendimento
    public function atendimento(): BelongsTo
    {
        return $this->belongsTo(Atendimento::class); // Use nullable() pois atendimento_id é nullable no DB
    }
}
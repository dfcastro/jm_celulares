<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaidaEstoque extends Model
{
    use HasFactory;

    protected $fillable = [
        'estoque_id',
        'atendimento_id',
        'quantidade',
        'data_saida',
        'observacoes',
    ];

    public function estoque(): BelongsTo
    {
        return $this->belongsTo(Estoque::class);
    }

    public function atendimento(): BelongsTo
    {
        return $this->belongsTo(Atendimento::class)->nullable();
    }
}
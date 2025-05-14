<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntradaEstoque extends Model
{
    use HasFactory;

    protected $table = 'entradas_estoque';

    protected $fillable = [
        'estoque_id',
        'quantidade',
        'data_entrada',
        'observacoes',
    ];

    protected $casts = [
        'data_entrada' => 'datetime',
    ];

    public function estoque(): BelongsTo
    {
        return $this->belongsTo(Estoque::class);
    }
}
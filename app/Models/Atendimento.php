<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Atendimento extends Model
{
    use HasFactory;
    protected $fillable = [
        'cliente_id',
        'celular',
        'problema_relatado',
        'data_entrada',
        'status',
        'tecnico_id',
        'data_conclusao',
        'observacoes',
        'codigo_consulta', // Adicione esta linha
    ];

    protected $casts = [
        'data_entrada' => 'datetime', // Garante que data_entrada seja um objeto Carbon
        'data_conclusao' => 'datetime', // Garante que data_conclusao seja um objeto Carbon (se aplicável)
    ];
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function saidasEstoque(): HasMany
    {
        return $this->hasMany(SaidaEstoque::class);
    }
}
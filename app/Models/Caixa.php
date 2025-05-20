<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon; // Importar Carbon

class Caixa extends Model
{
    use HasFactory;

    protected $table = 'caixas';

    protected $fillable = [
        'usuario_abertura_id',
        'usuario_fechamento_id',
        'data_abertura',
        'data_fechamento',
        'saldo_inicial',
        'saldo_final_calculado',
        'saldo_final_informado',
        'diferenca',
        'status',
        'observacoes_abertura',
        'observacoes_fechamento',
    ];

    protected $casts = [
        'data_abertura' => 'datetime',
        'data_fechamento' => 'datetime',
        'saldo_inicial' => 'decimal:2',
        'saldo_final_calculado' => 'decimal:2',
        'saldo_final_informado' => 'decimal:2',
        'diferenca' => 'decimal:2',
    ];

    /**
     * Usuário que abriu o caixa.
     */
    public function usuarioAbertura(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_abertura_id');
    }

    /**
     * Usuário que fechou o caixa.
     */
    public function usuarioFechamento(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_fechamento_id');
    }

    /**
     * Movimentações pertencentes a este caixa.
     */
    public function movimentacoes(): HasMany
    {
        return $this->hasMany(MovimentacaoCaixa::class);
    }

    /**
     * Verifica se o caixa está atualmente aberto.
     */
    public function estaAberto(): bool
    {
        return $this->status === 'Aberto' && is_null($this->data_fechamento);
    }

    /**
     * Formata a data de abertura.
     */
    public function getDataAberturaFormatadaAttribute(): ?string
    {
        return $this->data_abertura ? Carbon::parse($this->data_abertura)->format('d/m/Y H:i:s') : null;
    }

    /**
     * Formata a data de fechamento.
     */
    public function getDataFechamentoFormatadaAttribute(): ?string
    {
        return $this->data_fechamento ? Carbon::parse($this->data_fechamento)->format('d/m/Y H:i:s') : null;
    }
    public static function getCaixaAbertoAtual(): ?Caixa
    {
        return self::where('status', 'Aberto')->first();
    }
}
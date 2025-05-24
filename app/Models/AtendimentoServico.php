<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtendimentoServico extends Model
{
    use HasFactory;

    /**
     * O nome da tabela associada ao model.
     *
     * @var string
     */
    protected $table = 'atendimento_servicos';

    /**
     * Indica se o model deve ter timestamps (created_at, updated_at).
     * Defina como false se você não adicionou $table->timestamps() na migration.
     *
     * @var bool
     */
    public $timestamps = false; // Mude para true se você adicionou timestamps na migration

    /**
     * Os atributos que são mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'atendimento_id',
        'descricao_servico',
        'quantidade',
        'valor_unitario',
        'subtotal_servico',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantidade' => 'integer',
        'valor_unitario' => 'decimal:2',
        'subtotal_servico' => 'decimal:2',
    ];

    /**
     * Define o relacionamento com o Atendimento ao qual este serviço pertence.
     */
    public function atendimento(): BelongsTo
    {
        return $this->belongsTo(Atendimento::class);
    }
}
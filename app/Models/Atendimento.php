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
        //'celular',
        'descricao_aparelho',
        'problema_relatado',
        'data_entrada',
        'status',
        'tecnico_id',
        'data_conclusao',
        'observacoes',
        'codigo_consulta',
        'laudo_tecnico',
        'valor_servico',
        'forma_pagamento',   // <<<<<<<<<<<<<< ADICIONADO
        'desconto_servico', // <<<<<<<<<<<<<< ADICIONADO
    ];

    protected $casts = [
        'data_entrada' => 'datetime',
        'data_conclusao' => 'datetime',
        'valor_servico' => 'decimal:2',    // <<<<<<<<<<<<<< ADICIONADO
        'desconto_servico' => 'decimal:2', // <<<<<<<<<<<<<< ADICIONADO
    ];

    // ... seus relacionamentos (cliente, tecnico, saidasEstoque) ...
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
    public static function getStatusDePagamento(): array
    {
        return ['Entregue', 'Finalizado e Pago', 'Pago']; // <<<< AJUSTE ESTA LISTA
    }

    // Status que podem ser definidos na criação, por exemplo
    public static function getInitialStatuses(): array
    {
        return ['Em aberto', 'Em diagnóstico'];
    }

    // Retorna a classe CSS para o badge do status
    public static function getStatusClass($status): string
    {
        switch ($status) {
            case 'Entregue':
            case 'Finalizado e Pago':
            case 'Pago':
                return 'bg-success';
            case 'Em manutenção':
            case 'Aguardando peça':
            case 'Aguardando aprovação cliente':
                return 'bg-warning text-dark';
            case 'Pronto para entrega':
                return 'bg-info text-dark';
            case 'Cancelado':
                return 'bg-danger';
            case 'Em diagnóstico':
                return 'bg-primary';
            case 'Em aberto':
            default:
                return 'bg-secondary';
        }
    }

    // Retorna o ícone para o status
    public static function getStatusIcon($status): string
    {
        switch ($status) {
            case 'Entregue':
            case 'Finalizado e Pago':
            case 'Pago':
                return 'bi-check-circle-fill';
            case 'Em manutenção':
                return 'bi-gear-fill';
            case 'Aguardando peça':
            case 'Aguardando aprovação cliente':
                return 'bi-hourglass-split';
            case 'Pronto para entrega':
                return 'bi-box-seam-fill';
            case 'Cancelado':
                return 'bi-x-circle-fill';
            case 'Em diagnóstico':
                return 'bi-search';
            case 'Em aberto':
            default:
                return 'bi-folder2-open';
        }
    }
     public static function getPossibleStatuses(): array
    {
        return [
            'Em aberto',
            'Em diagnóstico',
            'Aguardando peça',
            'Aguardando aprovação cliente',
            'Em manutenção',
            'Pronto para entrega',
            'Entregue',
            'Cancelado',
            'Pago',
            'Finalizado e Pago',
            // Adicione ou remova conforme sua necessidade
        ];
    }
}

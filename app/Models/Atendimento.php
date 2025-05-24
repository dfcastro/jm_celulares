<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // Adicionado para consistência

class Atendimento extends Model
{
    use HasFactory;
    protected $fillable = [
        'cliente_id',
        'descricao_aparelho',
        'problema_relatado',
        'data_entrada',
        'status',
        'status_pagamento',
        'tecnico_id',
        'data_conclusao',
        'observacoes',
        'codigo_consulta',
        'laudo_tecnico',
        'valor_servico',
        'forma_pagamento',
        'desconto_servico',
    ];

    protected $casts = [
        'data_entrada' => 'datetime',
        'data_conclusao' => 'datetime',
        'valor_servico' => 'decimal:2',
        'desconto_servico' => 'decimal:2',
    ];

    // Relacionamentos (cliente, tecnico, saidasEstoque) como antes...
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function saidasEstoque(): HasMany
    {
        return $this->hasMany(SaidaEstoque::class);
    }
      /**
     * Define o relacionamento com os serviços detalhados deste atendimento.
     */
    public function servicosDetalhados(): HasMany // <<<< NOVO RELACIONAMENTO
    {
        return $this->hasMany(AtendimentoServico::class);
    }

    // Métodos de status (getPossibleStatuses, getAllowedStatusTransitions, etc.) como antes...
    public static function getPossibleStatuses(): array
    {
        return [
            'Em aberto',
            'Em diagnóstico',
            'Aguardando aprovação cliente',
            'Aguardando peça',
            'Em manutenção',
            'Pronto para entrega',
            'Entregue',
            'Cancelado',
            'Reprovado',
        ];
    }

    public static function getInitialStatuses(): array
    {
        return ['Em aberto', 'Em diagnóstico'];
    }

    public static function getAllowedStatusTransitions(User $user = null): array
    {
        $currentUser = $user ?: Auth::user();
        $transitions = [
            'Em aberto' => ['Em diagnóstico', 'Cancelado'],
            'Em diagnóstico' => ['Aguardando aprovação cliente', 'Aguardando peça', 'Em manutenção', 'Pronto para entrega', 'Cancelado', 'Reprovado'],
            'Aguardando aprovação cliente' => ['Em manutenção', 'Aguardando peça', 'Pronto para entrega', 'Reprovado', 'Cancelado'],
            'Aguardando peça' => ['Em manutenção', 'Pronto para entrega', 'Cancelado'],
            'Em manutenção' => ['Pronto para entrega', 'Aguardando peça', 'Cancelado'],
            'Pronto para entrega' => ['Entregue', 'Cancelado'],
            'Entregue' => [],
            'Cancelado' => [],
            'Reprovado' => [],
        ];

        if ($currentUser && $currentUser->tipo_usuario === 'admin') {
            $transitions['Entregue'] = array_merge($transitions['Entregue'], ['Pronto para entrega']);
            $transitions['Cancelado'] = array_merge($transitions['Cancelado'], ['Em aberto', 'Em diagnóstico']);
            $transitions['Reprovado'] = array_merge($transitions['Reprovado'], ['Aguardando aprovação cliente']);
        } elseif ($currentUser && $currentUser->tipo_usuario === 'tecnico') {
            unset($transitions['Em diagnóstico'][array_search('Entregue', $transitions['Em diagnóstico'] ?? [])]);
            unset($transitions['Aguardando aprovação cliente'][array_search('Entregue', $transitions['Aguardando aprovação cliente'] ?? [])]);
            unset($transitions['Aguardando peça'][array_search('Entregue', $transitions['Aguardando peça'] ?? [])]);
            unset($transitions['Em manutenção'][array_search('Entregue', $transitions['Em manutenção'] ?? [])]);
            unset($transitions['Pronto para entrega'][array_search('Cancelado', $transitions['Pronto para entrega'] ?? [])]);
        } elseif ($currentUser && $currentUser->tipo_usuario === 'atendente') {
            $transitions['Em aberto'] = ['Em diagnóstico', 'Cancelado'];
            $transitions['Em diagnóstico'] = ['Aguardando aprovação cliente', 'Cancelado'];
            $transitions['Aguardando aprovação cliente'] = ['Em manutenção', 'Reprovado', 'Cancelado'];
            $transitions['Pronto para entrega'] = ['Entregue'];
            $transitions['Aguardando peça'] = [];
            $transitions['Em manutenção'] = [];
        }
        return $transitions;
    }

    public function canTransitionTo(string $newStatus, User $user = null): bool
    {
        $currentUser = $user ?: Auth::user();
        if (!$currentUser) {
            return false;
        }
        $allowedTransitions = self::getAllowedStatusTransitions($currentUser);
        $currentStatus = $this->status;
        return isset($allowedTransitions[$currentStatus]) && in_array($newStatus, $allowedTransitions[$currentStatus]);
    }

    public static function getAllowedPaymentStatusTransitions(Atendimento $atendimento, User $user = null): array
    {
        $currentUser = $user ?: Auth::user();
        $transitions = [
            'Pendente' => ['Pago', 'Parcialmente Pago', 'Cancelado', 'Não Aplicável'],
            'Parcialmente Pago' => ['Pago', 'Devolvido', 'Cancelado'],
            'Pago' => ['Devolvido'],
            'Não Aplicável' => [],
            'Devolvido' => [],
            'Cancelado' => [],
        ];
        if ($currentUser && $currentUser->tipo_usuario === 'admin') {
            $transitions['Pago'] = array_merge($transitions['Pago'], ['Pendente']);
            $transitions['Devolvido'] = array_merge($transitions['Devolvido'], ['Pendente', 'Pago']);
            $transitions['Cancelado'] = array_merge($transitions['Cancelado'], ['Pendente']);
        }
        if (in_array($atendimento->status, ['Cancelado', 'Reprovado'])) {
            if ($atendimento->status_pagamento === 'Pendente' || $atendimento->status_pagamento === 'Parcialmente Pago') {
                $transitions[$atendimento->status_pagamento] = ['Cancelado', 'Não Aplicável'];
            } elseif ($atendimento->status_pagamento === 'Pago') {
                $transitions['Pago'] = ['Devolvido'];
            } else {
                $transitions[$atendimento->status_pagamento] = [];
            }
        }
        return $transitions;
    }

    public function canTransitionPaymentTo(string $newPaymentStatus, User $user = null): bool
    {
        $currentUser = $user ?: Auth::user();
        if (!$currentUser) {
            return false;
        }
        $allowedTransitions = self::getAllowedPaymentStatusTransitions($this, $currentUser);
        $currentPaymentStatus = $this->status_pagamento ?? 'Pendente';
        return isset($allowedTransitions[$currentPaymentStatus]) && in_array($newPaymentStatus, $allowedTransitions[$currentPaymentStatus]);
    }

    public static function getStatusClass(?string $status): string
    {
        switch ($status) {
            case 'Entregue':
            case 'Pronto para entrega':
                return 'bg-success';
            case 'Em manutenção':
            case 'Aguardando aprovação cliente':
                return 'bg-primary';
            case 'Aguardando peça':
                return 'bg-warning text-dark';
            case 'Cancelado':
            case 'Reprovado':
                return 'bg-danger';
            case 'Em diagnóstico':
                return 'bg-info text-dark';
            case 'Em aberto':
            default:
                return 'bg-secondary';
        }
    }

    public static function getStatusIcon(?string $status): string
    {
        switch ($status) {
            case 'Pronto para entrega':
            case 'Entregue':
                return 'bi-check2-circle';
            case 'Em manutenção':
                return 'bi-gear-fill';
            case 'Aguardando aprovação cliente':
                return 'bi-person-check';
            case 'Aguardando peça':
                return 'bi-hourglass-split';
            case 'Cancelado':
            case 'Reprovado':
                return 'bi-x-circle-fill';
            case 'Em diagnóstico':
                return 'bi-search';
            case 'Em aberto':
            default:
                return 'bi-folder2-open';
        }
    }

    public static function getPossiblePaymentStatuses(): array
    {
        return [
            'Pendente',
            'Pago',
            'Parcialmente Pago',
            'Não Aplicável',
            'Devolvido',
            'Cancelado',
        ];
    }

    public static function getPaymentStatusClass(?string $statusPagamento): string
    {
        switch ($statusPagamento) {
            case 'Pago':
                return 'bg-success';
            case 'Parcialmente Pago':
                return 'bg-info text-dark';
            case 'Devolvido':
                return 'bg-secondary';
            case 'Pendente':
                return 'bg-warning text-dark';
            case 'Cancelado':
                return 'bg-danger';
            case 'Não Aplicável':
            default:
                return 'bg-light text-dark border';
        }
    }

    public static function getPaymentStatusIcon(?string $statusPagamento): string
    {
        switch ($statusPagamento) {
            case 'Pago':
                return 'bi-patch-check-fill';
            case 'Parcialmente Pago':
                return 'bi-pie-chart-fill';
            case 'Pendente':
                return 'bi-hourglass-bottom';
            case 'Cancelado':
                return 'bi-x-octagon-fill';
            case 'Devolvido':
                return 'bi-arrow-left-circle-fill';
            case 'Não Aplicável':
            default:
                return 'bi-question-circle';
        }
    }

    // --- Assessors para valores calculados ---
    public function getValorServicoLiquidoAttribute(): float
    {
        return (float) ($this->valor_servico ?? 0) - (float) ($this->desconto_servico ?? 0);
    }

    public function getValorTotalPecasAttribute(): float
    {
        // Garante que a relação está carregada para evitar N+1 queries se usado em loop
        if (!$this->relationLoaded('saidasEstoque')) {
            $this->load('saidasEstoque.estoque');
        }

        return $this->saidasEstoque->sum(function ($saida) {
            return $saida->quantidade * (float) ($saida->estoque->preco_venda ?? 0);
        });
    }

    public function getValorTotalAtendimentoAttribute(): float
    {
        return $this->valor_servico_liquido + $this->valor_total_pecas;
    }
    // --- Fim dos Assessors ---

    public function isTotalmentePago(): bool
    {
        return $this->status_pagamento === 'Pago';
    }

    public function isPagamentoPendente(): bool
    {
        return $this->status_pagamento === 'Pendente';
    }

    public function isAbertoOuEmAndamento(): bool
    {
        return in_array($this->status, [
            'Em aberto',
            'Em diagnóstico',
            'Aguardando aprovação cliente',
            'Aguardando peça',
            'Em manutenção',
        ]);
    }

    public function podeSerConsideradoEntregue(): bool
    {
        if ($this->status === 'Entregue') {
            return true;
        }
        if (
            $this->status === 'Pronto para entrega' &&
            in_array($this->status_pagamento, ['Pago', 'Não Aplicável'])
        ) {
            return true;
        }
        return false;
    }

    public function isFinalizadoParaLista(): bool
    {
        if (in_array($this->status, ['Entregue', 'Cancelado', 'Reprovado'])) {
            return true;
        }
        if ($this->status === 'Pronto para entrega' && ($this->status_pagamento === 'Pago' || $this->status_pagamento === 'Não Aplicável')) {
            return true;
        }
        return false;
    }
}
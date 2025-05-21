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
        'descricao_aparelho',
        'problema_relatado',
        'data_entrada',
        'status',           // Status do serviço/reparo
        'status_pagamento', // Status financeiro
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
     * Retorna os status GERAIS possíveis para um atendimento (foco no progresso técnico).
     * @return array
     */
    public static function getPossibleStatuses(): array
    {
        return [
            'Em aberto',                 // Novo, aguardando triagem inicial
            'Em diagnóstico',            // Técnico está analisando
            'Aguardando aprovação cliente', // Orçamento enviado, aguardando OK do cliente para prosseguir
            'Aguardando peça',           // Peça necessária pedida/em trânsito
            'Em manutenção',             // Reparo em andamento
            'Pronto para entrega', 
            'Entregue',      // Serviço concluído, aguardando retirada/pagamento
            'Cancelado',                 // Pelo cliente ou pela loja antes da conclusão
            'Reprovado',                 // Cliente não aprovou o orçamento/serviço
            // 'Entregue' foi removido daqui - será controlado pela combinação de status e status_pagamento.
            // 'Pago' e 'Finalizado e Pago' foram removidos daqui.
        ];
    }

    /**
     * Retorna os status INICIAIS possíveis para um atendimento.
     * @return array
     */
    public static function getInitialStatuses(): array
    {
        return ['Em aberto', 'Em diagnóstico'];
    }

    /**
     * Retorna a classe CSS para o badge do status GERAL.
     * @param string|null $status
     * @return string
     */
    public static function getStatusClass(?string $status): string
    {
        switch ($status) {
            case 'Entregue':
            case 'Pronto para entrega':
                return 'bg-success'; // Verde, pois o serviço está pronto.
            case 'Em manutenção':
            case 'Aguardando aprovação cliente':
                return 'bg-primary'; // Azul para em progresso ou aguardando ação externa
            case 'Aguardando peça':
                return 'bg-warning text-dark'; // Amarelo para pendências de peças
            case 'Cancelado':
            case 'Reprovado':
                return 'bg-danger';
            case 'Em diagnóstico':
                return 'bg-info text-dark'; // Ciano para diagnóstico
            case 'Em aberto':
            default:
                return 'bg-secondary';
        }
    }

    /**
     * Retorna o ícone para o status GERAL.
     * @param string|null $status
     * @return string
     */
    public static function getStatusIcon(?string $status): string
    {
        switch ($status) {
            case 'Pronto para entrega':
                return 'bi-check2-circle'; // Ícone de pronto
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


    // ---- MÉTODOS PARA STATUS DE PAGAMENTO (permanecem os mesmos) ----

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
                return 'bg-secondary'; // Pode ser ajustado, talvez um laranja claro
            case 'Pendente':
                return 'bg-warning text-dark';
            case 'Cancelado':
                return 'bg-danger';
            case 'Não Aplicável':
            default:
                return 'bg-light text-dark border'; // Um cinza claro com borda
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
                return 'bi-x-octagon-fill'; // Um X mais forte
            case 'Devolvido':
                return 'bi-arrow-left-circle-fill'; // Ícone de retorno
            case 'Não Aplicável':
            default:
                return 'bi-question-circle';
        }
    }

    public function isTotalmentePago(): bool
    {
        return $this->status_pagamento === 'Pago';
    }

    public function isPagamentoPendente(): bool
    {
        return $this->status_pagamento === 'Pendente';
    }

    /**
     * Determina se o atendimento pode ser considerado "Entregue ao Cliente".
     * Um atendimento é considerado entregue se o serviço está pronto E o pagamento foi realizado (ou não é aplicável).
     * Você pode ajustar essa lógica conforme a regra de negócio.
     * @return bool
     */
    public function podeSerConsideradoEntregue(): bool
    {
        $servicoPronto = $this->status === 'Pronto para entrega';
        $pagamentoOk = $this->status_pagamento === 'Pago' || $this->status_pagamento === 'Não Aplicável';

        // Um exemplo de regra: para ser entregue, o serviço deve estar "Pronto para entrega" E
        // o status de pagamento deve ser "Pago" OU "Não Aplicável".
        return $servicoPronto && $pagamentoOk;
    }

    /**
     * Define se o atendimento está efetivamente finalizado e pode sair da lista de pendentes.
     * Poderia ser, por exemplo, quando o status GERAL é "Pronto para entrega" E o status de PAGAMENTO é "Pago"
     * OU quando o status GERAL é "Cancelado" ou "Reprovado".
     * A lógica exata depende do seu fluxo.
     * @return bool
     */
    public function isFinalizadoParaLista(): bool
    {
        if (in_array($this->status, ['Cancelado', 'Reprovado'])) {
            return true;
        }
        // Considera finalizado se está pronto e pago, ou pronto e não aplicável pagamento
        if ($this->status === 'Pronto para entrega' && ($this->status_pagamento === 'Pago' || $this->status_pagamento === 'Não Aplicável')) {
            return true;
        }
        // Adicione outras condições se necessário.
        // Por exemplo, se você tiver um status geral como "Entregue Fisicamente", ele também seria finalizado.
        return false;
    }

}
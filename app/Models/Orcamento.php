<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth; // Necessário para o usuário logado
use Carbon\Carbon; // Para manipulação de datas, se necessário

class Orcamento extends Model
{
    use HasFactory;

    protected $table = 'orcamentos';

    protected $fillable = [
        'cliente_id',
        'nome_cliente_avulso',
        'telefone_cliente_avulso',
        'email_cliente_avulso',
        'descricao_aparelho',
        'problema_relatado_cliente',
        'status',
        'data_emissao',
        'data_validade',
        'validade_dias',
        'valor_total_servicos',
        'valor_total_pecas',
        'sub_total',
        'desconto_tipo',
        'desconto_valor',
        'valor_final',
        'tempo_estimado_servico',
        'observacoes_internas',
        'termos_condicoes',
        'criado_por_id',
        'aprovado_por_id',
        'data_aprovacao',
        'data_reprovacao',
        'data_cancelamento',
        'atendimento_id_convertido',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'data_validade' => 'date',
        'data_aprovacao' => 'datetime',
        'data_reprovacao' => 'datetime',
        'data_cancelamento' => 'datetime',
        'valor_total_servicos' => 'decimal:2',
        'valor_total_pecas' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'desconto_valor' => 'decimal:2',
        'valor_final' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }

    public function aprovadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por_id');
    }

    public function atendimentoConvertido(): BelongsTo
    {
        return $this->belongsTo(Atendimento::class, 'atendimento_id_convertido');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(OrcamentoItem::class);
    }

    public function calcularValorFinal()
    {
        $subtotal = $this->valor_total_servicos + $this->valor_total_pecas;
        $this->sub_total = $subtotal;
        $valorDescontoCalculado = 0;

        if ($this->desconto_valor && $this->desconto_valor > 0) {
            if ($this->desconto_tipo === 'percentual') {
                $valorDescontoCalculado = ($subtotal * $this->desconto_valor) / 100;
            } elseif ($this->desconto_tipo === 'fixo') {
                $valorDescontoCalculado = $this->desconto_valor;
            }
        }
        $valorDescontoCalculado = min($valorDescontoCalculado, $subtotal);
        $this->valor_final = $subtotal - $valorDescontoCalculado;
    }

    /**
     * Retorna os status possíveis para um orçamento.
     * @return array
     */
    public static function getPossibleStatuses(): array
    {
        return ['Em Elaboração', 'Aguardando Aprovação', 'Aprovado', 'Reprovado', 'Cancelado', 'Convertido em OS'];
    }

    /**
     * Define as transições de status permitidas com base no perfil do usuário.
     *
     * @param User|null $user O usuário realizando a ação. Se null, usa o usuário logado.
     * @return array
     */
    public static function getAllowedStatusTransitions(User $user = null): array
    {
        $currentUser = $user ?: Auth::user();
        $transitions = [
            'Em Elaboração' => ['Aguardando Aprovação', 'Cancelado'],
            'Aguardando Aprovação' => ['Aprovado', 'Reprovado', 'Cancelado'],
            'Aprovado' => ['Convertido em OS', 'Cancelado'], // Um orçamento aprovado pode ser cancelado antes de virar OS
            'Reprovado' => ['Aguardando Aprovação', 'Cancelado'], // Pode voltar para aguardando se o cliente reconsiderar
            'Cancelado' => [], // Geralmente não se muda um status cancelado, exceto por admin
            'Convertido em OS' => [], // Não se altera após convertido
        ];

        // Admin pode ter mais flexibilidade
        if ($currentUser && $currentUser->tipo_usuario === 'admin') {
            $transitions['Cancelado'] = ['Em Elaboração', 'Aguardando Aprovação']; // Admin pode reabrir um cancelado
            $transitions['Reprovado'] = array_merge($transitions['Reprovado'], ['Em Elaboração']); // Admin pode reabrir um reprovado
            // Admin também poderia ter permissão para reverter 'Convertido em OS' em casos extremos, mas é complexo.
        }

        // Outros perfis (Técnico, Atendente) podem ter restrições adicionais
        if ($currentUser) {
            if ($currentUser->tipo_usuario === 'tecnico') {
                // Exemplo: Técnico talvez não possa aprovar ou cancelar um orçamento que está 'Aguardando Aprovação'
                unset($transitions['Aguardando Aprovação'][array_search('Aprovado', $transitions['Aguardando Aprovação'] ?? [])]);
                unset($transitions['Aguardando Aprovação'][array_search('Cancelado', $transitions['Aguardando Aprovação'] ?? [])]);
                // Removendo a possibilidade de converter para OS diretamente
                unset($transitions['Aprovado'][array_search('Convertido em OS', $transitions['Aprovado'] ?? [])]);
            }
            if ($currentUser->tipo_usuario === 'atendente') {
                // Atendente pode ter permissões similares ao admin para fluxo normal,
                // mas talvez não para reverter status finais.
            }
        }
        return $transitions;
    }

    /**
     * Verifica se o orçamento pode transitar para um novo status.
     *
     * @param string $newStatus O status desejado.
     * @param User|null $user O usuário realizando a ação.
     * @return bool
     */
    public function canTransitionTo(string $newStatus, User $user = null): bool
    {
        $currentUser = $user ?: Auth::user();
        if (!$currentUser) {
            return false; // Nenhuma transição se não houver usuário
        }

        $allowedTransitions = self::getAllowedStatusTransitions($currentUser);
        $currentStatus = $this->status;

        // Se o status atual não estiver mapeado ou o novo status não estiver na lista de permitidos
        if (!isset($allowedTransitions[$currentStatus]) || !in_array($newStatus, $allowedTransitions[$currentStatus])) {
            return false;
        }

        // Condições adicionais específicas para transições:
        if ($newStatus === 'Convertido em OS') {
            // Só pode converter para OS se tiver um cliente_id associado
            if (!$this->cliente_id) {
                return false;
            }
            // E se ainda não foi convertido
            if ($this->atendimento_id_convertido) {
                return false;
            }
        }

        // (Adicione outras condições específicas aqui se necessário)
        // Ex: if ($newStatus === 'Aprovado' && !$this->itens()->exists()) return false; // Não pode aprovar sem itens

        return true;
    }
}
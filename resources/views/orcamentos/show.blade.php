@extends('layouts.app')

@section('title', 'Detalhes do Orçamento #' . $orcamento->id)

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    .card-header h5,
    .card-title {
        font-weight: 500;
    }

    .section-title {
        margin-bottom: 0.5rem;
        font-weight: bold;
        color: #495057;
    }

    .dl-horizontal dt {
        float: left;
        width: 200px;
        font-weight: normal;
        color: #6c757d;
    }

    .dl-horizontal dd {
        margin-left: 220px;
        margin-bottom: .3rem;
    }

    .item-list {
        list-style: none;
        padding-left: 0;
    }

    .item-list li {
        border-bottom: 1px solid #eee;
        padding: 8px 0;
    }

    .item-list li:last-child {
        border-bottom: none;
    }

    .total-line {
        border-top: 2px solid #dee2e6;
        padding-top: 0.5rem;
        margin-top: 0.5rem;
    }

    .badge.status-orc {
        font-size: 1.1em;
    }

    pre {
        white-space: pre-wrap;
        font-family: inherit;
        margin: 0;
        font-size: 8.5pt;
    }
</style>
@endpush

@section('content')
<div class="container mt-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h1><i class="bi bi-receipt-cutoff"></i> Orçamento #{{ $orcamento->id }}</h1>
        <div>
            <a href="{{ route('orcamentos.pdf', $orcamento->id) }}" class="btn btn-info btn-sm me-2 mb-1" target="_blank">
                <i class="bi bi-printer-fill"></i> Gerar PDF
            </a>
            @if(in_array($orcamento->status, ['Em Elaboração', 'Aguardando Aprovação', 'Aprovado']))
            <form action="{{ route('orcamentos.enviarEmail', $orcamento->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm me-1 mb-1" onclick="return confirm('Tem certeza que deseja enviar este orçamento por e-mail para o cliente?');">
                    <i class="bi bi-envelope-fill"></i> Enviar por E-mail
                </button>
            </form>
            @endif
            <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary btn-sm mb-1">
                <i class="bi bi-list-ul"></i> Voltar para Lista
            </a>
        </div>
    </div>


    @if(session('info')) <div class="alert alert-info alert-dismissible fade show" role="alert">
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('info_conversao_cliente_necessario'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <strong>Ação Necessária:</strong> Para converter este orçamento em Ordem de Serviço, é preciso primeiro associá-lo a um cliente cadastrado.
        Por favor, <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="alert-link">edite o orçamento</a> para selecionar ou cadastrar um cliente.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif


    <div class="card shadow-lg">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="my-1">Detalhes do Orçamento</h5>
            @php
            $statusClassShow = '';
            if ($orcamento->status === 'Aprovado') $statusClassShow = 'bg-success';
            elseif ($orcamento->status === 'Reprovado') $statusClassShow = 'bg-danger';
            elseif ($orcamento->status === 'Cancelado') $statusClassShow = 'bg-secondary';
            elseif ($orcamento->status === 'Aguardando Aprovação') $statusClassShow = 'bg-warning text-dark';
            elseif ($orcamento->status === 'Em Elaboração') $statusClassShow = 'bg-info text-dark';
            elseif ($orcamento->status === 'Convertido em OS') $statusClassShow = 'bg-primary';
            else $statusClassShow = 'bg-light text-dark';
            @endphp
            <span class="badge rounded-pill status-orc {{ $statusClassShow }}">{{ $orcamento->status }}</span>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-7">
                    <h6 class="section-title">Cliente e Aparelho</h6>
                    <dl class="dl-horizontal">
                        <dt>Cliente:</dt>
                        <dd>
                            @if($orcamento->cliente)
                            {{ $orcamento->cliente->nome_completo }} ({{ $orcamento->cliente->cpf_cnpj }})
                            @if($orcamento->cliente->telefone)<br><small class="text-muted">Tel: {{ $orcamento->cliente->telefone }}</small>@endif
                            @if($orcamento->cliente->email)<br><small class="text-muted">Email: {{ $orcamento->cliente->email }}</small>@endif
                            @else
                            {{ $orcamento->nome_cliente_avulso ?? 'Não informado' }}
                            @if($orcamento->telefone_cliente_avulso)<br><small class="text-muted">Tel: {{ $orcamento->telefone_cliente_avulso }}</small>@endif
                            @if($orcamento->email_cliente_avulso)<br><small class="text-muted">Email: {{ $orcamento->email_cliente_avulso }}</small>@endif
                            @endif
                        </dd>
                        <dt>Aparelho:</dt>
                        <dd>{{ $orcamento->descricao_aparelho }}</dd>
                        <dt>Problema Relatado:</dt>
                        <dd>
                            <pre>{{ $orcamento->problema_relatado_cliente }}</pre>
                        </dd>
                    </dl>
                </div>
                <div class="col-md-5">
                    <h6 class="section-title">Detalhes do Orçamento</h6>
                    <dl class="dl-horizontal">
                        <dt>Data de Emissão:</dt>
                        <dd>{{ $orcamento->data_emissao->format('d/m/Y') }}</dd>
                        <dt>Validade:</dt>
                        <dd>
                            @if ($orcamento->data_validade)
                            {{ $orcamento->data_validade->format('d/m/Y') }}
                            ({{ $orcamento->data_emissao->diffInDays($orcamento->data_validade) }} dias)
                            @elseif($orcamento->validade_dias)
                            {{ $orcamento->validade_dias }} dias a partir da emissão
                            @else
                            Indeterminada
                            @endif
                        </dd>
                        <dt>Tempo Estimado:</dt>
                        <dd>{{ $orcamento->tempo_estimado_servico ?? 'Não informado' }}</dd>
                        <dt>Criado por:</dt>
                        <dd>{{ $orcamento->criadoPor->name ?? 'N/A' }} em {{ $orcamento->created_at->format('d/m/Y H:i') }}</dd>
                        @if($orcamento->data_aprovacao)
                        <dt>Aprovado em:</dt>
                        <dd>{{ $orcamento->data_aprovacao->format('d/m/Y H:i') }} @if($orcamento->aprovadoPor) por {{ $orcamento->aprovadoPor->name }} @endif</dd>
                        @endif
                        @if($orcamento->data_reprovacao)
                        <dt>Reprovado em:</dt>
                        <dd>{{ $orcamento->data_reprovacao->format('d/m/Y H:i') }}</dd>
                        @endif
                        @if($orcamento->data_cancelamento)
                        <dt>Cancelado em:</dt>
                        <dd>{{ $orcamento->data_cancelamento->format('d/m/Y H:i') }}</dd>
                        @endif
                        @if($orcamento->atendimento_id_convertido)
                        <dt>Convertido em OS:</dt>
                        <dd><a href="{{ route('atendimentos.show', $orcamento->atendimento_id_convertido) }}">#{{ $orcamento->atendimento_id_convertido }}</a></dd>
                        @endif
                    </dl>
                </div>
            </div>

            <hr class="my-4">

            <h6 class="section-title mt-4">Itens Orçados</h6>
            @if($orcamento->itens->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Tipo</th>
                            <th>Descrição</th>
                            <th class="text-center">Qtd</th>
                            <th class="text-end">Val. Unit. (R$)</th>
                            <th class="text-end">Subtotal (R$)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orcamento->itens as $item)
                        <tr>
                            <td>{{ ucfirst($item->tipo_item) }}</td>
                            <td>
                                @if($item->tipo_item == 'peca' && $item->estoque)
                                {{ $item->estoque->nome }}
                                @if($item->estoque->modelo_compativel) <small class="text-muted"> ({{ $item->estoque->modelo_compativel }})</small> @endif
                                @if($item->estoque->marca) <small class="text-muted"> [{{ $item->estoque->marca }}]</small> @endif
                                @else
                                {{ $item->descricao_item_manual }}
                                @endif
                            </td>
                            <td class="text-center">{{ $item->quantidade }}</td>
                            <td class="text-end">{{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($item->subtotal_item, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-muted">Nenhum item adicionado a este orçamento.</p>
            @endif

            <div class="row justify-content-end mt-3">
                <div class="col-md-5 col-lg-4">
                    <dl class="dl-horizontal">
                        <dt>Total Serviços:</dt>
                        <dd class="text-end">R$ {{ number_format($orcamento->valor_total_servicos, 2, ',', '.') }}</dd>

                        <dt>Total Peças:</dt>
                        <dd class="text-end">R$ {{ number_format($orcamento->valor_total_pecas, 2, ',', '.') }}</dd>

                        <dt class="fw-semibold">Subtotal:</dt>
                        <dd class="text-end fw-semibold">R$ {{ number_format($orcamento->sub_total, 2, ',', '.') }}</dd>

                        @if($orcamento->desconto_valor > 0)
                        <dt class="text-danger">Desconto
                            @if($orcamento->desconto_tipo == 'percentual')
                            ({{ number_format($orcamento->desconto_valor, 0) }}%):
                            @else
                            (Fixo):
                            @endif
                        </dt>
                        <dd class="text-end text-danger">
                            @php
                            $valorDescontoCalculado = 0;
                            if ($orcamento->desconto_tipo == 'percentual' && $orcamento->sub_total > 0) { // Evita divisão por zero se subtotal for 0
                            $valorDescontoCalculado = ($orcamento->sub_total * $orcamento->desconto_valor) / 100;
                            } elseif ($orcamento->desconto_tipo == 'fixo') {
                            $valorDescontoCalculado = $orcamento->desconto_valor;
                            }
                            $valorDescontoCalculado = min($valorDescontoCalculado, $orcamento->sub_total); // Garante que o desconto não seja maior que o subtotal
                            @endphp
                            - R$ {{ number_format($valorDescontoCalculado, 2, ',', '.') }}
                        </dd>
                        @endif
                        <dt class="fw-bolder fs-5 total-line">VALOR FINAL:</dt>
                        <dd class="text-end fw-bolder fs-5 total-line">R$ {{ number_format($orcamento->valor_final, 2, ',', '.') }}</dd>
                    </dl>
                </div>
            </div>

            @if($orcamento->termos_condicoes)
            <hr class="my-3">
            <h6 class="section-title">Termos e Condições</h6>
            <div class="p-3 border rounded bg-light small" style="white-space: pre-wrap;">
                {{ $orcamento->termos_condicoes }}
            </div>
            @endif

            @if($orcamento->observacoes_internas)
            <hr class="my-3">
            <h6 class="section-title">Observações Internas</h6>
            <div class="p-3 border rounded bg-light-subtle small fst-italic" style="white-space: pre-wrap;">
                {{ $orcamento->observacoes_internas }}
            </div>
            @endif
        </div>
        <div class="card-footer text-end d-flex justify-content-between align-items-center flex-wrap gap-1">
            <div>
                @if($orcamento->status == 'Em Elaboração' || (session()->get('orcamento_edit_cliente_id') === $orcamento->id && $orcamento->status === 'Aprovado' && !$orcamento->cliente_id) )
                <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="btn btn-warning btn-sm me-2">
                    <i class="bi bi-pencil-square"></i>
                    {{ $orcamento->status == 'Em Elaboração' ? 'Editar Orçamento' : 'Vincular Cliente para Converter' }}
                </a>
                @endif
            </div>
            <div class="btn-group-actions">
                @if($orcamento->status == 'Em Elaboração')
                <form action="{{ route('orcamentos.marcarAguardando', $orcamento->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-info btn-sm me-1">
                        <i class="bi bi-send-check-fill"></i> Finalizar e Enviar p/ Aprovação
                    </button>
                </form>
                @endif

                @if($orcamento->status == 'Aguardando Aprovação')
                <form action="{{ route('orcamentos.aprovar', $orcamento->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm me-1" onclick="return confirm('Confirmar aprovação deste orçamento?');">
                        <i class="bi bi-check-circle-fill"></i> Registrar Aprovação
                    </button>
                </form>
                <form action="{{ route('orcamentos.reprovar', $orcamento->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm me-1" onclick="return confirm('Confirmar reprovação deste orçamento?');">
                        <i class="bi bi-x-octagon-fill"></i> Registrar Reprovação
                    </button>
                </form>
                @endif

                @if($orcamento->status == 'Aprovado' && !$orcamento->atendimento_id_convertido)
                <form action="{{ route('orcamentos.converterEmOs', $orcamento->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja converter este orçamento em uma Ordem de Serviço? Esta ação dará baixa nas peças do estoque (se aplicável) e não poderá ser desfeita facilmente.');">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm me-1">
                        <i class="bi bi-box-arrow-in-down-left"></i> Converter em OS
                    </button>
                </form>
                @elseif($orcamento->atendimento_id_convertido)
                <a href="{{ route('atendimentos.show', $orcamento->atendimento_id_convertido) }}" class="btn btn-outline-success btn-sm me-1" title="Ver OS Gerada">
                    <i class="bi bi-eye-fill"></i> Ver OS #{{ $orcamento->atendimento_id_convertido }}
                </a>
                @endif

                @if(!in_array($orcamento->status, ['Cancelado', 'Convertido em OS', 'Reprovado']))
                @if(!($orcamento->status == 'Aprovado' && $orcamento->atendimento_id_convertido))
                <form action="{{ route('orcamentos.destroy', $orcamento->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja CANCELAR este orçamento?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-x-lg"></i> Cancelar Orçamento
                    </button>
                </form>
                @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
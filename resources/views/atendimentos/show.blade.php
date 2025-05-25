@extends('layouts.app')

@section('title', 'Detalhes do Atendimento #' . $atendimento->id)

@push('styles')
    {{-- Estilos existentes --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .card-header h5 { font-weight: 500; }
        .dl-horizontal dt { float: left; width: 180px; font-weight: normal; color: #6c757d; clear: left; }
        .dl-horizontal dd { margin-left: 200px; margin-bottom: .4rem; }
        .badge.fs-6 { font-size: 0.9rem !important; padding: .4em .7em; }
        .bg-light-subtle { background-color: #f8f9fa !important; }
        .section-title { margin-bottom: 0.75rem; font-weight: bold; color: #495057; font-size: 1.1rem; }
        .problem-box, .laudo-box, .obs-box { padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 0.25rem; background-color: #fdfdfd; white-space: pre-wrap; font-size: 0.9em; min-height: 60px; margin-bottom: 1rem; }
        .actions-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; }
        .valor-destaque { font-size: 1.2em; font-weight: bold; }

        /* Estilos para abas */
        .nav-tabs .nav-link.active {
            font-weight: bold;
            /* Outros estilos para aba ativa */
        }
        .tab-content {
            border: 1px solid #dee2e6;
            border-top: none;
            padding: 1.25rem; /* Ajuste o padding conforme necessário */
            border-bottom-left-radius: 0.375rem; /* Correspondendo ao card-radius do Bootstrap */
            border-bottom-right-radius: 0.375rem;
            background-color: #fff; /* Fundo branco para o conteúdo da aba */
        }
        .dl-horizontal-show dt {
            float: left;
            width: 160px;
            font-weight: normal;
            color: #6c757d;
            clear: left;
            text-align: right;
            padding-right: 10px;
            margin-bottom: .4rem;
        }
        .dl-horizontal-show dd {
            margin-left: 175px;
            margin-bottom: .4rem;
            font-weight: 500;
        }

        @media (max-width: 767.98px) { /* sm */
            .dl-horizontal-show dt,
            .dl-horizontal-show dd {
                width: 100%;
                float: none;
                margin-left: 0;
                text-align: left;
            }
            .dl-horizontal-show dt {
                margin-bottom: 0.1rem;
                font-weight: bold; /* Destaca o label em mobile */
            }
        }
         @media (max-width: 575.98px) { /* xs */
            .actions-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .actions-header > div { /* Div dos botões de ação */
                width: 100%;
                margin-top: 0.5rem;
            }
            .actions-header > div .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            .actions-header > div .d-inline { /* Para formulários inline */
                display: block !important;
                width: 100%;
            }
             .actions-header > div .d-inline .btn {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
<div class="container mt-0">
    {{-- CABEÇALHO DA PÁGINA E BOTÕES GLOBAIS --}}
    <div class="actions-header mb-4">
        <h1>Detalhes: Atendimento <span class="text-primary">#{{ $atendimento->id }}</span></h1>
        <div>
            <a href="{{ route('atendimentos.pdf', $atendimento->id) }}" class="btn btn-sm btn-outline-info me-1" target="_blank" title="Gerar PDF/OS">
                <i class="bi bi-printer"></i> PDF/OS
            </a>
            <a href="{{ route('atendimentos.edit', $atendimento->id) }}" class="btn btn-sm btn-outline-warning me-1" title="Edição Completa do Atendimento">
                <i class="bi bi-pencil-square"></i> Editar OS
            </a>
            <a href="{{ route('atendimentos.index') }}" class="btn btn-sm btn-outline-secondary" title="Voltar para Lista">
                <i class="bi bi-list-ul"></i> Lista
            </a>
        </div>
    </div>

    {{-- MENSAGENS DE FEEDBACK --}}
    <div id="feedbackGlobalAtendimentoShow" class="mb-3"></div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->hasBag('status_rapido_form'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="bi bi-exclamation-triangle-fill"></i> Erro ao atualizar status:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->getBag('status_rapido_form')->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        {{-- COLUNA DA ESQUERDA (PRINCIPAL) --}}
        <div class="col-lg-8">

            {{-- CARD 1: RESUMO DO ATENDIMENTO --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header"><h5 class="my-1"><i class="bi bi-person-badge-fill me-2"></i>Cliente e Aparelho</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="section-title">Dados do Cliente</h6>
                            <dl class="dl-horizontal-show">
                                <dt>Cliente:</dt>
                                <dd>
                                    @if($atendimento->cliente)
                                        <a href="{{ route('clientes.show', $atendimento->cliente->id) }}">{{ $atendimento->cliente->nome_completo }}</a>
                                    @else
                                        <span class="text-muted">Não informado</span>
                                    @endif
                                </dd>
                                <dt>CPF/CNPJ:</dt> <dd>{{ $atendimento->cliente->cpf_cnpj ?? 'N/A' }}</dd>
                                <dt>Telefone:</dt> <dd>{{ $atendimento->cliente->telefone ?? 'Não informado' }}</dd>
                                <dt>Cód. Consulta:</dt>
                                <dd>
                                    <span id="codigoConsultaParaCopiar" class="fw-bold user-select-all" style="cursor: pointer;" title="Clique para copiar">{{ $atendimento->codigo_consulta }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-1 py-0 px-1" id="btnCopiarCodigo" title="Copiar código"><i class="bi bi-clipboard"></i></button>
                                    <small id="mensagemCopiado" class="text-success ms-2" style="display: none;">Copiado!</small>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <h6 class="section-title">Dados do Aparelho</h6>
                            <dl class="dl-horizontal-show">
                                <dt>Descrição:</dt><dd>{{ $atendimento->descricao_aparelho }}</dd>
                                <dt>Data Entrada:</dt><dd>{{ $atendimento->data_entrada->format('d/m/Y H:i') }}</dd>
                                <dt>Problema Relatado:</dt><dd class="problem-box p-2 small">{{ $atendimento->problema_relatado }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 2: STATUS E AÇÕES RÁPIDAS --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header"><h5 class="my-1"><i class="bi bi-activity me-2"></i>Status e Acompanhamento</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="section-title">Situação Atual</h6>
                            <dl class="dl-horizontal-show">
                                <dt>Status Serviço:</dt>
                                <dd>
                                    <span id="statusAtualTexto" class="badge rounded-pill fs-6 {{ App\Models\Atendimento::getStatusClass($atendimento->status) }}">
                                        <i class="bi {{ App\Models\Atendimento::getStatusIcon($atendimento->status) }} me-1"></i>
                                        <span id="statusAtualNome">{{ $atendimento->status }}</span>
                                    </span>
                                </dd>
                                <dt>Status Pag.:</dt>
                                <dd>
                                    <span id="statusPagamentoTextoOuter">
                                        @include('atendimentos.partials._status_pagamento_badge', ['status_pagamento' => ($atendimento->status_pagamento ?? 'Pendente')])
                                    </span>
                                </dd>
                                <dt>Técnico Resp.:</dt>
                                <dd id="tecnicoAtualTexto">{{ $atendimento->tecnico->name ?? 'Não atribuído' }}</dd>
                                <dt>Data Conclusão:</dt>
                                <dd>
                                    {{ $atendimento->data_conclusao ? $atendimento->data_conclusao->format('d/m/Y') : ($atendimento->isFinalizadoParaLista() ? 'Concluído/Finalizado' : 'Pendente') }}
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            @can('is-internal-user')
                                <h6 class="section-title">Alteração Rápida de Status</h6>
                                <div class="p-2 border rounded bg-light-subtle mb-3">
                                    <form action="{{ route('atendimentos.atualizarStatus', $atendimento->id) }}" method="POST" id="formAtualizarStatusRapido">
                                        @csrf
                                        @method('PATCH')
                                        <div class="row g-2 align-items-center">
                                            <div class="col-12 col-md-auto mb-2 mb-md-0"><label for="status_rapido" class="form-label mb-0 fw-semibold small">Alterar Status Serviço:</label></div>
                                            <div class="col-12 col-md">
                                                <select class="form-select form-select-sm" id="status_rapido" name="status">
                                                    @foreach (App\Models\Atendimento::getPossibleStatuses() as $s)
                                                        <option value="{{ $s }}" {{ $atendimento->status == $s ? 'selected' : '' }}>{{ $s }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-auto"><button type="submit" class="btn btn-sm btn-primary w-100 w-md-auto"><i class="bi bi-check-lg"></i> Salvar</button></div>
                                        </div>
                                    </form>
                                </div>
                            @endcan
                             <div id="containerBotaoRegistrarPagamento" class="mt-2 d-grid">
                                {{-- Botão de pagamento inserido aqui pelo JavaScript --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 3: DETALHES OPERACIONAIS (COM ABAS) --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs nav-justified card-header-tabs" id="detalhesOsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="servicos-tab" data-bs-toggle="tab" data-bs-target="#servicos-content" type="button" role="tab" aria-controls="servicos-content" aria-selected="true"><i class="bi bi-list-check me-1"></i> Serviços da OS</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pecas-tab" data-bs-toggle="tab" data-bs-target="#pecas-content" type="button" role="tab" aria-controls="pecas-content" aria-selected="false"><i class="bi bi-tools me-1"></i> Peças Utilizadas</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="laudo-tab" data-bs-toggle="tab" data-bs-target="#laudo-content" type="button" role="tab" aria-controls="laudo-content" aria-selected="false"><i class="bi bi-clipboard2-pulse-fill me-1"></i> Laudo e Obs.</button>
                        </li>
                    </ul>
                </div>
                <div class="tab-content" id="detalhesOsTabContent">
                    {{-- Aba de Serviços --}}
                    <div class="tab-pane fade show active" id="servicos-content" role="tabpanel" aria-labelledby="servicos-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title mb-0">Serviços Realizados/A Realizar</h6>
                            <div>
                                @can('is-admin-or-tecnico')
                                    <button type="button" class="btn btn-success btn-sm py-0 px-1" id="adicionarServicoShow" title="Adicionar Novo Serviço">
                                        <i class="bi bi-plus-lg"></i> Add Serviço
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm py-0 px-1 ms-1" id="salvarServicosShow" title="Salvar Alterações nos Serviços">
                                        <i class="bi bi-save"></i> Salvar Serviços
                                    </button>
                                @endcan
                            </div>
                        </div>
                        <form id="formServicosDetalhadosShow">
                            <div id="servicos-detalhados-container-show">
                                @if($atendimento->servicosDetalhados && $atendimento->servicosDetalhados->isNotEmpty())
                                    @foreach($atendimento->servicosDetalhados as $index => $itemServico)
                                        @include('atendimentos.partials._item_servico_template', [
                                            'index' => $index,
                                            'itemServicoData' => $itemServico->toArray()
                                        ])
                                    @endforeach
                                @else
                                    <p id="nenhum-servico-mensagem-show" class="text-muted text-center small py-3">Nenhum serviço detalhado adicionado a esta OS ainda.</p>
                                @endif
                            </div>
                        </form>
                        <div id="feedbackServicosShow" class="mt-2 small"></div>
                    </div>
                    {{-- Aba de Peças --}}
                    <div class="tab-pane fade" id="pecas-content" role="tabpanel" aria-labelledby="pecas-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title mb-0">Peças Utilizadas no Atendimento</h6>
                            @can('is-admin-or-tecnico')
                                <a href="{{ route('saidas-estoque.create', ['atendimento_id' => $atendimento->id]) }}" class="btn btn-success btn-sm py-0 px-1" title="Adicionar Peça à OS"><i class="bi bi-plus-lg"></i> Add Peça</a>
                            @endcan
                        </div>
                        @if ($atendimento->saidasEstoque && $atendimento->saidasEstoque->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-hover mb-0">
                                    <thead><tr><th>Peça (Modelo/Marca)</th><th class="text-center">Qtd</th><th class="text-end">Subtotal</th></tr></thead>
                                    <tbody>
                                        @foreach ($atendimento->saidasEstoque as $saida)
                                        <tr>
                                            <td>
                                                <small @if(!$saida->estoque) class="text-danger" @endif>
                                                    {{ $saida->estoque->nome ?? 'Peça Inválida' }}
                                                    @if($saida->estoque && $saida->estoque->modelo_compativel) ({{ Str::limit($saida->estoque->modelo_compativel, 15) }})@endif
                                                    @if($saida->estoque && $saida->estoque->marca) [{{ Str::limit($saida->estoque->marca, 10) }}]@endif
                                                </small>
                                            </td>
                                            <td class="text-center"><small>{{ $saida->quantidade }}</small></td>
                                            <td class="text-end"><small>R${{ number_format($saida->quantidade * ($saida->estoque->preco_venda ?? 0), 2, ',', '.') }}</small></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center small py-3">Nenhuma peça utilizada neste atendimento ainda.</p>
                        @endif
                    </div>
                    {{-- Aba de Laudo e Observações --}}
                    <div class="tab-pane fade" id="laudo-content" role="tabpanel" aria-labelledby="laudo-tab">
                        <h6 class="section-title">
                            Laudo Técnico / Solução Aplicada:
                            @can('is-admin-or-tecnico')<button type="button" class="btn btn-link btn-sm p-0 ms-1" id="btnEditarLaudo" title="Editar Laudo"><i class="bi bi-pencil-square"></i></button>@endcan
                        </h6>
                        <div id="containerLaudo"><div id="textoLaudo" class="laudo-box">{{ $atendimento->laudo_tecnico ?: 'Ainda não informado.' }}</div>
                            @can('is-admin-or-tecnico')
                            <div id="formEditarLaudo" style="display:none;" class="mt-2">
                                <textarea class="form-control form-control-sm mb-2" id="inputLaudo" name="laudo_tecnico" rows="5">{{ $atendimento->laudo_tecnico }}</textarea>
                                <button type="button" class="btn btn-sm btn-success me-1" id="btnSalvarLaudo"><i class="bi bi-check-lg"></i> Salvar</button>
                                <button type="button" class="btn btn-sm btn-secondary" id="btnCancelarLaudo">Cancelar</button>
                                <div id="feedbackLaudo" class="mt-2 small d-inline-block"></div>
                            </div>@endcan
                        </div>
                        <h6 class="section-title mt-3">
                            Observações Internas:
                            @can('is-admin-or-tecnico')<button type="button" class="btn btn-link btn-sm p-0 ms-1" id="btnEditarObservacoes" title="Editar Observações"><i class="bi bi-pencil-square"></i></button>@endcan
                        </h6>
                        <div id="containerObservacoes"><div id="textoObservacoes" class="obs-box">{{ $atendimento->observacoes ?: 'Nenhuma.' }}</div>
                            @can('is-admin-or-tecnico')
                            <div id="formEditarObservacoes" style="display:none;" class="mt-2">
                                <textarea class="form-control form-control-sm mb-2" id="inputObservacoes" name="observacoes" rows="4">{{ $atendimento->observacoes }}</textarea>
                                <button type="button" class="btn btn-sm btn-success me-1" id="btnSalvarObservacoes"><i class="bi bi-check-lg"></i> Salvar</button>
                                <button type="button" class="btn btn-sm btn-secondary" id="btnCancelarObservacoes">Cancelar</button>
                                <div id="feedbackObservacoes" class="mt-2 small d-inline-block"></div>
                            </div>@endcan
                        </div>
                    </div>
                </div>
            </div>

        </div> {{-- Fim col-lg-8 --}}

        {{-- COLUNA DA DIREITA (FINANCEIRO E FINALIZAÇÃO) --}}
        <div class="col-lg-4">
            {{-- CARD: VALORES DO ATENDIMENTO --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="my-1"><i class="bi bi-currency-dollar me-2"></i>Financeiro</h5>
                    @can('is-admin')
                        <button type="button" class="btn btn-outline-primary btn-sm py-0 px-1" id="btnEditarValoresServico" title="Editar Desconto Global da OS"><i class="bi bi-pencil"></i> Edit (Desc. Global)</button>
                    @endcan
                </div>
                <div class="card-body">
                    <div id="areaExibirValores">
                        <dl class="dl-horizontal-show mb-0">
                            <dt><small>Mão de Obra:</small></dt>
                            <dd class="text-end" id="textoValorServico"><small>R$ {{ number_format($atendimento->valor_servico ?? 0, 2, ',', '.') }}</small></dd>
                            <dt><small>Desconto Global OS:</small></dt>
                            <dd class="text-end text-danger" id="textoDescontoServico"><small>- R$ {{ number_format($atendimento->desconto_servico ?? 0, 2, ',', '.') }}</small></dd>
                            <dt class="fw-semibold"><small>Subtotal Serviço:</small></dt>
                            <dd class="text-end fw-bold" id="textoSubtotalServico"><small>R$ {{ number_format($atendimento->valor_servico_liquido, 2, ',', '.') }}</small></dd>
                            <dt class="mt-2"><small>Total Peças:</small></dt>
                            <dd class="text-end mt-2" id="textoValorTotalPecas"><small>R$ {{ number_format($atendimento->valor_total_pecas, 2, ',', '.') }}</small></dd>
                            <dt class="border-top pt-2 fs-6 text-success"><small>TOTAL OS:</small></dt>
                            <dd class="text-end border-top pt-2 fs-5 fw-bolder text-success" id="textoValorTotalAtendimento">R$ {{ number_format($atendimento->valor_total_atendimento, 2, ',', '.') }}</dd>
                        </dl>
                    </div>
                    @can('is-admin')
                        <div id="formEditarValoresServico" style="display:none;" class="mt-2 pt-2 border-top">
                            <div class="mb-2">
                                <label for="inputValorServico" class="form-label form-label-sm fw-semibold">Valor Mão de Obra (R$):</label>
                                <input type="number" step="0.01" class="form-control form-control-sm bg-light" id="inputValorServico" value="{{ number_format($atendimento->valor_servico ?? 0, 2, '.', '') }}" readonly title="Este valor é a soma dos serviços detalhados. Edite os serviços para alterá-lo.">
                            </div>
                            <div class="mb-2">
                                <label for="inputDescontoServico" class="form-label form-label-sm fw-semibold">Desconto Global da OS (R$):</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" id="inputDescontoServico" value="{{ number_format($atendimento->desconto_servico ?? 0, 2, '.', '') }}">
                            </div>
                            <button type="button" class="btn btn-sm btn-success me-1" id="btnSalvarValoresServico"><i class="bi bi-check-lg"></i> Salvar Desconto</button>
                            <button type="button" class="btn btn-sm btn-secondary" id="btnCancelarValoresServico">Cancelar</button>
                            <div id="feedbackValoresServico" class="mt-1 small d-inline-block"></div>
                        </div>
                    @endcan
                </div>
            </div>

            {{-- CARD: FINALIZAÇÃO E HISTÓRICO --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header"><h5 class="my-1"><i class="bi bi-clock-history me-2"></i>Histórico e Ações</h5></div>
                <div class="card-body">
                    <dl class="dl-horizontal-show small">
                        <dt>Criado em:</dt><dd>{{ $atendimento->created_at->format('d/m/Y H:i') }}</dd>
                        <dt>Última atualização:</dt><dd>{{ $atendimento->updated_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                    @can('is-admin')
                        <form action="{{ route('atendimentos.destroy', $atendimento->id) }}" method="POST" class="d-grid mt-3">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Tem certeza que deseja excluir este atendimento? Esta ação não poderá ser desfeita e pode ter implicações no estoque e caixa se não tratada corretamente.')">
                                <i class="bi bi-trash"></i> Excluir Atendimento
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

        </div> {{-- Fim col-lg-4 --}}
    </div> {{-- Fim da row principal --}}

    {{-- MODAIS E TEMPLATES --}}
    @can('gerenciar-caixa')
        @include('atendimentos.partials._modal_registrar_pagamento', [
            'atendimento' => $atendimento,
            'formasPagamentoDisponiveis' => $formasPagamentoDisponiveis
        ])
    @endcan
    @include('atendimentos.partials._modal_confirmacao_abertura_caixa')
    <div id="atendimento-servico-item-template-show" style="display: none;">
        @include('atendimentos.partials._item_servico_template', ['index' => '__INDEX__'])
    </div>

</div> {{-- Fim container --}}
@endsection

@push('scripts')
    {{-- Todo o JavaScript existente, com atenção aos seletores dos novos elementos e abas. --}}
    <script>
        // O JavaScript completo da view original será colocado aqui,
        // com adaptações para os novos IDs e a estrutura com abas.
        // A lógica de exibição/ocultação de botões e formulários
        // precisará considerar que alguns elementos agora estão dentro de abas.
        // A função calcularESincronizarTotaisOSShow() continuará central para os valores.
        document.addEventListener('DOMContentLoaded', function () {
    // 1. OBTENÇÃO DE ELEMENTOS DO DOM E VARIÁVEIS GLOBAIS
    const feedbackGlobal = document.getElementById('feedbackGlobalAtendimentoShow');
    const atendimentoId = "{{ $atendimento->id }}";

    // Elementos da página principal que exibem os valores financeiros
    const textoValorServicoPage = document.getElementById('textoValorServico')?.querySelector('small');
    const textoDescontoServicoPage = document.getElementById('textoDescontoServico')?.querySelector('small');
    const textoSubtotalServicoPage = document.getElementById('textoSubtotalServico')?.querySelector('small');
    const textoValorTotalPecasPage = document.getElementById('textoValorTotalPecas')?.querySelector('small');
    const textoValorTotalAtendimentoPage = document.getElementById('textoValorTotalAtendimento');

    // Elementos de status na página principal
    const statusAtualTextoSpan = document.getElementById('statusAtualTexto');
    const statusAtualNomeSpan = document.getElementById('statusAtualNome');
    const statusPagamentoOuterContainer = document.getElementById('statusPagamentoTextoOuter');

    // Código de consulta
    const codigoConsultaSpan = document.getElementById('codigoConsultaParaCopiar');
    const btnCopiarCodigo = document.getElementById('btnCopiarCodigo');
    const mensagemCopiado = document.getElementById('mensagemCopiado');

    // Edição inline Laudo e Observações (dentro da aba 'laudo-content')
    const btnEditarLaudo = document.getElementById('btnEditarLaudo');
    const textoLaudoDisplay = document.getElementById('textoLaudo');
    const formEditarLaudo = document.getElementById('formEditarLaudo');
    const inputLaudo = document.getElementById('inputLaudo');
    const btnSalvarLaudo = document.getElementById('btnSalvarLaudo');
    const btnCancelarLaudo = document.getElementById('btnCancelarLaudo');
    const feedbackLaudo = document.getElementById('feedbackLaudo');

    const btnEditarObservacoes = document.getElementById('btnEditarObservacoes');
    const textoObservacoesDisplay = document.getElementById('textoObservacoes');
    const formEditarObservacoes = document.getElementById('formEditarObservacoes');
    const inputObservacoes = document.getElementById('inputObservacoes');
    const btnSalvarObservacoes = document.getElementById('btnSalvarObservacoes');
    const btnCancelarObservacoes = document.getElementById('btnCancelarObservacoes');
    const feedbackObservacoes = document.getElementById('feedbackObservacoes');

    // Edição inline de Desconto Global da OS (no card Financeiro)
    const btnEditarValoresServicoPage = document.getElementById('btnEditarValoresServico');
    const areaExibirValoresPage = document.getElementById('areaExibirValores');
    const formEditarValoresPage = document.getElementById('formEditarValoresServico');
    const inputValorServicoPage = document.getElementById('inputValorServico'); // Input de mão de obra (readonly)
    const inputDescontoServicoPage = document.getElementById('inputDescontoServico'); // Input de desconto global
    const btnSalvarValoresPage = document.getElementById('btnSalvarValoresServico');
    const btnCancelarValoresPage = document.getElementById('btnCancelarValoresServico');
    const feedbackValoresPage = document.getElementById('feedbackValoresServico');

    // Modal de Pagamento
    const modalRegistrarPagamentoElement = document.getElementById('modalRegistrarPagamento');
    const modalBootstrapInstance = modalRegistrarPagamentoElement ? new bootstrap.Modal(modalRegistrarPagamentoElement) : null;
    const formRegistrarPagamentoModal = document.getElementById('formRegistrarPagamentoAtendimento');
    const btnConfirmarPagamentoModal = document.getElementById('btnConfirmarPagamentoModal');
    const feedbackModalPagamento = document.getElementById('feedbackModalPagamento');
    const modalSubtotalServicoDisplay = document.getElementById('modalSubtotalServicoDisplay');
    const modalValorPecasDisplay = document.getElementById('modalValorPecasDisplay');
    const modalNovoValorTotalDevidoDisplay = document.getElementById('modalNovoValorTotalDevidoDisplay');
    const valorTotalOriginalDisplayModal = document.getElementById('valorTotalOriginalDisplayModal');

    // Modal de Confirmação de Abertura de Caixa
    const modalConfirmacaoAberturaCaixa = document.getElementById('modalConfirmacaoAberturaCaixa');
    const modalBootstrapConfirmacaoAbertura = modalConfirmacaoAberturaCaixa ? new bootstrap.Modal(modalConfirmacaoAberturaCaixa) : null;
    const modalConfirmarAbrirCaixaBody = document.getElementById('modalConfirmarAbrirCaixaInfo');

    // Serviços Detalhados (dentro da aba 'servicos-content')
    const containerServicosShow = document.getElementById('servicos-detalhados-container-show');
    const btnAdicionarServicoShow = document.getElementById('adicionarServicoShow');
    const btnSalvarServicosShow = document.getElementById('salvarServicosShow');
    const templateServicoShow = document.getElementById('atendimento-servico-item-template-show');
    const feedbackServicosShow = document.getElementById('feedbackServicosShow');
    const nenhumServicoMsgShow = document.getElementById('nenhum-servico-mensagem-show');
    let itemServicoOsShowIndex = containerServicosShow ? containerServicosShow.querySelectorAll('.item-servico-detalhado-row').length : 0;

    let statusGeralAtualJs = "{{ $atendimento->status }}";
    let statusPagamentoAtualJs = "{{ $atendimento->status_pagamento ?? 'Pendente' }}";
    if (statusPagamentoAtualJs === "") statusPagamentoAtualJs = "Pendente";
    const podeGerenciarCaixaJs = {{ Gate::allows('gerenciar-caixa') ? 'true' : 'false' }};

    // 2. DEFINIÇÕES DE FUNÇÕES (mantidas da versão anterior, com possíveis pequenos ajustes seletor)
    function exibirFeedbackGlobal(mensagem, tipo = 'success') {
        if (!feedbackGlobal) { console.warn("Elemento 'feedbackGlobalAtendimentoShow' não encontrado."); return; }
        feedbackGlobal.innerHTML = '';
        const alertDiv = document.createElement('div');
        const alertType = ['success', 'danger', 'warning', 'info'].includes(tipo) ? tipo : 'info';
        alertDiv.className = `alert alert-${alertType} alert-dismissible fade show`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `${mensagem}<button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>`;
        feedbackGlobal.appendChild(alertDiv);
        setTimeout(() => {
            const currentAlert = feedbackGlobal.querySelector('.alert');
            if (currentAlert && typeof bootstrap !== 'undefined' && bootstrap.Alert && bootstrap.Alert.getOrCreateInstance) {
                try { bootstrap.Alert.getOrCreateInstance(currentAlert).close(); } catch (e) { if (currentAlert.parentNode) currentAlert.parentNode.removeChild(currentAlert); }
            } else if (currentAlert && currentAlert.parentNode) { currentAlert.parentNode.removeChild(currentAlert); }
        }, 7000);
    }

    function atualizarEstadoBotaoRegistrarPagamento() {
        const containerBtnPagamento = document.getElementById('containerBotaoRegistrarPagamento');
        if (!containerBtnPagamento) { console.warn('Container do botão de pagamento não encontrado.'); return; }

        const statusGeraisPermitemPagamento = ['Pronto para entrega', 'Aguardando aprovação cliente'];
        const statusPagamentoPermiteAcao = ['Pendente', 'Parcialmente Pago'];
        const statusGeralBloqueante = ['Cancelado', 'Reprovado'];

        const permiteRegistrar = statusPagamentoPermiteAcao.includes(statusPagamentoAtualJs) &&
            statusGeraisPermitemPagamento.includes(statusGeralAtualJs) &&
            !statusGeralBloqueante.includes(statusGeralAtualJs) &&
            podeGerenciarCaixaJs;

        const mostrarDesabilitado = statusPagamentoPermiteAcao.includes(statusPagamentoAtualJs) &&
            !statusGeraisPermitemPagamento.includes(statusGeralAtualJs) &&
            !statusGeralBloqueante.includes(statusGeralAtualJs) &&
            podeGerenciarCaixaJs;

        let htmlBotao = '';
        if (permiteRegistrar) {
            htmlBotao = `<button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#modalRegistrarPagamento"><i class="bi bi-cash-coin me-1"></i> Registrar Pagamento</button>`;
        } else if (mostrarDesabilitado) {
            htmlBotao = `
            <button type="button" class="btn btn-outline-secondary w-100" disabled title="Defina os valores e avance o status do serviço para habilitar o pagamento.">
                <i class="bi bi-cash-coin me-1"></i> Registrar Pagamento
            </button>
            <small class="d-block text-muted mt-1 text-center" style="font-size: 0.8em;">
                O serviço precisa estar 'Pronto para entrega' ou 'Aguardando aprovação' para registrar o pagamento.
            </small>`;
        }
        containerBtnPagamento.innerHTML = htmlBotao;
        containerBtnPagamento.style.display = (permiteRegistrar || mostrarDesabilitado) ? 'block' : 'none';
    }

    function setupInlineEdit(config) {
        const { btnEditId, textDisplayId, formEditId, inputId, btnSaveId, btnCancelId, feedbackId, saveUrl, fieldName, placeholderText } = config;
        const btnEdit = document.getElementById(btnEditId);
        const textDisplay = document.getElementById(textDisplayId);
        const formEdit = document.getElementById(formEditId);
        const inputElement = document.getElementById(inputId);
        const btnSave = document.getElementById(btnSaveId);
        const btnCancel = document.getElementById(btnCancelId);
        const feedbackElement = document.getElementById(feedbackId);

        if (btnEdit && textDisplay && formEdit && inputElement && btnSave && btnCancel && feedbackElement) {
            btnEdit.addEventListener('click', function () {
                textDisplay.style.display = 'none'; formEdit.style.display = 'block';
                let currentText = textDisplay.innerText.trim();
                if (currentText === placeholderText || currentText === placeholderText.replace(/\n/g, '')) currentText = '';
                inputElement.value = currentText; inputElement.focus();
                feedbackElement.innerHTML = ''; btnEdit.style.display = 'none';
            });
            btnCancel.addEventListener('click', function () {
                textDisplay.style.display = 'block'; formEdit.style.display = 'none';
                feedbackElement.innerHTML = ''; btnEdit.style.display = 'inline-block';
            });
            btnSave.addEventListener('click', function () {
                const newValue = inputElement.value;
                feedbackElement.innerHTML = '<span class="text-info small">Salvando...</span>';
                btnSave.disabled = true; btnCancel.disabled = true;
                fetch(saveUrl, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                    body: JSON.stringify({ [fieldName]: newValue })
                })
                .then(response => response.json().then(data => ({ statusHttp: response.status, body: data })))
                .then(({ statusHttp, body }) => {
                    if (body.success) {
                        let displayValue = body.novos_valores && typeof body.novos_valores[fieldName] !== 'undefined' ? body.novos_valores[fieldName] : placeholderText;
                        if(fieldName === 'observacoes' || fieldName === 'laudo_tecnico'){
                             // Para campos de texto com <pre>, melhor usar innerHTML para manter quebras de linha
                            textDisplay.innerHTML = displayValue ? displayValue.replace(/\n/g, '<br>') : placeholderText;
                        } else {
                            textDisplay.innerText = displayValue;
                        }
                        exibirFeedbackGlobal(body.message, 'success');
                    } else {
                        exibirFeedbackGlobal(body.message || `Erro ao salvar ${fieldName}.`, 'danger');
                    }
                })
                .catch(error => {
                    console.error(`Erro AJAX para ${fieldName}:`, error);
                    exibirFeedbackGlobal(`Erro de comunicação ao salvar ${fieldName}.`, 'danger');
                })
                .finally(() => {
                    textDisplay.style.display = 'block'; formEdit.style.display = 'none';
                    btnSave.disabled = false; btnCancel.disabled = false;
                    btnEdit.style.display = 'inline-block'; feedbackElement.innerHTML = '';
                });
            });
        }
    }

    function executarCopia() {
        if (!codigoConsultaSpan) return;
        const textoParaCopiar = codigoConsultaSpan.innerText.trim();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(textoParaCopiar).then(() => {
                if (mensagemCopiado) { mensagemCopiado.style.display = 'inline'; setTimeout(() => { mensagemCopiado.style.display = 'none'; }, 2000); }
                if (btnCopiarCodigo) { const o = btnCopiarCodigo.innerHTML; btnCopiarCodigo.innerHTML = '<i class="bi bi-check-lg text-success"></i>'; btnCopiarCodigo.disabled = true; setTimeout(() => { btnCopiarCodigo.innerHTML = o; btnCopiarCodigo.disabled = false; }, 2000); }
            }).catch(err => { console.error('Erro ao copiar: ', err); /* alert('Não foi possível copiar.'); */ });
        } else { /* alert('Cópia automática não suportada.'); */ }
    }

    function calcularESincronizarTotaisOSShow() {
        if (!containerServicosShow) return;
        let totalServicosDetalhados = 0;
        containerServicosShow.querySelectorAll('.item-servico-detalhado-row').forEach(function(row) {
            const quantidade = parseFloat(row.querySelector('.item-servico-quantidade').value) || 0;
            const valorUnitario = parseFloat(row.querySelector('.item-servico-valor-unitario').value) || 0;
            const subtotalItem = quantidade * valorUnitario;
            const subtotalDisplayEl = row.querySelector('.item-servico-subtotal-display');
            if(subtotalDisplayEl) subtotalDisplayEl.textContent = subtotalItem.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            totalServicosDetalhados += subtotalItem;
        });

        // Atualiza o campo de Mão de Obra no card Financeiro
        if (textoValorServicoPage) {
            textoValorServicoPage.textContent = 'R$ ' + totalServicosDetalhados.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        // Atualiza o input hidden/readonly de valor_servico no form de edição de desconto global
        if(inputValorServicoPage){
            inputValorServicoPage.value = totalServicosDetalhados.toFixed(2);
        }

        // Pega o desconto global (do input se estiver editando, ou do display se não)
        const descontoGlobalOsStr = inputDescontoServicoPage && formEditarValoresPage.style.display === 'block'
                                  ? inputDescontoServicoPage.value
                                  : (textoDescontoServicoPage ? textoDescontoServicoPage.textContent.replace('- R$ ', '').replace(/\./g, '').replace(',', '.') : '0');
        const descontoGlobalOs = parseFloat(descontoGlobalOsStr.replace(',', '.')) || 0;

        const subtotalServicoLiquido = totalServicosDetalhados - descontoGlobalOs;
        const totalPecasStr = textoValorTotalPecasPage ? textoValorTotalPecasPage.textContent.replace('R$ ', '').replace(/\./g, '').replace(',', '.') : '0';
        const totalPecas = parseFloat(totalPecasStr) || 0;
        const totalOS = subtotalServicoLiquido + totalPecas;

        // Atualiza os displays no card Financeiro
        if (textoSubtotalServicoPage) {
            textoSubtotalServicoPage.textContent = 'R$ ' + subtotalServicoLiquido.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        if (textoValorTotalAtendimentoPage) {
            textoValorTotalAtendimentoPage.textContent = 'R$ ' + totalOS.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    function popularEcalcularTotaisModal() {
        if (!textoValorServicoPage || !textoDescontoServicoPage || !textoValorTotalPecasPage || !textoValorTotalAtendimentoPage ||
            !modalSubtotalServicoDisplay || !modalValorPecasDisplay || !modalNovoValorTotalDevidoDisplay || !valorTotalOriginalDisplayModal) {
            return;
        }
        let valorServicoAtual = parseFloat(textoValorServicoPage.textContent.replace('R$ ', '').replace(/\./g, '').replace(',', '.')) || 0;
        let descontoServicoAtual = parseFloat(textoDescontoServicoPage.textContent.replace('- R$ ', '').replace(/\./g, '').replace(',', '.')) || 0;
        let totalPecasAtual = parseFloat(textoValorTotalPecasPage.textContent.replace('R$ ', '').replace(/\./g, '').replace(',', '.')) || 0;
        let valorTotalOsAtual = parseFloat(textoValorTotalAtendimentoPage.textContent.replace('R$ ', '').replace(/\./g, '').replace(',', '.')) || 0;
        let subtotalServicoCalculado = valorServicoAtual - descontoServicoAtual;

        valorTotalOriginalDisplayModal.textContent = 'R$ ' + valorTotalOsAtual.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        modalSubtotalServicoDisplay.textContent = 'R$ ' + subtotalServicoCalculado.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        modalValorPecasDisplay.textContent = 'R$ ' + totalPecasAtual.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        modalNovoValorTotalDevidoDisplay.textContent = 'R$ ' + valorTotalOsAtual.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }


    // 3. EVENT LISTENERS E EXECUÇÃO INICIAL
    atualizarEstadoBotaoRegistrarPagamento(); // Estado inicial do botão de pagamento

    // Configuração dos listeners de edição inline para Laudo e Observações
    @can('is-admin-or-tecnico')
        if(btnEditarLaudo) {
            setupInlineEdit({
                btnEditId: 'btnEditarLaudo', textDisplayId: 'textoLaudo', formEditId: 'formEditarLaudo',
                inputId: 'inputLaudo', btnSaveId: 'btnSalvarLaudo', btnCancelId: 'btnCancelarLaudo',
                feedbackId: 'feedbackLaudo',
                saveUrl: `{{ route("atendimentos.atualizarCampoAjax", ["atendimento" => $atendimento->id, "campo" => "laudo_tecnico"]) }}`,
                fieldName: 'laudo_tecnico', placeholderText: 'Ainda não informado.'
            });
        }
        if(btnEditarObservacoes) {
            setupInlineEdit({
                btnEditId: 'btnEditarObservacoes', textDisplayId: 'textoObservacoes', formEditId: 'formEditarObservacoes',
                inputId: 'inputObservacoes', btnSaveId: 'btnSalvarObservacoes', btnCancelId: 'btnCancelarObservacoes',
                feedbackId: 'feedbackObservacoes',
                saveUrl: `{{ route("atendimentos.atualizarCampoAjax", ["atendimento" => $atendimento->id, "campo" => "observacoes"]) }}`,
                fieldName: 'observacoes', placeholderText: 'Nenhuma.'
            });
        }
    @endcan

    // Listener para o formulário de atualização rápida de status
    const formAtualizarStatusRapido = document.getElementById('formAtualizarStatusRapido');
    if (formAtualizarStatusRapido && statusAtualTextoSpan && statusAtualNomeSpan) {
        formAtualizarStatusRapido.addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(this);
            const url = this.action;
            const btnSubmitStatus = this.querySelector('button[type="submit"]');
            const originalButtonHtml = btnSubmitStatus.innerHTML;
            btnSubmitStatus.disabled = true;
            btnSubmitStatus.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Salvando...';
            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(response => response.json().then(data => ({ statusHttp: response.status, body: data })))
            .then(({ statusHttp, body }) => {
                if (body.success || body.feedback_tipo === 'info' || body.feedback_tipo === 'warning') {
                    exibirFeedbackGlobal(body.message, body.feedback_tipo || 'success');
                    if (body.novo_status_geral && body.novo_status_geral_classe_completa && body.novo_status_geral_icon) {
                        statusAtualNomeSpan.textContent = body.novo_status_geral;
                        statusAtualTextoSpan.className = 'badge rounded-pill fs-6 ' + body.novo_status_geral_classe_completa;
                        const iconElement = statusAtualTextoSpan.querySelector('i');
                        if (iconElement) iconElement.className = body.novo_status_geral_icon + ' me-1';
                        statusGeralAtualJs = body.novo_status_geral;
                        atualizarEstadoBotaoRegistrarPagamento();
                    }
                } else {
                    exibirFeedbackGlobal(body.message || 'Ocorreu um erro ao atualizar o status.', 'danger');
                }
            })
            .catch(error => {
                console.error('Erro AJAX status rápido:', error);
                exibirFeedbackGlobal('Erro de comunicação ao atualizar status.', 'danger');
            })
            .finally(() => {
                btnSubmitStatus.disabled = false;
                btnSubmitStatus.innerHTML = originalButtonHtml;
            });
        });
    }

    // Listeners para copiar código de consulta
    if (codigoConsultaSpan) codigoConsultaSpan.addEventListener('click', executarCopia);
    if (btnCopiarCodigo) btnCopiarCodigo.addEventListener('click', executarCopia);

    // Inicializar Tooltips do Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl); });

    // Listeners para edição de valores globais (Desconto)
    if (btnEditarValoresServicoPage && areaExibirValoresPage && formEditarValoresPage) {
        btnEditarValoresServicoPage.addEventListener('click', function () {
            areaExibirValoresPage.style.display = 'none'; formEditarValoresPage.style.display = 'block';
            // Preenche o input de valor_servico (readonly) com o valor atual dos itens
            if (inputValorServicoPage && textoValorServicoPage) inputValorServicoPage.value = (parseFloat(textoValorServicoPage.textContent.replace('R$ ', '').replace(/\./g, '').replace(',', '.')) || 0).toFixed(2);
            // Preenche o input de desconto com o valor atual
            if (inputDescontoServicoPage && textoDescontoServicoPage) inputDescontoServicoPage.value = (parseFloat(textoDescontoServicoPage.textContent.replace('- R$ ', '').replace(/\./g, '').replace(',', '.')) || 0).toFixed(2);
            if (feedbackValoresPage) feedbackValoresPage.innerHTML = '';
            btnEditarValoresServicoPage.style.display = 'none';
        });
        if (btnCancelarValoresPage) {
            btnCancelarValoresPage.addEventListener('click', function () {
                areaExibirValoresPage.style.display = 'block'; formEditarValoresPage.style.display = 'none';
                if (feedbackValoresPage) feedbackValoresPage.innerHTML = '';
                btnEditarValoresServicoPage.style.display = 'inline-block';
            });
        }
        if (btnSalvarValoresPage) {
            btnSalvarValoresPage.addEventListener('click', function () {
                // O valor_servico a ser enviado é a soma dos itens, já presente no inputValorServicoPage (readonly)
                const valorServicoSomaItens = parseFloat(inputValorServicoPage.value) || 0;
                const novoDescontoServicoGlobal = parseFloat(inputDescontoServicoPage.value) || 0;

                if (feedbackValoresPage) feedbackValoresPage.innerHTML = '<span class="text-info small">Salvando...</span>';
                btnSalvarValoresPage.disabled = true; if (btnCancelarValoresPage) btnCancelarValoresPage.disabled = true;

                fetch(`{{ route("atendimentos.atualizarValoresServicoAjax", $atendimento->id) }}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                    body: JSON.stringify({
                        valor_servico: valorServicoSomaItens.toFixed(2), // Envia o valor da soma dos itens como 'valor_servico'
                        desconto_servico: novoDescontoServicoGlobal.toFixed(2)
                    })
                })
                .then(response => response.json().then(data => ({ statusHttp: response.status, body: data })))
                .then(({ statusHttp, body }) => {
                    if (body.success && body.novos_valores) {
                        // Atualiza os displays da página com os novos valores retornados pelo backend
                        if (textoValorServicoPage) textoValorServicoPage.textContent = body.novos_valores.valor_servico; // Mão de obra (soma dos serviços)
                        if (textoDescontoServicoPage) textoDescontoServicoPage.textContent = body.novos_valores.desconto_servico; // Desconto global
                        if (textoSubtotalServicoPage) textoSubtotalServicoPage.textContent = body.novos_valores.subtotal_servico; // Mão de obra - Desconto global
                        // O total de peças não é alterado aqui, mas o total do atendimento sim
                        if (textoValorTotalAtendimentoPage) textoValorTotalAtendimentoPage.textContent = body.novos_valores.valor_total_atendimento;
                        exibirFeedbackGlobal(body.message, 'success');
                        // Não precisa chamar calcularESincronizarTotaisOSShow() aqui pois os valores já foram atualizados pelo backend
                        // e os displays refletem isso.
                    } else {
                        exibirFeedbackGlobal(body.message || 'Erro ao salvar desconto global.', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Erro AJAX (salvar desconto global):', error);
                    exibirFeedbackGlobal('Erro de comunicação ao salvar desconto global.', 'danger');
                })
                .finally(() => {
                    areaExibirValoresPage.style.display = 'block'; formEditarValoresPage.style.display = 'none';
                    btnSalvarValoresPage.disabled = false; if (btnCancelarValoresPage) btnCancelarValoresPage.disabled = false;
                    btnEditarValoresServicoPage.style.display = 'inline-block'; if (feedbackValoresPage) feedbackValoresPage.innerHTML = '';
                });
            });
        }
    }

    // Serviços Detalhados (Aba)
    if (btnAdicionarServicoShow && templateServicoShow && containerServicosShow) {
        btnAdicionarServicoShow.addEventListener('click', function() {
            if (nenhumServicoMsgShow) nenhumServicoMsgShow.style.display = 'none';
            let novoItemHTML = templateServicoShow.innerHTML.replace(/__INDEX__/g, itemServicoOsShowIndex);
            containerServicosShow.insertAdjacentHTML('beforeend', novoItemHTML);
            const novaLinha = containerServicosShow.querySelector(`.item-servico-detalhado-row[data-index="${itemServicoOsShowIndex}"]`);
            if(novaLinha) {
                novaLinha.querySelector('.item-servico-quantidade').addEventListener('input', calcularESincronizarTotaisOSShow);
                novaLinha.querySelector('.item-servico-valor-unitario').addEventListener('input', calcularESincronizarTotaisOSShow);
            }
            itemServicoOsShowIndex++;
            calcularESincronizarTotaisOSShow(); // Recalcula após adicionar
        });
    }

    if (containerServicosShow) {
        containerServicosShow.addEventListener('click', function(event) {
            if (event.target.closest('.remover-item-servico-detalhado')) {
                event.target.closest('.item-servico-detalhado-row').remove();
                if (containerServicosShow.querySelectorAll('.item-servico-detalhado-row').length === 0 && nenhumServicoMsgShow) {
                    nenhumServicoMsgShow.style.display = 'block';
                }
                calcularESincronizarTotaisOSShow(); // Recalcula após remover
            }
        });
        // Adiciona listeners para os inputs de quantidade e valor unitário dos itens de serviço
        containerServicosShow.querySelectorAll('.item-servico-quantidade, .item-servico-valor-unitario').forEach(input => {
            input.addEventListener('input', calcularESincronizarTotaisOSShow);
        });
    }

    // Listener para o campo de desconto global da OS (quando alterado no form de edição de desconto)
    if(inputDescontoServicoPage) {
        inputDescontoServicoPage.addEventListener('input', calcularESincronizarTotaisOSShow);
    }

    if (btnSalvarServicosShow && containerServicosShow && feedbackServicosShow) {
        btnSalvarServicosShow.addEventListener('click', function() {
            let servicosArray = [];
            document.querySelectorAll('#servicos-detalhados-container-show .item-servico-detalhado-row').forEach(function(row) {
                servicosArray.push({
                    id: row.querySelector('input[name^="servicos_detalhados"][name$="[id]"]').value,
                    descricao_servico: row.querySelector('.item-servico-descricao').value,
                    quantidade: row.querySelector('.item-servico-quantidade').value,
                    valor_unitario: row.querySelector('.item-servico-valor-unitario').value
                });
            });

            feedbackServicosShow.innerHTML = '<span class="text-info small">Salvando serviços...</span>';
            btnSalvarServicosShow.disabled = true;

            fetch(`{{ route('atendimentos.atualizarServicosDetalhadosAjax', $atendimento->id) }}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                body: JSON.stringify({ servicos_detalhados: servicosArray })
            })
            .then(response => response.json().then(data => ({ statusHttp: response.status, body: data })))
            .then(({ statusHttp, body }) => {
                if (body.success) {
                    feedbackServicosShow.innerHTML = `<span class="text-success small">${body.message}</span>`;
                    // Atualiza os displays de valores na página com os totais retornados pelo backend
                    if (body.novos_valores_atendimento) {
                        if (textoValorServicoPage) textoValorServicoPage.textContent = body.novos_valores_atendimento.valor_servico_formatado;
                        if (textoDescontoServicoPage) textoDescontoServicoPage.textContent = body.novos_valores_atendimento.desconto_servico_formatado;
                        if (textoSubtotalServicoPage) textoSubtotalServicoPage.textContent = body.novos_valores_atendimento.subtotal_servico_formatado;
                        if (textoValorTotalAtendimentoPage) textoValorTotalAtendimentoPage.textContent = body.novos_valores_atendimento.valor_total_atendimento_formatado;
                    }
                    // Recarrega os itens de serviço para garantir que os IDs estão corretos (se houver novos itens)
                    if(body.itens_servico_atualizados && containerServicosShow && templateServicoShow){
                        containerServicosShow.innerHTML = '';
                        itemServicoOsShowIndex = 0;
                        if(body.itens_servico_atualizados.length > 0){
                            if(nenhumServicoMsgShow) nenhumServicoMsgShow.style.display = 'none';
                            body.itens_servico_atualizados.forEach(itemData => {
                                let templateHTML = templateServicoShow.innerHTML.replace(/__INDEX__/g, itemServicoOsShowIndex);
                                containerServicosShow.insertAdjacentHTML('beforeend', templateHTML);
                                const novaLinha = containerServicosShow.querySelector(`.item-servico-detalhado-row[data-index="${itemServicoOsShowIndex}"]`);
                                if(novaLinha){
                                    novaLinha.querySelector('input[name^="servicos_detalhados"][name$="[id]"]').value = itemData.id;
                                    novaLinha.querySelector('.item-servico-descricao').value = itemData.descricao_servico;
                                    novaLinha.querySelector('.item-servico-quantidade').value = itemData.quantidade;
                                    novaLinha.querySelector('.item-servico-valor-unitario').value = parseFloat(itemData.valor_unitario).toFixed(2);
                                    novaLinha.querySelector('.item-servico-subtotal-display').textContent = parseFloat(itemData.subtotal_servico).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    // Reatribui listeners para os novos campos
                                    novaLinha.querySelector('.item-servico-quantidade').addEventListener('input', calcularESincronizarTotaisOSShow);
                                    novaLinha.querySelector('.item-servico-valor-unitario').addEventListener('input', calcularESincronizarTotaisOSShow);
                                }
                                itemServicoOsShowIndex++;
                            });
                        } else {
                            if(nenhumServicoMsgShow) nenhumServicoMsgShow.style.display = 'block';
                        }
                    }
                    calcularESincronizarTotaisOSShow(); // Garante que os totais gerais são atualizados
                } else {
                    let errorMsg = body.message || 'Erro ao salvar serviços.';
                    if (body.errors) {
                        errorMsg += '<ul class="mb-0 mt-1 small text-danger">';
                        for (const field in body.errors) { body.errors[field].forEach(err => errorMsg += `<li>${err}</li>`);}
                        errorMsg += '</ul>';
                    }
                    feedbackServicosShow.innerHTML = `<div class="alert alert-danger p-2 small">${errorMsg}</div>`;
                }
            })
            .catch(error => {
                console.error('Erro AJAX ao salvar serviços detalhados:', error);
                feedbackServicosShow.innerHTML = '<span class="text-danger small">Erro de comunicação ao salvar serviços.</span>';
            })
            .finally(() => {
                btnSalvarServicosShow.disabled = false;
                setTimeout(() => { if(feedbackServicosShow) feedbackServicosShow.innerHTML = ''; }, 5000);
            });
        });
    }

    // Lógica do Modal de Pagamento e Verificação de Caixa
    const containerBotaoPagamento = document.getElementById('containerBotaoRegistrarPagamento');
    if (containerBotaoPagamento && modalBootstrapInstance && modalBootstrapConfirmacaoAbertura) {
        containerBotaoPagamento.addEventListener('click', function(event) {
            const targetButton = event.target.closest('button[data-bs-target="#modalRegistrarPagamento"]');
            if (!targetButton) { return; }
            event.preventDefault();
            const valorTotalOsTexto = textoValorTotalAtendimentoPage ? textoValorTotalAtendimentoPage.textContent : 'R$ 0,00';
            const valorTotalOsFloat = parseFloat(valorTotalOsTexto.replace('R$ ', '').replace(/\./g, '').replace(',', '.')) || 0;

            fetch("{{ route('caixa.verificarStatusAjax') }}")
            .then(response => response.json())
            .then(data => {
                if (data.caixa_aberto) {
                    popularEcalcularTotaisModal(); // Popula o modal com os valores atuais da OS
                    modalBootstrapInstance.show();
                } else {
                    if (modalConfirmarAbrirCaixaBody) {
                        modalConfirmarAbrirCaixaBody.innerHTML = `
                            <p>Nenhum caixa aberto no momento.</p>
                            <p>O valor de <strong>${valorTotalOsTexto}</strong> referente a este atendimento precisa ser lançado.</p>
                            <p>Deseja abrir um novo caixa agora?</p>`;
                        const btnConfirmarAberturaCaixa = document.getElementById('btnConfirmarAberturaCaixa');
                        if (btnConfirmarAberturaCaixa) {
                            const novoBtn = btnConfirmarAberturaCaixa.cloneNode(true);
                            btnConfirmarAberturaCaixa.parentNode.replaceChild(novoBtn, btnConfirmarAberturaCaixa);
                            novoBtn.addEventListener('click', function() {
                                window.location.href = "{{ route('caixa.create') }}"; // Simplificado para redirecionar para abrir caixa
                            });
                        }
                        modalBootstrapConfirmacaoAbertura.show();
                    }
                }
            })
            .catch(error => {
                console.error('Erro ao verificar status do caixa:', error);
                exibirFeedbackGlobal('Não foi possível verificar o status do caixa. Tente novamente.', 'danger');
            });
        });
    }

    // Submissão do Formulário do Modal de Pagamento
    if (formRegistrarPagamentoModal && btnConfirmarPagamentoModal) {
        formRegistrarPagamentoModal.addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(formRegistrarPagamentoModal);
            // É importante que o modal envie os valores corretos de serviço e desconto
            // que estão atualmente na OS, não os que estavam quando a página carregou,
            // pois podem ter sido alterados pelos "Serviços Detalhados" ou "Desconto Global".
            // Os accessors do Model Atendimento (valor_servico, desconto_servico, etc.) serão usados no backend
            // após ele buscar o $atendimento. O que precisamos garantir é que o $atendimento no backend
            // tenha os valores corretos de servicosDetalhados e desconto_servico ANTES do cálculo do caixa.
            // O método registrarPagamentoAjax no AtendimentoController já recebe valor_servico e desconto_servico
            // do formulário do modal. Se esses campos não existem no modal, eles precisam ser adicionados
            // ou o controller precisa buscar os valores atuais do $atendimento->valor_servico e $atendimento->desconto_servico.
            // Para simplificar, vamos assumir que o backend pega os valores do $atendimento já atualizados.
            // A função popularEcalcularTotaisModal já coloca os valores atuais da OS no modal,
            // então precisamos garantir que esses valores sejam submetidos.
            // Adicionando os valores atuais de serviço e desconto ao FormData
            const valorServicoFinal = parseFloat(textoValorServicoPage.textContent.replace('R$ ', '').replace(/\./g, '').replace(',', '.')) || 0;
            const descontoServicoFinal = parseFloat(textoDescontoServicoPage.textContent.replace('- R$ ', '').replace(/\./g, '').replace(',', '.')) || 0;

            formData.append('valor_servico', valorServicoFinal.toFixed(2));
            formData.append('desconto_servico', descontoServicoFinal.toFixed(2));


            const url = "{{ route('atendimentos.registrarPagamentoAjax', $atendimento->id) }}";
            const originalButtonHtml = btnConfirmarPagamentoModal.innerHTML;
            btnConfirmarPagamentoModal.disabled = true;
            btnConfirmarPagamentoModal.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Processando...';
            if (feedbackModalPagamento) feedbackModalPagamento.innerHTML = '';

            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                body: formData
            })
            .then(response => response.json().then(data => ({ statusHttp: response.status, body: data })))
            .then(({ statusHttp, body }) => {
                if (body.success) {
                    if (feedbackModalPagamento) feedbackModalPagamento.innerHTML = `<div class="alert alert-success small p-2">${body.message}</div>`;
                    // Atualiza todos os displays da página com os novos valores
                    if (body.novos_valores_atendimento) {
                        if (textoValorServicoPage) textoValorServicoPage.textContent = body.novos_valores_atendimento.valor_servico;
                        if (textoDescontoServicoPage) textoDescontoServicoPage.textContent = body.novos_valores_atendimento.desconto_servico;
                        if (textoSubtotalServicoPage) textoSubtotalServicoPage.textContent = body.novos_valores_atendimento.subtotal_servico;
                        if (textoValorTotalPecasPage && body.novos_valores_atendimento.valor_total_pecas) {
                             textoValorTotalPecasPage.textContent = body.novos_valores_atendimento.valor_total_pecas;
                        }
                        if (textoValorTotalAtendimentoPage) textoValorTotalAtendimentoPage.textContent = body.novos_valores_atendimento.valor_total_atendimento;
                    }
                    // Atualiza status de pagamento
                    if (body.novo_status_pagamento_html && statusPagamentoOuterContainer) {
                        statusPagamentoOuterContainer.innerHTML = body.novo_status_pagamento_html;
                        if (body.novo_status_pagamento_texto) statusPagamentoAtualJs = body.novo_status_pagamento_texto;
                    }
                    // Atualiza status do serviço (se mudou)
                    if (body.novo_status_servico_texto && body.novo_status_servico_html && statusAtualTextoSpan && statusAtualNomeSpan) {
                        statusAtualNomeSpan.textContent = body.novo_status_servico_texto;
                        statusAtualTextoSpan.className = ''; // Limpa classes antigas
                        statusAtualTextoSpan.classList.add('badge', 'rounded-pill', 'fs-6');
                        // Extrai e aplica as classes do HTML do badge retornado
                        const classesStatusServico = body.novo_status_servico_html.match(/class="([^"]+)"/);
                        if (classesStatusServico && classesStatusServico[1]) {
                             classesStatusServico[1].split(' ').forEach(cls => {
                                if(!statusAtualTextoSpan.classList.contains(cls)) statusAtualTextoSpan.classList.add(cls);
                             });
                        }
                        // Extrai e aplica o ícone do HTML do badge retornado
                        const iconMatchServico = body.novo_status_servico_html.match(/<i class="([^"]+) me-1"><\/i>/);
                        const iconElementServico = statusAtualTextoSpan.querySelector('i');
                        if(iconMatchServico && iconElementServico){
                            iconElementServico.className = iconMatchServico[1] + ' me-1';
                        } else if (iconElementServico && body.novo_status_servico_html.includes('bi-')) {
                            // Fallback para tentar pegar a classe do ícone se a regex falhar mas um ícone existir
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = body.novo_status_servico_html;
                            const iTag = tempDiv.querySelector('i.bi');
                            if(iTag) iconElementServico.className = iTag.className;
                        }
                        statusGeralAtualJs = body.novo_status_servico_texto;
                    }
                    // Atualiza observações
                    if (body.observacoes_atualizadas && textoObservacoesDisplay) {
                        textoObservacoesDisplay.innerHTML = body.observacoes_atualizadas.replace(/\n/g, '<br>');
                    }
                    atualizarEstadoBotaoRegistrarPagamento(); // Atualiza o estado do botão de pagamento principal
                    setTimeout(() => {
                        if (modalBootstrapInstance) modalBootstrapInstance.hide();
                        exibirFeedbackGlobal(body.message, 'success'); // Mostra feedback global na página
                    }, 5000);
                } else {
                    let errorMessages = body.message || 'Ocorreu um erro.';
                    if (body.errors) {
                        errorMessages += '<ul class="mb-0 mt-2 small text-start">';
                        for (const field in body.errors) { body.errors[field].forEach(error => { errorMessages += `<li>${error}</li>`; }); }
                        errorMessages += '</ul>';
                    }
                    if (feedbackModalPagamento) { feedbackModalPagamento.innerHTML = `<div class="alert alert-danger p-2">${errorMessages}</div>`; }
                }
            })
            .catch(error => {
                console.error('Erro no fetch do modal:', error);
                if (feedbackModalPagamento) { feedbackModalPagamento.innerHTML = `<div class="alert alert-danger">Erro de comunicação. Tente novamente.</div>`; }
            })
            .finally(() => {
                btnConfirmarPagamentoModal.disabled = false;
                btnConfirmarPagamentoModal.innerHTML = '<i class="bi bi-check-circle-fill"></i> Confirmar Pagamento';
            });
        });
    }

    // Chamada inicial para calcular totais (caso haja itens de serviço ao carregar a página)
    if (containerServicosShow && (containerServicosShow.querySelectorAll('.item-servico-detalhado-row').length > 0 || (nenhumServicoMsgShow && nenhumServicoMsgShow.style.display === 'none'))) {
        calcularESincronizarTotaisOSShow();
    }

}); // Fim do DOMContentLoaded
    </script>
@endpush
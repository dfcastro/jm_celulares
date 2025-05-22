@extends('layouts.app')

@section('title', 'Detalhes do Atendimento #' . $atendimento->id)

@push('styles')
    <style>
        .card-header h5 {
            font-weight: 500;
        }

        .dl-horizontal dt {
            float: left;
            width: 180px;
            font-weight: normal;
            color: #6c757d;
            clear: left;
        }

        .dl-horizontal dd {
            margin-left: 200px;
            margin-bottom: .4rem;
        }

        .badge.fs-6 {
            font-size: 0.9rem !important;
            padding: .4em .7em;
        }

        .bg-light-subtle {
            background-color: #f8f9fa !important;
        }

        /* Um cinza mais claro para consistência */
        .section-title {
            margin-bottom: 0.75rem;
            font-weight: bold;
            color: #495057;
            font-size: 1.1rem;
        }

        .problem-box,
        .laudo-box,
        .obs-box {
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            background-color: #fdfdfd;
            white-space: pre-wrap;
            font-size: 0.9em;
            min-height: 60px;
            margin-bottom: 1rem;
        }

        .actions-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            /* Para que os botões quebrem a linha em telas menores */
            gap: 0.5rem;
            /* Espaçamento entre os botões */
        }

        .valor-destaque {
            font-size: 1.2em;
            font-weight: bold;
        }

        @media (max-width: 767.98px) {

            /* Abaixo de tablets (Bootstrap md breakpoint) */
            #statusAtualTexto.badge {
                /* Usando o ID para ser específico */
                font-size: 0.8rem !important;
                /* Reduz o tamanho da fonte do badge */
                padding: .3em .6em !important;
                /* Reduz o padding interno do badge */
                white-space: normal;
                /* Permite que o texto dentro do badge quebre linha se necessário */
                text-align: left;
                /* Alinha o texto quebrado à esquerda dentro do badge */
                display: inline-block;
                /* Garante que o badge se comporte bem com quebra de linha interna */
                max-width: 100%;
                /* Garante que o badge não ultrapasse seu contêiner */
            }

            #statusAtualTexto.badge .bi {
                font-size: 0.9em;
                /* Reduz um pouco o ícone se necessário */
            }
        }

        @media (max-width: 575.98px) {

            /* Telas muito pequenas (Bootstrap sm breakpoint) */
            #statusAtualTexto.badge {
                font-size: 0.75rem !important;
                /* Ainda menor para telas extra pequenas */
                padding: .25em .5em !important;
            }

            /* Se o dt e dd estiverem lado a lado e causando problemas em xs */
            .dl-horizontal-responsive dt,
            .dl-horizontal-responsive dd {
                width: 100% !important;
                float: none !important;
                margin-left: 0 !important;
                text-align: left !important;
                /* Garante alinhamento à esquerda */
            }

            .dl-horizontal-responsive dt {
                margin-bottom: 0.1rem;
                font-weight: bold;
            }

            .dl-horizontal-responsive dd {
                margin-bottom: 0.5rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container mt-0">
        <div class="actions-header mb-3">
            <h1>Detalhes: Atendimento <span class="text-primary">#{{ $atendimento->id }}</span></h1>
            <div>
                <a href="{{ route('atendimentos.pdf', $atendimento->id) }}" class="btn btn-sm btn-outline-info me-1"
                    target="_blank" title="Gerar PDF/OS">
                    <i class="bi bi-printer"></i> PDF/OS
                </a>
                <a href="{{ route('atendimentos.edit', $atendimento->id) }}" class="btn btn-sm btn-outline-warning me-1"
                    title="Edição Completa">
                    <i class="bi bi-pencil-square"></i> Editar Tudo
                </a>
                <a href="{{ route('atendimentos.index') }}" class="btn btn-sm btn-outline-secondary"
                    title="Voltar para Lista">
                    <i class="bi bi-list-ul"></i> Lista
                </a>
            </div>
        </div>

        {{-- Mensagens de feedback --}}
        <div id="feedbackGlobalAtendimentoShow" class="mb-3"></div>
        @if(session('error_pagamento'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error_pagamento') }}
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
            {{-- COLUNA DA ESQUERDA (MAIS LARGA) --}}
            <div class="col-lg-8">
                {{-- CARD 1: INFORMAÇÕES PRINCIPAIS E STATUS --}}
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <h5 class="my-1"><i class="bi bi-info-circle-fill me-2"></i>Informações do Atendimento</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="section-title">Dados do Cliente</h6>
                                <dl class="dl-horizontal">
                                    <dt>Cliente:</dt>
                                    <dd>
                                        @if($atendimento->cliente)
                                            <a
                                                href="{{ route('clientes.show', $atendimento->cliente->id) }}">{{ $atendimento->cliente->nome_completo }}</a>
                                        @else
                                            <span class="text-muted">Não informado</span>
                                        @endif
                                    </dd>
                                    <dt>CPF/CNPJ:</dt>
                                    <dd>{{ $atendimento->cliente->cpf_cnpj ?? 'N/A' }}</dd>
                                    <dt>Telefone:</dt>
                                    <dd>{{ $atendimento->cliente->telefone ?? 'Não informado' }}</dd>
                                    <dt>Cód. Consulta:</dt>
                                    <dd>
                                        <span id="codigoConsultaParaCopiar" class="fw-bold user-select-all"
                                            style="cursor: pointer;"
                                            title="Clique para copiar">{{ $atendimento->codigo_consulta }}</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary ms-1 py-0 px-1"
                                            id="btnCopiarCodigo" title="Copiar código"><i
                                                class="bi bi-clipboard"></i></button>
                                        <small id="mensagemCopiado" class="text-success ms-2"
                                            style="display: none;">Copiado!</small>
                                    </dd>
                                </dl>

                                <h6 class="section-title mt-3">Dados do Aparelho</h6>
                                <dl class="dl-horizontal">
                                    <dt>Descrição:</dt>
                                    <dd>{{ $atendimento->descricao_aparelho }}</dd>
                                    <dt>Data Entrada:</dt>
                                    <dd>{{ $atendimento->data_entrada->format('d/m/Y H:i') }}</dd>
                                    <dt>Problema Relatado:</dt>
                                    <dd class="problem-box">{{ $atendimento->problema_relatado }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <h6 class="section-title">Status e Responsáveis</h6>
                                {{-- Removido o <dl class="dl-horizontal dl-horizontal-responsive"> --}}

                                    <div class="row mb-1"> {{-- mb-1 ou mb-2 para espaçamento entre linhas --}}
                                        <dt class="col-5 col-sm-4 col-md-5 col-lg-4 fw-normal text-muted small">Status
                                            Serviço:</dt>
                                        <dd class="col-7 col-sm-8 col-md-7 col-lg-8 mb-0">
                                            <span id="statusAtualTexto"
                                                class="badge rounded-pill fs-6 {{ App\Models\Atendimento::getStatusClass($atendimento->status) }}">
                                                <i
                                                    class="bi {{ App\Models\Atendimento::getStatusIcon($atendimento->status) }} me-1"></i>
                                                <span id="statusAtualNome">{{ $atendimento->status }}</span>
                                            </span>
                                        </dd>
                                    </div>

                                    <div class="row mb-1">
                                        <dt class="col-5 col-sm-4 col-md-5 col-lg-4 fw-normal text-muted small">Status Pag.:
                                        </dt>
                                        <dd class="col-7 col-sm-8 col-md-7 col-lg-8 mb-0">
                                            <span id="statusPagamentoTextoOuter">
                                                @php
                                                    // Garante que $status_para_badge nunca seja null para o partial
                                                    $status_para_badge = $atendimento->status_pagamento ?? 'Pendente';
                                                @endphp
                                                @include('atendimentos.partials._status_pagamento_badge', ['status_pagamento' => $status_para_badge])
                                            </span>
                                        </dd>
                                    </div>

                                    <div class="row mb-1">
                                        <dt class="col-5 col-sm-4 col-md-5 col-lg-4 fw-normal text-muted small">Técnico
                                            Resp.:</dt>
                                        <dd class="col-7 col-sm-8 col-md-7 col-lg-8 mb-0" id="tecnicoAtualTexto">
                                            {{ $atendimento->tecnico->name ?? 'Não atribuído' }}
                                        </dd>
                                    </div>

                                    <div class="row"> {{-- Último item, pode não precisar de mb-1 --}}
                                        <dt class="col-5 col-sm-4 col-md-5 col-lg-4 fw-normal text-muted small">Data
                                            Conclusão:</dt>
                                        <dd class="col-7 col-sm-8 col-md-7 col-lg-8 mb-0">
                                            {{ $atendimento->data_conclusao ? $atendimento->data_conclusao->format('d/m/Y') : ($atendimento->isFinalizadoParaLista() ? 'Concluído/Finalizado' : 'Pendente') }}
                                        </dd>
                                    </div>

                                    {{-- O formulário de atualização rápida de status viria depois disso --}}
                                    @can('is-internal-user')
                                        <div class="mt-3 mb-3 p-2 border rounded bg-light-subtle"> {{-- Adicionado mt-3 para
                                            separar do dl --}}
                                            <form action="{{ route('atendimentos.atualizarStatus', $atendimento->id) }}"
                                                method="POST" id="formAtualizarStatusRapido">
                                                @csrf
                                                @method('PATCH')
                                                <div class="row g-2 align-items-center">
                                                    <div class="col-12 col-md-auto mb-2 mb-md-0">
                                                        <label for="status_rapido"
                                                            class="form-label mb-0 fw-semibold small">Alterar Status
                                                            Serviço:</label>
                                                    </div>
                                                    <div class="col-12 col-md">
                                                        <select class="form-select form-select-sm" id="status_rapido"
                                                            name="status">
                                                            @foreach (App\Models\Atendimento::getPossibleStatuses() as $s)
                                                                <option value="{{ $s }}" {{ $atendimento->status == $s ? 'selected' : '' }}>{{ $s }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-12 col-md-auto">
                                                        <button type="submit" class="btn btn-sm btn-primary w-100 w-md-auto">
                                                            <i class="bi bi-check-lg"></i> Salvar
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    @endcan

                                    <div id="containerBotaoRegistrarPagamento" class="mt-3" style="display: display;">
                                        {{-- Conteúdo (botão ativo/desabilitado) inserido pelo JS --}}
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: DIAGNÓSTICO E SOLUÇÃO --}}
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <h5 class="my-1"><i class="bi bi-clipboard2-pulse-fill me-2"></i>Diagnóstico e Solução</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="section-title">
                            Laudo Técnico / Solução Aplicada:
                            @can('is-admin-or-tecnico')
                                <button type="button" class="btn btn-link btn-sm p-0 ms-1" id="btnEditarLaudo"
                                    title="Editar Laudo"><i class="bi bi-pencil-square"></i></button>
                            @endcan
                        </h6>
                        <div id="containerLaudo">
                            <div id="textoLaudo" class="laudo-box">
                                {{ $atendimento->laudo_tecnico ?: 'Ainda não informado.' }}
                            </div>
                            @can('is-admin-or-tecnico')
                                <div id="formEditarLaudo" style="display:none;" class="mt-2">
                                    <textarea class="form-control form-control-sm mb-2" id="inputLaudo" name="laudo_tecnico"
                                        rows="5">{{ $atendimento->laudo_tecnico }}</textarea>
                                    <button type="button" class="btn btn-sm btn-success me-1" id="btnSalvarLaudo"><i
                                            class="bi bi-check-lg"></i> Salvar</button>
                                    <button type="button" class="btn btn-sm btn-secondary"
                                        id="btnCancelarLaudo">Cancelar</button>
                                    <div id="feedbackLaudo" class="mt-2 small d-inline-block"></div>
                                </div>
                            @endcan
                        </div>

                        <h6 class="section-title mt-3">
                            Observações Internas:
                            @can('is-admin-or-tecnico')
                                <button type="button" class="btn btn-link btn-sm p-0 ms-1" id="btnEditarObservacoes"
                                    title="Editar Observações"><i class="bi bi-pencil-square"></i></button>
                            @endcan
                        </h6>
                        <div id="containerObservacoes">
                            <div id="textoObservacoes" class="obs-box">{{ $atendimento->observacoes ?: 'Nenhuma.' }}</div>
                            @can('is-admin-or-tecnico')
                                <div id="formEditarObservacoes" style="display:none;" class="mt-2">
                                    <textarea class="form-control form-control-sm mb-2" id="inputObservacoes" name="observacoes"
                                        rows="4">{{ $atendimento->observacoes }}</textarea>
                                    <button type="button" class="btn btn-sm btn-success me-1" id="btnSalvarObservacoes"><i
                                            class="bi bi-check-lg"></i> Salvar</button>
                                    <button type="button" class="btn btn-sm btn-secondary"
                                        id="btnCancelarObservacoes">Cancelar</button>
                                    <div id="feedbackObservacoes" class="mt-2 small d-inline-block"></div>
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            {{-- COLUNA DA DIREITA (MAIS ESTREITA) --}}
            <div class="col-lg-4">
                {{-- CARD 3: PEÇAS UTILIZADAS --}}
                <div class="card mb-4 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="my-1"><i class="bi bi-tools me-2"></i>Peças</h5>
                        @can('is-admin-or-tecnico')
                            <a href="{{ route('saidas-estoque.create', ['atendimento_id' => $atendimento->id]) }}"
                                class="btn btn-success btn-sm py-0 px-1" title="Adicionar Peça">
                                <i class="bi bi-plus-lg"></i> Add
                            </a>
                        @endcan
                    </div>
                    <div class="card-body p-0">
                        @if ($atendimento->saidasEstoque && $atendimento->saidasEstoque->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-hover mb-0">
                                    <thead class="table-light visually-hidden"> {{-- Cabeçalho pode ser oculto para economizar
                                        espaço --}}
                                        <tr>
                                            <th>Peça</th>
                                            <th class="text-center">Qtd.</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($atendimento->saidasEstoque as $saida)
                                            <tr>
                                                <td>
                                                    <small @if(!$saida->estoque) class="text-danger" @endif>
                                                        {{ $saida->estoque->nome ?? 'Peça Inválida' }}
                                                        @if($saida->estoque && $saida->estoque->modelo_compativel)
                                                        ({{ Str::limit($saida->estoque->modelo_compativel, 15) }}) @endif
                                                    </small>
                                                </td>
                                                <td class="text-center"><small>{{ $saida->quantidade }}</small></td>
                                                <td class="text-end">
                                                    <small>R${{ number_format($saida->quantidade * ($saida->estoque->preco_venda ?? 0), 2, ',', '.') }}</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-3 text-center"><small class="text-muted fst-italic">Nenhuma peça utilizada.</small>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- CARD 4: VALORES DO ATENDIMENTO --}}
                <div class="card mb-4 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="my-1"><i class="bi bi-currency-dollar me-2"></i>Valores</h5>
                        @can('is-admin')
                            <button type="button" class="btn btn-outline-primary btn-sm py-0 px-1" id="btnEditarValoresServico"
                                title="Editar Valores">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                        @endcan
                    </div>
                    <div class="card-body">
                        @php
                            $valorTotalPecas = 0;
                            if ($atendimento->saidasEstoque) {
                                foreach ($atendimento->saidasEstoque as $saida) {
                                    if ($saida->estoque)
                                        $valorTotalPecas += $saida->quantidade * ($saida->estoque->preco_venda ?? 0);
                                }
                            }
                            $valorServicoAtual = $atendimento->valor_servico ?? 0;
                            $descontoServicoAtual = $atendimento->desconto_servico ?? 0;
                            $valorServicoLiquido = $valorServicoAtual - $descontoServicoAtual;
                            $valorTotalAtendimento = $valorServicoLiquido + $valorTotalPecas;
                        @endphp
                        <div id="areaExibirValores">
                            <dl class="dl-horizontal mb-0">
                                <dt><small>Mão de Obra:</small></dt>
                                <dd class="text-end" id="textoValorServico"><small>R$
                                        {{ number_format($valorServicoAtual, 2, ',', '.') }}</small></dd>
                                <dt><small>Desconto Serv.:</small></dt>
                                <dd class="text-end text-danger" id="textoDescontoServico"><small>- R$
                                        {{ number_format($descontoServicoAtual, 2, ',', '.') }}</small></dd>
                                <dt class="fw-semibold"><small>Subtotal Serviço:</small></dt>
                                <dd class="text-end fw-bold" id="textoSubtotalServico"><small>R$
                                        {{ number_format($valorServicoLiquido, 2, ',', '.') }}</small></dd>
                                <dt class="mt-2"><small>Total Peças:</small></dt>
                                <dd class="text-end mt-2" id="textoValorTotalPecas"><small>R$
                                        {{ number_format($valorTotalPecas, 2, ',', '.') }}</small></dd>
                                <dt class="border-top pt-2 fs-6 text-success"><small>TOTAL OS:</small></dt>
                                <dd class="text-end border-top pt-2 fs-5 fw-bolder text-success"
                                    id="textoValorTotalAtendimento">R$
                                    {{ number_format($valorTotalAtendimento, 2, ',', '.') }}
                                </dd>
                            </dl>
                        </div>
                        @can('is-admin')
                            <div id="formEditarValoresServico" style="display:none;" class="mt-2 pt-2 border-top">
                                <div class="mb-2">
                                    <label for="inputValorServico" class="form-label form-label-sm fw-semibold">Valor Serviço
                                        (R$):</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm" id="inputValorServico"
                                        value="{{ number_format($valorServicoAtual, 2, '.', '') }}">
                                </div>
                                <div class="mb-2">
                                    <label for="inputDescontoServico" class="form-label form-label-sm fw-semibold">Desconto
                                        (R$):</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm"
                                        id="inputDescontoServico"
                                        value="{{ number_format($descontoServicoAtual, 2, '.', '') }}">
                                </div>
                                <button type="button" class="btn btn-sm btn-success me-1" id="btnSalvarValoresServico"><i
                                        class="bi bi-check-lg"></i> Salvar</button>
                                <button type="button" class="btn btn-sm btn-secondary"
                                    id="btnCancelarValoresServico">Cancelar</button>
                                <div id="feedbackValoresServico" class="mt-1 small d-inline-block"></div>
                            </div>
                        @endcan
                    </div>
                </div>

                {{-- CARD 5: AÇÕES FINAIS E HISTÓRICO --}}
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <h5 class="my-1"><i class="bi bi-check2-square me-2"></i>Finalização e Histórico</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-1">Criado em: {{ $atendimento->created_at->format('d/m/Y H:i') }}</p>
                        <p class="text-muted small">Última atualização: {{ $atendimento->updated_at->format('d/m/Y H:i') }}
                        </p>
                        @can('is-admin-or-atendente')
                            <form action="{{ route('atendimentos.destroy', $atendimento->id) }}" method="POST"
                                class="d-grid mt-3">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Tem certeza que deseja excluir este atendimento? Esta ação não poderá ser desfeita e não estorna peças automaticamente.')">
                                    <i class="bi bi-trash"></i> Excluir Atendimento
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div> {{-- Fim da row principal --}}
    </div> {{-- Fim container --}}

    {{-- MODAL PARA REGISTRAR PAGAMENTO (HTML como antes) --}}
    @can('gerenciar-caixa')
        <div class="modal fade" id="modalRegistrarPagamento" tabindex="-1" aria-labelledby="modalRegistrarPagamentoLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalRegistrarPagamentoLabel"><i class="bi bi-cash-coin"></i> Registrar
                            Pagamento - Atendimento #{{ $atendimento->id }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="formRegistrarPagamentoAtendimento">
                        @csrf
                        <div class="modal-body">
                            <div id="feedbackModalPagamento" class="mb-3"></div>
                            <div class="alert alert-info">
                                <p class="mb-1"><strong>Cliente:</strong> {{ $atendimento->cliente->nome_completo ?? 'N/A' }}
                                </p>
                                <p class="mb-1"><strong>Aparelho:</strong> {{ $atendimento->descricao_aparelho }}</p>
                                <p class="mb-0"><strong>Valor Total Original do Atendimento:</strong>
                                    <strong id="valorTotalOriginalDisplayModal">R$
                                        {{ number_format($valorTotalDevidoModal ?? 0, 2, ',', '.') }}</strong>
                                </p>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label for="modal_valor_servico" class="form-label">Valor da Mão de Obra/Serviço (R$):</label>
                                <input type="number" step="0.01" class="form-control" id="modal_valor_servico"
                                    name="valor_servico" value="{{ number_format($valorServicoModal ?? 0, 2, '.', '') }}"
                                    min="0" {{ Gate::denies('is-admin') ? 'readonly' : '' }}>
                                @cannot('is-admin') <small class="form-text text-muted">Apenas administradores podem alterar
                                    este valor.</small> @endcannot
                            </div>
                            <div class="mb-3">
                                <label for="modal_desconto_servico" class="form-label">Desconto sobre Serviço R$
                                    (Opcional):</label>
                                <input type="number" step="0.01" class="form-control" id="modal_desconto_servico"
                                    name="desconto_servico" value="{{ number_format($descontoServicoModal ?? 0, 2, '.', '') }}"
                                    min="0" {{ Gate::denies('is-admin') ? 'readonly' : '' }}>
                                @cannot('is-admin') <small class="form-text text-muted">Apenas administradores podem aplicar
                                    descontos.</small> @endcannot
                            </div>
                            <div class="mb-3 p-2 bg-light border rounded">
                                <p class="mb-1">Subtotal do Serviço (após desconto):
                                    <strong id="modalSubtotalServicoDisplay">R$ 0,00</strong> {{-- Valor inicial --}}
                                </p>
                                <p class="mb-1">(+) Valor Total das Peças:
                                    <strong id="modalValorPecasDisplay">R$
                                        {{ number_format($valorTotalPecasModal ?? 0, 2, ',', '.') }}</strong> {{-- Valor inicial
                                    das peças --}}
                                </p>
                                <p class="mb-0 fs-5">(=) VALOR A PAGAR:
                                    <strong id="modalNovoValorTotalDevidoDisplay" class="text-primary">R$ 0,00</strong> {{--
                                    Valor inicial --}}
                                </p>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label for="modal_forma_pagamento" class="form-label">Forma de Pagamento <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="modal_forma_pagamento" name="forma_pagamento" required>
                                    <option value="">Selecione...</option>
                                    @if(isset($formasPagamentoDisponiveis) && (is_array($formasPagamentoDisponiveis) || is_object($formasPagamentoDisponiveis)))
                                        @foreach($formasPagamentoDisponiveis as $opcao)
                                            <option value="{{ $opcao }}" {{ old('forma_pagamento', $atendimento->forma_pagamento) == $opcao ? 'selected' : '' }}>
                                                {{ $opcao }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="Dinheiro">Dinheiro</option>
                                        <option value="Cartão de Débito">Cartão de Débito</option>
                                        <option value="Cartão de Crédito">Cartão de Crédito</option>
                                        <option value="PIX">PIX</option>
                                        <option value="Boleto">Boleto</option>
                                        <option value="Outro">Outro</option>
                                    @endif
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="modal_observacoes_pagamento" class="form-label">Observações do Pagamento
                                    (Opcional):</label>
                                <textarea class="form-control" id="modal_observacoes_pagamento" name="observacoes_pagamento"
                                    rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success" id="btnConfirmarPagamentoModal">
                                <i class="bi bi-check-circle-fill"></i> Confirmar Pagamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

@endsection

@push('scripts')
{{-- O JavaScript completo que forneci anteriormente vai aqui --}}

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. OBTENÇÃO DE ELEMENTOS DO DOM E VARIÁVEIS GLOBAIS DO ESCOPO
            const feedbackGlobal = document.getElementById('feedbackGlobalAtendimentoShow');
            const atendimentoId = "{{ $atendimento->id }}";

            let statusGeralAtualJs = "{{ $atendimento->status }}";
            let statusPagamentoAtualJs = "{{ $atendimento->status_pagamento ?? 'Pendente' }}";
            if (statusPagamentoAtualJs === "") {
                statusPagamentoAtualJs = "Pendente";
            }
            const podeGerenciarCaixaJs = {{ Gate::allows('gerenciar-caixa') ? 'true' : 'false' }};

            const statusAtualTextoSpan = document.getElementById('statusAtualTexto');
            const statusAtualNomeSpan = document.getElementById('statusAtualNome');
            const statusPagamentoOuterContainer = document.getElementById('statusPagamentoTextoOuter');
            const textoValorServicoPage = document.getElementById('textoValorServico');
            const textoDescontoServicoPage = document.getElementById('textoDescontoServico');
            const textoSubtotalServicoPage = document.getElementById('textoSubtotalServico');
            const textoValorTotalPecasPage = document.getElementById('textoValorTotalPecas');
            const textoValorTotalAtendimentoPage = document.getElementById('textoValorTotalAtendimento');
            const codigoConsultaSpan = document.getElementById('codigoConsultaParaCopiar');
            const btnCopiarCodigo = document.getElementById('btnCopiarCodigo');
            const mensagemCopiado = document.getElementById('mensagemCopiado');

            const btnEditarValoresServicoPage = document.getElementById('btnEditarValoresServico');
            const areaExibirValoresPage = document.getElementById('areaExibirValores');
            const formEditarValoresPage = document.getElementById('formEditarValoresServico');
            const inputValorServicoPage = document.getElementById('inputValorServico');
            const inputDescontoServicoPage = document.getElementById('inputDescontoServico');
            const btnSalvarValoresPage = document.getElementById('btnSalvarValoresServico');
            const btnCancelarValoresPage = document.getElementById('btnCancelarValoresServico');
            const feedbackValoresPage = document.getElementById('feedbackValoresServico');

            const modalRegistrarPagamento = document.getElementById('modalRegistrarPagamento');
            const formRegistrarPagamentoModal = document.getElementById('formRegistrarPagamentoAtendimento');
            const btnConfirmarPagamentoModal = document.getElementById('btnConfirmarPagamentoModal');
            const feedbackModalPagamento = document.getElementById('feedbackModalPagamento');
            const modalValorServicoInput = document.getElementById('modal_valor_servico');
            const modalDescontoServicoInput = document.getElementById('modal_desconto_servico');
            const modalSubtotalServicoDisplay = document.getElementById('modalSubtotalServicoDisplay');
            const modalNovoValorTotalDevidoDisplay = document.getElementById('modalNovoValorTotalDevidoDisplay');
            const modalValorPecasDisplay = document.getElementById('modalValorPecasDisplay');
            const valorTotalPecasFixoModal = parseFloat("{{ number_format($valorTotalPecasModal ?? 0, 2, '.', '') }}");

            // 2. DEFINIÇÕES DE FUNÇÕES
            function exibirFeedbackGlobal(mensagem, tipo = 'success') {
                if (!feedbackGlobal) { console.warn("Elemento 'feedbackGlobalAtendimentoShow' não encontrado."); return; }
                while (feedbackGlobal.firstChild) { feedbackGlobal.removeChild(feedbackGlobal.firstChild); }
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
                console.log('Chamando atualizarEstadoBotaoRegistrarPagamento...');
                console.log('Status Geral JS:', statusGeralAtualJs);
                console.log('Status Pagamento JS:', statusPagamentoAtualJs);
                console.log('Pode Gerenciar Caixa JS:', podeGerenciarCaixaJs);
                const containerBotaoPagamento = document.getElementById('containerBotaoRegistrarPagamento');
                if (!containerBotaoPagamento) { console.warn('Container do botão de pagamento não encontrado.'); return; }
                const statusGeraisPermitemPagamento = ['Pronto para entrega', 'Aguardando aprovação cliente'];
                const statusPagamentoPermiteAcao = ['Pendente', 'Parcialmente Pago'];
                const statusGeralBloqueante = ['Cancelado', 'Reprovado'];
                const permiteRegistrar = statusPagamentoPermiteAcao.includes(statusPagamentoAtualJs) &&
                    statusGeraisPermitemPagamento.includes(statusGeralAtualJs) &&
                    !statusGeralBloqueante.includes(statusGeralAtualJs) &&
                    podeGerenciarCaixaJs;
                console.log('Permite Registrar:', permiteRegistrar);
                const mostrarDesabilitado = statusPagamentoPermiteAcao.includes(statusPagamentoAtualJs) &&
                    !statusGeraisPermitemPagamento.includes(statusGeralAtualJs) &&
                    !statusGeralBloqueante.includes(statusGeralAtualJs) &&
                    podeGerenciarCaixaJs;
                console.log('Mostrar Desabilitado:', mostrarDesabilitado);
                let htmlBotao = '';
                if (permiteRegistrar) {
                    htmlBotao = `<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalRegistrarPagamento"><i class="bi bi-cash-coin me-1"></i> Registrar Pagamento</button>`;
                } else if (mostrarDesabilitado) {
                    htmlBotao = `
                    <button type="button" class="btn btn-outline-secondary" disabled title="Defina os valores e avance o status do serviço para habilitar o pagamento.">
                        <i class="bi bi-cash-coin me-1"></i> Registrar Pagamento
                    </button>
                    <small id="mensagemBotaoPagamentoDesabilitado" class="d-block text-muted mt-1">
                        O serviço precisa estar em status como 'Pronto para entrega' ou 'Aguardando aprovação cliente' (com valores definidos) para registrar o pagamento aqui. Para adiantamentos ou taxas, utilize a movimentação avulsa no caixa.
                    </small>`;
                }
                containerBotaoPagamento.innerHTML = htmlBotao;
                containerBotaoPagamento.style.display = (permiteRegistrar || mostrarDesabilitado) ? 'block' : 'none';
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
                        if (currentText === placeholderText) currentText = '';
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
                                    textDisplay.innerText = body.novos_valores && body.novos_valores[fieldName] ? body.novos_valores[fieldName] : placeholderText;
                                    exibirFeedbackGlobal(body.message, 'success');
                                } else {
                                    exibirFeedbackGlobal(body.message || `Erro ao salvar ${fieldName}.`, 'danger');
                                }
                            })
                            .catch(error => {
                                console.error(`Erro AJAX para ${fieldName}:`, error);
                                let errorMsg = `Erro de comunicação ao salvar ${fieldName}.`;
                                if (error && error.body && error.body.message) errorMsg = error.body.message;
                                exibirFeedbackGlobal(errorMsg, 'danger');
                            })
                            .finally(() => {
                                textDisplay.style.display = 'block'; formEdit.style.display = 'none';
                                btnSave.disabled = false; btnCancel.disabled = false;
                                btnEdit.style.display = 'inline-block'; feedbackElement.innerHTML = '';
                            });
                    });
                }
            }

            function calcularTotaisModal() {
                if (!modalValorServicoInput || !modalDescontoServicoInput || !modalSubtotalServicoDisplay || !modalNovoValorTotalDevidoDisplay || !modalValorPecasDisplay) {
                    console.warn("Modal: Elementos para cálculo de totais não encontrados."); return;
                }
                console.log("calcularTotaisModal FOI CHAMADA"); // Para confirmar a chamada

                let valorServicoStr = modalValorServicoInput.value; // Declarada aqui
                let descontoServicoStr = modalDescontoServicoInput.value; // Declarada aqui
                console.log("Strings originais dos Inputs:", valorServicoStr, descontoServicoStr);

                let valorServico = parseFloat(valorServicoStr.replace(',', '.')) || 0;
                let descontoServico = parseFloat(descontoServicoStr.replace(',', '.')) || 0;
                console.log("Valores Parseados:", valorServico, descontoServico);
                console.log("Peças Fixo:", valorTotalPecasFixoModal);

                if (descontoServico > valorServico) {
                    descontoServico = valorServico;
                    modalDescontoServicoInput.value = descontoServico.toFixed(2);
                }

                let subtotalServico = valorServico - descontoServico;
                let novoValorTotalDevido = subtotalServico + valorTotalPecasFixoModal;
                console.log("Subtotal Serviço:", subtotalServico, "Total Devido:", novoValorTotalDevido);

                modalSubtotalServicoDisplay.textContent = 'R$ ' + subtotalServico.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                modalValorPecasDisplay.textContent = 'R$ ' + valorTotalPecasFixoModal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                modalNovoValorTotalDevidoDisplay.textContent = 'R$ ' + novoValorTotalDevido.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function atualizarInputsModalPagamento(novosValoresNumericos) {
                console.log("atualizarInputsModalPagamento chamada com:", novosValoresNumericos);
                if (modalValorServicoInput && typeof novosValoresNumericos.valor_servico_raw !== 'undefined') {
                    let valorServicoRaw = parseFloat(novosValoresNumericos.valor_servico_raw); // Variável local
                    modalValorServicoInput.value = isNaN(valorServicoRaw) ? '0.00' : valorServicoRaw.toFixed(2);
                    console.log("Input modal_valor_servico atualizado para:", modalValorServicoInput.value);
                }
                if (modalDescontoServicoInput && typeof novosValoresNumericos.desconto_servico_raw !== 'undefined') {
                    let descontoServicoRaw = parseFloat(novosValoresNumericos.desconto_servico_raw); // Variável local
                    modalDescontoServicoInput.value = isNaN(descontoServicoRaw) ? '0.00' : descontoServicoRaw.toFixed(2);
                    console.log("Input modal_desconto_servico atualizado para:", modalDescontoServicoInput.value);
                }
                calcularTotaisModal();
            }

            function executarCopia() {
                if (!codigoConsultaSpan) return;
                const textoParaCopiar = codigoConsultaSpan.innerText.trim();
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(textoParaCopiar).then(() => {
                        if (mensagemCopiado) { mensagemCopiado.style.display = 'inline'; setTimeout(() => { mensagemCopiado.style.display = 'none'; }, 2000); }
                        if (btnCopiarCodigo) { const o = btnCopiarCodigo.innerHTML; btnCopiarCodigo.innerHTML = '<i class="bi bi-check-lg text-success"></i>'; btnCopiarCodigo.disabled = true; setTimeout(() => { btnCopiarCodigo.innerHTML = o; btnCopiarCodigo.disabled = false; }, 2000); }
                    }).catch(err => { console.error('Erro ao copiar: ', err); alert('Não foi possível copiar.'); });
                } else { alert('Cópia automática não suportada.'); }
            }

            // 3. EXECUÇÃO INICIAL E EVENT LISTENERS
            atualizarEstadoBotaoRegistrarPagamento();

            @can('is-admin-or-tecnico')
                setupInlineEdit({ /* ... config observações ... */
                    btnEditId: 'btnEditarObservacoes', textDisplayId: 'textoObservacoes', formEditId: 'formEditarObservacoes',
                    inputId: 'inputObservacoes', btnSaveId: 'btnSalvarObservacoes', btnCancelId: 'btnCancelarObservacoes',
                    feedbackId: 'feedbackObservacoes',
                    saveUrl: `{{ route("atendimentos.atualizarCampoAjax", ["atendimento" => $atendimento->id, "campo" => "observacoes"]) }}`,
                    fieldName: 'observacoes', placeholderText: 'Nenhuma.'
                });
                setupInlineEdit({ /* ... config laudo ... */
                    btnEditId: 'btnEditarLaudo', textDisplayId: 'textoLaudo', formEditId: 'formEditarLaudo',
                    inputId: 'inputLaudo', btnSaveId: 'btnSalvarLaudo', btnCancelId: 'btnCancelarLaudo',
                    feedbackId: 'feedbackLaudo',
                    saveUrl: `{{ route("atendimentos.atualizarCampoAjax", ["atendimento" => $atendimento->id, "campo" => "laudo_tecnico"]) }}`,
                    fieldName: 'laudo_tecnico', placeholderText: 'Ainda não informado.'
                });
            @endcan

        const formAtualizarStatusRapido = document.getElementById('formAtualizarStatusRapido');
            if (formAtualizarStatusRapido && statusAtualTextoSpan && statusAtualNomeSpan) {
                formAtualizarStatusRapido.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const formData = new FormData(this);
                    const url = this.action;
                    const btnSubmitStatus = this.querySelector('button[type="submit"]');
                    const originalButtonHtml = btnSubmitStatus.innerHTML;
                    btnSubmitStatus.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Salvando...';
                    btnSubmitStatus.disabled = true;
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
                                    console.log("AJAX Status Geral: statusGeralAtualJs atualizado para:", statusGeralAtualJs); // Depuração
                                    atualizarEstadoBotaoRegistrarPagamento();
                                }
                            } else {
                                exibirFeedbackGlobal(body.message || 'Ocorreu um erro.', body.feedback_tipo || 'danger');
                            }
                        })
                        .catch(error => {
                            console.error('Erro AJAX status rápido:', error);
                            let errorMsg = 'Erro de comunicação.'; if (error && error.body && error.body.message) errorMsg = error.body.message; else if (error && error.message) errorMsg = error.message;
                            exibirFeedbackGlobal(errorMsg, 'danger');
                        })
                        .finally(() => {
                            btnSubmitStatus.innerHTML = originalButtonHtml; btnSubmitStatus.disabled = false;
                        });
                });
            }

            if (codigoConsultaSpan) codigoConsultaSpan.addEventListener('click', executarCopia);
            if (btnCopiarCodigo) btnCopiarCodigo.addEventListener('click', executarCopia);

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl); });

            if (btnEditarValoresServicoPage && areaExibirValoresPage && formEditarValoresPage) {
                btnEditarValoresServicoPage.addEventListener('click', function () {
                    areaExibirValoresPage.style.display = 'none'; formEditarValoresPage.style.display = 'block';
                    if (inputValorServicoPage && textoValorServicoPage) inputValorServicoPage.value = (parseFloat(textoValorServicoPage.innerText.replace('R$ ', '').replace(/\./g, '').replace(',', '.')) || 0).toFixed(2);
                    if (inputDescontoServicoPage && textoDescontoServicoPage) inputDescontoServicoPage.value = (parseFloat(textoDescontoServicoPage.innerText.replace('- R$ ', '').replace(/\./g, '').replace(',', '.')) || 0).toFixed(2);
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
                        const novoValorServico = inputValorServicoPage.value;
                        const novoDescontoServico = inputDescontoServicoPage.value;
                        if (feedbackValoresPage) feedbackValoresPage.innerHTML = '<span class="text-info small">Salvando...</span>';
                        btnSalvarValoresPage.disabled = true; if (btnCancelarValoresPage) btnCancelarValoresPage.disabled = true;
                        fetch(`{{ route("atendimentos.atualizarValoresServicoAjax", $atendimento->id) }}`, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                            body: JSON.stringify({ valor_servico: novoValorServico, desconto_servico: novoDescontoServico })
                        })
                            .then(response => response.json().then(data => ({ statusHttp: response.status, body: data })))
                            .then(({ statusHttp, body }) => {
                                if (body.success && body.novos_valores) {
                                    if (textoValorServicoPage) textoValorServicoPage.innerText = 'R$ ' + body.novos_valores.valor_servico;
                                    if (textoDescontoServicoPage) textoDescontoServicoPage.innerText = '- R$ ' + body.novos_valores.desconto_servico;
                                    if (textoSubtotalServicoPage) textoSubtotalServicoPage.innerText = 'R$ ' + body.novos_valores.subtotal_servico;
                                    if (textoValorTotalAtendimentoPage) textoValorTotalAtendimentoPage.innerText = 'R$ ' + body.novos_valores.valor_total_atendimento;
                                    exibirFeedbackGlobal(body.message, 'success');
                                    atualizarInputsModalPagamento({
                                        valor_servico_raw: parseFloat(novoValorServico.replace(',', '.')),
                                        desconto_servico_raw: parseFloat(novoDescontoServico.replace(',', '.'))
                                    });
                                } else {
                                    exibirFeedbackGlobal(body.message || 'Erro ao salvar valores.', 'danger');
                                }
                            })
                            .catch(error => {
                                console.error('Erro AJAX (salvar valores):', error);
                                let errorMsg = 'Erro de comunicação.'; if (error && error.body && error.body.message) errorMsg = error.body.message;
                                exibirFeedbackGlobal(errorMsg, 'danger');
                            })
                            .finally(() => {
                                areaExibirValoresPage.style.display = 'block'; formEditarValoresPage.style.display = 'none';
                                btnSalvarValoresPage.disabled = false; if (btnCancelarValoresPage) btnCancelarValoresPage.disabled = false;
                                btnEditarValoresServicoPage.style.display = 'inline-block'; if (feedbackValoresPage) feedbackValoresPage.innerHTML = '';
                            });
                    });
                }
            }

            if (modalRegistrarPagamento && formRegistrarPagamentoModal && btnConfirmarPagamentoModal) {
                modalRegistrarPagamento.addEventListener('show.bs.modal', function () {
                    if (formRegistrarPagamentoModal) formRegistrarPagamentoModal.reset();
                    if (feedbackModalPagamento) feedbackModalPagamento.innerHTML = '';
                    const valorServicoAtualPg = (parseFloat(textoValorServicoPage.innerText.replace('R$ ', '').replace(/\./g, '').replace(',', '.')) || 0);
                    const descontoServicoAtualPg = (parseFloat(textoDescontoServicoPage.innerText.replace('- R$ ', '').replace(/\./g, '').replace(',', '.')) || 0);
                    if (modalValorServicoInput) modalValorServicoInput.value = valorServicoAtualPg.toFixed(2);
                    if (modalDescontoServicoInput) modalDescontoServicoInput.value = descontoServicoAtualPg.toFixed(2);
                    const formaPagamentoAtual = "{{ $atendimento->forma_pagamento ?? '' }}";
                    const modalFormaPagamentoSelect = document.getElementById('modal_forma_pagamento');
                    if (modalFormaPagamentoSelect) modalFormaPagamentoSelect.value = formaPagamentoAtual || "";
                    calcularTotaisModal();
                });

                formRegistrarPagamentoModal.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const formData = new FormData(formRegistrarPagamentoModal);
                    const url = "{{ route('atendimentos.registrarPagamentoAjax', $atendimento->id) }}";
                    const originalButtonHtml = btnConfirmarPagamentoModal.innerHTML;
                    btnConfirmarPagamentoModal.disabled = true;
                    btnConfirmarPagamentoModal.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processando...';
                    if (feedbackModalPagamento) feedbackModalPagamento.innerHTML = '';
                    fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                        body: formData
                    })
                        .then(response => response.json().then(data => ({ statusHttp: response.status, body: data })))
                        .then(({ statusHttp, body }) => {
                            if (body.success) {
                                if (feedbackModalPagamento) feedbackModalPagamento.innerHTML = `<div class="alert alert-success">${body.message}</div>`;
                                if (body.novos_valores_atendimento) {
                                    if (textoValorServicoPage) textoValorServicoPage.innerText = 'R$ ' + body.novos_valores_atendimento.valor_servico;
                                    if (textoDescontoServicoPage) textoDescontoServicoPage.innerText = '- R$ ' + body.novos_valores_atendimento.desconto_servico;
                                    if (textoSubtotalServicoPage) textoSubtotalServicoPage.innerText = 'R$ ' + body.novos_valores_atendimento.subtotal_servico;
                                    if (textoValorTotalPecasPage && body.novos_valores_atendimento.valor_total_pecas) {
                                        textoValorTotalPecasPage.innerHTML = 'R$ ' + body.novos_valores_atendimento.valor_total_pecas + (body.novos_valores_atendimento.quantidade_pecas ? ` <small class="d-block text-muted">(${body.novos_valores_atendimento.quantidade_pecas} peça(s)/item(ns))</small>` : '');
                                    }
                                    if (textoValorTotalAtendimentoPage) textoValorTotalAtendimentoPage.innerText = 'R$ ' + body.novos_valores_atendimento.valor_total_atendimento;
                                }
                                if (body.novo_status_pagamento_html) {
                                    if (statusPagamentoOuterContainer) {
                                        statusPagamentoOuterContainer.innerHTML = body.novo_status_pagamento_html;
                                        if (body.novo_status_pagamento_texto) statusPagamentoAtualJs = body.novo_status_pagamento_texto;
                                    }
                                }
                                if (body.observacoes_atualizadas && document.getElementById('textoObservacoes')) {
                                    document.getElementById('textoObservacoes').innerHTML = body.observacoes_atualizadas;
                                }
                                atualizarEstadoBotaoRegistrarPagamento();
                                setTimeout(() => {
                                    const modalInstance = bootstrap.Modal.getInstance(modalRegistrarPagamento);
                                    if (modalInstance) modalInstance.hide();
                                    exibirFeedbackGlobal(body.message, 'success');
                                }, 2000);
                            } else {
                                let errorMessages = body.message || 'Ocorreu um erro.';
                                if (body.errors) {
                                    errorMessages += '<ul class="mb-0 mt-2 small">';
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
                            btnConfirmarPagamentoModal.innerHTML = originalButtonHtml;
                        });
                });
            }
        });
    </script>
@endpush
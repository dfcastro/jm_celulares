@extends('layouts.app')

@section('title', 'Histórico Unificado de Movimentações de Estoque')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .card-header-filtros {
            background-color: #f8f9fa;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .movimentacao-entrada {
            /* color: #0f5132; */ /* Cor do texto do badge de sucesso */
            /* background-color: #d1e7dd; */ /* Fundo do badge de sucesso */
        }
        .movimentacao-saida {
            /* color: #842029; */ /* Cor do texto do badge de perigo */
            /* background-color: #f8d7da; */ /* Fundo do badge de perigo */
        }
        .icon-movimentacao {
            font-size: 1.1em;
            margin-right: 0.3rem;
        }
    </style>
@endpush

@section('content')
<div class="container mt-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h1><i class="bi bi-hourglass-split"></i> Histórico Unificado de Estoque</h1>
        <a href="{{ route('estoque.index') }}" class="btn btn-secondary" data-bs-toggle="tooltip" title="Voltar para a lista de itens em estoque">
            <i class="bi bi-boxes"></i> Voltar para Estoque
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Card de Filtros (mantido como na versão anterior) --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header card-header-filtros">
            <h5 class="my-0"><i class="bi bi-funnel-fill"></i> Filtros de Pesquisa</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('estoque.historico_unificado') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6 col-lg-3">
                        <label for="data_inicial_filtro" class="form-label form-label-sm">Data (De):</label>
                        <input type="date" class="form-control form-control-sm" id="data_inicial_filtro" name="data_inicial_filtro" value="{{ $request->input('data_inicial_filtro') }}">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="data_final_filtro" class="form-label form-label-sm">Data (Até):</label>
                        <input type="date" class="form-control form-control-sm" id="data_final_filtro" name="data_final_filtro" value="{{ $request->input('data_final_filtro') }}">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="tipo_movimentacao_filtro" class="form-label form-label-sm">Tipo Mov.:</label>
                        <select class="form-select form-select-sm" id="tipo_movimentacao_filtro" name="tipo_movimentacao_filtro">
                            <option value="">Todas</option>
                            <option value="Entrada" {{ $request->input('tipo_movimentacao_filtro') == 'Entrada' ? 'selected' : '' }}>Entradas</option>
                            <option value="Saída" {{ $request->input('tipo_movimentacao_filtro') == 'Saída' ? 'selected' : '' }}>Saídas</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="filtro_peca_nome_display_hist" class="form-label form-label-sm">Peça/Acessório:</label>
                        <input type="text" class="form-control form-control-sm" id="filtro_peca_nome_display_hist" name="filtro_peca_nome_display" placeholder="Nome, modelo ou ID da peça" value="{{ old('filtro_peca_nome_display', $pecaSelecionadaNomeFiltro ?? '') }}">
                        <input type="hidden" id="filtro_peca_id_hist" name="filtro_peca_id" value="{{ $request->input('filtro_peca_id') }}">
                    </div>
                </div>
                <div class="row g-3 mt-2 align-items-end">
                    <div class="col-md-9">
                        <label for="busca_obs_filtro" class="form-label form-label-sm">Buscar em Observações:</label>
                        <input type="text" class="form-control form-control-sm" id="busca_obs_filtro" name="busca_obs_filtro" value="{{ $request->input('busca_obs_filtro') }}" placeholder="Termo na observação da movimentação...">
                    </div>
                    <div class="col-md-3 d-flex align-items-end mt-lg-0 mt-2">
                        <div class="input-group input-group-sm">
                            <button class="btn btn-primary w-50" type="submit" style="border-top-right-radius: 0; border-bottom-right-radius: 0;" data-bs-toggle="tooltip" title="Aplicar Filtros"><i class="bi bi-search"></i> Filtrar</button>
                            <a href="{{ route('estoque.historico_unificado') }}" class="btn btn-secondary w-50" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" data-bs-toggle="tooltip" title="Limpar Filtros"><i class="bi bi-eraser-fill"></i> Limpar</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
 {{-- Card de Totalizadores (Aparece se houver filtros ou movimentações) --}}
 @if($request->hasAny(['data_inicial_filtro', 'data_final_filtro', 'tipo_movimentacao_filtro', 'filtro_peca_id', 'busca_obs_filtro']) || $movimentacoesPaginadas->total() > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="my-1"><i class="bi bi-bar-chart-line-fill"></i> Resumo das Movimentações Filtradas</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h6 class="text-muted">Total de Entradas</h6>
                        <p class="fs-4 fw-bold text-success">{{ $totalEntradasQuantidade }} unid.</p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Total de Saídas</h6>
                        <p class="fs-4 fw-bold text-danger">{{ $totalSaidasQuantidade }} unid.</p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Saldo das Movimentações</h6>
                        <p class="fs-4 fw-bold {{ $saldoMovimentacoes >= 0 ? 'text-primary' : 'text-warning' }}">
                            {{ $saldoMovimentacoes }} unid.
                            @if($saldoMovimentacoes > 0)
                                <small class="d-block text-muted fs-6">(Mais entradas que saídas)</small>
                            @elseif($saldoMovimentacoes < 0)
                                <small class="d-block text-muted fs-6">(Mais saídas que entradas)</small>
                            @else
                                <small class="d-block text-muted fs-6">(Entradas e saídas se equivalem)</small>
                            @endif
                        </p>
                    </div>
                </div>
                @if($request->input('filtro_peca_id') && $pecaSelecionadaNomeFiltro)
                    <p class="text-center mt-2 mb-0 small text-muted">
                        Resumo para a peça: <strong>{{ $pecaSelecionadaNomeFiltro }}</strong>
                        @if($request->input('data_inicial_filtro') || $request->input('data_final_filtro'))
                            no período de {{ $request->input('data_inicial_filtro') ? \Carbon\Carbon::parse($request->input('data_inicial_filtro'))->format('d/m/Y') : 'Início' }}
                            até {{ $request->input('data_final_filtro') ? \Carbon\Carbon::parse($request->input('data_final_filtro'))->format('d/m/Y') : 'Hoje' }}.
                        @endif
                    </p>
                @elseif($request->input('data_inicial_filtro') || $request->input('data_final_filtro'))
                     <p class="text-center mt-2 mb-0 small text-muted">
                        Resumo para todas as peças
                        no período de {{ $request->input('data_inicial_filtro') ? \Carbon\Carbon::parse($request->input('data_inicial_filtro'))->format('d/m/Y') : 'Início' }}
                        até {{ $request->input('data_final_filtro') ? \Carbon\Carbon::parse($request->input('data_final_filtro'))->format('d/m/Y') : 'Hoje' }}.
                    </p>
                @endif
            </div>
        </div>
    @endif

    
    @if($movimentacoesPaginadas->total() > 0)
        <div class="mb-2 d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Exibindo {{ $movimentacoesPaginadas->firstItem() }} a {{ $movimentacoesPaginadas->lastItem() }} de {{ $movimentacoesPaginadas->total() }} movimentações encontradas.
            </small>
            {{-- Aqui poderia ir um botão de Exportar no futuro --}}
        </div>



        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center" style="width: 120px;">Tipo</th>
                        <th>Peça (Modelo)</th>
                        <th class="text-center" style="width: 70px;">Qtd.</th>
                        <th style="width: 140px;">Data/Hora</th>
                        <th>Observações</th>
                        <th>Ref. Atendimento</th>
                        <th class="text-center" style="width: 100px;">ID Mov.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movimentacoesPaginadas as $movimento)
                        <tr class="{{ $movimento->tipo_movimentacao_label == 'Entrada' ? 'movimentacao-entrada' : 'movimentacao-saida' }}">
                            <td class="text-center">
                                @if($movimento->tipo_movimentacao_label == 'Entrada')
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle rounded-pill" data-bs-toggle="tooltip" title="Entrada em Estoque">
                                        <i class="bi bi-arrow-down-circle-fill icon-movimentacao"></i> Entrada
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle rounded-pill" data-bs-toggle="tooltip" title="Saída de Estoque">
                                        <i class="bi bi-arrow-up-circle-fill icon-movimentacao"></i> Saída
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('estoque.show', $movimento->estoque_id_original) }}" data-bs-toggle="tooltip" title="Ver detalhes de {{ $movimento->nome_peca }}">
                                    {{ Str::limit($movimento->nome_peca, 25) }}
                                </a>
                                @if($movimento->modelo_peca)
                                <small class="d-block text-muted" data-bs-toggle="tooltip" title="{{$movimento->modelo_peca}}">({{ Str::limit($movimento->modelo_peca, 20) }})</small>
                                @endif
                            </td>
                            <td class="text-center fw-bold">{{ $movimento->quantidade }}</td>
                            <td>{{ Carbon\Carbon::parse($movimento->data_movimentacao)->format('d/m/y H:i') }}</td>
                            <td data-bs-toggle="tooltip" title="{{ $movimento->observacoes }}">
                                {{ Str::limit($movimento->observacoes, 30) ?? '-' }}
                            </td>
                            <td>
                                @if($movimento->atendimento_id)
                                    <a href="{{ route('atendimentos.show', $movimento->atendimento_id) }}" data-bs-toggle="tooltip" title="Ver Atendimento #{{ $movimento->atendimento_id }} - Cliente: {{ $movimento->cliente_atendimento ?? 'N/A' }}">
                                        OS #{{ $movimento->atendimento_id }}
                                    </a>
                                @else
                                    <span class="text-muted fst-italic">N/A</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($movimento->tipo_movimentacao_label == 'Entrada')
                                    <a href="{{ route('entradas-estoque.show', $movimento->movimentacao_id) }}" class="btn btn-outline-success btn-sm py-0 px-1" data-bs-toggle="tooltip" title="Ver detalhes da Entrada #{{$movimento->movimentacao_id}}">
                                        <i class="bi bi-eye"></i> E-{{ $movimento->movimentacao_id }}
                                    </a>
                                @else
                                    <a href="{{ route('saidas-estoque.show', $movimento->movimentacao_id) }}" class="btn btn-outline-danger btn-sm py-0 px-1" data-bs-toggle="tooltip" title="Ver detalhes da Saída #{{$movimento->movimentacao_id}}">
                                        <i class="bi bi-eye"></i> S-{{ $movimento->movimentacao_id }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($movimentacoesPaginadas->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $movimentacoesPaginadas->links() }}
            </div>
        @endif
    @else
        <div class="alert alert-info text-center mt-4" role="alert">
            Nenhuma movimentação de estoque encontrada para os filtros selecionados.
        </div>
    @endif
</div>
@endsection

@push('scripts')
    {{-- jQuery e jQuery UI já devem estar no layouts.app --}}
    <script>
        $(document).ready(function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            let pecaSelecionadaFiltroHist = ($('#filtro_peca_id_hist').val() !== '');
            $("#filtro_peca_nome_display_hist").autocomplete({
                source: function (request, response) {
                    pecaSelecionadaFiltroHist = false;
                    $.ajax({
                        url: "{{ route('estoque.autocomplete') }}",
                        dataType: "json", data: { search: request.term },
                        success: function (data) {
                            response($.map(data, function (item) {
                                return { label: item.label, value: item.value, id: item.id };
                            }));
                        }
                    });
                },
                minLength: 1,
                select: function (event, ui) {
                    if (ui.item && ui.item.id) {
                        $("#filtro_peca_nome_display_hist").val(ui.item.value);
                        $("#filtro_peca_id_hist").val(ui.item.id);
                        pecaSelecionadaFiltroHist = true;
                    }
                    return false;
                },
                change: function (event, ui) {
                    if (!pecaSelecionadaFiltroHist && !ui.item) {
                        $("#filtro_peca_id_hist").val('');
                    }
                }
            });
            $("#filtro_peca_nome_display_hist").on('input', function() {
                if ($(this).val() === '') {
                    $("#filtro_peca_id_hist").val('');
                    pecaSelecionadaFiltroHist = false;
                } else if (!pecaSelecionadaFiltroHist) {
                     $("#filtro_peca_id_hist").val('');
                }
            });
        });
    </script>
@endpush
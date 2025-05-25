@extends('layouts.app')

@section('title', 'Histórico de Saídas de Estoque')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    {{-- jQuery UI CSS já está no layout base --}}
    <style>
        .card-header-filtros {
            background-color: #f8f9fa;
            /* Um cinza bem claro, pode ajustar */
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .col-acoes-saida {
            min-width: 100px;
            width: 100px;
            text-align: center;
        }

        .table .btn-sm {
            /* Ajuste geral para botões sm na tabela */
            padding: 0.2rem 0.4rem;
            font-size: 0.78rem;
        }

        .table .btn-info:hover,
        .table .btn-danger:hover {
            opacity: 0.85;
        }

        .text-muted.fst-italic {
            font-size: 0.9em;
        }

        .table td small.d-block.text-muted {
            font-size: 0.85em;
            line-height: 1.2;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-0">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <h1><i class="bi bi-box-arrow-up"></i> Histórico de Saídas de Estoque</h1>
            <div>
                <a href="{{ route('saidas-estoque.create') }}" class="btn btn-primary me-1" data-bs-toggle="tooltip"
                    title="Registrar uma nova saída de item do estoque">
                    <i class="bi bi-dash-circle-fill"></i> Nova Saída
                </a>
                <a href="{{ route('estoque.index') }}" class="btn btn-secondary" data-bs-toggle="tooltip"
                    title="Voltar para a lista principal de itens em estoque">
                    <i class="bi bi-boxes"></i> Voltar para Estoque
                </a>
            </div>
        </div>

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

        <div class="card mb-4 shadow-sm">
            <div class="card-header card-header-filtros">
                <h5 class="my-0"><i class="bi bi-funnel-fill"></i> Filtros de Pesquisa</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('saidas-estoque.index') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6 col-lg-3">
                            <label for="data_inicial_filtro" class="form-label form-label-sm">Data Saída (De):</label>
                            <input type="date" class="form-control form-control-sm" id="data_inicial_filtro"
                                name="data_inicial_filtro" value="{{ request('data_inicial_filtro') }}">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="data_final_filtro" class="form-label form-label-sm">Data Saída (Até):</label>
                            <input type="date" class="form-control form-control-sm" id="data_final_filtro"
                                name="data_final_filtro" value="{{ request('data_final_filtro') }}">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="filtro_peca_nome_display" class="form-label form-label-sm">Peça:</label>
                            <input type="text" class="form-control form-control-sm" id="filtro_peca_nome_display"
                                name="filtro_peca_nome_display" placeholder="Nome, modelo ou ID da peça"
                                value="{{ old('filtro_peca_nome_display', $pecaSelecionadaNome ?? '') }}">
                            <input type="hidden" id="filtro_peca_id" name="filtro_peca_id"
                                value="{{ request('filtro_peca_id') }}">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="filtro_atendimento_info" class="form-label form-label-sm">Atendimento:</label>
                            <input type="text" class="form-control form-control-sm" id="filtro_atendimento_info"
                                name="filtro_atendimento_info" placeholder="ID, Cliente, Aparelho"
                                value="{{ old('filtro_atendimento_info', $atendimentoSelecionadoInfo ?? '') }}">
                            <input type="hidden" id="filtro_atendimento_id" name="filtro_atendimento_id"
                                value="{{ request('filtro_atendimento_id') }}">
                        </div>
                    </div>
                    <div class="row g-3 mt-2 align-items-end">
                        <div class="col-md-9">
                            <label for="busca_obs_saida" class="form-label form-label-sm">Buscar em Observações:</label>
                            <input type="text" class="form-control form-control-sm" id="busca_obs_saida"
                                name="busca_obs_saida" value="{{ request('busca_obs_saida') }}"
                                placeholder="Termo na observação...">
                        </div>
                        <div class="col-md-3 d-flex align-items-end mt-lg-0 mt-2">
                            <div class="input-group input-group-sm">
                                <button class="btn btn-primary w-50" type="submit"
                                    style="border-top-right-radius: 0; border-bottom-right-radius: 0;"
                                    data-bs-toggle="tooltip" title="Aplicar Filtros"><i class="bi bi-search"></i>
                                    Filtrar</button>
                                <a href="{{ route('saidas-estoque.index') }}" class="btn btn-secondary w-50"
                                    style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
                                    data-bs-toggle="tooltip" title="Limpar Filtros"><i class="bi bi-eraser-fill"></i>
                                    Limpar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Peça</th>
                        <th>Modelo/Marca</th>
                        <th>Atendimento (Cliente)</th>
                        <th class="text-center">Qtd.</th>
                        <th>Data Saída</th>
                        <th>Observações</th>
                        <th class="col-acoes-saida text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($saidas as $saida)
                        <tr>
                            <td>{{ $saida->id }}</td>
                            <td>
                                @if($saida->estoque)
                                    <a href="{{ route('estoque.show', $saida->estoque->id) }}" data-bs-toggle="tooltip"
                                        title="Ver detalhes de: {{ $saida->estoque->nome }}">
                                        {{ Str::limit($saida->estoque->nome, 25) }}
                                    </a>
                                @else
                                    <span class="text-danger" data-bs-toggle="tooltip"
                                        title="Esta peça pode ter sido removida do cadastro de estoque.">Peça não encontrada</span>
                                @endif
                            </td>
                            <td>
                                @if($saida->estoque)
                                    <small class="d-block text-muted" data-bs-toggle="tooltip"
                                        title="{{ $saida->estoque->modelo_compativel ?? 'Modelo não especificado' }}">
                                        {{ Str::limit($saida->estoque->modelo_compativel ?? 'N/A', 20) }}
                                    </small>
                                    <small class="d-block text-muted">Marca: {{ $saida->estoque->marca ?? 'N/A' }}</small>
                                @else
                                    <span class="text-muted fst-italic">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if ($saida->atendimento)
                                    <a href="{{ route('atendimentos.show', $saida->atendimento->id) }}" data-bs-toggle="tooltip"
                                        title="Ver Atendimento #{{ $saida->atendimento->id }} - Cliente: {{ $saida->atendimento->cliente->nome_completo ?? 'N/A' }}">
                                        #{{ $saida->atendimento->id }}
                                        @if($saida->atendimento->cliente)
                                            - {{ Str::limit($saida->atendimento->cliente->nome_completo, 18) }}
                                        @else
                                            - <span class="fst-italic">(Cliente Avulso)</span>
                                        @endif
                                    </a>
                                @else
                                    <span class="text-muted fst-italic">Uso Interno / Não Vinculado</span>
                                @endif
                            </td>
                            <td class="text-center fw-bold">{{ $saida->quantidade }}</td>
                            <td>{{ $saida->data_saida->format('d/m/Y H:i') }}</td>
                            <td data-bs-toggle="tooltip" title="{{ $saida->observacoes }}">
                                {{ Str::limit($saida->observacoes, 30) ?? '-' }}
                            </td>
                            <td class="col-acoes-saida text-center">
                                <a href="{{ route('saidas-estoque.show', $saida->id) }}" class="btn btn-info btn-sm"
                                    data-bs-toggle="tooltip" title="Ver Detalhes da Saída">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                @can('is-admin-or-tecnico')
                                    <form action="{{ route('saidas-estoque.destroy', ['saidas_estoque' => $saida->id]) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Tem certeza que deseja excluir esta saída? A quantidade será RETORNADA ao estoque do item.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip"
                                            title="Excluir Saída (Estorna Estoque)">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">Nenhuma saída de estoque registrada com os filtros
                                aplicados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($saidas->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $saidas->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    {{-- jQuery e jQuery UI já estão no layout base --}}
    <script>
        $(document).ready(function () {
            // Inicialização de Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Autocomplete para Peça no Filtro
            let pecaSelecionadaFiltroSaida = ($('#filtro_peca_id').val() !== '');
            $("#filtro_peca_nome_display").autocomplete({
                source: function (request, response) {
                    pecaSelecionadaFiltroSaida = false; // Reseta a flag antes de cada nova busca
                    $.ajax({
                        url: "{{ route('estoque.autocomplete') }}",
                        dataType: "json", data: { search: request.term },
                        success: function (data) {
                            response($.map(data, function (item) {
                                return {
                                    label: item.label, // O controller já deve formatar bem (Nome (Modelo) - Qtd: X - Tipo: Y)
                                    value: item.value, // Nome (Modelo)
                                    id: item.id
                                };
                            }));
                        }
                    });
                },
                minLength: 1, // Pode ajustar para 2 se preferir menos resultados iniciais
                select: function (event, ui) {
                    if (ui.item && ui.item.id) {
                        $("#filtro_peca_nome_display").val(ui.item.value); // Mostra "Nome (Modelo)"
                        $("#filtro_peca_id").val(ui.item.id);
                        pecaSelecionadaFiltroSaida = true;
                    }
                    return false;
                },
                change: function (event, ui) {
                    // Se o campo foi alterado mas nenhum item válido foi selecionado do autocomplete
                    if (!pecaSelecionadaFiltroSaida && !ui.item) {
                        $("#filtro_peca_id").val(''); // Limpa o ID se o texto não corresponde a uma seleção
                    }
                    // Se um item foi selecionado (ui.item existe), a flag pecaSelecionadaFiltroSaida já é true
                    // e o ID já está setado, então não fazemos nada aqui.
                }
            });
            // Limpar ID se o campo de nome da peça for limpo manualmente
            $("#filtro_peca_nome_display").on('input', function () {
                if ($(this).val() === '') {
                    $("#filtro_peca_id").val('');
                    pecaSelecionadaFiltroSaida = false;
                } else if (!pecaSelecionadaFiltroSaida) { // Se está digitando e não veio de uma seleção
                    $("#filtro_peca_id").val('');
                }
            });


            // Autocomplete para Atendimento no Filtro
            let atendimentoSelecionadoFiltroSaida = ($('#filtro_atendimento_id').val() !== '');
            $("#filtro_atendimento_info").autocomplete({
                source: function (request, response) {
                    atendimentoSelecionadoFiltroSaida = false; // Reseta
                    $.ajax({
                        url: "{{ route('atendimentos.autocomplete') }}",
                        dataType: "json", data: { search_autocomplete: request.term },
                        success: function (dataFromServer) {
                            response($.map(dataFromServer, function (item) {
                                let label = `#${item.id}`;
                                if (item.cliente) label += ` - ${item.cliente.nome_completo}`;
                                else label += ` - Cliente não assoc.`;
                                if (item.descricao_aparelho) {
                                    let descAparelho = item.descricao_aparelho;
                                    label += ` (${descAparelho.length > 20 ? descAparelho.substring(0, 20) + "..." : descAparelho})`;
                                }
                                if (item.status) label += ` [${item.status}]`;
                                return {
                                    label: label,
                                    value: `#${item.id} - ${item.cliente ? item.cliente.nome_completo : 'Sem Cliente'}`,
                                    id: item.id
                                };
                            }));
                        }
                    });
                },
                minLength: 1,
                select: function (event, ui) {
                    if (ui.item && ui.item.id) {
                        $("#filtro_atendimento_info").val(ui.item.value);
                        $("#filtro_atendimento_id").val(ui.item.id);
                        atendimentoSelecionadoFiltroSaida = true;
                    }
                    return false;
                },
                change: function (event, ui) {
                    if (!atendimentoSelecionadoFiltroSaida && !ui.item) {
                        $("#filtro_atendimento_id").val('');
                    }
                }
            });
            // Limpar ID se o campo de info do atendimento for limpo manualmente
            $("#filtro_atendimento_info").on('input', function () {
                if ($(this).val() === '') {
                    $("#filtro_atendimento_id").val('');
                    atendimentoSelecionadoFiltroSaida = false;
                } else if (!atendimentoSelecionadoFiltroSaida) {
                    $('#filtro_atendimento_id').val('');
                }
            });
        });
    </script>
@endpush
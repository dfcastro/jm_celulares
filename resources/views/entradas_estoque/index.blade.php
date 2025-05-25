@extends('layouts.app')

@section('title', 'Histórico de Entradas de Estoque')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    {{-- jQuery UI CSS já deve estar no layouts.app --}}
    <style>
        .card-header-filtros {
            background-color: #f8f9fa;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .col-acoes-entrada {
            min-width: 100px;
            width: 100px;
            text-align: center;
        }

        /* Melhorias CSS Adicionais */
        .table .btn-group-sm .btn, .table .btn-sm {
            padding: 0.2rem 0.4rem;
            font-size: 0.78rem;
        }
        .table .btn-info:hover, .table .btn-danger:hover {
            opacity: 0.85;
        }
        .text-muted.fst-italic, .text-danger { /* Para "Peça não encontrada" ou "Não Vinculado" */
            font-size: 0.9em;
        }
        .table td small { /* Para Modelo/Marca */
            font-size: 0.85em;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-0">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <h1><i class="bi bi-box-arrow-in-down"></i> Histórico de Entradas de Estoque</h1>
            <div>
                <a href="{{ route('entradas-estoque.create') }}" class="btn btn-primary me-1">
                    <i class="bi bi-plus-circle-fill"></i> Nova Entrada
                </a>
                <a href="{{ route('estoque.index') }}" class="btn btn-secondary">
                    <i class="bi bi-boxes"></i> Voltar para Estoque
                </a>
            </div>
        </div>

        {{-- Mensagens de Feedback --}}
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
        {{-- Adicione outros feedbacks de sessão se necessário --}}

        {{-- Card de Filtros --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header card-header-filtros">
                <h5 class="my-0"><i class="bi bi-funnel-fill"></i> Filtros de Pesquisa</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('entradas-estoque.index') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6 col-lg-3">
                            <label for="data_inicial_filtro" class="form-label form-label-sm">Data Entrada (De):</label>
                            <input type="date" class="form-control form-control-sm" id="data_inicial_filtro"
                                name="data_inicial_filtro" value="{{ request('data_inicial_filtro') }}">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="data_final_filtro" class="form-label form-label-sm">Data Entrada (Até):</label>
                            <input type="date" class="form-control form-control-sm" id="data_final_filtro"
                                name="data_final_filtro" value="{{ request('data_final_filtro') }}">
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label for="filtro_peca_nome_display" class="form-label form-label-sm">Peça/Acessório:</label>
                            <input type="text" class="form-control form-control-sm" id="filtro_peca_nome_display"
                                name="filtro_peca_nome_display" placeholder="Digite nome, modelo ou ID da peça..."
                                value="{{ old('filtro_peca_nome_display', $pecaSelecionadaNome ?? '') }}">
                            <input type="hidden" id="filtro_peca_id" name="filtro_peca_id"
                                value="{{ request('filtro_peca_id') }}">
                        </div>
                        <div class="col-md-6 col-lg-2 d-flex align-items-end mt-lg-0 mt-2">
                            <div class="input-group input-group-sm">
                                <button class="btn btn-primary w-50" type="submit"
                                    style="border-top-right-radius: 0; border-bottom-right-radius: 0;"
                                    data-bs-toggle="tooltip" title="Aplicar Filtros"><i class="bi bi-search"></i>
                                    Filtrar</button>
                                <a href="{{ route('entradas-estoque.index') }}" class="btn btn-secondary w-50"
                                    style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
                                    data-bs-toggle="tooltip" title="Limpar Filtros"><i class="bi bi-eraser-fill"></i>
                                    Limpar</a>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-12">
                            <label for="busca_obs_entrada" class="form-label form-label-sm">Buscar em Observações:</label>
                            <input type="text" class="form-control form-control-sm" id="busca_obs_entrada"
                                name="busca_obs_entrada" value="{{ request('busca_obs_entrada') }}"
                                placeholder="Termo na observação...">
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
                        <th class="text-center">Qtd.</th>
                        <th>Data Entrada</th>
                        <th>Observações</th>
                        <th class="col-acoes-entrada text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entradas as $entrada)
                        <tr>
                            <td>{{ $entrada->id }}</td>
                            <td>
                                @if($entrada->estoque)
                                    <a href="{{ route('estoque.show', $entrada->estoque->id) }}" data-bs-toggle="tooltip"
                                        title="Ver detalhes de {{ $entrada->estoque->nome }}">
                                        {{ $entrada->estoque->nome }}
                                    </a>
                                @else
                                    <span class="text-danger" data-bs-toggle="tooltip"
                                        title="Esta peça pode ter sido removida do cadastro de estoque.">Peça não encontrada</span>
                                @endif
                            </td>
                            <td>
                                @if($entrada->estoque)
                                    <small class="d-block text-muted">{{ $entrada->estoque->modelo_compativel ?? 'N/A' }}</small>
                                    <small class="d-block text-muted">Marca: {{ $entrada->estoque->marca ?? 'N/A' }}</small>
                                @else
                                    <span class="text-muted fst-italic">N/A</span>
                                @endif
                            </td>
                            <td class="text-center fw-bold">{{ $entrada->quantidade }}</td> {{-- Adicionado fw-bold para
                            destacar --}}
                            <td>{{ $entrada->data_entrada->format('d/m/Y H:i') }}</td>
                            <td data-bs-toggle="tooltip" title="{{ $entrada->observacoes }}">
                                {{ Str::limit($entrada->observacoes, 35) ?? '-' }}
                            </td>
                            <td class="col-acoes-entrada text-center">
                                <a href="{{ route('entradas-estoque.show', $entrada->id) }}" class="btn btn-info btn-sm"
                                    data-bs-toggle="tooltip" title="Ver Detalhes da Entrada">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                @can('is-admin')
                                    <form action="{{ route('entradas-estoque.destroy', $entrada->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Tem certeza que deseja excluir esta entrada? A quantidade será REMOVIDA do estoque atual do item.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip"
                                            title="Excluir Entrada (Estorna Estoque)">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">Nenhuma entrada de estoque registrada com os filtros
                                aplicados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($entradas->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $entradas->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    {{-- jQuery e jQuery UI já devem estar no layouts.app.blade.php --}}
    <script>
        $(document).ready(function () {
            // Inicialização de Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Autocomplete para Peça no Filtro
            let pecaSelecionadaFiltro = ($('#filtro_peca_id').val() !== '');

            $("#filtro_peca_nome_display").autocomplete({
                source: function (request, response) {
                    pecaSelecionadaFiltro = false;
                    $.ajax({
                        url: "{{ route('estoque.autocomplete') }}",
                        dataType: "json",
                        data: { search: request.term },
                        success: function (data) {
                            response($.map(data, function (item) {
                                return {
                                    label: item.label, // Já formatado no controller
                                    value: item.value, // Nome (Modelo)
                                    id: item.id
                                };
                            }));
                        }
                    });
                },
                minLength: 1,
                select: function (event, ui) {
                    if (ui.item && ui.item.id) {
                        // O campo 'value' do autocomplete já tem o nome e modelo.
                        // O campo 'label' tem mais detalhes (qtd, tipo) que não queremos no input.
                        $("#filtro_peca_nome_display").val(ui.item.value);
                        $("#filtro_peca_id").val(ui.item.id);
                        pecaSelecionadaFiltro = true;
                    }
                    return false;
                },
                change: function (event, ui) {
                    if (!pecaSelecionadaFiltro && !ui.item) {
                        $("#filtro_peca_id").val('');
                    }
                }
            });

            $("#filtro_peca_nome_display").on('input', function () {
                if ($(this).val() === '') {
                    $("#filtro_peca_id").val('');
                    pecaSelecionadaFiltro = false;
                } else if (!pecaSelecionadaFiltro) {
                    $("#filtro_peca_id").val('');
                }
            });
        });
    </script>
@endpush
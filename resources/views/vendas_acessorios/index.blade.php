@extends('layouts.app')

@section('title', 'Lista de Vendas de Acessórios')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .card-header-filtros { background-color: #f8f9fa; }
        .table th, .table td { vertical-align: middle; }
        .col-acoes-venda { min-width: 100px; width: 100px; text-align: center; }
        .table .btn-sm { padding: 0.2rem 0.4rem; font-size: 0.78rem; }
    </style>
@endpush

@section('content')
<div class="container mt-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h1><i class="bi bi-receipt-cutoff"></i> Lista de Vendas de Acessórios</h1>
        <div>
            <a href="{{ route('vendas-acessorios.create') }}" class="btn btn-primary mb-1 me-1" data-bs-toggle="tooltip" title="Registrar uma nova venda">
                <i class="bi bi-cart-plus-fill"></i> Nova Venda
            </a>
            <a href="{{ route('estoque.index') }}" class="btn btn-secondary mb-1" data-bs-toggle="tooltip" title="Voltar para gestão de estoque">
                <i class="bi bi-boxes"></i> Estoque
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

    {{-- Card de Filtros --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header card-header-filtros">
            <h5 class="my-0"><i class="bi bi-funnel-fill"></i> Filtros de Pesquisa</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('vendas-acessorios.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6 col-lg-3">
                        <label for="data_inicial_filtro" class="form-label form-label-sm">Data Venda (De):</label>
                        <input type="date" class="form-control form-control-sm" id="data_inicial_filtro" name="data_inicial_filtro" value="{{ $request->input('data_inicial_filtro') }}">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="data_final_filtro" class="form-label form-label-sm">Data Venda (Até):</label>
                        <input type="date" class="form-control form-control-sm" id="data_final_filtro" name="data_final_filtro" value="{{ $request->input('data_final_filtro') }}">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="filtro_cliente_nome_display" class="form-label form-label-sm">Cliente:</label>
                        <input type="text" class="form-control form-control-sm" id="filtro_cliente_nome_display" name="filtro_cliente_nome_display" placeholder="Nome ou CPF/CNPJ"
                               value="{{ old('filtro_cliente_nome_display', $clienteSelecionadoNome ?? '') }}">
                        <input type="hidden" id="filtro_cliente_id" name="filtro_cliente_id" value="{{ $request->input('filtro_cliente_id') }}">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="filtro_forma_pagamento" class="form-label form-label-sm">Forma de Pagamento:</label>
                        <select class="form-select form-select-sm" id="filtro_forma_pagamento" name="filtro_forma_pagamento">
                            <option value="">Todas</option>
                            @foreach ($formasPagamentoDisponiveis as $forma)
                                <option value="{{ $forma }}" {{ $request->input('filtro_forma_pagamento') == $forma ? 'selected' : '' }}>
                                    {{ $forma }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                     <div class="col-md-6 col-lg-3 mt-md-2">
                        <label for="filtro_usuario_id" class="form-label form-label-sm">Registrado por:</label>
                        <select class="form-select form-select-sm" id="filtro_usuario_id" name="filtro_usuario_id">
                            <option value="">Todos</option>
                            @foreach ($usuariosSistema as $usuario)
                                <option value="{{ $usuario->id }}" {{ $request->input('filtro_usuario_id') == $usuario->id ? 'selected' : '' }}>
                                    {{ $usuario->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-9 mt-md-2 d-flex align-items-end justify-content-end">
                        <button class="btn btn-primary btn-sm me-2" type="submit" data-bs-toggle="tooltip" title="Aplicar Filtros"><i class="bi bi-search"></i> Filtrar</button>
                        <a href="{{ route('vendas-acessorios.index') }}" class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" title="Limpar Filtros"><i class="bi bi-eraser-fill"></i> Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($vendas->total() > 0)
        <div class="mb-2 text-muted small">
            Exibindo {{ $vendas->firstItem() }} a {{ $vendas->lastItem() }} de {{ $vendas->total() }} vendas encontradas.
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered table-sm">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Cliente</th>
                    <th class="text-end">Valor Total</th>
                    <th>Forma Pag.</th>
                    <th>Registr. por</th>
                    <th>Observações</th>
                    <th class="col-acoes-venda text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($vendas as $venda)
                    <tr>
                        <td>{{ $venda->id }}</td>
                        <td>{{ $venda->data_venda_formatada }}</td>
                        <td>
                            @if($venda->cliente)
                                <a href="{{ route('clientes.show', $venda->cliente->id) }}" data-bs-toggle="tooltip" title="Ver detalhes de {{ $venda->cliente->nome_completo }}">
                                    {{ Str::limit($venda->cliente->nome_completo, 20) }}
                                </a>
                            @else
                                Venda Balcão
                            @endif
                        </td>
                        <td class="text-end fw-bold">R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
                        <td>{{ $venda->forma_pagamento ?? '-' }}</td>
                        <td>{{ $venda->usuarioRegistrou->name ?? 'N/A' }}</td>
                        <td data-bs-toggle="tooltip" title="{{ $venda->observacoes }}">
                            {{ Str::limit($venda->observacoes, 25) ?? '-' }}
                        </td>
                        <td class="col-acoes-venda text-center">
                            <a href="{{ route('vendas-acessorios.show', $venda->id) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="Ver Detalhes da Venda">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                            <a href="{{ route('vendas-acessorios.devolver.form', $venda->id) }}" class="btn btn-warning btn-sm mt-1 mt-md-0 ms-md-1" data-bs-toggle="tooltip" title="Devolver Itens desta Venda">
                                <i class="bi bi-arrow-return-left"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">Nenhuma venda de acessório registrada com os filtros aplicados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($vendas->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $vendas->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            let clienteSelecionadoFiltroVenda = ($('#filtro_cliente_id').val() !== '');
            $("#filtro_cliente_nome_display").autocomplete({
                source: function (request, response) { /* ... (código do autocomplete como antes) ... */
                    clienteSelecionadoFiltroVenda = false;
                    $.ajax({
                        url: "{{ route('clientes.autocomplete') }}",
                        dataType: "json", data: { search: request.term },
                        success: function (data) {
                            response($.map(data, function (item) {
                                return { label: item.label, value: item.value, id: item.id };
                            }));
                        }
                    });
                 },
                minLength: 2,
                select: function (event, ui) { /* ... (código do select como antes) ... */
                    if (ui.item && ui.item.id) {
                        $("#filtro_cliente_nome_display").val(ui.item.value);
                        $("#filtro_cliente_id").val(ui.item.id);
                        clienteSelecionadoFiltroVenda = true;
                    }
                    return false;
                },
                change: function (event, ui) { /* ... (código do change como antes) ... */
                    if (!clienteSelecionadoFiltroVenda && !ui.item) {
                        $("#filtro_cliente_id").val('');
                    }
                }
            });
            $("#filtro_cliente_nome_display").on('input', function() { /* ... (código do input como antes) ... */
                if ($(this).val() === '') {
                    $("#filtro_cliente_id").val('');
                    clienteSelecionadoFiltroVenda = false;
                } else if (!clienteSelecionadoFiltroVenda) {
                     $("#filtro_cliente_id").val('');
                }
            });
        });
    </script>
@endpush
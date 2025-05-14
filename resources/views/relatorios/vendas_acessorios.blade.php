{{-- resources/views/relatorios/vendas_acessorios.blade.php --}}
@extends('layouts.app')

@section('title', 'Relatório: Vendas de Acessórios por Período')

@section('content')
    <div class="container mt-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Relatório: Vendas de Acessórios</h1>
        </div>

        {{-- Formulário de Filtros --}}
        <form action="{{ route('relatorios.vendas_acessorios') }}" method="GET" class="mb-4 p-3 border rounded bg-light">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="data_inicial" class="form-label">Data Inicial:</label>
                    <input type="date" class="form-control form-control-sm" id="data_inicial" name="data_inicial"
                        value="{{ $dataInicial->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label for="data_final" class="form-label">Data Final:</label>
                    <input type="date" class="form-control form-control-sm" id="data_final" name="data_final"
                        value="{{ $dataFinal->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label for="cliente_nome_filtro" class="form-label">Cliente (Opcional):</label>
                    <input type="text" class="form-control form-control-sm" id="cliente_nome_filtro"
                        name="cliente_nome_filtro" placeholder="Digite nome ou CPF/CNPJ"
                        value="{{ old('cliente_nome_filtro', $clienteSelecionado->nome_completo ?? '') }}">
                    <input type="hidden" id="cliente_id_filtro" name="cliente_id"
                        value="{{ old('cliente_id', $clienteId ?? '') }}">
                    {{-- Guardamos o clienteId selecionado para enviar ao controller --}}
                    {{-- E o cliente_nome_filtro é para exibir o nome no campo e para o autocomplete --}}
                </div>
                <div class="col-md-3">
                    <label for="forma_pagamento" class="form-label">Forma de Pagamento (Opcional):</label>
                    <select class="form-select form-select-sm" id="forma_pagamento" name="forma_pagamento">
                        <option value="">Todas as Formas</option>
                        @foreach ($formasPagamentoDisponiveis as $forma)
                            <option value="{{ $forma }}" {{ $formaPagamento == $forma ? 'selected' : '' }}>
                                {{ $forma }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 mt-3 text-end">
                    <button class="btn btn-primary btn-sm" type="submit">
                        <i class="bi bi-funnel"></i> Gerar Relatório
                    </button>
                    <a href="{{ route('relatorios.vendas_acessorios') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-eraser"></i> Limpar Filtros
                    </a>
                </div>
            </div>
            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </form>

        {{-- Resultados --}}
        @if(request()->has('data_inicial')) {{-- Mostra resultados apenas se o formulário foi submetido --}}
            @if($vendas->isEmpty())
                <div class="alert alert-info" role="alert">
                    Nenhuma venda encontrada para o período e filtros selecionados.
                </div>
            @else
                <div class="card">
                    <div class="card-header">
                        Resultados do Período: {{ $dataInicial->format('d/m/Y') }} - {{ $dataFinal->format('d/m/Y') }}
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID Venda</th>
                                        <th>Data</th>
                                        <th>Cliente</th>
                                        <th>Forma de Pagamento</th>
                                        <th class="text-end">Valor Total (R$)</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($vendas as $venda)
                                        <tr>
                                            <td>{{ $venda->id }}</td>
                                            <td>{{ $venda->data_venda->format('d/m/Y H:i') }}</td>
                                            <td>{{ $venda->cliente->nome_completo ?? 'Venda Balcão' }}</td>
                                            <td>{{ $venda->forma_pagamento ?? 'N/A' }}</td>
                                            <td class="text-end">{{ number_format($venda->valor_total, 2, ',', '.') }}</td>
                                            <td>
                                                <a href="{{ route('vendas-acessorios.show', $venda->id) }}" class="btn btn-sm btn-info"
                                                    title="Detalhes da Venda">
                                                    <i class="bi bi-eye"></i> Ver Detalhes
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-secondary">
                                        <td colspan="4" class="text-end fw-bold">Total de Vendas no Período:</td>
                                        <td class="text-end fw-bold">{{ number_format($totalVendasValor, 2, ',', '.') }}</td>
                                        <td>({{ $numeroDeVendas }} venda(s))</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Links de Paginação --}}
                        @if ($vendas->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $vendas->appends(request()->query())->links() }} {{-- Mantém os filtros na paginação --}}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @else
            <div class="alert alert-light text-center" role="alert">
                <i class="bi bi-info-circle"></i> Selecione os filtros acima e clique em "Gerar Relatório" para visualizar os
                dados.
            </div>
        @endif


        <div class="mt-4">
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar ao Painel
            </a>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    {{-- jQuery UI CSS já está no layouts/app.blade.php --}}
@endpush

@push('scripts')
{{-- jQuery e jQuery UI já estão carregados no layouts/app.blade.php --}}
<script>
    $(document).ready(function() {
        // Autocomplete para o filtro de cliente no relatório
        $("#cliente_nome_filtro").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('clientes.autocomplete') }}", // Rota que você já usa para buscar clientes
                    type: 'GET',
                    dataType: "json",
                    data: {
                        search: request.term
                    },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: item.nome_completo + (item.cpf_cnpj ? ' (' + item.cpf_cnpj + ')' : ''),
                                value: item.nome_completo, // O que vai para o input de texto
                                id: item.id // O ID do cliente
                            };
                        }));
                    }
                });
            },
            minLength: 2, // Começar a buscar após 2 caracteres
            select: function(event, ui) {
                $("#cliente_nome_filtro").val(ui.item.label); // Mostra o nome completo e CPF/CNPJ
                $("#cliente_id_filtro").val(ui.item.id);    // Guarda o ID no campo hidden
                return false; // Impede que o jQuery UI altere o valor do input para ui.item.value
            },
            // Opcional: Limpar o ID se o texto for alterado e não corresponder a um item
            change: function(event, ui) {
                if (!ui.item) {
                    $("#cliente_id_filtro").val('');
                    // Se o campo de texto não estiver vazio e nenhum item foi selecionado,
                    // talvez você queira limpar o campo de texto também ou deixar como está.
                    // Se quiser limpar o texto se não selecionar:
                    // if ($("#cliente_nome_filtro").val() !== '') {
                    //     $("#cliente_nome_filtro").val('');
                    // }
                }
            }
        });

        // Se o campo cliente_nome_filtro for limpo manualmente, limpar também o cliente_id_filtro
        $("#cliente_nome_filtro").on('input', function() {
            if ($(this).val() === '') {
                $("#cliente_id_filtro").val('');
            }
        });
    });
</script>
@endpush

{{-- Opcional: Adicionar JavaScript para datepickers se quiser uma UI melhor para as datas --}}
{{-- @push('scripts')
<script>
    // Exemplo com jQuery UI Datepicker (já temos jQuery UI no layout)
    // $(function() {
    //     $("#data_inicial, #data_final").datepicker({
    //         dateFormat: "yy-mm-dd" // Formato que o HTML input type="date" espera
    //     });
    // });
</script>
@endpush --}}
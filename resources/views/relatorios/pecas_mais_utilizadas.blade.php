{{-- resources/views/relatorios/pecas_mais_utilizadas.blade.php --}}
@extends('layouts.app')

@section('title', 'Relatório: Peças Mais Utilizadas em Reparos')

@section('content')
    <div class="container mt-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Relatório: Peças Mais Utilizadas em Reparos</h1>
        </div>

        {{-- Formulário de Filtros --}}
        <form action="{{ route('relatorios.pecas_mais_utilizadas') }}" method="GET" class="mb-4 p-3 border rounded bg-light">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="data_inicial" class="form-label">Data Inicial (Saída):</label>
                    <input type="date" class="form-control form-control-sm" id="data_inicial" name="data_inicial"
                           value="{{ $dataInicial->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label for="data_final" class="form-label">Data Final (Saída):</label>
                    <input type="date" class="form-control form-control-sm" id="data_final" name="data_final"
                           value="{{ $dataFinal->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label for="tipo_item" class="form-label">Tipo da Peça (Opcional):</label>
                    <select class="form-select form-select-sm" id="tipo_item" name="tipo_item">
                        <option value="">Todos os Tipos</option>
                        {{-- Sugestão: Focar em tipos relevantes para reparo --}}
                        <option value="PECA_REPARO" {{ $tipoItem == 'PECA_REPARO' ? 'selected' : '' }}>Peça p/ Reparo</option>
                        <option value="GERAL" {{ $tipoItem == 'GERAL' ? 'selected' : '' }}>Geral</option>
                        @foreach ($tiposDeItemParaFiltro->diff(['PECA_REPARO', 'GERAL']) as $tipo) {{-- Mostra outros tipos, se houver --}}
                            <option value="{{ $tipo }}" {{ $tipoItem == $tipo ? 'selected' : '' }}>
                                @if($tipo == 'ACESSORIO_VENDA') Acessório p/ Venda @else {{ $tipo }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="limite_resultados" class="form-label">Mostrar Top:</label>
                    <select class="form-select form-select-sm" id="limite_resultados" name="limite_resultados">
                        <option value="5" {{ $limiteResultados == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ $limiteResultados == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $limiteResultados == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $limiteResultados == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary btn-sm w-100" type="submit">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
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
        @if(request()->has('data_inicial'))
            @if($pecasMaisUtilizadas->isEmpty())
                <div class="alert alert-info" role="alert">
                    Nenhuma peça utilizada em reparos encontrada para o período e filtros selecionados.
                </div>
            @else
                <div class="card">
                    <div class="card-header">
                        Top {{ $limiteResultados }} Peças Mais Utilizadas de {{ $dataInicial->format('d/m/Y') }} até {{ $dataFinal->format('d/m/Y') }}
                        @if($tipoItem)
                            (Tipo:
                            @if($tipoItem == 'PECA_REPARO') Peça p/ Reparo
                            @elseif($tipoItem == 'ACESSORIO_VENDA') Acessório p/ Venda
                            @elseif($tipoItem == 'GERAL') Geral
                            @else {{ $tipoItem }}
                            @endif)
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Peça</th>
                                        <th>Modelo</th>
                                        <th>Marca</th>
                                        <th>Tipo Estoque</th>
                                        <th class="text-center">Total Utilizado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pecasMaisUtilizadas as $index => $item)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                <a href="{{ route('estoque.show', $item->estoque_id) }}">{{ $item->nome_item }}</a>
                                            </td>
                                            <td>{{ $item->modelo_compativel ?? 'N/A' }}</td>
                                            <td>{{ $item->marca ?? 'N/A' }}</td>
                                            <td>
                                                @if($item->tipo_item_estoque == 'PECA_REPARO') Peça p/ Reparo
                                                @elseif($item->tipo_item_estoque == 'ACESSORIO_VENDA') Acessório p/ Venda
                                                @elseif($item->tipo_item_estoque == 'GERAL') Geral
                                                @else {{ $item->tipo_item_estoque ?? 'N/D' }}
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold">{{ $item->total_utilizado }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="alert alert-light text-center" role="alert">
                <i class="bi bi-info-circle"></i> Selecione os filtros acima e clique em "Filtrar" para visualizar os dados.
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
@endpush
{{-- resources/views/relatorios/atendimentos_tecnico.blade.php --}}
@extends('layouts.app')

@section('title', 'Relatório: Atendimentos por Técnico')

@section('content')
    <div class="container mt-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Relatório: Atendimentos por Técnico</h1>
        </div>

        {{-- Formulário de Filtros --}}
        <form action="{{ route('relatorios.atendimentos_tecnico') }}" method="GET" class="mb-4 p-3 border rounded bg-light">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="data_inicial" class="form-label">Data Inicial (Entrada Atend.):</label>
                    <input type="date" class="form-control form-control-sm" id="data_inicial" name="data_inicial"
                           value="{{ $dataInicial->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label for="data_final" class="form-label">Data Final (Entrada Atend.):</label>
                    <input type="date" class="form-control form-control-sm" id="data_final" name="data_final"
                           value="{{ $dataFinal->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label for="tecnico_id" class="form-label">Técnico (Opcional):</label>
                    <select class="form-select form-select-sm" id="tecnico_id" name="tecnico_id">
                        <option value="">Todos os Técnicos / Não Atribuídos</option>
                        @foreach ($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id }}" {{ $tecnicoId == $tecnico->id ? 'selected' : '' }}>
                                {{ $tecnico->name }}
                            </option>
                        @endforeach
                        <option value="0" {{ (is_numeric($tecnicoId) && intval($tecnicoId) === 0) ? 'selected' : '' }}>Apenas Não Atribuídos</option>
                    </select>
                </div>
                {{-- Opcional: Checkbox para mostrar detalhes de todos
                <div class="col-md-3">
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="mostrar_todos_detalhes" name="mostrar_todos_detalhes" {{ request('mostrar_todos_detalhes') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="mostrar_todos_detalhes">
                            Listar todos os atendimentos
                        </label>
                    </div>
                </div>
                --}}
                <div class="col-md-12 mt-3 text-end">
                    <button class="btn btn-primary btn-sm" type="submit">
                        <i class="bi bi-funnel"></i> Gerar Relatório
                    </button>
                    <a href="{{ route('relatorios.atendimentos_tecnico') }}" class="btn btn-secondary btn-sm">
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

        {{-- Resumo por Técnico --}}
        @if(request()->has('data_inicial'))
            <h3 class="mb-3">Resumo por Técnico no Período <small class="text-muted">({{ $dataInicial->format('d/m/Y') }} - {{ $dataFinal->format('d/m/Y') }})</small></h3>
            @if($atendimentosPorTecnico->isEmpty())
                <div class="alert alert-info">Nenhum atendimento encontrado para o período
                    @if($tecnicoId)
                        e técnico selecionado.
                    @else
                        selecionado.
                    @endif
                </div>
            @else
                <div class="list-group mb-4">
                    @foreach ($atendimentosPorTecnico as $nomeTecnico => $total)
                        <div class="list-group-item d-flex justify-content-between align-items-center {{ ($tecnicos->firstWhere('name', $nomeTecnico) && $tecnicoId == $tecnicos->firstWhere('name', $nomeTecnico)->id) || ($nomeTecnico == 'Não Atribuído' && is_numeric($tecnicoId) && intval($tecnicoId) === 0) ? 'active' : '' }}">
                            {{ $nomeTecnico }}
                            <span class="badge bg-primary rounded-pill">{{ $total }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Listagem Detalhada de Atendimentos (se um técnico foi selecionado OU se "mostrar_todos_detalhes" está ativo) --}}
            @if ($atendimentosDetalhados)
                <hr class="my-4">
                <h4 class="mb-3">
                    Detalhes dos Atendimentos
                    @if($tecnicoId && $tecnicos->find($tecnicoId))
                        para: {{ $tecnicos->find($tecnicoId)->name }}
                    @elseif(is_numeric($tecnicoId) && intval($tecnicoId) === 0)
                        (Não Atribuídos)
                    @elseif(request('mostrar_todos_detalhes') == '1')
                        (Todos os Técnicos)
                    @endif
                </h4>
                @if($atendimentosDetalhados->isEmpty())
                    <div class="alert alert-info">Nenhum atendimento detalhado encontrado para a seleção.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Celular</th>
                                    <th>Data Entrada</th>
                                    <th>Status</th>
                                    @if(!$tecnicoId && request('mostrar_todos_detalhes') == '1') {{-- Mostra técnico se estiver listando todos --}}
                                        <th>Técnico</th>
                                    @endif
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($atendimentosDetalhados as $atendimento)
                                    <tr>
                                        <td>{{ $atendimento->id }}</td>
                                        <td>{{ $atendimento->cliente->nome_completo ?? 'N/A' }}</td>
                                        <td>{{ $atendimento->celular }}</td>
                                        <td>{{ $atendimento->data_entrada->format('d/m/Y') }}</td>
                                        <td>{{ $atendimento->status }}</td>
                                        @if(!$tecnicoId && request('mostrar_todos_detalhes') == '1')
                                            <td>{{ $atendimento->tecnico->name ?? 'Não atribuído' }}</td>
                                        @endif
                                        <td>
                                            <a href="{{ route('atendimentos.show', $atendimento->id) }}" class="btn btn-xs btn-outline-info" title="Ver Detalhes">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- Paginação para a lista detalhada --}}
                    @if ($atendimentosDetalhados->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $atendimentosDetalhados->appends(request()->query())->links() }}
                        </div>
                    @endif
                @endif
            @elseif(request()->has('data_inicial') && $tecnicoId)
                 <div class="alert alert-info mt-3">Nenhum atendimento detalhado encontrado para o técnico selecionado no período.</div>
            @endif
        @else
            <div class="alert alert-light text-center" role="alert">
                <i class="bi bi-info-circle"></i> Selecione os filtros acima e clique em "Gerar Relatório" para visualizar os dados.
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
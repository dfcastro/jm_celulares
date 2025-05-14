{{-- resources/views/relatorios/atendimentos_status.blade.php --}}
@extends('layouts.app')

@section('title', 'Relatório: Atendimentos por Status')

@section('content')
    <div class="container mt-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Relatório: Atendimentos por Status</h1>
        </div>

        {{-- Formulário de Filtros --}}
        <form action="{{ route('relatorios.atendimentos_status') }}" method="GET" class="mb-4 p-3 border rounded bg-light">
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
                    <label class="form-label">&nbsp;</label> {{-- Espaçador --}}
                    <button class="btn btn-primary btn-sm w-100" type="submit">
                        <i class="bi bi-funnel"></i> Filtrar Período
                    </button>
                    {{-- Link para limpar todos os filtros, incluindo ver_status --}}
                    <a href="{{ route('relatorios.atendimentos_status') }}" class="btn btn-secondary btn-sm w-100 mt-1">
                        <i class="bi bi-eraser"></i> Limpar Filtros e Visualização
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

        {{-- Resultados em Cards --}}
        <h3 class="mb-3">Resumo por Status no Período <small class="text-muted">({{ $dataInicial->format('d/m/Y') }} - {{ $dataFinal->format('d/m/Y') }})</small></h3>
        <div class="row">
            @if(empty($statusComContagem))
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        Nenhum atendimento encontrado para o período selecionado ou nenhum status definido.
                    </div>
                </div>
            @else
                @foreach ($statusComContagem as $item)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card {{ $statusSelecionado == $item->status ? 'border-primary shadow' : '' }}">
                            <div class="card-header {{ $statusSelecionado == $item->status ? 'bg-primary text-white' : 'bg-light' }}">
                                {{ $item->status }}
                            </div>
                            <div class="card-body text-center">
                                <h3 class="card-title display-4">{{ $item->total_atendimentos }}</h3>
                                <p class="card-text">atendimento(s)</p>
                                @if($item->total_atendimentos > 0)
                                    {{-- O 'page' => null ou 'pagina_status' => null força o reset da paginação da lista detalhada ao mudar de status --}}
                                    <a href="{{ route('relatorios.atendimentos_status', array_merge(request()->only(['data_inicial', 'data_final']), ['ver_status' => $item->status, 'pagina_status' => 1])) }}"
                                       class="btn btn-sm {{ $statusSelecionado == $item->status ? 'btn-primary' : 'btn-outline-primary' }} mt-2">
                                        <i class="bi bi-list-ul"></i> {{ $statusSelecionado == $item->status ? 'Visualizando Lista' : 'Ver Lista' }} ({{ $item->total_atendimentos }})
                                    </a>
                                @else
                                    <p class="mt-2 mb-0 text-muted fst-italic">Nenhum atendimento neste status</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Seção para listar atendimentos de um status específico --}}
        @if ($statusSelecionado && $atendimentosDoStatusSelecionado)
            <hr class="my-4">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-3">Atendimentos: "{{ $statusSelecionado }}" <small class="text-muted">({{ $dataInicial->format('d/m/Y') }} - {{ $dataFinal->format('d/m/Y') }})</small></h3>
                <a href="{{ route('relatorios.atendimentos_status', request()->only(['data_inicial', 'data_final'])) }}" class="btn btn-sm btn-outline-secondary mb-3">
                    <i class="bi bi-x-circle"></i> Fechar Lista
                </a>
            </div>

            @if($atendimentosDoStatusSelecionado->isEmpty())
                <div class="alert alert-info">Nenhum atendimento encontrado com este status no período selecionado.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Celular</th>
                                <th>Data Entrada</th>
                                <th>Técnico</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($atendimentosDoStatusSelecionado as $atendimento)
                                <tr>
                                    <td>{{ $atendimento->id }}</td>
                                    <td>{{ $atendimento->cliente->nome_completo ?? 'N/A' }}</td>
                                    <td>{{ $atendimento->celular }}</td>
                                    <td>{{ $atendimento->data_entrada->format('d/m/Y') }}</td>
                                    <td>{{ $atendimento->tecnico->name ?? 'Não atribuído' }}</td>
                                    <td>
                                        <a href="{{ route('atendimentos.show', $atendimento->id) }}" class="btn btn-xs btn-outline-info" title="Ver Detalhes do Atendimento">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Paginação para a lista de atendimentos do status --}}
                @if ($atendimentosDoStatusSelecionado->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $atendimentosDoStatusSelecionado->appends(request()->all())->links() }}
                    </div>
                @endif
            @endif
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
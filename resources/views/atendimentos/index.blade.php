{{-- resources/views/atendimentos/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Lista de Atendimentos')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .status-badge {
            font-size: 0.85em;
            padding: 0.4em 0.7em;
        }
        /* Cores customizadas para os badges de status, se desejar */
        .badge.bg-status-diagnostico { background-color: #17a2b8 !important; color: white; } /* Bootstrap 'info' */
        .badge.bg-status-aguardando { background-color: #ffc107 !important; color: #212529; } /* Bootstrap 'warning' */
        .badge.bg-status-manutencao { background-color: #0d6efd !important; color: white; } /* Bootstrap 'primary' */
        .badge.bg-status-pronto { background-color: #198754 !important; color: white; } /* Bootstrap 'success' */
        .badge.bg-status-entregue { background-color: #6c757d !important; color: white; } /* Bootstrap 'secondary' */
    </style>
@endpush

@section('content')
    <div class="container mt-0">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <h1><i class="bi bi-headset"></i> Lista de Atendimentos</h1>
            @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
                <a href="{{ route('atendimentos.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Novo Atendimento
                </a>
            @endif
        </div>

        
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Card de Filtros --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header">
                <h5 class="my-0"><i class="bi bi-funnel-fill"></i> Filtros de Pesquisa</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('atendimentos.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <label for="busca_atendimento" class="form-label">Buscar por Cliente/Cód./ID:</label>
                            <input type="text" class="form-control form-control-sm" id="busca_atendimento" name="busca_atendimento"
                                   value="{{ request('busca_atendimento') }}" placeholder="Nome, CPF/CNPJ, Cód., ID">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="filtro_status" class="form-label">Status:</label>
                            <select class="form-select form-select-sm" id="filtro_status" name="filtro_status">
                                <option value="">Todos os Status</option>
                                @foreach ($todosOsStatus as $statusValue)
                                    <option value="{{ $statusValue }}" {{ request('filtro_status') == $statusValue ? 'selected' : '' }}>
                                        {{ $statusValue }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="filtro_tecnico_id" class="form-label">Técnico:</label>
                            <select class="form-select form-select-sm" id="filtro_tecnico_id" name="filtro_tecnico_id">
                                <option value="">Todos os Técnicos</option>
                                @foreach ($tecnicos as $tecnico)
                                    <option value="{{ $tecnico->id }}" {{ request('filtro_tecnico_id') == $tecnico->id ? 'selected' : '' }}>
                                        {{ $tecnico->name }}
                                    </option>
                                @endforeach
                                <option value="0" {{ request('filtro_tecnico_id') === '0' ? 'selected' : '' }}>Não Atribuído</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6 col-lg-3">
                            <label for="data_inicial_filtro" class="form-label">Data Entrada (De):</label>
                            <input type="date" class="form-control form-control-sm" id="data_inicial_filtro" name="data_inicial_filtro"
                                   value="{{ request('data_inicial_filtro') }}">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="data_final_filtro" class="form-label">Data Entrada (Até):</label>
                            <input type="date" class="form-control form-control-sm" id="data_final_filtro" name="data_final_filtro"
                                   value="{{ request('data_final_filtro') }}">
                        </div>
                        <div class="col-md-12 col-lg-6 d-flex align-items-end mt-3 mt-lg-0">
                            <button class="btn btn-primary btn-sm me-2" type="submit"><i class="bi bi-search"></i> Aplicar Filtros</button>
                            <a href="{{ route('atendimentos.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-eraser-fill"></i> Limpar Filtros</a>
                        </div>
                    </div>
                    @if ($errors->any() && ($errors->has('data_inicial_filtro') || $errors->has('data_final_filtro')))
                        <div class="alert alert-danger mt-3 py-2">
                            <ul class="mb-0 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered table-sm"> {{-- table-sm para mais compacta --}}
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Aparelho/Descrição</th>
                        <th>Data Entrada</th>
                        <th class="text-center">Status</th>
                        <th>Técnico</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($atendimentos as $atendimento)
                        {{-- A cor da linha inteira já foi removida, vamos focar no badge --}}
                        <tr>
                            <td>{{ $atendimento->id }}</td>
                            <td>
                                @if($atendimento->cliente)
                                <a href="{{ route('clientes.show', $atendimento->cliente->id) }}">{{ $atendimento->cliente->nome_completo }}</a>
                                <small class="d-block text-muted">{{ $atendimento->cliente->cpf_cnpj }}</small>
                                @else
                                <span class="text-muted fst-italic">Cliente não informado</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($atendimento->descricao_aparelho, 35) }}</td>
                            <td>{{ $atendimento->data_entrada->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                <span class="badge rounded-pill status-badge
                                    @if($atendimento->status == 'Em diagnóstico') bg-status-diagnostico
                                    @elseif($atendimento->status == 'Aguardando peça') bg-status-aguardando
                                    @elseif($atendimento->status == 'Em manutenção') bg-status-manutencao
                                    @elseif($atendimento->status == 'Pronto para entrega') bg-status-pronto
                                    @elseif($atendimento->status == 'Entregue') bg-status-entregue
                                    @else bg-light text-dark @endif">
                                    {{ $atendimento->status }}
                                </span>
                            </td>
                            <td>{{ $atendimento->tecnico->name ?? 'N/A' }}</td>
                            <td class="text-center">
                                <a href="{{ route('atendimentos.show', $atendimento->id) }}" class="btn btn-info btn-sm" title="Ver Detalhes"><i class="bi bi-eye-fill"></i></a>
                                @if(Gate::allows('is-admin-or-tecnico') || (Auth::user()->tipo_usuario == 'atendente' && !in_array($atendimento->status, ['Entregue']))) {{-- Exemplo de permissão para editar --}}
                                <a href="{{ route('atendimentos.edit', $atendimento->id) }}" class="btn btn-warning btn-sm" title="Editar"><i class="bi bi-pencil-fill"></i></a>
                                @endif
                                @can('is-admin-or-atendente') {{-- Quem pode excluir --}}
                                <form action="{{ route('atendimentos.destroy', $atendimento->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este atendimento?')"><i class="bi bi-trash-fill"></i></button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4">Nenhum atendimento encontrado com os filtros aplicados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($atendimentos->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $atendimentos->links() }}
            </div>
        @endif
    </div>
@endsection
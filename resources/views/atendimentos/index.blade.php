@extends('layouts.app')

@section('title', 'Lista de Atendimentos')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .status-badge {
            font-size: 0.80em; /* Levemente menor para caber texto e ícone */
            padding: 0.4em 0.65em;
            vertical-align: middle; /* Ajuda no alinhamento com o texto ao lado, se houver */
        }
        .status-badge .bi {
            font-size: 0.95em; /* Ícone um pouco menor que o texto do badge */
            /* vertical-align: text-bottom; /* Tenta alinhar melhor o ícone com o texto */
        }
        /* Cores customizadas para os badges de status de serviço (geral) */
        .badge.bg-status-em-aberto { background-color: #6c757d !important; color: white; }
        .badge.bg-status-em-diagnostico { background-color: #0dcaf0 !important; color: #000; }
        .badge.bg-status-aguardando-aprovacao-cliente { background-color: #ffc107 !important; color: #000; }
        .badge.bg-status-aguardando-peca { background-color: #fd7e14 !important; color: white; }
        .badge.bg-status-em-manutencao { background-color: #0d6efd !important; color: white; }
        .badge.bg-status-pronto-para-entrega { background-color: #198754 !important; color: white; }
        .badge.bg-status-cancelado { background-color: #dc3545 !important; color: white; }
        .badge.bg-status-reprovado { background-color: #495057 !important; color: white; }

        /* Adicionar uma classe para a coluna de ações para controlar melhor seu tamanho */
        .col-acoes {
            min-width: 130px; /* Ajuste conforme os botões que você tem */
            width: 130px;
            text-align: center;
        }
        /* Ajuste para o input-group dos botões de filtro em telas menores */
        @media (max-width: 991.98px) { /* Abaixo do LG, onde os filtros podem quebrar */
            .filter-buttons-group .btn {
                flex-grow: 1 !important;
            }
             .filter-buttons-group .input-group-sm { /* Garante que o input-group também se expanda */
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
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
    @if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
<div class="container mt-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h1><i class="bi bi-headset"></i> Lista de Atendimentos</h1>
        @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
            <a href="{{ route('atendimentos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Novo Atendimento
            </a>
        @endif
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <h5 class="my-0"><i class="bi bi-funnel-fill"></i> Filtros de Pesquisa</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('atendimentos.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6 col-lg-4">
                        <label for="busca_atendimento" class="form-label form-label-sm">Buscar por Cliente/Cód./ID:</label>
                        <input type="text" class="form-control form-control-sm" id="busca_atendimento" name="busca_atendimento"
                               value="{{ request('busca_atendimento') }}" placeholder="Nome, CPF/CNPJ, Cód., ID">
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label for="filtro_status" class="form-label form-label-sm">Status Serviço:</label>
                        <select class="form-select form-select-sm" id="filtro_status" name="filtro_status">
                            <option value="">Todos</option>
                            @if(isset($todosOsStatus))
                                @foreach ($todosOsStatus as $statusValue)
                                    <option value="{{ $statusValue }}" {{ request('filtro_status') == $statusValue ? 'selected' : '' }}>
                                        {{ $statusValue }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="filtro_status_pagamento" class="form-label form-label-sm">Status Pagamento:</label>
                        <select class="form-select form-select-sm" id="filtro_status_pagamento" name="filtro_status_pagamento">
                            <option value="">Todos</option>
                            @foreach (App\Models\Atendimento::getPossiblePaymentStatuses() as $sp)
                                <option value="{{ $sp }}" {{ request('filtro_status_pagamento') == $sp ? 'selected' : '' }}>
                                    {{ $sp }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="filtro_tecnico_id" class="form-label form-label-sm">Técnico:</label>
                        <select class="form-select form-select-sm" id="filtro_tecnico_id" name="filtro_tecnico_id">
                            <option value="">Todos</option>
                             @if(isset($tecnicos))
                                @foreach ($tecnicos as $tecnico)
                                    <option value="{{ $tecnico->id }}" {{ request('filtro_tecnico_id') == $tecnico->id ? 'selected' : '' }}>
                                        {{ $tecnico->name }}
                                    </option>
                                @endforeach
                            @endif
                            <option value="0" {{ request('filtro_tecnico_id') === '0' ? 'selected' : '' }}>Não Atribuído</option>
                        </select>
                    </div>

                    <div class="col-md-6 col-lg-3 mt-lg-3"> {{-- mt-lg-3 para alinhar com a linha de cima em telas grandes --}}
                        <label for="data_inicial_filtro" class="form-label form-label-sm">Entrada (De):</label>
                        <input type="date" class="form-control form-control-sm" id="data_inicial_filtro" name="data_inicial_filtro"
                               value="{{ request('data_inicial_filtro') }}">
                    </div>
                    <div class="col-md-6 col-lg-3 mt-lg-3">
                        <label for="data_final_filtro" class="form-label form-label-sm">Entrada (Até):</label>
                        <input type="date" class="form-control form-control-sm" id="data_final_filtro" name="data_final_filtro"
                               value="{{ request('data_final_filtro') }}">
                    </div>
                    <div class="col-md-12 col-lg-3 mt-lg-3 filter-buttons-group"> {{-- Adicionada classe para controle responsivo dos botões --}}
                        <label class="form-label form-label-sm d-block d-lg-none">&nbsp;</label> {{-- Label para espaço em mobile, some em lg --}}
                        <label class="form-label form-label-sm d-none d-lg-block">&nbsp;</label> {{-- Label para espaço em desktop --}}
                        <div class="input-group input-group-sm">
                            <button class="btn btn-primary" type="submit" style="border-top-right-radius: 0; border-bottom-right-radius: 0;"><i class="bi bi-search"></i> Aplicar</button>
                            <a href="{{ route('atendimentos.index') }}" class="btn btn-outline-secondary" style="border-top-left-radius: 0; border-bottom-left-radius: 0;"><i class="bi bi-eraser-fill"></i> Limpar</a>
                        </div>
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
        <table class="table table-striped table-hover table-bordered table-sm align-middle"> {{-- Adicionado align-middle à tabela --}}
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Aparelho/Descrição</th>
                    <th>Data Entrada</th>
                    <th class="text-center">Status Serviço</th>
                    <th class="text-center">Status Pgto.</th>
                    <th>Técnico</th>
                    <th class="col-acoes">Ações</th> {{-- Usando a classe para definir largura --}}
                </tr>
            </thead>
            <tbody>
                @forelse ($atendimentos as $atendimento)
                    <tr>
                        <td>{{ $atendimento->id }}</td>
                        <td class="align-middle">
    @if($atendimento->cliente)
        <a href="{{ route('clientes.show', $atendimento->cliente->id) }}"
           class="d-block text-decoration-none" {{-- d-block para o link ocupar a linha --}}
           data-bs-toggle="tooltip" data-bs-placement="top"
           title="{{ ucfirst($atendimento->cliente->nome_completo) }} ({{ $atendimento->cliente->cpf_cnpj }})">
            {{ Str::limit(ucfirst($atendimento->cliente->nome_completo), 22) }}
        </a>
        <small class="d-block text-muted" style="font-size: 0.78em; line-height: 1.2;">{{ $atendimento->cliente->cpf_cnpj }}</small>
    @else
        <span class="text-muted fst-italic">Não informado</span>
    @endif
</td>
                        <td>
                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $atendimento->descricao_aparelho }}">
                                {{ Str::limit($atendimento->descricao_aparelho, 30) }}
                            </span>
                        </td>
                        <td>{{ $atendimento->data_entrada->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            <span class="badge rounded-pill status-badge {{ App\Models\Atendimento::getStatusClass($atendimento->status) }}"
                                  title="Status do Serviço: {{ $atendimento->status }}">
                                <i class="bi {{ App\Models\Atendimento::getStatusIcon($atendimento->status) }} me-1"></i>
                                <span class="d-none d-md-inline">{{ $atendimento->status }}</span>
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                $statusPag = $atendimento->status_pagamento ?? 'Pendente';
                            @endphp
                            <span class="badge rounded-pill status-badge {{ App\Models\Atendimento::getPaymentStatusClass($statusPag) }}"
                                  title="Status do Pagamento: {{ $statusPag }}">
                                <i class="bi {{ App\Models\Atendimento::getPaymentStatusIcon($statusPag) }}"></i>
                                <span class="d-none d-md-inline">{{ $statusPag }}</span>
                            </span>
                        </td>
                        <td>{{ $atendimento->tecnico->name ?? 'N/A' }}</td>
                        <td class="col-acoes">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('atendimentos.show', $atendimento->id) }}" class="btn btn-info" title="Ver Detalhes"><i class="bi bi-eye-fill"></i></a>
                                @if(Gate::allows('is-internal-user'))
                                <a href="{{ route('atendimentos.edit', $atendimento->id) }}" class="btn btn-warning" title="Editar"><i class="bi bi-pencil-fill"></i></a>
                                @endif
                                @can('is-admin-or-atendente')
                                <form action="{{ route('atendimentos.destroy', $atendimento->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Excluir"
                                            onclick="return confirm('Tem certeza que deseja excluir este atendimento? Lembre-se que esta ação pode ter implicações no estoque e caixa se não tratada corretamente.')">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-4">Nenhum atendimento encontrado com os filtros aplicados.</td></tr>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endpush
@extends('layouts.app')

@section('title', 'Lista de Estoque')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .card-header-filtros {
            background-color: #f8f9fa;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .col-acoes-estoque { /* Para a coluna de ações Detalhes/Editar/Excluir */
            min-width: 110px;
            width: 110px;
            text-align: center;
        }
        .col-movimentacoes-estoque { /* Para a coluna de ações Entrada/Saída/Histórico */
            min-width: 100px; /* Ajuste conforme necessário */
            width: 100px;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-0">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <h1><i class="bi bi-boxes"></i> Lista de Estoque</h1>
            <div>
                <a href="{{ route('estoque.create') }}" class="btn btn-primary me-1">
                    <i class="bi bi-plus-circle-fill"></i> Nova Peça/Acessório
                </a>
                <a href="{{ route('estoque.historico_unificado') }}" class="btn btn-secondary">
                    <i class="bi bi-clock-history"></i> Histórico Geral
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
                <form action="{{ route('estoque.index') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4 col-lg-4">
                            <label for="busca" class="form-label form-label-sm">Buscar por Nome/Modelo/Nº Série:</label>
                            <input type="text" class="form-control form-control-sm" id="busca" name="busca" placeholder="Digite para buscar..." value="{{ request('busca') }}">
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <label for="filtro_tipo" class="form-label form-label-sm">Tipo:</label>
                            <select class="form-select form-select-sm" id="filtro_tipo" name="filtro_tipo">
                                <option value="">Todos os Tipos</option>
                                <option value="PECA_REPARO" {{ request('filtro_tipo') == 'PECA_REPARO' ? 'selected' : '' }}>Peça para Reparo</option>
                                <option value="ACESSORIO_VENDA" {{ request('filtro_tipo') == 'ACESSORIO_VENDA' ? 'selected' : '' }}>Acessório para Venda</option>
                                <option value="GERAL" {{ request('filtro_tipo') == 'GERAL' ? 'selected' : '' }}>Geral / Ambos</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <label for="filtro_marca" class="form-label form-label-sm">Marca:</label>
                            <input type="text" class="form-control form-control-sm" id="filtro_marca" name="filtro_marca" placeholder="Digite a marca..." value="{{ request('filtro_marca') }}">
                        </div>
                        <div class="col-md-12 col-lg-2 d-flex align-items-end mt-lg-0 mt-2">
                            <div class="input-group input-group-sm">
                                <button class="btn btn-primary w-50" type="submit" style="border-top-right-radius: 0; border-bottom-right-radius: 0;"><i class="bi bi-search"></i> Filtrar</button>
                                <a href="{{ route('estoque.index') }}" class="btn btn-secondary w-50" style="border-top-left-radius: 0; border-bottom-left-radius: 0;"><i class="bi bi-eraser-fill"></i> Limpar</a>
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
                        <th>Nome</th>
                        <th>Modelo Compatível</th>
                        {{-- <th>Núm. Série</th> --}}
                        <th>Tipo</th>
                        <th>Marca</th>
                        <th class="text-center">Qtd.</th>
                        <th class="text-end">Custo (R$)</th>
                        <th class="text-end">Venda (R$)</th>
                        <th class="text-center">Est. Mín.</th>
                        <th class="col-acoes-estoque text-center">Ações</th>
                        <th class="col-movimentacoes-estoque text-center">Movimentar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($estoque as $item)
                        <tr class="{{ $item->quantidade <= $item->estoque_minimo && $item->estoque_minimo > 0 ? 'table-warning' : '' }}">
                            <td>{{ $item->id }}</td>
                            <td>
                                <a href="{{ route('estoque.show', $item->id) }}" data-bs-toggle="tooltip" title="Ver detalhes de {{ $item->nome }}">{{ $item->nome }}</a>
                                @if($item->numero_serie)
                                    <small class="d-block text-muted" style="font-size: 0.8em;">S/N: {{ Str::limit($item->numero_serie, 15) }}</small>
                                @endif
                            </td>
                            <td>{{ $item->modelo_compativel ?? 'N/A' }}</td>
                            {{-- <td>{{ $item->numero_serie ?? 'N/A' }}</td> --}}
                            <td>
                                @if($item->tipo == 'PECA_REPARO') Peça p/ Reparo
                                @elseif($item->tipo == 'ACESSORIO_VENDA') Acessório
                                @elseif($item->tipo == 'GERAL') Geral
                                @else {{ $item->tipo ?? 'N/D' }}
                                @endif
                            </td>
                            <td>{{ $item->marca ?? 'N/A' }}</td>
                            <td class="text-center fw-bold">{{ $item->quantidade }}</td>
                            <td class="text-end">{{ $item->preco_custo ? number_format($item->preco_custo, 2, ',', '.') : 'N/A' }}</td>
                            <td class="text-end">{{ $item->preco_venda ? number_format($item->preco_venda, 2, ',', '.') : 'N/A' }}</td>
                            <td class="text-center">{{ $item->estoque_minimo ?? 'N/A' }}</td>
                            <td class="col-acoes-estoque text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('estoque.show', $item->id) }}" class="btn btn-info" data-bs-toggle="tooltip" title="Ver Detalhes"><i class="bi bi-eye-fill"></i></a>
                                    <a href="{{ route('estoque.edit', $item->id) }}" class="btn btn-warning" data-bs-toggle="tooltip" title="Editar Item"><i class="bi bi-pencil-fill"></i></a>
                                    @can('is-admin')
                                        <form action="{{ route('estoque.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este item? Esta ação não poderá ser desfeita.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" data-bs-toggle="tooltip" title="Excluir Item"><i class="bi bi-trash-fill"></i></button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                            <td class="col-movimentacoes-estoque text-center">
                                <div class="d-grid gap-1">
                                    <a href="{{ route('entradas-estoque.create', ['estoque_id' => $item->id]) }}" class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Registrar Entrada em Estoque">
                                        <i class="bi bi-plus-lg"></i> Entrada
                                    </a>
                                    @if ($item->quantidade > 0)
                                        <a href="{{ route('saidas-estoque.create', ['estoque_id' => $item->id]) }}" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Registrar Saída Avulsa de Estoque">
                                            <i class="bi bi-dash-lg"></i> Saída
                                        </a>
                                    @else
                                        <button class="btn btn-outline-danger btn-sm" disabled data-bs-toggle="tooltip" title="Item sem estoque para saída avulsa">- Saída</button>
                                    @endif
                                    <a href="{{ route('estoque.historico_peca', ['estoque' => $item->id]) }}" class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" title="Ver Histórico de Movimentações">
                                        <i class="bi bi-hourglass-split"></i> Hist.
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">Nenhuma peça ou acessório em estoque encontrado com os filtros aplicados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($estoque->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $estoque->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // Inicialização de Tooltips do Bootstrap
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
@endpush
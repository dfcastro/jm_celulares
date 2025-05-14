{{-- resources/views/estoque/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Lista de Estoque')

@section('content')
    <div class="container mt-0">
        <h1>Lista de Estoque</h1>

        
       
        @if(session('error'))
             <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Botões de Ação e Novo Formulário de Busca --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <a href="{{ route('estoque.create') }}" class="btn btn-primary">Nova Peça/Acessório</a>
                <a href="{{ route('estoque.historico_unificado') }}" class="btn btn-secondary">Histórico Geral</a>
            </div>
        </div>

        {{-- Formulário de Busca Aprimorado --}}
        <form action="{{ route('estoque.index') }}" method="GET" class="mb-4 p-3 border rounded bg-light">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="busca" class="form-label">Buscar por Nome/Modelo/Nº Série:</label>
                    <input type="text" class="form-control form-control-sm" id="busca" name="busca" placeholder="Digite para buscar..." value="{{ request('busca') }}">
                </div>
                <div class="col-md-3">
                    <label for="filtro_tipo" class="form-label">Filtrar por Tipo:</label>
                    <select class="form-select form-select-sm" id="filtro_tipo" name="filtro_tipo">
                        <option value="">Todos os Tipos</option>
                        <option value="PECA_REPARO" {{ request('filtro_tipo') == 'PECA_REPARO' ? 'selected' : '' }}>Peça para Reparo</option>
                        <option value="ACESSORIO_VENDA" {{ request('filtro_tipo') == 'ACESSORIO_VENDA' ? 'selected' : '' }}>Acessório para Venda</option>
                        <option value="GERAL" {{ request('filtro_tipo') == 'GERAL' ? 'selected' : '' }}>Geral / Ambos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtro_marca" class="form-label">Filtrar por Marca:</label>
                    {{-- Para Marcas, um input de texto é mais prático se houver muitas.
                         Se forem poucas marcas fixas, um select também funcionaria. --}}
                    <input type="text" class="form-control form-control-sm" id="filtro_marca" name="filtro_marca" placeholder="Digite a marca..." value="{{ request('filtro_marca') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm w-100" type="submit">Filtrar</button>
                    <a href="{{ route('estoque.index') }}" class="btn btn-secondary btn-sm w-100 mt-1">Limpar Filtros</a>
                </div>
            </div>
        </form>


        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Modelo Compatível</th>
                        <th>Núm. Série</th>
                        <th>Tipo</th>
                        <th>Marca</th>
                        <th>Qtd</th>
                        <th>Preço Custo</th>
                        <th>Preço Venda</th>
                        <th>Est. Mín.</th>
                        <th>Ações</th>
                        <th>Movimentações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($estoque as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->nome }}</td>
                            <td>{{ $item->modelo_compativel ?? 'N/A' }}</td>
                            <td>{{ $item->numero_serie ?? 'N/A' }}</td>
                            <td>
                                @if($item->tipo == 'PECA_REPARO') Peça p/ Reparo
                                @elseif($item->tipo == 'ACESSORIO_VENDA') Acessório p/ Venda
                                @elseif($item->tipo == 'GERAL') Geral
                                @else {{ $item->tipo ?? 'N/D' }}
                                @endif
                            </td>
                            <td>{{ $item->marca ?? 'N/A' }}</td>
                            <td>{{ $item->quantidade }}</td>
                            <td>{{ $item->preco_custo ? 'R$ ' . number_format($item->preco_custo, 2, ',', '.') : 'N/A' }}</td>
                            <td>{{ $item->preco_venda ? 'R$ ' . number_format($item->preco_venda, 2, ',', '.') : 'N/A' }}</td>
                            <td>{{ $item->estoque_minimo ?? 'N/A' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('estoque.show', $item->id) }}" class="btn btn-info btn-sm" title="Detalhes"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('estoque.edit', $item->id) }}" class="btn btn-warning btn-sm" title="Editar"><i class="bi bi-pencil"></i></a>
                                    @can('is-admin')
    <form action="{{ route('estoque.destroy', $item->id) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm" title="Excluir" onclick="return confirm('Tem certeza?')"><i class="bi bi-trash"></i></button>
    </form>
@endcan
                                </div>
                            </td>
                            <td>
                                <div class="d-grid gap-1">
                                    <a href="{{ route('entradas-estoque.create', ['estoque_id' => $item->id]) }}" class="btn btn-success btn-sm" title="Registrar Entrada">+ Entrada</a>
                                   @if ($item->quantidade > 0)
                                        <a href="{{ route('saidas-estoque.create', ['estoque_id' => $item->id]) }}" class="btn btn-danger btn-sm" title="Registrar Saída">- Saída</a>
                                    @else
                                        <button class="btn btn-danger btn-sm" disabled title="Peça sem estoque para saída">- Saída</button>
                                    @endif
                                    <a href="{{ route('estoque.historico_peca', ['estoque' => $item->id]) }}" class="btn btn-secondary btn-sm" title="Ver Histórico">Histórico</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center">Nenhuma peça em estoque encontrada com os filtros aplicados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($estoque->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $estoque->appends(request()->query())->links() }} {{-- Importante para manter os filtros na paginação --}}
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush
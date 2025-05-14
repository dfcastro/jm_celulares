{{-- resources/views/relatorios/estoque_baixo.blade.php --}}
@extends('layouts.app')

@section('title', 'Relatório: Estoque Abaixo do Mínimo')

@section('content')
    <div class="container mt-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Relatório: Estoque Abaixo do Mínimo</h1>
            {{-- Botão para imprimir ou exportar pode ser adicionado aqui no futuro --}}
        </div>

        @if($itensAbaixoMinimo->isEmpty())
            <div class="alert alert-success" role="alert">
                Ótima notícia! Nenhum item do estoque está abaixo do mínimo definido.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nome da Peça/Acessório</th>
                            <th>Tipo</th>
                            <th>Marca</th>
                            <th class="text-center">Qtd. Atual</th>
                            <th class="text-center">Est. Mínimo</th>
                            <th class="text-center">Diferença</th>
                            <th>Ações Sugeridas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($itensAbaixoMinimo as $item)
                            <tr class="{{ $item->quantidade < $item->estoque_minimo ? 'table-danger' : ($item->quantidade == $item->estoque_minimo ? 'table-warning' : '') }}">
                                <td>{{ $item->id }}</td>
                                <td>
                                    <a href="{{ route('estoque.show', $item->id) }}">{{ $item->nome }}</a>
                                    @if($item->modelo_compativel)
                                        <small class="d-block text-muted">{{ $item->modelo_compativel }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($item->tipo == 'PECA_REPARO') Peça p/ Reparo
                                    @elseif($item->tipo == 'ACESSORIO_VENDA') Acessório p/ Venda
                                    @elseif($item->tipo == 'GERAL') Geral
                                    @else {{ $item->tipo ?? 'N/D' }}
                                    @endif
                                </td>
                                <td>{{ $item->marca ?? 'N/A' }}</td>
                                <td class="text-center fw-bold">{{ $item->quantidade }}</td>
                                <td class="text-center">{{ $item->estoque_minimo }}</td>
                                <td class="text-center fw-bold">{{ $item->estoque_minimo - $item->quantidade }}</td>
                                <td>
                                    <a href="{{ route('entradas-estoque.create', ['estoque_id' => $item->id]) }}" class="btn btn-sm btn-success">
                                        <i class="bi bi-plus-circle"></i> Registrar Entrada
                                    </a>
                                    <a href="{{ route('estoque.edit', $item->id) }}" class="btn btn-sm btn-warning ms-1">
                                        <i class="bi bi-pencil"></i> Editar Item
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Links de Paginação --}}
            @if ($itensAbaixoMinimo->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $itensAbaixoMinimo->links() }}
                </div>
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
    {{-- Se você já incluiu Bootstrap Icons no layouts/app.blade.php, esta linha não é estritamente necessária aqui,
         mas não faz mal repeti-la ou garantir que está lá. --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush
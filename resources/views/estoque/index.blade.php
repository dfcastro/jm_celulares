{{-- resources/views/estoque/index.blade.php --}}
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1>Lista de Estoque</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <a href="{{ route('estoque.create') }}" class="btn btn-primary mb-3">Nova Peça</a>
        <a href="{{ route('estoque.historico_unificado') }}" class="btn btn-secondary mb-3">Ver Histórico de Movimentações</a>

        <div class="mb-3">
            <form action="{{ route('estoque.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="busca" class="form-label">Buscar Peça:</label>
                    <input type="text" class="form-control" id="busca" name="busca" placeholder="Nome, Modelo, Número de Série" value="{{ request('busca') }}">
                </div>
                <div class="col-md-6">
                    <button class="btn btn-primary" type="submit">Buscar</button>
                    <a href="{{ route('estoque.index') }}" class="btn btn-secondary">Limpar Busca</a>
                </div>
            </form>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Modelo Compatível</th>
                    <th>Número de Série</th>
                    <th>Quantidade</th>
                    <th>Preço de Custo</th>
                    <th>Preço de Venda</th>
                    <th>Estoque Mínimo</th>
                    <th>Ações</th>
                    <th>Movimentações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($estoque as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->nome }}</td>
                        <td>{{ $item->modelo_compativel ?? 'Não especificado' }}</td>
                        <td>{{ $item->numero_serie ?? 'Não especificado' }}</td>
                        <td>{{ $item->quantidade }}</td>
                        <td>{{ $item->preco_custo ? 'R$ ' . number_format($item->preco_custo, 2, ',', '.') : 'Não informado' }}
                        </td>
                        <td>{{ $item->preco_venda ? 'R$ ' . number_format($item->preco_venda, 2, ',', '.') : 'Não informado' }}
                        </td>
                        <td>{{ $item->estoque_minimo ?? 'Não definido' }}</td>
                        <td>
                            <a href="{{ route('estoque.show', $item->id) }}" class="btn btn-info btn-sm">Detalhes</a>
                            <a href="{{ route('estoque.edit', $item->id) }}" class="btn btn-warning btn-sm">Editar</a>
                            <form action="{{ route('estoque.destroy', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Tem certeza que deseja remover esta peça do estoque? Isso pode não ser possível se houver movimentações vinculadas.')">Remover</button>
                            </form>
                        </td>
                        <td> {{-- Conteúdo da nova coluna --}}
                            <a href="{{ route('entradas-estoque.create', ['estoque_id' => $item->id]) }}"
                                class="btn btn-success btn-sm mb-1" title="Registrar Entrada para esta Peça">
                                + Entrada
                            </a><br>
                           {{-- Botão - Saída (desabilitado se quantidade for 0) --}}
                           @if ($item->quantidade > 0)
                                <a href="{{ route('saidas-estoque.create', ['estoque_id' => $item->id]) }}"
                                    class="btn btn-danger btn-sm mb-1" title="Registrar Saída para esta Peça">
                                    - Saída
                                </a>
                            @else
                                <button class="btn btn-danger btn-sm mb-1" disabled title="Peça sem estoque para saída">
                                    - Saída
                                </button>
                            @endif
                            <br>
                            <a href="{{ route('estoque.historico_peca', ['estoque' => $item->id]) }}"
                                class="btn btn-secondary btn-sm" title="Ver Histórico de Movimentações desta Peça">
                                Histórico
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10">Nenhuma peça em estoque.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Adiciona os links de paginação --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $estoque->links() }}
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
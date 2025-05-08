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

        <a href="{{ route('estoque.create') }}" class="btn btn-primary mb-3">Nova Peça</a>

        <div class="mb-3">
            <form action="{{ route('estoque.index') }}" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control" name="modelo" placeholder="Filtrar por modelo compatível">
                    <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                </div>
            </form>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Modelo Compatível</th>
                    <th>Quantidade</th>
                    <th>Preço de Custo</th>
                    <th>Preço de Venda</th>
                    <th>Estoque Mínimo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($estoque as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->nome }}</td>
                        <td>{{ $item->modelo_compativel ?? 'Não especificado' }}</td>
                        <td>{{ $item->quantidade }}</td>
                        <td>{{ $item->preco_custo ? 'R$ ' . number_format($item->preco_custo, 2, ',', '.') : 'Não informado' }}</td>
                        <td>{{ $item->preco_venda ? 'R$ ' . number_format($item->preco_venda, 2, ',', '.') : 'Não informado' }}</td>
                        <td>{{ $item->estoque_minimo ?? 'Não definido' }}</td>
                        <td>
                            <a href="{{ route('estoque.show', $item->id) }}" class="btn btn-info btn-sm">Detalhes</a>
                            <a href="{{ route('estoque.edit', $item->id) }}" class="btn btn-warning btn-sm">Editar</a>
                            <form action="{{ route('estoque.destroy', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja remover esta peça do estoque?')">Remover</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">Nenhuma peça em estoque.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
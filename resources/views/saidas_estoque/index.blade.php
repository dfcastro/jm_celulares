<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saídas de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Saídas de Estoque</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <a href="{{ route('saidas-estoque.create') }}" class="btn btn-primary mb-3">Nova Saída</a>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Peça</th>
                    <th>Atendimento</th>
                    <th>Quantidade</th>
                    <th>Data Saída</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($saidas as $saida)
                    <tr>
                        <td>{{ $saida->id }}</td>
                        <td>{{ $saida->estoque->nome }}</td>
                        <td>
                            @if ($saida->atendimento)
                                <a href="{{ route('atendimentos.show', $saida->atendimento->id) }}">#{{ $saida->atendimento->id }}</a>
                            @else
                                Não Vinculado
                            @endif
                        </td>
                        <td>{{ $saida->quantidade }}</td>
                        <td>{{ $saida->data_saida->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('saidas-estoque.show', $saida->id) }}" class="btn btn-info btn-sm">Detalhes</a>
                            <a href="{{ route('saidas-estoque.edit', $saida->id) }}" class="btn btn-warning btn-sm">Editar</a>
                            <form action="{{ route('saidas-estoque.destroy', $saida->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta saída?')">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Nenhuma saída de estoque registrada.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $saidas->links() }}
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
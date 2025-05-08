<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Atendimentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Lista de Atendimentos</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <a href="{{ route('atendimentos.create') }}" class="btn btn-primary mb-3">Novo Atendimento</a>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Celular</th>
                    <th>Data Entrada</th>
                    <th>Status</th>
                    <th>Técnico</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($atendimentos as $atendimento)
                    <tr>
                        <td>{{ $atendimento->id }}</td>
                        <td>{{ $atendimento->cliente->nome_completo }}</td>
                        <td>{{ $atendimento->celular }}</td>
                        <td>{{ date('d/m/Y', strtotime($atendimento->data_entrada)) }}</td>
                        <td>{{ $atendimento->status }}</td>
                        <td>{{ $atendimento->tecnico->name ?? 'Não atribuído' }}</td>
                        <td>
                            <a href="{{ route('atendimentos.show', $atendimento->id) }}" class="btn btn-info btn-sm">Detalhes</a>
                            <a href="{{ route('atendimentos.edit', $atendimento->id) }}" class="btn btn-warning btn-sm">Editar</a>
                            <form action="{{ route('atendimentos.destroy', $atendimento->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este atendimento?')">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">Nenhum atendimento registrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
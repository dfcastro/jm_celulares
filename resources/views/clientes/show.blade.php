<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Detalhes do Cliente</h1>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $cliente->nome_completo }}</h5>
                <p class="card-text"><strong>CPF/CNPJ:</strong> {{ $cliente->cpf_cnpj }}</p>
                <p class="card-text"><strong>Endereço:</strong> {{ $cliente->endereco ?? 'Não informado' }}</p>
                <p class="card-text"><strong>Telefone:</strong> {{ $cliente->telefone ?? 'Não informado' }}</p>
                <p class="card-text"><strong>Email:</strong> {{ $cliente->email ?? 'Não informado' }}</p>
                <p class="card-text"><strong>Cadastrado em:</strong> {{ $cliente->created_at->format('d/m/Y H:i:s') }}</p>
                <p class="card-text"><strong>Última atualização:</strong> {{ $cliente->updated_at->format('d/m/Y H:i:s') }}</p>

                <a href="{{ route('clientes.index') }}" class="btn btn-primary">Voltar para a lista de clientes</a>
                <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-warning">Editar Cliente</a>
                <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este cliente?')">Excluir Cliente</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
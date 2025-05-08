<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Entrada de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Detalhes da Entrada de Estoque</h1>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Entrada #{{ $entradaEstoque->id }}</h5>
                <p class="card-text"><strong>Peça:</strong> {{ $entradaEstoque->estoque->nome }} ({{ $entradaEstoque->estoque->modelo_compativel ?? 'Modelo não especificado' }})</p>
                <p class="card-text"><strong>Quantidade:</strong> {{ $entradaEstoque->quantidade }}</p>
                <p class="card-text"><strong>Data de Entrada:</strong> {{ $entradaEstoque->data_entrada->format('d/m/Y H:i:s') }}</p>
                <p class="card-text"><strong>Observações:</strong> {{ $entradaEstoque->observacoes ?? 'Nenhuma observação' }}</p>
                <p class="card-text"><strong>Criado em:</strong> {{ $entradaEstoque->created_at->format('d/m/Y H:i:s') }}</p>
                <p class="card-text"><strong>Última atualização:</strong> {{ $entradaEstoque->updated_at->format('d/m/Y H:i:s') }}</p>

                <a href="{{ route('entradas-estoque.index') }}" class="btn btn-primary">Voltar para Entradas</a>
                <a href="{{ route('entradas-estoque.edit', $entradaEstoque->id) }}" class="btn btn-warning">Editar Entrada</a>
                <form action="{{ route('entradas-estoque.destroy', $entradaEstoque->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta entrada?')">Excluir Entrada</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
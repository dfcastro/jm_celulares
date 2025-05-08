<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Peça</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Detalhes da Peça</h1>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $estoque->nome }}</h5>
                <p class="card-text"><strong>Quantidade:</strong> {{ $estoque->quantidade }}</p>
                <p class="card-text"><strong>Preço de Custo:</strong> {{ $estoque->preco_custo ? 'R$ ' . number_format($estoque->preco_custo, 2, ',', '.') : 'Não informado' }}</p>
                <p class="card-text"><strong>Preço de Venda:</strong> {{ $estoque->preco_venda ? 'R$ ' . number_format($estoque->preco_venda, 2, ',', '.') : 'Não informado' }}</p>
                <p class="card-text"><strong>Estoque Mínimo:</strong> {{ $estoque->estoque_minimo ?? 'Não definido' }}</p>
                <p class="card-text"><strong>Adicionado em:</strong> {{ $estoque->created_at->format('d/m/Y H:i:s') }}</p>
                <p class="card-text"><strong>Última atualização:</strong> {{ $estoque->updated_at->format('d/m/Y H:i:s') }}</p>

                <a href="{{ route('estoque.index') }}" class="btn btn-primary">Voltar para o Estoque</a>
                <a href="{{ route('estoque.edit', $estoque->id) }}" class="btn btn-warning">Editar Peça</a>
                <form action="{{ route('estoque.destroy', $estoque->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja remover esta peça do estoque?')">Remover Peça</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
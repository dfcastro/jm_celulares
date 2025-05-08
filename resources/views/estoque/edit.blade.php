<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Peça no Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Editar Peça no Estoque</h1>

        <form action="{{ route('estoque.update', $estoque->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="nome" class="form-label">Nome da Peça</label>
                <input type="text" class="form-control" id="nome" name="nome" value="{{ $estoque->nome }}" required>
            </div>
            <div class="mb-3">
    <label for="modelo_compativel" class="form-label">Modelo Compatível (Opcional)</label>
    <input type="text" class="form-control" id="modelo_compativel" name="modelo_compativel" value="{{ $estoque->modelo_compativel }}">
</div>
            <div class="mb-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" class="form-control" id="quantidade" name="quantidade" value="{{ $estoque->quantidade }}" min="0" required>
            </div>
            <div class="mb-3">
                <label for="preco_custo" class="form-label">Preço de Custo (Opcional)</label>
                <input type="number" step="0.01" class="form-control" id="preco_custo" name="preco_custo" value="{{ $estoque->preco_custo ?? '0.00' }}" min="0">
            </div>
            <div class="mb-3">
                <label for="preco_venda" class="form-label">Preço de Venda (Opcional)</label>
                <input type="number" step="0.01" class="form-control" id="preco_venda" name="preco_venda" value="{{ $estoque->preco_venda ?? '0.00' }}" min="0">
            </div>
            <div class="mb-3">
                <label for="estoque_minimo" class="form-label">Estoque Mínimo (Opcional)</label>
                <input type="number" class="form-control" id="estoque_minimo" name="estoque_minimo" value="{{ $estoque->estoque_minimo ?? '0' }}" min="0">
            </div>
            <button type="submit" class="btn btn-primary">Atualizar Peça</button>
            <a href="{{ route('estoque.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
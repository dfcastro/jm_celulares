{{-- resources/views/estoque/create.blade.php --}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Peça no Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Nova Peça no Estoque</h1>

        {{-- BLOCO PARA EXIBIR ERROS DE VALIDAÇÃO --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {{-- FIM BLOCO PARA EXIBIR ERROS --}}

        <form action="{{ route('estoque.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="nome" class="form-label">Nome da Peça</label>
                <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome') }}" required>
                {{-- Opcional: Exibir erro específico para o campo nome --}}
                @error('nome')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="modelo_compativel" class="form-label">Modelo Compatível (Opcional)</label>
                <input type="text" class="form-control" id="modelo_compativel" name="modelo_compativel" value="{{ old('modelo_compativel') }}">
            </div>
            <div class="mb-3">
                <label for="numero_serie" class="form-label">Número de Série (Opcional)</label>
                <input type="text" class="form-control" id="numero_serie" name="numero_serie" value="{{ old('numero_serie') }}">
            </div>

            <div class="mb-3">
                <label for="preco_custo" class="form-label">Preço de Custo (Opcional)</label>
                <input type="number" step="0.01" class="form-control" id="preco_custo" name="preco_custo" value="{{ old('preco_custo', '0.00') }}" min="0">
            </div>
            <div class="mb-3">
                <label for="preco_venda" class="form-label">Preço de Venda (Opcional)</label>
                <input type="number" step="0.01" class="form-control" id="preco_venda" name="preco_venda" value="{{ old('preco_venda', '0.00') }}" min="0">
            </div>
            <div class="mb-3">
                <label for="estoque_minimo" class="form-label">Estoque Mínimo (Opcional)</label>
                <input type="number" class="form-control" id="estoque_minimo" name="estoque_minimo" value="{{ old('estoque_minimo', '0') }}" min="0">
            </div>
            <button type="submit" class="btn btn-primary">Adicionar Peça</button>
            <a href="{{ route('estoque.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
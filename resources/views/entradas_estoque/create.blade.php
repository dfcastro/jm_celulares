{{-- resources/views/entradas_estoque/create.blade.php --}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Entrada de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Nova Entrada de Estoque</h1>

        
        <form action="{{ route('entradas-estoque.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="estoque_id" class="form-label">Peça</label>
                <select class="form-control" id="estoque_id" name="estoque_id" required>
                    <option value="">Selecione a Peça</option>
                    @foreach ($estoques as $estoque)
                        {{-- Adiciona selected se o ID da peça atual for igual a $selectedEstoqueId --}}
                        <option value="{{ $estoque->id }}" {{ isset($selectedEstoqueId) && $estoque->id == $selectedEstoqueId ? 'selected' : '' }}>
                            {{ $estoque->nome }} ({{ $estoque->modelo_compativel ?? 'Modelo não especificado' }})
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Selecione a peça que está entrando no estoque.</small>
            </div>
            <div class="mb-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" class="form-control" id="quantidade" name="quantidade" value="{{ old('quantidade', 1) }}" min="1" required>
            </div>
            {{-- NOVO/RE-ADICIONADO: Campo para seleção da data --}}
            <div class="mb-3">
                <label for="data_entrada" class="form-label">Data de Entrada</label>
                <input type="date" class="form-control" id="data_entrada" name="data_entrada" value="{{ old('data_entrada', date('Y-m-d')) }}" required>
            </div>
            <div class="mb-3">
                <label for="observacoes" class="form-label">Observações (Opcional)</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3">{{ old('observacoes') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Registrar Entrada</button>
            <a href="{{ route('entradas-estoque.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
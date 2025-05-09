<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Saída de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Nova Saída de Estoque</h1>
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
        <form action="{{ route('saidas-estoque.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="estoque_id" class="form-label">Peça</label>
                <select class="form-control" id="estoque_id" name="estoque_id" required>
                    <option value="">Selecione a Peça</option>
                    @foreach ($estoques as $estoque)
                         {{-- Adiciona selected se o ID da peça atual for igual a $selectedEstoqueId --}}
                        <option value="{{ $estoque->id }}" {{ isset($selectedEstoqueId) && $estoque->id == $selectedEstoqueId ? 'selected' : '' }}>
                            {{ $estoque->nome }} ({{ $estoque->modelo_compativel ?? 'Modelo não especificado' }}) - Quantidade: {{ $estoque->quantidade }}
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Selecione a peça que está saindo do estoque.</small>
            </div>
            <div class="mb-3">
                <label for="atendimento_id" class="form-label">Atendimento (Opcional)</label>
                <select class="form-control" id="atendimento_id" name="atendimento_id">
                    <option value="">Selecione o Atendimento (Opcional)</option>
                    @foreach ($atendimentos as $atendimento)
                        <option value="{{ $atendimento->id }}">#{{ $atendimento->id }} - {{ $atendimento->cliente->nome_completo }} ({{ $atendimento->celular }})</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Selecione o atendimento ao qual esta saída está vinculada, se aplicável.</small>
            </div>
            <div class="mb-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" class="form-control" id="quantidade" name="quantidade" value="{{ old('quantidade', 1) }}" min="1" required>
                <small class="form-text text-muted">Informe a quantidade de peças que estão saindo.</small>
                 {{-- Opcional: Exibir erro específico para o campo quantidade --}}
                 @error('quantidade')
                     <div class="text-danger">{{ $message }}</div>
                 @enderror
            </div>
            <div class="mb-3">
                <label for="data_saida" class="form-label">Data de Saída</label>
                <input type="date" class="form-control" id="data_saida" name="data_saida" value="{{ date('Y-m-d') }}" required>
                <small class="form-text text-muted">Selecione a data em que a peça saiu do estoque.</small>
            </div>
            <div class="mb-3">
                <label for="observacoes" class="form-label">Observações (Opcional)</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                <small class="form-text text-muted">Adicione qualquer observação relevante sobre esta saída.</small>
            </div>
            <button type="submit" class="btn btn-primary">Registrar Saída</button>
            <a href="{{ route('saidas-estoque.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
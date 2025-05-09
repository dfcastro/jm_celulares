<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Saída de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Detalhes da Saída de Estoque</h1>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Saída #{{ $saidaEstoque->id }}</h5>
                <p class="card-text"><strong>Peça:</strong> {{ $saidaEstoque->estoque->nome }} ({{ $saidaEstoque->estoque->modelo_compativel ?? 'Modelo não especificado' }})</p>
                <p class="card-text">
                    <strong>Atendimento:</strong>
                    @if ($saidaEstoque->atendimento)
                        <a href="{{ route('atendimentos.show', $saidaEstoque->atendimento->id) }}">#{{ $saidaEstoque->atendimento->id }} - {{ $saidaEstoque->atendimento->cliente->nome_completo }}</a>
                    @else
                        Não Vinculado
                    @endif
                </p>
                <p class="card-text"><strong>Quantidade:</strong> {{ $saidaEstoque->quantidade }}</p>
                <p class="card-text"><strong>Data de Saída:</strong> {{ $saidaEstoque->data_saida->format('d/m/Y H:i:s') }}</p>
                <p class="card-text"><strong>Observações:</strong> {{ $saidaEstoque->observacoes ?? 'Nenhuma observação' }}</p>
                <p class="card-text"><strong>Criado em:</strong> {{ $saidaEstoque->created_at->format('d/m/Y H:i:s') }}</p>
                <p class="card-text"><strong>Última atualização:</strong> {{ $saidaEstoque->updated_at->format('d/m/Y H:i:s') }}</p>

                <a href="{{ route('saidas-estoque.index') }}" class="btn btn-primary">Voltar para Saídas</a>
              
                <form action="{{ route('saidas-estoque.destroy', $saidaEstoque->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta saída?')">Excluir Saída</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
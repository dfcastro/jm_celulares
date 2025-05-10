{{-- resources/views/estoque/historico_unificado.blade.php --}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico Unificado de Movimentações</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Histórico Unificado de Movimentações de Estoque</h1>

        <a href="{{ route('estoque.index') }}" class="btn btn-secondary mb-3">Voltar para Lista de Peças</a>

        @if($movimentacoes->count() > 0)
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Peça</th>
                        <th>Quantidade</th>
                        <th>Data/Hora</th>
                        <th>Observações</th>
                        <th>Relacionado</th> {{-- Nova coluna para Atendimento --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach($movimentacoes as $movimento)
                        <tr>
                            <td>
                                @if($movimento['tipo'] == 'Entrada')
                                    <span class="badge bg-success">Entrada</span>
                                @else
                                    <span class="badge bg-danger">Saída</span>
                                @endif
                            </td>
                            <td>{{ $movimento['peca'] }}</td>
                            <td>{{ $movimento['quantidade'] }}</td>
                            <td>{{ $movimento['data']->format('d/m/Y H:i:s') }}</td> {{-- Formata a data --}}
                            <td>{{ $movimento['observacoes'] ?? '-' }}</td>
                            <td>{{ $movimento['relacionado'] ?? '-' }}</td> {{-- Exibe o relacionado ou '-' --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{-- Note: Esta listagem não tem paginação. Para muitos itens, considere paginar. --}}
        @else
            <div class="alert alert-info">Nenhuma movimentação de estoque registrada.</div>
        @endif

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
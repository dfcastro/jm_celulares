<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado da Consulta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Resultado da Consulta</h1>

        @if ($atendimento)
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Atendimento #{{ $atendimento->id }}</h5>
                    <p class="card-text"><strong>Cliente:</strong> {{ $atendimento->cliente->nome_completo }}</p>
                    <p class="card-text"><strong>Celular:</strong> {{ $atendimento->celular }}</p>
                    <p class="card-text"><strong>Problema Relatado:</strong> {{ $atendimento->problema_relatado }}</p>
                    <p class="card-text"><strong>Data de Entrada:</strong> {{ $atendimento->data_entrada->format('d/m/Y') }}</p>
                    <p class="card-text"><strong>Status:</strong> {{ $atendimento->status }}</p>
                    <p class="card-text"><strong>Observações:</strong> {{ $atendimento->observacoes ?? 'Nenhuma observação' }}</p>
                </div>
            </div>
            <a href="{{ route('consulta.index') }}" class="btn btn-secondary mt-3">Nova Consulta</a>
        @else
            <div class="alert alert-warning">
                Nenhum atendimento encontrado com o código informado.
            </div>
            <a href="{{ route('consulta.index') }}" class="btn btn-primary mt-3">Tentar Novamente</a>
        @endif
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
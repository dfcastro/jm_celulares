<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Atendimento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Detalhes do Atendimento</h1>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Atendimento #{{ $atendimento->id }}</h5>
                <p class="card-text"><strong>Cliente:</strong> <a href="{{ route('clientes.show', $atendimento->cliente->id) }}">{{ $atendimento->cliente->nome_completo }}</a> ({{ $atendimento->cliente->cpf_cnpj }})</p>
                <p class="card-text"><strong>Celular:</strong> {{ $atendimento->celular }}</p>
                <p class="card-text"><strong>Problema Relatado:</strong> {{ $atendimento->problema_relatado }}</p>
                <p class="card-text"><strong>Data de Entrada:</strong> {{ $atendimento->data_entrada->format('d/m/Y') }}</p>
                <p class="card-text"><strong>Status:</strong> {{ $atendimento->status }}</p>
                <p class="card-text"><strong>Técnico Responsável:</strong> {{ $atendimento->tecnico->name ?? 'Não atribuído' }}</p>
                <p class="card-text"><strong>Data de Conclusão:</strong> {{ $atendimento->data_conclusao ? $atendimento->data_conclusao->format('d/m/Y') : 'Não concluído' }}</p>
                <p class="card-text"><strong>Código de Consulta:</strong> {{ $atendimento->codigo_consulta }}</p>
                <p class="card-text"><strong>Observações:</strong> {{ $atendimento->observacoes ?? 'Nenhuma observação' }}</p>
                <p class="card-text"><strong>Criado em:</strong> {{ $atendimento->created_at->format('d/m/Y H:i:s') }}</p>
                <p class="card-text"><strong>Última atualização:</strong> {{ $atendimento->updated_at->format('d/m/Y H:i:s') }}</p>

                <a href="{{ route('atendimentos.index') }}" class="btn btn-primary">Voltar para a lista de atendimentos</a>
                <a href="{{ route('atendimentos.edit', $atendimento->id) }}" class="btn btn-warning">Editar Atendimento</a>
                <form action="{{ route('atendimentos.destroy', $atendimento->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este atendimento?')">Excluir Atendimento</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
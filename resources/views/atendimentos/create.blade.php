<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Atendimento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Novo Atendimento</h1>

        <form action="{{ route('atendimentos.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="cliente_id" class="form-label">Cliente</label>
                <select class="form-control" id="cliente_id" name="cliente_id" required>
                    <option value="">Selecione o Cliente</option>
                    @foreach ($clientes as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->nome_completo }} ({{ $cliente->cpf_cnpj }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="celular" class="form-label">Celular</label>
                <input type="text" class="form-control" id="celular" name="celular" required>
            </div>
            <div class="mb-3">
                <label for="problema_relatado" class="form-label">Problema Relatado</label>
                <textarea class="form-control" id="problema_relatado" name="problema_relatado" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="data_entrada" class="form-label">Data de Entrada</label>
                <input type="date" class="form-control" id="data_entrada" name="data_entrada" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="mb-3">
                <label for="tecnico_id" class="form-label">Técnico Responsável (Opcional)</label>
                <select class="form-control" id="tecnico_id" name="tecnico_id">
                    <option value="">Não atribuído</option>
                    @foreach ($tecnicos as $tecnico)
                        <option value="{{ $tecnico->id }}">{{ $tecnico->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Salvar Atendimento</button>
            <a href="{{ route('atendimentos.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Atendimento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Editar Atendimento</h1>

        <form action="{{ route('atendimentos.update', $atendimento->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="cliente_id" class="form-label">Cliente</label>
                <select class="form-control" id="cliente_id" name="cliente_id" required>
                    <option value="">Selecione o Cliente</option>
                    @foreach ($clientes as $cliente)
                        <option value="{{ $cliente->id }}" {{ $atendimento->cliente_id == $cliente->id ? 'selected' : '' }}>
                            {{ $cliente->nome_completo }} ({{ $cliente->cpf_cnpj }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="celular" class="form-label">Celular</label>
                <input type="text" class="form-control" id="celular" name="celular" value="{{ $atendimento->celular }}" required>
            </div>
            <div class="mb-3">
                <label for="problema_relatado" class="form-label">Problema Relatado</label>
                <textarea class="form-control" id="problema_relatado" name="problema_relatado" rows="3" required>{{ $atendimento->problema_relatado }}</textarea>
            </div>
            <div class="mb-3">
                <label for="data_entrada" class="form-label">Data de Entrada</label>
                <input type="date" class="form-control" id="data_entrada" name="data_entrada" value="{{ $atendimento->data_entrada->format('Y-m-d') }}" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Em diagnóstico" {{ $atendimento->status == 'Em diagnóstico' ? 'selected' : '' }}>Em diagnóstico</option>
                    <option value="Aguardando peça" {{ $atendimento->status == 'Aguardando peça' ? 'selected' : '' }}>Aguardando peça</option>
                    <option value="Em manutenção" {{ $atendimento->status == 'Em manutenção' ? 'selected' : '' }}>Em manutenção</option>
                    <option value="Pronto para entrega" {{ $atendimento->status == 'Pronto para entrega' ? 'selected' : '' }}>Pronto para entrega</option>
                    <option value="Entregue" {{ $atendimento->status == 'Entregue' ? 'selected' : '' }}>Entregue</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="tecnico_id" class="form-label">Técnico Responsável (Opcional)</label>
                <select class="form-control" id="tecnico_id" name="tecnico_id">
                    <option value="">Não atribuído</option>
                    @foreach ($tecnicos as $tecnico)
                        <option value="{{ $tecnico->id }}" {{ $atendimento->tecnico_id == $tecnico->id ? 'selected' : '' }}>{{ $tecnico->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="data_conclusao" class="form-label">Data de Conclusão (Opcional)</label>
                <input type="date" class="form-control" id="data_conclusao" name="data_conclusao" value="{{ $atendimento->data_conclusao ? $atendimento->data_conclusao->format('Y-m-d') : '' }}">
            </div>
            <div class="mb-3">
                <label for="observacoes" class="form-label">Observações (Opcional)</label>
                <textarea class="form-control" id="observacoes" name="observacoes" rows="3">{{ $atendimento->observacoes }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Atualizar Atendimento</button>
            <a href="{{ route('atendimentos.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
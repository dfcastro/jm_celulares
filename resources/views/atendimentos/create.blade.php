<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Atendimento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
    <style>
        .ui-autocomplete {
            z-index: 1000;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1>Novo Atendimento</h1>

        <form action="{{ route('atendimentos.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="cliente_nome" class="form-label">Cliente</label>
                <input type="text" class="form-control" id="cliente_nome" name="cliente_nome"
                    placeholder="Digite o nome ou CPF/CNPJ do cliente" required>
                <input type="hidden" id="cliente_id" name="cliente_id">
            </div>
            <div class="mb-3">
                <label for="celular" class="form-label">Celular</label>
                <input type="text" class="form-control" id="celular" name="celular" required>
            </div>
            <div class="mb-3">
                <label for="problema_relatado" class="form-label">Problema Relatado</label>
                <textarea class="form-control" id="problema_relatado" name="problema_relatado" rows="3"
                    required></textarea>
            </div>
            <div class="mb-3">
                <label for="data_entrada" class="form-label">Data de Entrada</label>
                <input type="date" class="form-control" id="data_entrada" name="data_entrada" value="{{ now()->format('Y-m-d') }}" required>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function () {
            $("#cliente_nome").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "{{ route('clientes.autocomplete') }}",
                        type: 'GET',
                        dataType: "json",
                        data: {
                            search: request.term
                        },
                        success: function (data) {
                            response($.map(data, function (item) {
                                return {
                                    label: item.nome_completo + ' (' + item.cpf_cnpj + ')',
                                    value: item.nome_completo,
                                    id: item.id,
                                    telefone: item.telefone // Adicionando o telefone ao item
                                };
                            }));
                        }
                    });
                },
                select: function (event, ui) {
                    $('#cliente_nome').val(ui.item.label);
                    $('#cliente_id').val(ui.item.id);
                    $('#celular').val(ui.item.telefone); // Preenchendo o campo celular
                    return false;
                }
            });
        });
    </script>
</body>

</html>
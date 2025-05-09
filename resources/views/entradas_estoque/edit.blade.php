<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Entrada de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Editar Entrada de Estoque</h1>

        <form action="{{ route('entradas-estoque.update', ['entradas_estoque' => $entradaEstoque->id]) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="estoque_id" class="form-label">Peça</label>
                <select class="form-control" id="estoque_id" name="estoque_id" required>
                    <option value="">Selecione a Peça</option>
                    @foreach ($estoques as $estoque)
                        <option value="{{ $estoque->id }}" {{ $entradaEstoque->estoque_id == $estoque->id ? 'selected' : '' }}>
                            {{ $estoque->nome }} ({{ $estoque->modelo_compativel ?? 'Modelo não especificado' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" class="form-control" id="quantidade" name="quantidade" value="{{ $entradaEstoque->quantidade }}" min="1" required>
            </div>
            <div class="mb-3">
                <label for="data_entrada" class="
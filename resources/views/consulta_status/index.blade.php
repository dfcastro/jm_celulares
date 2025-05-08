<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Consultar Status do Reparo</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('consulta.status') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="codigo_consulta" class="form-label">Código de Consulta</label>
                <input type="text" class="form-control" id="codigo_consulta" name="codigo_consulta" maxlength="10" required>
                <small class="form-text text-muted">Informe o código de consulta recebido.</small>
            </div>
            <button type="submit" class="btn btn-primary">Consultar</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
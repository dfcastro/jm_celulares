@extends('layouts.app')

@section('title', 'Nova Entrada de Estoque')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    {{-- jQuery UI CSS já deve estar no layouts.app --}}
    <style>
        #info_peca_selecionada_entrada {
            font-size: 0.875em;
            background-color: #f8f9fa;
            padding: 0.5rem 0.75rem;
            border-radius: 0.25rem;
            margin-top: 0.5rem;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-0">
        <h1><i class="bi bi-box-arrow-in-down"></i> Nova Entrada de Estoque</h1>

        {{-- Bloco para exibir erros de validação e mensagens de sessão --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <strong><i class="bi bi-exclamation-triangle-fill"></i> Ops! Verifique os erros abaixo:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        {{-- Adicione outros feedbacks de sessão aqui se necessário --}}

        <form action="{{ route('entradas-estoque.store') }}" method="POST" class="mt-3">
            @csrf
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-pencil-square"></i> Detalhes da Entrada</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="estoque_nome" class="form-label">Peça/Acessório <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('estoque_id') is-invalid @enderror" id="estoque_nome"
                            placeholder="Comece a digitar o nome, modelo ou ID da peça..." value="{{ old('estoque_nome') }}" required>
                        <input type="hidden" id="estoque_id" name="estoque_id" value="{{ old('estoque_id', $selectedEstoqueId ?? '') }}">
                        <small class="form-text text-muted">Selecione o item que está entrando no estoque.</small>
                        @error('estoque_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div id="info_peca_selecionada_entrada" style="display: none;">
                            {-- Informações da peça selecionada serão exibidas aqui --}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantidade" class="form-label">Quantidade a Adicionar <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('quantidade') is-invalid @enderror" id="quantidade" name="quantidade"
                                value="{{ old('quantidade', 1) }}" min="1" required>
                            @error('quantidade')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="data_entrada" class="form-label">Data de Entrada <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('data_entrada') is-invalid @enderror" id="data_entrada" name="data_entrada"
                                value="{{ old('data_entrada', date('Y-m-d')) }}" required>
                            @error('data_entrada')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações (Opcional)</label>
                        <textarea class="form-control @error('observacoes') is-invalid @enderror" id="observacoes" name="observacoes"
                            rows="3" placeholder="Ex: Nota fiscal nº XXX, Compra do fornecedor Y, Devolução de cliente Z...">{{ old('observacoes') }}</textarea>
                        @error('observacoes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <a href="{{ route('entradas-estoque.index') }}" class="btn btn-secondary me-2"><i class="bi bi-x-circle"></i> Cancelar</a>
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle-fill"></i> Registrar Entrada</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    {{-- jQuery e jQuery UI já devem estar no layouts.app.blade.php --}}
    <script>
        $(document).ready(function () {
            function exibirInfoPeca(item) {
                var infoDiv = $('#info_peca_selecionada_entrada');
                if (item && item.id) {
                    var infoHtml = '<strong>ID:</strong> ' + item.id +
                                   ' | <strong>Tipo:</strong> ' + (item.tipo || 'N/D') +
                                   ' | <strong>Qtd Atual:</strong> ' + item.quantidade_disponivel;
                    infoDiv.html(infoHtml).show();
                } else {
                    infoDiv.hide().html('');
                }
            }

            $("#estoque_nome").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "{{ route('estoque.autocomplete') }}", // Rota de autocomplete do EstoqueController
                        type: 'GET',
                        dataType: "json",
                        data: {
                            search: request.term
                            // tipos_filtro: ['PECA_REPARO', 'ACESSORIO_VENDA', 'GERAL'] // Pode manter ou remover se quiser todos
                        },
                        success: function (data) {
                            response(data); // O controller já retorna o formato label/value/id e outros
                        }
                    });
                },
                minLength: 1, // Começar a buscar após 1 caractere
                select: function (event, ui) {
                    $('#estoque_nome').val(ui.item.value); // value: "Nome (Modelo)"
                    $('#estoque_id').val(ui.item.id);
                    exibirInfoPeca(ui.item); // Exibe informações da peça selecionada
                    $('#quantidade').focus(); // Move o foco para o campo quantidade
                    return false;
                },
                change: function (event, ui) {
                    if (!ui.item) { // Se o usuário digitou algo que não corresponde a um item da lista
                        $('#estoque_id').val('');
                        // Não limpar #estoque_nome aqui, pois o usuário pode estar no meio da digitação
                        // ou o valor pode ser útil para a validação no backend (embora estoque_id seja o principal)
                        exibirInfoPeca(null); // Limpa as informações da peça
                    }
                }
            });

            // Lógica para pré-popular o autocomplete e as informações da peça se $selectedEstoqueId estiver presente
            const selectedEstoqueId = $('#estoque_id').val();
            if (selectedEstoqueId) {
                $.ajax({
                    url: "{{ route('estoque.autocomplete') }}", // Reutiliza a rota de autocomplete
                    type: 'GET',
                    dataType: "json",
                    data: { search: selectedEstoqueId }, // Busca pelo ID
                    success: function (data) {
                        if (data.length > 0) {
                            // O autocomplete pode retornar múltiplos resultados se 'search' não for exato
                            // Idealmente, o backend deveria tratar a busca por ID de forma especial
                            // para retornar apenas 1 item. Assumindo que ele retorna o item correto.
                            const item = data.find(i => i.id == selectedEstoqueId);
                            if (item) {
                                $('#estoque_nome').val(item.value); // Preenche o campo de texto com o nome da peça
                                exibirInfoPeca(item);
                            } else {
                                $('#estoque_id').val(''); // Limpa se o ID não for encontrado
                            }
                        } else {
                             $('#estoque_id').val(''); // Limpa se o ID não for encontrado
                        }
                    },
                    error: function() {
                        console.error("Erro ao buscar dados da peça pré-selecionada.");
                        $('#estoque_id').val('');
                        exibirInfoPeca(null);
                    }
                });
            }
        });
    </script>
@endpush
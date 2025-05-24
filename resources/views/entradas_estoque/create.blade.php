{{-- resources/views/entradas_estoque/create.blade.php --}}
@extends('layouts.app') {{-- Estende o layout base --}}

@section('title', 'Nova Entrada de Estoque') {{-- Título da página --}}

@section('content') {{-- Conteúdo principal da página --}}
    <div class="container mt-0"> {{-- Removi mt-5 pois o layout já tem padding --}}

        <h1>Nova Entrada de Estoque</h1>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('entradas-estoque.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="estoque_nome" class="form-label">Peça</label>
                {{-- Campo de texto para autocomplete --}}
                <input type="text" class="form-control" id="estoque_nome"
                    placeholder="Comece a digitar o nome ou modelo da peça..." value="{{ old('estoque_nome') }}" required>
                {{-- Campo hidden para guardar o ID da peça selecionada --}}
                <input type="hidden" id="estoque_id" name="estoque_id"
                    value="{{ old('estoque_id', $selectedEstoqueId ?? '') }}">
                <small class="form-text text-muted">Comece a digitar o nome ou modelo da peça e selecione na
                    lista.</small>
                @error('estoque_id') {{-- Exibir erro se o ID da peça não for válido --}}
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" class="form-control" id="quantidade" name="quantidade"
                    value="{{ old('quantidade', 1) }}" min="1" required>
            </div>
            {{-- NOVO/RE-ADICIONADO: Campo para seleção da data --}}
            <div class="mb-3">
                <label for="data_entrada" class="form-label">Data de Entrada</label>
                <input type="date" class="form-control" id="data_entrada" name="data_entrada"
                    value="{{ old('data_entrada', date('Y-m-d')) }}" required>
            </div>
            <div class="mb-3">
                <label for="observacoes" class="form-label">Observações (Opcional)</label>
                <textarea class="form-control" id="observacoes" name="observacoes"
                    rows="3">{{ old('observacoes') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Registrar Entrada</button>
            <a href="{{ route('entradas-estoque.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
    {{-- Inclua jQuery (se ainda não estiver incluído) e jQuery UI JS --}}


@endsection
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>


    <script>
        $(document).ready(function () {
            $("#estoque_nome").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "{{ route('estoque.autocomplete') }}",
                        type: 'GET',
                        dataType: "json",
                        data: {
                            search: request.term
                        },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: function (event, ui) {
                    $('#estoque_nome').val(ui.item.value);
                    $('#estoque_id').val(ui.item.id);
                    return false;
                },
                change: function (event, ui) {
                    if (!ui.item) {
                        $('#estoque_id').val('');
                        $('#estoque_nome').val('');
                    }
                }
            });

            // Lógica para pré-popular o autocomplete se $selectedEstoqueId estiver presente
            // (que é carregado no campo hidden estoque_id no Blade)
            const selectedEstoqueId = $('#estoque_id').val();
            if (selectedEstoqueId) {
                $.ajax({
                    url: "{{ route('estoque.autocomplete') }}",
                    type: 'GET',
                    dataType: "json",
                    // Para buscar o nome da peça pelo ID
                    // No seu EstoqueController@autocomplete, você precisa adaptar para
                    // que ele também busque por ID se 'search' for um número.
                    data: { search: selectedEstoqueId },
                    success: function (data) {
                        if (data.length > 0) {
                            // O autocomplete retorna uma lista. Procuramos o item com o ID correspondente.
                            const item = data.find(i => i.id == selectedEstoqueId);
                            if (item) {
                                $('#estoque_nome').val(item.value); // Preenche o campo de texto com o nome da peça
                                // O campo hidden #estoque_id já deve estar preenchido pelo Blade
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
{{-- Para esta página, não precisamos de @push('styles') ou @push('scripts') geralmente --}}
{{-- Mas se você fosse adicionar ícones do Bootstrap, por exemplo: --}}
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush
{{-- resources/views/saidas_estoque/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nova Saída de Estoque')

@section('content')
    <div class="container mt-0">
        <h1>Nova Saída de Estoque</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('saidas-estoque.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="estoque_nome" class="form-label">Peça</label>
                <input type="text" class="form-control" id="estoque_nome"
                    placeholder="Comece a digitar o nome ou modelo da peça..." value="{{ old('estoque_nome') }}" required>
                <input type="hidden" id="estoque_id" name="estoque_id"
                    value="{{ old('estoque_id', $selectedEstoqueId ?? '') }}">
                <small class="form-text text-muted">Selecione a peça que está saindo do estoque.</small>
                @error('estoque_id')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="atendimento_info_create" class="form-label">Atendimento (Opcional)</label>
                <input type="text" class="form-control" id="atendimento_info_create"
                    placeholder="ID, Cliente, Celular do Atendimento"
                    value="{{ old('atendimento_info_create', $atendimentoSelecionado->cliente->nome_completo ?? '') }}">
                {{-- Para exibir nome se pré-selecionado --}}
                <input type="hidden" id="atendimento_id_create" name="atendimento_id"
                    value="{{ old('atendimento_id', $selectedAtendimentoId ?? '') }}">
                <small class="form-text text-muted">Digite para buscar e vincular a um atendimento, se aplicável.</small>
                @error('atendimento_id') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" class="form-control" id="quantidade" name="quantidade"
                    value="{{ old('quantidade', 1) }}" min="1" required>
                @error('quantidade')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="data_saida" class="form-label">Data de Saída</label>
                <input type="date" class="form-control" id="data_saida" name="data_saida"
                    value="{{ old('data_saida', date('Y-m-d')) }}" required>
            </div>

            <div class="mb-3">
                <label for="observacoes" class="form-label">Observações (Opcional)</label>
                <textarea class="form-control" id="observacoes" name="observacoes"
                    rows="3">{{ old('observacoes') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Registrar Saída</button>
            <a href="{{ route('saidas-estoque.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
@endsection

@push('scripts')
    {{-- jQuery e jQuery UI já são carregados pelo layouts/app.blade.php --}}
    <script>
          $(document).ready(function () {
        // Autocomplete para Peça (já existente e filtrado)
        $("#estoque_nome").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "{{ route('estoque.autocomplete') }}",
                    type: 'GET',
                    dataType: "json",
                    data: {
                        search: request.term,
                        tipos_filtro: ['PECA_REPARO', 'GERAL']
                    },
                    success: function (data) { response(data); },
                    error: function() { console.error("Erro no autocomplete de peças."); response([]); }
                });
            },
            minLength: 1,
            select: function (event, ui) {
                $('#estoque_nome').val(ui.item.value);
                $('#estoque_id').val(ui.item.id);
                if (ui.item.quantidade_disponivel > 0) {
                    $('#quantidade').attr('max', ui.item.quantidade_disponivel).val(1);
                } else {
                    $('#quantidade').attr('max', 0).val(0);
                }
                return false;
            },
            change: function(event, ui) {
                if (!ui.item) {
                    $('#estoque_id').val('');
                    $('#quantidade').removeAttr('max').val(1);
                }
            }
        });

        // NOVO: Autocomplete para Atendimento
        $("#atendimento_info_create").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('atendimentos.autocomplete') }}", // Rota criada anteriormente
                    dataType: "json",
                    data: {
                        search_autocomplete: request.term // Parâmetro esperado pelo AtendimentoController@autocomplete
                    },
                    success: function(dataFromServer) {
                        response($.map(dataFromServer, function(item) {
                            let label = `#${item.id}`;
                            if(item.cliente) {
                                label += ` - ${item.cliente.nome_completo}`;
                            } else {
                                label += ` - Cliente não associado`;
                            }
                            if(item.celular) label += ` (${item.celular})`;
                            // Adicionar o status ao label pode ser útil aqui
                            if(item.status) label += ` [${item.status}]`;


                            return {
                                label: label, // O que é mostrado na lista de sugestões
                                value: `#${item.id} - ${item.cliente ? item.cliente.nome_completo : 'Sem Cliente'}`, // O que vai para o input após selecionar
                                id: item.id
                            };
                        }));
                    },
                    error: function(){
                        console.log("Erro ao buscar atendimentos para autocomplete.");
                        response([]);
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                $("#atendimento_info_create").val(ui.item.value); // Preenche o campo de texto visível
                $("#atendimento_id_create").val(ui.item.id);    // Preenche o campo hidden com o ID
                return false;
            },
            change: function(event, ui) {
                if (!ui.item) {
                    $("#atendimento_id_create").val('');
                    // Se quiser limpar o texto se não selecionar:
                    // if ($("#atendimento_info_create").val() !== '') {
                    //     $("#atendimento_info_create").val('');
                    // }
                }
            }
        });

         // Limpar ID se o campo de info do atendimento for limpo manualmente
        $("#atendimento_info_create").on('input', function() {
            if ($(this).val() === '') {
                $("#atendimento_id_create").val('');
            }
        });


        // Pré-popular campos se IDs foram passados via URL (ex: vindo de outra página)
        const selectedEstoqueId = $('#estoque_id').val();
        const selectedAtendimentoId = $('#atendimento_id_create').val(); // Usar o ID do campo hidden de atendimento

        if (selectedEstoqueId && !$('#estoque_nome').val()) {
            $.ajax({
                url: "{{ route('estoque.autocomplete') }}", type: 'GET', dataType: "json",
                data: { search: selectedEstoqueId, tipos_filtro: ['PECA_REPARO', 'GERAL'] },
                success: function (data) {
                    if (data.length > 0) {
                        const item = data.find(i => i.id == selectedEstoqueId);
                        if (item) {
                            $('#estoque_nome').val(item.value);
                            if (item.quantidade_disponivel > 0) {
                                 $('#quantidade').attr('max', item.quantidade_disponivel);
                            } else {
                                 $('#quantidade').attr('max', 0);
                            }
                        }
                    }
                }
            });
        }

        if (selectedAtendimentoId && !$('#atendimento_info_create').val()) {
            $.ajax({
                url: "{{ route('atendimentos.autocomplete') }}", type: 'GET', dataType: "json",
                data: { search_autocomplete: selectedAtendimentoId }, // Busca pelo ID
                success: function (dataFromServer) {
                    if (dataFromServer.length > 0) {
                         // Como o controller retorna um array, pegamos o primeiro (deve ser único se buscar por ID)
                        const item = dataFromServer.find(i => i.id == selectedAtendimentoId);
                        if (item) {
                            let valueToDisplay = `#${item.id}`;
                            if(item.cliente) valueToDisplay += ` - ${item.cliente.nome_completo}`;
                            else valueToDisplay += ` - Cliente não associado`;

                            $('#atendimento_info_create').val(valueToDisplay);
                            // O $('#atendimento_id_create').val() já deve estar correto pelo 'old()' ou vindo do request.
                        }
                    }
                }
            });
        }
    });
    </script>
@endpush
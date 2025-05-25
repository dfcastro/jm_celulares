{{-- resources/views/vendas_acessorios/create.blade.php --}}

@extends('layouts.app')

@section('title', 'Registrar Nova Venda de Acessório')

@section('content')
    {{-- Seu HTML do formulário aqui... --}}
    {{-- ... (todo o conteúdo do formulário que já estava funcionando) ... --}}
    <div class="container mt-0">
        <h1>Registrar Nova Venda de Acessório</h1>
        @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
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
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('vendas-acessorios.store') }}" method="POST">
            @csrf
            <div class="card mb-4">
                <div class="card-header">Dados da Venda</div>
                <div class="card-body">
                    {{-- Campos: data_venda, cliente_nome, cliente_id, forma_pagamento, valor_total, observacoes --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="data_venda" class="form-label">Data da Venda</label>
                            <input type="date" class="form-control" id="data_venda" name="data_venda"
                                value="{{ old('data_venda', date('Y-m-d')) }}" required>
                            @error('data_venda')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cliente_nome" class="form-label">Cliente (Opcional)</label>
                            <input type="text" class="form-control" id="cliente_nome" name="cliente_nome"
                                placeholder="Digite o nome ou CPF/CNPJ do cliente" value="{{ old('cliente_nome') }}">
                            <input type="hidden" id="cliente_id" name="cliente_id" value="{{ old('cliente_id') }}">
                            @error('cliente_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="forma_pagamento" class="form-label">Forma de Pagamento (Opcional)</label>
                            <select class="form-select" id="forma_pagamento" name="forma_pagamento">
                                <option value="">Selecione a Forma de Pagamento</option>
                                @foreach($formasPagamento as $forma)
                                    <option value="{{ $forma }}" {{ old('forma_pagamento') == $forma ? 'selected' : '' }}>
                                        {{ $forma }}
                                    </option>
                                @endforeach
                            </select>
                            @error('forma_pagamento')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="valor_total" class="form-label">Valor Total (Será calculado)</label>
                            <input type="text" class="form-control" id="valor_total" name="valor_total"
                                value="{{ old('valor_total', '0.00') }}" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações (Opcional)</label>
                        <textarea class="form-control" id="observacoes" name="observacoes"
                            rows="3">{{ old('observacoes') }}</textarea>
                        @error('observacoes')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">Itens Vendidos</div>
                <div class="card-body">
                    <div class="row mb-2 d-none d-md-flex">
                        <div class="col-md-4"><label class="form-label">Peça</label></div>
                        <div class="col-md-2"><label class="form-label">Qtd</label></div>
                        <div class="col-md-2"><label class="form-label">Preço Unit</label></div>
                        <div class="col-md-2"><label class="form-label">Desconto (R$)</label></div>
                        <div class="col-md-2"></div>
                    </div>
                    <div id="itens-venda-container">
                        @if(old('itens'))
                            @foreach(old('itens') as $index => $itemData)
                                @include('vendas_acessorios._item_venda_template', ['index' => $index, 'itemData' => $itemData])
                            @endforeach
                        @endif
                    </div>
                    <button type="button" class="btn btn-secondary mt-3" id="adicionar-item">Adicionar Item</button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary me-2">Registrar Venda</button>
            <a href="{{ route('vendas-acessorios.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // --- Configuração do Autocomplete de Cliente ---
            $("#cliente_nome").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "{{ route('clientes.autocomplete') }}", // Certifique-se que esta rota existe e funciona
                        type: 'GET',
                        dataType: "json",
                        data: { search: request.term }, // O controller espera 'search', está correto.
                        success: function (data) {
                            // O controller já retorna os dados formatados.
                            // A resposta 'data' já é um array de objetos como:
                            // [{ label: "Nome Cliente (12345)", value: "Nome Cliente", id: 1, telefone: "...", email: "..."}]
                            // Basta passar 'data' diretamente para a função 'response'.
                            response(data);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            // Adicionar um tratamento de erro para facilitar a depuração
                            console.error("Erro na requisição AJAX para autocomplete de clientes:");
                            console.error("Status: " + textStatus);
                            console.error("Erro: " + errorThrown);
                            if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                                console.error("Mensagem do servidor: " + jqXHR.responseJSON.message);
                            }
                            response([]); // Importante para não quebrar o autocomplete em caso de erro
                        }
                    });
                },
                select: function (event, ui) {
                    // ui.item conterá o objeto selecionado, vindo diretamente do controller:
                    // { label: "Nome (CPF)", value: "Nome", id: X, telefone: "Y", email: "Z"}
                    $('#cliente_nome').val(ui.item.label); // Preenche o campo de nome com o label completo.
                    // Se quiser apenas o nome, use ui.item.value
                    $('#cliente_id').val(ui.item.id);     // Guarda o ID do cliente.

                    // Opcional: Você pode preencher outros campos se eles existirem no seu formulário
                    // Ex:
                    // if ($('#cliente_telefone').length) { // Verifica se o campo existe
                    //    $('#cliente_telefone').val(ui.item.telefone);
                    // }
                    // if ($('#cliente_email').length) { // Verifica se o campo existe
                    //    $('#cliente_email').val(ui.item.email);
                    // }

                    return false; // Previne que o valor (ui.item.value) seja inserido no input automaticamente
                    // se você preferir usar o label. Se quiser o value, pode omitir esta linha
                    // ou retornar true, e ajustar o val() acima para ui.item.value.
                },
                change: function (event, ui) {
                    // Se o campo for limpo ou um valor que não corresponde a um item for digitado
                    if (!ui.item) {
                        $('#cliente_id').val(''); // Limpa o ID do cliente
                        // Opcional: Limpar outros campos relacionados ao cliente
                        // Ex:
                        // if ($('#cliente_telefone').length) {
                        //    $('#cliente_telefone').val('');
                        // }
                        // if ($('#cliente_email').length) {
                        //    $('#cliente_email').val('');
                        // }
                    }
                }
            });

            // --- Configuração da Adição Dinâmica de Itens de Venda ---
            let itemIndex = $('#itens-venda-container .item-venda').length;

            function adicionarItem(itemData = null) {
                // Correção: itemData aqui é um objeto, não uma string JSON para ser decodificada.
                // O template _item_venda_template já espera um array/objeto $itemData.
                $.get("{{ route('vendas-acessorios.item_template') }}", { index: itemIndex, itemData: itemData }, function (html) {
                    $('#itens-venda-container').append(html);
                    inicializarAutocompletePeca($('#itens-venda-container .item-venda:last-child .item-estoque-autocomplete'));
                    itemIndex++;
                    calcularTotalVenda();
                });
            }

            function inicializarAutocompletePeca(element) {
                element.autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            url: "{{ route('estoque.autocomplete') }}", // Esta rota agora usa EstoqueController@autocomplete
                            type: 'GET',
                            dataType: "json",
                            data: {
                                search: request.term,
                                tipos_filtro: ['ACESSORIO_VENDA', 'GERAL'] // <<<<<<<<<<<<<< ADICIONADO FILTRO
                            },
                            success: function (data) {
                                response(data);
                            },
                            error: function () { // Adicionado para depuração de erros AJAX
                                console.error("Erro ao buscar itens de estoque para autocomplete.");
                                response([]); // Retorna array vazio em caso de erro
                            }
                        });
                    },
                    minLength: 1, // Pode ajustar para 1 ou 2
                    select: function (event, ui) {
                        $(this).val(ui.item.value);
                        $(this).closest('.item-venda').find('.item-estoque-id').val(ui.item.id);
                        $(this).closest('.item-venda').find('.item-preco-unitario').val(ui.item.preco_venda);
                        // Verifica se a quantidade disponível é maior que 0 antes de setar para 1
                        if (ui.item.quantidade_disponivel > 0) {
                            $(this).closest('.item-venda').find('.item-quantidade').val(1).attr('max', ui.item.quantidade_disponivel);
                        } else {
                            $(this).closest('.item-venda').find('.item-quantidade').val(0).attr('max', 0); // Se não há estoque, não pode vender
                        }
                        // Adiciona o estoque disponível como um data attribute para referência futura se necessário
                        $(this).closest('.item-venda').find('.item-quantidade').data('estoque-disponivel', ui.item.quantidade_disponivel);

                        calcularTotalVenda();
                        return false;
                    },
                    change: function (event, ui) {
                        if (!ui.item) {
                            $(this).closest('.item-venda').find('.item-estoque-id').val('');
                            $(this).closest('.item-venda').find('.item-preco-unitario').val('0.00');
                            $(this).closest('.item-venda').find('.item-quantidade').val('1').removeData('estoque-disponivel').removeAttr('max');
                            calcularTotalVenda(); // Recalcula se o item for limpo
                        }
                    }
                });
            }

            $('#itens-venda-container').on('click', '.remover-item', function () {
                $(this).closest('.item-venda').remove();
                calcularTotalVenda();
            });

            function calcularTotalVenda() {
                let total = 0;
                $('#itens-venda-container .item-venda').each(function () {
                    const quantidade = parseFloat($(this).find('.item-quantidade').val()) || 0;
                    const precoUnitario = parseFloat($(this).find('.item-preco-unitario').val()) || 0;
                    const descontoItem = parseFloat($(this).find('.item-desconto').val()) || 0;
                    const subtotalItem = Math.max(0, (quantidade * precoUnitario) - descontoItem);
                    total += subtotalItem;
                });
                $('#valor_total').val(total.toFixed(2));
            }

            $('#itens-venda-container').on('input', '.item-quantidade, .item-preco-unitario, .item-desconto', calcularTotalVenda);

            $('#adicionar-item').on('click', function () {
                adicionarItem();
            });

            if (itemIndex > 0) {
                $('#itens-venda-container .item-venda').each(function () {
                    inicializarAutocompletePeca($(this).find('.item-estoque-autocomplete'));
                });
                calcularTotalVenda();
            } else {
                adicionarItem(); // Adiciona a primeira linha se não houver itens 'old'
            }
        });
    </script>
@endpush
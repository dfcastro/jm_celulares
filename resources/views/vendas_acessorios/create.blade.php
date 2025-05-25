@extends('layouts.app')

@section('title', 'Registrar Nova Venda de Acessório')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    {{-- jQuery UI CSS já deve estar no layouts.app --}}
    <style>
        .readonly-look {
            background-color: #e9ecef;
            opacity: 1;
        }

        #info_cliente_selecionado_venda {
            /* Para feedback do autocomplete de cliente */
            font-size: 0.875em;
            background-color: #f8f9fa;
            padding: 0.5rem 0.75rem;
            border-radius: 0.25rem;
            margin-top: 0.5rem;
            border: 1px solid #e9ecef;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-0">
        <h1><i class="bi bi-cart-plus-fill"></i> Registrar Nova Venda de Acessório</h1>
        <div id="feedbackGlobalVendaAcessorio" class="mb-3"></div>
        {{-- Mensagens de Feedback e Erros --}}
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

        <form action="{{ route('vendas-acessorios.store') }}" method="POST" id="formCriarVendaAcessorio" class="mt-3">
            @csrf

            {{-- Card de Dados da Venda --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-file-earmark-text-fill"></i> Dados da Venda</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="data_venda" class="form-label">Data da Venda <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('data_venda') is-invalid @enderror"
                                id="data_venda" name="data_venda" value="{{ old('data_venda', date('Y-m-d')) }}" required>
                            @error('data_venda') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="cliente_nome_venda" class="form-label">Cliente (Opcional)</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('cliente_id') is-invalid @enderror"
                                    id="cliente_nome_venda" name="cliente_nome_display_venda"
                                    placeholder="Digite nome ou CPF/CNPJ do cliente"
                                    value="{{ old('cliente_nome_display_venda') }}">
                                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal"
                                    data-bs-target="#modalNovoCliente" title="Cadastrar Novo Cliente">
                                    <i class="bi bi-person-plus-fill"></i> Novo
                                </button>
                            </div>
                            <input type="hidden" id="cliente_id_venda" name="cliente_id" value="{{ old('cliente_id') }}">
                            @error('cliente_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            <div id="info_cliente_selecionado_venda" style="display: none;"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                            <select class="form-select @error('forma_pagamento') is-invalid @enderror" id="forma_pagamento"
                                name="forma_pagamento">
                                <option value="">Selecione...</option>
                                @foreach($formasPagamento as $forma)
                                    <option value="{{ $forma }}" {{ old('forma_pagamento') == $forma ? 'selected' : '' }}>
                                        {{ $forma }}
                                    </option>
                                @endforeach
                            </select>
                            @error('forma_pagamento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="valor_total_venda" class="form-label fw-bold">VALOR TOTAL (R$)</label>
                            <input type="text" class="form-control fw-bold readonly-look" id="valor_total_venda"
                                name="valor_total_display" {{-- Alterado para _display --}}
                                value="{{ old('valor_total_display', '0,00') }}" readonly
                                style="font-size: 1.2rem; color: #198754;">
                            <input type="hidden" name="valor_total" id="valor_total_hidden_venda"
                                value="{{ old('valor_total', '0.00') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações (Opcional)</label>
                        <textarea class="form-control @error('observacoes') is-invalid @enderror" id="observacoes"
                            name="observacoes" rows="2"
                            placeholder="Detalhes adicionais sobre a venda...">{{ old('observacoes') }}</textarea>
                        @error('observacoes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Card de Itens Vendidos --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-cart-check"></i> Itens Vendidos</h5>
                </div>
                <div class="card-body">
                    {{-- Cabeçalho da tabela de itens (visível em telas maiores) --}}
                    <div class="row mb-2 d-none d-md-flex fw-bold text-muted small">
                        <div class="col-md-4">Peça/Acessório</div>
                        <div class="col-md-2 col-sm-4">Qtd</div>
                        <div class="col-md-2 col-sm-4">Preço Unit. (R$)</div>
                        <div class="col-md-2 col-sm-4">Desconto (R$)</div>
                        <div class="col-md-1">Ação</div>
                    </div>
                    <div id="itens-venda-container">
                        {{-- Itens serão adicionados aqui --}}
                        @if(old('itens'))
                            @foreach(old('itens') as $index => $itemData)
                                @include('vendas_acessorios._item_venda_template', ['index' => $index, 'itemData' => $itemData])
                            @endforeach
                        @endif
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mt-3" id="adicionar-item-venda">
                        <i class="bi bi-plus-circle"></i> Adicionar Item
                    </button>
                    @error('itens') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <a href="{{ route('vendas-acessorios.index') }}" class="btn btn-secondary me-2"><i
                        class="bi bi-x-circle"></i> Cancelar</a>
                <button type="submit" class="btn btn-primary" id="btnRegistrarVendaAcessorio"><i
                        class="bi bi-save-fill"></i> Registrar Venda</button>
            </div>
        </form>
    </div>

    {{-- Template para os itens de venda (já existe, referenciado) --}}
    <div id="venda-item-template-source" style="display: none;">
        @include('vendas_acessorios._item_venda_template', ['index' => '__INDEX__'])
    </div>

    @include('clientes.partials.modal_create') {{-- Inclui o modal de criar cliente --}}
    @include('vendas_acessorios.partials._modal_aviso_caixa_fechado') {{-- NOVO INCLUDE --}}

@endsection

@push('scripts')
    {{-- Seu JavaScript existente com adaptações --}}
    <script>
        $(document).ready(function () {
            // --- AUTOCOMPLETE DE CLIENTE (para Venda) ---
            function exibirInfoClienteVenda(cliente) {
                var infoDiv = $('#info_cliente_selecionado_venda');
                if (cliente && cliente.id) {
                    var infoHtml = '<strong>ID:</strong> ' + cliente.id +
                        (cliente.telefone ? ' | <strong>Tel:</strong> ' + cliente.telefone : '') +
                        (cliente.email ? ' | <strong>Email:</strong> ' + cliente.email : '');
                    infoDiv.html(infoHtml).show();
                } else {
                    infoDiv.hide().html('');
                }
            }

            let clienteSelecionadoPeloAutocompleteVenda = ($('#cliente_id_venda').val() !== '');
            if (clienteSelecionadoPeloAutocompleteVenda && $('#cliente_nome_venda').val()) {
                // Tenta buscar dados adicionais se o nome já está preenchido
                const initialId = $('#cliente_id_venda').val();
                $.ajax({
                    url: "{{ route('clientes.autocomplete') }}",
                    dataType: "json", data: { search: initialId },
                    success: function (data) {
                        const clienteEncontrado = data.find(c => c.id == initialId);
                        if (clienteEncontrado) exibirInfoClienteVenda(clienteEncontrado);
                    }
                });
            }

            $("#cliente_nome_venda").autocomplete({
                source: "{{ route('clientes.autocomplete') }}",
                minLength: 2,
                select: function (event, ui) {
                    if (ui.item && ui.item.id) {
                        $('#cliente_nome_venda').val(ui.item.value);
                        $('#cliente_id_venda').val(ui.item.id);
                        exibirInfoClienteVenda(ui.item);
                        clienteSelecionadoPeloAutocompleteVenda = true;
                    }
                    return false;
                },
                change: function (event, ui) {
                    if (!clienteSelecionadoPeloAutocompleteVenda && !ui.item && $('#cliente_nome_venda').val() !== '') {
                        $('#cliente_id_venda').val('');
                        exibirInfoClienteVenda(null);
                    }
                }
            });
            $("#cliente_nome_venda").on('input', function () {
                if ($(this).val() === '') {
                    $('#cliente_id_venda').val('');
                    exibirInfoClienteVenda(null);
                    clienteSelecionadoPeloAutocompleteVenda = false;
                } else if (!clienteSelecionadoPeloAutocompleteVenda) {
                    $('#cliente_id_venda').val('');
                }
            });

            // Lógica do Modal de Novo Cliente
            $('#formNovoCliente').on('submit', function (e) { /* ... (Lógica do modal como antes, mas preenchendo #cliente_nome_venda e #cliente_id_venda) ... */
                e.preventDefault();
                var formData = $(this).serialize();
                var btnSalvar = $('#btnSalvarNovoCliente');
                var originalText = btnSalvar.html();
                btnSalvar.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Salvando...');
                $('#formNovoCliente .invalid-feedback').remove();
                $('#formNovoCliente .is-invalid').removeClass('is-invalid');
                $.ajax({
                    url: "{{ route('clientes.store') }}", type: 'POST', data: formData, dataType: 'json',
                    success: function (response) {
                        if (response.success && response.cliente) {
                            var cliente = response.cliente;
                            $('#cliente_nome_venda').val(cliente.nome_completo); // Campo de display na venda
                            $('#cliente_id_venda').val(cliente.id);       // Campo hidden na venda
                            exibirInfoClienteVenda(cliente);
                            clienteSelecionadoPeloAutocompleteVenda = true;
                            $('#modalNovoCliente').modal('hide');
                            $('#formNovoCliente')[0].reset();
                        } else {
                            if (response.errors) {
                                $.each(response.errors, function (key, value) {
                                    $('#modal_' + key).addClass('is-invalid').closest('.mb-3').append('<div class="invalid-feedback d-block">' + value[0] + '</div>');
                                });
                            }
                        }
                    },
                    error: function (xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            $.each(xhr.responseJSON.errors, function (key, value) {
                                $('#modal_' + key).addClass('is-invalid').closest('.mb-3').append('<div class="invalid-feedback d-block">' + value[0] + '</div>');
                            });
                        } else { alert('Erro desconhecido ao salvar cliente.'); }
                    },
                    complete: function () { btnSalvar.prop('disabled', false).html(originalText); }
                });
            });
            // Máscaras e ViaCEP do modal (como antes)
            if ($.fn.mask) {
                // ... (máscaras para CPF/CNPJ, Telefone, CEP do modal) ...
                var CpfCnpjMaskBehaviorModal = function (val) { return val.replace(/\D/g, '').length <= 11 ? '000.000.000-009' : '00.000.000/0000-00'; },
                    cpfCnpjOptionsModal = { onKeyPress: function (val, e, field, options) { field.mask(CpfCnpjMaskBehaviorModal.apply({}, arguments), options); } };
                $('#modal_cpf_cnpj').mask(CpfCnpjMaskBehaviorModal, cpfCnpjOptionsModal);
                var SPMaskBehaviorModal = function (val) { return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009'; },
                    spOptionsModal = { onKeyPress: function (val, e, field, options) { field.mask(SPMaskBehaviorModal.apply({}, arguments), options); } };
                $('#modal_telefone').mask(SPMaskBehaviorModal, spOptionsModal);
                $('#modal_cep').mask('00000-000');
            }
            $('#modal_cep').on('blur', function () { /* ... lógica ViaCEP do modal ... */ });
            $('#modalNovoCliente').on('hidden.bs.modal', function () { /* ... reset do modal ... */ });


            // --- ADIÇÃO DINÂMICA DE ITENS DE VENDA ---
            let itemVendaIndex = $('#itens-venda-container .item-venda').length;
            const templateVendaHTML = $('#venda-item-template-source').html(); // Pega o HTML do template

            function adicionarItemVenda(itemData = null) {
                let novoItemHtml = templateVendaHTML.replace(/__INDEX__/g, itemVendaIndex);
                $('#itens-venda-container').append(novoItemHtml);
                let linhaAtual = $('#itens-venda-container .item-venda[data-index="' + itemVendaIndex + '"]');

                if (itemData) { // Para repopular com dados 'old'
                    linhaAtual.find('input[name$="[nome_peca_search]"]').val(itemData.nome_peca_search || '');
                    linhaAtual.find('.item-estoque-id').val(itemData.item_estoque_id || '');
                    linhaAtual.find('.item-quantidade').val(itemData.quantidade || 1);
                    let precoUnitarioOld = String(itemData.preco_unitario || '0.00').replace(',', '.');
                    linhaAtual.find('.item-preco-unitario').val(parseFloat(precoUnitarioOld).toFixed(2));
                    let descontoOld = String(itemData.desconto || '0.00').replace(',', '.');
                    linhaAtual.find('.item-desconto').val(parseFloat(descontoOld).toFixed(2));
                }

                initializeAutocompletePecaVenda(linhaAtual.find('.item-estoque-autocomplete'));
                itemVendaIndex++;
                calcularTotalVenda();
                // Se for o primeiro item adicionado (ou se não houver itens old), foca nele
                if ($('#itens-venda-container .item-venda').length === 1 && !itemData) {
                    linhaAtual.find('.item-estoque-autocomplete').focus();
                }
            }

            function initializeAutocompletePecaVenda(element) {
                let pecaSelecionadaNaLinha = false;
                element.autocomplete({
                    source: function (request, response) {
                        pecaSelecionadaNaLinha = false;
                        $.ajax({
                            url: "{{ route('estoque.autocomplete') }}",
                            type: 'GET', dataType: "json",
                            data: {
                                search: request.term,
                                tipos_filtro: ['ACESSORIO_VENDA', 'GERAL'] // Filtro de tipos
                            },
                            success: function (data) { response(data); },
                            error: function () { response([]); }
                        });
                    },
                    minLength: 1,
                    select: function (event, ui) {
                        var row = $(this).closest('.item-venda');
                        $(this).val(ui.item.value); // Nome da peça (ex: "Capa iPhone (Transparente)")
                        row.find('.item-estoque-id').val(ui.item.id);
                        row.find('.item-preco-unitario').val(ui.item.preco_venda).trigger('input');
                        row.find('.item-quantidade').attr('max', ui.item.quantidade_disponivel).val(1).trigger('input'); // Define max e atualiza qtd para 1

                        var infoDiv = row.find('div[id^="info_item_venda_"]');
                        var tipoPecaFormatado = ui.item.tipo_formatado || ui.item.tipo_original || 'N/D';
                        var infoPecaHtml = '<i class="bi bi-info-circle"></i> Qtd. Disp.: ' + ui.item.quantidade_disponivel + ' | Tipo: ' + tipoPecaFormatado;
                        infoDiv.html(infoPecaHtml).show();
                        pecaSelecionadaNaLinha = true;
                        // Focar no próximo campo lógico, talvez quantidade ou desconto
                        row.find('.item-quantidade').focus();
                        return false;
                    },
                    change: function (event, ui) {
                        var row = $(this).closest('.item-venda');
                        var infoDiv = row.find('div[id^="info_item_venda_"]');
                        if (!pecaSelecionadaNaLinha && !ui.item) {
                            row.find('.item-estoque-id').val('');
                            if ($(this).val() === '') infoDiv.hide().html('');
                        } else if (ui.item) {
                            var tipoPecaFormatado = ui.item.tipo_formatado || ui.item.tipo_original || 'N/D';
                            var infoPecaHtml = '<i class="bi bi-info-circle"></i> Qtd. Disp.: ' + ui.item.quantidade_disponivel + ' | Tipo: ' + tipoPecaFormatado;
                            infoDiv.html(infoPecaHtml).show();
                        }
                        row.find('.item-quantidade').trigger('input'); // Revalida qtd ao mudar a peça
                        calcularTotalVenda();
                    }
                });
                // Limpar info se o campo de busca for limpo manualmente
                element.on('input', function () {
                    var row = $(this).closest('.item-venda');
                    if ($(this).val() === '') {
                        row.find('.item-estoque-id').val('');
                        row.find('div[id^="info_item_venda_"]').hide().html('');
                        row.find('.item-quantidade').removeAttr('max').val(1); // Reseta max
                        calcularTotalVenda();
                    }
                });
            }

            $('#itens-venda-container').on('input', '.item-quantidade', function () {
                var row = $(this).closest('.item-venda');
                var quantidadeDesejada = parseInt($(this).val()) || 0;
                var maxDisponivel = parseInt($(this).attr('max')) || 0;
                var avisoDiv = row.find('small[id^="aviso_qtd_venda_"]');
                var btnRegistrarVenda = $('#btnRegistrarVendaAcessorio');

                if (maxDisponivel > 0 && quantidadeDesejada > maxDisponivel) {
                    avisoDiv.text('Qtd. excede estoque (' + maxDisponivel + ')').show();
                    // btnRegistrarVenda.prop('disabled', true); // Desabilitar botão principal pode ser muito agressivo aqui
                    // A validação do backend é mais crucial.
                } else if (quantidadeDesejada <= 0 && maxDisponivel > 0) {
                    avisoDiv.text('Qtd. deve ser > 0').show();
                }
                else {
                    avisoDiv.hide().text('');
                    // btnRegistrarVenda.prop('disabled', false);
                }
                calcularTotalVenda(); // Sempre recalcular
            });


            $('#itens-venda-container').on('click', '.remover-item', function () {
                $(this).closest('.item-venda').remove();
                calcularTotalVenda();
                if ($('#itens-venda-container .item-venda').length === 0) {
                    itemVendaIndex = 0; // Reseta o índice se todos os itens forem removidos
                }
            });
            // Lógica para verificar caixa antes de submeter a venda
            const formCriarVendaAcessorio = $('#formCriarVendaAcessorio');
            const btnRegistrarVendaAcessorio = $('#btnRegistrarVendaAcessorio');
            const modalAvisoCaixaFechadoVenda = new bootstrap.Modal(document.getElementById('modalAvisoCaixaFechadoVenda'));
            // Para feedback não modal (opcional)
            // const feedbackGlobalVendaAcessorio = $('#feedbackGlobalVendaAcessorio');

            formCriarVendaAcessorio.on('submit', function (event) {
                event.preventDefault(); // Previne o envio padrão PARA FAZER A VERIFICAÇÃO AJAX PRIMEIRO

                // Desabilita o botão para evitar múltiplos cliques enquanto verifica
                btnRegistrarVendaAcessorio.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verificando caixa...');

                $.ajax({
                    url: "{{ route('caixa.verificarStatusAjax') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.caixa_aberto) {
                            // Caixa está aberto, permite o envio do formulário
                            // Para submeter o formulário de verdade agora:
                            // Remove o listener temporariamente para não entrar em loop e submete
                            formCriarVendaAcessorio.off('submit').submit();
                        } else {
                            // Caixa está fechado, exibe o modal
                            modalAvisoCaixaFechadoVenda.show();
                            btnRegistrarVendaAcessorio.prop('disabled', false).html('<i class="bi bi-save-fill"></i> Registrar Venda'); // Reabilita o botão
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error("Erro ao verificar status do caixa:", textStatus, errorThrown);
                        // Em caso de erro na verificação, pode optar por permitir a venda com aviso,
                        // ou bloquear e mostrar um erro genérico.
                        // Por segurança, vamos bloquear e informar o erro.
                        alert('Erro ao verificar o status do caixa. Não foi possível registrar a venda. Tente novamente.');
                        // Se tiver a div de feedback:
                        // feedbackGlobalVendaAcessorio.html('<div class="alert alert-danger">Erro ao verificar status do caixa. Tente novamente.</div>');
                        btnRegistrarVendaAcessorio.prop('disabled', false).html('<i class="bi bi-save-fill"></i> Registrar Venda');
                    }
                });
            });
            function calcularTotalVenda() {
                let totalGeral = 0;
                $('#itens-venda-container .item-venda').each(function () {
                    let quantidadeStr = $(this).find('.item-quantidade').val();
                    let precoUnitarioStr = $(this).find('.item-preco-unitario').val();
                    let descontoItemStr = $(this).find('.item-desconto').val();

                    let quantidade = parseFloat(String(quantidadeStr).replace(',', '.')) || 0;
                    let precoUnitario = parseFloat(String(precoUnitarioStr).replace(',', '.')) || 0;
                    let descontoItem = parseFloat(String(descontoItemStr).replace(',', '.')) || 0;

                    let subtotalItemBruto = quantidade * precoUnitario;
                    let subtotalItemLiquido = Math.max(0, subtotalItemBruto - descontoItem); // Garante que não seja negativo
                    $(this).find('.item-subtotal-venda-display').text(subtotalItemLiquido.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    totalGeral += subtotalItemLiquido;
                });
                $('#valor_total_venda').val(totalGeral.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#valor_total_hidden_venda').val(totalGeral.toFixed(2)); // Para o backend
            }

            $('#itens-venda-container').on('input', '.item-preco-unitario, .item-desconto', calcularTotalVenda);

            $('#adicionar-item-venda').on('click', function () {
                adicionarItemVenda();
            });

            // Inicialização para itens 'old' (se houver)
            if (itemVendaIndex > 0) {
                $('#itens-venda-container .item-venda').each(function () {
                    initializeAutocompletePecaVenda($(this).find('.item-estoque-autocomplete'));
                    // Disparar input na quantidade para validar o estoque maximo
                    $(this).find('.item-quantidade').trigger('input');
                });
                calcularTotalVenda();
            } else {
                adicionarItemVenda(); // Adiciona a primeira linha se não houver itens 'old'
            }

            // Se o cliente_id já vier preenchido (ex: da URL), busca e exibe as infos
            const initialClienteVendaId = $('#cliente_id_venda').val();
            if (initialClienteVendaId && !$('#cliente_nome_venda').val()) {
                $.ajax({
                    url: "{{ route('clientes.autocomplete') }}",
                    dataType: "json", data: { search: initialClienteVendaId },
                    success: function (data) {
                        const clienteEncontrado = data.find(c => c.id == initialClienteVendaId);
                        if (clienteEncontrado) {
                            $('#cliente_nome_venda').val(clienteEncontrado.value);
                            exibirInfoClienteVenda(clienteEncontrado);
                            clienteSelecionadoPeloAutocompleteVenda = true;
                        }
                    }
                });
            }
        });
    </script>
@endpush
@extends('layouts.app')

@section('title', 'Novo Orçamento')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    {{-- jQuery UI CSS já deve estar no layouts.app --}}
    <style>
        .item-orcamento-row {
            /* Estilos já definidos no template _item_orcamento_template */
        }
        .readonly-look {
            background-color: #e9ecef;
            opacity: 1;
        }
        /* Para feedback do autocomplete de cliente */
        #info_cliente_selecionado_orcamento {
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
    <h1><i class="bi bi-file-earmark-plus-fill"></i> Criar Novo Orçamento</h1>

    {{-- Mensagens de Feedback e Erros de Validação --}}
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

    <form action="{{ route('orcamentos.store') }}" method="POST" id="formCriarOrcamento" class="mt-3">
        @csrf

        {{-- Card de Dados do Cliente e Aparelho --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="my-1"><i class="bi bi-person-badge"></i> Dados do Cliente e Aparelho</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="cliente_search_orcamento" class="form-label">Cliente Cadastrado</label>
                        <div class="input-group">
                            <input type="text"
                                class="form-control @error('cliente_id') is-invalid @enderror"
                                id="cliente_search_orcamento"
                                name="cliente_search_orcamento_display"
                                placeholder="Digite nome ou CPF/CNPJ para buscar..."
                                value="{{ old('cliente_search_orcamento_display') }}">
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalNovoCliente" title="Cadastrar Novo Cliente">
                                <i class="bi bi-person-plus-fill"></i> Novo
                            </button>
                        </div>
                        <input type="hidden" id="cliente_id_orcamento" name="cliente_id" value="{{ old('cliente_id', $request->input('cliente_id')) }}">
                        @error('cliente_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                         <div id="info_cliente_selecionado_orcamento" style="display: none;"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-7 mb-3">
                        <label for="nome_cliente_avulso_orcamento" class="form-label">Ou Nome do Cliente (Avulso) <span id="span_nome_avulso_req" class="text-danger" style="{{ old('cliente_id') ? 'display:none;' : '' }}">*</span></label>
                        <input type="text" class="form-control @error('nome_cliente_avulso') is-invalid @enderror"
                            id="nome_cliente_avulso_orcamento" name="nome_cliente_avulso"
                            value="{{ old('nome_cliente_avulso') }}" placeholder="Nome completo do cliente não cadastrado"
                            {{ old('cliente_id') ? 'readonly' : '' }}>
                        @error('nome_cliente_avulso') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="telefone_cliente_avulso_orcamento" class="form-label">Telefone (Avulso)</label>
                        <input type="text" class="form-control"
                            id="telefone_cliente_avulso_orcamento" name="telefone_cliente_avulso"
                            value="{{ old('telefone_cliente_avulso') }}" {{ old('cliente_id') ? 'readonly' : '' }}>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="email_cliente_avulso_orcamento" class="form-label">Email (Avulso)</label>
                    <input type="email" class="form-control"
                        id="email_cliente_avulso_orcamento" name="email_cliente_avulso"
                        value="{{ old('email_cliente_avulso') }}" {{ old('cliente_id') ? 'readonly' : '' }}>
                </div>
                <hr>
                <div class="mb-3">
                    <label for="descricao_aparelho" class="form-label">Descrição do Aparelho <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('descricao_aparelho') is-invalid @enderror" id="descricao_aparelho" name="descricao_aparelho" value="{{ old('descricao_aparelho') }}" placeholder="Ex: iPhone 12 Pro Max Preto, Samsung S23 com película" required>
                     @error('descricao_aparelho') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="problema_relatado_cliente" class="form-label">Problema Relatado <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('problema_relatado_cliente') is-invalid @enderror" id="problema_relatado_cliente" name="problema_relatado_cliente" rows="3" placeholder="Ex: Tela não liga, não carrega, molhou" required>{{ old('problema_relatado_cliente') }}</textarea>
                    @error('problema_relatado_cliente') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Card de Itens do Orçamento --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="my-1"><i class="bi bi-tools"></i> Itens do Orçamento (Peças e Serviços)</h5>
            </div>
            <div class="card-body">
                <div id="itens-orcamento-container">
                    @if(old('itens'))
                        @foreach(old('itens') as $index => $itemData)
                            @include('orcamentos._item_orcamento_template', ['index' => $index, 'itemData' => $itemData])
                        @endforeach
                    @endif
                </div>
                <button type="button" class="btn btn-secondary btn-sm mt-3" id="adicionar-item-orcamento">
                    <i class="bi bi-plus-circle"></i> Adicionar Item
                </button>
                 @error('itens') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Card de Valores e Condições --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="my-1"><i class="bi bi-calculator-fill"></i> Valores e Condições</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="valor_total_servicos_display" class="form-label">Total Serviços (R$)</label>
                        <input type="text" class="form-control readonly-look" id="valor_total_servicos_display" readonly value="0.00">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="valor_total_pecas_display" class="form-label">Total Peças (R$)</label>
                        <input type="text" class="form-control readonly-look" id="valor_total_pecas_display" readonly value="0.00">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="sub_total_display" class="form-label">Subtotal (R$)</label>
                        <input type="text" class="form-control readonly-look" id="sub_total_display" readonly value="0.00">
                    </div>
                </div>
                <div class="row align-items-end">
                    <div class="col-md-4 mb-3">
                        <label for="desconto_tipo" class="form-label">Tipo de Desconto</label>
                        <select class="form-select" id="desconto_tipo" name="desconto_tipo">
                            <option value="">Sem Desconto</option>
                            @foreach ($tiposDesconto as $key => $value)
                                <option value="{{ $key }}" {{ old('desconto_tipo') == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="desconto_valor" class="form-label">Valor do Desconto</label>
                        <input type="number" step="0.01" class="form-control @error('desconto_valor') is-invalid @enderror" id="desconto_valor" name="desconto_valor" value="{{ old('desconto_valor', '0.00') }}" min="0">
                        <small id="desconto_info" class="form-text text-muted"></small>
                        @error('desconto_valor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="valor_final_display" class="form-label fw-bold">VALOR FINAL (R$)</label>
                        <input type="text" class="form-control fw-bold readonly-look" id="valor_final_display" readonly value="0.00" style="font-size: 1.2rem; color: #198754;">
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="data_emissao" class="form-label">Data de Emissão <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('data_emissao') is-invalid @enderror" id="data_emissao" name="data_emissao" value="{{ old('data_emissao', now()->format('Y-m-d')) }}" required>
                        @error('data_emissao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="validade_dias" class="form-label">Validade (dias)</label>
                        <input type="number" class="form-control" id="validade_dias" name="validade_dias" value="{{ old('validade_dias', 7) }}" min="0">
                        <small class="form-text text-muted">0 ou vazio para sem validade.</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tempo_estimado_servico" class="form-label">Tempo Estimado de Serviço</label>
                        <input type="text" class="form-control" id="tempo_estimado_servico" name="tempo_estimado_servico" value="{{ old('tempo_estimado_servico') }}" placeholder="Ex: 2 dias úteis, 24 horas">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="termos_condicoes" class="form-label">Termos e Condições (Visível ao cliente)</label>
                    <textarea class="form-control" id="termos_condicoes" name="termos_condicoes" rows="4" placeholder="Ex: Garantia de 90 dias para peças e serviços (defeitos de fabricação/serviço). Exclui-se mau uso, líquidos ou quedas. Aparelhos não retirados em 90 dias após comunicação de conclusão poderão ser descartados/vendidos para cobrir custos, cf. legislação. Orçamento válido por X dias.">{{ old('termos_condicoes', config('app.orcamento_termos_padrao', "Garantia de 90 dias para peças e serviços (defeitos de fabricação/serviço). Exclui-se mau uso, contato com líquidos ou quedas. Orçamento válido por 7 dias. Aparelhos não retirados em 90 dias após comunicação de conclusão poderão ser descartados/vendidos para cobrir custos, cf. legislação.")) }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="observacoes_internas" class="form-label">Observações Internas (Não visível ao cliente)</label>
                    <textarea class="form-control" id="observacoes_internas" name="observacoes_internas" rows="2" placeholder="Anotações internas sobre o orçamento, negociações, etc.">{{ old('observacoes_internas') }}</textarea>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-end">
            <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary me-2"><i class="bi bi-x-circle"></i> Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save2-fill"></i> Salvar Orçamento</button>
        </div>
    </form>
</div>

{{-- Template para os itens do orçamento (reutilizado) --}}
<div id="orcamento-item-template" style="display: none;">
    @include('orcamentos._item_orcamento_template', ['index' => '__INDEX__'])
</div>

@include('clientes.partials.modal_create') {{-- Inclui o modal de criar cliente --}}

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Função para habilitar/desabilitar campos de cliente avulso e span de obrigatório
    function toggleClienteAvulsoOrcamento() {
        const clienteIdVal = $('#cliente_id_orcamento').val();
        const camposAvulsos = $('#nome_cliente_avulso_orcamento, #telefone_cliente_avulso_orcamento, #email_cliente_avulso_orcamento');
        const spanReq = $('#span_nome_avulso_req');

        if (clienteIdVal) { // Se um cliente CADASTRADO foi selecionado
            camposAvulsos.val('').prop('readonly', true).removeClass('is-invalid');
            $('#nome_cliente_avulso_orcamento').prop('required', false);
            spanReq.hide();
            // Limpar erros de validação dos campos avulsos
            camposAvulsos.siblings('.invalid-feedback').remove();
        } else { // Se NENHUM cliente cadastrado está selecionado
            camposAvulsos.prop('readonly', false);
            $('#nome_cliente_avulso_orcamento').prop('required', true);
            spanReq.show();
        }
    }

    function exibirInfoClienteOrcamento(cliente) {
        var infoDiv = $('#info_cliente_selecionado_orcamento');
        if (cliente && cliente.id) {
            var infoHtml = '<strong>ID:</strong> ' + cliente.id +
                           (cliente.telefone ? ' | <strong>Tel:</strong> ' + cliente.telefone : '') +
                           (cliente.email ? ' | <strong>Email:</strong> ' + cliente.email : '');
            infoDiv.html(infoHtml).show();
        } else {
            infoDiv.hide().html('');
        }
    }

    let clienteSelecionadoPeloAutocompleteOrc = false;
    $("#cliente_search_orcamento").autocomplete({
        source: "{{ route('clientes.autocomplete') }}", // Sua rota de autocomplete de clientes
        minLength: 2,
        select: function(event, ui) {
            if (ui.item && ui.item.id) {
                $('#cliente_search_orcamento').val(ui.item.value); // Nome completo
                $('#cliente_id_orcamento').val(ui.item.id);
                exibirInfoClienteOrcamento(ui.item); // Exibe telefone e email
                clienteSelecionadoPeloAutocompleteOrc = true;
                toggleClienteAvulsoOrcamento();
                // Limpar erros do campo cliente_id e nome_cliente_avulso
                $('#cliente_id_orcamento').removeClass('is-invalid').siblings('.invalid-feedback').remove();
                $('#nome_cliente_avulso_orcamento').removeClass('is-invalid').siblings('.invalid-feedback').remove();
            }
            return false;
        },
        change: function(event, ui) {
            if (!clienteSelecionadoPeloAutocompleteOrc && !ui.item) {
                $('#cliente_id_orcamento').val('');
                exibirInfoClienteOrcamento(null);
            }
            toggleClienteAvulsoOrcamento();
        }
    });

    $("#cliente_search_orcamento").on('input', function() {
        if ($(this).val() === '') {
            $('#cliente_id_orcamento').val('');
            exibirInfoClienteOrcamento(null);
            clienteSelecionadoPeloAutocompleteOrc = false;
        } else if (!clienteSelecionadoPeloAutocompleteOrc) {
            $('#cliente_id_orcamento').val('');
        }
        toggleClienteAvulsoOrcamento();
    });

    // Lógica do Modal de Novo Cliente (como na view de atendimento)
    $('#formNovoCliente').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $('#btnSalvarNovoCliente').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Salvando...');
        $('#formNovoCliente .invalid-feedback').remove();
        $('#formNovoCliente .is-invalid').removeClass('is-invalid');
        $.ajax({
            url: "{{ route('clientes.store') }}", type: 'POST', data: formData, dataType: 'json',
            success: function(response) {
                if (response.success && response.cliente) {
                    var cliente = response.cliente;
                    $('#cliente_search_orcamento').val(cliente.nome_completo);
                    $('#cliente_id_orcamento').val(cliente.id);
                    exibirInfoClienteOrcamento(cliente);
                    clienteSelecionadoPeloAutocompleteOrc = true;
                    toggleClienteAvulsoOrcamento();
                    $('#modalNovoCliente').modal('hide');
                    $('#formNovoCliente')[0].reset();
                } else {
                    // alert(response.message || 'Ocorreu um erro.');
                    if (response.errors) {
                        $.each(response.errors, function(key, value) {
                            $('#modal_' + key).addClass('is-invalid').closest('.mb-3').append('<div class="invalid-feedback d-block">' + value[0] + '</div>');
                        });
                    }
                }
            },
            error: function(xhr) {
                // alert('Erro ao conectar com o servidor.');
                 if (xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        $('#modal_' + key).addClass('is-invalid').closest('.mb-3').append('<div class="invalid-feedback d-block">' + value[0] + '</div>');
                    });
                } else {
                    alert('Erro desconhecido ao salvar cliente.');
                }
            },
            complete: function() { $('#btnSalvarNovoCliente').prop('disabled', false).html('Salvar Cliente'); }
        });
    });
    // Máscaras e ViaCEP do modal (como antes)
    if ($.fn.mask) {
        var CpfCnpjMaskBehaviorModal = function(val) { return val.replace(/\D/g, '').length <= 11 ? '000.000.000-009' : '00.000.000/0000-00'; },
            cpfCnpjOptionsModal = { onKeyPress: function(val, e, field, options) { field.mask(CpfCnpjMaskBehaviorModal.apply({}, arguments), options); } };
        $('#modal_cpf_cnpj').mask(CpfCnpjMaskBehaviorModal, cpfCnpjOptionsModal);
        var SPMaskBehaviorModal = function(val) { return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009'; },
            spOptionsModal = { onKeyPress: function(val, e, field, options) { field.mask(SPMaskBehaviorModal.apply({}, arguments), options); } };
        $('#modal_telefone').mask(SPMaskBehaviorModal, spOptionsModal);
        $('#telefone_cliente_avulso_orcamento').mask(SPMaskBehaviorModal, spOptionsModal); // Adiciona máscara ao telefone avulso
        $('#modal_cep').mask('00000-000');
    }
    $('#modal_cep').on('blur', function() { /* ... lógica ViaCEP do modal ... */
        var cep = $(this).val().replace(/\D/g, '');
        var formDoModal = $(this).closest('form'); // Garante que estamos no escopo do modal
        if (cep.length === 8) {
            formDoModal.find('#endereco_modal_fields').slideDown();
            $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {
                if (!("erro" in dados)) {
                    formDoModal.find('#modal_logradouro').val(dados.logradouro);
                    formDoModal.find('#modal_bairro').val(dados.bairro);
                    formDoModal.find('#modal_cidade').val(dados.localidade);
                    formDoModal.find('#modal_estado').val(dados.uf);
                    formDoModal.find('#modal_numero').focus();
                } else {
                    alert("CEP (Modal) não encontrado.");
                    formDoModal.find('#modal_logradouro, #modal_bairro, #modal_cidade, #modal_estado').val('');
                }
            }).fail(function() { alert("Erro ao consultar o serviço de CEP no modal."); });
        }
    });
     $('#modalNovoCliente').on('hidden.bs.modal', function () {
        $('#formNovoCliente')[0].reset();
        $('#formNovoCliente .is-invalid').removeClass('is-invalid');
        $('#formNovoCliente .invalid-feedback').remove();
        $('#formNovoCliente').find('#endereco_modal_fields').hide();
    });


    // ----- Lógica para Itens do Orçamento (mantida da sua versão create.blade.php) -----
    let itemOrcamentoIndex = $('#itens-orcamento-container .item-orcamento-row').length;

    function adicionarItemOrcamentoComDados(itemData = null) {
        let template = $('#orcamento-item-template').html().replace(/__INDEX__/g, itemOrcamentoIndex);
        $('#itens-orcamento-container').append(template);
        let novaLinha = $('#itens-orcamento-container .item-orcamento-row[data-index="' + itemOrcamentoIndex + '"]');

        if (itemData) {
            novaLinha.find('input[name$="[id]"]').val(itemData.id || '');
            novaLinha.find('.tipo-item-select').val(itemData.tipo_item || 'servico');
            if (itemData.tipo_item === 'peca') {
                novaLinha.find('.item-estoque-search').val(itemData.nome_peca_search || '');
                novaLinha.find('.item-estoque-id').val(itemData.estoque_id || '');
                if(itemData.estoque_id && itemData.nome_peca_search === ''){ // Tenta buscar o nome se só veio o ID (old)
                    // Esta parte é complexa e pode precisar de uma chamada AJAX para buscar o nome da peça
                    // Por ora, se o nome não veio no old, o usuário terá que buscar de novo.
                }
            } else {
                novaLinha.find('.descricao-servico-manual').val(itemData.descricao_item_manual || '');
            }
            novaLinha.find('.item-quantidade').val(itemData.quantidade || 1);
            novaLinha.find('.item-preco-unitario').val(parseFloat(itemData.valor_unitario || 0).toFixed(2));
        }

        initializeAutocompleteEstoqueOrc(novaLinha.find('.item-estoque-search'));
        toggleItemFieldsOrc(novaLinha.find('.tipo-item-select'));
        itemOrcamentoIndex++;
        calcularTotaisOrcamento();
    }


    $('#adicionar-item-orcamento').on('click', function() {
        adicionarItemOrcamentoComDados();
    });

    $('#itens-orcamento-container').on('click', '.remover-item-orcamento', function() {
        $(this).closest('.item-orcamento-row').remove();
        calcularTotaisOrcamento();
        if ($('#itens-orcamento-container .item-orcamento-row').length === 0) {
             itemOrcamentoIndex = 0; // Reseta o índice se todos os itens forem removidos
        }
    });

    function toggleItemFieldsOrc(selectElement) {
        var row = $(selectElement).closest('.item-orcamento-row');
        var tipo = $(selectElement).val();
        var infoPecaDiv = row.find('div[id^="info_peca_orc_"]');

        if (tipo === 'peca') {
            row.find('.campo-peca').show();
            row.find('.campo-servico').hide();
            row.find('.item-estoque-search').prop('required', true);
            row.find('.descricao-servico-manual').prop('required', false).val('');
            infoPecaDiv.show();
        } else if (tipo === 'servico') {
            row.find('.campo-peca').hide();
            row.find('.campo-servico').show();
            row.find('.item-estoque-search').prop('required', false).val('');
            row.find('.item-estoque-id').val('');
            // row.find('.item-preco-unitario').val('0.00'); // Não limpar se pode haver valor padrão
            row.find('.descricao-servico-manual').prop('required', true);
            infoPecaDiv.hide().html('');
        } else {
            row.find('.campo-peca').hide();
            row.find('.campo-servico').hide();
            row.find('.item-estoque-search').prop('required', false);
            row.find('.descricao-servico-manual').prop('required', false);
            infoPecaDiv.hide().html('');
        }
        calcularTotaisOrcamento();
    }

    $('#itens-orcamento-container').on('change', '.tipo-item-select', function() {
        toggleItemFieldsOrc(this);
    });

    // Inicializar campos para itens 'old'
    if (itemOrcamentoIndex > 0) {
         $('#itens-orcamento-container .item-orcamento-row').each(function() {
            initializeAutocompleteEstoqueOrc($(this).find('.item-estoque-search'));
            toggleItemFieldsOrc($(this).find('.tipo-item-select'));
        });
        calcularTotaisOrcamento(); // Calcula totais para os itens 'old'
    } else {
        adicionarItemOrcamentoComDados(); // Adiciona o primeiro item se não houver 'old'
    }


    function initializeAutocompleteEstoqueOrc(element) {
        element.autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('estoque.autocomplete') }}",
                    dataType: "json",
                    data: {
                        search: request.term,
                        tipos_filtro: ['ACESSORIO_VENDA', 'GERAL', 'PECA_REPARO']
                    },
                    success: function(data) { response(data); }
                });
            },
            minLength: 1,
            select: function(event, ui) {
                var row = $(this).closest('.item-orcamento-row');
                row.find('.item-estoque-search').val(ui.item.value);
                row.find('.item-estoque-id').val(ui.item.id);
                row.find('.item-preco-unitario').val(ui.item.preco_venda); // Usa o preço de venda do estoque

                // Exibe informações da peça
                var infoDivPeca = row.find('div[id^="info_peca_orc_"]');
                var tipoPecaFormatado = ui.item.tipo_formatado || ui.item.tipo_original || 'N/D';
                var infoPecaHtml = '<i class="bi bi-info-circle"></i> Qtd. Disp.: ' + ui.item.quantidade_disponivel + ' | Tipo: ' + tipoPecaFormatado;
                infoDivPeca.html(infoPecaHtml).show();

                calcularTotaisOrcamento();
                return false;
            },
            change: function(event, ui) {
                var row = $(this).closest('.item-orcamento-row');
                if (!ui.item) {
                    row.find('.item-estoque-id').val('');
                    row.find('div[id^="info_peca_orc_"]').hide().html('');
                    // Não limpar o nome ou preço, pode ser peça/serviço manual
                }
                calcularTotaisOrcamento();
            }
        });
    }

    function calcularTotaisOrcamento() {
        let totalServicos = 0;
        let totalPecas = 0;

        $('#itens-orcamento-container .item-orcamento-row').each(function() {
            let tipo = $(this).find('.tipo-item-select').val();
            let quantidade = parseFloat($(this).find('.item-quantidade').val().replace(',', '.')) || 0;
            let valorUnitario = parseFloat($(this).find('.item-preco-unitario').val().replace(',', '.')) || 0;
            let subtotalItem = quantidade * valorUnitario;

            $(this).find('.item-subtotal-display').text(subtotalItem.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

            if (tipo === 'servico') {
                totalServicos += subtotalItem;
            } else if (tipo === 'peca') {
                totalPecas += subtotalItem;
            }
        });

        $('#valor_total_servicos_display').val(totalServicos.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#valor_total_pecas_display').val(totalPecas.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        let subTotalOrcamento = totalServicos + totalPecas;
        $('#sub_total_display').val(subTotalOrcamento.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        let descontoTipo = $('#desconto_tipo').val();
        let descontoValorInputStr = $('#desconto_valor').val().replace(',', '.');
        let descontoValorInput = parseFloat(descontoValorInputStr) || 0;
        let valorFinal = subTotalOrcamento;
        let descontoCalculado = 0;

        if (descontoValorInput > 0 && descontoTipo && subTotalOrcamento > 0) {
            if (descontoTipo === 'percentual') {
                descontoCalculado = (subTotalOrcamento * descontoValorInput) / 100;
                $('#desconto_info').text(descontoValorInput.toLocaleString('pt-BR') + '% de R$ ' + subTotalOrcamento.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' = R$ ' + descontoCalculado.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            } else if (descontoTipo === 'fixo') {
                descontoCalculado = descontoValorInput;
                $('#desconto_info').text('Desconto fixo de R$ ' + descontoCalculado.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            }
             // Garante que o desconto não seja maior que o subtotal
            descontoCalculado = Math.min(descontoCalculado, subTotalOrcamento);
        } else {
            $('#desconto_info').text('');
        }
        valorFinal = subTotalOrcamento - descontoCalculado;
        valorFinal = Math.max(0, valorFinal);
        $('#valor_final_display').val(valorFinal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }

    $('#itens-orcamento-container').on('input', '.item-quantidade, .item-preco-unitario', calcularTotaisOrcamento);
    $('#desconto_tipo, #desconto_valor').on('input change', calcularTotaisOrcamento);

    // Inicializa o estado dos campos de cliente avulso e totais
    toggleClienteAvulsoOrcamento();
    if (itemOrcamentoIndex > 0) { // Se houver itens 'old'
        calcularTotaisOrcamento();
    }

    // Se o cliente_id já vier preenchido (ex: vindo de um link), busca e exibe as infos
    const initialClienteId = $('#cliente_id_orcamento').val();
    if (initialClienteId && !$('#cliente_search_orcamento').val()) { // Apenas se o nome não estiver preenchido
        $.ajax({
            url: "{{ route('clientes.autocomplete') }}",
            dataType: "json", data: { search: initialClienteId }, // Busca por ID pode não funcionar bem com o autocomplete padrão
            success: function(data) {
                // Tenta encontrar o cliente pelo ID na resposta
                const clienteEncontrado = data.find(c => c.id == initialClienteId);
                if (clienteEncontrado) {
                    $('#cliente_search_orcamento').val(clienteEncontrado.value);
                    exibirInfoClienteOrcamento(clienteEncontrado);
                    clienteSelecionadoPeloAutocompleteOrc = true;
                    toggleClienteAvulsoOrcamento();
                }
            }
        });
    } else if ($('#cliente_id_orcamento').val() && $('#cliente_search_orcamento').val()){
        // Se já tem ID e nome (veio de old() por exemplo), tenta buscar infos adicionais
        const tempClienteId = $('#cliente_id_orcamento').val();
         $.ajax({
            url: "{{ route('clientes.autocomplete') }}",
            dataType: "json", data: { search: tempClienteId },
            success: function(data) {
                const clienteEncontrado = data.find(c => c.id == tempClienteId);
                if (clienteEncontrado) {
                    exibirInfoClienteOrcamento(clienteEncontrado);
                }
            }
        });
    }


});
</script>
@endpush
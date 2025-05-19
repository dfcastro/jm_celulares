@extends('layouts.app') {{-- Ou o layout que você está usando --}}

@section('title', 'Novo Orçamento')

@push('styles')
<style>
    .item-orcamento-row {
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        border-bottom: 1px dashed #eee;
    }

    .item-orcamento-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
</style>
@endpush

@section('content')
<div class="container mt-0">
    <h1><i class="bi bi-file-earmark-plus-fill"></i> Criar Novo Orçamento</h1>

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="bi bi-exclamation-triangle-fill"></i> Ops! Verifique os erros abaixo:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('orcamentos.store') }}" method="POST" id="formCriarOrcamento">
        @csrf
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="my-1"><i class="bi bi-person-badge"></i> Dados do Cliente e Aparelho</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="cliente_search_orcamento" class="form-label">Buscar Cliente Cadastrado</label>
                        <div class="input-group">
                            <input type="text"
                                class="form-control @error('cliente_id') is-invalid @enderror"
                                id="cliente_search_orcamento"
                                name="cliente_search_orcamento_display" {{-- Nome apenas para display e busca --}}
                                placeholder="Digite nome ou CPF/CNPJ para buscar cliente..."
                                value="{{ old('cliente_search_orcamento_display', $orcamento->cliente->nome_completo ?? '') }}"> {{-- Para edição --}}
                            {{-- Futuramente, um botão para limpar a seleção do cliente aqui pode ser útil --}}
                        </div>
                        <input type="hidden" id="cliente_id_orcamento" name="cliente_id" value="{{ old('cliente_id', $orcamento->cliente_id ?? '') }}">
                        @error('cliente_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Deixe em branco ou preencha os campos abaixo para um cliente não cadastrado.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-7 mb-3">
                        <label for="nome_cliente_avulso" class="form-label">Ou informe o Nome do Cliente (Avulso)</label>
                        <input type="text" class="form-control @error('nome_cliente_avulso') is-invalid @enderror"
                            id="nome_cliente_avulso_orcamento" name="nome_cliente_avulso"
                            value="{{ old('nome_cliente_avulso', $orcamento->nome_cliente_avulso ?? '') }}"
                            placeholder="Nome completo do cliente não cadastrado">
                        @error('nome_cliente_avulso')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="telefone_cliente_avulso_orcamento" class="form-label">Telefone (Avulso)</label>
                        <input type="text" class="form-control"
                            id="telefone_cliente_avulso_orcamento" name="telefone_cliente_avulso"
                            value="{{ old('telefone_cliente_avulso', $orcamento->telefone_cliente_avulso ?? '') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="email_cliente_avulso_orcamento" class="form-label">Email (Avulso)</label>
                    <input type="email" class="form-control"
                        id="email_cliente_avulso_orcamento" name="email_cliente_avulso"
                        value="{{ old('email_cliente_avulso', $orcamento->email_cliente_avulso ?? '') }}">
                </div>

                <hr>
                <div class="mb-3">
                    <label for="descricao_aparelho" class="form-label">Descrição do Aparelho <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="descricao_aparelho" name="descricao_aparelho" value="{{ old('descricao_aparelho') }}" placeholder="Ex: iPhone 12 Pro Max Preto, Samsung S23 com película" required>
                </div>
                <div class="mb-3">
                    <label for="problema_relatado_cliente" class="form-label">Problema Relatado <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="problema_relatado_cliente" name="problema_relatado_cliente" rows="3" placeholder="Ex: Tela não liga, não carrega, molhou" required>{{ old('problema_relatado_cliente') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="my-1"><i class="bi bi-tools"></i> Itens do Orçamento (Peças e Serviços)</h5>
            </div>
            <div class="card-body">
                <div id="itens-orcamento-container">
                    {{-- Itens serão adicionados aqui via JavaScript --}}
                    @if(old('itens'))
                    @foreach(old('itens') as $index => $itemData)
                    @include('orcamentos._item_orcamento_template', ['index' => $index, 'itemData' => $itemData])
                    @endforeach
                    @endif
                </div>
                <button type="button" class="btn btn-secondary btn-sm mt-3" id="adicionar-item-orcamento">
                    <i class="bi bi-plus-circle"></i> Adicionar Item (Peça/Serviço)
                </button>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="my-1"><i class="bi bi-calculator-fill"></i> Valores e Condições</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="valor_total_servicos_display" class="form-label">Total Serviços (R$)</label>
                        <input type="text" class="form-control" id="valor_total_servicos_display" readonly value="0.00">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="valor_total_pecas_display" class="form-label">Total Peças (R$)</label>
                        <input type="text" class="form-control" id="valor_total_pecas_display" readonly value="0.00">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="sub_total_display" class="form-label">Subtotal (R$)</label>
                        <input type="text" class="form-control" id="sub_total_display" readonly value="0.00">
                    </div>
                </div>
                <div class="row">
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
                        <input type="number" step="0.01" class="form-control" id="desconto_valor" name="desconto_valor" value="{{ old('desconto_valor', '0.00') }}" min="0">
                        <small id="desconto_info" class="form-text text-muted"></small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="valor_final_display" class="form-label fw-bold">VALOR FINAL (R$)</label>
                        <input type="text" class="form-control fw-bold" id="valor_final_display" readonly value="0.00" style="font-size: 1.1rem;">
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="data_emissao" class="form-label">Data de Emissão <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="data_emissao" name="data_emissao" value="{{ old('data_emissao', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="validade_dias" class="form-label">Validade (dias)</label>
                        <input type="number" class="form-control" id="validade_dias" name="validade_dias" value="{{ old('validade_dias', 7) }}" min="0">
                        <small class="form-text text-muted">Deixe 0 ou vazio para sem validade.</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tempo_estimado_servico" class="form-label">Tempo Estimado de Serviço</label>
                        <input type="text" class="form-control" id="tempo_estimado_servico" name="tempo_estimado_servico" value="{{ old('tempo_estimado_servico') }}" placeholder="Ex: 2 dias úteis, 24 horas">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="termos_condicoes" class="form-label">Termos e Condições (Visível ao cliente)</label>
                    <textarea class="form-control" id="termos_condicoes" name="termos_condicoes" rows="3">{{ old('termos_condicoes', "Garantia de 90 dias para peças e serviços, exceto por mau uso, contato com líquidos ou quedas. Orçamento válido por X dias.") }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="observacoes_internas" class="form-label">Observações Internas (Não visível ao cliente)</label>
                    <textarea class="form-control" id="observacoes_internas" name="observacoes_internas" rows="2">{{ old('observacoes_internas') }}</textarea>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-end">
            <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary me-2">Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save2-fill"></i> Salvar Orçamento</button>
        </div>
    </form>
</div>

{{-- Template para os itens do orçamento (será usado pelo JavaScript) --}}
<div id="orcamento-item-template" style="display: none;">
    @include('orcamentos._item_orcamento_template', ['index' => '__INDEX__'])
</div>

@endsection

@push('scripts')
{{-- jQuery e jQuery UI já estão no layout base (app.blade.php) --}}
<script>
    $(document).ready(function() {
        // Autocomplete para cliente_id (opcional, se quiser buscar aqui também)
        $("#cliente_id_text_search").autocomplete({ // Supondo que você adicione um input com este ID
            source: "{{ route('clientes.autocomplete') }}",
            minLength: 2,
            select: function(event, ui) {
                $('#cliente_id').val(ui.item.id);
                // Preencher nome_cliente_avulso e outros campos se desejar
                $('#nome_cliente_avulso').val(ui.item.value).prop('readonly', true);
                // ...
            }
        });

        // Lógica para habilitar/desabilitar campos de cliente avulso
        function toggleClienteAvulso() {
            if ($('#cliente_id').val()) {
                $('#nome_cliente_avulso').val('').prop('readonly', true).prop('required', false);
                $('#telefone_cliente_avulso').val('').prop('readonly', true);
                $('#email_cliente_avulso').val('').prop('readonly', true);
                $('#span_nome_avulso_req').hide();
            } else {
                $('#nome_cliente_avulso').prop('readonly', false).prop('required', true);
                $('#telefone_cliente_avulso').prop('readonly', false);
                $('#email_cliente_avulso').prop('readonly', false);
                $('#span_nome_avulso_req').show();
            }
        }
        $('#cliente_id').on('change', toggleClienteAvulso);
        toggleClienteAvulso(); // Chamada inicial


        // Adicionar Itens ao Orçamento
        let itemOrcamentoIndex = $('#itens-orcamento-container .item-orcamento-row').length;

        $('#adicionar-item-orcamento').on('click', function() {
            let template = $('#orcamento-item-template').html().replace(/__INDEX__/g, itemOrcamentoIndex);
            $('#itens-orcamento-container').append(template);
            initializeAutocompleteEstoque($('#itens-orcamento-container .item-orcamento-row[data-index="' + itemOrcamentoIndex + '"] .item-estoque-search'));
            itemOrcamentoIndex++;
            toggleItemFields($('#itens-orcamento-container .item-orcamento-row:last-child .tipo-item-select'));
        });

        // Remover Itens
        $('#itens-orcamento-container').on('click', '.remover-item-orcamento', function() {
            $(this).closest('.item-orcamento-row').remove();
            calcularTotaisOrcamento();
        });

        // Mostrar/ocultar campos dependendo do tipo de item (peça/serviço)
        function toggleItemFields(selectElement) {
            var row = $(selectElement).closest('.item-orcamento-row');
            var tipo = $(selectElement).val();

            if (tipo === 'peca') {
                row.find('.campo-peca').show();
                row.find('.campo-servico').hide();
                row.find('.item-estoque-search').prop('required', true);
                row.find('.descricao-servico-manual').prop('required', false).val('');
            } else if (tipo === 'servico') {
                row.find('.campo-peca').hide();
                row.find('.campo-servico').show();
                row.find('.item-estoque-search').prop('required', false).val('');
                row.find('.item-estoque-id').val('');
                row.find('.item-preco-unitario').val('0.00'); // Pode querer limpar ou não
                row.find('.descricao-servico-manual').prop('required', true);
            } else {
                row.find('.campo-peca').hide();
                row.find('.campo-servico').hide();
                row.find('.item-estoque-search').prop('required', false);
                row.find('.descricao-servico-manual').prop('required', false);
            }
            calcularTotaisOrcamento();
        }

        $('#itens-orcamento-container').on('change', '.tipo-item-select', function() {
            toggleItemFields(this);
        });

        // Inicializar campos para itens já existentes (caso de 'old' input)
        $('#itens-orcamento-container .tipo-item-select').each(function() {
            toggleItemFields(this);
            initializeAutocompleteEstoque($(this).closest('.item-orcamento-row').find('.item-estoque-search'));
        });


        // Autocomplete para Peças do Estoque nos Itens
        function initializeAutocompleteEstoque(element) {
            element.autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "{{ route('estoque.autocomplete') }}", // Sua rota de autocomplete de estoque
                        dataType: "json",
                        data: {
                            search: request.term,
                            tipos_filtro: ['ACESSORIO_VENDA', 'GERAL', 'PECA_REPARO'] // Para orçamento, pode ser qualquer tipo
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                minLength: 1,
                select: function(event, ui) {
                    var row = $(this).closest('.item-orcamento-row');
                    row.find('.item-estoque-search').val(ui.item.value); // Nome da peça
                    row.find('.item-estoque-id').val(ui.item.id);
                    row.find('.item-preco-unitario').val(ui.item.preco_venda);
                    // Não alteramos a quantidade aqui, deixamos o usuário definir
                    calcularTotaisOrcamento();
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        var row = $(this).closest('.item-orcamento-row');
                        row.find('.item-estoque-id').val('');
                        // Não limpar o preço ou nome, pois pode ser uma peça avulsa que o usuário descreverá
                        // ou o usuário pode ter limpado intencionalmente.
                    }
                    calcularTotaisOrcamento();
                }
            });
        }

        // Calcular Totais do Orçamento
        function calcularTotaisOrcamento() {
            let totalServicos = 0;
            let totalPecas = 0;

            $('#itens-orcamento-container .item-orcamento-row').each(function() {
                let tipo = $(this).find('.tipo-item-select').val();
                let quantidade = parseFloat($(this).find('.item-quantidade').val()) || 0;
                let valorUnitario = parseFloat($(this).find('.item-preco-unitario').val()) || 0;
                let subtotalItem = quantidade * valorUnitario;

                $(this).find('.item-subtotal-display').text(subtotalItem.toFixed(2)); // Atualiza display do subtotal do item

                if (tipo === 'servico') {
                    totalServicos += subtotalItem;
                } else if (tipo === 'peca') {
                    totalPecas += subtotalItem;
                }
            });

            $('#valor_total_servicos_display').val(totalServicos.toFixed(2));
            $('#valor_total_pecas_display').val(totalPecas.toFixed(2));

            let subTotalOrcamento = totalServicos + totalPecas;
            $('#sub_total_display').val(subTotalOrcamento.toFixed(2));

            let descontoTipo = $('#desconto_tipo').val();
            let descontoValorInput = parseFloat($('#desconto_valor').val()) || 0;
            let valorFinal = subTotalOrcamento;
            let descontoCalculado = 0;

            if (descontoValorInput > 0) {
                if (descontoTipo === 'percentual') {
                    descontoCalculado = (subTotalOrcamento * descontoValorInput) / 100;
                    $('#desconto_info').text(descontoValorInput + '% de R$ ' + subTotalOrcamento.toFixed(2) + ' = R$ ' + descontoCalculado.toFixed(2));
                } else if (descontoTipo === 'fixo') {
                    descontoCalculado = descontoValorInput;
                    $('#desconto_info').text('Desconto fixo de R$ ' + descontoCalculado.toFixed(2));
                }
            } else {
                $('#desconto_info').text('');
            }

            valorFinal = subTotalOrcamento - descontoCalculado;
            valorFinal = Math.max(0, valorFinal); // Garante que não seja negativo

            $('#valor_final_display').val(valorFinal.toFixed(2));
        }

        $('#itens-orcamento-container').on('input', '.item-quantidade, .item-preco-unitario', calcularTotaisOrcamento);
        $('#desconto_tipo, #desconto_valor').on('input change', calcularTotaisOrcamento);

        // Adicionar primeira linha de item se não houver 'old' data
        if (itemOrcamentoIndex === 0) {
            $('#adicionar-item-orcamento').click();
        } else {
            // Para itens 'old', recalcular totais na carga
            calcularTotaisOrcamento();
        }
        let clienteSelecionadoPeloAutocompleteOrcamento = false;

        $("#cliente_search_orcamento").autocomplete({
            source: function(request, response) {
                clienteSelecionadoPeloAutocompleteOrcamento = false; // Reseta a flag
                $.ajax({
                    url: "{{ route('clientes.autocomplete') }}", // Sua rota de autocomplete de clientes
                    dataType: "json",
                    data: {
                        search: request.term
                    },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: item.label, // Ex: "Nome Cliente (CPF/CNPJ)"
                                value: item.value, // Ex: "Nome Cliente" (o que vai pro input visível)
                                id: item.id,
                                // Pode adicionar outros dados do cliente se precisar preencher mais campos automaticamente
                                // telefone: item.telefone,
                                // email: item.email
                            };
                        }));
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                if (ui.item && ui.item.id) {
                    $('#cliente_search_orcamento').val(ui.item.value); // Nome para o campo de busca
                    $('#cliente_id_orcamento').val(ui.item.id); // ID para o campo hidden
                    clienteSelecionadoPeloAutocompleteOrcamento = true;

                    // Limpar campos de cliente avulso, pois um cadastrado foi selecionado
                    $('#nome_cliente_avulso_orcamento').val('');
                    $('#telefone_cliente_avulso_orcamento').val('');
                    $('#email_cliente_avulso_orcamento').val('');

                    // Opcional: focar no próximo campo
                    // $('#descricao_aparelho').focus();
                }
                return false;
            },
            change: function(event, ui) {
                // Se o campo foi alterado e nenhum item do autocomplete foi selecionado
                if (!clienteSelecionadoPeloAutocompleteOrcamento && !ui.item) {
                    $('#cliente_id_orcamento').val(''); // Limpa o ID se o nome não corresponder
                }
            }
        });

        // Se o campo de busca de cliente for limpo manualmente, limpar o ID também
        $("#cliente_search_orcamento").on('input', function() {
            if ($(this).val() === '') {
                $('#cliente_id_orcamento').val('');
                clienteSelecionadoPeloAutocompleteOrcamento = false;
            } else {
                // Se o usuário está digitando, e não é uma seleção de autocomplete,
                // o ID deve ser limpo para forçar uma nova seleção ou o uso dos campos de cliente avulso.
                if (!clienteSelecionadoPeloAutocompleteOrcamento) {
                    $('#cliente_id_orcamento').val('');
                }
            }
        });

        // Lógica para o formulário de criar orçamento (já existente)
        // ...

        // Lógica para popular os campos se estiver editando um orçamento
        // (Isso deve ser feito no OrcamentoController ao passar dados para a view edit.blade.php,
        // e o old() já deve cuidar de repopular)
        @if(isset($orcamento) && $orcamento -> cliente_id)
        // Se estiver editando e já tem um cliente vinculado,
        // o value do #cliente_search_orcamento e #cliente_id_orcamento
        // já devem ter sido preenchidos pelo old() ou pelo objeto $orcamento.
        // Você pode querer desabilitar os campos de cliente avulso se um cliente_id estiver presente.
        $('#nome_cliente_avulso_orcamento').prop('readonly', true);
        $('#telefone_cliente_avulso_orcamento').prop('readonly', true);
        $('#email_cliente_avulso_orcamento').prop('readonly', true);
        @elseif(!old('cliente_id'))
        // Se for criação e não houver old('cliente_id'), garante que os campos de avulso estão habilitados.
        $('#nome_cliente_avulso_orcamento').prop('readonly', false);
        $('#telefone_cliente_avulso_orcamento').prop('readonly', false);
        $('#email_cliente_avulso_orcamento').prop('readonly', false);
        @endif

        // Ao submeter, garantir que se cliente_id está preenchido, os campos de avulso sejam ignorados ou limpos
        $('#formCriarOrcamento').on('submit', function() { // Adicione um ID ao seu form: id="formCriarOrcamento"
            if ($('#cliente_id_orcamento').val()) {
                $('#nome_cliente_avulso_orcamento').val('');
                $('#telefone_cliente_avulso_orcamento').val('');
                $('#email_cliente_avulso_orcamento').val('');
            }
        });


        // Lógica da view de ATENDIMENTO para popular o autocomplete de cliente
        // (Adaptar se for reutilizar o mesmo JavaScript)
        // No seu `atendimentos/create.blade.php` o ID do input de busca é #cliente_nome
        // e o ID do campo hidden é #cliente_id.
        // Se você for usar o mesmo JS, precisará generalizar ou duplicar com os IDs corretos.
        // Para o formulário de orçamento, os IDs que usamos foram:
        // Campo de busca: #cliente_search_orcamento
        // Campo hidden para ID: #cliente_id_orcamento
    });
</script>
@endpush
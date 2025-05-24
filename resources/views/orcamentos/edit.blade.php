@extends('layouts.app')

@section('title', 'Editar Orçamento #' . $orcamento->id)

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

    .readonly-look {
        /* Para dar uma aparência de desabilitado sem usar 'disabled' que não envia o valor */
        background-color: #e9ecef;
        opacity: 0.7;
    }

    .disabled-card-section {
        opacity: 0.6;
        pointer-events: none;
        /* Impede interações com o mouse */
    }
</style>
@endpush

@section('content')
<div class="container mt-0">
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="bi bi-pencil-square"></i> Editar Orçamento #{{ $orcamento->id }}</h1>
        <a href="{{ route('orcamentos.show', $orcamento->id) }}" class="btn btn-sm btn-info">
            <i class="bi bi-eye"></i> Ver Detalhes do Orçamento
        </a>
    </div>

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

    @if(isset($edicaoApenasCliente) && $edicaoApenasCliente)
    <div class="alert alert-warning mt-3">
        <i class="bi bi-info-circle-fill"></i>
        <strong>Modo de Edição Restrita:</strong> Este orçamento já foi aprovado. Por favor, selecione ou cadastre um cliente abaixo para prosseguir com a conversão para Ordem de Serviço. Outros campos do orçamento não podem ser alterados neste momento.
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

    <form action="{{ route('orcamentos.update', $orcamento->id) }}" method="POST" id="formEditarOrcamento" class="mt-3">
        @csrf
        @method('PUT')

        {{-- Card de Dados do Cliente e Aparelho --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="my-1"><i class="bi bi-person-badge"></i> Dados do Cliente e Aparelho</h5>
            </div>
            <div class="card-body">
                {{-- Campos de Cliente --}}
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="cliente_search_orcamento_edit" class="form-label">Cliente Cadastrado <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text"
                                class="form-control @error('cliente_id') is-invalid @enderror"
                                id="cliente_search_orcamento_edit"
                                name="cliente_search_orcamento_display"
                                placeholder="Digite nome ou CPF/CNPJ para buscar..."
                                value="{{ old('cliente_search_orcamento_display', $orcamento->cliente->nome_completo ?? ($orcamento->nome_cliente_avulso && !$orcamento->cliente_id ? $orcamento->nome_cliente_avulso : '' ) ) }}"> {{-- Mostra nome avulso se não houver cliente_id --}}
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalNovoCliente">
                                <i class="bi bi-person-plus-fill"></i> Novo
                            </button>
                        </div>
                        <input type="hidden" id="cliente_id_orcamento_edit" name="cliente_id" value="{{ old('cliente_id', $orcamento->cliente_id ?? '') }}">
                        @error('cliente_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @if(!(isset($edicaoApenasCliente) && $edicaoApenasCliente))
                        <small class="form-text text-muted">Deixe em branco ou preencha os campos abaixo para um cliente não cadastrado (se permitido).</small>
                        @endif
                    </div>
                </div>

                <div class="row" @if(isset($edicaoApenasCliente) && $edicaoApenasCliente && $orcamento->cliente_id) style="display:none;" @endif> {{-- Esconde se for edição apenas de cliente E um cliente já foi selecionado --}}
                    <div class="col-md-7 mb-3">
                        <label for="nome_cliente_avulso_orcamento_edit" class="form-label">Ou informe o Nome do Cliente (Avulso)
                            <span id="span_nome_avulso_req_edit" class="text-danger" style="{{  old('cliente_id', $orcamento->cliente_id) ? 'display:none;' : '' }}">*</span></label>
                        <input type="text" class="form-control @error('nome_cliente_avulso') is-invalid @enderror"
                            id="nome_cliente_avulso_orcamento_edit" name="nome_cliente_avulso"
                            value="{{ old('nome_cliente_avulso', $orcamento->nome_cliente_avulso ?? '') }}"
                            @if( (isset($edicaoApenasCliente) && $edicaoApenasCliente) || $orcamento->cliente_id ) readonly @endif>
                        @error('nome_cliente_avulso') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="telefone_cliente_avulso_orcamento_edit" class="form-label">Telefone (Avulso)</label>
                        <input type="text" class="form-control"
                            id="telefone_cliente_avulso_orcamento_edit" name="telefone_cliente_avulso"
                            value="{{ old('telefone_cliente_avulso', $orcamento->telefone_cliente_avulso ?? '') }}"
                            @if( (isset($edicaoApenasCliente) && $edicaoApenasCliente) || $orcamento->cliente_id ) readonly @endif>
                    </div>
                </div>
                <div class="mb-3" @if(isset($edicaoApenasCliente) && $edicaoApenasCliente && $orcamento->cliente_id) style="display:none;" @endif>
                    <label for="email_cliente_avulso_orcamento_edit" class="form-label">Email (Avulso)</label>
                    <input type="email" class="form-control"
                        id="email_cliente_avulso_orcamento_edit" name="email_cliente_avulso"
                        value="{{ old('email_cliente_avulso', $orcamento->email_cliente_avulso ?? '') }}"
                        @if( (isset($edicaoApenasCliente) && $edicaoApenasCliente) || $orcamento->cliente_id ) readonly @endif>
                </div>
                <hr>
                <div class="mb-3">
                    <label for="descricao_aparelho_edit" class="form-label">Descrição do Aparelho <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly-look @endif" id="descricao_aparelho_edit" name="descricao_aparelho" value="{{ old('descricao_aparelho', $orcamento->descricao_aparelho) }}" required @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly @endif>
                </div>
                <div class="mb-3">
                    <label for="problema_relatado_cliente_edit" class="form-label">Problema Relatado <span class="text-danger">*</span></label>
                    <textarea class="form-control @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly-look @endif" id="problema_relatado_cliente_edit" name="problema_relatado_cliente" rows="3" required @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly @endif>{{ old('problema_relatado_cliente', $orcamento->problema_relatado_cliente) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="status_edit" class="form-label">Status do Orçamento <span class="text-danger">*</span></label>
                    <select class="form-select @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly-look @endif" id="status_edit" name="status" required @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) disabled @endif>
                        @foreach ($statusOrcamentoSelect as $status)
                        <option value="{{ $status }}" {{ old('status', $orcamento->status) == $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                        @endforeach
                    </select>
                    @if(isset($edicaoApenasCliente) && $edicaoApenasCliente)
                    <input type="hidden" name="status" value="{{ $orcamento->status }}">
                    @endif
                </div>
            </div>
        </div>

        {{-- Card de Itens do Orçamento --}}
        <div class="card shadow-sm mb-4 @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) disabled-card-section @endif">
            <div class="card-header">
                <h5 class="my-1"><i class="bi bi-tools"></i> Itens do Orçamento (Peças e Serviços)</h5>
            </div>
            <div class="card-body">
                <div id="itens-orcamento-container-edit">
                    @php $itemData = []; @endphp
                    @if(!empty(old('itens', $orcamento->itens->toArray())))
                    @foreach(old('itens', $orcamento->itens->toArray()) as $index => $itemDataOld)
                    @php
                    if (is_object($itemDataOld)) $itemDataOld = (array)$itemDataOld;
                    if (($itemDataOld['tipo_item'] ?? '') == 'peca' && !empty($itemDataOld['estoque_id'])) {
                    $itemModelDoOrcamento = $orcamento->itens->first(function ($itemModel) use ($itemDataOld) {
                    $itemIdNoOld = $itemDataOld['id'] ?? null; // ID do orcamento_items, se estiver vindo do 'old' de uma tentativa anterior
                    $estoqueIdNoOld = $itemDataOld['estoque_id'] ?? null;

                    if ($itemIdNoOld) { // Se temos um ID de item do orçamento (provavelmente de uma tentativa de update falha)
                    return $itemModel->id == $itemIdNoOld;
                    } elseif ($estoqueIdNoOld) { // Senão, tentamos pelo estoque_id (ao carregar inicialmente da DB)
                    return $itemModel->estoque_id == $estoqueIdNoOld;
                    }
                    return false;
                    });
                    if ($itemModelDoOrcamento && $itemModelDoOrcamento->estoque) {
                    $itemDataOld['nome_peca_search'] = $itemModelDoOrcamento->estoque->nome . ' (' . ($itemModelDoOrcamento->estoque->modelo_compativel ?? 'N/A') . ')';
                    }
                    }
                    @endphp
                    @include('orcamentos._item_orcamento_template', [
                    'index' => $index,
                    'itemData' => $itemDataOld,
                    'edicaoApenasCliente' => (isset($edicaoApenasCliente) && $edicaoApenasCliente)
                    ])
                    @endforeach
                    @endif
                </div>
                <button type="button" class="btn btn-secondary btn-sm mt-3" id="adicionar-item-orcamento-edit" @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) disabled @endif>
                    <i class="bi bi-plus-circle"></i> Adicionar Item
                </button>
            </div>
        </div>

        {{-- Card de Valores e Condições --}}
        <div class="card shadow-sm mb-4 @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) disabled-card-section @endif">
            <div class="card-header">
                <h5 class="my-1"><i class="bi bi-calculator-fill"></i> Valores e Condições</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="valor_total_servicos_display_edit" class="form-label">Total Serviços (R$)</label>
                        <input type="text" class="form-control readonly-look" id="valor_total_servicos_display_edit" readonly value="{{ number_format($orcamento->valor_total_servicos, 2, ',', '.') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="valor_total_pecas_display_edit" class="form-label">Total Peças (R$)</label>
                        <input type="text" class="form-control readonly-look" id="valor_total_pecas_display_edit" readonly value="{{ number_format($orcamento->valor_total_pecas, 2, ',', '.') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="sub_total_display_edit" class="form-label">Subtotal (R$)</label>
                        <input type="text" class="form-control readonly-look" id="sub_total_display_edit" readonly value="{{ number_format($orcamento->sub_total, 2, ',', '.') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="desconto_tipo_edit" class="form-label">Tipo de Desconto</label>
                        <select class="form-select @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly-look @endif" id="desconto_tipo_edit" name="desconto_tipo" @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) disabled @endif>
                            <option value="">Sem Desconto</option>
                            @foreach ($tiposDesconto as $key => $value)
                            <option value="{{ $key }}" {{ old('desconto_tipo', $orcamento->desconto_tipo) == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                            @endforeach
                        </select>
                        @if(isset($edicaoApenasCliente) && $edicaoApenasCliente && $orcamento->desconto_tipo)
                        <input type="hidden" name="desconto_tipo" value="{{ $orcamento->desconto_tipo }}">
                        @endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="desconto_valor_edit" class="form-label">Valor do Desconto</label>
                        <input type="number" step="0.01" class="form-control @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly-look @endif" id="desconto_valor_edit" name="desconto_valor" value="{{ old('desconto_valor', number_format($orcamento->desconto_valor ?? 0, 2, '.', '')) }}" min="0" @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly @endif>
                        <small id="desconto_info_edit" class="form-text text-muted"></small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="valor_final_display_edit" class="form-label fw-bold">VALOR FINAL (R$)</label>
                        <input type="text" class="form-control fw-bold readonly-look" id="valor_final_display_edit" readonly value="{{ number_format($orcamento->valor_final, 2, ',', '.') }}" style="font-size: 1.1rem;">
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="data_emissao_edit" class="form-label">Data de Emissão <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly-look @endif" id="data_emissao_edit" name="data_emissao" value="{{ old('data_emissao', $orcamento->data_emissao->format('Y-m-d')) }}" required @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly @endif>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="validade_dias_edit" class="form-label">Validade (dias)</label>
                        <input type="number" class="form-control @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly-look @endif" id="validade_dias_edit" name="validade_dias" value="{{ old('validade_dias', $orcamento->validade_dias ?? 7) }}" min="0" @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly @endif>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tempo_estimado_servico_edit" class="form-label">Tempo Estimado de Serviço</label>
                        <input type="text" class="form-control @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly-look @endif" id="tempo_estimado_servico_edit" name="tempo_estimado_servico" value="{{ old('tempo_estimado_servico', $orcamento->tempo_estimado_servico) }}" @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly @endif>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="termos_condicoes_edit" class="form-label">Termos e Condições</label>
                    <textarea class="form-control @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly-look @endif" id="termos_condicoes_edit" name="termos_condicoes" rows="3" @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly @endif>{{ old('termos_condicoes', $orcamento->termos_condicoes) }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="observacoes_internas_edit" class="form-label">Observações Internas</label>
                    <textarea class="form-control @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly-look @endif" id="observacoes_internas_edit" name="observacoes_internas" rows="2" @if(isset($edicaoApenasCliente) && $edicaoApenasCliente) readonly @endif>{{ old('observacoes_internas', $orcamento->observacoes_internas) }}</textarea>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-end">
            <a href="{{ route('orcamentos.show', $orcamento->id) }}" class="btn btn-secondary me-2">Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save2-fill"></i> Salvar Alterações</button>
        </div>
    </form>
</div>

{{-- Template para os itens do orçamento (reutilizado) --}}
<div id="orcamento-item-template-edit" style="display: none;">
    {{-- Passamos a flag para o template do item --}}
    @include('orcamentos._item_orcamento_template', ['index' => '__INDEX__', 'itemData' => [], 'edicaoApenasCliente' => (isset($edicaoApenasCliente) && $edicaoApenasCliente)])
</div>

@include('clientes.partials.modal_create') {{-- Inclui o modal de criar cliente --}}

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // A variável PHP $edicaoApenasCliente é injetada no JavaScript
     const edicaoApenasCliente = @json((isset($edicaoApenasCliente) && $edicaoApenasCliente) ? true : false);

        // Função para habilitar/desabilitar campos de cliente avulso e span de obrigatório
        function toggleClienteAvulsoEdit() {
            const clienteIdVal = $('#cliente_id_orcamento_edit').val();
            const camposAvulsos = $('#nome_cliente_avulso_orcamento_edit, #telefone_cliente_avulso_orcamento_edit, #email_cliente_avulso_orcamento_edit');
            const spanReq = $('#span_nome_avulso_req_edit');

            if (clienteIdVal) { // Se um cliente CADASTRADO foi selecionado
                camposAvulsos.val('').prop('readonly', true).removeClass('readonly-look'); // Limpa e torna readonly
                $('#nome_cliente_avulso_orcamento_edit').prop('required', false);
                spanReq.hide();
            } else { // Se NENHUM cliente cadastrado está selecionado
                // Se for edicaoApenasCliente, os campos avulsos devem ser readonly
                // Se não for edicaoApenasCliente, eles são editáveis
                camposAvulsos.prop('readonly', edicaoApenasCliente);
                if (edicaoApenasCliente) {
                    camposAvulsos.addClass('readonly-look');
                } else {
                    camposAvulsos.removeClass('readonly-look');
                }
                $('#nome_cliente_avulso_orcamento_edit').prop('required', !edicaoApenasCliente); // Obrigatório se for edição normal e não houver cliente_id
                spanReq.toggle(!edicaoApenasCliente && !clienteIdVal);
            }
        }
        toggleClienteAvulsoEdit(); // Chamada inicial para configurar o estado dos campos

        $("#cliente_search_orcamento_edit").autocomplete({
            source: "{{ route('clientes.autocomplete') }}",
            minLength: 2,
            select: function(event, ui) {
                if (ui.item && ui.item.id) {
                    $('#cliente_search_orcamento_edit').val(ui.item.value);
                    $('#cliente_id_orcamento_edit').val(ui.item.id);
                    $(this).data('ui-autocomplete-item-label', ui.item.value); // Guarda o label selecionado
                    toggleClienteAvulsoEdit(); // Atualiza o estado dos campos de cliente avulso
                }
                return false;
            }
        }).on('input', function() {
            // Se o usuário está digitando e o valor não corresponde ao que foi selecionado, limpa o ID
            if ($(this).val() !== $(this).data('ui-autocomplete-item-label')) {
                $('#cliente_id_orcamento_edit').val('');
                toggleClienteAvulsoEdit();
            }
        });


        $('#formEditarOrcamento').on('submit', function() {
            if ($('#cliente_id_orcamento_edit').val()) {
                $('#nome_cliente_avulso_orcamento_edit').val('');
                $('#telefone_cliente_avulso_orcamento_edit').val('');
                $('#email_cliente_avulso_orcamento_edit').val('');
            }
            // Re-habilita campos desabilitados pelo 'disabled' antes de submeter,
            // para que seus valores sejam enviados. O 'readonly' já envia.
            // Isso é importante para o select de status, por exemplo.
            $('#status_edit').prop('disabled', false);
            $('#desconto_tipo_edit').prop('disabled', false);
            // Para itens, se eles forem completamente desabilitados no template,
            // pode ser necessário uma lógica mais complexa para enviar os dados existentes se não foram alterados.
            // Por ora, se estão readonly, os valores serão enviados.
        });

        // ----- Lógica para Itens do Orçamento (adaptada para IDs de edição) -----
        let itemOrcamentoIndexEdit = $('#itens-orcamento-container-edit .item-orcamento-row').length;

        $('#adicionar-item-orcamento-edit').on('click', function() {
            if (edicaoApenasCliente) return; // Não adiciona itens se for edição apenas de cliente
            let templateHTML = $('#orcamento-item-template-edit').html();
            templateHTML = templateHTML.replace(/__INDEX__/g, itemOrcamentoIndexEdit);
            // A flag edicaoApenasCliente já é passada para o template _item_orcamento_template
            // então não precisa ser passada dinamicamente aqui de novo, a menos que o template seja mais complexo
            let $newItem = $(templateHTML);

            $('#itens-orcamento-container-edit').append($newItem);
            initializeAutocompleteEstoqueEdit($newItem.find('.item-estoque-search'));
            toggleItemFieldsEdit($newItem.find('.tipo-item-select')); // Aplica readonly/disabled no novo item
            itemOrcamentoIndexEdit++;
        });

        $('#itens-orcamento-container-edit').on('click', '.remover-item-orcamento', function() {
            if (edicaoApenasCliente) return;
            $(this).closest('.item-orcamento-row').remove();
            calcularTotaisOrcamentoEdit();
        });

        function toggleItemFieldsEdit(selectElement) {
            var row = $(selectElement).closest('.item-orcamento-row');
            var tipo = $(selectElement).val();

            row.find('.item-estoque-search, .descricao-servico-manual, .item-quantidade, .item-preco-unitario').prop('readonly', edicaoApenasCliente);
            if (edicaoApenasCliente) {
                row.find('.item-estoque-search, .descricao-servico-manual, .item-quantidade, .item-preco-unitario').addClass('readonly-look');
                row.find('.tipo-item-select').prop('disabled', true).addClass('readonly-look');
                row.find('.remover-item-orcamento').prop('disabled', true);
            } else {
                row.find('.item-estoque-search, .descricao-servico-manual, .item-quantidade, .item-preco-unitario').removeClass('readonly-look');
                row.find('.tipo-item-select').prop('disabled', false).removeClass('readonly-look');
                row.find('.remover-item-orcamento').prop('disabled', false);
            }


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
                // row.find('.item-preco-unitario').val('0.00'); // Não limpar se estiver editando
                row.find('.descricao-servico-manual').prop('required', true);
            } else {
                row.find('.campo-peca').hide();
                row.find('.campo-servico').hide();
                row.find('.item-estoque-search').prop('required', false);
                row.find('.descricao-servico-manual').prop('required', false);
            }
            calcularTotaisOrcamentoEdit();
        }

        $('#itens-orcamento-container-edit').on('change', '.tipo-item-select', function() {
            toggleItemFieldsEdit(this);
        });

        $('#itens-orcamento-container-edit .item-orcamento-row').each(function(idx, el) {
            let select = $(el).find('.tipo-item-select');
            toggleItemFieldsEdit(select);
            initializeAutocompleteEstoqueEdit($(el).find('.item-estoque-search'));
        });

        function initializeAutocompleteEstoqueEdit(element) {
            if (edicaoApenasCliente && !element.is(':focus')) { // Não inicializa nem foca se for edição apenas de cliente, a menos que já esteja focado
                element.prop('readonly', true).addClass('readonly-look');
                return;
            }
            element.autocomplete({
                source: "{{ route('estoque.autocomplete') }}",
                minLength: 1,
                select: function(event, ui) {
                    var row = $(this).closest('.item-orcamento-row');
                    row.find('.item-estoque-search').val(ui.item.value);
                    row.find('.item-estoque-id').val(ui.item.id);
                    row.find('.item-preco-unitario').val(ui.item.preco_venda);
                    calcularTotaisOrcamentoEdit();
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item && $(this).val() !== '') { // Se o valor foi alterado e não é um item válido
                        $(this).closest('.item-orcamento-row').find('.item-estoque-id').val('');
                    }
                    // Não limpar o nome ou preço se não selecionar, pode ser peça avulsa
                    calcularTotaisOrcamentoEdit();
                }
            });
        }

        function calcularTotaisOrcamentoEdit() {
            let totalServicos = 0;
            let totalPecas = 0;

            $('#itens-orcamento-container-edit .item-orcamento-row').each(function() {
                let tipo = $(this).find('.tipo-item-select').val();
                let quantidade = parseFloat($(this).find('.item-quantidade').val()) || 0;
                let valorUnitario = parseFloat($(this).find('.item-preco-unitario').val()) || 0;
                let subtotalItem = quantidade * valorUnitario;
                $(this).find('.item-subtotal-display').text(subtotalItem.toFixed(2));

                if (tipo === 'servico') {
                    totalServicos += subtotalItem;
                } else if (tipo === 'peca') {
                    totalPecas += subtotalItem;
                }
            });

            $('#valor_total_servicos_display_edit').val(totalServicos.toFixed(2));
            $('#valor_total_pecas_display_edit').val(totalPecas.toFixed(2));
            let subTotalOrcamento = totalServicos + totalPecas;
            $('#sub_total_display_edit').val(subTotalOrcamento.toFixed(2));

            let descontoTipo = $('#desconto_tipo_edit').val();
            let descontoValorInput = parseFloat($('#desconto_valor_edit').val()) || 0;
            let valorFinal = subTotalOrcamento;
            let descontoCalculado = 0;

            if (descontoValorInput > 0 && descontoTipo && subTotalOrcamento > 0) {
                if (descontoTipo === 'percentual') {
                    descontoCalculado = (subTotalOrcamento * descontoValorInput) / 100;
                    $('#desconto_info_edit').text(descontoValorInput.toFixed(0) + '% de R$ ' + subTotalOrcamento.toFixed(2) + ' = R$ ' + descontoCalculado.toFixed(2));
                } else if (descontoTipo === 'fixo') {
                    descontoCalculado = descontoValorInput;
                    $('#desconto_info_edit').text('Desconto fixo de R$ ' + descontoCalculado.toFixed(2));
                }
                descontoCalculado = Math.min(descontoCalculado, subTotalOrcamento); // Desconto não pode ser maior que subtotal
            } else {
                $('#desconto_info_edit').text('');
            }
            valorFinal = subTotalOrcamento - descontoCalculado;
            valorFinal = Math.max(0, valorFinal);
            $('#valor_final_display_edit').val(valorFinal.toFixed(2));
        }

        $('#itens-orcamento-container-edit').on('input', '.item-quantidade, .item-preco-unitario', calcularTotaisOrcamentoEdit);
        $('#desconto_tipo_edit, #desconto_valor_edit').on('input change', calcularTotaisOrcamentoEdit);
        calcularTotaisOrcamentoEdit();


        // Lógica do Modal de Novo Cliente
        $('#formNovoCliente').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $('#btnSalvarNovoCliente').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Salvando...');
            $.ajax({
                url: "{{ route('clientes.store') }}",
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.cliente) {
                        var cliente = response.cliente;
                        $('#cliente_search_orcamento_edit').val(cliente.nome_completo);
                        $('#cliente_id_orcamento_edit').val(cliente.id);
                        toggleClienteAvulsoEdit();
                        $('#modalNovoCliente').modal('hide');
                        $('#formNovoCliente')[0].reset();
                        $('#formNovoCliente .invalid-feedback').remove();
                        $('#formNovoCliente .is-invalid').removeClass('is-invalid');
                    } else {
                        alert(response.message || 'Ocorreu um erro.');
                        if (response.errors) {
                            $('#formNovoCliente .invalid-feedback').remove();
                            $('#formNovoCliente .is-invalid').removeClass('is-invalid');
                            $.each(response.errors, function(key, value) {
                                $('#modal_' + key).addClass('is-invalid').after('<div class="invalid-feedback d-block">' + value[0] + '</div>');
                            });
                        }
                    }
                },
                error: function(xhr) {
                    alert('Erro ao conectar com o servidor.');
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        $('#formNovoCliente .invalid-feedback').remove();
                        $('#formNovoCliente .is-invalid').removeClass('is-invalid');
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            $('#modal_' + key).addClass('is-invalid').after('<div class="invalid-feedback d-block">' + value[0] + '</div>');
                        });
                    }
                },
                complete: function() {
                    $('#btnSalvarNovoCliente').prop('disabled', false).html('Salvar Cliente');
                }
            });
        });
        if ($.fn.mask) {
            var CpfCnpjMaskBehaviorModal = function(val) {
                    return val.replace(/\D/g, '').length <= 11 ? '000.000.000-009' : '00.000.000/0000-00';
                },
                cpfCnpjOptionsModal = {
                    onKeyPress: function(val, e, field, options) {
                        field.mask(CpfCnpjMaskBehaviorModal.apply({}, arguments), options);
                    }
                };
            $('#modal_cpf_cnpj').mask(CpfCnpjMaskBehaviorModal, cpfCnpjOptionsModal);
            var SPMaskBehaviorModal = function(val) {
                    return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
                },
                spOptionsModal = {
                    onKeyPress: function(val, e, field, options) {
                        field.mask(SPMaskBehaviorModal.apply({}, arguments), options);
                    }
                };
            $('#modal_telefone').mask(SPMaskBehaviorModal, spOptionsModal);
            $('#modal_cep').mask('00000-000');
        }
        $('#modal_cep').on('blur', function() {
            var cep = $(this).val().replace(/\D/g, '');
            if (cep.length === 8) {
                $('#formNovoCliente').find('#endereco_modal_fields').slideDown();
                $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {
                    if (!("erro" in dados)) {
                        $('#modal_logradouro').val(dados.logradouro);
                        $('#modal_bairro').val(dados.bairro);
                        $('#modal_cidade').val(dados.localidade);
                        $('#modal_estado').val(dados.uf);
                        $('#modal_numero').focus();
                    } else {
                        alert("CEP (Modal) não encontrado.");
                    }
                });
            }
        });
        $('#modalNovoCliente').on('hidden.bs.modal', function() {
            $('#formNovoCliente')[0].reset();
            $('#formNovoCliente .is-invalid').removeClass('is-invalid');
            $('#formNovoCliente .invalid-feedback').remove();
            $('#formNovoCliente').find('#endereco_modal_fields').hide();
        });

    });
</script>
@endpush
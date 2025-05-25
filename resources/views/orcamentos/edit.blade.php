@extends('layouts.app')

@section('title', 'Editar Orçamento #' . $orcamento->id)

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .item-orcamento-row {
            /* Estilos já definidos no _item_orcamento_template.blade.php */
        }
        .readonly-look {
            background-color: #e9ecef;
            opacity: 0.8; /* Um pouco mais de opacidade para indicar que não está totalmente desabilitado */
        }
        .disabled-card-section .form-control,
        .disabled-card-section .form-select,
        .disabled-card-section .btn:not([data-bs-toggle="modal"]) { /* Não desabilita botões de modal */
            pointer-events: none;
            opacity: 0.65;
        }
        #info_cliente_selecionado_orcamento_edit { /* Para feedback do autocomplete de cliente */
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><i class="bi bi-pencil-square text-warning"></i> Editar Orçamento #{{ $orcamento->id }}</h1>
        <div>
            <a href="{{ route('orcamentos.show', $orcamento->id) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Ver detalhes do orçamento">
                <i class="bi bi-eye"></i> Detalhes
            </a>
            <a href="{{ route('orcamentos.index') }}" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="Voltar para a lista de orçamentos">
                <i class="bi bi-list-ul"></i> Lista
            </a>
        </div>
    </div>

    {{-- Feedback e Erros --}}
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

        @php $isReadOnlyForm = isset($edicaoApenasCliente) && $edicaoApenasCliente; @endphp

        {{-- Card de Dados do Cliente e Aparelho --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="my-1"><i class="bi bi-person-badge"></i> Dados do Cliente e Aparelho</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="cliente_search_orcamento_edit" class="form-label">Cliente Cadastrado <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text"
                                class="form-control @error('cliente_id') is-invalid @enderror {{ $isReadOnlyForm && !$orcamento->cliente_id ? '' : ($isReadOnlyForm ? 'readonly-look' : '') }}"
                                id="cliente_search_orcamento_edit"
                                name="cliente_search_orcamento_display"
                                placeholder="Digite nome ou CPF/CNPJ para buscar..."
                                value="{{ old('cliente_search_orcamento_display', optional($orcamento->cliente)->nome_completo ?? ($orcamento->nome_cliente_avulso && !$orcamento->cliente_id ? $orcamento->nome_cliente_avulso : '' ) ) }}"
                                {{ $isReadOnlyForm && $orcamento->cliente_id ? 'readonly' : '' }}>
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#modalNovoCliente" {{ $isReadOnlyForm ? 'disabled' : '' }}>
                                <i class="bi bi-person-plus-fill"></i> Novo
                            </button>
                        </div>
                        <input type="hidden" id="cliente_id_orcamento_edit" name="cliente_id" value="{{ old('cliente_id', $orcamento->cliente_id ?? '') }}">
                        @error('cliente_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div id="info_cliente_selecionado_orcamento_edit" class="info-selecao" style="{{ old('cliente_id', $orcamento->cliente_id) ? '' : 'display: none;' }}">
                            @if($orcamento->cliente)
                                <strong>ID:</strong> {{ $orcamento->cliente->id }}
                                @if($orcamento->cliente->telefone) | <strong>Tel:</strong> {{ $orcamento->cliente->telefone }} @endif
                                @if($orcamento->cliente->email) | <strong>Email:</strong> {{ $orcamento->cliente->email }} @endif
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row px-3" @if($isReadOnlyForm && $orcamento->cliente_id) style="display:none;" @endif>
                    <div class="col-md-7 mb-3">
                        <label for="nome_cliente_avulso_orcamento_edit" class="form-label">Ou Nome do Cliente (Avulso)
                            <span id="span_nome_avulso_req_edit" class="text-danger" style="{{ old('cliente_id', $orcamento->cliente_id) ? 'display:none;' : '' }}">*</span>
                        </label>
                        <input type="text" class="form-control @error('nome_cliente_avulso') is-invalid @enderror {{ $isReadOnlyForm || $orcamento->cliente_id ? 'readonly-look' : '' }}"
                            id="nome_cliente_avulso_orcamento_edit" name="nome_cliente_avulso"
                            value="{{ old('nome_cliente_avulso', $orcamento->nome_cliente_avulso ?? '') }}"
                            {{ $isReadOnlyForm || $orcamento->cliente_id ? 'readonly' : '' }}>
                        @error('nome_cliente_avulso') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="telefone_cliente_avulso_orcamento_edit" class="form-label">Telefone (Avulso)</label>
                        <input type="text" class="form-control {{ $isReadOnlyForm || $orcamento->cliente_id ? 'readonly-look' : '' }}"
                            id="telefone_cliente_avulso_orcamento_edit" name="telefone_cliente_avulso"
                            value="{{ old('telefone_cliente_avulso', $orcamento->telefone_cliente_avulso ?? '') }}"
                             {{ $isReadOnlyForm || $orcamento->cliente_id ? 'readonly' : '' }}>
                    </div>
                </div>
                <div class="mb-3 px-3" @if($isReadOnlyForm && $orcamento->cliente_id) style="display:none;" @endif>
                    <label for="email_cliente_avulso_orcamento_edit" class="form-label">Email (Avulso)</label>
                    <input type="email" class="form-control {{ $isReadOnlyForm || $orcamento->cliente_id ? 'readonly-look' : '' }}"
                        id="email_cliente_avulso_orcamento_edit" name="email_cliente_avulso"
                        value="{{ old('email_cliente_avulso', $orcamento->email_cliente_avulso ?? '') }}"
                         {{ $isReadOnlyForm || $orcamento->cliente_id ? 'readonly' : '' }}>
                </div>
                <hr class="@if($isReadOnlyForm && $orcamento->cliente_id) d-none @endif">
                <div class="@if($isReadOnlyForm) disabled-card-section @endif px-3">
                    <div class="mb-3">
                        <label for="descricao_aparelho_edit" class="form-label">Descrição do Aparelho <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('descricao_aparelho') is-invalid @enderror" id="descricao_aparelho_edit" name="descricao_aparelho" value="{{ old('descricao_aparelho', $orcamento->descricao_aparelho) }}" required>
                        @error('descricao_aparelho') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="problema_relatado_cliente_edit" class="form-label">Problema Relatado <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('problema_relatado_cliente') is-invalid @enderror" id="problema_relatado_cliente_edit" name="problema_relatado_cliente" rows="3" required>{{ old('problema_relatado_cliente', $orcamento->problema_relatado_cliente) }}</textarea>
                        @error('problema_relatado_cliente') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Card de Itens do Orçamento --}}
        <div class="card shadow-sm mb-4 @if($isReadOnlyForm) disabled-card-section @endif">
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
                                // Lógica para buscar nome da peça se 'nome_peca_search' não estiver no old e estoque_id estiver
                                if (($itemDataOld['tipo_item'] ?? '') == 'peca' && !empty($itemDataOld['estoque_id']) && empty($itemDataOld['nome_peca_search'])) {
                                    $pecaDoOld = App\Models\Estoque::find($itemDataOld['estoque_id']);
                                    if ($pecaDoOld) {
                                        $itemDataOld['nome_peca_search'] = $pecaDoOld->nome . ($pecaDoOld->modelo_compativel ? ' (' . $pecaDoOld->modelo_compativel . ')' : '');
                                        // Para popular a div de info, precisaríamos de mais dados da peça no old ou buscar via JS
                                        $itemDataOld['estoque'] = $pecaDoOld->toArray(); // Passa dados da peça para o template
                                    }
                                }
                            @endphp
                            @include('orcamentos._item_orcamento_template', [
                                'index' => $index,
                                'itemData' => $itemDataOld,
                                'edicaoApenasCliente' => $isReadOnlyForm
                            ])
                        @endforeach
                    @endif
                </div>
                <button type="button" class="btn btn-secondary btn-sm mt-3" id="adicionar-item-orcamento-edit">
                    <i class="bi bi-plus-circle"></i> Adicionar Item
                </button>
                @error('itens') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Card de Valores e Condições --}}
        <div class="card shadow-sm mb-4 @if($isReadOnlyForm) disabled-card-section @endif">
            <div class="card-header">
                <h5 class="my-1"><i class="bi bi-calculator-fill"></i> Valores e Condições</h5>
            </div>
            <div class="card-body">
                {{-- Status --}}
                 <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="status_edit" class="form-label">Status do Orçamento <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status_edit" name="status" required>
                            @foreach ($statusOrcamentoSelect as $statusValue)
                            <option value="{{ $statusValue }}" {{ old('status', $orcamento->status) == $statusValue ? 'selected' : '' }}>
                                {{ $statusValue }}
                            </option>
                            @endforeach
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                 </div>
                 <hr>
                {{-- Linha de Totais de Serviço e Peças --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="valor_total_servicos_display_edit" class="form-label">Total Serviços (R$)</label>
                        <input type="text" class="form-control readonly-look" id="valor_total_servicos_display_edit" readonly value="{{ number_format(old('valor_total_servicos_calc', $orcamento->valor_total_servicos), 2, ',', '.') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="valor_total_pecas_display_edit" class="form-label">Total Peças (R$)</label>
                        <input type="text" class="form-control readonly-look" id="valor_total_pecas_display_edit" readonly value="{{ number_format(old('valor_total_pecas_calc', $orcamento->valor_total_pecas), 2, ',', '.') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="sub_total_display_edit" class="form-label">Subtotal (R$)</label>
                        <input type="text" class="form-control readonly-look" id="sub_total_display_edit" readonly value="{{ number_format(old('sub_total_calc', $orcamento->sub_total), 2, ',', '.') }}">
                    </div>
                </div>
                {{-- Linha de Desconto e Valor Final --}}
                <div class="row align-items-end">
                    <div class="col-md-4 mb-3">
                        <label for="desconto_tipo_edit" class="form-label">Tipo de Desconto</label>
                        <select class="form-select @error('desconto_tipo') is-invalid @enderror" id="desconto_tipo_edit" name="desconto_tipo">
                            <option value="">Sem Desconto</option>
                            @foreach ($tiposDesconto as $key => $value)
                            <option value="{{ $key }}" {{ old('desconto_tipo', $orcamento->desconto_tipo) == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                            @endforeach
                        </select>
                        @error('desconto_tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="desconto_valor_edit" class="form-label">Valor do Desconto</label>
                        <input type="number" step="0.01" class="form-control @error('desconto_valor') is-invalid @enderror" id="desconto_valor_edit" name="desconto_valor" value="{{ old('desconto_valor', number_format($orcamento->desconto_valor ?? 0, 2, '.', '')) }}" min="0">
                        <small id="desconto_info_edit" class="form-text text-muted"></small>
                        @error('desconto_valor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="valor_final_display_edit" class="form-label fw-bold">VALOR FINAL (R$)</label>
                        <input type="text" class="form-control fw-bold readonly-look" id="valor_final_display_edit" readonly value="{{ number_format(old('valor_final_calc', $orcamento->valor_final), 2, ',', '.') }}" style="font-size: 1.2rem; color: #198754;">
                    </div>
                </div>
                <hr>
                {{-- Linha de Datas e Tempo Estimado --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="data_emissao_edit" class="form-label">Data de Emissão <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('data_emissao') is-invalid @enderror" id="data_emissao_edit" name="data_emissao" value="{{ old('data_emissao', $orcamento->data_emissao->format('Y-m-d')) }}" required>
                         @error('data_emissao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="validade_dias_edit" class="form-label">Validade (dias)</label>
                        <input type="number" class="form-control" id="validade_dias_edit" name="validade_dias" value="{{ old('validade_dias', $orcamento->validade_dias ?? 7) }}" min="0">
                        <small class="form-text text-muted">0 ou vazio para sem validade.</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tempo_estimado_servico_edit" class="form-label">Tempo Estimado de Serviço</label>
                        <input type="text" class="form-control" id="tempo_estimado_servico_edit" name="tempo_estimado_servico" value="{{ old('tempo_estimado_servico', $orcamento->tempo_estimado_servico) }}" placeholder="Ex: 2 dias úteis, 24 horas">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="termos_condicoes_edit" class="form-label">Termos e Condições</label>
                    <textarea class="form-control" id="termos_condicoes_edit" name="termos_condicoes" rows="4">{{ old('termos_condicoes', $orcamento->termos_condicoes) }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="observacoes_internas_edit" class="form-label">Observações Internas</label>
                    <textarea class="form-control" id="observacoes_internas_edit" name="observacoes_internas" rows="2">{{ old('observacoes_internas', $orcamento->observacoes_internas) }}</textarea>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-end">
            <a href="{{ route('orcamentos.show', $orcamento->id) }}" class="btn btn-secondary me-2">Cancelar</a>
            <button type="submit" class="btn btn-primary" {{ $isReadOnlyForm && !$orcamento->cliente_id ? 'disabled' : '' }}><i class="bi bi-save2-fill"></i> Salvar Alterações</button>
        </div>
    </form>
</div>

{{-- Template para os itens do orçamento (reutilizado) --}}
<div id="orcamento-item-template-edit" style="display: none;">
    @include('orcamentos._item_orcamento_template', ['index' => '__INDEX__', 'itemData' => [], 'edicaoApenasCliente' => $isReadOnlyForm])
</div>

@include('clientes.partials.modal_create') {{-- Inclui o modal de criar cliente --}}

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // A variável $isReadOnlyForm é injetada do Blade (true se edicaoApenasCliente for true)
    const isReadOnlyForm = @json($isReadOnlyForm ?? false);

    // Função para habilitar/desabilitar campos de cliente avulso e span de obrigatório
    function toggleClienteAvulsoOrcamentoEdit() {
        const clienteIdVal = $('#cliente_id_orcamento_edit').val();
        const camposAvulsos = $('#nome_cliente_avulso_orcamento_edit, #telefone_cliente_avulso_orcamento_edit, #email_cliente_avulso_orcamento_edit');
        const spanReq = $('#span_nome_avulso_req_edit');
        const nomeClienteAvulsoInput = $('#nome_cliente_avulso_orcamento_edit');

        if (clienteIdVal) {
            camposAvulsos.val('').prop('readonly', true).removeClass('is-invalid readonly-look');
            nomeClienteAvulsoInput.prop('required', false);
            spanReq.hide();
            camposAvulsos.siblings('.invalid-feedback').remove();
        } else {
            camposAvulsos.prop('readonly', isReadOnlyForm);
            if (isReadOnlyForm) {
                camposAvulsos.addClass('readonly-look');
            } else {
                camposAvulsos.removeClass('readonly-look');
            }
            nomeClienteAvulsoInput.prop('required', !isReadOnlyForm && !clienteIdVal);
            spanReq.toggle(!isReadOnlyForm && !clienteIdVal);
        }
    }

    function exibirInfoClienteOrcamentoEdit(cliente) {
        var infoDiv = $('#info_cliente_selecionado_orcamento_edit');
        if (cliente && cliente.id) {
            var infoHtml = '<strong>ID:</strong> ' + cliente.id +
                           (cliente.telefone ? ' | <strong>Tel:</strong> ' + cliente.telefone : '') +
                           (cliente.email ? ' | <strong>Email:</strong> ' + cliente.email : '');
            infoDiv.html(infoHtml).show();
        } else {
            infoDiv.hide().html('');
        }
    }

    let clienteSelecionadoPeloAutocompleteOrcEdit = ($('#cliente_id_orcamento_edit').val() !== '');
    if (clienteSelecionadoPeloAutocompleteOrcEdit) {
        // Tenta buscar o objeto cliente completo para popular os detalhes se o nome já estiver preenchido
        // Isso é útil se o `old()` preencheu o nome, mas não temos o objeto cliente completo no JS ainda.
        const initialId = $('#cliente_id_orcamento_edit').val();
        const initialName = $('#cliente_search_orcamento_edit').val();
        if(initialId && initialName){
             $.ajax({
                url: "{{ route('clientes.autocomplete') }}", // Supondo que a busca por ID retorne o objeto
                dataType: "json",
                data: { search: initialId }, // Tenta buscar pelo ID
                success: function(data) {
                    const clienteEncontrado = data.find(c => c.id == initialId);
                    if (clienteEncontrado) {
                        exibirInfoClienteOrcamentoEdit(clienteEncontrado);
                    } else if (initialName) { // Se não achou pelo ID mas tem nome, mostra o que tem
                         exibirInfoClienteOrcamentoEdit({id: initialId }); // Mostra pelo menos o ID
                    }
                }
            });
        }
    }


    $("#cliente_search_orcamento_edit").autocomplete({
        source: "{{ route('clientes.autocomplete') }}",
        minLength: 2,
        disabled: isReadOnlyForm && $('#cliente_id_orcamento_edit').val() !== '',
        select: function(event, ui) {
            if (ui.item && ui.item.id) {
                $('#cliente_search_orcamento_edit').val(ui.item.value);
                $('#cliente_id_orcamento_edit').val(ui.item.id);
                exibirInfoClienteOrcamentoEdit(ui.item); // Passa o objeto completo do autocomplete
                clienteSelecionadoPeloAutocompleteOrcEdit = true;
                toggleClienteAvulsoOrcamentoEdit();
                $('#cliente_id_orcamento_edit').removeClass('is-invalid').siblings('.invalid-feedback').remove();
                $('#nome_cliente_avulso_orcamento_edit').removeClass('is-invalid').siblings('.invalid-feedback').remove();
                if (isReadOnlyForm && $('#cliente_id_orcamento_edit').val() !== '') {
                    $('button[type="submit"]').prop('disabled', false);
                }
            }
            return false;
        },
        change: function(event, ui) {
            if (!clienteSelecionadoPeloAutocompleteOrcEdit && !ui.item && $('#cliente_search_orcamento_edit').val() !== '') {
                $('#cliente_id_orcamento_edit').val('');
                exibirInfoClienteOrcamentoEdit(null);
            }
            toggleClienteAvulsoOrcamentoEdit();
            if (isReadOnlyForm && $('#cliente_id_orcamento_edit').val() === '') {
                $('button[type="submit"]').prop('disabled', true);
            }
        }
    });

    $("#cliente_search_orcamento_edit").on('input', function() {
        if ($(this).is('[readonly]')) return;
        if ($(this).val() === '') {
            $('#cliente_id_orcamento_edit').val('');
            exibirInfoClienteOrcamentoEdit(null);
            clienteSelecionadoPeloAutocompleteOrcEdit = false;
        } else if (!clienteSelecionadoPeloAutocompleteOrcEdit) {
            $('#cliente_id_orcamento_edit').val('');
        }
        toggleClienteAvulsoOrcamentoEdit();
        if (isReadOnlyForm && $('#cliente_id_orcamento_edit').val() === '') {
            $('button[type="submit"]').prop('disabled', true);
        } else if (isReadOnlyForm) { // Se modo restrito mas cliente_id foi preenchido
            $('button[type="submit"]').prop('disabled', false);
        }
    });

    // Lógica do Modal de Novo Cliente
    $('#formNovoCliente').on('submit', function(e) {
        e.preventDefault();
        if (isReadOnlyForm && $('#cliente_id_orcamento_edit').val() !== '') { // Previne modal se já tem cliente e está em modo restrito
             alert('Não é possível adicionar um novo cliente neste modo de edição restrita quando um cliente já está vinculado.');
             return;
        }
        var formData = $(this).serialize();
        var btnSalvar = $('#btnSalvarNovoCliente');
        var originalText = btnSalvar.html();
        btnSalvar.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Salvando...');
        $('#formNovoCliente .invalid-feedback').remove();
        $('#formNovoCliente .is-invalid').removeClass('is-invalid');
        $.ajax({
            url: "{{ route('clientes.store') }}", type: 'POST', data: formData, dataType: 'json',
            success: function(response) {
                if (response.success && response.cliente) {
                    var cliente = response.cliente;
                    $('#cliente_search_orcamento_edit').val(cliente.nome_completo);
                    $('#cliente_id_orcamento_edit').val(cliente.id);
                    exibirInfoClienteOrcamentoEdit(cliente);
                    clienteSelecionadoPeloAutocompleteOrcEdit = true;
                    toggleClienteAvulsoOrcamentoEdit();
                    $('#modalNovoCliente').modal('hide');
                    $('#formNovoCliente')[0].reset();
                    if (isReadOnlyForm) {
                        $('button[type="submit"]').prop('disabled', false);
                    }
                } else {
                    if (response.errors) {
                        $.each(response.errors, function(key, value) {
                            $('#modal_' + key).addClass('is-invalid').closest('.mb-3').append('<div class="invalid-feedback d-block">' + value[0] + '</div>');
                        });
                    }
                }
            },
            error: function(xhr) {
                 if (xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        $('#modal_' + key).addClass('is-invalid').closest('.mb-3').append('<div class="invalid-feedback d-block">' + value[0] + '</div>');
                    });
                } else { alert('Erro desconhecido ao salvar cliente.');}
            },
            complete: function() { btnSalvar.prop('disabled', false).html(originalText); }
        });
    });
    // Máscaras e ViaCEP do modal
    if ($.fn.mask) {
        var CpfCnpjMaskBehaviorModal = function(val) { return val.replace(/\D/g, '').length <= 11 ? '000.000.000-009' : '00.000.000/0000-00'; },
            cpfCnpjOptionsModal = { onKeyPress: function(val, e, field, options) { field.mask(CpfCnpjMaskBehaviorModal.apply({}, arguments), options); } };
        $('#modal_cpf_cnpj').mask(CpfCnpjMaskBehaviorModal, cpfCnpjOptionsModal);
        var SPMaskBehaviorModal = function(val) { return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009'; },
            spOptionsModal = { onKeyPress: function(val, e, field, options) { field.mask(SPMaskBehaviorModal.apply({}, arguments), options); } };
        $('#modal_telefone').mask(SPMaskBehaviorModal, spOptionsModal);
        $('#telefone_cliente_avulso_orcamento_edit').mask(SPMaskBehaviorModal, spOptionsModal);
        $('#modal_cep').mask('00000-000');
    }
    $('#modal_cep').on('blur', function() {
        var cep = $(this).val().replace(/\D/g, '');
        var formDoModal = $(this).closest('form');
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

    // ----- Lógica para Itens do Orçamento (para EDICAO) -----
    let itemOrcamentoIndexEdit = 0; // Será atualizado pelo loop Blade que renderiza os itens existentes

    function adicionarItemOrcamentoEditComDados(itemData = null, isInitialPopulation = false) {
        if (isReadOnlyForm && !isInitialPopulation) return; // Não adiciona novas linhas se for readonly, a menos que seja a população inicial
        let templateHTML = $('#orcamento-item-template-edit').html().replace(/__INDEX__/g, itemOrcamentoIndexEdit);
        $('#itens-orcamento-container-edit').append(templateHTML);
        let novaLinha = $('#itens-orcamento-container-edit .item-orcamento-row[data-index="' + itemOrcamentoIndexEdit + '"]');

        if (itemData) {
            novaLinha.find('input[name$="[id]"]').val(itemData.id || '');
            let tipoItem = itemData.tipo_item || 'servico';
            novaLinha.find('.tipo-item-select').val(tipoItem);
            // Chamada explícita do toggle após definir o valor
            toggleItemFieldsOrcEdit(novaLinha.find('.tipo-item-select').get(0));

            if (tipoItem === 'peca') {
                novaLinha.find('.item-estoque-search').val(itemData.nome_peca_search || '');
                novaLinha.find('.item-estoque-id').val(itemData.estoque_id || '');
                var infoPecaDiv = novaLinha.find('div[id^="info_peca_orc_"]');
                var estoqueInfo = itemData.estoque; // Espera que 'estoque' seja um objeto com os dados da peça
                if (estoqueInfo && typeof estoqueInfo.id !== 'undefined') {
                     var tipoPecaFormatado = estoqueInfo.tipo_formatado || estoqueInfo.tipo_original || estoqueInfo.tipo || 'N/D';
                     var qtdDisp = typeof estoqueInfo.quantidade_disponivel !== 'undefined' ? estoqueInfo.quantidade_disponivel : (estoqueInfo.quantidade || 'N/A');
                     infoPecaDiv.html('<i class="bi bi-info-circle"></i> Qtd. Disp.: ' + qtdDisp + ' | Tipo: ' + tipoPecaFormatado).show();
                } else if (itemData.estoque_id && itemData.nome_peca_search){
                     infoPecaDiv.html('<i class="bi bi-info-circle"></i> Detalhes da peça serão carregados.').show();
                }
            } else {
                novaLinha.find('.descricao-servico-manual').val(itemData.descricao_item_manual || '');
            }
            novaLinha.find('.item-quantidade').val(itemData.quantidade || 1);
            let valorUnitarioOld = String(itemData.valor_unitario || '0.00').replace(',', '.');
            novaLinha.find('.item-preco-unitario').val(parseFloat(valorUnitarioOld).toFixed(2));
        } else { // Linha nova (não de 'old' nem de $orcamento->itens)
            toggleItemFieldsOrcEdit(novaLinha.find('.tipo-item-select').get(0));
        }

        initializeAutocompleteEstoqueOrcEdit(novaLinha.find('.item-estoque-search'));
        itemOrcamentoIndexEdit++;
        // calcularTotaisOrcamentoEdit(); // O cálculo será chamado ao modificar campos de qtd/valor
    }

    $('#adicionar-item-orcamento-edit').on('click', function() {
        if (isReadOnlyForm) return;
        adicionarItemOrcamentoEditComDados();
    });

    $('#itens-orcamento-container-edit').on('click', '.remover-item-orcamento', function() {
        if (isReadOnlyForm) return;
        $(this).closest('.item-orcamento-row').remove();
        calcularTotaisOrcamentoEdit();
        // Não resetar itemOrcamentoIndexEdit aqui, pois os índices dos itens restantes não mudam
        if ($('#itens-orcamento-container-edit .item-orcamento-row').length === 0) {
            // Se quiser adicionar uma linha nova automaticamente se tudo for removido (e não for readonly):
            // if (!isReadOnlyForm) adicionarItemOrcamentoEditComDados();
        }
    });

    function toggleItemFieldsOrcEdit(selectElement) {
        var row = $(selectElement).closest('.item-orcamento-row');
        var tipo = $(selectElement).val();
        var infoPecaDiv = row.find('div[id^="info_peca_orc_"]');
        const camposItem = row.find('.item-estoque-search, .descricao-servico-manual, .item-quantidade, .item-preco-unitario');
        const selectTipoItem = row.find('.tipo-item-select');
        const btnRemoverItem = row.find('.remover-item-orcamento');

        if (isReadOnlyForm) {
            camposItem.prop('readonly', true).addClass('readonly-look');
            selectTipoItem.prop('disabled', true).addClass('readonly-look');
            btnRemoverItem.prop('disabled', true);
        } else {
            camposItem.prop('readonly', false).removeClass('readonly-look');
            selectTipoItem.prop('disabled', false).removeClass('readonly-look');
            btnRemoverItem.prop('disabled', false);
        }

        if (tipo === 'peca') {
            row.find('.campo-peca').show();
            row.find('.campo-servico').hide();
            row.find('.item-estoque-search').prop('required', !isReadOnlyForm);
            row.find('.descricao-servico-manual').prop('required', false).val('');
            if(row.find('.item-estoque-id').val() || row.find('.item-estoque-search').val()){ infoPecaDiv.show(); }
            else { infoPecaDiv.hide().html(''); }
        } else if (tipo === 'servico') {
            row.find('.campo-peca').hide();
            row.find('.campo-servico').show();
            row.find('.item-estoque-search').prop('required', false).val('');
            row.find('.item-estoque-id').val('');
            row.find('.descricao-servico-manual').prop('required', !isReadOnlyForm);
            infoPecaDiv.hide().html('');
        } else {
            row.find('.campo-peca').hide(); row.find('.campo-servico').hide();
            row.find('.item-estoque-search').prop('required', false);
            row.find('.descricao-servico-manual').prop('required', false);
            infoPecaDiv.hide().html('');
        }
    }

    $('#itens-orcamento-container-edit').on('change', '.tipo-item-select', function() {
        toggleItemFieldsOrcEdit(this);
        calcularTotaisOrcamentoEdit();
    });

    // Inicializar itens existentes do orçamento (renderizados pelo Blade com old() ou $orcamento->itens)
    $('#itens-orcamento-container-edit .item-orcamento-row').each(function(index) {
        itemOrcamentoIndexEdit = index +1; // Atualiza o contador global de índice
        initializeAutocompleteEstoqueOrcEdit($(this).find('.item-estoque-search'));
        toggleItemFieldsOrcEdit($(this).find('.tipo-item-select').get(0));
    });

    // Adiciona um item inicial se não houver itens (e não for readonly)
    if (itemOrcamentoIndexEdit === 0 && !isReadOnlyForm && $('#itens-orcamento-container-edit .item-orcamento-row').length === 0) {
        adicionarItemOrcamentoEditComDados(null, false); // Passa false para isInitialPopulation
    }
    calcularTotaisOrcamentoEdit(); // Calcula totais na carga


    function initializeAutocompleteEstoqueOrcEdit(element) {
        if (isReadOnlyForm && !element.is(':focus')) {
            element.prop('readonly', true).addClass('readonly-look');
            return;
        }
        element.autocomplete({
            source: function(request, response) {
                 $.ajax({
                    url: "{{ route('estoque.autocomplete') }}", dataType: "json",
                    data: { search: request.term }, success: function(data) { response(data); }
                });
            },
            minLength: 1,
            select: function(event, ui) {
                var row = $(this).closest('.item-orcamento-row');
                row.find('.item-estoque-search').val(ui.item.value);
                row.find('.item-estoque-id').val(ui.item.id);
                row.find('.item-preco-unitario').val(ui.item.preco_venda).trigger('input');
                var infoDivPeca = row.find('div[id^="info_peca_orc_"]');
                var tipoPecaFormatado = ui.item.tipo_formatado || ui.item.tipo_original || 'N/D';
                var infoPecaHtml = '<i class="bi bi-info-circle"></i> Qtd. Disp.: ' + ui.item.quantidade_disponivel + ' | Tipo: ' + tipoPecaFormatado;
                infoDivPeca.html(infoPecaHtml).show();
                return false;
            },
            change: function(event, ui) {
                var row = $(this).closest('.item-orcamento-row');
                var infoDivPeca = row.find('div[id^="info_peca_orc_"]');
                if (!ui.item) {
                    row.find('.item-estoque-id').val('');
                    if ($(this).val() === '') { infoDivPeca.hide().html(''); }
                } else {
                     var tipoPecaFormatado = ui.item.tipo_formatado || ui.item.tipo_original || 'N/D';
                     var infoPecaHtml = '<i class="bi bi-info-circle"></i> Qtd. Disp.: ' + ui.item.quantidade_disponivel + ' | Tipo: ' + tipoPecaFormatado;
                     infoDivPeca.html(infoPecaHtml).show();
                }
                calcularTotaisOrcamentoEdit();
            }
        });
    }

    function calcularTotaisOrcamentoEdit() {
        let totalServicos = 0; let totalPecas = 0;
        $('#itens-orcamento-container-edit .item-orcamento-row').each(function() {
            let tipo = $(this).find('.tipo-item-select').val();
            let quantidade = parseFloat(String($(this).find('.item-quantidade').val()).replace(',', '.')) || 0;
            let valorUnitario = parseFloat(String($(this).find('.item-preco-unitario').val()).replace(',', '.')) || 0;
            let subtotalItem = quantidade * valorUnitario;
            $(this).find('.item-subtotal-display').text(subtotalItem.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            if (tipo === 'servico') totalServicos += subtotalItem;
            else if (tipo === 'peca') totalPecas += subtotalItem;
        });
        $('#valor_total_servicos_display_edit').val(totalServicos.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#valor_total_pecas_display_edit').val(totalPecas.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        let subTotalOrcamento = totalServicos + totalPecas;
        $('#sub_total_display_edit').val(subTotalOrcamento.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        let descontoTipo = $('#desconto_tipo_edit').val();
        let descontoValorInput = parseFloat(String($('#desconto_valor_edit').val()).replace(',', '.')) || 0;
        let valorFinal = subTotalOrcamento; let descontoCalculado = 0;
        if (descontoValorInput > 0 && descontoTipo && subTotalOrcamento > 0) {
            if (descontoTipo === 'percentual') {
                descontoCalculado = (subTotalOrcamento * descontoValorInput) / 100;
                $('#desconto_info_edit').text(descontoValorInput.toLocaleString('pt-BR') + '% de R$ ' + subTotalOrcamento.toLocaleString('pt-BR', {minimumFractionDigits:2,maximumFractionDigits:2}) + ' = R$ ' + descontoCalculado.toLocaleString('pt-BR', {minimumFractionDigits:2,maximumFractionDigits:2}));
            } else if (descontoTipo === 'fixo') {
                descontoCalculado = descontoValorInput;
                $('#desconto_info_edit').text('Desconto fixo de R$ ' + descontoCalculado.toLocaleString('pt-BR', {minimumFractionDigits:2,maximumFractionDigits:2}));
            }
            descontoCalculado = Math.min(descontoCalculado, subTotalOrcamento);
        } else { $('#desconto_info_edit').text(''); }
        valorFinal = subTotalOrcamento - descontoCalculado;
        valorFinal = Math.max(0, valorFinal);
        $('#valor_final_display_edit').val(valorFinal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }

    $('#itens-orcamento-container-edit').on('input', '.item-quantidade, .item-preco-unitario', calcularTotaisOrcamentoEdit);
    $('#desconto_tipo_edit, #desconto_valor_edit').on('input change', calcularTotaisOrcamentoEdit);

    // Inicializa estado e cálculos
    toggleClienteAvulsoOrcamentoEdit();
    // O cálculo inicial de totais e a adição do primeiro item (se necessário)
    // são tratados pela lógica de inicialização dos itens existentes.

    // Lógica para desabilitar campos se for edicaoApenasCliente
    if (isReadOnlyForm) {
        $('#formEditarOrcamento').find('input:not([name="_token"]):not([name="_method"]), textarea, select')
            .each(function() {
                const id = $(this).attr('id');
                // Permite edição apenas dos campos de cliente se cliente_id não estiver preenchido
                const isClienteFieldPermitido = ['cliente_search_orcamento_edit', 'cliente_id_orcamento_edit',
                                                 'nome_cliente_avulso_orcamento_edit',
                                                 'telefone_cliente_avulso_orcamento_edit',
                                                 'email_cliente_avulso_orcamento_edit'].includes(id);

                const isDentroModalNovoCliente = $(this).closest('#modalNovoCliente').length > 0;

                if (!isDentroModalNovoCliente) {
                    if (isReadOnlyForm && $('#cliente_id_orcamento_edit').val() === '' && isClienteFieldPermitido) {
                        $(this).prop('disabled', false).removeClass('readonly-look');
                        if(id === 'cliente_search_orcamento_edit') $(this).prop('readonly', false); // Campo de busca sempre editável
                    } else if (isReadOnlyForm && $('#cliente_id_orcamento_edit').val() !== '' && isClienteFieldPermitido && id !== 'cliente_search_orcamento_edit' && id !== 'cliente_id_orcamento_edit' ){
                        // Se já tem cliente e é modo restrito, desabilita campos AVULSOS
                        $(this).prop('disabled', true).addClass('readonly-look');
                    } else if (isReadOnlyForm && !isClienteFieldPermitido) {
                        $(this).prop('disabled', true).addClass('readonly-look');
                    }
                }
            });

        if (isReadOnlyForm) { // Garante que o botão do modal de cliente seja desabilitado se já houver cliente
            $('#btnNovoCliente').prop('disabled', ($('#cliente_id_orcamento_edit').val() !== ''));
        }


        $('#adicionar-item-orcamento-edit').prop('disabled', true);
        // Botão Salvar: desabilitado se modo restrito E já tem cliente, OU se modo restrito E não tem cliente.
        // Deve ser habilitado no modo restrito APENAS quando um cliente é efetivamente selecionado.
        if (isReadOnlyForm) {
            if ($('#cliente_id_orcamento_edit').val() === '') {
                $('button[type="submit"]').prop('disabled', true); // Começa desabilitado se não tem cliente
            } else {
                 $('button[type="submit"]').prop('disabled', false); // Habilitado se já tem cliente
            }
        }
    }

    // Lógica para pré-popular a div de info do cliente na carga da página para edit
    // A variável `initialClienteIdEdit` foi corrigida.
    const initialClienteIdOnEdit = $('#cliente_id_orcamento_edit').val(); // Renomeada para evitar conflito
    const initialClienteNomeDisplayOnEdit = $('#cliente_search_orcamento_edit').val();

    if (initialClienteIdOnEdit && initialClienteNomeDisplayOnEdit) {
        $.ajax({
            url: "{{ route('clientes.autocomplete') }}",
            dataType: "json", data: { search: initialClienteIdOnEdit },
            success: function(data) {
                const clienteEncontrado = data.find(c => c.id == initialClienteIdOnEdit);
                if (clienteEncontrado) {
                    exibirInfoClienteOrcamentoEdit(clienteEncontrado);
                    // clienteSelecionadoPeloAutocompleteOrcEdit = true; // Já deve ter sido setada no topo do script
                }
            }
        });
    }
    toggleClienteAvulsoOrcamentoEdit(); // Chamada final
});
</script>
@endpush
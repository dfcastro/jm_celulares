@extends('layouts.app')

@section('title', 'Editar Atendimento #' . $atendimento->id)

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .ui-autocomplete {
            z-index: 1055 !important;
        }

        .card-title {
            margin-bottom: 1.5rem;
        }

        /* Estilos para campos readonly ou desabilitados */
        .readonly-look {
            background-color: #e9ecef; /* Cinza claro do Bootstrap */
            opacity: 0.75; /* Para dar uma aparência de desabilitado */
            cursor: not-allowed;
        }

        .disabled-card-section .form-control,
        .disabled-card-section .form-select,
        .disabled-card-section .btn:not(.btn-info):not(.btn-secondary) { /* Não desabilita botões de navegação/visualização */
            pointer-events: none; /* Impede cliques e interações */
            opacity: 0.65;
        }
        .disabled-card-section .readonly-look { /* Garante que o estilo readonly-look ainda se aplique */
            background-color: #e9ecef !important;
            opacity: 0.7 !important;
        }

        .bg-light-subtle {
            background-color: var(--bs-light-bg-subtle) !important;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-pencil-square text-warning"></i> Editar Atendimento #{{ $atendimento->id }}</h1>
            <div>
                <a href="{{ route('atendimentos.show', $atendimento->id) }}" class="btn btn-sm btn-info"><i
                        class="bi bi-eye"></i> Ver Detalhes</a>
                <a href="{{ route('atendimentos.index') }}" class="btn btn-sm btn-secondary"><i class="bi bi-list-ul"></i>
                    Voltar para Lista</a>
            </div>
        </div>

        {{-- Mensagens de feedback --}}
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

        @unless($permitirEdicaoItens)
            <div class="alert alert-warning" role="alert">
                <i class="bi bi-lock-fill"></i> <strong>Edição Limitada:</strong> Este atendimento está no status "{{ $atendimento->status }}" e não permite alterações em serviços, peças ou valores.
            </div>
        @endunless

        <form action="{{ route('atendimentos.update', $atendimento->id) }}" method="POST" id="formEditarAtendimento">
            @csrf
            @method('PUT')

            {{-- CARD 1: Dados do Cliente e Aparelho --}}
            <div class="card shadow-sm mb-4 @unless($permitirEdicaoItens) disabled-card-section @endunless">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-person-badge"></i> Cliente e Aparelho</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="cliente_id_edit" class="form-label">Cliente <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('cliente_id') is-invalid @enderror {{ !$permitirEdicaoItens ? 'readonly-look' : '' }}"
                                id="cliente_nome_edit" name="cliente_nome_display_edit"
                                placeholder="Digite nome ou CPF/CNPJ para buscar..."
                                value="{{ old('cliente_nome_display_edit', $atendimento->cliente->nome_completo ?? '') }}"
                                required {{ !$permitirEdicaoItens ? 'readonly' : '' }}>
                            <input type="hidden" id="cliente_id_edit" name="cliente_id"
                                value="{{ old('cliente_id', $atendimento->cliente_id) }}">
                            @error('cliente_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div id="info_cliente_selecionado_edit" class="mt-2 text-muted small"
                                style="{{ old('cliente_id', $atendimento->cliente_id) ? '' : 'display: none;' }}">
                                Telefone: <span
                                    id="cliente_telefone_info_edit">{{ $atendimento->cliente->telefone ?? '' }}</span> |
                                Email: <span id="cliente_email_info_edit">{{ $atendimento->cliente->email ?? '' }}</span>
                            </div>
                             @if($permitirEdicaoItens) {{-- Só mostra o botão de novo cliente se puder editar --}}
                            <small><a href="{{ route('clientes.create', ['redirect_to' => url()->current()]) }}"
                                    target="_blank" class="text-decoration-none mt-1 d-inline-block"><i
                                        class="bi bi-plus-circle"></i> Cadastrar Novo Cliente (Nova Aba)</a>
                            </small>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="descricao_aparelho_edit" class="form-label">Aparelho (Marca/Modelo/Cor) <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('descricao_aparelho') is-invalid @enderror {{ !$permitirEdicaoItens ? 'readonly-look' : '' }}"
                                id="descricao_aparelho_edit" name="descricao_aparelho"
                                value="{{ old('descricao_aparelho', $atendimento->descricao_aparelho) }}" required {{ !$permitirEdicaoItens ? 'readonly' : '' }}>
                            @error('descricao_aparelho') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="problema_relatado_edit" class="form-label">Problema Relatado <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control @error('problema_relatado') is-invalid @enderror {{ !$permitirEdicaoItens ? 'readonly-look' : '' }}"
                            id="problema_relatado_edit" name="problema_relatado" rows="3"
                            required {{ !$permitirEdicaoItens ? 'readonly' : '' }}>{{ old('problema_relatado', $atendimento->problema_relatado) }}</textarea>
                        @error('problema_relatado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- CARD 2: Serviços Detalhados da OS --}}
            <div class="card shadow-sm mb-4 @unless($permitirEdicaoItens) disabled-card-section @endunless">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="my-1"><i class="bi bi-tools"></i> Serviços Detalhados da OS</h5>
                    @if($permitirEdicaoItens)
                        @can('is-admin-or-tecnico')
                            <button type="button" class="btn btn-success btn-sm" id="adicionar-servico-detalhado-os-edit">
                                <i class="bi bi-plus-circle"></i> Adicionar Serviço
                            </button>
                        @endcan
                    @else
                         <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle rounded-pill p-2">
                            <i class="bi bi-lock-fill me-1"></i> Edição bloqueada
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    <div id="servicos-detalhados-container-edit">
                        @php
                            $servicosParaIterar = old('servicos_detalhados', $atendimento->servicosDetalhados->toArray() ?: []);
                        @endphp
                        @if(!empty($servicosParaIterar))
                            @foreach($servicosParaIterar as $index => $itemServicoData)
                                @php if (is_object($itemServicoData)) $itemServicoData = (array)$itemServicoData; @endphp
                                @include('atendimentos.partials._item_servico_template', [
                                    'index' => $index,
                                    'itemServicoData' => $itemServicoData,
                                    'isReadOnly' => !$permitirEdicaoItens // Passa a flag para o template
                                ])
                            @endforeach
                        @else
                            <p id="nenhum-servico-mensagem-edit" class="text-muted text-center small py-2">Nenhum serviço
                                detalhado adicionado.</p>
                        @endif
                    </div>
                    @error('servicos_detalhados') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- CARD 3: Status, Prazos e Técnico --}}
            {{-- A edição de status e técnico é geralmente permitida mesmo se os itens estão bloqueados --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-ui-checks-grid"></i> Status, Prazos e Técnico</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="data_entrada_display_edit" class="form-label">Data de Entrada (Registrada)</label>
                            <input type="text" class="form-control readonly-look" id="data_entrada_display_edit"
                                value="{{ $atendimento->data_entrada ? $atendimento->data_entrada->format('d/m/Y H:i:s') : 'N/A' }}"
                                readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status_edit" class="form-label">Status do Serviço <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status_edit" name="status"
                                required>
                                @foreach (App\Models\Atendimento::getPossibleStatuses() as $s)
                                    <option value="{{ $s }}" {{ old('status', $atendimento->status) == $s ? 'selected' : '' }}>
                                        {{ $s }}</option>
                                @endforeach
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status_pagamento_edit" class="form-label">Status do Pagamento <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('status_pagamento') is-invalid @enderror"
                                id="status_pagamento_edit" name="status_pagamento" required>
                                @foreach (App\Models\Atendimento::getPossiblePaymentStatuses() as $sp)
                                    <option value="{{ $sp }}" {{ old('status_pagamento', $atendimento->status_pagamento) == $sp ? 'selected' : '' }}>{{ $sp }}</option>
                                @endforeach
                            </select>
                            @error('status_pagamento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tecnico_id_edit" class="form-label">Técnico Responsável</label>
                            <select class="form-select @error('tecnico_id') is-invalid @enderror" id="tecnico_id_edit"
                                name="tecnico_id">
                                <option value="">Não atribuído</option>
                                @foreach ($tecnicos as $tecnico)
                                    <option value="{{ $tecnico->id }}" {{ old('tecnico_id', $atendimento->tecnico_id) == $tecnico->id ? 'selected' : '' }}>{{ $tecnico->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tecnico_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="data_conclusao_edit" class="form-label">Data de Conclusão (Opcional)</label>
                            <input type="date" class="form-control @error('data_conclusao') is-invalid @enderror"
                                id="data_conclusao_edit" name="data_conclusao"
                                value="{{ old('data_conclusao', $atendimento->data_conclusao ? $atendimento->data_conclusao->format('Y-m-d') : '') }}">
                            @error('data_conclusao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="laudo_tecnico_edit" class="form-label">Laudo Técnico / Solução Aplicada</label>
                        <textarea
                            class="form-control @error('laudo_tecnico') is-invalid @enderror {{ (Gate::denies('is-admin-or-tecnico') || !$permitirEdicaoItens) ? 'readonly-look' : '' }}"
                            id="laudo_tecnico_edit" name="laudo_tecnico" rows="4" {{ (Gate::denies('is-admin-or-tecnico') || !$permitirEdicaoItens) ? 'readonly' : '' }}>{{ old('laudo_tecnico', $atendimento->laudo_tecnico) }}</textarea>
                        @error('laudo_tecnico') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if(!$permitirEdicaoItens && Gate::denies('is-admin-or-tecnico'))
                            <small class="form-text text-muted">Apenas admin/técnico podem editar o laudo, e apenas se o atendimento não estiver finalizado.</small>
                        @elseif(!$permitirEdicaoItens)
                             <small class="form-text text-muted">Edição bloqueada (Status: {{ $atendimento->status }})</small>
                        @elseif(Gate::denies('is-admin-or-tecnico'))
                            <small class="form-text text-muted">Apenas administradores ou técnicos podem editar o laudo.</small>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="observacoes_edit" class="form-label">Observações (Internas/Gerais)</label>
                        <textarea class="form-control @error('observacoes') is-invalid @enderror {{ !$permitirEdicaoItens ? 'readonly-look' : '' }}" id="observacoes_edit"
                            name="observacoes" rows="3" {{ !$permitirEdicaoItens ? 'readonly' : '' }}>{{ old('observacoes', $atendimento->observacoes) }}</textarea>
                        @error('observacoes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                         @if(!$permitirEdicaoItens)
                             <small class="form-text text-muted">Edição bloqueada (Status: {{ $atendimento->status }})</small>
                        @endif
                    </div>
                </div>
            </div>

            {{-- CARD 4: Valores e Pagamento --}}
            <div class="card shadow-sm @unless($permitirEdicaoItens && Gate::allows('is-admin')) disabled-card-section @endunless">
                {{-- Aplica disabled-card-section se itens não podem ser editados OU se usuário não é admin (para desconto) --}}
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-currency-dollar"></i> Valores e Pagamento</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="valor_servico_total_display_edit" class="form-label">Total Mão de Obra (R$)</label>
                            <input type="text" class="form-control readonly-look" id="valor_servico_total_display_edit"
                                value="R$ {{ number_format(old('valor_servico', $atendimento->valor_servico ?? 0), 2, ',', '.') }}"
                                readonly title="Este valor é a soma dos serviços detalhados.">
                            <input type="hidden" name="valor_servico" id="valor_servico_hidden_edit"
                                value="{{ old('valor_servico', $atendimento->valor_servico ?? '0.00') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="desconto_servico_edit" class="form-label">Desconto Global OS (R$)</label>
                            <input type="number" step="0.01"
                                class="form-control @error('desconto_servico') is-invalid @enderror {{ Gate::denies('is-admin') || !$permitirEdicaoItens ? 'readonly-look' : '' }}"
                                id="desconto_servico_edit" name="desconto_servico"
                                value="{{ old('desconto_servico', number_format($atendimento->desconto_servico ?? 0, 2, '.', '')) }}"
                                min="0" {{ Gate::denies('is-admin') || !$permitirEdicaoItens ? 'readonly' : '' }}>
                            @error('desconto_servico') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @if(!$permitirEdicaoItens)
                                <small class="form-text text-muted">Edição bloqueada (Status: {{ $atendimento->status }})</small>
                            @elseif(Gate::denies('is-admin'))
                                 <small class="form-text text-muted">Apenas administradores podem aplicar desconto global.</small>
                            @endif
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="forma_pagamento_edit" class="form-label">Forma de Pagamento</label>
                            <select class="form-select @error('forma_pagamento') is-invalid @enderror {{ !$permitirEdicaoItens ? 'readonly-look' : '' }}"
                                id="forma_pagamento_edit" name="forma_pagamento" {{ !$permitirEdicaoItens ? 'disabled' : '' }}>
                                <option value="">Selecione (se pago)</option>
                                @foreach($formasPagamento as $opcao)
                                    <option value="{{ $opcao }}" {{ old('forma_pagamento', $atendimento->forma_pagamento) == $opcao ? 'selected' : '' }}>{{ $opcao }}</option>
                                @endforeach
                            </select>
                            @error('forma_pagamento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @if(!$permitirEdicaoItens)
                                <input type="hidden" name="forma_pagamento" value="{{ old('forma_pagamento', $atendimento->forma_pagamento) }}">
                                <small class="form-text text-muted">Edição bloqueada (Status: {{ $atendimento->status }})</small>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Subtotal Serviços (Líquido):</strong>
                                <span id="displaySubtotalServicoOsEdit" class="fw-bold">R$
                                    {{ number_format($atendimento->valor_servico_liquido, 2, ',', '.') }}</span>
                            </p>
                            <p class="mb-1"><strong>(+) Total Peças Registradas:</strong>
                                @php $totalPecasOsEdit = $atendimento->saidasEstoque->sum(fn($saida) => $saida->quantidade * ($saida->estoque->preco_venda ?? 0)); @endphp
                                <span id="displayTotalPecasEdit" class="fw-bold">R$
                                    {{ number_format($totalPecasOsEdit, 2, ',', '.') }}</span>
                                <input type="hidden" id="hiddenTotalPecasEdit" value="{{ $totalPecasOsEdit }}">
                                <small class="d-block text-muted">(Peças são gerenciadas na tela de "Detalhes do
                                    Atendimento")</small>
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-0 fs-5"><strong>VALOR TOTAL DA OS:</strong>
                                <span id="displayValorTotalOsFinalEdit" class="fw-bolder text-success">R$
                                    {{ number_format($atendimento->valor_total_atendimento, 2, ',', '.') }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Salvar Alterações</button>
                <a href="{{ route('atendimentos.show', $atendimento->id) }}" class="btn btn-secondary ms-2">Cancelar</a>
            </div>
        </form>

        {{-- Template para os itens de serviço (reutilizado) --}}
        <div id="atendimento-servico-item-template-edit" style="display: none;">
            @include('atendimentos.partials._item_servico_template', ['index' => '__INDEX__', 'isReadOnly' => !$permitirEdicaoItens])
        </div>

        {{-- Modal para Novo Cliente (se necessário, mas o autocomplete é mais comum aqui) --}}
        @include('clientes.partials.modal_create')
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const permitirEdicaoItensGlobal = {{ $permitirEdicaoItens ? 'true' : 'false' }};

            // ----- AUTOCOMPLETE DE CLIENTE (PARA EDIT) -----
            // ... (código do autocomplete de cliente como na sua versão anterior do edit.blade.php)
            let clienteSelecionadoEdit = ($('#cliente_id_edit').val() !== '');
            $("#cliente_nome_edit").autocomplete({
                source: "{{ route('clientes.autocomplete') }}",
                minLength: 2,
                disabled: !permitirEdicaoItensGlobal, // Desabilita se não pode editar
                select: function(event, ui) {
                    if (ui.item && ui.item.id) {
                        $('#cliente_nome_edit').val(ui.item.value);
                        $('#cliente_id_edit').val(ui.item.id);
                        const telefoneInfoEl = document.getElementById('cliente_telefone_info_edit');
                        const emailInfoEl = document.getElementById('cliente_email_info_edit');
                        const infoContainerEl = document.getElementById('info_cliente_selecionado_edit');
                        if(telefoneInfoEl) telefoneInfoEl.textContent = ui.item.telefone || 'Não informado';
                        if(emailInfoEl) emailInfoEl.textContent = ui.item.email || 'Não informado';
                        if(infoContainerEl) $(infoContainerEl).show();
                        clienteSelecionadoEdit = true;
                    }
                    return false;
                },
                change: function(event, ui) {
                    if (!clienteSelecionadoEdit && !ui.item) {
                        $('#cliente_id_edit').val('');
                        const infoContainerEl = document.getElementById('info_cliente_selecionado_edit');
                        if(infoContainerEl) $(infoContainerEl).hide();
                    }
                }
            });
             $("#cliente_nome_edit").on('input', function() {
                if(!permitirEdicaoItensGlobal) return;
                if ($(this).val() === '') {
                    $('#cliente_id_edit').val('');
                    const infoContainerEl = document.getElementById('info_cliente_selecionado_edit');
                    if(infoContainerEl) $(infoContainerEl).hide();
                    clienteSelecionadoEdit = false;
                } else if (!clienteSelecionadoEdit) {
                    $('#cliente_id_edit').val('');
                }
            });


            // ----- LÓGICA DO MODAL DE NOVO CLIENTE -----
            // ... (código do modal de novo cliente, como antes, mas verificando `permitirEdicaoItensGlobal` antes de habilitar o botão do modal se necessário)
            const btnNovoClienteModalTrigger = $('button[data-bs-target="#modalNovoCliente"]');
            if(!permitirEdicaoItensGlobal && btnNovoClienteModalTrigger.length){
                btnNovoClienteModalTrigger.prop('disabled', true).addClass('disabled-look');
            }
            $('#formNovoCliente').on('submit', function(e) {
                // ... (lógica de submissão do modal, como antes)
                e.preventDefault();
                var formData = $(this).serialize();
                $('#btnSalvarNovoCliente').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Salvando...');
                $.ajax({
                    url: "{{ route('clientes.store') }}", type: 'POST', data: formData, dataType: 'json',
                    success: function(response) {
                        if (response.success && response.cliente) {
                            var cliente = response.cliente;
                            $('#cliente_nome_edit').val(cliente.nome_completo);
                            $('#cliente_id_edit').val(cliente.id);
                            const telefoneInfoEl = document.getElementById('cliente_telefone_info_edit');
                            const emailInfoEl = document.getElementById('cliente_email_info_edit');
                            const infoContainerEl = document.getElementById('info_cliente_selecionado_edit');
                            if(telefoneInfoEl) telefoneInfoEl.textContent = cliente.telefone || 'Não informado';
                            if(emailInfoEl) emailInfoEl.textContent = cliente.email || 'Não informado';
                            if(infoContainerEl) $(infoContainerEl).show();
                            $('#modalNovoCliente').modal('hide');
                            $('#formNovoCliente')[0].reset();
                            $('#formNovoCliente .invalid-feedback').remove();
                            $('#formNovoCliente .is-invalid').removeClass('is-invalid');
                        } else {
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
                        alert('Erro ao conectar com o servidor para salvar o cliente.');
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            $('#formNovoCliente .invalid-feedback').remove();
                            $('#formNovoCliente .is-invalid').removeClass('is-invalid');
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                $('#modal_' + key).addClass('is-invalid').after('<div class="invalid-feedback d-block">' + value[0] + '</div>');
                            });
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
                            formDoModal.find('#modal_cep').focus();
                        }
                    }).fail(function() { alert("Erro ao consultar o serviço de CEP no modal."); });
                } else if (cep.length > 0 && cep.length < 8) {
                    alert("CEP (Modal) inválido. Digite 8 dígitos.");
                }
            });
            $('#modalNovoCliente').on('hidden.bs.modal', function() {
                $('#formNovoCliente')[0].reset();
                $('#formNovoCliente .is-invalid').removeClass('is-invalid');
                $('#formNovoCliente .invalid-feedback').remove();
                $('#formNovoCliente').find('#endereco_modal_fields').hide();
            });


            // ----- LÓGICA PARA ITENS DE SERVIÇO DETALHADOS (EDIÇÃO) -----
            let itemServicoOSEditIndex = $('#servicos-detalhados-container-edit .item-servico-detalhado-row').length;
            const templateServicoEdit = $('#atendimento-servico-item-template-edit').html();

            function adicionarItemServicoOSEdit(itemData = null, isInitial = false) {
                if (!permitirEdicaoItensGlobal && !isInitial) return; // Não adiciona se bloqueado e não for carga inicial

                let novoItemHTML = templateServicoEdit.replace(/__INDEX__/g, itemServicoOSEditIndex);
                $('#servicos-detalhados-container-edit').append(novoItemHTML);
                let novaLinha = $('#servicos-detalhados-container-edit .item-servico-detalhado-row[data-index="' + itemServicoOSEditIndex + '"]');

                if (itemData) {
                    novaLinha.find('input[name$="[id]"]').val(itemData.id || '');
                    novaLinha.find('.item-servico-descricao').val(itemData.descricao_servico || '');
                    novaLinha.find('.item-servico-quantidade').val(itemData.quantidade || 1);
                    novaLinha.find('.item-servico-valor-unitario').val(parseFloat(itemData.valor_unitario || 0).toFixed(2));
                }

                // Se não for permitido editar, marca os campos como readonly
                if (!permitirEdicaoItensGlobal) {
                    novaLinha.find('input, select').prop('readonly', true).addClass('readonly-look');
                    novaLinha.find('.remover-item-servico-detalhado').hide();
                } else {
                     // Garante que os listeners são adicionados para itens novos
                    novaLinha.find('.item-servico-quantidade, .item-servico-valor-unitario').on('input', calcularTotaisEstimadosOSEdit);
                }
                itemServicoOSEditIndex++;
                calcularTotaisEstimadosOSEdit();
            }

            // Botão para adicionar novo serviço
            $('#adicionar-servico-detalhado-os-edit').on('click', function() {
                if (!permitirEdicaoItensGlobal) return;
                $('#nenhum-servico-mensagem-edit').hide();
                adicionarItemServicoOSEdit();
            });

            // Remover item de serviço
            $('#servicos-detalhados-container-edit').on('click', '.remover-item-servico-detalhado', function() {
                if (!permitirEdicaoItensGlobal) return;
                $(this).closest('.item-servico-detalhado-row').remove();
                calcularTotaisEstimadosOSEdit();
                if ($('#servicos-detalhados-container-edit .item-servico-detalhado-row').length === 0) {
                    $('#nenhum-servico-mensagem-edit').show();
                    itemServicoOSEditIndex = 0; // Reseta o índice se todos forem removidos
                }
            });

            // Calcular totais ao carregar e ao modificar campos
            function calcularTotaisEstimadosOSEdit() {
                let totalMaoDeObra = 0;
                $('#servicos-detalhados-container-edit .item-servico-detalhado-row').each(function() {
                    let quantidade = parseFloat($(this).find('.item-servico-quantidade').val()) || 0;
                    let valorUnitario = parseFloat($(this).find('.item-servico-valor-unitario').val()) || 0;
                    let subtotalItem = quantidade * valorUnitario;
                    $(this).find('.item-servico-subtotal-display').text(subtotalItem.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    totalMaoDeObra += subtotalItem;
                });

                $('#valor_servico_total_display_edit').val('R$ ' + totalMaoDeObra.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#valor_servico_hidden_edit').val(totalMaoDeObra.toFixed(2)); // Para o backend

                let descontoServico = parseFloat($('#desconto_servico_edit').val().replace(',', '.')) || 0;
                if (descontoServico > totalMaoDeObra) {
                    descontoServico = totalMaoDeObra;
                    // Opcional: Atualizar visualmente o campo de desconto se ele for corrigido
                    // $('#desconto_servico_edit').val(descontoServico.toFixed(2).replace('.', ','));
                }

                let subtotalServicoOS = totalMaoDeObra - descontoServico;
                let totalPecasOS = parseFloat($('#hiddenTotalPecasEdit').val().replace(',', '.')) || 0;
                let valorTotalFinalOS = subtotalServicoOS + totalPecasOS;

                $('#displaySubtotalServicoOsEdit').text('R$ ' + subtotalServicoOS.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#displayValorTotalOsFinalEdit').text('R$ ' + valorTotalFinalOS.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            }

            // Adiciona listeners para os inputs dos itens de serviço existentes e futuros
            $('#servicos-detalhados-container-edit').on('input', '.item-servico-quantidade, .item-servico-valor-unitario', calcularTotaisEstimadosOSEdit);
            $('#desconto_servico_edit').on('input', calcularTotaisEstimadosOSEdit);

            // Inicialização dos itens de serviço existentes (renderizados pelo Blade)
            $('#servicos-detalhados-container-edit .item-servico-detalhado-row').each(function(index) {
                const isReadOnlyCurrent = !permitirEdicaoItensGlobal;
                $(this).find('input, select').prop('readonly', isReadOnlyCurrent).toggleClass('readonly-look', isReadOnlyCurrent);
                if(isReadOnlyCurrent){
                    $(this).find('.remover-item-servico-detalhado').hide();
                } else {
                    $(this).find('.remover-item-servico-detalhado').show();
                }
            });

            // Calcular totais na carga inicial da página
            calcularTotaisEstimadosOSEdit();
            // Se não houver itens e for permitido editar, adiciona uma linha inicial
             if (itemServicoOSEditIndex === 0 && permitirEdicaoItensGlobal && $('#servicos-detalhados-container-edit .item-servico-detalhado-row').length === 0) {
                $('#nenhum-servico-mensagem-edit').hide();
                adicionarItemServicoOSEdit(null, false); // Adiciona a primeira linha
            } else if (itemServicoOSEditIndex > 0){
                 $('#nenhum-servico-mensagem-edit').hide();
            }

        });
    </script>
@endpush
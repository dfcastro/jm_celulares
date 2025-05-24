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

        .item-servico-detalhado-row {
            /* Estilos como no create.blade.php, se necessário */
        }

        .readonly-look {
            background-color: #e9ecef;
            opacity: 1;
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

        <form action="{{ route('atendimentos.update', $atendimento->id) }}" method="POST" id="formEditarAtendimento">
            @csrf
            @method('PUT')

            {{-- CARD 1: Dados do Cliente e Aparelho --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-person-badge"></i> Cliente e Aparelho</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="cliente_id_edit" class="form-label">Cliente <span
                                    class="text-danger">*</span></label>
                            {{-- Campo de texto para autocomplete --}}
                            <input type="text" class="form-control @error('cliente_id') is-invalid @enderror"
                                id="cliente_nome_edit" name="cliente_nome_display_edit"
                                placeholder="Digite nome ou CPF/CNPJ para buscar..."
                                value="{{ old('cliente_nome_display_edit', $atendimento->cliente->nome_completo ?? '') }}"
                                required>
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
                            <small><a href="{{ route('clientes.create', ['redirect_to' => url()->current()]) }}"
                                    target="_blank" class="text-decoration-none mt-1 d-inline-block"><i
                                        class="bi bi-plus-circle"></i> Cadastrar Novo Cliente (Nova Aba)</a>
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="descricao_aparelho_edit" class="form-label">Aparelho (Marca/Modelo/Cor) <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('descricao_aparelho') is-invalid @enderror"
                                id="descricao_aparelho_edit" name="descricao_aparelho"
                                value="{{ old('descricao_aparelho', $atendimento->descricao_aparelho) }}" required>
                            @error('descricao_aparelho') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="problema_relatado_edit" class="form-label">Problema Relatado <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control @error('problema_relatado') is-invalid @enderror"
                            id="problema_relatado_edit" name="problema_relatado" rows="3"
                            required>{{ old('problema_relatado', $atendimento->problema_relatado) }}</textarea>
                        @error('problema_relatado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- CARD 2: Serviços Detalhados da OS --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="my-1"><i class="bi bi-tools"></i> Serviços Detalhados da OS</h5>
                    @can('is-admin-or-tecnico')
                        <button type="button" class="btn btn-success btn-sm" id="adicionar-servico-detalhado-os-edit">
                            <i class="bi bi-plus-circle"></i> Adicionar Serviço
                        </button>
                    @endcan
                </div>
                <div class="card-body">
                    <div id="servicos-detalhados-container-edit">
                        @php
                            $servicosParaIterar = old('servicos_detalhados', $atendimento->servicosDetalhados->toArray() ?: []);
                        @endphp
                        @if(!empty($servicosParaIterar))
                            @foreach($servicosParaIterar as $index => $itemServicoData)
                                @php if (is_object($itemServicoData)) $itemServicoData = (array)$itemServicoData; @endphp
                                @include('atendimentos.partials._item_servico_template', ['index' => $index, 'itemServicoData' => $itemServicoData])
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
                            class="form-control @error('laudo_tecnico') is-invalid @enderror {{ Gate::denies('is-admin-or-tecnico') ? 'readonly-look' : '' }}"
                            id="laudo_tecnico_edit" name="laudo_tecnico" rows="4" {{ Gate::denies('is-admin-or-tecnico') ? 'readonly' : '' }}>{{ old('laudo_tecnico', $atendimento->laudo_tecnico) }}</textarea>
                        @error('laudo_tecnico') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="observacoes_edit" class="form-label">Observações (Internas/Gerais)</label>
                        <textarea class="form-control @error('observacoes') is-invalid @enderror" id="observacoes_edit"
                            name="observacoes" rows="3">{{ old('observacoes', $atendimento->observacoes) }}</textarea>
                        @error('observacoes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- CARD 4: Valores e Pagamento --}}
            <div class="card shadow-sm">
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
                                class="form-control @error('desconto_servico') is-invalid @enderror {{ Gate::denies('is-admin') ? 'readonly-look' : '' }}"
                                id="desconto_servico_edit" name="desconto_servico"
                                value="{{ old('desconto_servico', number_format($atendimento->desconto_servico ?? 0, 2, '.', '')) }}"
                                min="0" {{ Gate::denies('is-admin') ? 'readonly' : '' }}>
                            @error('desconto_servico') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @cannot('is-admin') <small class="form-text text-muted">Apenas administradores.</small>
                            @endcannot
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="forma_pagamento_edit" class="form-label">Forma de Pagamento</label>
                            <select class="form-select @error('forma_pagamento') is-invalid @enderror"
                                id="forma_pagamento_edit" name="forma_pagamento">
                                <option value="">Selecione (se pago)</option>
                                @foreach($formasPagamento as $opcao)
                                    <option value="{{ $opcao }}" {{ old('forma_pagamento', $atendimento->forma_pagamento) == $opcao ? 'selected' : '' }}>{{ $opcao }}</option>
                                @endforeach
                            </select>
                            @error('forma_pagamento') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
            @include('atendimentos.partials._item_servico_template', ['index' => '__INDEX__'])
        </div>

        {{-- Modal para Novo Cliente (se necessário, mas o autocomplete é mais comum aqui) --}}
        @include('clientes.partials.modal_create')
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // ----- AUTOCOMPLETE DE CLIENTE (PARA EDIT) -----
            let clienteSelecionadoEdit = false;
            // Certifique-se de que o input de busca de cliente no seu edit.blade.php tem o ID 'cliente_nome_edit'
        // e o input hidden para o ID do cliente tem o ID 'cliente_id_edit'.
        $("#cliente_nome_edit").autocomplete({
            source: "{{ route('clientes.autocomplete') }}",
            minLength: 2,
            select: function(event, ui) {
                if (ui.item && ui.item.id) {
                    $('#cliente_nome_edit').val(ui.item.value);
                    $('#cliente_id_edit').val(ui.item.id);
                    // Atualizar spans de info do cliente se eles existirem na edit.blade.php
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
        // Limpar ID se o campo de nome do cliente for limpo manualmente
         $("#cliente_nome_edit").on('input', function() {
            if ($(this).val() === '') {
                $('#cliente_id_edit').val('');
                const infoContainerEl = document.getElementById('info_cliente_selecionado_edit');
                if(infoContainerEl) $(infoContainerEl).hide();
                clienteSelecionadoEdit = false;
            } else if (!clienteSelecionadoEdit) {
                 $('#cliente_id_edit').val(''); // Limpa se estiver digitando e não selecionou
            }
        });


        // ----- LÓGICA DO MODAL DE NOVO CLIENTE -----
        // Este código é idêntico ao da create.blade.php, apenas garanta que os IDs
        // dos campos do formulário principal que ele preenche (como #cliente_nome_edit, #cliente_id_edit)
        // estejam corretos para a tela de edição.
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
                        // Preenche os campos do formulário de EDIÇÃO de atendimento com o novo cliente
                        $('#cliente_nome_edit').val(cliente.nome_completo); // Campo de display
                        $('#cliente_id_edit').val(cliente.id);       // Campo hidden

                        const telefoneInfoEl = document.getElementById('cliente_telefone_info_edit');
                        const emailInfoEl = document.getElementById('cliente_email_info_edit');
                        const infoContainerEl = document.getElementById('info_cliente_selecionado_edit');

                        if(telefoneInfoEl) telefoneInfoEl.textContent = cliente.telefone || 'Não informado';
                        if(emailInfoEl) emailInfoEl.textContent = cliente.email || 'Não informado';
                        if(infoContainerEl) $(infoContainerEl).show();


                        // Adiciona o novo cliente ao select de clientes (se o select ainda existir e for usado)
                        // Se o campo cliente_id_edit é um select:
                        // $('#cliente_id_edit').append(new Option(cliente.nome_completo + ' (' + cliente.cpf_cnpj + ')', cliente.id, true, true)).trigger('change');

                        $('#modalNovoCliente').modal('hide');
                        $('#formNovoCliente')[0].reset();
                        $('#formNovoCliente .invalid-feedback').remove();
                        $('#formNovoCliente .is-invalid').removeClass('is-invalid');
                        // Talvez focar em outro campo do formulário de edição
                        $('#descricao_aparelho_edit').focus();
                    } else {
                        // alert(response.message || 'Ocorreu um erro ao salvar o cliente.');
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

        // Máscaras e ViaCEP do modal de cliente (como antes)
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


        // ----- LÓGICA PARA ATUALIZAR TOTAIS NA TELA DE EDIÇÃO -----
        // Esta função é chamada quando o desconto global da OS é alterado.
        function calcularTotaisEstimadosOSEdit() {
            // Pega o valor da mão de obra do display (que é preenchido pelo PHP e reflete a soma dos AtendimentoServico)
            let valorServicoDisplay = $('#valor_servico_display_edit').val() || 'R$ 0,00';
            let valorServico = parseFloat(valorServicoDisplay.replace('R$ ', '').replace(/\./g, '').replace(',', '.')) || 0;

            let descontoServico = parseFloat($('#desconto_servico_edit').val().replace(',', '.')) || 0;

            if (descontoServico > valorServico) {
                descontoServico = valorServico;
                // Opcional: Atualizar visualmente o campo de desconto se ele for corrigido
                // $('#desconto_servico_edit').val(descontoServico.toFixed(2).replace('.', ','));
            }

            let subtotalServicoOS = valorServico - descontoServico;
            let totalPecasOS = parseFloat($('#hiddenTotalPecasEdit').val()) || 0; // Pega do input hidden que armazena o total de peças
            let valorTotalFinalOS = subtotalServicoOS + totalPecasOS;

            $('#displaySubtotalServicoOsEdit').text('R$ ' + subtotalServicoOS.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#displayValorTotalOsFinalEdit').text('R$ ' + valorTotalFinalOS.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        }

        // Listener para recalcular quando o desconto global da OS for alterado
        $('#desconto_servico_edit').on('input', calcularTotaisEstimadosOSEdit);

        // Calcular totais na carga inicial da página de edição para garantir que os displays estejam corretos
        calcularTotaisEstimadosOSEdit();

    });
    </script>
@endpush
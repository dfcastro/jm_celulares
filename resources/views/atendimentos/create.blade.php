@extends('layouts.app')

@section('title', 'Registrar Novo Atendimento')

@push('styles')
<style>
    .ui-autocomplete {
        z-index: 1055 !important;
    }
    .card-title {
        margin-bottom: 1.5rem;
    }
    /* Removidos estilos de .item-servico-detalhado-row e .readonly-look pois não serão usados aqui */
</style>
@endpush

@section('content')
{{-- Seção de mensagens de feedback global --}}
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
{{-- Adicione @if(session('info')) e @if(session('warning')) se necessário --}}

<div class="container mt-0">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="my-0"><i class="bi bi-headset"></i> Registrar Novo Atendimento</h4>
                </div>
                <div class="card-body p-4">
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

                    <form action="{{ route('atendimentos.store') }}" method="POST" id="formCriarAtendimento">
                        @csrf

                        {{-- CARD 1: Dados do Cliente e Aparelho --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h5 class="my-1"><i class="bi bi-person-badge"></i> Cliente e Aparelho</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="cliente_nome" class="form-label">Cliente <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control @error('cliente_id') is-invalid @enderror"
                                                id="cliente_nome" name="cliente_nome_display"
                                                placeholder="Digite nome ou CPF/CNPJ para buscar..." value="{{ old('cliente_nome_display', optional($clienteSelecionado)->nome_completo) }}" required>
                                            <button class="btn btn-outline-secondary" type="button" id="btnNovoCliente" data-bs-toggle="modal" data-bs-target="#modalNovoCliente" title="Cadastrar Novo Cliente">
                                                <i class="bi bi-person-plus-fill"></i> Novo
                                            </button>
                                        </div>
                                        <input type="hidden" id="cliente_id" name="cliente_id" value="{{ old('cliente_id', $selectedClienteId ?? '') }}">
                                        @error('cliente_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div id="info_cliente_selecionado" class="mt-2 text-muted small" style="{{ old('cliente_id', $selectedClienteId ?? '') ? '' : 'display: none;' }}">
                                            Telefone: <span id="cliente_telefone_info">{{ old('cliente_id', $selectedClienteId ?? '') && $clienteSelecionado ? $clienteSelecionado->telefone : '' }}</span> |
                                            Email: <span id="cliente_email_info">{{ old('cliente_id', $selectedClienteId ?? '') && $clienteSelecionado ? $clienteSelecionado->email : '' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="descricao_aparelho" class="form-label">Aparelho (Marca/Modelo/Cor) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('descricao_aparelho') is-invalid @enderror" id="descricao_aparelho" name="descricao_aparelho"
                                            value="{{ old('descricao_aparelho') }}"
                                            placeholder="Ex: iPhone 11 Pro Max Preto, Samsung A52 Azul com listras" required>
                                        @error('descricao_aparelho')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="problema_relatado" class="form-label">Problema Relatado pelo Cliente <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('problema_relatado') is-invalid @enderror" id="problema_relatado" name="problema_relatado" rows="3"
                                        placeholder="Descreva o problema informado pelo cliente..." required>{{ old('problema_relatado') }}</textarea>
                                    @error('problema_relatado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- CARD: Status, Prazos e Técnico --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h5 class="my-1"><i class="bi bi-ui-checks-grid"></i> Detalhes Iniciais do Atendimento</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="data_entrada" class="form-label">Data de Entrada <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('data_entrada') is-invalid @enderror"
                                            id="data_entrada" name="data_entrada" value="{{ old('data_entrada', now()->format('Y-m-d')) }}" required>
                                        @error('data_entrada')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tecnico_id" class="form-label">Técnico Responsável (Opcional)</label>
                                        <select class="form-select @error('tecnico_id') is-invalid @enderror" id="tecnico_id" name="tecnico_id">
                                            <option value="">Não atribuído</option>
                                            @foreach ($tecnicos as $tecnico)
                                            <option value="{{ $tecnico->id }}" {{ old('tecnico_id') == $tecnico->id ? 'selected' : '' }}>{{ $tecnico->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('tecnico_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="observacoes_create" class="form-label">Observações Iniciais (Opcional)</label>
                                    <textarea class="form-control" id="observacoes_create" name="observacoes" rows="2" placeholder="Ex: Cliente deixou carregador, aparelho com película trincada...">{{ old('observacoes') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- O card de Valores e Pagamento foi REMOVIDO desta view --}}
                        {{-- Campos hidden para valores default que o controller espera ou pode usar --}}
                        <input type="hidden" name="valor_servico" value="0.00">
                        <input type="hidden" name="desconto_servico" value="0.00">
                        <input type="hidden" name="status_pagamento" value="Pendente">
                        {{-- forma_pagamento será null por padrão --}}


                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save"></i> Registrar Atendimento</button>
                            <a href="{{ route('atendimentos.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para Novo Cliente --}}
    @include('clientes.partials.modal_create')

    {{-- O template de item de serviço NÃO é mais necessário aqui --}}
</div>
@endsection

@push('scripts')
    {{-- jQuery e jQuery UI já são carregados pelo layouts/app.blade.php --}}
<script>
$(document).ready(function() {
    // ----- AUTOCOMPLETE DE CLIENTE (Mantido como antes) -----
    let clienteSelecionadoPeloAutocomplete = false;
    $("#cliente_nome").autocomplete({
        source: function(request, response) {
            clienteSelecionadoPeloAutocomplete = false;
            $.ajax({
                url: "{{ route('clientes.autocomplete') }}",
                type: 'GET', dataType: "json", data: { search: request.term },
                success: function(data) {
                    response($.map(data, function(item) {
                        return { label: item.label, value: item.value, id: item.id, telefone: item.telefone, email: item.email };
                    }));
                },
                error: function() { response([]); }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            if (ui.item && ui.item.id) {
                $('#cliente_nome').val(ui.item.value);
                $('#cliente_id').val(ui.item.id);
                $('#cliente_telefone_info').text(ui.item.telefone || 'Não informado');
                $('#cliente_email_info').text(ui.item.email || 'Não informado');
                $('#info_cliente_selecionado').show();
                clienteSelecionadoPeloAutocomplete = true;
            }
            $('#descricao_aparelho').focus(); // Foca no próximo campo importante
            return false;
        },
        change: function(event, ui) {
            if (!clienteSelecionadoPeloAutocomplete && !ui.item) {
                $('#cliente_id').val('');
                $('#info_cliente_selecionado').hide();
            }
        }
    });
    $("#cliente_nome").on('input', function() {
        if ($(this).val() === '') {
            $('#cliente_id').val('');
            $('#info_cliente_selecionado').hide();
            clienteSelecionadoPeloAutocomplete = false;
        } else if (!clienteSelecionadoPeloAutocomplete) {
            $('#cliente_id').val('');
        }
    });

    // ----- LÓGICA DO MODAL DE NOVO CLIENTE (Mantida como antes) -----
    $('#formNovoCliente').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $('#btnSalvarNovoCliente').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Salvando...');
        $.ajax({
            url: "{{ route('clientes.store') }}", type: 'POST', data: formData, dataType: 'json',
            success: function(response) {
                if (response.success && response.cliente) {
                    var cliente = response.cliente;
                    $('#cliente_nome').val(cliente.nome_completo);
                    $('#cliente_id').val(cliente.id);
                    $('#cliente_telefone_info').text(cliente.telefone || 'Não informado');
                    $('#cliente_email_info').text(cliente.email || 'Não informado');
                    $('#info_cliente_selecionado').show();
                    $('#modalNovoCliente').modal('hide');
                    $('#formNovoCliente')[0].reset();
                    $('#formNovoCliente .invalid-feedback').remove();
                    $('#formNovoCliente .is-invalid').removeClass('is-invalid');
                    $('#descricao_aparelho').focus();
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

    // Removida a lógica de adicionar/remover serviços detalhados e calcular totais
    // pois o card de "Serviços Detalhados" e o de "Valores" foram removidos/simplificados.
});
</script>
@endpush
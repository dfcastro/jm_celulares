@extends('layouts.app')

@section('title', 'Registrar Novo Atendimento')

@push('styles')
{{-- jQuery UI CSS já está no layout principal (layouts/app.blade.php) --}}
<style>
    .ui-autocomplete {
        z-index: 1055 !important;
        /* Para aparecer sobre outros elementos se necessário */
    }

    .card-title {
        margin-bottom: 1.5rem;
        /* Mais espaço abaixo do título do card */
    }
</style>
@endpush

@section('content')
<div class="container mt-0">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="my-0"><i class="bi bi-headset"></i> Registrar Novo Atendimento</h4>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif  

                    <form action="{{ route('atendimentos.store') }}" method="POST">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="cliente_nome" class="form-label">Cliente <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    {{-- Este input é para exibição e para o autocomplete --}}
                                    <input type="text" class="form-control @error('cliente_id') is-invalid @enderror"
                                        id="cliente_nome" name="cliente_nome_display" {{-- O 'name' aqui é para o que o usuário digita --}}
                                        placeholder="Digite nome ou CPF/CNPJ para buscar..." value="{{ old('cliente_nome_display') }}" required>
                                    <button class="btn btn-outline-secondary" type="button" id="btnNovoCliente" data-bs-toggle="modal" data-bs-target="#modalNovoCliente">
                                        <i class="bi bi-person-plus-fill"></i> Novo
                                    </button>
                                </div>
                                <input type="hidden" id="cliente_id" name="cliente_id" value="{{ old('cliente_id') }}">
                                @error('cliente_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div> {{-- d-block para feedback de hidden --}}
                                @enderror
                                <div id="info_cliente_selecionado" class="mt-2 text-muted small" style="display: none;">
                                    Telefone: <span id="cliente_telefone_info"></span> | Email: <span id="cliente_email_info"></span>
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
                            <textarea class="form-control @error('problema_relatado') is-invalid @enderror" id="problema_relatado" name="problema_relatado" rows="4"
                                placeholder="Descreva o problema informado pelo cliente..." required>{{ old('problema_relatado') }}</textarea>
                            @error('problema_relatado')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="data_entrada" class="form-label">Data de Entrada <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('data_entrada') is-invalid @enderror" id="data_entrada" name="data_entrada" value="{{ old('data_entrada', now()->format('Y-m-d')) }}" required>
                                @error('data_entrada')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
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

</div>
@endsection

@push('scripts')
{{-- jQuery e jQuery UI já são carregados pelo layouts/app.blade.php --}}
<script>
    $(document).ready(function() {
        let clienteSelecionadoPeloAutocomplete = false; // Nova flag

        $("#cliente_nome").autocomplete({
            source: function(request, response) {
                // Resetar a flag quando uma nova busca é iniciada
                clienteSelecionadoPeloAutocomplete = false;
                $.ajax({
                    url: "{{ route('clientes.autocomplete') }}",
                    type: 'GET',
                    dataType: "json",
                    data: {
                        search: request.term
                    },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: item.label,
                                value: item.value,
                                id: item.id,
                                telefone: item.telefone,
                                email: item.email
                            };
                        }));
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Erro ao buscar clientes para autocomplete:", textStatus, errorThrown);
                        response([]);
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                console.log("Autocomplete SELECT - ui.item:", ui.item);
                if (ui.item && ui.item.id) {
                    $('#cliente_nome').val(ui.item.value); // Nome do cliente no campo visível
                    $('#cliente_id').val(ui.item.id); // ID no campo hidden
                    console.log("Autocomplete SELECT - #cliente_id preenchido com:", ui.item.id);

                    $('#cliente_telefone_info').text(ui.item.telefone || 'Não informado');
                    $('#cliente_email_info').text(ui.item.email || 'Não informado');
                    $('#info_cliente_selecionado').show();
                    clienteSelecionadoPeloAutocomplete = true; // Marcar que uma seleção válida foi feita
                } else {
                    console.error("Autocomplete SELECT - item inválido ou sem ID:", ui.item);
                    $('#cliente_id').val('');
                    $('#info_cliente_selecionado').hide();
                    clienteSelecionadoPeloAutocomplete = false;
                }
                $('#descricao_aparelho').focus();
                return false;
            },
            change: function(event, ui) {
                console.log("Autocomplete CHANGE - ui.item:", ui.item);
                // Só limpar se NENHUM item foi selecionado via 'select' E o campo de texto foi alterado para algo que não corresponde a um item
                if (!clienteSelecionadoPeloAutocomplete && !ui.item) {
                    console.log("Autocomplete CHANGE - Limpando #cliente_id porque não houve seleção válida e ui.item é nulo.");
                    $('#cliente_id').val('');
                    $('#info_cliente_selecionado').hide();
                    $('#cliente_telefone_info').text('');
                    $('#cliente_email_info').text('');
                }
                // Se houve uma seleção válida, não fazemos nada aqui, pois o 'select' já cuidou disso.
                // Resetar a flag para a próxima interação, caso o usuário apague o campo manualmente depois de selecionar.
                // Se o campo ficar vazio, na próxima busca a flag será resetada no 'source'.
                // Se o usuário apagar e não selecionar nada, a submissão falhará, o que é esperado.
            },
            // Adicionar um evento 'close' para resetar a flag se o menu do autocomplete fechar sem seleção
            close: function(event, ui) {
                // Se o menu fechar e o campo de texto não corresponder a um item selecionado anteriormente (via flag)
                // E não houver um ui.item (o que pode acontecer se o usuário clicar fora)
                // Esta lógica pode precisar de ajuste fino dependendo do comportamento exato do 'change' e 'select'
                // Por ora, vamos confiar mais na flag e no 'change'.
            }
        });

        // Resetar a flag se o campo de nome for limpo manualmente
        $("#cliente_nome").on('input', function() {
            if ($(this).val() === '') {
                console.log("Campo #cliente_nome limpo manualmente, resetando #cliente_id e flag.");
                $('#cliente_id').val('');
                $('#info_cliente_selecionado').hide();
                clienteSelecionadoPeloAutocomplete = false;
            } else {
                // Se o usuário está digitando algo novo, a flag deve ser resetada
                // para que o evento 'change' funcione corretamente se ele não selecionar um item
                // clienteSelecionadoPeloAutocomplete = false; // Cuidado: isso pode interferir se ele está editando um nome já selecionado
            }
        });


        // Lógica do submit do formulário (para depuração)
        $('#formNovoAtendimento').on('submit', function(event) { // Certifique-se que seu form tem id="formNovoAtendimento"
            var clienteIdValor = $('#cliente_id').val();
            console.log("Valor de #cliente_id NO MOMENTO DO SUBMIT:", clienteIdValor);
            if (!clienteIdValor) {
                 alert("DEBUG: Cliente ID está vazio antes de submeter via autocomplete!");
                // event.preventDefault(); // Descomente para impedir o envio e depurar
            }
        });

        // Lógica para o formulário de novo cliente no modal
        $('#formNovoCliente').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $('#btnSalvarNovoCliente').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...');

            $.ajax({
                url: "{{ route('clientes.store') }}", // Rota para salvar cliente
                type: 'POST',
                data: formData,
                dataType: 'json', // Espera um JSON de volta
                success: function(response) {
                    if (response.success && response.cliente) {
                        var cliente = response.cliente;
                        // Preenche os campos do formulário de atendimento com o novo cliente
                        $('#cliente_nome').val(cliente.nome_completo);
                        $('#cliente_id').val(cliente.id);

                        $('#cliente_telefone_info').text(cliente.telefone || 'Não informado');
                        $('#cliente_email_info').text(cliente.email || 'Não informado');
                        $('#info_cliente_selecionado').show();

                        $('#modalNovoCliente').modal('hide'); // Fecha o modal
                        $('#formNovoCliente')[0].reset(); // Limpa o formulário do modal
                        $('.invalid-feedback').remove(); // Remove mensagens de erro antigas do modal
                        $('#descricao_aparelho').focus();
                    } else {
                        // Tratar erros de validação retornados pelo controller de cliente
                        // (Esta parte precisa ser melhorada para exibir erros no modal)
                        alert(response.message || 'Ocorreu um erro ao salvar o cliente.');
                        if (response.errors) {
                            // Limpa erros anteriores
                            $('#formNovoCliente .invalid-feedback').remove();
                            $('#formNovoCliente .is-invalid').removeClass('is-invalid');

                            $.each(response.errors, function(key, value) {
                                $('#modal_' + key).addClass('is-invalid')
                                    .after('<div class="invalid-feedback d-block">' + value[0] + '</div>');
                            });
                        }
                    }
                },
                error: function(xhr) {
                    // Tratar erros de AJAX ou servidor
                    alert('Erro ao conectar com o servidor para salvar o cliente.');
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        // Limpa erros anteriores
                        $('#formNovoCliente .invalid-feedback').remove();
                        $('#formNovoCliente .is-invalid').removeClass('is-invalid');
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            $('#modal_' + key).addClass('is-invalid')
                                .after('<div class="invalid-feedback d-block">' + value[0] + '</div>');
                        });
                    }
                },
                complete: function() {
                    $('#btnSalvarNovoCliente').prop('disabled', false).html('Salvar Cliente');
                }
            });
        });
        // LÓGICA DAS MÁSCARAS PARA OS CAMPOS DO MODAL
        // Garanta que o jQuery Mask Plugin já foi carregado antes deste script.
        if ($.fn.mask) { // Verifica se o plugin de máscara está carregado
            // Máscara para CPF/CNPJ no MODAL
            var CpfCnpjMaskBehaviorModal = function(val) {
                    return val.replace(/\D/g, '').length <= 11 ? '000.000.000-009' : '00.000.000/0000-00';
                },
                cpfCnpjOptionsModal = {
                    onKeyPress: function(val, e, field, options) {
                        field.mask(CpfCnpjMaskBehaviorModal.apply({}, arguments), options);
                    }
                };
            $('#modal_cpf_cnpj').mask(CpfCnpjMaskBehaviorModal, cpfCnpjOptionsModal);

            // Máscara para Telefone no MODAL (com DDD e 9º dígito)
            var SPMaskBehaviorModal = function(val) {
                    return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
                },
                spOptionsModal = {
                    onKeyPress: function(val, e, field, options) {
                        field.mask(SPMaskBehaviorModal.apply({}, arguments), options);
                    }
                };
            $('#modal_telefone').mask(SPMaskBehaviorModal, spOptionsModal);

            // Máscara para CEP no MODAL
            $('#modal_cep').mask('00000-000');
        } else {
            console.warn('jQuery Mask Plugin não está carregado. As máscaras do modal não funcionarão.');
        }


        // ViaCEP para o modal (já deve estar aqui de alguma forma)
        $('#modal_cep').on('blur', function() {
            var cep = $(this).val().replace(/\D/g, '');
            if (cep.length === 8) {
                // Mostra campos de endereço se estiverem ocultos
                $(this).closest('form').find('#endereco_modal_fields').slideDown();
                $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {
                    if (!("erro" in dados)) {
                        $('#modal_logradouro').val(dados.logradouro);
                        // Você precisaria adicionar inputs para bairro, cidade, estado no modal
                        // e preenchê-los aqui:
                        // $('#modal_bairro').val(dados.bairro);
                        // $('#modal_cidade').val(dados.localidade);
                        // $('#modal_estado').val(dados.uf);
                        $('#modal_numero').focus();
                    } else {
                        // alert("CEP não encontrado no modal.");
                        // Limpar campos se o CEP for inválido
                        $('#modal_logradouro').val('');
                        // ...limpar outros campos de endereço...
                    }
                });
            } else {
                $(this).closest('form').find('#endereco_modal_fields').slideUp(); // Esconde se o CEP não for válido
            }
        });

        // Limpar formulário do modal quando ele é fechado
        $('#modalNovoCliente').on('hidden.bs.modal', function() {
            $('#formNovoCliente')[0].reset();
            $('#formNovoCliente .invalid-feedback').remove();
            $('#formNovoCliente .is-invalid').removeClass('is-invalid');
            $('#formNovoCliente').find('#endereco_modal_fields').hide(); // Garante que os campos de endereço fiquem ocultos
        });
    });
</script>
@endpush
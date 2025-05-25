@extends('layouts.app')

@section('title', 'Editar Cliente: ' . $cliente->nome_completo)

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Estilo para ocultar a seção de endereço inicialmente se o CEP não estiver preenchido */
        #endereco_fields_edit {
            display:
                {{ old('cep', $cliente->cep) ? 'block' : 'none' }}
            ;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-0">
        <h1><i class="bi bi-pencil-square"></i> Editar Cliente: <span
                class="text-primary">{{ $cliente->nome_completo }}</span></h1>

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
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        {{-- Adicione outros feedbacks de sessão se necessário --}}

        <form action="{{ route('clientes.update', $cliente->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Card de Dados Principais --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-person-vcard"></i> Dados Principais</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nome_completo" class="form-label">Nome Completo <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nome_completo') is-invalid @enderror"
                            id="nome_completo" name="nome_completo"
                            value="{{ old('nome_completo', $cliente->nome_completo) }}" required>
                        @error('nome_completo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cpf_cnpj" class="form-label">CPF/CNPJ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('cpf_cnpj') is-invalid @enderror" id="cpf_cnpj"
                                name="cpf_cnpj" value="{{ old('cpf_cnpj', $cliente->cpf_cnpj) }}" required>
                            @error('cpf_cnpj') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control @error('telefone') is-invalid @enderror" id="telefone"
                                name="telefone" value="{{ old('telefone', $cliente->telefone) }}">
                            @error('telefone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email (Opcional)</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email', $cliente->email) }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Card de Endereço --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-geo-alt-fill"></i> Endereço (Opcional)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="cep_edit" class="form-label">CEP</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('cep') is-invalid @enderror" id="cep_edit"
                                    name="cep" value="{{ old('cep', $cliente->cep) }}">
                                <button class="btn btn-outline-secondary" type="button" id="btnBuscarCepEdit"><i
                                        class="bi bi-search"></i></button>
                            </div>
                            <small id="cep_status_edit" class="form-text"></small>
                            @error('cep') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div id="endereco_fields_edit"
                        style="{{ old('cep', $cliente->cep) ? 'display:block;' : 'display:none;' }}">
                        <div class="row">
                            <div class="col-md-9 mb-3">
                                <label for="logradouro_edit" class="form-label">Logradouro</label>
                                <input type="text" class="form-control @error('logradouro') is-invalid @enderror"
                                    id="logradouro_edit" name="logradouro"
                                    value="{{ old('logradouro', $cliente->logradouro) }}">
                                @error('logradouro') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="numero_edit" class="form-label">Número</label>
                                <input type="text" class="form-control @error('numero') is-invalid @enderror"
                                    id="numero_edit" name="numero" value="{{ old('numero', $cliente->numero) }}">
                                @error('numero') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="complemento_edit" class="form-label">Complemento</label>
                            <input type="text" class="form-control @error('complemento') is-invalid @enderror"
                                id="complemento_edit" name="complemento"
                                value="{{ old('complemento', $cliente->complemento) }}">
                            @error('complemento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="bairro_edit" class="form-label">Bairro</label>
                                <input type="text" class="form-control @error('bairro') is-invalid @enderror"
                                    id="bairro_edit" name="bairro" value="{{ old('bairro', $cliente->bairro) }}">
                                @error('bairro') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="cidade_edit" class="form-label">Cidade</label>
                                <input type="text" class="form-control @error('cidade') is-invalid @enderror"
                                    id="cidade_edit" name="cidade" value="{{ old('cidade', $cliente->cidade) }}">
                                @error('cidade') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="estado_edit" class="form-label">Estado</label>
                                <input type="text" class="form-control @error('estado') is-invalid @enderror"
                                    id="estado_edit" name="estado" maxlength="2"
                                    value="{{ old('estado', $cliente->estado) }}">
                                @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> Atualizar Cliente</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    {{-- jQuery e jQuery Mask Plugin já devem estar no layouts.app.blade.php --}}
    {{-- Se não estiverem, inclua-os aqui ou no layout principal:
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    --}}
    <script>
        $(document).ready(function () {
            // Máscara para CPF/CNPJ (no formulário de EDIÇÃO)
            var CpfCnpjMaskBehaviorEdit = function (val) {
                return val.replace(/\D/g, '').length <= 11 ? '000.000.000-009' : '00.000.000/0000-00';
            },
                cpfCnpjOptionsEdit = {
                    onKeyPress: function (val, e, field, options) {
                        field.mask(CpfCnpjMaskBehaviorEdit.apply({}, arguments), options);
                    }
                };
            $('#cpf_cnpj').mask(CpfCnpjMaskBehaviorEdit, cpfCnpjOptionsEdit); // Assumindo que o ID é 'cpf_cnpj'. Se você mudou para 'cpf_cnpj_edit', ajuste aqui.

            // Máscara para Telefone (no formulário de EDIÇÃO)
            var SPMaskBehaviorEdit = function (val) {
                return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
            },
                spOptionsEdit = {
                    onKeyPress: function (val, e, field, options) {
                        field.mask(SPMaskBehaviorEdit.apply({}, arguments), options);
                    }
                };
            $('#telefone').mask(SPMaskBehaviorEdit, spOptionsEdit); // CORRIGIDO AQUI e assumindo ID 'telefone'. Se for 'telefone_edit', ajuste.

            // Máscara para CEP (no formulário de EDIÇÃO)
            $('#cep_edit').mask('00000-000');

            // Função para limpar campos de endereço (para edit)
            function limparCamposEnderecoEdit() {
                $('#logradouro_edit').val('');
                $('#bairro_edit').val('');
                $('#cidade_edit').val('');
                $('#estado_edit').val('');
                $('#numero_edit').val('');
                $('#complemento_edit').val('');
            }

            // Função para buscar CEP (para edit)
            function buscarCepEdit() {
                var cep = $('#cep_edit').val().replace(/\D/g, '');
                var cepStatus = $('#cep_status_edit');
                var enderecoFields = $('#endereco_fields_edit');

                if (cep.length === 8) {
                    cepStatus.html('<i class="bi bi-hourglass-split"></i> Buscando CEP...').removeClass('text-danger text-success text-warning');

                    $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function (dados) {
                        if (!("erro" in dados)) {
                            $('#logradouro_edit').val(dados.logradouro);
                            $('#bairro_edit').val(dados.bairro);
                            $('#cidade_edit').val(dados.localidade);
                            $('#estado_edit').val(dados.uf);
                            enderecoFields.slideDown();
                            $('#numero_edit').focus();
                            cepStatus.html('<i class="bi bi-check-circle-fill text-success"></i> CEP encontrado!').addClass('text-success');
                        } else {
                            cepStatus.html('<i class="bi bi-x-circle-fill text-danger"></i> CEP não encontrado. Preencha manualmente.').addClass('text-danger');
                            enderecoFields.slideDown();
                            limparCamposEnderecoEdit(); // Limpa para preenchimento manual
                            $('#logradouro_edit').focus();
                        }
                    }).fail(function () {
                        cepStatus.html('<i class="bi bi-exclamation-triangle-fill text-danger"></i> Erro ao buscar CEP. Preencha manualmente.').addClass('text-danger');
                        enderecoFields.slideDown();
                    });
                } else if (cep.length > 0 && cep.length < 8) {
                    cepStatus.html('<i class="bi bi-exclamation-circle-fill text-warning"></i> CEP inválido.').removeClass('text-danger text-success').addClass('text-warning');
                } else if (cep.length === 0) {
                    cepStatus.html('');
                }
            }

            $('#cep_edit').on('blur', buscarCepEdit);
            $('#btnBuscarCepEdit').on('click', buscarCepEdit);

            // Se já houver um CEP preenchido na edição, e os campos de endereço estiverem vazios,
            // tenta buscar o CEP ao carregar a página.
            if ($('#cep_edit').val().replace(/\D/g, '').length === 8 && $('#logradouro_edit').val() === '') {
                buscarCepEdit();
            } else if ($('#cep_edit').val().replace(/\D/g, '').length > 0) {
                // Se já tem CEP e campos de endereço (provavelmente do old() ou do DB),
                // apenas garante que os campos de endereço estejam visíveis.
                $('#endereco_fields_edit').show();
            }
        });
    </script>
@endpush
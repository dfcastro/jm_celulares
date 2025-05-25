@extends('layouts.app')

@section('title', 'Cadastro de Novo Cliente')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Estilo para ocultar a seção de endereço inicialmente */
        #endereco_fields {
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-0">
        <h1><i class="bi bi-person-plus-fill"></i> Novo Cliente</h1>

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

        <form action="{{ route('clientes.store') }}" method="POST">
            @csrf

            {{-- Card de Dados Principais --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-person-vcard"></i> Dados Principais</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nome_completo" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nome_completo') is-invalid @enderror" id="nome_completo" name="nome_completo" value="{{ old('nome_completo') }}" required>
                        @error('nome_completo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cpf_cnpj" class="form-label">CPF/CNPJ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('cpf_cnpj') is-invalid @enderror" id="cpf_cnpj" name="cpf_cnpj" value="{{ old('cpf_cnpj') }}" required>
                            @error('cpf_cnpj') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control @error('telefone') is-invalid @enderror" id="telefone" name="telefone" value="{{ old('telefone') }}">
                            @error('telefone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email (Opcional)</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
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
                            <label for="cep" class="form-label">CEP</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('cep') is-invalid @enderror" id="cep" name="cep" value="{{ old('cep') }}">
                                <button class="btn btn-outline-secondary" type="button" id="btnBuscarCep"><i class="bi bi-search"></i></button>
                            </div>
                            <small id="cep_status" class="form-text"></small>
                            @error('cep') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div id="endereco_fields"> {{-- Campos de endereço que serão exibidos após busca de CEP --}}
                        <div class="row">
                            <div class="col-md-9 mb-3">
                                <label for="logradouro" class="form-label">Logradouro</label>
                                <input type="text" class="form-control @error('logradouro') is-invalid @enderror" id="logradouro" name="logradouro" value="{{ old('logradouro') }}">
                                @error('logradouro') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="numero" class="form-label">Número</label>
                                <input type="text" class="form-control @error('numero') is-invalid @enderror" id="numero" name="numero" value="{{ old('numero') }}">
                                @error('numero') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="complemento" class="form-label">Complemento</label>
                            <input type="text" class="form-control @error('complemento') is-invalid @enderror" id="complemento" name="complemento" value="{{ old('complemento') }}">
                            @error('complemento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="bairro" class="form-label">Bairro</label>
                                <input type="text" class="form-control @error('bairro') is-invalid @enderror" id="bairro" name="bairro" value="{{ old('bairro') }}">
                                @error('bairro') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="cidade" class="form-label">Cidade</label>
                                <input type="text" class="form-control @error('cidade') is-invalid @enderror" id="cidade" name="cidade" value="{{ old('cidade') }}">
                                @error('cidade') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <input type="text" class="form-control @error('estado') is-invalid @enderror" id="estado" name="estado" maxlength="2" value="{{ old('estado') }}">
                                @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> Salvar Cliente</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    {{-- jQuery e jQuery Mask Plugin já devem estar no layouts.app.blade.php --}}
    <script>
        $(document).ready(function(){
            // Máscaras
            var CpfCnpjMaskBehavior = function (val) {
                return val.replace(/\D/g, '').length <= 11 ? '000.000.000-009' : '00.000.000/0000-00';
            },
            cpfCnpjOptions = {
                onKeyPress: function(val, e, field, options) {
                    field.mask(CpfCnpjMaskBehavior.apply({}, arguments), options);
                }
            };
            $('#cpf_cnpj').mask(CpfCnpjMaskBehavior, cpfCnpjOptions);

            var SPMaskBehavior = function (val) {
              return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
            },
            spOptions = {
              onKeyPress: function(val, e, field, options) {
                  field.mask(SPMaskBehavior.apply({}, arguments), options);
              }
            };
            $('#telefone').mask(SPMaskBehavior, spOptions);
            $('#cep').mask('00000-000');

            // Função para limpar campos de endereço
            function limparCamposEndereco() {
                $('#logradouro').val('');
                $('#bairro').val('');
                $('#cidade').val('');
                $('#estado').val('');
                $('#numero').val('');
                $('#complemento').val('');
            }

            // Função para buscar CEP
            function buscarCep() {
                var cep = $('#cep').val().replace(/\D/g, '');
                var cepStatus = $('#cep_status');
                var enderecoFields = $('#endereco_fields');

                if (cep.length === 8) {
                    cepStatus.html('<i class="bi bi-hourglass-split"></i> Buscando CEP...').removeClass('text-danger text-success');
                    limparCamposEndereco(); // Limpa antes de nova busca

                    $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function (dados) {
                        if (!("erro" in dados)) {
                            $('#logradouro').val(dados.logradouro);
                            $('#bairro').val(dados.bairro);
                            $('#cidade').val(dados.localidade);
                            $('#estado').val(dados.uf);
                            enderecoFields.slideDown(); // Exibe os campos
                            $('#numero').focus();
                            cepStatus.html('<i class="bi bi-check-circle-fill text-success"></i> CEP encontrado!').addClass('text-success');
                        } else {
                            cepStatus.html('<i class="bi bi-x-circle-fill text-danger"></i> CEP não encontrado.').addClass('text-danger');
                            enderecoFields.slideUp(); // Mantém oculto ou oculta se já estava visível
                        }
                    }).fail(function() {
                        cepStatus.html('<i class="bi bi-exclamation-triangle-fill text-danger"></i> Erro ao buscar CEP. Tente novamente.').addClass('text-danger');
                        enderecoFields.slideUp();
                    });
                } else if (cep.length > 0 && cep.length < 8) {
                    cepStatus.html('<i class="bi bi-exclamation-circle-fill text-warning"></i> CEP inválido (precisa de 8 dígitos).').removeClass('text-danger text-success').addClass('text-warning');
                    enderecoFields.slideUp();
                    limparCamposEndereco();
                } else {
                    cepStatus.html('');
                    enderecoFields.slideUp();
                     // Não limpar se o usuário apenas apagou, pode ser que ele vá digitar de novo.
                }
            }

            // Evento para buscar CEP ao perder o foco ou clicar no botão
            $('#cep').on('blur', buscarCep);
            $('#btnBuscarCep').on('click', buscarCep);

            // Se já houver um CEP preenchido (ex: old input), tenta buscar
            if ($('#cep').val().replace(/\D/g, '').length === 8) {
                buscarCep();
            }
        });
    </script>
@endpush
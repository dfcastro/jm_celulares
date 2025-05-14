@extends('layouts.app') {{-- Estende o layout base --}}

@section('title', 'Cadastro de Clientes') {{-- Título da página --}}

@section('content') {{-- Conteúdo principal da página --}}
    <div class="container mt-0"> {{-- Removi mt-5 pois o layout já tem padding --}}
        <h1>Novo Cliente</h1>

        {{-- Bloco para exibir erros de validação --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('clientes.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="nome_completo" class="form-label">Nome Completo</label>
                <input type="text" class="form-control" id="nome_completo" name="nome_completo" value="{{ old('nome_completo') }}" required>
                 @error('nome_completo') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="cpf_cnpj" class="form-label">CPF/CNPJ</label>
                <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" value="{{ old('cpf_cnpj') }}" required>
                 @error('cpf_cnpj') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="cep" class="form-label">CEP</label>
                    <input type="text" class="form-control" id="cep" name="cep" value="{{ old('cep') }}">
                    @error('cep') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="logradouro" class="form-label">Logradouro</label>
                    <input type="text" class="form-control" id="logradouro" name="logradouro" value="{{ old('logradouro') }}">
                    @error('logradouro') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2 mb-3">
                    <label for="numero" class="form-label">Número</label>
                    <input type="text" class="form-control" id="numero" name="numero" value="{{ old('numero') }}">
                    @error('numero') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="mb-3">
                <label for="complemento" class="form-label">Complemento (Opcional)</label>
                <input type="text" class="form-control" id="complemento" name="complemento" value="{{ old('complemento') }}">
                @error('complemento') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="bairro" class="form-label">Bairro</label>
                    <input type="text" class="form-control" id="bairro" name="bairro" value="{{ old('bairro') }}">
                    @error('bairro') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="cidade" class="form-label">Cidade</label>
                    <input type="text" class="form-control" id="cidade" name="cidade" value="{{ old('cidade') }}">
                    @error('cidade') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-2 mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <input type="text" class="form-control" id="estado" name="estado" maxlength="2" value="{{ old('estado') }}">
                    @error('estado') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="mb-3">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="text" class="form-control" id="telefone" name="telefone" value="{{ old('telefone') }}">
                 @error('telefone') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                 @error('email') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-primary">Salvar Cliente</button>
            <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

   {{-- Inclua jQuery (se não estiver incluído) e jQuery Mask Plugin --}}
  
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    

    <script>
        $(document).ready(function(){
            // Máscara para CPF/CNPJ
            var CpfCnpjMaskBehavior = function (val) {
                return val.replace(/\D/g, '').length <= 11 ? '000.000.000-009' : '00.000.000/0000-00';
            },
            cpfCnpjOptions = {
                onKeyPress: function(val, e, field, options) {
                    field.mask(CpfCnpjMaskBehavior.apply({}, arguments), options);
                }
            };
            $('#cpf_cnpj').mask(CpfCnpjMaskBehavior, cpfCnpjOptions);

            // Máscara para Telefone (com DDD e 9º dígito)
            var SPMaskBehavior = function (val) {
              return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
            },
            spOptions = {
              onKeyPress: function(val, e, field, options) {
                  field.mask(SPMaskBehavior.apply({}, arguments), options);
              }
            };
            $('#telefone').mask(SPMaskBehavior, spOptions);

            // NOVO: Máscara para CEP
            $('#cep').mask('00000-000');

            // NOVO: Autocomplete de Endereço por CEP
            $('#cep').on('blur', function() { // Quando o campo CEP perde o foco
                var cep = $(this).val().replace(/\D/g, ''); // Remove caracteres não numéricos

                if (cep.length === 8) { // Verifica se o CEP tem 8 dígitos
                    // Consulta a API ViaCEP
                    $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {
                        if (!("erro" in dados)) { // Se não houver erro na consulta
                            // Preenche os campos do formulário com os dados retornados
                            $('#logradouro').val(dados.logradouro);
                            $('#bairro').val(dados.bairro);
                            $('#cidade').val(dados.localidade);
                            $('#estado').val(dados.uf);
                            $('#numero').focus(); // Foca no campo número para o usuário preencher
                        } else {
                            // CEP não encontrado ou inválido
                            alert("CEP não encontrado ou inválido.");
                            // Limpa os campos de endereço se o CEP for inválido
                            $('#logradouro').val('');
                            $('#bairro').val('');
                            $('#cidade').val('');
                            $('#estado').val('');
                            $('#cep').focus(); // Foca de volta no CEP
                        }
                    });
                } else if (cep.length > 0) {
                    alert("CEP inválido. Digite 8 dígitos.");
                }
            });
        });
    </script>
    @endpush
{{-- Para esta página, não precisamos de @push('styles') ou @push('scripts') geralmente --}}
{{-- Mas se você fosse adicionar ícones do Bootstrap, por exemplo: --}}
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush
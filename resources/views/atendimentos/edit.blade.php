@extends('layouts.app')

@section('title', 'Editar Atendimento #' . $atendimento->id)

@section('content')
<div class="container mt-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Editar Atendimento #{{ $atendimento->id }}</h1>
        <div>
            <a href="{{ route('atendimentos.show', $atendimento->id) }}" class="btn btn-info btn-sm"><i class="bi bi-eye"></i> Ver Detalhes</a>
            <a href="{{ route('atendimentos.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-list-ul"></i> Voltar para Lista</a>
        </div>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="bi bi-exclamation-triangle-fill"></i> Ops!</strong> Verifique os erros abaixo:
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('atendimentos.update', $atendimento->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                Dados do Atendimento
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cliente_id" class="form-label">Cliente <span class="text-danger">*</span></label>
                        <select class="form-select @error('cliente_id') is-invalid @enderror" id="cliente_id" name="cliente_id" required>
                            <option value="">Selecione o Cliente</option>
                            @foreach ($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ old('cliente_id', $atendimento->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nome_completo }} ({{ $cliente->cpf_cnpj }})
                            </option>
                            @endforeach
                        </select>
                        @error('cliente_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="descricao_aparelho" class="form-label">Aparelho e Defeito Inicial <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('descricao_aparelho') is-invalid @enderror" id="descricao_aparelho" name="descricao_aparelho"
                            value="{{ old('descricao_aparelho', $atendimento->descricao_aparelho) }}"
                            placeholder="Ex: iPhone X tela trincada, Samsung S21 não liga" required>
                        @error('descricao_aparelho')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="problema_relatado" class="form-label">Problema Relatado <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('problema_relatado') is-invalid @enderror" id="problema_relatado" name="problema_relatado" rows="3" required>{{ old('problema_relatado', $atendimento->problema_relatado) }}</textarea>
                    @error('problema_relatado')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="data_entrada_display" class="form-label">Data de Entrada (Registrada)</label>
                        <input type="text" class="form-control" id="data_entrada_display"
                            value="{{ $atendimento->data_entrada ? $atendimento->data_entrada->format('d/m/Y H:i:s') : 'N/A' }}"
                            readonly disabled>
                        <small class="form-text text-muted">A data de entrada original não pode ser alterada.</small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">Status do Serviço <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            @foreach (App\Models\Atendimento::getPossibleStatuses() as $s) {{-- Usa o método atualizado --}}
                            <option value="{{ $s }}" {{ old('status', $atendimento->status) == $s ? 'selected' : '' }}>
                                {{ $s }}
                            </option>
                            @endforeach
                        </select>
                        @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="status_pagamento" class="form-label">Status do Pagamento <span class="text-danger">*</span></label>
                        <select class="form-select @error('status_pagamento') is-invalid @enderror" id="status_pagamento" name="status_pagamento" required>
                            @foreach (App\Models\Atendimento::getPossiblePaymentStatuses() as $sp)
                            <option value="{{ $sp }}" {{ old('status_pagamento', $atendimento->status_pagamento) == $sp ? 'selected' : '' }}>
                                {{ $sp }}
                            </option>
                            @endforeach
                        </select>
                        @error('status_pagamento')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="tecnico_id" class="form-label">Técnico Responsável</label>
                        <select class="form-select @error('tecnico_id') is-invalid @enderror" id="tecnico_id" name="tecnico_id">
                            <option value="">Não atribuído</option>
                            @foreach ($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id }}" {{ old('tecnico_id', $atendimento->tecnico_id) == $tecnico->id ? 'selected' : '' }}>
                                {{ $tecnico->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('tecnico_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="data_conclusao" class="form-label">Data de Conclusão (Opcional)</label>
                    <input type="date" class="form-control @error('data_conclusao') is-invalid @enderror" id="data_conclusao" name="data_conclusao"
                        value="{{ old('data_conclusao', $atendimento->data_conclusao ? $atendimento->data_conclusao->format('Y-m-d') : '') }}">
                    @error('data_conclusao')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="observacoes" class="form-label">Observações (Internas/Gerais)</label>
                    <textarea class="form-control @error('observacoes') is-invalid @enderror" id="observacoes" name="observacoes" rows="3">{{ old('observacoes', $atendimento->observacoes) }}</textarea>
                    @error('observacoes')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- CAMPO LAUDO TÉCNICO --}}
                <div class="mb-3">
                    <label for="laudo_tecnico" class="form-label">Laudo Técnico / Solução Aplicada</label>
                    <textarea class="form-control @error('laudo_tecnico') is-invalid @enderror" id="laudo_tecnico" name="laudo_tecnico" rows="5"
                        {{ Gate::denies('is-admin-or-tecnico') ? 'readonly' : '' }}>{{ old('laudo_tecnico', $atendimento->laudo_tecnico) }}</textarea>
                    @error('laudo_tecnico')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @cannot('is-admin-or-tecnico')
                    @if($atendimento->laudo_tecnico)
                    <small class="form-text text-muted">Apenas administradores ou técnicos podem editar o laudo.</small>
                    @endif
                    @endcannot
                </div>

                {{-- Campos de Valor --}}
                <hr>
                <h5 class="mb-3">Valores do Serviço</h5>
                <div class="row">
                    <div class="mb-3">
                        <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                        <select class="form-select @error('forma_pagamento') is-invalid @enderror" id="forma_pagamento" name="forma_pagamento">
                            <option value="">Selecione se o pagamento foi realizado</option>
                            {{-- Você pode popular estas opções a partir de uma configuração ou enum --}}
                            @php
                            // Obter as mesmas formas de pagamento usadas na Venda de Acessórios
                            // Supondo que $formasPagamento seja passado pelo controller, ou defina aqui.
                            // Se $formasPagamento não estiver disponível aqui, você precisa passá-la
                            // do AtendimentoController@edit, assim como fez no VendaAcessorioController@create.
                            // Por enquanto, vou colocar algumas opções fixas como exemplo.
                            // O ideal é ter uma fonte única para estas opções.
                            $opcoesPagamento = ['Dinheiro', 'Cartão de Débito', 'Cartão de Crédito', 'PIX', 'Boleto', 'Outro'];
                            @endphp
                            @foreach($opcoesPagamento as $opcao)
                            <option value="{{ $opcao }}" {{ old('forma_pagamento', $atendimento->forma_pagamento) == $opcao ? 'selected' : '' }}>
                                {{ $opcao }}
                            </option>
                            @endforeach
                        </select>
                        @error('forma_pagamento')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="valor_servico" class="form-label">Valor do Serviço (Mão de Obra) R$</label>
                        <input type="number" step="0.01" class="form-control @error('valor_servico') is-invalid @enderror" id="valor_servico" name="valor_servico"
                            value="{{ old('valor_servico', $atendimento->valor_servico ?? '0.00') }}" min="0"
                            {{ Gate::denies('is-admin') ? 'readonly' : '' }}>
                        @error('valor_servico')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @cannot('is-admin') <small class="form-text text-muted">Apenas administradores podem alterar valores.</small> @endcannot
                    </div>
                    <div class="col-md-6 mb-3"> {{-- Este bloco pode conter a linha 121 --}}
                        <label for="desconto_servico" class="form-label">Desconto sobre Serviço R$ (Opcional)</label>
                        <input type="number" step="0.01" class="form-control @error('desconto_servico') is-invalid @enderror" id="desconto_servico" name="desconto_servico"
                            value="{{ old('desconto_servico', $atendimento->desconto_servico ?? '0.00') }}" min="0"
                            {{ Gate::denies('is-admin') ? 'readonly' : '' }}>
                        @error('desconto_servico')
                        <div class="invalid-feedback">{{ $message }}</div> {{-- Se a linha 121 for esta, está correta --}}
                        @enderror
                        @cannot('is-admin') <small class="form-text text-muted">Apenas administradores podem aplicar descontos.</small> @endcannot
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Salvar Alterações</button>
                    <a href="{{ route('atendimentos.show', $atendimento->id) }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </div> {{-- Fim card-body --}}
        </div> {{-- Fim card --}}
    </form>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush
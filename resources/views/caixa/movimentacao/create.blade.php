@extends('layouts.app')

@php
    $tituloPagina = ($tipo === 'entrada') ? 'Registrar Suprimento/Entrada Avulsa' : 'Registrar Sangria/Despesa';
    $tipoMovimentacaoValor = ($tipo === 'entrada') ? 'ENTRADA' : 'SAIDA';
    $corCardHeader = ($tipo === 'entrada') ? 'bg-success' : 'bg-danger';
    $textoBotao = ($tipo === 'entrada') ? 'Registrar Entrada' : 'Registrar Saída';
    $iconeBotao = ($tipo === 'entrada') ? 'bi-plus-circle-fill' : 'bi-dash-circle-fill';
@endphp

@section('title', $tituloPagina . ' no Caixa #' . $caixa->id)

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header {{ $corCardHeader }} text-white">
                    <h4 class="mb-0">{{ $tituloPagina }} (Caixa #{{ $caixa->id }})</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('caixa.movimentacao.store', $caixa->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="tipo_movimentacao" value="{{ $tipoMovimentacaoValor }}">

                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('descricao') is-invalid @enderror"
                                   id="descricao" name="descricao" value="{{ old('descricao') }}" required
                                   placeholder="{{ $tipo === 'entrada' ? 'Ex: Reforço de troco, Aporte do proprietário' : 'Ex: Pagamento de conta de água, Retirada para depósito' }}">
                            @error('descricao')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="valor" class="form-label">Valor <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" class="form-control @error('valor') is-invalid @enderror"
                                       id="valor" name="valor" value="{{ old('valor') }}"
                                       step="0.01" min="0.01" required
                                       aria-label="Valor em reais">
                            </div>
                            @error('valor')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        @if ($tipo === 'saida') {{-- Forma de pagamento mais relevante para saídas/despesas --}}
                        <div class="mb-3">
                            <label for="forma_pagamento" class="form-label">Forma de Pagamento/Retirada (Opcional)</label>
                            <select class="form-select @error('forma_pagamento') is-invalid @enderror" id="forma_pagamento" name="forma_pagamento">
                                <option value="">Selecione, se aplicável</option>
                                <option value="Dinheiro" {{ old('forma_pagamento') == 'Dinheiro' ? 'selected' : '' }}>Dinheiro</option>
                                <option value="Transferência Bancária" {{ old('forma_pagamento') == 'Transferência Bancária' ? 'selected' : '' }}>Transferência Bancária</option>
                                <option value="Débito Conta" {{ old('forma_pagamento') == 'Débito Conta' ? 'selected' : '' }}>Débito em Conta</option>
                                <option value="Outro" {{ old('forma_pagamento') == 'Outro' ? 'selected' : '' }}>Outro</option>
                            </select>
                            @error('forma_pagamento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @else {{-- Para entradas de suprimento, geralmente é Dinheiro --}}
                            <input type="hidden" name="forma_pagamento" value="Dinheiro">
                        @endif


                        <div class="mb-3">
                            <label for="observacoes" class="form-label">Observações (Opcional)</label>
                            <textarea class="form-control @error('observacoes') is-invalid @enderror"
                                      id="observacoes" name="observacoes"
                                      rows="3">{{ old('observacoes') }}</textarea>
                            @error('observacoes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="{{ route('caixa.show', $caixa->id) }}" class="btn btn-secondary me-md-2">Cancelar</a>
                            <button type="submit" class="btn {{ $tipo === 'entrada' ? 'btn-success' : 'btn-danger' }}">
                                <i class="bi {{ $iconeBotao }} me-2"></i>{{ $textoBotao }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> --}}
@endpush
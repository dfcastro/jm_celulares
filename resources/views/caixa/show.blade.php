@extends('layouts.app')

@section('title', 'Detalhes do Caixa #' . $caixa->id)

@section('content')
    <div class="container mt-4">
        <div class="card">
            <div class="card-header {{ $caixa->estaAberto() ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Detalhes do Caixa #{{ $caixa->id }}</h4>
                    <span class="badge bg-light text-dark fs-6">Status: {{ $caixa->status }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Data de Abertura:</strong> {{ $caixa->data_abertura_formatada }}</p>
                        <p><strong>Aberto por:</strong> {{ $caixa->usuarioAbertura->name ?? 'N/A' }}</p>
                        <p><strong>Saldo Inicial:</strong> R$ {{ number_format($caixa->saldo_inicial, 2, ',', '.') }}</p>
                        @if($caixa->observacoes_abertura)
                            <p><strong>Obs. Abertura:</strong> {{ nl2br(e($caixa->observacoes_abertura)) }}</p>
                        @endif
                    </div>
                    @if(!$caixa->estaAberto())
                        <div class="col-md-6">
                            <p><strong>Data de Fechamento:</strong> {{ $caixa->data_fechamento_formatada ?? '-' }}</p>
                            <p><strong>Fechado por:</strong> {{ $caixa->usuarioFechamento->name ?? 'N/A' }}</p>
                            <p><strong>Saldo Calculado:</strong> R$
                                {{ number_format($caixa->saldo_final_calculado ?? 0, 2, ',', '.') }}</p>
                            <p><strong>Saldo Informado:</strong> R$
                                {{ number_format($caixa->saldo_final_informado ?? 0, 2, ',', '.') }}</p>
                            <p class="fw-bold"><strong>Diferença:</strong>
                                <span
                                    class="{{ ($caixa->diferenca ?? 0) == 0 ? 'text-success' : (($caixa->diferenca ?? 0) > 0 ? 'text-warning' : 'text-danger') }}">
                                    R$ {{ number_format($caixa->diferenca ?? 0, 2, ',', '.') }}
                                </span>
                            </p>
                            @if($caixa->observacoes_fechamento)
                                <p><strong>Obs. Fechamento:</strong> {{ nl2br(e($caixa->observacoes_fechamento)) }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <hr>

                <h5>Movimentações do Caixa</h5>
                @if($caixa->movimentacoes->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Data/Hora</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                    <th class="text-end">Valor (R$)</th>
                                    <th>Forma Pag.</th>
                                    <th>Usuário</th>
                                    <th>Obs.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $saldoAtual = $caixa->saldo_inicial; @endphp
                                @foreach($caixa->movimentacoes as $index => $movimentacao)
                                    {{-- A primeira movimentação (suprimento inicial) não altera o saldo que já é o inicial --}}
                                    @if($index > 0 || $movimentacao->descricao !== 'Suprimento Inicial (Abertura de Caixa)')
                                        @php
                                            $saldoAtual += ($movimentacao->tipo === 'ENTRADA' ? $movimentacao->valor : -$movimentacao->valor);
                                        @endphp
                                    @endif
                                    <tr>
                                        <td>{{ $movimentacao->id }}</td>
                                        <td>{{ $movimentacao->data_movimentacao_formatada }}</td>
                                        <td>
                                            @if($movimentacao->tipo === 'ENTRADA')
                                                <span class="badge bg-success">ENTRADA</span>
                                            @else
                                                <span class="badge bg-danger">SAÍDA</span>
                                            @endif
                                        </td>
                                        <td>{{ $movimentacao->descricao }}</td>
                                        <td class="text-end">{{ number_format($movimentacao->valor, 2, ',', '.') }}</td>
                                        <td>{{ $movimentacao->forma_pagamento ?? '-' }}</td>
                                        <td>{{ $movimentacao->usuario->name ?? 'N/A' }}</td>
                                        <td>{{ $movimentacao->observacoes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-group-divider">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">SALDO ATUAL CALCULADO EM CAIXA:</td>
                                    <td class="text-end fw-bold">R$ {{ number_format($saldoAtual, 2, ',', '.') }}</td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <p class="text-center text-muted">Nenhuma movimentação registrada para este caixa ainda.</p>
                @endif

                <div class="mt-4 d-flex flex-wrap gap-2 justify-content-start align-items-center border-top pt-3">
                    @if($caixa->estaAberto() && Gate::allows('gerenciar-caixa'))
                        <a href="{{ route('caixa.movimentacao.create', ['caixa' => $caixa->id, 'tipo' => 'entrada']) }}"
                            class="btn btn-success">
                            <i class="bi bi-plus-circle me-1"></i> Registrar Suprimento/Entrada
                        </a>
                        <a href="{{ route('caixa.movimentacao.create', ['caixa' => $caixa->id, 'tipo' => 'saida']) }}"
                            class="btn btn-warning">
                            <i class="bi bi-dash-circle me-1"></i> Registrar Sangria/Saída
                        </a>
                        {{-- O botão de fechar caixa virá depois --}}
                        {{-- <a href="{{ route('caixa.editFechar', $caixa->id) }}" class="btn btn-danger">
                            <i class="bi bi-lock me-1"></i> Fechar Caixa
                        </a> --}}
                       

                    @elseif (!$caixa->estaAberto())
                        <span
                            class="badge bg-info-subtle border border-info-subtle text-info-emphasis rounded-pill px-3 py-2">Este
                            caixa está fechado. Nenhuma outra operação pode ser realizada.</span>
                    @endif
                </div>
                <div class="mt-3">
                    <a href="{{ route('caixa.index') }}" class="btn btn-secondary"><i class="bi bi-list-ul me-1"></i>
                        Histórico de Caixas</a>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('styles')
    {{-- Se você usa Bootstrap Icons e ele não está global --}}
    {{--
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> --}}
@endpush
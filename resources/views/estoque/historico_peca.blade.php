@extends('layouts.app')

@section('title', 'Histórico de Movimentação: ' . $estoque->nome) {{-- Título da página atualizado --}}

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .movimentacao-entrada {
            /* color: #0f5132; */
            /* background-color: #d1e7dd; */ /* Verde claro para entradas */
        }
        .movimentacao-saida {
            /* color: #842029; */
            /* background-color: #f8d7da; */ /* Vermelho claro para saídas */
        }
         .card-header-filtros {
            background-color: #f8f9fa;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-0">
        <h1><i class="bi bi-hourglass-split"></i> Histórico de Movimentação: <span class="text-primary">{{ $estoque->nome }}</span></h1>
        <p>
            <strong>Modelo Compatível:</strong> {{ $estoque->modelo_compativel ?? 'Não especificado' }} |
            <strong>Tipo:</strong> {{ $estoque->tipo_formatado }} |
            <strong>Marca:</strong> {{ $estoque->marca ?? 'N/A' }} |
            <strong>Qtd. Atual:</strong> <span class="fw-bold">{{ $estoque->quantidade }}</span>
        </p>

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <a href="{{ route('estoque.index') }}" class="btn btn-secondary mb-2"><i class="bi bi-arrow-left"></i> Voltar para Estoque</a>
            <a href="{{ route('estoque.historico_unificado') }}" class="btn btn-info mb-2"><i class="bi bi-archive-fill"></i> Ver Histórico Geral</a>
        </div>

        {{-- Formulário de Filtros (Opcional, mas recomendado) --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header card-header-filtros">
                <h5 class="my-0"><i class="bi bi-funnel-fill"></i> Filtros do Histórico da Peça</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('estoque.historico_peca', $estoque->id) }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="data_inicial_hist" class="form-label form-label-sm">Data (De):</label>
                            <input type="date" class="form-control form-control-sm" id="data_inicial_hist" name="data_inicial_hist" value="{{ $request->input('data_inicial_hist') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="data_final_hist" class="form-label form-label-sm">Data (Até):</label>
                            <input type="date" class="form-control form-control-sm" id="data_final_hist" name="data_final_hist" value="{{ $request->input('data_final_hist') }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                             <div class="input-group input-group-sm">
                                <button class="btn btn-primary w-50" type="submit" style="border-top-right-radius: 0; border-bottom-right-radius: 0;" data-bs-toggle="tooltip" title="Aplicar Filtros"><i class="bi bi-search"></i> Filtrar</button>
                                <a href="{{ route('estoque.historico_peca', $estoque->id) }}" class="btn btn-secondary w-50" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" data-bs-toggle="tooltip" title="Limpar Filtros"><i class="bi bi-eraser-fill"></i> Limpar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        @if($movimentacoesPaginadas->total() > 0)
            <div class="mb-2 text-muted small">
                Exibindo {{ $movimentacoesPaginadas->firstItem() }} a {{ $movimentacoesPaginadas->lastItem() }} de {{ $movimentacoesPaginadas->total() }} movimentações encontradas.
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>Tipo</th>
                            <th class="text-center">Qtd.</th>
                            <th>Data/Hora</th>
                            <th>Observações</th>
                            <th>Ref. OS</th>
                            <th class="text-center">ID Mov.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movimentacoesPaginadas as $movimento)
                            <tr class="{{ $movimento->tipo_movimentacao_label == 'Entrada' ? 'movimentacao-entrada' : 'movimentacao-saida' }}">
                                <td>
                                    @if($movimento->tipo_movimentacao_label == 'Entrada')
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle rounded-pill">
                                            <i class="bi bi-arrow-down-circle-fill me-1"></i> Entrada
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle rounded-pill">
                                            <i class="bi bi-arrow-up-circle-fill me-1"></i> Saída
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">{{ $movimento->quantidade }}</td>
                                <td>{{ Carbon\Carbon::parse($movimento->data_movimentacao)->format('d/m/Y H:i') }}</td>
                                <td data-bs-toggle="tooltip" title="{{ $movimento->observacoes }}">
                                    {{ Str::limit($movimento->observacoes, 40) ?? '-' }}
                                </td>
                                <td>
                                    @if($movimento->atendimento_id)
                                        <a href="{{ route('atendimentos.show', $movimento->atendimento_id) }}" data-bs-toggle="tooltip" title="Ver Atendimento OS #{{ $movimento->atendimento_id }} - Cliente: {{ $movimento->cliente_atendimento ?? 'N/A' }}">
                                            OS #{{ $movimento->atendimento_id }}
                                        </a>
                                    @else
                                        <span class="text-muted fst-italic">-</span>
                                    @endif
                                </td>
                                 <td class="text-center">
                                    @if($movimento->tipo_movimentacao_label == 'Entrada')
                                        <a href="{{ route('entradas-estoque.show', $movimento->movimentacao_id) }}" class="btn btn-outline-success btn-xs py-0 px-1" data-bs-toggle="tooltip" title="Ver detalhes da Entrada #{{$movimento->movimentacao_id}}">
                                            <i class="bi bi-eye"></i> E-{{ $movimento->movimentacao_id }}
                                        </a>
                                    @else
                                        <a href="{{ route('saidas-estoque.show', $movimento->movimentacao_id) }}" class="btn btn-outline-danger btn-xs py-0 px-1" data-bs-toggle="tooltip" title="Ver detalhes da Saída #{{$movimento->movimentacao_id}}">
                                            <i class="bi bi-eye"></i> S-{{ $movimento->movimentacao_id }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Links de Paginação --}}
            @if ($movimentacoesPaginadas->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $movimentacoesPaginadas->links() }} {{-- Laravel já cuida de adicionar os appends($request->query()) que fizemos no controller --}}
                </div>
            @endif
        @else
            <div class="alert alert-info mt-3">
                @if($request->filled('data_inicial_hist') || $request->filled('data_final_hist'))
                    Nenhuma movimentação registrada para esta peça no período selecionado.
                @else
                    Nenhuma movimentação registrada para esta peça ainda.
                @endif
            </div>
        @endif

    </div>
@endsection

@push('scripts')
    <script>
        // Inicialização de Tooltips do Bootstrap, se ainda não estiver global no seu app.js
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endpush
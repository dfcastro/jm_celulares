@extends('layouts.app')

@section('title', 'Detalhes da Venda #' . ($vendaAcessorio->id ?? 'N/A'))

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .card-header h5 { font-weight: 500; }
        .dl-horizontal-show dt { float: left; width: 180px; font-weight: normal; color: #6c757d; clear: left; text-align: right; padding-right: 10px; margin-bottom: .5rem; }
        .dl-horizontal-show dd { margin-left: 195px; margin-bottom: .5rem; font-weight: 500; }
        .problem-box { padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 0.25rem; background-color: #f8f9fa; white-space: pre-wrap; font-size: 0.9em; min-height: 50px; }
        @media (max-width: 767.98px) {
            .dl-horizontal-show dt, .dl-horizontal-show dd { width: 100%; float: none; margin-left: 0; text-align: left; }
            .dl-horizontal-show dt { margin-bottom: 0.1rem; font-weight: bold; }
        }
    </style>
@endpush

@section('content')
<div class="container mt-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><i class="bi bi-receipt"></i> Detalhes da Venda #{{ $vendaAcessorio->id ?? 'N/A' }}</h1>
        <a href="{{ route('vendas-acessorios.index') }}" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Voltar para a lista de vendas">
            <i class="bi bi-arrow-left"></i> Voltar para Lista
        </a>
    </div>

    {{-- Mensagens de Feedback --}}
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

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="my-1"><i class="bi bi-info-circle-fill"></i> Informações da Venda</h5>
        </div>
        <div class="card-body">
            <dl class="dl-horizontal-show">
                <dt>ID da Venda:</dt>
                <dd>{{ $vendaAcessorio->id ?? 'N/A' }}</dd>

                <dt>Data da Venda:</dt>
                <dd>{{ $vendaAcessorio->data_venda_formatada ?? (optional($vendaAcessorio->data_venda)->format('d/m/Y H:i') ?? 'Data não informada') }}</dd>

                <dt>Cliente:</dt>
                <dd>
                    @if($vendaAcessorio->cliente)
                        <a href="{{ route('clientes.show', $vendaAcessorio->cliente->id) }}" data-bs-toggle="tooltip" title="Ver detalhes de {{ $vendaAcessorio->cliente->nome_completo }}">
                            {{ $vendaAcessorio->cliente->nome_completo }}
                        </a>
                        ({{ $vendaAcessorio->cliente->cpf_cnpj }})
                    @else
                        Venda Balcão
                    @endif
                </dd>

                <dt>Valor Total:</dt>
                <dd class="fw-bold text-success">R$ {{ number_format($vendaAcessorio->valor_total, 2, ',', '.') }}</dd>

                <dt>Forma de Pagamento:</dt>
                <dd>{{ $vendaAcessorio->forma_pagamento ?? '-' }}</dd>

                <dt>Registrado por:</dt>
                <dd>{{ $vendaAcessorio->usuarioRegistrou->name ?? 'N/A' }}</dd>

                <dt>Observações:</dt>
                <dd>
                    @if($vendaAcessorio->observacoes)
                        <div class="problem-box">{{ $vendaAcessorio->observacoes }}</div>
                    @else
                        <span class="text-muted">Nenhuma observação.</span>
                    @endif
                </dd>

                <dt>Registrada em:</dt>
                <dd>{{ optional($vendaAcessorio->created_at)->format('d/m/Y H:i:s') ?? '-' }}</dd>
                 <dt>Última Atualização:</dt>
                <dd>{{ optional($vendaAcessorio->updated_at)->format('d/m/Y H:i:s') ?? '-' }}</dd>
            </dl>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="my-1"><i class="bi bi-list-stars"></i> Itens Vendidos</h5>
        </div>
        <div class="card-body">
            @if ($vendaAcessorio->itensEstoque->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Item de Estoque</th>
                                <th class="text-center">Qtd. Vendida</th>
                                <th class="text-end">Preço Unit. (R$)</th>
                                <th class="text-end">Desconto (R$)</th>
                                <th class="text-end">Subtotal (R$)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($vendaAcessorio->itensEstoque as $itemEstoque)
                                <tr>
                                    <td>
                                        <a href="{{ route('estoque.show', $itemEstoque->id) }}" data-bs-toggle="tooltip" title="Ver item {{ $itemEstoque->nome }}">
                                            {{ $itemEstoque->nome }}
                                        </a>
                                        <small class="d-block text-muted">({{ $itemEstoque->modelo_compativel ?? 'N/A' }})</small>
                                    </td>
                                    <td class="text-center">{{ $itemEstoque->pivot->quantidade }}</td>
                                    <td class="text-end">{{ number_format($itemEstoque->pivot->preco_unitario_venda, 2, ',', '.') }}</td>
                                    <td class="text-end text-danger">{{ number_format($itemEstoque->pivot->desconto, 2, ',', '.') }}</td>
                                    <td class="text-end fw-bold">{{ number_format(($itemEstoque->pivot->quantidade * $itemEstoque->pivot->preco_unitario_venda) - $itemEstoque->pivot->desconto, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center">Nenhum item registrado para esta venda.</p>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-footer text-end">
             <a href="{{ route('vendas-acessorios.devolver.form', $vendaAcessorio->id) }}" class="btn btn-warning btn-sm me-2" data-bs-toggle="tooltip" title="Registrar devolução para esta venda">
                <i class="bi bi-arrow-return-left"></i> Devolver Venda
            </a>
            @can('is-admin')
            <form action="{{ route('vendas-acessorios.destroy', ['vendas_acessorio' => $vendaAcessorio->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta venda? O estoque dos itens será revertido e a movimentação no caixa (se houver) NÃO será automaticamente excluída.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Excluir esta venda. ATENÇÃO: Esta ação reverte o estoque dos itens, mas não afeta o caixa automaticamente.">
                    <i class="bi bi-trash-fill"></i> Excluir Venda
                </button>
            </form>
            @endcan
            <a href="{{ route('vendas-acessorios.index') }}" class="btn btn-primary btn-sm ms-2">
                <i class="bi bi-list-ul"></i> Voltar para Lista
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endpush
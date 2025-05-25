@extends('layouts.app')

@section('title', 'Devolver Venda #' . $vendas_acessorio->id)

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Estilos do card e gerais */
        .card-header h5 {
            font-weight: 500;
        }
        .card-header.bg-light { /* Para os cards internos */
            background-color: #f8f9fa !important;
        }

        /* Estilos para a lista de descrição na informação da venda original */
        .dl-horizontal-devolucao dt {
            font-weight: bold;
            color: #495057;
        }
        .dl-horizontal-devolucao dd {
            margin-bottom: 0.3rem;
        }

        /* Estilos para a linha de cada item a ser devolvido */
        .item-devolucao-linha {
            /* A linha já tem border-bottom e padding do código anterior */
        }
        .item-devolucao-linha .form-label-sm {
            margin-bottom: 0.2rem; /* Menor margem para labels sm */
            font-size: 0.8rem; /* Label um pouco menor para economizar espaço */
        }
        .item-devolucao-info {
            line-height: 1.3; /* Ajusta o espaçamento entre linhas das small tags */
            font-size: 0.85rem; /* Torna as informações de detalhe um pouco menores */
        }
        .item-devolucao-info small {
            margin-bottom: 0.1rem;
        }
        .item-devolucao-info strong { /* Destaca o nome da peça */
            color: #212529;
        }
        .form-control-sm { /* Garante que o input de quantidade também seja sm */
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
@endpush

@section('content')
<div class="container mt-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h1><i class="bi bi-box-arrow-left text-warning"></i> Devolver Itens da Venda #{{ $vendas_acessorio->id }}</h1>
        <a href="{{ route('vendas-acessorios.show', $vendas_acessorio->id) }}" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Cancelar e voltar para os detalhes da venda">
            <i class="bi bi-x-circle"></i> Cancelar Devolução
        </a>
    </div>

    {{-- Mensagens de Feedback e Erros --}}
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

    {{-- Card de Informações da Venda Original --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="my-1"><i class="bi bi-info-circle-fill"></i> Informações da Venda Original</h5>
        </div>
        <div class="card-body">
             <dl class="row mb-0 dl-horizontal-devolucao">
                <dt class="col-sm-3 text-sm-end">Cliente:</dt>
                <dd class="col-sm-9">{{ $vendas_acessorio->cliente->nome_completo ?? 'Venda Balcão' }}</dd>

                <dt class="col-sm-3 text-sm-end">Data da Venda:</dt>
                <dd class="col-sm-9">{{ $vendas_acessorio->data_venda ? $vendas_acessorio->data_venda->format('d/m/Y H:i') : 'N/A' }}</dd>

                <dt class="col-sm-3 text-sm-end">Valor Total Original:</dt>
                <dd class="col-sm-9 fw-bold">R$ {{ number_format($vendas_acessorio->valor_total, 2, ',', '.') }}</dd>
            </dl>
        </div>
    </div>

    <form action="{{ route('vendas-acessorios.devolver.processar', $vendas_acessorio->id) }}" method="POST">
        @csrf
        {{-- Card para Selecionar Itens e Quantidades --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="my-1"><i class="bi bi-list-check"></i> Selecione os Itens e Quantidades para Devolução</h5>
            </div>
            <div class="card-body">
                @if (isset($vendas_acessorio->itensEstoque) && $vendas_acessorio->itensEstoque->isNotEmpty())
                    @foreach ($vendas_acessorio->itensEstoque as $itemVendido)
                        @php
                            $quantidadeJaDevolvidaParaEsteItem = $vendas_acessorio->devolucoesVendas()
                                ->join('devolucao_venda_estoque', 'devolucoes_vendas.id', '=', 'devolucao_venda_estoque.devolucao_venda_id')
                                ->where('devolucao_venda_estoque.estoque_id', $itemVendido->id)
                                ->sum('devolucao_venda_estoque.quantidade_devolvida');
                            $quantidadeAindaDevolvivel = $itemVendido->pivot->quantidade - $quantidadeJaDevolvidaParaEsteItem;
                        @endphp

                        <div class="row mb-3 align-items-start border-bottom pb-3 pt-2 item-devolucao-linha">
                            <input type="hidden" name="itens_devolver[{{ $loop->index }}][estoque_id]" value="{{ $itemVendido->id }}">

                            {{-- Informações da Peça --}}
                            <div class="col-md-5 col-lg-5 mb-2 mb-md-0"> {{-- Aumentado um pouco o lg --}}
                                <label class="form-label form-label-sm fw-bold d-block">Peça/Acessório:</label>
                                <span data-bs-toggle="tooltip" title="ID do Item no Estoque: {{ $itemVendido->id }}">
                                    {{ $itemVendido->nome }}
                                    @if($itemVendido->modelo_compativel)
                                        <small class="text-muted">({{ Str::limit($itemVendido->modelo_compativel, 30) }})</small>
                                    @endif
                                </span>
                            </div>

                            {{-- Detalhes da Venda Original para este Item --}}
                            <div class="col-md-4 col-lg-4 mb-2 mb-md-0">
                                <label class="form-label form-label-sm fw-bold d-block">Detalhes da Venda Original:</label>
                                <div class="item-devolucao-info">
                                    <small class="d-block">Qtd. Vendida: <span class="fw-semibold">{{ $itemVendido->pivot->quantidade }}</span></small>
                                    <small class="d-block">Preço Unit. Venda: <span class="fw-semibold">R$ {{ number_format($itemVendido->pivot->preco_unitario_venda, 2, ',', '.') }}</span></small>
                                    @if(isset($itemVendido->pivot->desconto) && $itemVendido->pivot->desconto > 0 && $itemVendido->pivot->quantidade > 0)
                                        <small class="d-block">Desconto Unit. (aprox.): <span class="fw-semibold text-danger">- R$ {{ number_format($itemVendido->pivot->desconto / $itemVendido->pivot->quantidade, 2, ',', '.') }}</span></small>
                                    @elseif(isset($itemVendido->pivot->desconto) && $itemVendido->pivot->desconto > 0)
                                         <small class="d-block">Desconto Total no Item: <span class="fw-semibold text-danger">- R$ {{ number_format($itemVendido->pivot->desconto, 2, ',', '.') }}</span></small>
                                    @endif
                                    @if ($quantidadeJaDevolvidaParaEsteItem > 0)
                                        <small class="d-block text-info mt-1">Qtd. já devolvida: <span class="fw-semibold">{{ $quantidadeJaDevolvidaParaEsteItem }}</span></small>
                                    @endif
                                </div>
                            </div>

                            {{-- Campo para Quantidade a Devolver --}}
                            <div class="col-md-3 col-lg-3 mb-2 mb-md-0"> {{-- Aumentado um pouco o lg --}}
                                <label for="quantidade_devolver_{{ $loop->index }}" class="form-label form-label-sm fw-bold">Qtd. a Devolver:</label>
                                <input type="number" class="form-control form-control-sm @error('itens_devolver.'.$loop->index.'.quantidade_devolver') is-invalid @enderror"
                                       id="quantidade_devolver_{{ $loop->index }}"
                                       name="itens_devolver[{{ $loop->index }}][quantidade_devolver]"
                                       value="{{ old('itens_devolver.'.$loop->index.'.quantidade_devolver', 0) }}"
                                       min="0" max="{{ $quantidadeAindaDevolvivel }}"
                                       data-item-id="{{ $itemVendido->id }}"
                                       {{ $quantidadeAindaDevolvivel <= 0 ? 'disabled' : '' }}>
                                @error('itens_devolver.'.$loop->index.'.quantidade_devolver')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @if ($quantidadeAindaDevolvivel <= 0 && $itemVendido->pivot->quantidade > 0)
                                    <small class="text-success d-block mt-1">Todas as unidades já foram devolvidas.</small>
                                @elseif ($quantidadeAindaDevolvivel > 0)
                                     <small class="form-text text-muted">Máximo devolvível: {{ $quantidadeAindaDevolvivel }}</small>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center">Nenhum item encontrado nesta venda para devolução.</p>
                @endif
            </div>
        </div>

        {{-- Card de Detalhes da Devolução --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="my-1"><i class="bi bi-card-text"></i> Detalhes da Devolução</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="observacoes_devolucao" class="form-label">Observações da Devolução (Opcional)</label>
                    <textarea class="form-control @error('observacoes_devolucao') is-invalid @enderror" id="observacoes_devolucao" name="observacoes_devolucao"
                        rows="3" placeholder="Motivo da devolução, estado do item retornado, etc.">{{ old('observacoes_devolucao') }}</textarea>
                    @error('observacoes_devolucao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                {{-- Futuramente: Campo para 'Valor a ser estornado/creditado ao cliente' se for diferente do valor dos itens --}}
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-end">
            <a href="{{ route('vendas-acessorios.show', $vendas_acessorio->id) }}" class="btn btn-secondary me-2" data-bs-toggle="tooltip" title="Cancelar e voltar para os detalhes da venda">
                <i class="bi bi-x-circle"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" title="Confirmar a devolução dos itens selecionados e quantidades especificadas">
                <i class="bi bi-check2-circle"></i> Registrar Devolução
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script>
        // Script para inicializar tooltips
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Opcional: Adicionar um listener para desabilitar o botão de submissão após o primeiro clique
            const formDevolucao = document.querySelector('form[action*="devolver/processar"]'); // Seletor mais específico
            if(formDevolucao) {
                formDevolucao.addEventListener('submit', function() {
                    const btnSubmit = formDevolucao.querySelector('button[type="submit"]');
                    if(btnSubmit){
                        btnSubmit.disabled = true;
                        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...';
                    }
                });
            }
        });
    </script>
@endpush
@extends('layouts.app')

@section('title', 'Detalhes da Saída de Estoque #' . $saidaEstoque->id)

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .card-header h5 {
            font-weight: 500;
        }

        .dl-horizontal-show dt {
            float: left;
            width: 200px;
            /* Pode ajustar conforme a necessidade */
            font-weight: normal;
            color: #6c757d;
            clear: left;
            text-align: right;
            padding-right: 10px;
            margin-bottom: .5rem;
        }

        .dl-horizontal-show dd {
            margin-left: 215px;
            /* Ajuste conforme dt + padding */
            margin-bottom: .5rem;
            font-weight: 500;
        }

        .info-box {
            /* Similar ao problem-box, para observações */
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            background-color: #f8f9fa;
            white-space: pre-wrap;
            font-size: 0.9em;
            min-height: 50px;
        }

        @media (max-width: 767.98px) {

            /* sm */
            .dl-horizontal-show dt,
            .dl-horizontal-show dd {
                width: 100%;
                float: none;
                margin-left: 0;
                text-align: left;
            }

            .dl-horizontal-show dt {
                margin-bottom: 0.1rem;
                font-weight: bold;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container mt-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1><i class="bi bi-box-arrow-up"></i> Detalhes da Saída de Estoque</h1>
            <a href="{{ route('saidas-estoque.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Voltar para Histórico de Saídas
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
        {{-- Adicione outros feedbacks de sessão se necessário --}}

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="my-1">Saída #{{ $saidaEstoque->id }}</h5>
            </div>
            <div class="card-body">
                <dl class="dl-horizontal-show">
                    <dt>ID da Saída:</dt>
                    <dd>{{ $saidaEstoque->id }}</dd>

                    <dt>Peça/Acessório:</dt>
                    <dd>
                        @if ($saidaEstoque->estoque)
                            <a href="{{ route('estoque.show', $saidaEstoque->estoque->id) }}" data-bs-toggle="tooltip"
                                title="Ver detalhes de {{ $saidaEstoque->estoque->nome }}">
                                {{ $saidaEstoque->estoque->nome }}
                            </a>
                            <small class="d-block text-muted">
                                Modelo: {{ $saidaEstoque->estoque->modelo_compativel ?? 'N/A' }}
                                @if($saidaEstoque->estoque->marca) | Marca: {{ $saidaEstoque->estoque->marca }} @endif
                                @if($saidaEstoque->estoque->tipo)
                                    | Tipo:
                                    @if($saidaEstoque->estoque->tipo == 'PECA_REPARO') Peça p/ Reparo
                                    @elseif($saidaEstoque->estoque->tipo == 'ACESSORIO_VENDA') Acessório
                                    @elseif($saidaEstoque->estoque->tipo == 'GERAL') Geral
                                    @else {{ $saidaEstoque->estoque->tipo }}
                                    @endif
                                @endif
                            </small>
                        @else
                            <span class="text-danger" data-bs-toggle="tooltip"
                                title="Esta peça pode ter sido removida do cadastro de estoque.">Peça não encontrada no estoque
                                atual.</span>
                        @endif
                    </dd>

                    <dt>Quantidade Retirada:</dt>
                    <dd>{{ $saidaEstoque->quantidade }} unidade(s)</dd>

                    <dt>Data da Saída:</dt>
                    <dd>{{ $saidaEstoque->data_saida ? $saidaEstoque->data_saida->format('d/m/Y H:i:s') : 'N/A' }}</dd>

                    <dt>Atendimento Vinculado:</dt>
                    <dd>
                        @if ($saidaEstoque->atendimento)
                            <a href="{{ route('atendimentos.show', $saidaEstoque->atendimento->id) }}" data-bs-toggle="tooltip"
                                title="Ver Atendimento #{{ $saidaEstoque->atendimento->id }}">
                                #{{ $saidaEstoque->atendimento->id }}
                            </a>
                            @if($saidaEstoque->atendimento->cliente)
                                - {{ $saidaEstoque->atendimento->cliente->nome_completo }}
                            @else
                                - (Cliente Avulso/Não Vinculado ao Atendimento)
                            @endif
                            <small class="d-block text-muted">Aparelho:
                                {{ Str::limit($saidaEstoque->atendimento->descricao_aparelho, 30) ?? 'N/A' }} | Status OS:
                                {{ $saidaEstoque->atendimento->status ?? 'N/A' }}</small>
                        @else
                            <span class="text-muted fst-italic">Uso Interno / Não Vinculado a um Atendimento</span>
                        @endif
                    </dd>

                    <dt>Observações da Saída:</dt>
                    <dd>
                        @if($saidaEstoque->observacoes)
                            <div class="info-box">{{ $saidaEstoque->observacoes }}</div>
                        @else
                            <span class="text-muted">Nenhuma observação registrada.</span>
                        @endif
                    </dd>

                    <dt>Registrado em:</dt>
                    <dd>{{ $saidaEstoque->created_at ? $saidaEstoque->created_at->format('d/m/Y H:i:s') : 'N/A' }}</dd>

                    <dt>Última atualização:</dt>
                    <dd>{{ $saidaEstoque->updated_at ? $saidaEstoque->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</dd>
                </dl>
            </div>
            <div class="card-footer text-end">
                @can('is-admin-or-tecnico') {{-- Apenas usuários autorizados podem excluir uma saída --}}
                    <form action="{{ route('saidas-estoque.destroy', ['saidas_estoque' => $saidaEstoque->id]) }}" method="POST"
                    class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta saída? A quantidade será
                    RETORNADA ao estoque do item.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip"
                        title="Excluir Saída (Estorna Estoque)">
                        <i class="bi bi-trash-fill"></i> Excluir Saída
                    </button>
                    </form>
                @endcan
                <a href="{{ route('saidas-estoque.index') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-list-ul"></i> Voltar para Histórico de Saídas
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Inicialização de Tooltips do Bootstrap
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
@endpush
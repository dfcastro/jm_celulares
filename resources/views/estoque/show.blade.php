@extends('layouts.app')

@section('title', 'Detalhes: ' . $estoque->nome)

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .card-header h5 {
            font-weight: 500;
        }
        .dl-horizontal-show dt {
    float: left;
    width: 210px; /* AUMENTE ESTE VALOR - ajuste conforme necessário */
    font-weight: normal;
    color: #6c757d;
    clear: left;
    text-align: right;
    padding-right: 10px;
    margin-bottom: .5rem;
}
.dl-horizontal-show dd {
    margin-left: 225px; /* AUMENTE ESTE VALOR = nova largura do dt + padding-right + margem extra */
    margin-bottom: .5rem;
    font-weight: 500;
}
        .estoque-baixo {
            color: #dc3545; /* Vermelho Bootstrap para perigo */
            font-weight: bold;
        }
        .estoque-no-minimo {
            color: #ffc107; /* Amarelo Bootstrap para aviso */
            font-weight: bold;
        }
        .actions-footer .btn, .actions-footer .d-inline {
             margin-left: 0.5rem; /* Espaçamento entre botões no footer */
        }
        .actions-footer {
            display: flex;
            flex-wrap: wrap; /* Permite que os botões quebrem a linha em telas menores */
            justify-content: flex-end; /* Alinha os botões à direita */
            gap: 0.5rem; /* Espaço entre os botões */
        }


        @media (max-width: 767.98px) { /* sm */
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
            .actions-footer {
                justify-content: center; /* Centraliza botões em telas menores */
            }
            .actions-footer .btn, .actions-footer .d-inline {
                width: 100%;
                margin-left: 0;
                margin-bottom: 0.5rem;
            }
            .actions-footer .d-inline form .btn {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container mt-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1><i class="bi bi-eye-fill"></i> Detalhes do Item: <span class="text-primary">{{ $estoque->nome }}</span></h1>
            <a href="{{ route('estoque.index') }}" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Voltar para a lista de estoque">
                <i class="bi bi-arrow-left"></i> Voltar para Lista
            </a>
        </div>

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

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="my-1">Informações de Cadastro</h5>
            </div>
            <div class="card-body">
                <dl class="dl-horizontal-show">
                    <dt>ID:</dt>
                    <dd>{{ $estoque->id }}</dd>

                    <dt>Nome:</dt>
                    <dd>{{ $estoque->nome }}</dd>

                    <dt>Tipo:</dt>
                    <dd>
                        @if($estoque->tipo == 'PECA_REPARO') Peça para Reparo
                        @elseif($estoque->tipo == 'ACESSORIO_VENDA') Acessório para Venda
                        @elseif($estoque->tipo == 'GERAL') Geral / Ambos
                        @else {{ $estoque->tipo ?? 'Não definido' }}
                        @endif
                    </dd>

                    <dt>Marca:</dt>
                    <dd>{{ $estoque->marca ?? 'Não informada' }}</dd>

                    <dt>Modelo Compatível:</dt>
                    <dd>{{ $estoque->modelo_compativel ?? 'Não especificado' }}</dd>

                    <dt>Número de Série:</dt>
                    <dd>{{ $estoque->numero_serie ?? 'Não especificado' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header bg-light">
                <h5 class="my-1">Controle de Estoque e Financeiro</h5>
            </div>
            <div class="card-body">
                <dl class="dl-horizontal-show">
                    <dt>Quantidade em Estoque:</dt>
                    <dd>
                        <span class="{{ $estoque->quantidade < $estoque->estoque_minimo && $estoque->estoque_minimo > 0 ? 'estoque-baixo' : ($estoque->quantidade == $estoque->estoque_minimo && $estoque->estoque_minimo > 0 ? 'estoque-no-minimo' : '') }}">
                            {{ $estoque->quantidade }}
                        </span>
                        @if($estoque->quantidade < $estoque->estoque_minimo && $estoque->estoque_minimo > 0)
                            <i class="bi bi-exclamation-triangle-fill text-danger" data-bs-toggle="tooltip" title="Estoque abaixo do mínimo!"></i>
                        @elseif($estoque->quantidade == $estoque->estoque_minimo && $estoque->estoque_minimo > 0)
                            <i class="bi bi-exclamation-circle-fill text-warning" data-bs-toggle="tooltip" title="Estoque no nível mínimo!"></i>
                        @endif
                    </dd>

                    <dt>Estoque Mínimo:</dt>
                    <dd>{{ $estoque->estoque_minimo ?? 'Não definido' }}</dd>

                    <dt>Preço de Custo:</dt>
                    <dd>{{ $estoque->preco_custo ? 'R$ ' . number_format($estoque->preco_custo, 2, ',', '.') : 'Não informado' }}</dd>

                    <dt>Preço de Venda:</dt>
                    <dd>{{ $estoque->preco_venda ? 'R$ ' . number_format($estoque->preco_venda, 2, ',', '.') : 'Não informado' }}</dd>
                </dl>
            </div>
        </div>

         <div class="card shadow-sm mt-4">
            <div class="card-header bg-light">
                <h5 class="my-1">Datas de Registro</h5>
            </div>
            <div class="card-body">
                <dl class="dl-horizontal-show">
                    <dt>Adicionado em:</dt>
                    <dd>{{ $estoque->created_at->format('d/m/Y H:i:s') }}</dd>

                    <dt>Última atualização:</dt>
                    <dd>{{ $estoque->updated_at->format('d/m/Y H:i:s') }}</dd>
                </dl>
            </div>
             <div class="card-footer actions-footer">
                <a href="{{ route('estoque.historico_peca', ['estoque' => $estoque->id]) }}" class="btn btn-outline-info btn-sm" data-bs-toggle="tooltip" title="Ver Histórico de Movimentações deste Item">
                    <i class="bi bi-hourglass-split"></i> Histórico do Item
                </a>
                <a href="{{ route('entradas-estoque.create', ['estoque_id' => $estoque->id]) }}" class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Registrar Entrada para este Item">
                    <i class="bi bi-plus-circle-fill"></i> Nova Entrada
                </a>
                @if ($estoque->quantidade > 0)
                    <a href="{{ route('saidas-estoque.create', ['estoque_id' => $estoque->id]) }}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Registrar Saída Avulsa para este Item">
                        <i class="bi bi-dash-circle-fill"></i> Nova Saída
                    </a>
                @else
                    <button class="btn btn-outline-warning btn-sm" disabled data-bs-toggle="tooltip" title="Item sem estoque para registrar saída avulsa">
                        <i class="bi bi-dash-circle-fill"></i> Nova Saída
                    </button>
                @endif
                <a href="{{ route('estoque.edit', $estoque->id) }}" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Editar Informações deste Item">
                    <i class="bi bi-pencil-fill"></i> Editar Item
                </a>
                @can('is-admin')
                    <form action="{{ route('estoque.destroy', $estoque->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja remover este item do estoque? Esta ação não poderá ser desfeita.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Remover Item do Estoque">
                            <i class="bi bi-trash-fill"></i> Remover Item
                        </button>
                    </form>
                @endcan
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
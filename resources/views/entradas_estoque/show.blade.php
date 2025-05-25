@extends('layouts.app')

@section('title', 'Detalhes da Entrada #' . $entradaEstoque->id)

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .card-header h5 {
            font-weight: 500;
        }
        .dl-horizontal-show dt {
            float: left;
            width: 180px; /* Ajustado para acomodar labels maiores */
            font-weight: normal;
            color: #6c757d;
            clear: left;
            text-align: right;
            padding-right: 10px;
            margin-bottom: .5rem;
        }
        .dl-horizontal-show dd {
            margin-left: 195px; /* Ajustado */
            margin-bottom: .5rem;
            font-weight: 500;
        }
        .problem-box { /* Reutilizando estilo para observações */
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            background-color: #f8f9fa; /* Um pouco diferente do #fdfdfd para variar */
            white-space: pre-wrap;
            font-size: 0.9em;
            min-height: 50px; /* Altura mínima */
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
        }
    </style>
@endpush

@section('content')
<div class="container mt-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><i class="bi bi-box-arrow-in-down"></i> Detalhes da Entrada de Estoque</h1>
        <a href="{{ route('entradas-estoque.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Voltar para Histórico
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
    {{-- Adicione outros feedbacks de sessão (info, warning) se os usar aqui --}}

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="my-1">Entrada #{{ $entradaEstoque->id }}</h5>
        </div>
        <div class="card-body">
            <dl class="dl-horizontal-show">
                <dt>ID da Entrada:</dt>
                <dd>{{ $entradaEstoque->id }}</dd>

                <dt>Peça/Acessório:</dt>
                <dd>
                    @if($entradaEstoque->estoque)
                        <a href="{{ route('estoque.show', $entradaEstoque->estoque->id) }}" data-bs-toggle="tooltip" title="Ver detalhes de {{ $entradaEstoque->estoque->nome }}">
                            {{ $entradaEstoque->estoque->nome }}
                        </a>
                        <small class="d-block text-muted">
                            {{ $entradaEstoque->estoque->modelo_compativel ?? 'Modelo não especificado' }}
                            @if($entradaEstoque->estoque->marca) | Marca: {{ $entradaEstoque->estoque->marca }} @endif
                        </small>
                    @else
                        <span class="text-danger">Peça não encontrada no estoque atual.</span>
                    @endif
                </dd>

                <dt>Quantidade Adicionada:</dt>
                <dd>{{ $entradaEstoque->quantidade }} unidade(s)</dd>

                <dt>Data da Entrada:</dt>
                <dd>{{ $entradaEstoque->data_entrada->format('d/m/Y H:i:s') }}</dd>

                <dt>Observações:</dt>
                <dd>
                    @if($entradaEstoque->observacoes)
                        <div class="problem-box">{{ $entradaEstoque->observacoes }}</div>
                    @else
                        <span class="text-muted">Nenhuma observação registrada.</span>
                    @endif
                </dd>

                <dt>Registrado em:</dt>
                <dd>{{ $entradaEstoque->created_at->format('d/m/Y H:i:s') }}</dd>

                <dt>Última atualização:</dt>
                <dd>{{ $entradaEstoque->updated_at->format('d/m/Y H:i:s') }}</dd>
            </dl>
        </div>
        <div class="card-footer text-end">
            @can('is-admin') {{-- Apenas admin pode excluir uma entrada --}}
            <form action="{{ route('entradas-estoque.destroy', $entradaEstoque->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta entrada? A quantidade será REMOVIDA do estoque atual do item.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Excluir Entrada (Estorna do Estoque)">
                    <i class="bi bi-trash-fill"></i> Excluir Entrada
                </button>
            </form>
            @endcan
            <a href="{{ route('entradas-estoque.index') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-list-ul"></i> Voltar para Histórico de Entradas
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
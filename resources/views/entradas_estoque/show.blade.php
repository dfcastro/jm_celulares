@extends('layouts.app') {{-- Estende o layout base --}}

@section('title', 'Detalhes da Entrada de Estoque') {{-- Título da página --}}

@section('content') {{-- Conteúdo principal da página --}}
    <div class="container mt-0"> {{-- Removi mt-5 pois o layout já tem padding --}}

        <h1>Detalhes da Entrada de Estoque</h1>
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
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Entrada #{{ $entradaEstoque->id }}</h5>
                <p class="card-text"><strong>Peça:</strong> {{ $entradaEstoque->estoque->nome }}
                    ({{ $entradaEstoque->estoque->modelo_compativel ?? 'Modelo não especificado' }})</p>
                <p class="card-text"><strong>Quantidade:</strong> {{ $entradaEstoque->quantidade }}</p>
                <p class="card-text"><strong>Data de Entrada:</strong>
                    {{ $entradaEstoque->data_entrada->format('d/m/Y H:i:s') }}</p>
                <p class="card-text"><strong>Observações:</strong>
                    {{ $entradaEstoque->observacoes ?? 'Nenhuma observação' }}</p>
                <p class="card-text"><strong>Criado em:</strong>
                    {{ $entradaEstoque->created_at->format('d/m/Y H:i:s') }}</p>
                <p class="card-text"><strong>Última atualização:</strong>
                    {{ $entradaEstoque->updated_at->format('d/m/Y H:i:s') }}</p>

                <a href="{{ route('entradas-estoque.index') }}" class="btn btn-primary">Voltar para Entradas</a>

                <form action="{{ route('entradas-estoque.destroy', $entradaEstoque->id) }}" method="POST" class="d-inline">
                    @csrf {{-- Importante: Token CSRF --}}
                    @method('DELETE') {{-- Importante: Simula o método DELETE --}}
                    <button type="submit" class="btn btn-danger btn-sm"
                        onclick="return confirm('Tem certeza?')">Excluir</button>
                </form>
            </div>
        </div>
    </div>

@endsection

{{-- Para esta página, não precisamos de @push('styles') ou @push('scripts') geralmente --}}
{{-- Mas se você fosse adicionar ícones do Bootstrap, por exemplo: --}}
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush
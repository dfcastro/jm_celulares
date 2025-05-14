@extends('layouts.app') {{-- Estende o layout base --}}

@section('title', 'Detalhes da Entrada de Estoque') {{-- Título da página --}}

@section('content') {{-- Conteúdo principal da página --}}
    <div class="container mt-0"> {{-- Removi mt-5 pois o layout já tem padding --}}

        <h1>Detalhes da Entrada de Estoque</h1>

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
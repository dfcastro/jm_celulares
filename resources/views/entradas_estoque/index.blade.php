@extends('layouts.app') {{-- Estende o layout base --}}

@section('title', 'Entradas de Estoque') {{-- Título da página --}}

@section('content') {{-- Conteúdo principal da página --}}
    <div class="container mt-0"> {{-- Removi mt-5 pois o layout já tem padding --}}

        <h1>Entradas de Estoque</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <a href="{{ route('entradas-estoque.create') }}" class="btn btn-primary mb-3">Nova Entrada</a>
        <a href="{{ route('estoque.index') }}" class="btn btn-secondary mb-3">Voltar para Estoque</a>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Peça</th>
                    <th>Quantidade</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($entradas as $entrada)
                    <tr>
                        <td>{{ $entrada->id }}</td>
                        <td>{{ $entrada->estoque->nome }}</td>
                        <td>{{ $entrada->quantidade }}</td>
                        <td>{{ $entrada->data_entrada->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('entradas-estoque.show', $entrada->id) }}" class="btn btn-info btn-sm">Detalhes</a>
                            
                            <form action="{{ route('entradas-estoque.destroy', $entrada->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza?')">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Nenhuma entrada de estoque registrada.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $entradas->links() }}
    </div>
    
    @endsection

{{-- Para esta página, não precisamos de @push('styles') ou @push('scripts') geralmente --}}
{{-- Mas se você fosse adicionar ícones do Bootstrap, por exemplo: --}}
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush
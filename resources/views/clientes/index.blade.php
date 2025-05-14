@extends('layouts.app') {{-- Estende o layout base --}}

@section('title', 'Lista de Clientes') {{-- Título da página --}}

@section('content') {{-- Conteúdo principal da página --}}
    <div class="container mt-0"> {{-- Removi mt-5 pois o layout já tem padding --}}
        <h1>Lista de Clientes</h1>

       

        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('clientes.create') }}" class="btn btn-primary">Novo Cliente</a>
            {{-- Formulário de Busca --}}
            <form action="{{ route('clientes.index') }}" method="GET" class="d-flex">
                <input type="text" class="form-control me-2" id="busca" name="busca" placeholder="Nome ou CPF/CNPJ" value="{{ request('busca') }}">
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                @if(request('busca'))
                    <a href="{{ route('clientes.index') }}" class="btn btn-outline-danger ms-2">Limpar</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>CPF/CNPJ</th>
                        <th>Telefone</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clientes as $cliente)
                        <tr>
                            <td>{{ $cliente->id }}</td>
                            <td>{{ucfirst( $cliente->nome_completo) }}</td>
                            <td>{{ $cliente->cpf_cnpj }}</td>
                            <td>{{ $cliente->telefone ?? 'Não informado' }}</td>
                            <td>
                                <a href="{{ route('clientes.show', $cliente->id) }}" class="btn btn-info btn-sm" title="Detalhes"><i class="bi bi-eye"></i> Detalhes</a>
                                <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-warning btn-sm" title="Editar"><i class="bi bi-pencil"></i> Editar</a>
                                @can('is-admin')
    <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm" title="Excluir" onclick="return confirm('Tem certeza?')"><i class="bi bi-trash"></i></button>
    </form>
@endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">Nenhum cliente cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Links de Paginação --}}
        @if ($clientes->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $clientes->links() }}
            </div>
        @endif
    </div>
@endsection

{{-- Para esta página, não precisamos de @push('styles') ou @push('scripts') geralmente --}}
{{-- Mas se você fosse adicionar ícones do Bootstrap, por exemplo: --}}
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush
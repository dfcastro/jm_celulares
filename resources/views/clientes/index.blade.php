@extends('layouts.app') {{-- Estende o layout base --}}

@section('title', 'Lista de Clientes') {{-- Título da página --}}

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .card-header-filtros {
            background-color: #f8f9fa; /* Um cinza claro para o cabeçalho dos filtros */
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
@endpush

@section('content') {{-- Conteúdo principal da página --}}
    <div class="container mt-0">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <h1><i class="bi bi-people-fill"></i> Lista de Clientes</h1>
            <a href="{{ route('clientes.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus-fill"></i> Novo Cliente
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
        {{-- Adicione outros @if(session('info')) ou @if(session('warning')) se necessário --}}

        {{-- Card de Filtros --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header card-header-filtros">
                <h5 class="my-0"><i class="bi bi-funnel-fill"></i> Filtros de Pesquisa</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('clientes.index') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-9">
                            <label for="busca" class="form-label form-label-sm">Buscar por Nome ou CPF/CNPJ:</label>
                            <input type="text" class="form-control form-control-sm" id="busca" name="busca" placeholder="Digite para buscar..." value="{{ request('busca') }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary btn-sm w-100 me-2" type="submit"><i class="bi bi-search"></i> Filtrar</button>
                            @if(request('busca'))
                                <a href="{{ route('clientes.index') }}" class="btn btn-secondary btn-sm w-100"><i class="bi bi-eraser-fill"></i> Limpar</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>CPF/CNPJ</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th>Cidade</th>
                        <th>Cadastrado em</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clientes as $cliente)
                        <tr>
                            <td>{{ $cliente->id }}</td>
                            <td>{{ Str::ucfirst($cliente->nome_completo) }}</td>
                            <td>{{ $cliente->cpf_cnpj }}</td>
                            <td>{{ $cliente->telefone ?? 'N/A' }}</td>
                            <td>{{ $cliente->email ?? 'N/A' }}</td>
                            <td>{{ $cliente->cidade ?? 'N/A' }}</td>
                            <td>{{ $cliente->created_at->format('d/m/Y') }}</td>
                            <td class="text-center">
                                <a href="{{ route('clientes.show', $cliente->id) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="Ver Detalhes">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Editar Cliente">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                @can('is-admin')
                                    <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este cliente? Esta ação não poderá ser desfeita.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Excluir Cliente">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4">Nenhum cliente encontrado com os filtros aplicados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Links de Paginação --}}
        @if ($clientes->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $clientes->appends(request()->query())->links() }} {{-- Mantém os filtros na paginação --}}
            </div>
        @endif
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
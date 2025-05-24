@extends('layouts.app')

@section('title', 'Histórico de Caixas')

@section('content')
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
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><i class="bi bi-archive-fill me-2"></i>Histórico de Caixas</h1>
        <div>
            @can('gerenciar-caixa')
                @if (!$caixaAberto)
                    <a href="{{ route('caixa.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i>Abrir Novo Caixa
                    </a>
                @else
                    <a href="{{ route('caixa.show', $caixaAberto->id) }}" class="btn btn-info">
                        <i class="bi bi-eye me-2"></i>Ver Caixa Aberto (#{{ $caixaAberto->id }})
                    </a>
                @endif
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            Listagem dos Caixas Registrados
        </div>
        <div class="card-body">
            @if ($caixas->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#ID</th>
                                <th>Data Abertura</th>
                                <th>Aberto por</th>
                                <th>Saldo Inicial (R$)</th>
                                <th>Data Fechamento</th>
                                <th>Fechado por</th>
                                <th>Saldo Final Inform. (R$)</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($caixas as $caixa)
                                <tr>
                                    <td>{{ $caixa->id }}</td>
                                    <td>{{ $caixa->data_abertura_formatada }}</td>
                                    <td>{{ $caixa->usuarioAbertura->name ?? 'N/A' }}</td>
                                    <td class="text-end">{{ number_format($caixa->saldo_inicial, 2, ',', '.') }}</td>
                                    <td>{{ $caixa->data_fechamento_formatada ?? '-' }}</td>
                                    <td>{{ $caixa->usuarioFechamento->name ?? '-' }}</td>
                                    <td class="text-end">{{ $caixa->data_fechamento ? number_format($caixa->saldo_final_informado ?? 0, 2, ',', '.') : '-' }}</td>
                                    <td class="text-center">
                                        @if ($caixa->status === 'Aberto')
                                            <span class="badge bg-success">{{ $caixa->status }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $caixa->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('caixa.show', $caixa->id) }}" class="btn btn-sm btn-info" title="Visualizar Detalhes">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        {{-- Futuramente, botões para reabrir (com cautela), imprimir, etc. --}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $caixas->links() }} {{-- Links de Paginação --}}
                </div>
            @else
                <div class="alert alert-info text-center" role="alert">
                    Nenhum caixa registrado até o momento.
                    @if (!$caixaAberto && Gate::allows('gerenciar-caixa'))
                        Clique em "Abrir Novo Caixa" para começar.
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
    {{-- Se você usa Bootstrap Icons e ele não está global no layouts.app --}}
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> --}}
@endpush
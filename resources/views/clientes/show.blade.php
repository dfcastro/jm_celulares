@extends('layouts.app') {{-- Estende o layout base --}}

@section('title', 'Detalhes de Cliente') {{-- Título da página --}}

@section('content') {{-- Conteúdo principal da página --}}
    <h1>Detalhes do Cliente</h1>
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
            <h5 class="card-title">{{ $cliente->nome_completo }}</h5>
            <p class="card-text"><strong>CPF/CNPJ:</strong> {{ $cliente->cpf_cnpj }}</p>
            <p class="card-text"><strong>CEP:</strong> {{ $cliente->cep ?? 'Não informado' }}</p>
            <p class="card-text"><strong>Logradouro:</strong> {{ $cliente->logradouro ?? 'Não informado' }}</p>
            <p class="card-text"><strong>Número:</strong> {{ $cliente->numero ?? 'Não informado' }}</p>
            <p class="card-text"><strong>Complemento:</strong> {{ $cliente->complemento ?? 'Não informado' }}</p>
            <p class="card-text"><strong>Bairro:</strong> {{ $cliente->bairro ?? 'Não informado' }}</p>
            <p class="card-text"><strong>Cidade:</strong> {{ $cliente->cidade ?? 'Não informado' }}</p>
            <p class="card-text"><strong>Estado:</strong> {{ $cliente->estado ?? 'Não informado' }}</p>
            <p class="card-text"><strong>Telefone:</strong> {{ $cliente->telefone ?? 'Não informado' }}</p>
            <p class="card-text"><strong>Email:</strong> {{ $cliente->email ?? 'Não informado' }}</p>
            <p class="card-text"><strong>Cadastrado em:</strong> {{ $cliente->created_at->format('d/m/Y H:i:s') }}</p>
            <p class="card-text"><strong>Última atualização:</strong> {{ $cliente->updated_at->format('d/m/Y H:i:s') }}</p>

            <a href="{{ route('clientes.index') }}" class="btn btn-primary">Voltar para a lista de clientes</a>
            <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-warning">Editar Cliente</a>
            <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger"
                    onclick="return confirm('Tem certeza que deseja excluir este cliente?')">Excluir Cliente</button>
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
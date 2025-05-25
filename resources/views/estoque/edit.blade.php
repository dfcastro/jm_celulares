@extends('layouts.app')

@section('title', 'Editar Item do Estoque: ' . $estoque->nome)

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .readonly-look {
            background-color: #e9ecef; /* Cor de fundo para campos readonly */
            opacity: 1; /* Garante que o texto seja legível */
        }
    </style>
@endpush

@section('content')
    <div class="container mt-0">
        <h1><i class="bi bi-pencil-square"></i> Editar Item: <span class="text-primary">{{ $estoque->nome }}</span></h1>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <strong><i class="bi bi-exclamation-triangle-fill"></i> Ops! Verifique os erros abaixo:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        {{-- Adicione outros feedbacks de sessão (error, info, warning) se desejar --}}

        <form action="{{ route('estoque.update', $estoque->id) }}" method="POST" class="mt-3">
            @csrf
            @method('PUT')

            {{-- Card de Informações do Item --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-info-circle-fill"></i> Informações do Item</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome da Peça/Acessório <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome', $estoque->nome) }}" placeholder="Ex: Tela Frontal, Cabo USB-C" required>
                        @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo" class="form-label">Tipo do Item <span class="text-danger">*</span></label>
                            <select class="form-select @error('tipo') is-invalid @enderror" id="tipo" name="tipo" required>
                                <option value="">Selecione um tipo...</option>
                                <option value="PECA_REPARO" {{ old('tipo', $estoque->tipo) == 'PECA_REPARO' ? 'selected' : '' }}>Peça para Reparo</option>
                                <option value="ACESSORIO_VENDA" {{ old('tipo', $estoque->tipo) == 'ACESSORIO_VENDA' ? 'selected' : '' }}>Acessório para Venda</option>
                                <option value="GERAL" {{ old('tipo', $estoque->tipo) == 'GERAL' ? 'selected' : '' }}>Geral / Uso Diverso</option>
                            </select>
                            @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="marca" class="form-label">Marca (Opcional)</label>
                            <input type="text" class="form-control @error('marca') is-invalid @enderror" id="marca" name="marca" value="{{ old('marca', $estoque->marca) }}" placeholder="Ex: Samsung, Apple">
                            @error('marca') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modelo_compativel" class="form-label">Modelo(s) Compatível(is) (Opcional)</label>
                        <input type="text" class="form-control @error('modelo_compativel') is-invalid @enderror" id="modelo_compativel" name="modelo_compativel" value="{{ old('modelo_compativel', $estoque->modelo_compativel) }}" placeholder="Ex: iPhone 12/12 Pro, Galaxy S21">
                        <small class="form-text text-muted">Separe múltiplos modelos por vírgula, se aplicável.</small>
                        @error('modelo_compativel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="numero_serie" class="form-label">Número de Série/Part Number (Opcional)</label>
                        <input type="text" class="form-control @error('numero_serie') is-invalid @enderror" id="numero_serie" name="numero_serie" value="{{ old('numero_serie', $estoque->numero_serie) }}" placeholder="Se aplicável ao item">
                        @error('numero_serie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Card de Detalhes Financeiros e Controle de Estoque --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-clipboard-data-fill"></i> Detalhes Financeiros e Controle</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-3 mb-3">
                            <label for="quantidade" class="form-label">Quantidade Atual</label>
                            <input type="number" class="form-control readonly-look" id="quantidade" name="quantidade_display" value="{{ $estoque->quantidade }}" readonly>
                            <small class="form-text text-muted">Para alterar, use Entradas/Saídas.</small>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <label for="estoque_minimo" class="form-label">Estoque Mínimo (Opcional)</label>
                            <input type="number" class="form-control @error('estoque_minimo') is-invalid @enderror" id="estoque_minimo" name="estoque_minimo" value="{{ old('estoque_minimo', $estoque->estoque_minimo ?? '0') }}" min="0" placeholder="0">
                            @error('estoque_minimo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                         <div class="col-md-6 col-lg-3 mb-3">
                            <label for="preco_custo" class="form-label">Preço de Custo (R$) (Opcional)</label>
                            <input type="number" step="0.01" class="form-control @error('preco_custo') is-invalid @enderror {{ Gate::denies('is-admin') ? 'readonly-look' : '' }}" id="preco_custo" name="preco_custo" value="{{ old('preco_custo', $estoque->preco_custo ?? '0.00') }}" min="0" placeholder="0,00" {{ Gate::denies('is-admin') ? 'readonly' : '' }}>
                            @error('preco_custo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @cannot('is-admin') <small class="form-text text-muted">Apenas administradores.</small> @endcannot
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <label for="preco_venda" class="form-label">Preço de Venda (R$) (Opcional)</label>
                            <input type="number" step="0.01" class="form-control @error('preco_venda') is-invalid @enderror {{ Gate::denies('is-admin') ? 'readonly-look' : '' }}" id="preco_venda" name="preco_venda" value="{{ old('preco_venda', $estoque->preco_venda ?? '0.00') }}" min="0" placeholder="0,00" {{ Gate::denies('is-admin') ? 'readonly' : '' }}>
                            @error('preco_venda') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @cannot('is-admin') <small class="form-text text-muted">Apenas administradores.</small> @endcannot
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <a href="{{ route('estoque.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> Atualizar Item</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    {{-- Nenhum script específico para este formulário por enquanto --}}
@endpush
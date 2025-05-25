@extends('layouts.app')

@section('title', 'Nova Peça/Acessório no Estoque')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush

@section('content')
    <div class="container mt-0">
        <h1><i class="bi bi-box-seam-fill"></i> Nova Peça/Acessório no Estoque</h1>

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
        {{-- Adicione outros feedbacks de sessão (success, info, warning) se desejar, como em outras telas --}}
         @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('estoque.store') }}" method="POST" class="mt-3">
            @csrf

            {{-- Card de Informações do Item --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-info-circle-fill"></i> Informações do Item</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome da Peça/Acessório <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nome') is-invalid @enderror" id="nome" name="nome" value="{{ old('nome') }}" placeholder="Ex: Tela Frontal, Cabo USB-C, Película de Vidro" required>
                        @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo" class="form-label">Tipo do Item <span class="text-danger">*</span></label>
                            <select class="form-select @error('tipo') is-invalid @enderror" id="tipo" name="tipo" required>
                                <option value="">Selecione um tipo...</option>
                                <option value="PECA_REPARO" {{ old('tipo') == 'PECA_REPARO' ? 'selected' : '' }}>Peça para Reparo</option>
                                <option value="ACESSORIO_VENDA" {{ old('tipo') == 'ACESSORIO_VENDA' ? 'selected' : '' }}>Acessório para Venda</option>
                                <option value="GERAL" {{ old('tipo') == 'GERAL' ? 'selected' : '' }}>Geral / Uso Diverso</option>
                                {{-- Adicione mais opções se necessário --}}
                            </select>
                            @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="marca" class="form-label">Marca (Opcional)</label>
                            <input type="text" class="form-control @error('marca') is-invalid @enderror" id="marca" name="marca" value="{{ old('marca') }}" placeholder="Ex: Samsung, Apple, iFixit">
                            @error('marca') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modelo_compativel" class="form-label">Modelo(s) Compatível(is) (Opcional)</label>
                        <input type="text" class="form-control @error('modelo_compativel') is-invalid @enderror" id="modelo_compativel" name="modelo_compativel" value="{{ old('modelo_compativel') }}" placeholder="Ex: iPhone 12/12 Pro, Galaxy S21, A03s">
                        <small class="form-text text-muted">Separe múltiplos modelos por vírgula, se aplicável.</small>
                        @error('modelo_compativel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="numero_serie" class="form-label">Número de Série/Part Number (Opcional)</label>
                        <input type="text" class="form-control @error('numero_serie') is-invalid @enderror" id="numero_serie" name="numero_serie" value="{{ old('numero_serie') }}" placeholder="Se aplicável ao item">
                        @error('numero_serie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Card de Detalhes Financeiros e Controle de Estoque --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-currency-dollar"></i> Detalhes Financeiros e Controle</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="preco_custo" class="form-label">Preço de Custo (R$) (Opcional)</label>
                            <input type="number" step="0.01" class="form-control @error('preco_custo') is-invalid @enderror" id="preco_custo" name="preco_custo" value="{{ old('preco_custo', '0.00') }}" min="0" placeholder="0,00">
                            @error('preco_custo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="preco_venda" class="form-label">Preço de Venda (R$) (Opcional)</label>
                            <input type="number" step="0.01" class="form-control @error('preco_venda') is-invalid @enderror" id="preco_venda" name="preco_venda" value="{{ old('preco_venda', '0.00') }}" min="0" placeholder="0,00">
                            @error('preco_venda') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="estoque_minimo" class="form-label">Estoque Mínimo (Opcional)</label>
                            <input type="number" class="form-control @error('estoque_minimo') is-invalid @enderror" id="estoque_minimo" name="estoque_minimo" value="{{ old('estoque_minimo', '0') }}" min="0" placeholder="0">
                            <small class="form-text text-muted">Para alertas de reposição.</small>
                            @error('estoque_minimo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <p class="form-text text-muted">
                        <i class="bi bi-info-circle"></i> A quantidade inicial em estoque é adicionada através de uma "Nova Entrada de Estoque" após o cadastro do item.
                    </p>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <a href="{{ route('estoque.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> Adicionar Item ao Estoque</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    {{-- Nenhum script específico para este formulário por enquanto,
         mas o push está aqui caso precise adicionar algo no futuro (ex: máscaras para preços se desejar) --}}
@endpush
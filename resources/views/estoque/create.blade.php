@extends('layouts.app') {{-- Estende o layout base --}}

@section('title', 'Nova Peça no Estoque') {{-- Título da página --}}

@section('content') {{-- Conteúdo principal da página --}}
    <div class="container mt-0"> {{-- Removi mt-5 pois o layout já tem padding --}}
        <h1>Nova Peça no Estoque</h1>

        {{-- BLOCO PARA EXIBIR ERROS DE VALIDAÇÃO --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {{-- FIM BLOCO PARA EXIBIR ERROS --}}

        <form action="{{ route('estoque.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="nome" class="form-label">Nome da Peça</label>
                <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome') }}" required>
                {{-- Opcional: Exibir erro específico para o campo nome --}}
                @error('nome')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="modelo_compativel" class="form-label">Modelo Compatível (Opcional)</label>
                <input type="text" class="form-control" id="modelo_compativel" name="modelo_compativel"
                    value="{{ old('modelo_compativel') }}">
            </div>
            <div class="mb-3">
                <label for="numero_serie" class="form-label">Número de Série (Opcional)</label>
                <input type="text" class="form-control" id="numero_serie" name="numero_serie"
                    value="{{ old('numero_serie') }}">
            </div>
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo do Item</label>
                <select class="form-select" id="tipo" name="tipo">
                    <option value="">Selecione um tipo</option>
                    <option value="PECA_REPARO" {{ old('tipo') == 'PECA_REPARO' ? 'selected' : '' }}>Peça para Reparo</option>
                    <option value="ACESSORIO_VENDA" {{ old('tipo') == 'ACESSORIO_VENDA' ? 'selected' : '' }}>Acessório para
                        Venda</option>
                    <option value="GERAL" {{ old('tipo') == 'GERAL' ? 'selected' : '' }}>Geral / Ambos</option>
                    {{-- Adicione mais opções se necessário --}}
                </select>
                @error('tipo')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            {{-- FIM NOVO CAMPO TIPO --}}
            <div class="mb-3">
                <label for="preco_custo" class="form-label">Preço de Custo (Opcional)</label>
                <input type="number" step="0.01" class="form-control" id="preco_custo" name="preco_custo"
                    value="{{ old('preco_custo', '0.00') }}" min="0">
            </div>
            <div class="mb-3">
                <label for="marca" class="form-label">Marca (Opcional)</label>
                <input type="text" class="form-control" id="marca" name="marca" value="{{ old('marca') }}">
                @error('marca')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="preco_venda" class="form-label">Preço de Venda (Opcional)</label>
                <input type="number" step="0.01" class="form-control" id="preco_venda" name="preco_venda"
                    value="{{ old('preco_venda', '0.00') }}" min="0">
            </div>
            <div class="mb-3">
                <label for="estoque_minimo" class="form-label">Estoque Mínimo (Opcional)</label>
                <input type="number" class="form-control" id="estoque_minimo" name="estoque_minimo"
                    value="{{ old('estoque_minimo', '0') }}" min="0">
            </div>
            <button type="submit" class="btn btn-primary">Adicionar Peça</button>
            <a href="{{ route('estoque.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

@endsection

{{-- Para esta página, não precisamos de @push('styles') ou @push('scripts') geralmente --}}
{{-- Mas se você fosse adicionar ícones do Bootstrap, por exemplo: --}}
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush
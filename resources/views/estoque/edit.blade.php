@extends('layouts.app') {{-- Estende o layout base --}}

@section('title', 'Editar Peça no Estoque') {{-- Título da página --}}

@section('content') {{-- Conteúdo principal da página --}}
    <div class="container mt-0"> {{-- Removi mt-5 pois o layout já tem padding --}}
        <h1>Editar Peça no Estoque</h1>

        <form action="{{ route('estoque.update', $estoque->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="nome" class="form-label">Nome da Peça</label>
                <input type="text" class="form-control" id="nome" name="nome" value="{{ $estoque->nome }}" required>
            </div>
            <div class="mb-3">
                <label for="modelo_compativel" class="form-label">Modelo Compatível (Opcional)</label>
                <input type="text" class="form-control" id="modelo_compativel" name="modelo_compativel"
                    value="{{ $estoque->modelo_compativel }}">
            </div>
            <div class="mb-3">
                <label for="numero_serie" class="form-label">Número de Série (Opcional)</label>
                {{-- Preenche com o valor existente --}}
                <input type="text" class="form-control" id="numero_serie" name="numero_serie"
                    value="{{ $estoque->numero_serie }}">
            </div>
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo do Item</label>
                <select class="form-select" id="tipo" name="tipo">
                    <option value="">Selecione um tipo</option>
                    <option value="PECA_REPARO" {{ old('tipo', $estoque->tipo) == 'PECA_REPARO' ? 'selected' : '' }}>Peça para
                        Reparo</option>
                    <option value="ACESSORIO_VENDA" {{ old('tipo', $estoque->tipo) == 'ACESSORIO_VENDA' ? 'selected' : '' }}>
                        Acessório para Venda</option>
                    <option value="GERAL" {{ old('tipo', $estoque->tipo) == 'GERAL' ? 'selected' : '' }}>Geral / Ambos
                    </option>
                    {{-- Adicione mais opções se necessário --}}
                </select>
                @error('tipo')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            {{-- Depois do campo "Tipo do Item" --}}
            <div class="mb-3">
                <label for="marca" class="form-label">Marca (Opcional)</label>
                <input type="text" class="form-control" id="marca" name="marca" value="{{ old('marca', $estoque->marca) }}">
                @error('marca')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="quantidade" class="form-label">Quantidade</label>
                <input type="number" class="form-control" id="quantidade" name="quantidade"
                    value="{{ $estoque->quantidade }}" min="0" required>
            </div>
            
            
            <div class="mb-3">
                <label for="preco_custo" class="form-label">Preço de Custo (Opcional)</label>
                <input type="number" step="0.01" class="form-control" id="preco_custo" name="preco_custo"
                    value="{{ old('preco_custo', $estoque->preco_custo ?? '0.00') }}" min="0">
                @error('preco_custo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="preco_venda" class="form-label">Preço de Venda (Opcional)</label>
                <input type="number" step="0.01" class="form-control" id="preco_venda" name="preco_venda"
                    value="{{ old('preco_venda', $estoque->preco_venda ?? '0.00') }}" min="0">
                @error('preco_venda') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            
            <div class="mb-3">
                <label for="estoque_minimo" class="form-label">Estoque Mínimo (Opcional)</label>
                <input type="number" class="form-control" id="estoque_minimo" name="estoque_minimo"
                    value="{{ $estoque->estoque_minimo ?? '0' }}" min="0">
            </div>
            <button type="submit" class="btn btn-primary">Atualizar Peça</button>
            <a href="{{ route('estoque.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

@endsection

{{-- Para esta página, não precisamos de @push('styles') ou @push('scripts') geralmente --}}
{{-- Mas se você fosse adicionar ícones do Bootstrap, por exemplo: --}}
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush
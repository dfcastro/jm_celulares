{{-- resources/views/estoque/show.blade.php --}}

@extends('layouts.app')

@section('title', 'Detalhes da Peça - ' . $estoque->nome)

@section('content')
    <div class="container mt-0">
        <h1>Detalhes da Peça</h1>

        <div class="card">
            <div class="card-header">
                <h5 class="my-0">{{ $estoque->nome }}</h5>
            </div>
            <div class="card-body">
                <p class="card-text"><strong>ID:</strong> {{ $estoque->id }}</p>
                <p class="card-text"><strong>Nome:</strong> {{ $estoque->nome }}</p>
                <p class="card-text"><strong>Marca:</strong> {{ $estoque->marca ?? 'Não informada' }}</p>
                <p class="card-text"><strong>Modelo Compatível:</strong> {{ $estoque->modelo_compativel ?? 'Não especificado' }}</p>
                <p class="card-text"><strong>Número de Série:</strong> {{ $estoque->numero_serie ?? 'Não especificado' }}</p>
                <p class="card-text"><strong>Tipo:</strong> {{-- <<<<<<<<<<<<<< NOVA LINHA AQUI --}}
                    @if($estoque->tipo == 'PECA_REPARO')
                        Peça para Reparo
                    @elseif($estoque->tipo == 'ACESSORIO_VENDA')
                        Acessório para Venda
                    @elseif($estoque->tipo == 'GERAL')
                        Geral / Ambos
                    @else
                        {{ $estoque->tipo ?? 'Não definido' }}
                    @endif
                </p>
                <p class="card-text"><strong>Quantidade em Estoque:</strong> {{ $estoque->quantidade }}</p>
                <p class="card-text"><strong>Preço de Custo:</strong> {{ $estoque->preco_custo ? 'R$ ' . number_format($estoque->preco_custo, 2, ',', '.') : 'Não informado' }}</p>
                <p class="card-text"><strong>Preço de Venda:</strong> {{ $estoque->preco_venda ? 'R$ ' . number_format($estoque->preco_venda, 2, ',', '.') : 'Não informado' }}</p>
                <p class="card-text"><strong>Estoque Mínimo:</strong> {{ $estoque->estoque_minimo ?? 'Não definido' }}</p>
                <p class="card-text"><strong>Adicionado em:</strong> {{ $estoque->created_at->format('d/m/Y H:i:s') }}</p>
                <p class="card-text"><strong>Última atualização:</strong> {{ $estoque->updated_at->format('d/m/Y H:i:s') }}</p>
            </div>
            <div class="card-footer">
                <a href="{{ route('estoque.index') }}" class="btn btn-primary">Voltar para o Estoque</a>
                <a href="{{ route('estoque.edit', $estoque->id) }}" class="btn btn-warning">Editar Peça</a>
                <form action="{{ route('estoque.destroy', $estoque->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja remover esta peça do estoque?')">Remover Peça</button>
                </form>
            </div>
        </div>
    </div>
@endsection
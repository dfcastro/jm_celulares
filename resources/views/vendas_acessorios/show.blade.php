@extends('layouts.app')

@section('title', 'Detalhes da Venda #' . ($vendaAcessorio->id ?? 'N/A')) {{-- Título corrigido --}}

@section('content')
    <div class="container mt-0">
        <h1>Detalhes da Venda #{{ $vendaAcessorio->id ?? 'N/A' }}</h1>

        <div class="card mb-4">
            <div class="card-header">Informações da Venda</div>
            <div class="card-body">
                <p class="card-text"><strong>Data da Venda:</strong> {{ $vendaAcessorio->data_venda_formatada ?? (optional($vendaAcessorio->data_venda)->format('d/m/Y H:i') ?? 'Data não informada') }}</p>
                <p class="card-text">
                    <strong>Cliente:</strong>
                    @if($vendaAcessorio->cliente)
                        {{ $vendaAcessorio->cliente->nome_completo }} ({{ $vendaAcessorio->cliente->cpf_cnpj }})
                    @else
                        Venda Balcão
                    @endif
                </p>
                <p class="card-text"><strong>Valor Total:</strong> {{ 'R$ ' . number_format($vendaAcessorio->valor_total, 2, ',', '.') }}</p>
                <p class="card-text"><strong>Forma de Pagamento:</strong> {{ $vendaAcessorio->forma_pagamento ?? '-' }}</p>
                <p class="card-text"><strong>Registrado por:</strong> {{ $vendaAcessorio->usuarioRegistrou->name ?? 'N/A' }}</p> {{-- Assumindo que você adicionou o relacionamento usuarioRegistrou() --}}
                <p class="card-text"><strong>Observações:</strong> {{ $vendaAcessorio->observacoes ?? '-' }}</p>
                <p class="card-text"><strong>Registrada em:</strong> {{ optional($vendaAcessorio->created_at)->format('d/m/Y H:i:s') ?? '-' }}</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Itens Vendidos</div>
            <div class="card-body">
                @if ($vendaAcessorio->itensEstoque->count() > 0) {{-- CORRIGIDO AQUI --}}
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Peça</th>
                                <th>Qtd Vendida</th>
                                <th>Preço Unitário</th>
                                <th>Desconto (R$)</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($vendaAcessorio->itensEstoque as $itemEstoque) {{-- CORRIGIDO AQUI --}}
                                <tr>
                                    <td>
                                        {{ $itemEstoque->nome }}
                                        ({{ $itemEstoque->modelo_compativel ?? 'N/A' }})
                                    </td>
                                    <td>{{ $itemEstoque->pivot->quantidade }}</td>
                                    <td>{{ 'R$ ' . number_format($itemEstoque->pivot->preco_unitario_venda, 2, ',', '.') }}</td>
                                    <td>{{ 'R$ ' . number_format($itemEstoque->pivot->desconto, 2, ',', '.') }}</td>
                                    <td>{{ 'R$ ' . number_format(($itemEstoque->pivot->quantidade * $itemEstoque->pivot->preco_unitario_venda) - $itemEstoque->pivot->desconto, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>Nenhum item registrado para esta venda.</p>
                @endif
            </div>
        </div>

        {{-- Botões de Ação --}}
        <a href="{{ route('vendas-acessorios.index') }}" class="btn btn-secondary">Voltar para Lista de Vendas</a>
        <a href="{{ route('vendas-acessorios.devolver.form', $vendaAcessorio->id) }}" class="btn btn-info me-2">Devolver Venda</a>

        @can('is-admin') {{-- Apenas admin pode excluir a venda diretamente, por exemplo --}}
        <form action="{{ route('vendas-acessorios.destroy', ['vendas_acessorio' => $vendaAcessorio->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta venda? O estoque dos itens será revertido e a movimentação no caixa (se houver) NÃO será automaticamente excluída.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Excluir Venda</button>
        </form>
        @endcan

    </div>

    {{-- Removido o script duplicado do Bootstrap Bundle --}}
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush
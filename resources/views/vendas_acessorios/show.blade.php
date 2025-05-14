@extends('layouts.app') {{-- Estende o layout base --}}

@section('title', 'Cadastro de Clientes') {{-- Título da página --}}

@section('content') {{-- Conteúdo principal da página --}}
    <div class="container mt-0"> {{-- Removi mt-5 pois o layout já tem padding --}}
        <h1>Detalhes da Venda #{{ $vendaAcessorio->id ?? 'N/A' }}</h1>

        <div class="card mb-4">
            <div class="card-header">Informações da Venda</div>
            <div class="card-body">
                 <p class="card-text"><strong>Data da Venda:</strong> {{ optional($vendaAcessorio->data_venda)->format('d/m/Y H:i') ?? 'Data não informada' }}</p>
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
                 <p class="card-text"><strong>Observações:</strong> {{ $vendaAcessorio->observacoes ?? '-' }}</p>
                 <p class="card-text"><strong>Registrada em:</strong> {{ optional($vendaAcessorio->created_at)->format('d/m/Y H:i:s') ?? '-' }}</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Itens Vendidos</div>
            <div class="card-body">
                @if ($vendaAcessorio->itensVendidos->count() > 0)
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
                             @foreach ($vendaAcessorio->itensVendidos as $itemEstoque)
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
           <a href="{{ route('vendas-acessorios.devolver.form', $vendaAcessorio->id) }}" class="btn btn-info me-2">Devolver Venda</a> {{-- <<<<<<< ADICIONE ESTA LINHA <<<<<<< --}}

        {{-- DEBUG FINAL: Inspecione $id logo antes do formulário --}}
        

        <form action="{{ route('vendas-acessorios.destroy', ['vendas_acessorio' => $id]) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta venda? O estoque dos itens será revertido.')">Excluir Venda</button>
        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @endsection

{{-- Para esta página, não precisamos de @push('styles') ou @push('scripts') geralmente --}}
{{-- Mas se você fosse adicionar ícones do Bootstrap, por exemplo: --}}
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush
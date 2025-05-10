{{-- resources/views/vendas_acessorios/show.blade.php --}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Venda #{{ $vendaAcessorio->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Detalhes da Venda #{{ $vendaAcessorio->id }}</h1>

        <div class="card mb-4">
            <div class="card-header">Informações da Venda</div>
            <div class="card-body">
                 <p class="card-text"><strong>Data da Venda:</strong> {{ $vendaAcessorio->data_venda->format('d/m/Y H:i') }}</p> {{-- Exibimos data e hora --}}
                 <p class="card-text">
                     <strong>Cliente:</strong>
                     @if($vendaAcessorio->cliente)
                         {{ $vendaAcessorio->cliente->nome_completo }} ({{ $vendaAcessorio->cliente->cpf_cnpj }})
                         {{-- Opcional: Link para detalhes do cliente --}}
                         {{-- <a href="{{ route('clientes.show', $vendaAcessorio->cliente->id) }}">{{ $vendaAcessorio->cliente->nome_completo }} ({{ $vendaAcessorio->cliente->cpf_cnpj }})</a> --}}
                     @else
                         Venda Balcão
                     @endif
                 </p>
                 <p class="card-text"><strong>Valor Total:</strong> {{ 'R$ ' . number_format($vendaAcessorio->valor_total, 2, ',', '.') }}</p>
                 <p class="card-text"><strong>Forma de Pagamento:</strong> {{ $vendaAcessorio->forma_pagamento ?? '-' }}</p>
                 <p class="card-text"><strong>Observações:</strong> {{ $vendaAcessorio->observacoes ?? '-' }}</p>
                 <p class="card-text"><strong>Registrada em:</strong> {{ $vendaAcessorio->created_at->format('d/m/Y H:i:s') }}</p>
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
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Loop sobre os itens de estoque (peças) vendidos nesta venda --}}
                             @foreach ($vendaAcessorio->itensVendidos as $itemEstoque)
                                <tr>
                                    <td>
                                        {{ $itemEstoque->nome }}
                                        ({{ $itemEstoque->modelo_compativel ?? 'N/A' }})
                                        {{-- Opcional: Link para detalhes da peça --}}
                                         {{-- <a href="{{ route('estoque.show', $itemEstoque->id) }}">{{ $itemEstoque->nome }} ({{ $itemEstoque->modelo_compativel ?? 'N/A' }})</a> --}}
                                    </td>
                                    {{-- Acessamos os dados da tabela pivô usando ->pivot --}}
                                    <td>{{ $itemEstoque->pivot->quantidade }}</td>
                                    <td>{{ 'R$ ' . number_format($itemEstoque->pivot->preco_unitario_venda, 2, ',', '.') }}</td>
                                     {{-- Calcula o subtotal para esta linha --}}
                                    <td>{{ 'R$ ' . number_format($itemEstoque->pivot->quantidade * $itemEstoque->pivot->preco_unitario_venda, 2, ',', '.') }}</td>
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
        {{-- Botão para excluir (se decidir permitir) --}}
        {{-- <form action="{{ route('vendas-acessorios.destroy', $vendaAcessorio->id) }}" method="POST" class="d-inline"> --}}
            {{-- @csrf --}}
            {{-- @method('DELETE') --}}
            {{-- <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta venda?')">Excluir Venda</button> --}}
        {{-- </form> --}}

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
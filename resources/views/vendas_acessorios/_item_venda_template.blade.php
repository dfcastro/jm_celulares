{{-- resources/views/vendas_acessorios/_item_venda_template.blade.php --}}
<div class="row item-venda align-items-center">
    <div class="col-md-4 mb-3">
        <label class="form-label visually-hidden">Peça</label>
        <input type="text" class="form-control item-estoque-autocomplete" placeholder="Buscar Peça..."
               value="{{ old("itens.{$index}.nome_peca", $itemData['nome_peca'] ?? '') }}"
               required>
        <input type="hidden" class="item-estoque-id" name="itens[{{ $index }}][item_estoque_id]"
               value="{{ old("itens.{$index}.item_estoque_id", $itemData['item_estoque_id'] ?? ($itemData['estoque_id'] ?? '')) }}"
               required>
        @error("itens.{$index}.item_estoque_id")
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-2 mb-3">
        <label class="form-label visually-hidden">Qtd</label>
        <input type="number" class="form-control item-quantidade" name="itens[{{ $index }}][quantidade]"
               value="{{ old("itens.{$index}.quantidade", $itemData['quantidade'] ?? 1) }}"
               min="1" required>
        @error("itens.{$index}.quantidade")
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-2 mb-3">
        <label class="form-label visually-hidden">Preço Unit</label>
        <input type="number" step="0.01" class="form-control item-preco-unitario"
               name="itens[{{ $index }}][preco_unitario]" {{-- NOME CORRIGIDO --}}
               value="{{ old("itens.{$index}.preco_unitario", isset($itemData['preco_unitario']) ? number_format($itemData['preco_unitario'], 2, '.', '') : (isset($itemData['preco_unitario_venda']) ? number_format($itemData['preco_unitario_venda'], 2, '.', '') : number_format(0, 2, '.', ''))) }}"
               min="0" required>
        @error("itens.{$index}.preco_unitario") {{-- ATUALIZADO PARA 'preco_unitario' --}}
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-2 mb-3">
        <label class="form-label visually-hidden">Desconto (R$)</label>
        <input type="number" step="0.01" class="form-control item-desconto" name="itens[{{ $index }}][desconto]"
               value="{{ old("itens.{$index}.desconto", isset($itemData['desconto']) ? number_format($itemData['desconto'], 2, '.', '') : number_format(0, 2, '.', '')) }}"
               min="0">
        @error("itens.{$index}.desconto")
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-2 mb-3 d-flex align-items-end">
        <button type="button" class="btn btn-danger remover-item">Remover</button>
    </div>
</div>
{{-- resources/views/vendas_acessorios/_item_venda_template.blade.php --}}
@php
    $itemData = $itemData ?? []; // Garante que $itemData exista
    // Ajusta para pegar o nome da peça corretamente do old() ou $itemData
    $nomePecaValue = old("itens.{$index}.nome_peca_search", $itemData['nome_peca_search'] ?? ($itemData['nome_peca'] ?? ($itemData['item_estoque_autocomplete'] ?? '')));
    // Se 'nome_peca_search' não existir, tenta 'nome_peca', depois 'item_estoque_autocomplete' (do JS antigo)
@endphp
<div class="row item-venda align-items-center pt-2 pb-3 mb-2 border-bottom" data-index="{{ $index }}">
    <div class="col-md-4 mb-2">
        <label for="itens_{{ $index }}_nome_peca_search" class="form-label form-label-sm">Peça/Acessório <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm item-estoque-autocomplete"
               name="itens[{{ $index }}][nome_peca_search]" {{-- Campo para display e busca --}}
               id="itens_{{ $index }}_nome_peca_search"
               placeholder="Buscar Peça..."
               value="{{ $nomePecaValue }}"
               required>
        <input type="hidden" class="item-estoque-id" name="itens[{{ $index }}][item_estoque_id]"
               value="{{ old("itens.{$index}.item_estoque_id", $itemData['item_estoque_id'] ?? ($itemData['estoque_id'] ?? '')) }}"
               required>
        @error("itens.{$index}.item_estoque_id")
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
        <div id="info_item_venda_{{ $index }}" class="form-text text-muted small mt-1" style="min-height: 18px;"></div>
    </div>
    <div class="col-md-2 col-sm-4 mb-2">
        <label for="itens_{{ $index }}_quantidade" class="form-label form-label-sm">Qtd <span class="text-danger">*</span></label>
        <input type="number" class="form-control form-control-sm item-quantidade" name="itens[{{ $index }}][quantidade]"
               id="itens_{{ $index }}_quantidade"
               value="{{ old("itens.{$index}.quantidade", $itemData['quantidade'] ?? 1) }}"
               min="1" required>
        @error("itens.{$index}.quantidade")
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
        <small id="aviso_qtd_venda_{{ $index }}" class="form-text text-danger small" style="display: none;"></small>
    </div>
    <div class="col-md-2 col-sm-4 mb-2">
        <label for="itens_{{ $index }}_preco_unitario" class="form-label form-label-sm">Preço Unit. (R$) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" class="form-control form-control-sm item-preco-unitario"
               name="itens[{{ $index }}][preco_unitario]"
               id="itens_{{ $index }}_preco_unitario"
               value="{{ old("itens.{$index}.preco_unitario", isset($itemData['preco_unitario']) ? number_format(floatval(str_replace(',', '.', $itemData['preco_unitario'])), 2, '.', '') : (isset($itemData['preco_unitario_venda']) ? number_format(floatval(str_replace(',', '.', $itemData['preco_unitario_venda'])), 2, '.', '') : '0.00')) }}"
               min="0" required>
        @error("itens.{$index}.preco_unitario")
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-2 col-sm-4 mb-2">
        <label for="itens_{{ $index }}_desconto" class="form-label form-label-sm">Desconto (R$)</label>
        <input type="number" step="0.01" class="form-control form-control-sm item-desconto" name="itens[{{ $index }}][desconto]"
               id="itens_{{ $index }}_desconto"
               value="{{ old("itens.{$index}.desconto", isset($itemData['desconto']) ? number_format(floatval(str_replace(',', '.', $itemData['desconto'])), 2, '.', '') : '0.00') }}"
               min="0">
        @error("itens.{$index}.desconto")
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-1 mb-2 d-flex align-items-end">
        <button type="button" class="btn btn-danger btn-sm remover-item w-100" title="Remover Item">
            <i class="bi bi-trash"></i>
        </button>
    </div>
     <div class="col-12 text-end">
        <small class="text-muted">Subtotal do Item: R$ <strong class="item-subtotal-venda-display">0.00</strong></small>
    </div>
</div>
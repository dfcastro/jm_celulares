{{-- resources/views/vendas_acessorios/_item_venda_template.blade.php --}}

{{-- Esta view parcial é usada para renderizar o HTML de uma única linha de item de venda.
Ela espera as variáveis:
- $index: O índice numérico para o nome dos campos (ex: 0, 1, 2)
- $itemData: Um array opcional com dados antigos do item para pré-popular (uso com old input)
    --}}

    <div class="row item-venda align-items-center"> {{-- Classe item-venda para identificar a linha via JS --}}
        <div class="col-md-4 mb-3">
            {{-- Label visualmente escondido, bom para acessibilidade --}}
            <label class="form-label visually-hidden">Peça</label>

            {{-- Campo de texto para digitar o nome/busca da peça (onde o autocomplete será aplicado) --}}
            {{-- A classe 'item-estoque-autocomplete' será usada pelo JavaScript para inicializar o autocomplete --}}
            <input type="text" class="form-control item-estoque-autocomplete" placeholder="Buscar Peça..."
                value="{{ old("itens.{$index}.nome_peca", isset($itemData['nome_peca']) ? $itemData['nome_peca'] : '') }}"
                required>
            {{-- CAMPO HIDDEN para guardar o ID da peça selecionada pelo autocomplete --}}
            {{-- name="itens[{{ $index }}][estoque_id]" é crucial para enviar o ID correto para o backend --}}
            {{-- A classe 'item-estoque-id' será usada pelo JavaScript para encontrar este campo --}}
            <input type="hidden" class="item-estoque-id" name="itens[{{ $index }}][estoque_id]"
                value="{{ old("itens.{$index}.estoque_id", isset($itemData['estoque_id']) ? $itemData['estoque_id'] : '') }}"
                required> {{-- O ID da peça selecionada é obrigatório --}}


            {{-- Exibe erro de validação específico para o campo estoque_id deste item --}}
            @error("itens.{$index}.estoque_id")
                <div class="text-danger">{{ $message }}</div>
            @enderror
            {{-- Opcional: Campo hidden para estoque disponível (para validação frontend antes de enviar) --}}
            {{-- <input type="hidden" class="item-estoque-disponivel-field"
                value="{{ $itemData['estoque_disponivel'] ?? '' }}"> --}}
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label visually-hidden">Qtd</label>
            {{-- Input para a quantidade vendida deste item --}}
            {{-- name="itens[{{ $index }}][quantidade]" crucial para agrupar no array 'itens' --}}
            <input type="number" class="form-control item-quantidade" name="itens[{{ $index }}][quantidade]"
                value="{{ old("itens.{$index}.quantidade", isset($itemData['quantidade']) ? $itemData['quantidade'] : 1) }}"
                min="1" required>
            {{-- Exibe erro de validação específico para este campo (itens.X.quantidade) --}}
            @error("itens.{$index}.quantidade")
                <div class="text-danger">{{ $message }}</div>
            @enderror
            {{-- Opcional: Campo hidden para estoque disponível (para validação frontend antes de enviar) --}}
            {{-- <input type="hidden" class="item-estoque-disponivel-field"
                value="{{ $itemData['estoque_disponivel'] ?? '' }}"> --}}
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label visually-hidden">Preço Unit</label>
            {{-- Input para o preço unitário de venda deste item --}}
            {{-- name="itens[{{ $index }}][preco_unitario_venda]" crucial para agrupar no array 'itens' --}}
            <input type="number" step="0.01" class="form-control item-preco-unitario"
                name="itens[{{ $index }}][preco_unitario_venda]"
                value="{{ old("itens.{$index}.preco_unitario_venda", isset($itemData['preco_unitario_venda']) ? number_format($itemData['preco_unitario_venda'], 2, '.', '') : number_format(0, 2, '.', '')) }}"
                min="0" required>
            {{-- Exibe erro de validação específico para este campo (itens.X.preco_unitario_venda) --}}
            @error("itens.{$index}.preco_unitario_venda")
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        {{-- NOVO CAMPO: Desconto (em Reais) --}}
        <div class="col-md-2 mb-3">
            <label class="form-label visually-hidden">Desconto (R$)</label>
            <input type="number" step="0.01" class="form-control item-desconto" name="itens[{{ $index }}][desconto]"
                value="{{ old("itens.{$index}.desconto", isset($itemData['desconto']) ? number_format($itemData['desconto'], 2, '.', '') : number_format(0, 2, '.', '')) }}"
                min="0"> {{-- Desconto pode ser 0, mas não negativo --}}
            @error("itens.{$index}.desconto")
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-2 mb-3 d-flex align-items-end">
            {{-- Botão para remover esta linha de item --}}
            {{-- type="button" é importante para não submeter o formulário --}}
            <button type="button" class="btn btn-danger remover-item">Remover</button>
        </div>
    </div>
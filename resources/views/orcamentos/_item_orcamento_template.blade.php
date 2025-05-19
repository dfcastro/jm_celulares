@php
    // Define valores padrão se $itemData não estiver definido ou se campos específicos não existirem
    $currentTipoItem = old("itens.{$index}.tipo_item", $itemData['tipo_item'] ?? 'servico');
    $currentEstoqueId = old("itens.{$index}.estoque_id", $itemData['estoque_id'] ?? '');

    // Para nome_peca_search, precisamos buscar no objeto Estoque se $itemData vier do banco
    $currentNomePeca = old("itens.{$index}.nome_peca_search", $itemData['nome_peca_search'] ?? '');
    if (empty($currentNomePeca) && $currentTipoItem == 'peca' && !empty($currentEstoqueId) && isset($itemData['estoque']) && $itemData['estoque']) {
        // Se $itemData['estoque'] é um array (vindo do toArray() do model Estoque em old input)
        if (is_array($itemData['estoque'])) {
             $currentNomePeca = ($itemData['estoque']['nome'] ?? '') . ' (' . ($itemData['estoque']['modelo_compativel'] ?? 'N/A') . ')';
        }
        // Se $itemData['estoque'] é um objeto (vindo do relacionamento carregado)
        elseif (is_object($itemData['estoque'])) {
             $currentNomePeca = $itemData['estoque']->nome . ' (' . ($itemData['estoque']->modelo_compativel ?? 'N/A') . ')';
        }
    }

    $currentDescricaoManual = old("itens.{$index}.descricao_item_manual", $itemData['descricao_item_manual'] ?? '');
    $currentQuantidade = old("itens.{$index}.quantidade", $itemData['quantidade'] ?? 1);
    $currentValorUnitario = old("itens.{$index}.valor_unitario", $itemData['valor_unitario'] ?? '0.00');
    $currentSubtotal = $currentQuantidade * (float)str_replace(',', '.', $currentValorUnitario);

    // Define se os campos estarão readonly/disabled
    // A variável $edicaoApenasCliente é passada pela view principal (edit.blade.php)
    $isReadOnly = isset($edicaoApenasCliente) && $edicaoApenasCliente;
@endphp

<div class="row item-orcamento-row align-items-end" data-index="{{ $index }}">
    {{-- Campo oculto para o ID do item do orçamento (se estiver editando um existente) --}}
    <input type="hidden" name="itens[{{ $index }}][id]" value="{{ $itemData['id'] ?? '' }}">

    <div class="col-md-3 mb-2">
        <label for="itens_{{ $index }}_tipo_item" class="form-label form-label-sm">Tipo <span class="text-danger">*</span></label>
        <select class="form-select form-select-sm tipo-item-select @if($isReadOnly) readonly-look @endif"
                name="itens[{{ $index }}][tipo_item]"
                id="itens_{{ $index }}_tipo_item" required @if($isReadOnly) disabled @endif>
            <option value="servico" {{ $currentTipoItem == 'servico' ? 'selected' : '' }}>Serviço</option>
            <option value="peca" {{ $currentTipoItem == 'peca' ? 'selected' : '' }}>Peça</option>
        </select>
        {{-- Se o select estiver desabilitado, precisamos enviar seu valor atual --}}
        @if($isReadOnly)
            <input type="hidden" name="itens[{{ $index }}][tipo_item]" value="{{ $currentTipoItem }}">
        @endif
    </div>

    {{-- Campos para PEÇA --}}
    <div class="col-md-4 mb-2 campo-peca" style="{{ $currentTipoItem == 'peca' ? '' : 'display:none;' }}">
        <label for="itens_{{ $index }}_nome_peca_search" class="form-label form-label-sm">Buscar Peça <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm item-estoque-search @if($isReadOnly) readonly-look @endif"
               name="itens[{{ $index }}][nome_peca_search]"
               id="itens_{{ $index }}_nome_peca_search"
               placeholder="Nome da peça no estoque..."
               value="{{ $currentNomePeca }}"
               @if($isReadOnly) readonly @endif>
        <input type="hidden" class="item-estoque-id" name="itens[{{ $index }}][estoque_id]" value="{{ $currentEstoqueId }}">
    </div>

    {{-- Campo para SERVIÇO --}}
    <div class="col-md-4 mb-2 campo-servico" style="{{ $currentTipoItem == 'servico' ? '' : 'display:none;' }}">
        <label for="itens_{{ $index }}_descricao_item_manual" class="form-label form-label-sm">Descrição do Serviço <span class="text-danger">*</span></label>
        <input type="text" class="form-control form-control-sm descricao-servico-manual @if($isReadOnly) readonly-look @endif"
               name="itens[{{ $index }}][descricao_item_manual]"
               id="itens_{{ $index }}_descricao_item_manual"
               placeholder="Ex: Troca de tela, Limpeza..."
               value="{{ $currentDescricaoManual }}"
               @if($isReadOnly) readonly @endif>
    </div>

    <div class="col-md-1 mb-2">
        <label for="itens_{{ $index }}_quantidade" class="form-label form-label-sm">Qtd <span class="text-danger">*</span></label>
        <input type="number" class="form-control form-control-sm item-quantidade @if($isReadOnly) readonly-look @endif"
               name="itens[{{ $index }}][quantidade]"
               id="itens_{{ $index }}_quantidade" value="{{ $currentQuantidade }}" min="1" required
               @if($isReadOnly) readonly @endif>
    </div>

    <div class="col-md-2 mb-2">
        <label for="itens_{{ $index }}_valor_unitario" class="form-label form-label-sm">Val. Unit. (R$) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" class="form-control form-control-sm item-preco-unitario @if($isReadOnly) readonly-look @endif"
               name="itens[{{ $index }}][valor_unitario]"
               id="itens_{{ $index }}_valor_unitario" value="{{ number_format((float)str_replace(',', '.', $currentValorUnitario), 2, '.', '') }}" min="0" required
               @if($isReadOnly) readonly @endif>
    </div>

    <div class="col-md-1 mb-2 text-end d-flex align-items-center pt-3">
        <button type="button" class="btn btn-danger btn-sm remover-item-orcamento" title="Remover Item" @if($isReadOnly) disabled @endif>
            <i class="bi bi-trash"></i>
        </button>
    </div>
    <div class="col-12 mb-2 text-end">
        <small class="text-muted">Subtotal do Item: R$ <span class="item-subtotal-display">{{ number_format($currentSubtotal, 2, ',', '.') }}</span></small>
    </div>
</div>
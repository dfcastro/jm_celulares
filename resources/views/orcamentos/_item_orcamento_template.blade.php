@php
    $itemData = $itemData ?? [];
    $currentTipoItem = old("itens.{$index}.tipo_item", $itemData['tipo_item'] ?? 'servico');
    $currentEstoqueId = old("itens.{$index}.estoque_id", $itemData['estoque_id'] ?? '');
    $currentNomePeca = old("itens.{$index}.nome_peca_search", $itemData['nome_peca_search'] ?? '');
    // ... (lógica para $currentNomePeca como antes) ...
    if (empty($currentNomePeca) && $currentTipoItem == 'peca' && !empty($currentEstoqueId)) {
        if (isset($itemData['estoque']) && $itemData['estoque']) {
            $estoqueInfo = is_array($itemData['estoque']) ? $itemData['estoque'] : (is_object($itemData['estoque']) ? (array) $itemData['estoque'] : null);
            if ($estoqueInfo) {
                $currentNomePeca = ($estoqueInfo['nome'] ?? '') . ' (' . ($estoqueInfo['modelo_compativel'] ?? 'N/A') . ')';
            }
        } elseif (isset($itemData['item_estoque_id']) && !isset($itemData['estoque'])) {
            // Caso de old input onde apenas o ID foi salvo, mas o nome da peça não foi repopulado no old().
            // O ideal é que o controller repopule 'nome_peca_search' no old() se possível.
            // Se não, o usuário terá que buscar a peça novamente.
        }
    }

    $currentDescricaoManual = old("itens.{$index}.descricao_item_manual", $itemData['descricao_item_manual'] ?? '');
    $currentQuantidade = old("itens.{$index}.quantidade", $itemData['quantidade'] ?? 1);
    $currentValorUnitario = old("itens.{$index}.valor_unitario", $itemData['valor_unitario'] ?? '0.00');
    // Formatando para garantir que seja um float para cálculo
    $valorUnitarioFloat = (float) str_replace(',', '.', is_string($currentValorUnitario) ? $currentValorUnitario : number_format($currentValorUnitario, 2, '.', ''));
    $currentSubtotal = $currentQuantidade * $valorUnitarioFloat;

    $isReadOnly = isset($edicaoApenasCliente) && $edicaoApenasCliente;
@endphp

<div class="row item-orcamento-row align-items-center pt-2 pb-3 mb-3 border-bottom" data-index="{{ $index }}"> {{--
    Removido align-items-end para testar --}}
    <input type="hidden" name="itens[{{ $index }}][id]" value="{{ $itemData['id'] ?? '' }}">

    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
        <label for="itens_{{ $index }}_tipo_item" class="form-label form-label-sm">Tipo <span
                class="text-danger">*</span></label>
        <select class="form-select form-select-sm tipo-item-select @if($isReadOnly) readonly-look @endif"
            name="itens[{{ $index }}][tipo_item]" id="itens_{{ $index }}_tipo_item" required @if($isReadOnly) disabled
            @endif>
            <option value="servico" {{ $currentTipoItem == 'servico' ? 'selected' : '' }}>Serviço</option>
            <option value="peca" {{ $currentTipoItem == 'peca' ? 'selected' : '' }}>Peça</option>
        </select>
        @if($isReadOnly)
            <input type="hidden" name="itens[{{ $index }}][tipo_item]" value="{{ $currentTipoItem }}">
        @endif
    </div>

    {{-- Campo Peça --}}
    <div class="col-lg-4 col-md-8 col-sm-6 mb-2 campo-peca"
        style="{{ $currentTipoItem == 'peca' ? '' : 'display:none;' }}">
        <label for="itens_{{ $index }}_nome_peca_search" class="form-label form-label-sm">Buscar Peça <span
                class="text-danger">*</span></label>
        <input type="text"
            class="form-control form-control-sm item-estoque-search @if($isReadOnly) readonly-look @endif"
            name="itens[{{ $index }}][nome_peca_search]" id="itens_{{ $index }}_nome_peca_search"
            placeholder="Nome da peça no estoque..." value="{{ $currentNomePeca }}" @if($isReadOnly) readonly @endif>
        <input type="hidden" class="item-estoque-id" name="itens[{{ $index }}][estoque_id]"
            value="{{ $currentEstoqueId }}">
        <div id="info_peca_orc_{{ $index }}" class="form-text text-muted small mt-1" style="min-height: 18px;"></div>
    </div>

    {{-- Campo Serviço --}}
    <div class="col-lg-4 col-md-8 col-sm-6 mb-2 campo-servico"
        style="{{ $currentTipoItem == 'servico' ? '' : 'display:none;' }}">
        <label for="itens_{{ $index }}_descricao_item_manual" class="form-label form-label-sm">Descrição do Serviço
            <span class="text-danger">*</span></label>
        <input type="text"
            class="form-control form-control-sm descricao-servico-manual @if($isReadOnly) readonly-look @endif"
            name="itens[{{ $index }}][descricao_item_manual]" id="itens_{{ $index }}_descricao_item_manual"
            placeholder="Ex: Troca de tela, Limpeza..." value="{{ $currentDescricaoManual }}" @if($isReadOnly) readonly
            @endif>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-4 mb-2">
        <label for="itens_{{ $index }}_quantidade" class="form-label form-label-sm">Qtd <span
                class="text-danger">*</span></label>
        <input type="number" class="form-control form-control-sm item-quantidade @if($isReadOnly) readonly-look @endif"
            name="itens[{{ $index }}][quantidade]" id="itens_{{ $index }}_quantidade" value="{{ $currentQuantidade }}"
            min="1" required @if($isReadOnly) readonly @endif>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-4 mb-2">
        <label for="itens_{{ $index }}_valor_unitario" class="form-label form-label-sm">Val. Unit. (R$) <span
                class="text-danger">*</span></label>
        <input type="number" step="0.01"
            class="form-control form-control-sm item-preco-unitario @if($isReadOnly) readonly-look @endif"
            name="itens[{{ $index }}][valor_unitario]" id="itens_{{ $index }}_valor_unitario"
            value="{{ number_format($valorUnitarioFloat, 2, '.', '') }}" min="0" required @if($isReadOnly) readonly
            @endif>
    </div>

    <div class="col-lg-1 col-md-2 col-sm-4 mb-2 d-flex align-items-end"> {{-- Botão alinhado à base do seu contêiner
        --}}
        <button type="button" class="btn btn-danger btn-sm remover-item-orcamento w-100" title="Remover Item"
            @if($isReadOnly) disabled @endif>
            <i class="bi bi-trash"></i>
        </button>
    </div>

    <div class="col-12 text-end mt-1">
        <small class="text-muted">Subtotal do Item: R$ <strong
                class="item-subtotal-display">{{ number_format($currentSubtotal, 2, ',', '.') }}</strong></small>
    </div>
</div>
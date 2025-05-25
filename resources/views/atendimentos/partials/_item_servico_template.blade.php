@php
    // Valores padrão para o template (serão substituídos pelo JS ou dados 'old')
    $itemServicoData = $itemServicoData ?? []; // Garante que $itemServicoData exista
    $descricaoServico = old("servicos_detalhados.{$index}.descricao_servico", $itemServicoData['descricao_servico'] ?? '');
    $quantidade = old("servicos_detalhados.{$index}.quantidade", $itemServicoData['quantidade'] ?? 1);
    $valorUnitario = old("servicos_detalhados.{$index}.valor_unitario", $itemServicoData['valor_unitario'] ?? '0.00');
    $subtotalItem = $quantidade * (float) str_replace(',', '.', $valorUnitario);
    $itemId = old("servicos_detalhados.{$index}.id", $itemServicoData['id'] ?? '');
    $isReadOnly = $isReadOnly ?? false; // Recebe de quem inclui o template
@endphp

<div class="row item-servico-detalhado-row align-items-end mb-3 p-2 border rounded bg-light-subtle"
    data-index="{{ $index }}">
    <input type="hidden" name="servicos_detalhados[{{ $index }}][id]" value="{{ $itemId }}">

    <div class="col-md-6 mb-2">
        <label for="servicos_detalhados_{{ $index }}_descricao_servico" class="form-label form-label-sm">Descrição do
            Serviço <span class="text-danger">*</span></label>
        <input type="text"
            class="form-control form-control-sm item-servico-descricao {{ $isReadOnly ? 'readonly-look' : '' }}"
            name="servicos_detalhados[{{ $index }}][descricao_servico]"
            id="servicos_detalhados_{{ $index }}_descricao_servico" value="{{ $descricaoServico }}" required {{ $isReadOnly ? 'readonly' : '' }}>
    </div>

    <div class="col-md-2 mb-2">
        <label for="servicos_detalhados_{{ $index }}_quantidade" class="form-label form-label-sm">Qtd <span
                class="text-danger">*</span></label>
        <input type="number"
            class="form-control form-control-sm item-servico-quantidade {{ $isReadOnly ? 'readonly-look' : '' }}"
            name="servicos_detalhados[{{ $index }}][quantidade]" id="servicos_detalhados_{{ $index }}_quantidade"
            value="{{ $quantidade }}" min="1" required {{ $isReadOnly ? 'readonly' : '' }}>
    </div>

    <div class="col-md-2 mb-2">
        <label for="servicos_detalhados_{{ $index }}_valor_unitario" class="form-label form-label-sm">Val. Unit. (R$)
            <span class="text-danger">*</span></label>
        <input type="number" step="0.01"
            class="form-control form-control-sm item-servico-valor-unitario {{ $isReadOnly ? 'readonly-look' : '' }}"
            name="servicos_detalhados[{{ $index }}][valor_unitario]"
            id="servicos_detalhados_{{ $index }}_valor_unitario"
            value="{{ number_format((float) str_replace(',', '.', $valorUnitario), 2, '.', '') }}" min="0" required {{ $isReadOnly ? 'readonly' : '' }}>
    </div>

    <div class="col-md-1 mb-2 text-end d-flex align-items-center pt-3">
        @if(!$isReadOnly) {{-- Só mostra o botão de remover se não for readonly --}}
            <button type="button" class="btn btn-danger btn-sm remover-item-servico-detalhado" title="Remover Serviço">
                <i class="bi bi-trash"></i>
            </button>
        @endif
    </div>
    <div class="col-12 mb-1 text-end">
        <small class="text-muted">Subtotal do Serviço: R$ <span
                class="item-servico-subtotal-display">{{ number_format($subtotalItem, 2, ',', '.') }}</span></small>
    </div>
</div>
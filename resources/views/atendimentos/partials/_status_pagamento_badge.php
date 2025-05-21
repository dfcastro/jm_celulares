<span id="statusPagamentoTexto" class="badge rounded-pill fs-6 {{ App\Models\Atendimento::getPaymentStatusClass($status_pagamento) }}">
    <i class="bi {{ App\Models\Atendimento::getPaymentStatusIcon($status_pagamento) }} me-1"></i>
    <span id="statusPagamentoNome">{{ $status_pagamento ?? 'Pendente' }}</span>
</span>
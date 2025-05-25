{{-- resources/views/atendimentos/partials/_modal_registrar_pagamento.blade.php --}}
{{-- Este modal espera as variáveis:
    $atendimento (objeto Atendimento)
    $formasPagamentoDisponiveis (array de strings com as formas de pagamento)
--}}
<div class="modal fade" id="modalRegistrarPagamento" tabindex="-1" aria-labelledby="modalRegistrarPagamentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> {{-- Usando modal-lg para mais espaço --}}
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalRegistrarPagamentoLabel"><i class="bi bi-cash-coin"></i> Registrar Pagamento - Atendimento #{{ $atendimento->id }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formRegistrarPagamentoAtendimento">
                @csrf
                <div class="modal-body">
                    <div id="feedbackModalPagamento" class="mb-3"></div>

                    <div class="alert alert-secondary small p-3">
                        <p class="mb-1"><strong>Cliente:</strong> {{ $atendimento->cliente->nome_completo ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Aparelho:</strong> {{ $atendimento->descricao_aparelho }}</p>
                        <p class="mb-0"><strong>Valor Total Original OS:</strong>
                            <strong id="valorTotalOriginalDisplayModal">
                                R$ {{ number_format($atendimento->valor_total_atendimento, 2, ',', '.') }}
                            </strong>
                        </p>
                    </div>
                    <hr class="my-3">

                    <div class="mb-3 p-3 bg-light border rounded text-center">
                        <p class="mb-1 fs-5">VALOR TOTAL A SER PAGO:</p>
                        <h3 id="modalNovoValorTotalDevidoDisplay" class="text-primary fw-bold">
                            {{-- Preenchido via JS, mas pode ter um valor inicial se necessário --}}
                            R$ {{ number_format($atendimento->valor_total_atendimento, 2, ',', '.') }}
                        </h3>
                        <hr class="my-2">
                        <small class="d-block text-muted">Detalhes do valor:</small>
                        <small class="d-block text-muted">
                            Subtotal Mão de Obra (Líquido):
                            <span id="modalSubtotalServicoDisplay">
                                R$ {{ number_format($atendimento->valor_servico_liquido, 2, ',', '.') }}
                            </span>
                        </small>
                        <small class="d-block text-muted">
                            (+) Total Peças:
                            <span id="modalValorPecasDisplay">
                                R$ {{ number_format($atendimento->valor_total_pecas, 2, ',', '.') }}
                            </span>
                        </small>
                    </div>
                    <hr class="my-3">

                    {{-- Campos para valores atuais de serviço e desconto, para serem enviados ao backend --}}
                    {{-- Estes campos serão preenchidos/atualizados pelo JavaScript antes do submit do modal --}}
                    <input type="hidden" name="valor_servico" id="modal_valor_servico_hidden" value="{{ $atendimento->valor_servico ?? '0.00' }}">
                    <input type="hidden" name="desconto_servico" id="modal_desconto_servico_hidden" value="{{ $atendimento->desconto_servico ?? '0.00' }}">


                    <div class="mb-3">
                        <label for="modal_forma_pagamento" class="form-label">Forma de Pagamento <span class="text-danger">*</span></label>
                        <select class="form-select @error('forma_pagamento') is-invalid @enderror" id="modal_forma_pagamento" name="forma_pagamento" required>
                            <option value="">Selecione a forma de pagamento...</option>
                            @if(isset($formasPagamentoDisponiveis))
                                @foreach($formasPagamentoDisponiveis as $opcao)
                                    <option value="{{ $opcao }}" {{ (old('forma_pagamento', $atendimento->forma_pagamento) == $opcao && $atendimento->status_pagamento !== 'Pendente') ? 'selected' : '' }}>
                                        {{ $opcao }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('forma_pagamento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="modal_observacoes_pagamento" class="form-label">Observações do Pagamento (Opcional):</label>
                        <textarea class="form-control @error('observacoes_pagamento') is-invalid @enderror"
                                  id="modal_observacoes_pagamento" name="observacoes_pagamento"
                                  rows="2" placeholder="Ex: Pago com duas formas, valor parcial, etc."></textarea>
                        @error('observacoes_pagamento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnConfirmarPagamentoModal">
                        <i class="bi bi-check-circle-fill"></i> Confirmar Pagamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
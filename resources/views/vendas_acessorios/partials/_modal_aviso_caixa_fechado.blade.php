{{-- resources/views/vendas_acessorios/partials/_modal_aviso_caixa_fechado.blade.php --}}
<div class="modal fade" id="modalAvisoCaixaFechadoVenda" tabindex="-1" aria-labelledby="modalAvisoCaixaFechadoVendaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalAvisoCaixaFechadoVendaLabel"><i class="bi bi-exclamation-triangle-fill"></i> Atenção: Caixa Fechado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Não é possível registrar esta venda porque não há um caixa aberto no momento.</p>
                <p>Por favor, abra um novo caixa para continuar.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Entendi</button>
                <a href="{{ route('caixa.create') }}" class="btn btn-success">
                    <i class="bi bi-box-arrow-in-right"></i> Abrir Caixa Agora
                </a>
            </div>
        </div>
    </div>
</div>
{{-- resources/views/atendimentos/partials/_modal_confirmacao_abertura_caixa.blade.php --}}
<div class="modal fade" id="modalConfirmacaoAberturaCaixa" tabindex="-1" aria-labelledby="modalConfirmacaoAberturaCaixaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalConfirmacaoAberturaCaixaLabel"><i class="bi bi-box-arrow-in-right"></i> Caixa Fechado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalConfirmarAbrirCaixaInfo">
                {{-- Conteúdo preenchido via JavaScript --}}
                <p>Nenhum caixa aberto no momento.</p>
                <p>Deseja abrir um novo caixa agora para registrar esta entrada financeira?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Não, depois</button>
                {{-- O ID deste botão é importante para o JavaScript --}}
                <button type="button" class="btn btn-success" id="btnConfirmarAberturaCaixa">Sim, abrir caixa</button>
            </div>
        </div>
    </div>
</div>
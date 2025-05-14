{{-- resources/views/clientes/partials/modal_create.blade.php --}}
<div class="modal fade" id="modalNovoCliente" tabindex="-1" aria-labelledby="modalNovoClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"> {{-- modal-lg para mais espaço --}}
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNovoClienteLabel"><i class="bi bi-person-plus-fill"></i> Cadastrar Novo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoCliente">
                    @csrf
                    <div class="mb-3">
                        <label for="modal_nome_completo" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_nome_completo" name="nome_completo" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="modal_cpf_cnpj" class="form-label">CPF/CNPJ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_cpf_cnpj" name="cpf_cnpj" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="modal_telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="modal_telefone" name="telefone">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="modal_email" class="form-label">Email (Opcional)</label>
                        <input type="email" class="form-control" id="modal_email" name="email">
                    </div>

                    <hr>
                    <h6 class="mb-3">Endereço (Opcional)</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="modal_cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="modal_cep" name="cep">
                        </div>
                    </div>

                    {{-- Campos de endereço que serão preenchidos pelo ViaCEP --}}
                    <div id="endereco_modal_fields" style="display:none;"> {{-- Começa oculto --}}
                        <div class="row">
                            <div class="col-md-9 mb-3">
                                <label for="modal_logradouro" class="form-label">Logradouro</label>
                                <input type="text" class="form-control" id="modal_logradouro" name="logradouro">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="modal_numero" class="form-label">Número</label>
                                <input type="text" class="form-control" id="modal_numero" name="numero">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="modal_complemento" class="form-label">Complemento</label>
                            <input type="text" class="form-control" id="modal_complemento" name="complemento">
                        </div>
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="modal_bairro" class="form-label">Bairro</label>
                                <input type="text" class="form-control" id="modal_bairro" name="bairro">
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="modal_cidade" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="modal_cidade" name="cidade">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="modal_estado" class="form-label">Estado</label>
                                <input type="text" class="form-control" id="modal_estado" name="estado" maxlength="2">
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-top">
                        <button type="submit" class="btn btn-primary w-100" id="btnSalvarNovoCliente">Salvar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
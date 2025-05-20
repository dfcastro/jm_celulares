{{-- resources/views/atendimentos/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detalhes do Atendimento #' . $atendimento->id)

@section('content')
<div class="container mt-0">
    <h1>Detalhes do Atendimento <span class="text-primary">#{{ $atendimento->id }}</span></h1>
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <a href="{{ route('atendimentos.pdf', $atendimento->id) }}" class="btn btn-info me-2" target="_blank">
                <i class="bi bi-printer"></i> Gerar PDF/OS
            </a>
            <a href="{{ route('atendimentos.edit', $atendimento->id) }}" class="btn btn-warning"><i
                    class="bi bi-pencil"></i> Editar Atendimento Completo</a>
        </div>
        <a href="{{ route('atendimentos.index') }}" class="btn btn-secondary"><i class="bi bi-list-ul"></i> Voltar
            para Lista</a>
    </div>

    {{-- Mensagens de feedback global para esta página --}}
    <div id="feedbackGlobalAtendimentoShow" class="mb-3"></div>
    {{-- Para erros de validação do formulário de status rápido, se não for AJAX --}}
    @if ($errors->hasBag('status_rapido_form'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="bi bi-exclamation-triangle-fill"></i> Erro ao atualizar status:</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->getBag('status_rapido_form')->all() as $message)
            <li>{{ $message }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif


    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <h5 class="my-1"><i class="bi bi-info-circle-fill me-2"></i>Informações Gerais do Atendimento</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><strong>Cliente:</strong>
                        @if($atendimento->cliente)
                        <a href="{{ route('clientes.show', $atendimento->cliente->id) }}">{{ $atendimento->cliente->nome_completo }}</a>
                        <small class="d-block text-muted">CPF/CNPJ: {{ $atendimento->cliente->cpf_cnpj }}</small>
                        <small class="d-block text-muted">Tel: {{ $atendimento->cliente->telefone ?? 'Não informado' }}</small>
                        @else
                        <span class="text-muted">Cliente não informado</span>
                        @endif
                    </p>
                    <p class="mb-2"><strong>Aparelho/Descrição:</strong><br>{{ $atendimento->descricao_aparelho }}</p>
                    <p class="mb-2"><strong>Problema Relatado:</strong></p>
                    <div class="p-2 border rounded bg-light mb-3" style="white-space: pre-wrap; font-size: 0.95em;">
                        {{ $atendimento->problema_relatado }}
                    </div>
                    <p class="mb-2"><strong>Data de Entrada:</strong> {{ $atendimento->data_entrada->format('d/m/Y H:i') }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong>Status Atual:</strong>
                        <span id="statusAtualTexto" class="badge rounded-pill fs-6 {{ App\Models\Atendimento::getStatusClass($atendimento->status) }}">
                            <i class="bi {{ App\Models\Atendimento::getStatusIcon($atendimento->status) }} me-1"></i>
                            <span id="statusAtualNome">{{ $atendimento->status }}</span>
                        </span>
                    </p>

                    @can('is-internal-user') {{-- Ajuste a Gate conforme necessário --}}
                    <div class="mt-2 mb-3 p-2 border rounded bg-light-subtle">
                        <form action="{{ route('atendimentos.atualizarStatus', $atendimento->id) }}" method="POST" class="d-flex align-items-center flex-wrap" id="formAtualizarStatusRapido">
                            @csrf
                            @method('PATCH')
                            <label for="status_rapido" class="form-label me-2 mb-0 fw-semibold small">Alterar Status:</label>
                            <select class="form-select form-select-sm flex-grow-1 me-2 mb-1 mb-sm-0" style="min-width: 180px;" id="status_rapido" name="status" aria-label="Alterar status do atendimento">
                                @foreach (\App\Models\Atendimento::getPossibleStatuses() as $s) {{-- Usa o método do Model --}}
                                <option value="{{ $s }}" {{ $atendimento->status == $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i> Salvar</button>
                        </form>
                        <div id="feedbackStatusRapido" class="mt-1 small"></div>
                    </div>
                    @endcan

                    <p class="mb-2"><strong>Técnico Responsável:</strong> <span id="tecnicoAtualTexto">{{ $atendimento->tecnico->name ?? 'Não atribuído' }}</span>
                        {{-- Futuro local para edição inline do técnico, se desejado --}}
                    </p>
                    <p class="mb-2"><strong>Data de Conclusão Estimada/Real:</strong>
                        {{ $atendimento->data_conclusao ? $atendimento->data_conclusao->format('d/m/Y') : ($atendimento->status == 'Entregue' || $atendimento->status == 'Cancelado' || $atendimento->status == 'Finalizado e Pago' ? 'Concluído/Finalizado' : 'Não concluído') }}
                    </p>
                    <p class="mb-2">
                        <strong>Código de Consulta (Cliente):</strong>
                        <span id="codigoConsultaParaCopiar" class="fw-bold user-select-all" style="cursor: pointer;" title="Clique para copiar o código">
                            {{ $atendimento->codigo_consulta }}
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-1 py-0 px-1" id="btnCopiarCodigo" title="Copiar código">
                            <i class="bi bi-clipboard"></i>
                        </button>
                        <small id="mensagemCopiado" class="text-success ms-2" style="display: none;">Copiado!</small>
                    </p>

                    {{-- Observações Internas Editáveis --}}
                    <div class="mb-3">
                        <p class="mb-1">
                            <strong>Observações Internas:</strong>
                            @can('is-admin-or-tecnico')
                            <button type="button" class="btn btn-link btn-sm p-0 ms-1" id="btnEditarObservacoes" aria-label="Editar Observações" data-bs-toggle="tooltip" title="Editar Observações">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            @endcan
                        </p>
                        <div id="containerObservacoes">
                            <div id="textoObservacoes" class="p-2 border rounded bg-light-subtle" style="white-space: pre-wrap; font-size: 0.9em; min-height: 60px;">{{ $atendimento->observacoes ?: 'Nenhuma.' }}</div>
                            @can('is-admin-or-tecnico')
                            <div id="formEditarObservacoes" style="display:none;" class="mt-2">
                                <textarea class="form-control form-control-sm mb-2" id="inputObservacoes" name="observacoes" rows="4">{{ $atendimento->observacoes }}</textarea>
                                <button type="button" class="btn btn-sm btn-success me-1" id="btnSalvarObservacoes"><i class="bi bi-check-lg"></i> Salvar</button>
                                <button type="button" class="btn btn-sm btn-secondary" id="btnCancelarObservacoes">Cancelar</button>
                                <div id="feedbackObservacoes" class="mt-2 small d-inline-block"></div>
                            </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            {{-- Laudo Técnico Editável --}}
            <hr class="my-3">
            <div>
                <h6 class="fw-bold">
                    <i class="bi bi-card-text me-1"></i>Laudo Técnico / Solução Aplicada:
                    @can('is-admin-or-tecnico')
                    <button type="button" class="btn btn-link btn-sm p-0 ms-1" id="btnEditarLaudo" aria-label="Editar Laudo Técnico" data-bs-toggle="tooltip" title="Editar Laudo Técnico">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    @endcan
                </h6>
                <div id="containerLaudo">
                    <div id="textoLaudo" class="p-3 border rounded bg-white shadow-sm" style="white-space: pre-wrap; font-size: 0.95em; min-height: 80px;">{{ $atendimento->laudo_tecnico ?: 'Ainda não informado.' }}</div>
                    @can('is-admin-or-tecnico')
                    <div id="formEditarLaudo" style="display:none;" class="mt-2">
                        <textarea class="form-control form-control-sm mb-2" id="inputLaudo" name="laudo_tecnico" rows="5">{{ $atendimento->laudo_tecnico }}</textarea>
                        <button type="button" class="btn btn-sm btn-success me-1" id="btnSalvarLaudo"><i class="bi bi-check-lg"></i> Salvar</button>
                        <button type="button" class="btn btn-sm btn-secondary" id="btnCancelarLaudo">Cancelar</button>
                        <div id="feedbackLaudo" class="mt-2 small d-inline-block"></div>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- =============================================== --}}
    {{-- BLOCO DE PEÇAS UTILIZADAS - REINTEGRADO --}}
    {{-- =============================================== --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="my-1"><i class="bi bi-tools me-2"></i>Peças Utilizadas no Atendimento</h5>
            @can('is-admin-or-tecnico')
            <a href="{{ route('saidas-estoque.create', ['atendimento_id' => $atendimento->id, 'estoque_id' => old('estoque_id_peca_rapida')]) }}"
                class="btn btn-success btn-sm">
                <i class="bi bi-plus-circle"></i> Registrar Nova Saída de Peça
            </a>
            @endcan
        </div>
        <div class="card-body p-0">
            @if ($atendimento->saidasEstoque && $atendimento->saidasEstoque->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Peça (ID Estoque)</th>
                            <th scope="col">Nome</th>
                            <th scope="col">Modelo</th>
                            <th scope="col">Marca</th>
                            <th scope="col" class="text-center">Qtd.</th>
                            <th scope="col" class="text-end">Preço Venda Unit.</th>
                            <th scope="col" class="text-end">Subtotal Peça</th>
                            <th scope="col">Data Saída</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($atendimento->saidasEstoque as $saida)
                        <tr>
                            <td>
                                @if($saida->estoque)
                                <a href="{{ route('estoque.show', $saida->estoque->id) }}">{{ $saida->estoque->id }}</a>
                                @else
                                N/A
                                @endif
                            </td>
                            <td>{{ $saida->estoque->nome ?? 'Peça não encontrada' }}</td>
                            <td>{{ $saida->estoque->modelo_compativel ?? '-' }}</td>
                            <td>{{ $saida->estoque->marca ?? '-' }}</td>
                            <td class="text-center">{{ $saida->quantidade }}</td>
                            <td class="text-end">R$ {{ number_format($saida->estoque->preco_venda ?? 0, 2, ',', '.') }}</td>
                            <td class="text-end">R$ {{ number_format($saida->quantidade * ($saida->estoque->preco_venda ?? 0), 2, ',', '.') }}</td>
                            <td>{{ $saida->data_saida ? $saida->data_saida->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-3">
                <p class="text-muted fst-italic mb-0">Nenhuma peça registrada como utilizada para este atendimento.</p>
            </div>
            @endif
        </div>
    </div>




    {{-- BLOCO DE VALORES DO ATENDIMENTO - Com Edição Inline para Valor e Desconto --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="my-1"><i class="bi bi-currency-dollar me-2"></i>Valores do Atendimento</h5>
            {{-- Botão para editar valores, visível apenas para admin --}}
            @can('is-admin')
            <button type="button" class="btn btn-outline-primary btn-sm" id="btnEditarValoresServico">
                <i class="bi bi-pencil-square"></i> Editar Valores
            </button>
            @endcan
        </div>
        <div class="card-body">
            @php
            // Cálculos permanecem os mesmos
            $valorTotalPecas = 0;
            if ($atendimento->saidasEstoque) {
            foreach ($atendimento->saidasEstoque as $saida) {
            if ($saida->estoque) {
            $precoVendaPeca = $saida->estoque->preco_venda ?? 0;
            $valorTotalPecas += $saida->quantidade * $precoVendaPeca;
            }
            }
            }
            $valorServicoAtual = $atendimento->valor_servico ?? 0;
            $descontoServicoAtual = $atendimento->desconto_servico ?? 0;
            $valorServicoLiquido = $valorServicoAtual - $descontoServicoAtual;
            $valorTotalAtendimento = $valorServicoLiquido + $valorTotalPecas;
            @endphp

            {{-- Div para exibir os valores --}}
            <div id="areaExibirValores">
                <dl class="row">
                    <dt class="col-sm-5">Valor da Mão de Obra/Serviço:</dt>
                    <dd class="col-sm-7 text-end" id="textoValorServico">R$ {{ number_format($valorServicoAtual, 2, ',', '.') }}</dd>

                    <dt class="col-sm-5">Desconto sobre Serviço:</dt>
                    <dd class="col-sm-7 text-end text-danger" id="textoDescontoServico">- R$ {{ number_format($descontoServicoAtual, 2, ',', '.') }}</dd>

                    <dt class="col-sm-5 border-top pt-2 fw-semibold">Subtotal do Serviço:</dt>
                    <dd class="col-sm-7 text-end border-top pt-2 fw-bold" id="textoSubtotalServico">R$ {{ number_format($valorServicoLiquido, 2, ',', '.') }}</dd>

                    <dt class="col-sm-5 mt-3">Valor Total das Peças Utilizadas:</dt>
                    <dd class="col-sm-7 text-end mt-3" id="textoValorTotalPecas">R$ {{ number_format($valorTotalPecas, 2, ',', '.') }}
                        @if($atendimento->saidasEstoque && $atendimento->saidasEstoque->isNotEmpty())
                        <small class="d-block text-muted">({{ $atendimento->saidasEstoque->sum('quantidade') }} peça(s)/item(ns))</small>
                        @endif
                    </dd>

                    <dt class="col-sm-5 border-top pt-3 fs-5 text-success">VALOR TOTAL DO ATENDIMENTO:</dt>
                    <dd class="col-sm-7 text-end border-top pt-3 fs-5 fw-bolder text-success" id="textoValorTotalAtendimento">R$ {{ number_format($valorTotalAtendimento, 2, ',', '.') }}</dd>
                </dl>
            </div>

            {{-- Formulário para editar valores (inicialmente oculto, visível apenas para admin) --}}
            @can('is-admin')
            <div id="formEditarValoresServico" style="display:none;" class="mt-3 pt-3 border-top">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="inputValorServico" class="form-label fw-semibold">Novo Valor do Serviço (R$):</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" id="inputValorServico" name="valor_servico" value="{{ number_format($valorServicoAtual, 2, '.', '') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="inputDescontoServico" class="form-label fw-semibold">Novo Desconto (R$):</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" id="inputDescontoServico" name="desconto_servico" value="{{ number_format($descontoServicoAtual, 2, '.', '') }}">
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-success me-1" id="btnSalvarValoresServico"><i class="bi bi-check-lg"></i> Salvar Valores</button>
                <button type="button" class="btn btn-sm btn-secondary" id="btnCancelarValoresServico">Cancelar</button>
                <div id="feedbackValoresServico" class="mt-2 small d-inline-block"></div>
            </div>
            @endcan
        </div>
    </div>



    <div class="mt-4 d-flex justify-content-between align-items-center">
        <div>
            <p class="text-muted small mb-0">Atendimento criado em:
                {{ $atendimento->created_at->format('d/m/Y H:i:s') }}
            </p>
            <p class="text-muted small">Última atualização: {{ $atendimento->updated_at->format('d/m/Y H:i:s') }}</p>
        </div>
        @can('is-admin-or-atendente')
        <form action="{{ route('atendimentos.destroy', $atendimento->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger"
                onclick="return confirm('Tem certeza que deseja excluir este atendimento? Todas as saídas de peças vinculadas serão mantidas no histórico de saídas, mas desvinculadas deste atendimento. Esta ação não poderá ser desfeita.')">
                <i class="bi bi-trash"></i> Excluir Atendimento
            </button>
        </form>
        @endcan
    </div>
</div>
@endsection

@push('styles')
<style>
    .badge.fs-6 {
        font-size: 0.95rem !important;
        padding: .5em .9em;
    }

    .bg-light-subtle {
        background-color: #fcfcfc !important;
        border-color: #e9ecef !important;
    }

    .user-select-all {
        user-select: all;
    }

    #feedbackGlobalAtendimentoShow .alert {
        margin-bottom: 1rem;
        padding: 0.75rem 1.25rem;
        font-size: 0.9rem;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const feedbackGlobal = document.getElementById('feedbackGlobalAtendimentoShow');

        // DEFINIÇÃO DE exibirFeedbackGlobal NO TOPO DO ESCOPO
        function exibirFeedbackGlobal(mensagem, tipo = 'success') {
            if (!feedbackGlobal) {
                console.warn("Elemento 'feedbackGlobalAtendimentoShow' não encontrado no DOM.");
                // Opcional: usar alert() como fallback se o elemento não existir
                // alert(`${tipo.toUpperCase()}: ${mensagem}`);
                return;
            }
            // Remove alerts antigos para não acumular
            while (feedbackGlobal.firstChild) {
                feedbackGlobal.removeChild(feedbackGlobal.firstChild);
            }
            const alertDiv = document.createElement('div');
            // Garante que o tipo seja válido para classes de alerta Bootstrap
            const alertType = ['success', 'danger', 'warning', 'info'].includes(tipo) ? tipo : 'info';
            alertDiv.className = `alert alert-${alertType} alert-dismissible fade show`;
            alertDiv.setAttribute('role', 'alert');
            alertDiv.innerHTML = `${mensagem}
                              <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>`;
            feedbackGlobal.appendChild(alertDiv);

            // Auto-close (garantir que bootstrap esteja disponível ou usar fallback)
            setTimeout(() => {
                const currentAlert = feedbackGlobal.querySelector('.alert');
                if (currentAlert) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Alert && bootstrap.Alert.getOrCreateInstance) {
                        try {
                            bootstrap.Alert.getOrCreateInstance(currentAlert).close();
                        } catch (e) {
                            console.warn("Erro ao tentar fechar alerta Bootstrap:", e);
                            // Fallback se o getOrCreateInstance falhar por algum motivo (ex: elemento já sendo removido)
                            if (currentAlert.parentNode) currentAlert.parentNode.removeChild(currentAlert);
                        }
                    } else if (currentAlert.classList.contains('show')) { // Fallback simples
                        currentAlert.classList.remove('show');
                        setTimeout(() => {
                            if (currentAlert.parentNode) currentAlert.parentNode.removeChild(currentAlert);
                        }, 150);
                    } else if (currentAlert.parentNode) {
                        currentAlert.parentNode.removeChild(currentAlert);
                    }
                }
            }, 7000); // Aumentado para 7 segundos
        }

        function setupInlineEdit(config) {
            const {
                btnEditId,
                textDisplayId,
                formEditId,
                inputId,
                btnSaveId,
                btnCancelId,
                feedbackId,
                saveUrl,
                fieldName,
                placeholderText
            } = config;
            const btnEdit = document.getElementById(btnEditId);
            const textDisplay = document.getElementById(textDisplayId);
            const formEdit = document.getElementById(formEditId);
            const inputElement = document.getElementById(inputId);
            const btnSave = document.getElementById(btnSaveId);
            const btnCancel = document.getElementById(btnCancelId);
            const feedbackElement = document.getElementById(feedbackId);

            if (btnEdit && textDisplay && formEdit && inputElement && btnSave && btnCancel && feedbackElement) {
                btnEdit.addEventListener('click', function() {
                    textDisplay.style.display = 'none';
                    formEdit.style.display = 'block';
                    let currentText = textDisplay.innerText.trim();
                    if (currentText === placeholderText) currentText = '';
                    inputElement.value = currentText;
                    inputElement.focus();
                    feedbackElement.innerHTML = '';
                    btnEdit.style.display = 'none';
                });

                btnCancel.addEventListener('click', function() {
                    textDisplay.style.display = 'block';
                    formEdit.style.display = 'none';
                    feedbackElement.innerHTML = '';
                    btnEdit.style.display = 'inline-block';
                });

                btnSave.addEventListener('click', function() {
                    const newValue = inputElement.value;
                    feedbackElement.innerHTML = '<span class="text-info small">Salvando...</span>';
                    btnSave.disabled = true;
                    btnCancel.disabled = true;

                    fetch(saveUrl, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json' // Importante para o Laravel saber que esperamos JSON
                            },
                            body: JSON.stringify({
                                [fieldName]: newValue
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(errData => Promise.reject({
                                    status: response.status,
                                    data: errData,
                                    responseText: JSON.stringify(errData)
                                }));
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                textDisplay.innerText = data.novo_valor || placeholderText;
                                exibirFeedbackGlobal(data.message, 'success');
                            } else {
                                exibirFeedbackGlobal(data.message || 'Erro ao salvar o campo ' + fieldName + '.', 'danger');
                            }
                        })
                        .catch(error => {
                            console.error('Erro AJAX para ' + fieldName + ':', error.status, error.data, error.responseText);
                            let errorMessage = 'Erro de comunicação ao salvar ' + fieldName + '.';
                            if (error && error.data && error.data.message) {
                                errorMessage = error.data.message;
                                if (error.data.errors && error.data.errors[fieldName]) {
                                    errorMessage += ' Detalhes: ' + error.data.errors[fieldName].join(', ');
                                } else if (error.data.errors) {
                                    // Mostrar todos os erros de validação se houver
                                    let errorsText = Object.values(error.data.errors).flat().join(' ');
                                    errorMessage += ' Detalhes: ' + errorsText;
                                }
                            } else if (error && error.message && typeof error.message === 'string') {
                                errorMessage = error.message;
                            }
                            exibirFeedbackGlobal(errorMessage, 'danger');
                        })
                        .finally(() => {
                            textDisplay.style.display = 'block';
                            formEdit.style.display = 'none';
                            btnSave.disabled = false;
                            btnCancel.disabled = false;
                            btnEdit.style.display = 'inline-block';
                            feedbackElement.innerHTML = '';
                        });
                });
            }
        }

        @can('is-admin-or-tecnico')
        setupInlineEdit({
            btnEditId: 'btnEditarObservacoes',
            textDisplayId: 'textoObservacoes',
            formEditId: 'formEditarObservacoes',
            inputId: 'inputObservacoes',
            btnSaveId: 'btnSalvarObservacoes',
            btnCancelId: 'btnCancelarObservacoes',
            feedbackId: 'feedbackObservacoes',
            saveUrl: '{{ route("atendimentos.atualizarCampoAjax", ["atendimento" => $atendimento->id, "campo" => "observacoes"]) }}',
            fieldName: 'observacoes',
            placeholderText: 'Nenhuma.'
        });

        setupInlineEdit({
            btnEditId: 'btnEditarLaudo',
            textDisplayId: 'textoLaudo',
            formEditId: 'formEditarLaudo',
            inputId: 'inputLaudo',
            btnSaveId: 'btnSalvarLaudo',
            btnCancelId: 'btnCancelarLaudo',
            feedbackId: 'feedbackLaudo',
            saveUrl: '{{ route("atendimentos.atualizarCampoAjax", ["atendimento" => $atendimento->id, "campo" => "laudo_tecnico"]) }}',
            fieldName: 'laudo_tecnico',
            placeholderText: 'Ainda não informado.'
        });
        @endcan

        const formAtualizarStatusRapido = document.getElementById('formAtualizarStatusRapido');
        // const feedbackStatusRapido = document.getElementById('feedbackStatusRapido'); // Feedback local, se usar
        const statusAtualTextoSpan = document.getElementById('statusAtualTexto');
        const statusAtualNomeSpan = document.getElementById('statusAtualNome');

        if (formAtualizarStatusRapido) {
            formAtualizarStatusRapido.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(this);
                const url = this.action;
                const btnSubmitStatus = this.querySelector('button[type="submit"]');
                const originalButtonHtml = btnSubmitStatus.innerHTML;

                btnSubmitStatus.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...';
                btnSubmitStatus.disabled = true;
                // if (feedbackStatusRapido) feedbackStatusRapido.innerHTML = '<span class="text-info small">Atualizando...</span>';

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(async response => { // Tornar async
                        const responseClone = response.clone(); // Clonar para ler o corpo múltiplas vezes se necessário (embora tentemos evitar)
                        if (!response.ok) {
                            let errorPayload = {
                                status: response.status,
                                data: {
                                    message: "Erro desconhecido."
                                }
                            };
                            try {
                                errorPayload.data = await response.json(); // Tenta ler como JSON
                            } catch (e) {
                                errorPayload.data._rawResponse = await responseClone.text(); // Usa o clone para ler como texto
                                errorPayload.data._isTextError = true;
                                console.error("Resposta de erro não JSON ou erro de parse:", errorPayload.data._rawResponse);
                            }
                            return Promise.reject(errorPayload);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && (data.success || data.feedback_tipo === 'info' || data.feedback_tipo === 'warning')) { // Considera info/warning como "sucesso" da operação de status
                            exibirFeedbackGlobal(data.message, data.feedback_tipo || 'success');
                            if (statusAtualTextoSpan && statusAtualNomeSpan && data.novo_status && data.novo_status_classe_completa && data.novo_status_icon) {
                                statusAtualNomeSpan.textContent = data.novo_status;
                                statusAtualTextoSpan.className = 'badge rounded-pill fs-6 ' + data.novo_status_classe_completa;
                                const iconElement = statusAtualTextoSpan.querySelector('i');
                                if (iconElement) {
                                    iconElement.className = data.novo_status_icon + ' me-1';
                                }
                            }

                            // <<<< NOVA LÓGICA DE REDIRECIONAMENTO >>>>
                            if (data.redirect_to_edit) {
                                // Adiciona um pequeno delay para o usuário ler a mensagem antes de redirecionar
                                setTimeout(function() {
                                    window.location.href = data.redirect_to_edit;
                                }, 3000); // Redireciona após 3 segundos
                            }
                            // <<<< FIM DA NOVA LÓGICA >>>>

                        } else {
                            exibirFeedbackGlobal(data.message || 'Ocorreu um erro ao atualizar o status.', data.feedback_tipo || 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Erro AJAX ao atualizar status:', error);
                        let errorMsg = 'Erro de comunicação ao atualizar status.';
                        if (error && error.status === 422 && error.data && error.data.errors) {
                            errorMsg = "Por favor, corrija os seguintes erros: ";
                            let validationErrors = [];
                            for (const key in error.data.errors) {
                                validationErrors.push(error.data.errors[key].join(', '));
                            }
                            errorMsg += validationErrors.join(' ');
                        } else if (error && error.data && error.data.message) {
                            errorMsg = error.data.message;
                        } else if (error && error.data && error.data._isTextError) {
                            errorMsg = "Erro no servidor. Detalhes no console.";
                        } else if (error && error.message) { // Erro de rede geral ou de promessa rejeitada sem data
                            errorMsg = error.message;
                        }
                        exibirFeedbackGlobal(errorMsg, 'danger');
                    })
                    .finally(() => {
                        // if (feedbackStatusRapido) feedbackStatusRapido.innerHTML = '';
                        btnSubmitStatus.innerHTML = originalButtonHtml;
                        btnSubmitStatus.disabled = false;
                    });
            });
        }

        // Script de cópia
        const codigoConsultaSpan = document.getElementById('codigoConsultaParaCopiar');
        const btnCopiarCodigo = document.getElementById('btnCopiarCodigo');
        const mensagemCopiado = document.getElementById('mensagemCopiado');

        function executarCopia() {
            if (!codigoConsultaSpan) return;
            const textoParaCopiar = codigoConsultaSpan.innerText.trim();
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(textoParaCopiar)
                    .then(() => {
                        if (mensagemCopiado) {
                            mensagemCopiado.style.display = 'inline';
                            setTimeout(() => {
                                mensagemCopiado.style.display = 'none';
                            }, 2000);
                        }
                        if (btnCopiarCodigo) {
                            const originalIconHTML = btnCopiarCodigo.innerHTML;
                            btnCopiarCodigo.innerHTML = '<i class="bi bi-check-lg text-success"></i>';
                            btnCopiarCodigo.disabled = true;
                            setTimeout(() => {
                                btnCopiarCodigo.innerHTML = originalIconHTML;
                                btnCopiarCodigo.disabled = false;
                            }, 2000);
                        }
                    })
                    .catch(err => {
                        console.error('Erro ao copiar: ', err);
                        alert('Não foi possível copiar o código. Tente manualmente.');
                    });
            } else {
                alert('Cópia automática não suportada. Copie manualmente.');
            }
        }
        if (codigoConsultaSpan) codigoConsultaSpan.addEventListener('click', executarCopia);
        if (btnCopiarCodigo) btnCopiarCodigo.addEventListener('click', executarCopia);

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
    const btnEditarValores = document.getElementById('btnEditarValoresServico');
    const areaExibirValores = document.getElementById('areaExibirValores');
    const formEditarValores = document.getElementById('formEditarValoresServico');

    if (btnEditarValores && areaExibirValores && formEditarValores) {
        const inputValorServico = document.getElementById('inputValorServico');
        const inputDescontoServico = document.getElementById('inputDescontoServico');
        const btnSalvarValores = document.getElementById('btnSalvarValoresServico');
        const btnCancelarValores = document.getElementById('btnCancelarValoresServico');
        const feedbackValores = document.getElementById('feedbackValoresServico');

        // Elementos de texto para atualização
        const textoValorServico = document.getElementById('textoValorServico');
        const textoDescontoServico = document.getElementById('textoDescontoServico');
        const textoSubtotalServico = document.getElementById('textoSubtotalServico');
        const textoValorTotalAtendimento = document.getElementById('textoValorTotalAtendimento');


        btnEditarValores.addEventListener('click', function() {
            areaExibirValores.style.display = 'none';
            formEditarValores.style.display = 'block';
            // Preencher inputs com valores atuais (removendo 'R$ ' e trocando ',' por '.')
            inputValorServico.value = parseFloat(textoValorServico.innerText.replace('R$ ', '').replace(/\./g, '').replace(',', '.') || '0').toFixed(2);
            inputDescontoServico.value = parseFloat(textoDescontoServico.innerText.replace('- R$ ', '').replace(/\./g, '').replace(',', '.') || '0').toFixed(2);
            feedbackValores.innerHTML = '';
            btnEditarValores.style.display = 'none';
        });

        btnCancelarValores.addEventListener('click', function() {
            areaExibirValores.style.display = 'block';
            formEditarValores.style.display = 'none';
            feedbackValores.innerHTML = '';
            btnEditarValores.style.display = 'inline-block';
        });

        btnSalvarValores.addEventListener('click', function() {
            const novoValorServico = inputValorServico.value;
            const novoDescontoServico = inputDescontoServico.value;

            feedbackValores.innerHTML = '<span class="text-info small">Salvando valores...</span>';
            btnSalvarValores.disabled = true;
            btnCancelarValores.disabled = true;

            fetch('{{ route("atendimentos.atualizarValoresServicoAjax", $atendimento->id) }}', { // <<<< ROTA ALTERADA
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        valor_servico: novoValorServico,
                        desconto_servico: novoDescontoServico
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(errData => Promise.reject({
                            status: response.status,
                            data: errData
                        }));
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.novos_valores) {
                        textoValorServico.innerText = 'R$ ' + data.novos_valores.valor_servico;
                        textoDescontoServico.innerText = '- R$ ' + data.novos_valores.desconto_servico;
                        textoSubtotalServico.innerText = 'R$ ' + data.novos_valores.subtotal_servico;
                        textoValorTotalAtendimento.innerText = 'R$ ' + data.novos_valores.valor_total_atendimento;
                        exibirFeedbackGlobal(data.message, 'success');
                    } else {
                        exibirFeedbackGlobal(data.message || 'Erro ao salvar valores.', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Erro AJAX ao salvar valores:', error);
                    let errorMessage = 'Erro de comunicação ao salvar valores.';
                    if (error && error.data && error.data.message) {
                        errorMessage = error.data.message;
                        if (error.data.errors) {
                            let errorsText = Object.values(error.data.errors).flat().join(' ');
                            errorMessage += ' Detalhes: ' + errorsText;
                        }
                    }
                    exibirFeedbackGlobal(errorMessage, 'danger');
                })
                .finally(() => {
                    areaExibirValores.style.display = 'block';
                    formEditarValores.style.display = 'none';
                    btnSalvarValores.disabled = false;
                    btnCancelarValores.disabled = false;
                    btnEditarValores.style.display = 'inline-block';
                    feedbackValores.innerHTML = '';
                });
        });
    }
</script>
@endpush
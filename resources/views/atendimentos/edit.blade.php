@extends('layouts.app')

@section('title', 'Editar Atendimento #' . $atendimento->id)

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .card-header h5 {
            font-weight: 500;
        }

        .form-label {
            font-weight: 500;
            /* Um pouco mais de destaque para os labels */
        }

        /* Adicionar um espaçamento inferior maior para os cards para separá-los bem */
        .card.shadow-sm {
            margin-bottom: 1.8rem !important;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-pencil-square text-warning"></i> Editar Atendimento #{{ $atendimento->id }}</h1>
            <div>
                <a href="{{ route('atendimentos.show', $atendimento->id) }}" class="btn btn-sm btn-info"><i
                        class="bi bi-eye"></i> Ver Detalhes</a>
                <a href="{{ route('atendimentos.index') }}" class="btn btn-sm btn-secondary"><i class="bi bi-list-ul"></i>
                    Voltar para Lista</a>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="bi bi-exclamation-triangle-fill"></i> Ops! Verifique os erros abaixo:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('atendimentos.update', $atendimento->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- CARD 1: Dados do Cliente e Aparelho --}}
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-person-badge"></i> Cliente e Aparelho</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label for="cliente_id" class="form-label">Cliente <span class="text-danger">*</span></label>
                            <select class="form-select @error('cliente_id') is-invalid @enderror" id="cliente_id"
                                name="cliente_id" required>
                                <option value="">Selecione o Cliente</option>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ old('cliente_id', $atendimento->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nome_completo }} ({{ $cliente->cpf_cnpj }})
                                    </option>
                                @endforeach
                            </select>
                            @error('cliente_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            {{-- Botão para novo cliente pode ir aqui ou no form de criação --}}
                            <small><a href="{{ route('clientes.create', ['redirect_to' => url()->current()]) }}"
                                    target="_blank" class="text-decoration-none mt-1 d-inline-block"><i
                                        class="bi bi-plus-circle"></i> Cadastrar Novo Cliente</a></small>
                        </div>

                        <div class="col-md-5 mb-3">
                            <label for="descricao_aparelho" class="form-label">Aparelho (Marca/Modelo/Cor) <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('descricao_aparelho') is-invalid @enderror"
                                id="descricao_aparelho" name="descricao_aparelho"
                                value="{{ old('descricao_aparelho', $atendimento->descricao_aparelho) }}"
                                placeholder="Ex: iPhone X Preto" required>
                            @error('descricao_aparelho')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="problema_relatado" class="form-label">Problema Relatado pelo Cliente <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control @error('problema_relatado') is-invalid @enderror"
                            id="problema_relatado" name="problema_relatado" rows="3"
                            required>{{ old('problema_relatado', $atendimento->problema_relatado) }}</textarea>
                        @error('problema_relatado')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- CARD 2: Status, Prazos e Técnico --}}
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-ui-checks-grid"></i> Status, Prazos e Técnico</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="data_entrada_display" class="form-label">Data de Entrada (Registrada)</label>
                            <input type="text" class="form-control bg-light" id="data_entrada_display"
                                value="{{ $atendimento->data_entrada ? $atendimento->data_entrada->format('d/m/Y H:i:s') : 'N/A' }}"
                                readonly disabled>
                            <small class="form-text text-muted">A data de entrada original não pode ser alterada.</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Status do Serviço <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status"
                                required>
                                @foreach (App\Models\Atendimento::getPossibleStatuses() as $s)
                                    <option value="{{ $s }}" {{ old('status', $atendimento->status) == $s ? 'selected' : '' }}>
                                        {{ $s }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status_pagamento" class="form-label">Status do Pagamento <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('status_pagamento') is-invalid @enderror"
                                id="status_pagamento" name="status_pagamento" required>
                                @foreach (App\Models\Atendimento::getPossiblePaymentStatuses() as $sp)
                                    <option value="{{ $sp }}" {{ old('status_pagamento', $atendimento->status_pagamento) == $sp ? 'selected' : '' }}>
                                        {{ $sp }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status_pagamento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tecnico_id" class="form-label">Técnico Responsável</label>
                            <select class="form-select @error('tecnico_id') is-invalid @enderror" id="tecnico_id"
                                name="tecnico_id">
                                <option value="">Não atribuído</option>
                                @foreach ($tecnicos as $tecnico)
                                    <option value="{{ $tecnico->id }}" {{ old('tecnico_id', $atendimento->tecnico_id) == $tecnico->id ? 'selected' : '' }}>
                                        {{ $tecnico->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tecnico_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="data_conclusao" class="form-label">Data de Conclusão (Opcional)</label>
                            <input type="date" class="form-control @error('data_conclusao') is-invalid @enderror"
                                id="data_conclusao" name="data_conclusao"
                                value="{{ old('data_conclusao', $atendimento->data_conclusao ? $atendimento->data_conclusao->format('Y-m-d') : '') }}">
                            @error('data_conclusao')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 3: Diagnóstico e Solução --}}
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-clipboard2-pulse-fill"></i> Diagnóstico e Solução</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="laudo_tecnico" class="form-label">Laudo Técnico / Solução Aplicada</label>
                        <textarea class="form-control @error('laudo_tecnico') is-invalid @enderror" id="laudo_tecnico"
                            name="laudo_tecnico" rows="5" {{ Gate::denies('is-admin-or-tecnico') ? 'readonly' : '' }}>{{ old('laudo_tecnico', $atendimento->laudo_tecnico) }}</textarea>
                        @error('laudo_tecnico')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @cannot('is-admin-or-tecnico')
                        @if($atendimento->laudo_tecnico)
                            <small class="form-text text-muted">Apenas administradores ou técnicos podem editar o laudo.</small>
                        @endif
                        @endcannot
                    </div>
                    <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações (Internas/Gerais)</label>
                        <textarea class="form-control @error('observacoes') is-invalid @enderror" id="observacoes"
                            name="observacoes" rows="3">{{ old('observacoes', $atendimento->observacoes) }}</textarea>
                        @error('observacoes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>


            {{-- CARD 4: Valores e Pagamento --}}
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-currency-dollar"></i> Valores e Pagamento</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="valor_servico" class="form-label">Valor do Serviço (Mão de Obra) R$</label>
                            <input type="number" step="0.01"
                                class="form-control @error('valor_servico') is-invalid @enderror" id="valor_servico"
                                name="valor_servico"
                                value="{{ old('valor_servico', number_format($atendimento->valor_servico ?? 0, 2, '.', '')) }}"
                                min="0" {{ Gate::denies('is-admin') ? 'readonly class=bg-light' : '' }}>
                            @error('valor_servico')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @cannot('is-admin') <small class="form-text text-muted">Apenas administradores podem
                                alterar.</small> @endcannot
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="desconto_servico" class="form-label">Desconto sobre Serviço R$</label>
                            <input type="number" step="0.01"
                                class="form-control @error('desconto_servico') is-invalid @enderror" id="desconto_servico"
                                name="desconto_servico"
                                value="{{ old('desconto_servico', number_format($atendimento->desconto_servico ?? 0, 2, '.', '')) }}"
                                min="0" {{ Gate::denies('is-admin') ? 'readonly class=bg-light' : '' }}>
                            @error('desconto_servico')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @cannot('is-admin') <small class="form-text text-muted">Apenas administradores podem
                                alterar.</small> @endcannot
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="forma_pagamento" class="form-label">Forma de Pagamento</label>
                            <select class="form-select @error('forma_pagamento') is-invalid @enderror" id="forma_pagamento"
                                name="forma_pagamento">
                                <option value="">Selecione se pago</option>
                                @foreach($formasPagamento as $opcao) {{-- Assumindo que $formasPagamento é passado pelo
                                    controller --}}
                                    <option value="{{ $opcao }}" {{ old('forma_pagamento', $atendimento->forma_pagamento) == $opcao ? 'selected' : '' }}>
                                        {{ $opcao }}
                                    </option>
                                @endforeach
                            </select>
                            @error('forma_pagamento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Subtotal Serviço (M.Obra - Desc.):</strong>
                                <span id="displaySubtotalServico" class="fw-bold">R$ ...</span>
                            </p>
                            <p class="mb-1"><strong>(+) Total Peças (da OS):</strong>
                                @php
                                    $totalPecasOs = 0;
                                    if ($atendimento->saidasEstoque) {
                                        foreach ($atendimento->saidasEstoque as $saida) {
                                            if ($saida->estoque)
                                                $totalPecasOs += $saida->quantidade * ($saida->estoque->preco_venda ?? 0);
                                        }
                                    }
                                @endphp
                                <span id="displayTotalPecas" class="fw-bold">R$
                                    {{ number_format($totalPecasOs, 2, ',', '.') }}</span>
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-0 fs-5"><strong>VALOR TOTAL DA OS:</strong>
                                <span id="displayValorTotalOs" class="fw-bolder text-success">R$ ...</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Salvar Alterações</button>
                <a href="{{ route('atendimentos.show', $atendimento->id) }}" class="btn btn-secondary ms-2">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    {{-- jQuery e jQuery UI já devem estar carregados pelo layout principal --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const valorServicoInput = document.getElementById('valor_servico');
            const descontoServicoInput = document.getElementById('desconto_servico');
            const displaySubtotalServico = document.getElementById('displaySubtotalServico');
            const displayTotalPecas = document.getElementById('displayTotalPecas'); // O valor aqui é fixo da OS
            const displayValorTotalOs = document.getElementById('displayValorTotalOs');

            function calcularEAtualizarTotais() {
                let valorServico = parseFloat(valorServicoInput.value.replace(',', '.')) || 0;
                let descontoServico = parseFloat(descontoServicoInput.value.replace(',', '.')) || 0;

                if (descontoServico > valorServico) {
                    descontoServico = valorServico;
                    // Opcional: atualizar o campo de desconto se ele for corrigido
                    // descontoServicoInput.value = descontoServico.toFixed(2);
                }

                let subtotalServico = valorServico - descontoServico;
                let totalPecas = parseFloat(displayTotalPecas.textContent.replace('R$ ', '').replace(/\./g, '').replace(',', '.')) || 0;
                let valorTotalOs = subtotalServico + totalPecas;

                displaySubtotalServico.textContent = 'R$ ' + subtotalServico.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                displayValorTotalOs.textContent = 'R$ ' + valorTotalOs.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            if (valorServicoInput && descontoServicoInput && displaySubtotalServico && displayValorTotalOs && displayTotalPecas) {
                valorServicoInput.addEventListener('input', calcularEAtualizarTotais);
                descontoServicoInput.addEventListener('input', calcularEAtualizarTotais);
                // Calcula na carga inicial
                calcularEAtualizarTotais();
            }

            // Lógica do Select2 para clientes (se você decidir usar Select2)
            // $('#cliente_id').select2({ theme: 'bootstrap-5' });
            // $('#tecnico_id').select2({ theme: 'bootstrap-5', placeholder: 'Não atribuído', allowClear: true });
            // etc.
        });
    </script>
@endpush
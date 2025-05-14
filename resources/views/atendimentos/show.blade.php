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

    {{-- Mensagens de sucesso/erro específicas para esta página (ex: atualização de status) --}}
    @if(session('success_status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success_status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('error_status'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error_status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    {{-- Erros de validação do formulário de status rápido --}}
    @if ($errors->has('status_rapido_form'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="bi bi-exclamation-triangle-fill"></i> Erro ao atualizar status:</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->get('status_rapido_form.*') as $messages)
            @foreach ($messages as $message)
            <li>{{ $message }}</li>
            @endforeach
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                        <span class="badge rounded-pill fs-6
                                @if($atendimento->status == 'Em diagnóstico') bg-info text-dark
                                @elseif($atendimento->status == 'Aguardando peça') bg-warning text-dark
                                @elseif($atendimento->status == 'Em manutenção') bg-primary
                                @elseif($atendimento->status == 'Pronto para entrega') bg-success
                                @elseif($atendimento->status == 'Entregue') bg-secondary
                                @elseif($atendimento->status == 'Cancelado' || $atendimento->status == 'Reprovado') bg-danger
                                @else bg-light text-dark @endif">
                            <i class="bi bi-clipboard-data me-1"></i> {{ $atendimento->status }}
                        </span>
                    </p>

                    {{-- Formulário de Alteração Rápida de Status --}}
                    @can('is-internal-user') {{-- Ou a permissão que você definiu para alterar status --}}
                    <div class="mt-2 mb-3 p-2 border rounded bg-light-subtle">
                        <form action="{{ route('atendimentos.atualizarStatus', $atendimento->id) }}" method="POST" class="d-flex align-items-center flex-wrap">
                            @csrf
                            @method('PATCH')
                            <label for="status_rapido" class="form-label me-2 mb-0 fw-semibold small">Alterar Status:</label>
                            <select class="form-select form-select-sm flex-grow-1 me-2 mb-1 mb-sm-0" style="min-width: 180px;" id="status_rapido" name="status" aria-label="Alterar status do atendimento">
                                @php
                                $todosOsStatus = \App\Models\Atendimento::getPossibleStatuses(); // Supondo que você tenha um método assim no Model
                                // Ou defina o array diretamente:
                                $todosOsStatus = ['Em diagnóstico', 'Aguardando peça', 'Em manutenção', 'Pronto para entrega', 'Entregue', 'Cancelado', 'Reprovado'];
                                @endphp
                                @foreach ($todosOsStatus as $s)
                                <option value="{{ $s }}" {{ $atendimento->status == $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i> Salvar</button>
                        </form>
                    </div>
                    @endcan

                    <p class="mb-2"><strong>Técnico Responsável:</strong> {{ $atendimento->tecnico->name ?? 'Não atribuído' }}</p>
                    <p class="mb-2"><strong>Data de Conclusão Estimada/Real:</strong>
                        {{ $atendimento->data_conclusao ? $atendimento->data_conclusao->format('d/m/Y') : ($atendimento->status == 'Entregue' || $atendimento->status == 'Cancelado' || $atendimento->status == 'Reprovado' ? 'Concluído/Finalizado' : 'Não concluído') }}
                    </p>
                    <p class="mb-2">
                        <strong>Código de Consulta (Cliente):</strong>
                        <span id="codigoConsultaParaCopiar" class="fw-bold user-select-all" style="cursor: pointer;" title="Clique para copiar o código">
                            {{ $atendimento->codigo_consulta }}
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-1 py-0 px-1" id="btnCopiarCodigo" title="Copiar código">
                            <i class="bi bi-clipboard-check"></i> {{-- Ou bi-clipboard --}}
                        </button>
                        <small id="mensagemCopiado" class="text-success ms-2" style="display: none;">Copiado!</small>
                    </p>
                    <p class="mb-1"><strong>Observações Internas:</strong></p>
                    <div class="p-2 border rounded bg-light mb-3" style="white-space: pre-wrap; font-size: 0.9em;">
                        {{ $atendimento->observacoes ?? 'Nenhuma.' }}
                    </div>
                </div>
            </div>

            @if($atendimento->laudo_tecnico)
            <hr class="my-3">
            <div>
                <h6 class="fw-bold"><i class="bi bi-card-text me-1"></i>Laudo Técnico / Solução Aplicada:</h6>
                <div class="p-3 border rounded bg-white shadow-sm" style="white-space: pre-wrap; font-size: 0.95em;">
                    {{ $atendimento->laudo_tecnico }}
                </div>
            </div>
            @else
            <p class="mt-3 mb-0"><strong>Laudo Técnico:</strong> <span class="text-muted fst-italic">Ainda não informado.</span></p>
            @endif
        </div>
    </div>

    {{-- Bloco de Valores do Atendimento --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <h5 class="my-1"><i class="bi bi-currency-dollar me-2"></i>Valores do Atendimento</h5>
        </div>
        <div class="card-body">
            @php
            $valorTotalPecas = 0;
            if ($atendimento->saidasEstoque) {
            foreach ($atendimento->saidasEstoque as $saida) {
            if ($saida->estoque) {
            $precoVendaPeca = $saida->estoque->preco_venda ?? 0;
            $valorTotalPecas += $saida->quantidade * $precoVendaPeca;
            }
            }
            }
            $valorServicoLiquido = ($atendimento->valor_servico ?? 0) - ($atendimento->desconto_servico ?? 0);
            $valorTotalAtendimento = $valorServicoLiquido + $valorTotalPecas;
            @endphp

            <dl class="row">
                <dt class="col-sm-5">Valor da Mão de Obra/Serviço:</dt>
                <dd class="col-sm-7 text-end">R$ {{ number_format($atendimento->valor_servico ?? 0, 2, ',', '.') }}</dd>

                <dt class="col-sm-5">Desconto sobre Serviço:</dt>
                <dd class="col-sm-7 text-end text-danger">- R$
                    {{ number_format($atendimento->desconto_servico ?? 0, 2, ',', '.') }}
                </dd>

                <dt class="col-sm-5 border-top pt-2 fw-semibold">Subtotal do Serviço:</dt>
                <dd class="col-sm-7 text-end border-top pt-2 fw-bold">R$ {{ number_format($valorServicoLiquido, 2, ',', '.') }}
                </dd>

                <dt class="col-sm-5 mt-3">Valor Total das Peças Utilizadas:</dt>
                <dd class="col-sm-7 text-end mt-3">R$ {{ number_format($valorTotalPecas, 2, ',', '.') }}
                    @if($atendimento->saidasEstoque && $atendimento->saidasEstoque->isNotEmpty())
                    <small class="d-block text-muted">({{ $atendimento->saidasEstoque->sum('quantidade') }}
                        peça(s)/item(ns))</small>
                    @endif
                </dd>

                <dt class="col-sm-5 border-top pt-3 fs-5 text-success">VALOR TOTAL DO ATENDIMENTO:</dt>
                <dd class="col-sm-7 text-end border-top pt-3 fs-5 fw-bolder text-success">R$
                    {{ number_format($valorTotalAtendimento, 2, ',', '.') }}
                </dd>
            </dl>
        </div>
    </div>

    {{-- Bloco de Peças Utilizadas --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="my-1"><i class="bi bi-tools me-2"></i>Peças Utilizadas no Atendimento</h5>
            @can('is-admin-or-tecnico') {{-- Apenas admin ou técnico podem adicionar peças --}}
            <a href="{{ route('saidas-estoque.create', ['atendimento_id' => $atendimento->id, 'estoque_id' => old('estoque_id_peca_rapida')]) }}"
                class="btn btn-success btn-sm">
                <i class="bi bi-plus-circle"></i> Registrar Nova Saída de Peça
            </a>
            @endcan
        </div>
        <div class="card-body p-0"> {{-- p-0 para a tabela colar nas bordas do card-body --}}
            @if ($atendimento->saidasEstoque && $atendimento->saidasEstoque->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover mb-0"> {{-- mb-0 para remover margem inferior da tabela --}}
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
                            {{-- <th scope="col">Obs. Saída</th> --}}
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
                            {{-- <td>{{ Str::limit($saida->observacoes, 30) ?? '-' }}</td> --}}
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

    <div class="mt-4 d-flex justify-content-between align-items-center">
        <div>
            <p class="text-muted small mb-0">Atendimento criado em:
                {{ $atendimento->created_at->format('d/m/Y H:i:s') }} por {{-- Adicionar quem criou se tiver essa info --}}
            </p>
            <p class="text-muted small">Última atualização: {{ $atendimento->updated_at->format('d/m/Y H:i:s') }} por {{-- Adicionar quem atualizou --}}</p>
        </div>
        @can('is-admin-or-atendente') {{-- Apenas Admin ou Atendente podem excluir um atendimento --}}
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
{{-- Bootstrap Icons já devem estar no layout principal --}}
<style>
    .badge.fs-6 {
        font-size: 0.95rem !important;
        padding: .5em .9em;
    }

    .bg-light-subtle {
        background-color: #f8f9fa !important;
    }

    /* Para o formulário de status rápido */
    .user-select-all {
        user-select: all;
    }

    /* Facilita copiar o código de consulta */
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const codigoConsultaSpan = document.getElementById('codigoConsultaParaCopiar');
        const btnCopiarCodigo = document.getElementById('btnCopiarCodigo');
        const mensagemCopiado = document.getElementById('mensagemCopiado');

        function copiarCodigo() {
            if (codigoConsultaSpan) {
                const textoParaCopiar = codigoConsultaSpan.innerText.trim(); // Pega o texto do span

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(textoParaCopiar)
                        .then(() => {
                            // Sucesso na cópia
                            if (mensagemCopiado) {
                                mensagemCopiado.style.display = 'inline';
                                setTimeout(() => {
                                    mensagemCopiado.style.display = 'none';
                                }, 2000); // Esconde a mensagem após 2 segundos
                            }
                            console.log('Código de consulta copiado para a área de transferência!');

                            // Opcional: Mudar o ícone do botão temporariamente
                            if (btnCopiarCodigo) {
                                const originalIcon = btnCopiarCodigo.innerHTML;
                                btnCopiarCodigo.innerHTML = '<i class="bi bi-check-lg text-success"></i>';
                                setTimeout(() => {
                                    btnCopiarCodigo.innerHTML = originalIcon;
                                }, 2000);
                            }
                        })
                        .catch(err => {
                            console.error('Erro ao copiar o código de consulta: ', err);
                            alert('Não foi possível copiar o código. Tente manualmente.');
                        });
                } else {
                    // Fallback para navegadores mais antigos (raro hoje em dia)
                    // Esta parte é mais complexa e pode não ser necessária
                    console.warn('API Clipboard não suportada. Copia manual sugerida.');
                    alert('Seu navegador não suporta a cópia automática. Por favor, selecione e copie o código manualmente.');
                }
            }
        }

        // Adiciona o evento de clique ao span (se você ainda quiser que o próprio código seja clicável)
        if (codigoConsultaSpan) {
            codigoConsultaSpan.addEventListener('click', copiarCodigo);
        }

        // Adiciona o evento de clique ao botão
        if (btnCopiarCodigo) {
            btnCopiarCodigo.addEventListener('click', copiarCodigo);
        }
    });
</script>
@endpush
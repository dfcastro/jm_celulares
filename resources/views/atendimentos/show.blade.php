{{-- resources/views/atendimentos/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detalhes do Atendimento #' . $atendimento->id)

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .status-badge-lg { /* Badge maior para a página de detalhes */
            font-size: 1em;
            padding: 0.5em 0.8em;
        }
        .bg-status-diagnostico { background-color: #17a2b8 !important; color: white; }
        .bg-status-aguardando { background-color: #ffc107 !important; color: #212529; }
        .bg-status-manutencao { background-color: #0d6efd !important; color: white; }
        .bg-status-pronto { background-color: #198754 !important; color: white; }
        .bg-status-entregue { background-color: #6c757d !important; color: white; }

        .problem-box, .laudo-box, .obs-box {
            white-space: pre-wrap; /* Mantém quebras de linha e espaços */
            background-color: #f8f9fa; /* Fundo levemente diferente */
            padding: 10px;
            border-radius: 0.25rem;
            border: 1px solid #dee2e6;
            min-height: 60px;
        }
        .dl-horizontal dt { font-weight: bold; }
        .dl-horizontal dd { margin-bottom: .5rem; }
    </style>
@endpush

@section('content')
<div class="container mt-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-file-earmark-text-fill"></i> Detalhes do Atendimento #{{ $atendimento->id }}</h1>
        <div>
            <a href="{{ route('atendimentos.pdf', $atendimento->id) }}" class="btn btn-outline-secondary me-2" target="_blank">
                <i class="bi bi-printer"></i> Gerar PDF/OS
            </a>
            @if(Gate::allows('is-admin-or-tecnico') || (Auth::user()->tipo_usuario == 'atendente' && !in_array($atendimento->status, ['Entregue'])))
            <a href="{{ route('atendimentos.edit', $atendimento->id) }}" class="btn btn-warning me-2"><i class="bi bi-pencil-fill"></i> Editar</a>
            @endif
            <a href="{{ route('atendimentos.index') }}" class="btn btn-primary"><i class="bi bi-list-ul"></i> Voltar para Lista</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        {{-- Coluna da Esquerda: Cliente e Atendimento --}}
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <i class="bi bi-person-badge-fill"></i> Informações do Cliente e Atendimento
                </div>
                <div class="card-body">
                    @if($atendimento->cliente)
                    <h5><a href="{{ route('clientes.show', $atendimento->cliente->id) }}">{{ $atendimento->cliente->nome_completo }}</a></h5>
                    <dl class="row dl-horizontal">
                        <dt class="col-sm-4">CPF/CNPJ:</dt><dd class="col-sm-8">{{ $atendimento->cliente->cpf_cnpj }}</dd>
                        <dt class="col-sm-4">Telefone Principal:</dt><dd class="col-sm-8">{{ $atendimento->cliente->telefone ?? 'N/A' }}</dd>
                        <dt class="col-sm-4">Email:</dt><dd class="col-sm-8">{{ $atendimento->cliente->email ?? 'N/A' }}</dd>
                    </dl>
                    <hr>
                    @else
                    <p class="text-muted">Cliente não vinculado ou não informado.</p>
                    @endif

                    <h5>Detalhes do Aparelho e Serviço</h5>
                    <dl class="row dl-horizontal">
                        <dt class="col-sm-4">Aparelho/Descrição:</dt><dd class="col-sm-8">{{ $atendimento->descricao_aparelho }}</dd>
                        <dt class="col-sm-4">Contato do Atendimento:</dt><dd class="col-sm-8">{{ $atendimento->celular }}</dd> {{-- Este era o campo original 'celular' que agora é 'descricao_aparelho', talvez remover ou usar para um contato específico do atendimento? --}}
                        <dt class="col-sm-4">Data de Entrada:</dt><dd class="col-sm-8">{{ $atendimento->data_entrada->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-4">Técnico Responsável:</dt><dd class="col-sm-8">{{ $atendimento->tecnico->name ?? 'Não atribuído' }}</dd>
                        <dt class="col-sm-4">Data de Conclusão:</dt><dd class="col-sm-8">{{ $atendimento->data_conclusao ? $atendimento->data_conclusao->format('d/m/Y') : 'Não concluído' }}</dd>
                        <dt class="col-sm-4">Cód. Consulta Cliente:</dt><dd class="col-sm-8"><span class="badge bg-dark">{{ $atendimento->codigo_consulta }}</span></dd>
                    </dl>
                    <hr>
                    <h6>Problema Relatado:</h6>
                    <div class="problem-box mb-3">{{ $atendimento->problema_relatado }}</div>

                    @if($atendimento->observacoes)
                    <h6>Observações Internas:</h6>
                    <div class="obs-box mb-3">{{ $atendimento->observacoes }}</div>
                    @endif
                </div>
            </div>

            @if($atendimento->laudo_tecnico)
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                   <i class="bi bi-clipboard-check-fill"></i> Laudo Técnico / Solução Aplicada
                </div>
                <div class="card-body">
                    <div class="laudo-box">{{ $atendimento->laudo_tecnico }}</div>
                </div>
            </div>
            @endif
        </div>

        {{-- Coluna da Direita: Status, Valores e Peças --}}
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header text-center">
                    <i class="bi bi-bar-chart-line-fill"></i> Status Atual
                </div>
                <div class="card-body text-center">
                    <span class="badge rounded-pill status-badge-lg
                        @if($atendimento->status == 'Em diagnóstico') bg-status-diagnostico
                        @elseif($atendimento->status == 'Aguardando peça') bg-status-aguardando
                        @elseif($atendimento->status == 'Em manutenção') bg-status-manutencao
                        @elseif($atendimento->status == 'Pronto para entrega') bg-status-pronto
                        @elseif($atendimento->status == 'Entregue') bg-status-entregue
                        @else bg-light text-dark @endif">
                        {{ $atendimento->status }}
                    </span>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header"><i class="bi bi-currency-dollar"></i> Valores do Atendimento</div>
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
                    <dl class="dl-horizontal">
                        <dt>Mão de Obra/Serviço:</dt><dd>R$ {{ number_format($atendimento->valor_servico ?? 0, 2, ',', '.') }}</dd>
                        @if(($atendimento->desconto_servico ?? 0) > 0)
                        <dt class="text-danger">Desconto Serviço:</dt><dd class="text-danger">- R$ {{ number_format($atendimento->desconto_servico ?? 0, 2, ',', '.') }}</dd>
                        @endif
                        <dt class="border-top pt-2">Subtotal Serviço:</dt><dd class="border-top pt-2 fw-bold">R$ {{ number_format($valorServicoLiquido, 2, ',', '.') }}</dd>
                        <dt class="mt-2">Total Peças:</dt><dd class="mt-2">R$ {{ number_format($valorTotalPecas, 2, ',', '.') }}</dd>
                        <dt class="border-top pt-2 fs-5 text-success">VALOR TOTAL:</dt><dd class="border-top pt-2 fs-5 fw-bold text-success">R$ {{ number_format($valorTotalAtendimento, 2, ',', '.') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-tools"></i> Peças Utilizadas</span>
                    @can('is-admin-or-tecnico') {{-- Quem pode adicionar saída diretamente daqui --}}
                    <a href="{{ route('saidas-estoque.create', ['atendimento_id' => $atendimento->id]) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-lg"></i> Adicionar Peça
                    </a>
                    @endcan
                </div>
                <div class="card-body p-0"> {{-- p-0 para a tabela ocupar todo o card-body --}}
                    @if ($atendimento->saidasEstoque && $atendimento->saidasEstoque->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0"> {{-- mb-0 para remover margem inferior --}}
                                <thead>
                                    <tr>
                                        <th>Peça</th>
                                        <th class="text-center">Qtd</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($atendimento->saidasEstoque as $saida)
                                        <tr>
                                            <td>
                                                @if($saida->estoque)
                                                    <a href="{{ route('estoque.show', $saida->estoque->id) }}">{{ Str::limit($saida->estoque->nome, 25) }}</a>
                                                    <small class="d-block text-muted">{{ $saida->estoque->marca ?? 'N/A' }}</small>
                                                @else
                                                    <span class="text-danger fst-italic">Peça Removida</span>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $saida->quantidade }}</td>
                                            <td class="text-end">R$ {{ number_format($saida->quantidade * ($saida->estoque->preco_venda ?? 0), 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted p-3">Nenhuma peça registrada para este atendimento.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-end">
         @can('is-admin-or-atendente')
        <form action="{{ route('atendimentos.destroy', $atendimento->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este atendimento? Esta ação não poderá ser desfeita.')">
                <i class="bi bi-trash-fill"></i> Excluir Atendimento
            </button>
        </form>
        @endcan
    </div>
</div>
@endsection
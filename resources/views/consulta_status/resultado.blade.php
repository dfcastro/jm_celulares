@extends('layouts.public') {{-- USA O NOVO LAYOUT PÚBLICO --}}

@section('title', 'Resultado da Consulta do Atendimento - JM Celulares')

@section('content-public') {{-- CONTEÚDO DA PÁGINA --}}
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        @if ($atendimento)
            <div class="card shadow-sm">
                <div class="card-header text-white" style="background-color: var(--jm-laranja);">
                    <h4 class="my-0 text-center"><i class="bi bi-file-earmark-text-fill me-2"></i>Detalhes do Atendimento #{{ $atendimento->id }}</h4>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Cliente:</strong></p>
                            <p class="text-muted">{{ $atendimento->cliente->nome_completo }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Código de Consulta:</strong></p>
                            <p class="text-muted fw-bold">{{ $atendimento->codigo_consulta }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Aparelho/Descrição:</strong></p>
                            <p class="text-muted">{{ $atendimento->descricao_aparelho }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Data de Entrada:</strong></p>
                            <p class="text-muted">{{ $atendimento->data_entrada->format('d/m/Y \à\s H:i') }}</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p class="mb-1"><strong>Problema Relatado:</strong></p>
                        <div class="p-3 border rounded bg-light" style="white-space: pre-wrap;">{{ $atendimento->problema_relatado }}</div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <p class="mb-1"><strong>Status Atual:</strong></p>
                        <h3>
                            <span class="badge
                                @if($atendimento->status == 'Em diagnóstico') bg-info text-dark
                                @elseif($atendimento->status == 'Aguardando peça') bg-warning text-dark
                                @elseif($atendimento->status == 'Em manutenção') bg-primary
                                @elseif($atendimento->status == 'Pronto para entrega') bg-success
                                @elseif($atendimento->status == 'Entregue') bg-secondary
                                @else bg-light text-dark
                                @endif">
                                <i class="bi bi-clipboard-data-fill me-1"></i> {{ $atendimento->status }}
                            </span>
                        </h3>
                    </div>

                    @if($atendimento->laudo_tecnico)
                        <div class="mb-3">
                            <p class="mb-1"><strong>Laudo Técnico / Solução:</strong></p>
                            <div class="p-3 border rounded bg-light" style="white-space: pre-wrap;">{{ $atendimento->laudo_tecnico }}</div>
                        </div>
                    @endif

                    @if($atendimento->observacoes)
                        <div class="mb-3">
                            <p class="mb-1"><strong>Observações Adicionais:</strong></p>
                            <div class="p-3 border rounded bg-light" style="white-space: pre-wrap;">{{ $atendimento->observacoes }}</div>
                        </div>
                    @endif

                    @if($atendimento->data_conclusao && ($atendimento->status == 'Pronto para entrega' || $atendimento->status == 'Entregue'))
                        <div class="alert alert-success text-center">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Seu aparelho está pronto desde: <strong>{{ $atendimento->data_conclusao->format('d/m/Y') }}</strong>.
                        </div>
                    @elseif($atendimento->status == 'Aguardando peça')
                        <div class="alert alert-warning text-center">
                            <i class="bi bi-hourglass-split me-2"></i>
                            Estamos aguardando a chegada de peças para prosseguir com o reparo.
                        </div>
                    @endif

                    {{-- Linha do Tempo do Status (Sugestão) --}}
                    {{-- Isso requer que você tenha um histórico de status ou uma lógica para definir a ordem --}}
                    <div class="mt-4">
                        <h5>Progresso do Atendimento:</h5>
                        <ul class="list-group list-group-flush">
                            {{-- Exemplo de como poderia ser (você precisaria adaptar essa lógica) --}}
                            @php
                                $todosStatusPossiveis = ['Em diagnóstico', 'Aguardando peça', 'Em manutenção', 'Pronto para entrega', 'Entregue'];
                                $statusAtualIndex = array_search($atendimento->status, $todosStatusPossiveis);
                            @endphp
                            @foreach($todosStatusPossiveis as $index => $statusItem)
                                <li class="list-group-item d-flex justify-content-between align-items-center ps-0 {{ $index <= $statusAtualIndex ? 'text-success fw-bold' : 'text-muted' }}">
                                    <span>
                                        @if($index <= $statusAtualIndex)
                                            <i class="bi bi-check-circle-fill me-2"></i>
                                        @else
                                            <i class="bi bi-circle me-2"></i>
                                        @endif
                                        {{ $statusItem }}
                                    </span>
                                    @if($statusItem == $atendimento->status)
                                        <span class="badge bg-primary rounded-pill">Atual</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>

                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('consulta.index') }}" class="btn btn-primary"><i class="bi bi-search me-2"></i>Nova Consulta</a>
                    <a href="{{ route('site.home') }}" class="btn btn-outline-secondary"><i class="bi bi-house-door-fill me-2"></i>Página Inicial</a>
                </div>
            </div>
        @else
            <div class="alert alert-warning text-center" role="alert">
                <h4 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Atendimento Não Encontrado!</h4>
                <p>Nenhum atendimento foi encontrado com o código informado. Por favor, verifique o código e tente novamente.</p>
                <hr>
                <a href="{{ route('consulta.index') }}" class="btn btn-primary"><i class="bi bi-search me-2"></i>Tentar Novamente</a>
                <a href="{{ route('site.home') }}" class="btn btn-outline-secondary ms-2"><i class="bi bi-house-door-fill me-2"></i>Voltar para a Página Inicial</a>
            </div>
        @endif
    </div>
</div>
@endsection
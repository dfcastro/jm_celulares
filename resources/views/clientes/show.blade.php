@extends('layouts.app')

@section('title', 'Detalhes de Cliente: ' . $cliente->nome_completo)

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .card-header h5 {
            font-weight: 500;
        }
        .dl-horizontal-show dt {
            float: left;
            width: 150px; /* Ajuste conforme necessário */
            font-weight: normal;
            color: #6c757d;
            clear: left;
            text-align: right;
            padding-right: 10px;
            margin-bottom: .5rem;
        }
        .dl-horizontal-show dd {
            margin-left: 165px; /* Ajuste conforme dt + padding */
            margin-bottom: .5rem;
            font-weight: 500; /* Destaca o valor */
        }
        .actions-group .btn, .actions-group .d-inline {
            margin-bottom: 0.5rem; /* Espaçamento para botões em telas menores */
        }

        @media (max-width: 767.98px) { /* sm */
            .dl-horizontal-show dt,
            .dl-horizontal-show dd {
                width: 100%;
                float: none;
                margin-left: 0;
                text-align: left;
            }
            .dl-horizontal-show dt {
                margin-bottom: 0.1rem;
                font-weight: bold;
            }
            .actions-group {
                width: 100%;
            }
             .actions-group .btn, .actions-group .d-inline {
                width: 100%;
                margin-right: 0 !important;
            }
            .actions-group .d-inline form .btn {
                 width: 100%;
            }
        }
    </style>
@endpush

@section('content')
<div class="container mt-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <h1><i class="bi bi-person-lines-fill"></i> Detalhes do Cliente</h1>
        <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Voltar para Lista
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    {{-- Adicione outros feedbacks de sessão se necessário --}}

    <div class="row">
        <div class="col-lg-7">
            {{-- Card de Dados Pessoais e Contato --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="my-1"><i class="bi bi-person-vcard"></i> {{ $cliente->nome_completo }}</h5>
                </div>
                <div class="card-body">
                    <dl class="dl-horizontal-show">
                        <dt>ID:</dt>
                        <dd>{{ $cliente->id }}</dd>

                        <dt>CPF/CNPJ:</dt>
                        <dd>{{ $cliente->cpf_cnpj }}</dd>

                        <dt>Telefone:</dt>
                        <dd>{{ $cliente->telefone ?? 'Não informado' }}</dd>

                        <dt>Email:</dt>
                        <dd>{{ $cliente->email ?? 'Não informado' }}</dd>

                        <dt>Cadastrado em:</dt>
                        <dd>{{ $cliente->created_at->format('d/m/Y H:i') }}</dd>

                        <dt>Última atualização:</dt>
                        <dd>{{ $cliente->updated_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Card de Endereço --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="my-1"><i class="bi bi-geo-alt-fill"></i> Endereço</h5>
                </div>
                <div class="card-body">
                    @if($cliente->cep || $cliente->logradouro)
                        <dl class="dl-horizontal-show">
                            <dt>CEP:</dt>
                            <dd>{{ $cliente->cep ?? 'Não informado' }}</dd>

                            <dt>Logradouro:</dt>
                            <dd>{{ $cliente->logradouro ?? 'Não informado' }}</dd>

                            <dt>Número:</dt>
                            <dd>{{ $cliente->numero ?? 'Não informado' }}</dd>

                            <dt>Complemento:</dt>
                            <dd>{{ $cliente->complemento ?? 'Não informado' }}</dd>

                            <dt>Bairro:</dt>
                            <dd>{{ $cliente->bairro ?? 'Não informado' }}</dd>

                            <dt>Cidade:</dt>
                            <dd>{{ $cliente->cidade ?? 'Não informado' }}</dd>

                            <dt>Estado:</dt>
                            <dd>{{ $cliente->estado ?? 'Não informado' }}</dd>
                        </dl>
                    @else
                        <p class="text-muted">Nenhum endereço cadastrado para este cliente.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            {{-- Card de Ações Rápidas --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="my-1"><i class="bi bi-lightning-charge-fill"></i> Ações Rápidas</h5>
                </div>
                <div class="card-body actions-group">
                    <a href="{{ route('atendimentos.create', ['cliente_id' => $cliente->id]) }}" class="btn btn-primary mb-2 d-block">
                        <i class="bi bi-headset"></i> Novo Atendimento
                    </a>
                    <a href="{{ route('orcamentos.create', ['cliente_id' => $cliente->id]) }}" class="btn btn-success mb-2 d-block">
                        <i class="bi bi-file-earmark-medical-fill"></i> Novo Orçamento
                    </a>
                    <hr class="my-3">
                    <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-warning mb-2 d-block">
                        <i class="bi bi-pencil-fill"></i> Editar Cliente
                    </a>
                    @can('is-admin')
                        <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" class="d-block" onsubmit="return confirm('Tem certeza que deseja excluir este cliente? Esta ação não poderá ser desfeita.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash-fill"></i> Excluir Cliente
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

            {{-- Futuramente: Card de Histórico Resumido --}}
            {{--
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="my-1"><i class="bi bi-clock-history"></i> Histórico Recente</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><i>Em breve: Lista de últimos atendimentos e orçamentos.</i></p>
                    {{-- @if($cliente->atendimentos->isNotEmpty())
                        <h6>Últimos Atendimentos:</h6>
                        <ul class="list-group list-group-flush">
                            @foreach($cliente->atendimentos->take(3)->sortByDesc('data_entrada') as $atendimento)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ route('atendimentos.show', $atendimento->id) }}">#{{ $atendimento->id }} - {{ Str::limit($atendimento->descricao_aparelho, 20) }}</a>
                                    <small>{{ $atendimento->data_entrada->format('d/m/y') }}</small>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted small">Nenhum atendimento registrado.</p>
                    @endif --}}
                </div>
            </div>
    
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Se houver scripts específicos para esta página, adicione aqui --}}
@endpush
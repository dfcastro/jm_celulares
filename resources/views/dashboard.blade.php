{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Painel Principal - JM Celulares')

@section('content')
    <header class="mb-4 text-center">
        <h1>Painel Principal</h1>
        <p class="lead">Bem-vindo(a), {{ Auth::user()->name }}! <span class="badge bg-secondary">{{ ucfirst(Auth::user()->tipo_usuario) }}</span></p>
    </header>

    {{-- Linha para Avisos Rápidos / Widgets --}}
    <div class="row mb-4">
        {{-- Widget de Estoque Baixo --}}
        {{-- Exibe apenas para admin e tecnico, e se houver itens --}}
        @if(isset($itensEstoqueBaixo) && $itensEstoqueBaixo->isNotEmpty() && in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico','atendente']))
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card border-warning shadow-sm h-100">
                    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-exclamation-triangle-fill"></i> Atenção: Estoque Baixo!</span>
                        <span class="badge bg-danger rounded-pill">{{ $contagemItensEstoqueBaixo }}</span>
                    </div>
                    <div class="card-body pt-2" style="max-height: 200px; overflow-y: auto;">
                        @if($contagemItensEstoqueBaixo > 0)
                            <ul class="list-group list-group-flush small">
                                @foreach ($itensEstoqueBaixo->take(5) as $item) {{-- Mostra os primeiros 5 --}}
                                    <li class="list-group-item px-0 py-1 d-flex justify-content-between align-items-center">
                                        <a href="{{ route('estoque.show', $item->id) }}" class="text-decoration-none text-dark">{{ Str::limit($item->nome, 30) }}</a>
                                        <span class="badge {{ $item->quantidade <= 0 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                            Atual: {{ $item->quantidade }}/{{ $item->estoque_minimo }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                             <p class="card-text small text-muted">Nenhum item abaixo do estoque mínimo no momento.</p>
                        @endif
                    </div>
                    @if($contagemItensEstoqueBaixo > 0)
                    <div class="card-footer text-center py-2">
                        <a href="{{ route('relatorios.estoque_baixo') }}" class="btn btn-sm btn-outline-dark w-100">Ver Relatório Completo de Estoque Baixo</a>
                    </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Outros Widgets (Exemplos) --}}
        @if(Auth::user()->tipo_usuario == 'admin' || Auth::user()->tipo_usuario == 'tecnico' || Auth::user()->tipo_usuario == 'atendente')
            {{-- Card de Atendimentos em Aberto (Exemplo) --}}
            @php
                // Esta lógica deveria estar no DashboardController
                $atendimentosAbertosCount = \App\Models\Atendimento::whereNotIn('status', ['Entregue', 'Cancelado'])->count(); // Exemplo
            @endphp
            @if(isset($atendimentosAbertosCount) && $atendimentosAbertosCount > 0)
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card border-info shadow-sm h-100">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-tools"></i> Atendimentos em Aberto</span>
                        <span class="badge bg-light text-info rounded-pill">{{ $atendimentosAbertosCount }}</span>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="display-5">{{ $atendimentosAbertosCount }}</h3>
                        <p class="small text-muted">Aguardando ação</p>
                    </div>
                    <div class="card-footer text-center py-2">
                        <a href="{{ route('atendimentos.index', ['filtro_status_aberto' => 'sim']) }}" class="btn btn-sm btn-outline-dark w-100">Ver Atendimentos em Aberto</a>
                    </div>
                </div>
            </div>
            @endif
        @endif
        {{-- Adicionar mais widgets conforme necessário --}}

    </div>


    {{-- Seus cards de navegação principais --}}
    <h4 class="mb-3 mt-4 pt-2 border-top">Ações Rápidas e Módulos:</h4>
    <div class="row">
        @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card text-center h-100 shadow-hover">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <i class="bi bi-telephone-inbound-fill fs-1 text-primary mb-2"></i>
                    <h5 class="card-title">Serviços e Reparos</h5>
                    <p class="card-text small">Gerenciar ordens de serviço, status e peças.</p>
                    <a href="{{ route('atendimentos.index') }}" class="btn btn-primary mt-auto">Atendimentos</a>
                </div>
            </div>
        </div>
        @endif

        @if(in_array(Auth::user()->tipo_usuario, ['admin', 'atendente']))
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card text-center h-100 shadow-hover">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <i class="bi bi-cart-check-fill fs-1 text-success mb-2"></i>
                    <h5 class="card-title">Venda de Acessórios</h5>
                    <p class="card-text small">Registrar vendas de acessórios e gerenciar histórico.</p>
                    <a href="{{ route('vendas-acessorios.index') }}" class="btn btn-success mt-auto">Vendas de Acessórios</a>
                </div>
            </div>
        </div>
        @endif

        @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card text-center h-100 shadow-hover">
                 <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <i class="bi bi-box-seam-fill fs-1 text-info mb-2"></i>
                    <h5 class="card-title">Estoque</h5>
                    <p class="card-text small">Controlar peças, acessórios, entradas e saídas.</p>
                    <a href="{{ route('estoque.index') }}" class="btn btn-info text-white mt-auto">Consultar Estoque</a>
                </div>
            </div>
        </div>
        @endif

        @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card text-center h-100 shadow-hover">
                 <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <i class="bi bi-person-lines-fill fs-1 text-warning mb-2"></i>
                    <h5 class="card-title">Clientes</h5>
                    <p class="card-text small">Gerenciar cadastro de clientes.</p>
                    <a href="{{ route('clientes.index') }}" class="btn btn-warning mt-auto">Clientes</a>
                </div>
            </div>
        </div>
        @endif

        {{-- Consulta Pública pode ser um link direto, não necessariamente um card aqui se já estiver no menu --}}
        {{-- <div class="col-md-6 col-lg-4 mb-4"> ... </div> --}}

        @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
         <div class="col-md-6 col-lg-4 mb-4">
            <div class="card text-center h-100 shadow-hover">
                 <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <i class="bi bi-graph-up fs-1 text-dark mb-2"></i>
                    <h5 class="card-title">Relatórios</h5>
                    <p class="card-text small">Visualizar dados e métricas do sistema.</p>
                    <a href="{{ route('relatorios.estoque_baixo') }}" class="btn btn-dark mt-auto">Ver Relatórios</a> {{-- Link para o primeiro relatório ou um índice --}}
                </div>
            </div>
        </div>
        @endif

        @if(Auth::user()->tipo_usuario == 'admin')
         <div class="col-md-6 col-lg-4 mb-4">
            <div class="card text-center h-100 shadow-hover">
                 <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <i class="bi bi-people-fill fs-1 text-danger mb-2"></i>
                    <h5 class="card-title">Gerenciar Usuários</h5>
                    <p class="card-text small">Adicionar, editar e remover usuários do sistema.</p>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-danger mt-auto">Usuários</a>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        .card.shadow-hover { /* Para os cards de navegação rápida */
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .card.shadow-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important; /* Sobrescreve o shadow-sm se houver */
        }
        .widget-card .card-body {
            font-size: 0.9rem;
        }
        .widget-card .list-group-item {
            padding: .5rem .75rem;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush
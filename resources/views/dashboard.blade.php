{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Painel Principal - JM Celulares')

@section('content')
<header class="mb-4">
    <h1 class="display-5">Painel Principal</h1>
    <p class="lead">Bem-vindo(a), {{ Auth::user()->name }}!
        <span class="badge bg-primary rounded-pill align-middle">{{ ucfirst(Auth::user()->tipo_usuario) }}</span>
    </p>
</header>

{{-- Linha para Widgets de Contadores Rápidos --}}
<div class="row mb-4 g-3">
    <div class="col-md-6 col-lg-3">
        <div class="card text-white bg-info shadow-sm h-100">
            <div class="card-body text-center">
                <div class="d-flex justify-content-between align-items-center">
                    <i class="bi bi-tools fs-1 opacity-75"></i>
                    <div class="text-end">
                        <h3 class="card-title fs-2 mb-0">{{ $atendimentosHoje ?? 0 }}</h3>
                        <p class="card-text mb-0 small">Atendimentos Hoje</p>
                    </div>
                </div>
            </div>
            <a href="{{ route('atendimentos.index', ['data_inicial_filtro' => today()->toDateString(), 'data_final_filtro' => today()->toDateString()]) }}" class="card-footer text-white text-decoration-none small">
                Ver detalhes <i class="bi bi-arrow-right-circle"></i>
            </a>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card text-white bg-warning shadow-sm h-100">
            <div class="card-body text-center">
                <div class="d-flex justify-content-between align-items-center">
                    <i class="bi bi-hourglass-split fs-1 opacity-75"></i>
                    <div class="text-end">
                        <h3 class="card-title fs-2 mb-0">{{ $atendimentosPendentes ?? 0 }}</h3>
                        <p class="card-text mb-0 small">Atendimentos Pendentes</p>
                    </div>
                </div>
            </div>
            <a href="{{ route('atendimentos.index', ['filtro_status_aberto' => 'sim']) }}" class="card-footer text-white text-decoration-none small">
                Ver pendentes <i class="bi bi-arrow-right-circle"></i>
            </a>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card text-white bg-success shadow-sm h-100">
            <div class="card-body text-center">
                <div class="d-flex justify-content-between align-items-center">
                    <i class="bi bi-cash-coin fs-1 opacity-75"></i>
                    <div class="text-end">
                        <h3 class="card-title fs-2 mb-0">R$ {{ number_format($vendasHojeValor ?? 0, 2, ',', '.') }}</h3>
                        <p class="card-text mb-0 small">Vendas Hoje</p>
                    </div>
                </div>
            </div>
            <a href="{{ route('relatorios.vendas_acessorios', ['data_inicial' => today()->toDateString(), 'data_final' => today()->toDateString()]) }}" class="card-footer text-white text-decoration-none small">
                Ver relatório de vendas <i class="bi bi-arrow-right-circle"></i>
            </a>
        </div>
    </div>

    @if(Auth::user()->tipo_usuario != 'visitante')
    <div class="col-md-6 col-lg-3">
        <div class="card {{ ($contagemItensEstoqueBaixo ?? 0) > 0 ? 'text-white bg-danger' : 'text-secondary bg-light' }} shadow-sm h-100">
            <div class="card-body text-center">
                <div class="d-flex justify-content-between align-items-center">
                    <i class="bi bi-archive-fill fs-1 {{ ($contagemItensEstoqueBaixo ?? 0) > 0 ? 'opacity-75' : 'opacity-50' }}"></i>
                    <div class="text-end">
                        <h3 class="card-title fs-2 mb-0">{{ $contagemItensEstoqueBaixo ?? 0 }}</h3>
                        <p class="card-text mb-0 small">Itens Estoque Baixo</p>
                    </div>
                </div>
            </div>
            <a href="{{ route('relatorios.estoque_baixo') }}" class="card-footer {{ ($contagemItensEstoqueBaixo ?? 0) > 0 ? 'text-white' : 'text-secondary' }} text-decoration-none small">
                Ver itens <i class="bi bi-arrow-right-circle"></i>
            </a>
        </div>
    </div>
    @endif
</div>

{{-- Linha para Gráficos --}}
<div class="row mb-4 g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <i class="bi bi-pie-chart-fill me-1"></i> Atendimentos por Status (Últimos 30 dias)
            </div>
            <div class="card-body" style="min-height: 300px;">
                <canvas id="graficoAtendimentosStatus"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <i class="bi bi-bar-chart-line-fill me-1"></i> Em Breve: Atendimentos na Semana
            </div>
            <div class="card-body text-center text-muted" style="min-height: 300px; display:flex; align-items:center; justify-content:center;">
                <p>Outro gráfico virá aqui!</p>
            </div>
        </div>
    </div>
</div>


{{-- Cards de Navegação Rápida COM ÍCONES RESTAURADOS --}}
<h4 class="mb-3 mt-5 pt-3 border-top">Ações Rápidas e Módulos:</h4>
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4"> {{-- Usando row-cols para responsividade --}}
    @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
    <div class="col">
        <div class="card text-center h-100 shadow-hover">
            <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                {{-- ÍCONE RESTAURADO --}}
                <i class="bi bi-telephone-inbound-fill fs-1 text-primary mb-3"></i>
                <h5 class="card-title mb-2">Serviços e Reparos</h5>
                <p class="card-text small text-muted mb-3">Gerenciar ordens de serviço, status e peças.</p>
                <a href="{{ route('atendimentos.index') }}" class="btn btn-primary mt-auto stretched-link">Atendimentos</a>
            </div>
        </div>
    </div>
    @endif

    @if(in_array(Auth::user()->tipo_usuario, ['admin', 'atendente']))
    <div class="col">
        <div class="card text-center h-100 shadow-hover">
            <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                {{-- ÍCONE RESTAURADO --}}
                <i class="bi bi-cart-check-fill fs-1 text-success mb-3"></i>
                <h5 class="card-title mb-2">Venda de Acessórios</h5>
                <p class="card-text small text-muted mb-3">Registrar vendas de acessórios e gerenciar histórico.</p>
                <a href="{{ route('vendas-acessorios.index') }}" class="btn btn-success mt-auto stretched-link">Vendas</a>
            </div>
        </div>
    </div>
    @endif

    @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
    <div class="col">
        <div class="card text-center h-100 shadow-hover">
            <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                {{-- ÍCONE RESTAURADO --}}
                <i class="bi bi-box-seam-fill fs-1 text-info mb-3"></i>
                <h5 class="card-title mb-2">Estoque</h5>
                <p class="card-text small text-muted mb-3">Controlar peças, acessórios, entradas e saídas.</p>
                <a href="{{ route('estoque.index') }}" class="btn btn-info text-white mt-auto stretched-link">Consultar Estoque</a>
            </div>
        </div>
    </div>
    @endif

    @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
    <div class="col">
        <div class="card text-center h-100 shadow-hover">
            <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                {{-- ÍCONE RESTAURADO --}}
                <i class="bi bi-person-lines-fill fs-1 text-warning mb-3"></i>
                <h5 class="card-title mb-2">Clientes</h5>
                <p class="card-text small text-muted mb-3">Gerenciar cadastro de clientes.</p>
                <a href="{{ route('clientes.index') }}" class="btn btn-warning mt-auto stretched-link">Clientes</a>
            </div>
        </div>
    </div>
    @endif

    @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
    <div class="col">
        <div class="card text-center h-100 shadow-hover">
            <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                {{-- ÍCONE RESTAURADO --}}
                <i class="bi bi-graph-up-arrow fs-1 text-dark mb-3"></i>
                <h5 class="card-title mb-2">Relatórios</h5>
                <p class="card-text small text-muted mb-3">Visualizar dados e métricas do sistema.</p>
                <a href="{{ route('relatorios.estoque_baixo') }}" class="btn btn-dark mt-auto stretched-link">Ver Relatórios</a>
            </div>
        </div>
    </div>
    @endif

    @if(Auth::user()->tipo_usuario == 'admin')
    <div class="col">
        <div class="card text-center h-100 shadow-hover">
            <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                {{-- ÍCONE RESTAURADO --}}
                <i class="bi bi-people-fill fs-1 text-danger mb-3"></i>
                <h5 class="card-title mb-2">Gerenciar Usuários</h5>
                <p class="card-text small text-muted mb-3">Adicionar, editar e remover usuários do sistema.</p>
                <a href="{{ route('usuarios.index') }}" class="btn btn-danger mt-auto stretched-link">Usuários</a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .card.shadow-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .card .card-footer:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .card-body .fs-1 {
        /* font-size: 3rem !important; */
    }

    .card-title.fs-2 {
        font-weight: 600;
    }

    .card-text.small {
        font-size: 0.85em;
    }

    .card-body .fs-1.mb-3 {
        /* Ícones dos cards de navegação */
        font-size: 2.8rem !important;
    }

    .card-body .card-title.mb-2 {
        font-weight: 600;
    }

    .stretched-link::after {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 1;
        content: "";
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script type="text/javascript">
    var chartData = {
        labels: @json($labelsGraficoStatus ?? []),
        data: @json($dadosGraficoStatus ?? [])
    };
    // console.log('Dados brutos para o gráfico:', chartData);
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctxStatusCanvas = document.getElementById('graficoAtendimentosStatus');

        if (ctxStatusCanvas) {
            // console.log('Canvas encontrado:', ctxStatusCanvas);
            // console.log('Dados recebidos do Blade para o gráfico:');
            // console.log('Labels:', chartData.labels);
            // console.log('Data:', chartData.data);

            // Validação dos dados antes de tentar criar o gráfico
            if (Array.isArray(chartData.labels) &&
                Array.isArray(chartData.data) &&
                chartData.labels.length > 0 &&
                chartData.data.length === chartData.labels.length) {

                try {
                    new Chart(ctxStatusCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: chartData.labels,
                            datasets: [{
                                label: 'Nº de Atendimentos', // Esta é uma propriedade do dataset
                                data: chartData.data,
                                backgroundColor: [
                                    'rgba(54, 162, 235, 0.8)', // Azul
                                    'rgba(255, 206, 86, 0.8)', // Amarelo
                                    'rgba(75, 192, 192, 0.8)', // Verde Água
                                    'rgba(153, 102, 255, 0.8)', // Roxo
                                    'rgba(201, 203, 207, 0.8)', // Cinza
                                    'rgba(255, 99, 132, 0.8)', // Vermelho
                                    'rgba(255, 159, 64, 0.8)' // Laranja
                                ],
                                borderColor: '#fff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let labelText = context.label || ''; // Nome do status (fatia)
                                            let value = context.parsed || 0;
                                            // Monta o texto do tooltip
                                            return labelText + ': ' + value + ' atendimento(s)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                    console.log("Gráfico de status renderizado com sucesso.");
                } catch (e) {
                    console.error("Erro ao inicializar o Chart.js:", e);
                    if (ctxStatusCanvas.parentElement) {
                        ctxStatusCanvas.parentElement.innerHTML = '<p class="text-center text-danger small p-3">Ocorreu um erro ao tentar exibir o gráfico de status.</p>';
                    }
                }
            } else {
                console.warn("Dados para o gráfico de status estão incompletos, vazios ou não são arrays válidos.");
                // console.log("Labels recebidos:", chartData.labels);
                // console.log("Data recebida:", chartData.data);
                if (ctxStatusCanvas.parentElement) {
                    ctxStatusCanvas.parentElement.innerHTML = '<p class="text-center text-muted small p-3">Sem dados suficientes para exibir o gráfico de status no momento.</p>';
                }
            }
        } else {
            console.error("Elemento canvas 'graficoAtendimentosStatus' não encontrado no DOM.");
        }
    });
</script>
@endpush
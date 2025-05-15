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
    <h4 class="mb-3 mt-5 pt-3 border-top"><i class="bi bi-speedometer2 me-2"></i>Resumo Rápido</h4> 
    <div class="row mb-4 g-3">
        {{-- Atendimentos Hoje --}}
        <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card text-white bg-info shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <i class="bi bi-tools fs-1 opacity-75"></i>
                        <div class="text-end">
                            <h1 class="card-title fs-2 mb-0">{{ $atendimentosHoje ?? 0 }}</h1>
                            <p class="card-text mb-0 small">Atend. Hoje</p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('atendimentos.index', ['data_inicial_filtro' => today()->toDateString(), 'data_final_filtro' => today()->toDateString()]) }}"
                    class="card-footer text-white text-decoration-none small">
                    Ver detalhes <i class="bi bi-arrow-right-circle"></i>
                </a>
            </div>
        </div>

        {{-- Atendimentos Pendentes --}}
        <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card text-white bg-warning shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <i class="bi bi-hourglass-split fs-1 opacity-75"></i>
                        <div class="text-end">
                            <h3 class="card-title fs-2 mb-0">{{ $atendimentosPendentes ?? 0 }}</h3>
                            <p class="card-text mb-0 small">Atend. Pendentes</p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('atendimentos.index', ['filtro_status_aberto' => 'sim']) }}"
                    class="card-footer text-white text-decoration-none small">
                    Ver pendentes <i class="bi bi-arrow-right-circle"></i>
                </a>
            </div>
        </div>

        {{-- Vendas Hoje --}}
        <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card text-white bg-success shadow-sm h-100">
                <div class="card-body text-center"> {{-- Mantido text-center para o card como um todo --}}
                    <div class="d-flex justify-content-between align-items-center">
                        <i class="bi bi-cash-coin fs-1 opacity-75"></i>
                        <div class="text-end"> {{-- Div para alinhar o texto à direita --}}
                            {{-- Aplicando a nova classe para o valor monetário --}}
                            <h3 class="card-title widget-valor-monetario mb-0">
                                R${{ number_format($vendasHojeValor ?? 0, 2, ',', '.') }}</h3>
                            <p class="card-text mb-0 small">Vendas Hoje</p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('relatorios.vendas_acessorios', ['data_inicial' => today()->toDateString(), 'data_final' => today()->toDateString()]) }}"
                    class="card-footer text-white text-decoration-none small">
                    Ver relatório <i class="bi bi-arrow-right-circle"></i>
                </a>
            </div>
        </div>

        {{-- Vendas Esta Semana --}}
        <<div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card text-white bg-primary shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <i class="bi bi-calendar-week fs-1 opacity-75"></i>
                        <div class="text-end">
                            <h3 class="card-title widget-valor-monetario mb-0">
                                R${{ number_format($vendasEstaSemanaValor ?? 0, 2, ',', '.') }}</h3>
                            <p class="card-text mb-0 small">Vendas Semana</p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('relatorios.vendas_acessorios', ['data_inicial' => now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString(), 'data_final' => now()->endOfWeek(\Carbon\Carbon::SUNDAY)->toDateString()]) }}"
                    class="card-footer text-white text-decoration-none small">
                    Ver relatório <i class="bi bi-arrow-right-circle"></i>
                </a>
            </div>
    </div>

    {{-- Vendas Este Mês --}}
    <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">
        <div class="card text-white bg-secondary shadow-sm h-100">
            <div class="card-body text-center">
                <div class="d-flex justify-content-between align-items-center">
                    <i class="bi bi-calendar-month fs-1 opacity-75"></i>
                    <div class="text-end">
                        <h3 class="card-title widget-valor-monetario mb-0">
                            R${{ number_format($vendasEsteMesValor ?? 0, 2, ',', '.') }}</h3>
                        <p class="card-text mb-0 small">Vendas Mês</p>
                    </div>
                </div>
            </div>
            <a href="{{ route('relatorios.vendas_acessorios', ['data_inicial' => now()->startOfMonth()->toDateString(), 'data_final' => now()->endOfDay()->toDateString()]) }}"
                class="card-footer text-white text-decoration-none small">
                Ver relatório <i class="bi bi-arrow-right-circle"></i>
            </a>
        </div>
    </div>

    {{-- Estoque Baixo --}}
    @if(Auth::user()->tipo_usuario != 'visitante') {{-- Ou sua gate específica --}}
        <div class="col-sm-6 col-md-4 col-lg-3 col-xl-2">
            <div
                class="card {{ ($contagemItensEstoqueBaixo ?? 0) > 0 ? 'text-white bg-danger' : 'text-secondary bg-light' }} shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-between align-items-center">
                        <i
                            class="bi bi-archive-fill fs-1 {{ ($contagemItensEstoqueBaixo ?? 0) > 0 ? 'opacity-75' : 'opacity-50' }}"></i>
                        <div class="text-end">
                            <h3 class="card-title fs-2 mb-0">{{ $contagemItensEstoqueBaixo ?? 0 }}</h3>
                            <p class="card-text mb-0 small">Estoque Baixo</p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('relatorios.estoque_baixo') }}"
                    class="card-footer {{ ($contagemItensEstoqueBaixo ?? 0) > 0 ? 'text-white' : 'text-secondary' }} text-decoration-none small">
                    Ver itens <i class="bi bi-arrow-right-circle"></i>
                </a>
            </div>
        </div>
    @endif
    </div>
    {{-- Cards de Navegação Rápida --}}
    <h4 class="mb-3 mt-5 pt-3 border-top"><i class="bi bi-grid-1x2-fill me-2"></i>Ações Rápidas e Módulos:</h4>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4">
        @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
            <div class="col">
                <div class="card text-center h-100 shadow-hover">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                        <i class="bi bi-telephone-inbound-fill fs-1 text-primary mb-3"></i>
                        <h5 class="card-title mb-2">Serviços e Reparos</h5>
                        <p class="card-text small text-muted mb-3">Gerenciar ordens de serviço, status e peças.</p>
                        <a href="{{ route('atendimentos.index') }}"
                            class="btn btn-primary mt-auto stretched-link">Atendimentos</a>
                    </div>
                </div>
            </div>
        @endif

        @if(in_array(Auth::user()->tipo_usuario, ['admin', 'atendente']))
            <div class="col">
                <div class="card text-center h-100 shadow-hover">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                        <i class="bi bi-cart-check-fill fs-1 text-success mb-3"></i>
                        <h5 class="card-title mb-2">Venda de Acessórios</h5>
                        <p class="card-text small text-muted mb-3">Registrar vendas de acessórios e gerenciar histórico.</p>
                        <a href="{{ route('vendas-acessorios.index') }}"
                            class="btn btn-success mt-auto stretched-link">Vendas</a>
                    </div>
                </div>
            </div>
        @endif

        @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
            <div class="col">
                <div class="card text-center h-100 shadow-hover">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                        <i class="bi bi-box-seam-fill fs-1 text-info mb-3"></i>
                        <h5 class="card-title mb-2">Estoque</h5>
                        <p class="card-text small text-muted mb-3">Controlar peças, acessórios, entradas e saídas.</p>
                        <a href="{{ route('estoque.index') }}" class="btn btn-info text-white mt-auto stretched-link">Consultar
                            Estoque</a>
                    </div>
                </div>
            </div>
        @endif

        @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
            <div class="col">
                <div class="card text-center h-100 shadow-hover">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
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
                        <i class="bi bi-graph-up-arrow fs-1 text-dark mb-3"></i>
                        <h5 class="card-title mb-2">Relatórios</h5>
                        <p class="card-text small text-muted mb-3">Visualizar dados e métricas do sistema.</p>
                        <a href="{{ route('relatorios.estoque_baixo') }}" class="btn btn-dark mt-auto stretched-link">Ver
                            Relatórios</a>
                    </div>
                </div>
            </div>
        @endif

        @if(Auth::user()->tipo_usuario == 'admin')
            <div class="col">
                <div class="card text-center h-100 shadow-hover">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                        <i class="bi bi-people-fill fs-1 text-danger mb-3"></i>
                        <h5 class="card-title mb-2">Gerenciar Usuários</h5>
                        <p class="card-text small text-muted mb-3">Adicionar, editar e remover usuários do sistema.</p>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-danger mt-auto stretched-link">Usuários</a>
                    </div>
                </div>
            </div>
        @endif
    </div>
    {{-- Linha para Gráficos --}}
    <h4 class="mb-3 mt-4"><i class="bi bi-graph-up me-2"></i>Visão Geral Gráfica</h4>
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
                    <i class="bi bi-bar-chart-line-fill me-1"></i> Vendas nos Últimos 7 Dias
                </div>
                <div class="card-body" style="min-height: 300px;">
                    <canvas id="graficoVendasSemana"></canvas> {{-- NOVO CANVAS --}}
                </div>
            </div>
        </div>
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
            /* Ícones dos widgets de contador */
            font-size: 2.0rem !important;
            /* Ajuste se o ícone estiver muito grande */
            margin-right: 0.5rem;
            /* Espaço entre o ícone e o texto */
        }

        .card-title.fs-2 {
            /* Números grandes nos widgets */
            font-weight: 700;
            /* Vamos controlar o tamanho da fonte com mais precisão */
            font-size: 1.8 rem;
            /* Ponto de partida, ajuste conforme necessário */
            line-height: 1.1;
            /* Para evitar altura excessiva da linha */
            word-break: break-all;
            /* Tenta quebrar palavras/números longos se necessário, mas pode não ser ideal para números */
            white-space: normal;
            /* Permite quebra de linha se o número for MUITO grande */
        }

        .card-body .text-end {
            /* Div que contém o título e o subtítulo */
            flex-grow: 1;
            /* Para ocupar o espaço restante ao lado do ícone */
        }

        .card .card-body .d-flex {
            /* Container flex do ícone e texto */
            min-height: 60px;
            /* Altura mínima para alinhar visualmente mesmo com números de tamanhos diferentes */
        }

        .card-text.small {
            /* Texto abaixo dos números nos widgets */
            font-size: 1.0rem;
            /* Ainda menor para dar mais espaço ao número */
            line-height: 1;
        }

        .widget-valor-monetario {
            font-size: 1.5rem;
            /* Um pouco menor se for R$ X.XXX,XX */
            /* Adicione mais estilos se necessário */
        }

        .card-text.small {
            /* Texto abaixo dos números nos widgets */
            font-size: 1.0rem;
            /* Um pouco menor para caber melhor */
        }

        /* Ícones dos cards de navegação rápida */
        .card-body>.fs-1.text-primary,
        .card-body>.fs-1.text-success,
        .card-body>.fs-1.text-info,
        .card-body>.fs-1.text-warning,
        .card-body>.fs-1.text-dark,
        .card-body>.fs-1.text-danger {
            font-size: 2.8rem !important;
            /* Tamanho do ícone */
        }

        .card-body>.card-title.mb-2 {
            /* Título dos cards de navegação */
            font-weight: 600;
        }

        .stretched-link::after {
            /* Para os cards de navegação serem clicáveis */
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
        window.graficoStatusLabels = @json($labelsGraficoStatus ?? []);
        window.graficoStatusData = @json($dadosGraficoStatus ?? []);
        window.graficoVendasSemanaLabels = @json($labelsGraficoVendasSemana ?? []);
        window.graficoVendasSemanaData = @json($dadosGraficoVendasSemana ?? []);
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // GRÁFICO DE ATENDIMENTOS POR STATUS
            const ctxStatusCanvas = document.getElementById('graficoAtendimentosStatus');
            if (ctxStatusCanvas && typeof Chart !== 'undefined') {
                const labelsStatus = window.graficoStatusLabels;
                const dataStatus = window.graficoStatusData;

                if (Array.isArray(labelsStatus) && Array.isArray(dataStatus) && labelsStatus.length > 0 && dataStatus.length === labelsStatus.length) {
                    new Chart(ctxStatusCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: labelsStatus,
                            datasets: [{
                                label: 'Nº de Atendimentos',
                                data: dataStatus,
                                backgroundColor: [
                                    'rgba(54, 162, 235, 0.8)',  // Azul
                                    'rgba(255, 206, 86, 0.8)', // Amarelo
                                    'rgba(75, 192, 192, 0.8)', // Verde Água
                                    'rgba(153, 102, 255, 0.8)',// Roxo
                                    'rgba(201, 203, 207, 0.8)', // Cinza
                                    'rgba(255, 99, 132, 0.8)',  // Vermelho
                                    'rgba(255, 159, 64, 0.8)'  // Laranja
                                ],
                                borderColor: '#fff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: function (context) { return (context.label || '') + ': ' + (context.parsed || 0); } } } }
                        }
                    });
                } else {
                    if (ctxStatusCanvas.parentElement) ctxStatusCanvas.parentElement.innerHTML = '<p class="text-center text-muted small p-3">Sem dados para o gráfico de status.</p>';
                }
            }

            // GRÁFICO DE VENDAS DA SEMANA
            const ctxVendasSemanaCanvas = document.getElementById('graficoVendasSemana');
            if (ctxVendasSemanaCanvas && typeof Chart !== 'undefined') {
                const labelsVendas = window.graficoVendasSemanaLabels;
                const dataVendas = window.graficoVendasSemanaData;

                if (Array.isArray(labelsVendas) && Array.isArray(dataVendas) && labelsVendas.length > 0 && dataVendas.length === labelsVendas.length) {
                    new Chart(ctxVendasSemanaCanvas, {
                        type: 'bar',
                        data: {
                            labels: labelsVendas,
                            datasets: [{
                                label: 'Valor Vendido (R$)',
                                data: dataVendas,
                                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1,
                                borderRadius: 4,
                                barPercentage: 0.7,
                                categoryPercentage: 0.8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { callback: function (value) { return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }); } }
                                },
                                x: { grid: { display: false } }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            let label = context.dataset.label || '';
                                            if (label) { label += ': '; }
                                            if (context.parsed.y !== null) {
                                                label += context.parsed.y.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                                            }
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                } else {
                    if (ctxVendasSemanaCanvas.parentElement) ctxVendasSemanaCanvas.parentElement.innerHTML = '<p class="text-center text-muted small p-3">Sem dados de vendas para o gráfico da semana.</p>';
                }
            }
        });
    </script>
@endpush
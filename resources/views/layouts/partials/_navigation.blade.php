{{-- resources/views/layouts/partials/_navigation.blade.php --}}
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <i class="bi bi-phone-vibrate-fill me-1" style="color: var(--jm-laranja, #FFA500);"></i>
            <span style="color: var(--jm-laranja, #FFA500); font-weight: bold;">JM</span>
            <span class="text-white">CELULARES</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
            aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @auth {{-- Todos os menus principais abaixo são para usuários logados --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" aria-current="page"
                            href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i> Painel</a>
                    </li>

                    @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('orcamentos.*') ? 'active' : '' }}"
                                href="#" id="navbarDropdownOrcamentos" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-file-earmark-medical-fill me-1"></i> Orçamentos
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownOrcamentos">
                                <li><a class="dropdown-item {{ request()->routeIs('orcamentos.index') ? 'active' : '' }}"
                                        href="{{ route('orcamentos.index') }}"><i class="bi bi-list-ul me-2"></i>Listar Orçamentos</a></li>
                                <li><a class="dropdown-item {{ request()->routeIs('orcamentos.create') ? 'active' : '' }}"
                                        href="{{ route('orcamentos.create') }}"><i class="bi bi-plus-circle-fill me-2"></i>Novo Orçamento</a></li>
                            </ul>
                        </li>
                    @endif

                    @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('atendimentos.*') ? 'active' : '' }}"
                                href="#" id="navbarDropdownAtendimentos" role="button" {{-- Removido href para rota index --}}
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-headset me-1"></i> Atendimentos
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownAtendimentos">
                                <li><a class="dropdown-item {{ request()->routeIs('atendimentos.index') ? 'active' : '' }}" href="{{ route('atendimentos.index') }}"><i class="bi bi-list-task me-2"></i>Listar Atendimentos</a></li>
                                <li><a class="dropdown-item {{ request()->routeIs('atendimentos.create') ? 'active' : '' }}" href="{{ route('atendimentos.create') }}"><i class="bi bi-plus-square-dotted me-2"></i>Novo Atendimento</a></li>
                            </ul>
                        </li>
                    @endif

                    @if(in_array(Auth::user()->tipo_usuario, ['admin', 'atendente']))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('vendas-acessorios.*') ? 'active' : '' }}"
                                href="#" id="navbarDropdownVendas" role="button" {{-- Removido href para rota index --}}
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-cart3 me-1"></i> Vendas
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownVendas">
                                <li><a class="dropdown-item {{ request()->routeIs('vendas-acessorios.index') ? 'active' : '' }}" href="{{ route('vendas-acessorios.index') }}"><i class="bi bi-receipt-cutoff me-2"></i>Listar Vendas</a></li>
                                <li><a class="dropdown-item {{ request()->routeIs('vendas-acessorios.create') ? 'active' : '' }}" href="{{ route('vendas-acessorios.create') }}"><i class="bi bi-cart-plus-fill me-2"></i>Nova Venda</a></li>
                            </ul>
                        </li>
                    @endif

                    @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('estoque.index') || request()->routeIs('estoque.show') || request()->routeIs('estoque.create') || request()->routeIs('estoque.edit') || request()->routeIs('entradas-estoque.*') || request()->routeIs('saidas-estoque.*') || request()->routeIs('relatorios.estoque_baixo') || request()->routeIs('estoque.historico_unificado') ? 'active' : '' }}"
                                href="#" id="navbarDropdownEstoque" role="button" {{-- Removido href para rota index --}}
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-boxes me-1"></i> Estoque
                                @if(isset($contagemItensEstoqueBaixoGlobal) && $contagemItensEstoqueBaixoGlobal > 0 && in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico']))
                                    <span class="badge bg-danger rounded-pill ms-1 animate-pulse"
                                        title="{{ $contagemItensEstoqueBaixoGlobal }} itens com estoque baixo!">
                                        {{ $contagemItensEstoqueBaixoGlobal }}
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownEstoque">
                                <li><a class="dropdown-item {{ request()->routeIs('estoque.index') ? 'active' : '' }}" href="{{ route('estoque.index') }}"><i class="bi bi-box-seam me-2"></i>Consultar Estoque</a></li>
                                @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
                                    <li><a class="dropdown-item {{ request()->routeIs('estoque.create') ? 'active' : '' }}" href="{{ route('estoque.create') }}"><i class="bi bi-box-arrow-in-up me-2"></i>Novo Item de Estoque</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item {{ request()->routeIs('entradas-estoque.index') || request()->routeIs('entradas-estoque.create') ? 'active' : '' }}" href="{{ route('entradas-estoque.index') }}"><i class="bi bi-box-arrow-in-down me-2"></i>Entradas no Estoque</a></li>
                                @endif
                                @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico']))
                                    <li><a class="dropdown-item {{ request()->routeIs('saidas-estoque.index') || request()->routeIs('saidas-estoque.create') ? 'active' : '' }}" href="{{ route('saidas-estoque.index') }}"><i class="bi bi-box-arrow-up me-2"></i>Saídas (Avulsas)</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item {{ request()->routeIs('estoque.historico_unificado') ? 'active' : '' }}" href="{{ route('estoque.historico_unificado') }}"><i class="bi bi-hourglass-split me-2"></i>Histórico Unificado</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item d-flex justify-content-between align-items-center {{ request()->routeIs('relatorios.estoque_baixo') ? 'active' : '' }}"
                                            href="{{ route('relatorios.estoque_baixo') }}">
                                            <div><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Estoque Baixo</div>
                                            @if(isset($contagemItensEstoqueBaixoGlobal) && $contagemItensEstoqueBaixoGlobal > 0)
                                                <span class="badge bg-warning text-dark rounded-pill">{{ $contagemItensEstoqueBaixoGlobal }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('clientes.*') ? 'active' : '' }}"
                                href="#" id="navbarDropdownClientes" role="button" {{-- Removido href para rota index --}}
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-lines-fill me-1"></i> Clientes
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownClientes">
                                <li><a class="dropdown-item {{ request()->routeIs('clientes.index') ? 'active' : '' }}" href="{{ route('clientes.index') }}"><i class="bi bi-person-rolodex me-2"></i>Lista de Clientes</a></li>
                                <li><a class="dropdown-item {{ request()->routeIs('clientes.create') ? 'active' : '' }}" href="{{ route('clientes.create') }}"><i class="bi bi-person-plus-fill me-2"></i>Novo Cliente</a></li>
                            </ul>
                        </li>
                    @endif

                    @can('gerenciar-caixa')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('caixa.*') ? 'active' : '' }}" href="#"
                                id="navbarDropdownCaixa" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-safe2-fill me-1"></i> Caixa
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownCaixa">
                                <li><a class="dropdown-item {{ request()->routeIs('caixa.index') ? 'active' : '' }}" href="{{ route('caixa.index') }}"><i class="bi bi-archive-fill me-2"></i>Histórico de Caixas</a></li>
                                <li><a class="dropdown-item {{ request()->routeIs('caixa.create') ? 'active' : '' }}" href="{{ route('caixa.create') }}"><i class="bi bi-door-open-fill me-2"></i>Abrir Caixa</a></li>
                            </ul>
                        </li>
                    @endcan

                    @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('relatorios.*') && !request()->routeIs('relatorios.estoque_baixo') ? 'active' : '' }}"
                                href="#" id="navbarDropdownRelatorios" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-bar-chart-line-fill me-1"></i> Relatórios
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownRelatorios">
                                @if(in_array(Auth::user()->tipo_usuario, ['admin', 'atendente']))
                                    <li><a class="dropdown-item {{ request()->routeIs('relatorios.vendas_acessorios') ? 'active' : '' }}"
                                            href="{{ route('relatorios.vendas_acessorios') }}"><i class="bi bi-tags-fill me-2"></i>Vendas de Acessórios</a></li>
                                    <li><a class="dropdown-item {{ request()->routeIs('relatorios.itens_mais_vendidos') ? 'active' : '' }}"
                                            href="{{ route('relatorios.itens_mais_vendidos') }}"><i class="bi bi-trophy-fill me-2"></i>Itens Mais Vendidos</a></li>
                                @endif
                                @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico']))
                                    <li><a class="dropdown-item {{ request()->routeIs('relatorios.pecas_mais_utilizadas') ? 'active' : '' }}"
                                            href="{{ route('relatorios.pecas_mais_utilizadas') }}"><i class="bi bi-wrench-adjustable-circle-fill me-2"></i>Peças Mais Utilizadas</a></li>
                                    <li><a class="dropdown-item {{ request()->routeIs('relatorios.atendimentos_tecnico') ? 'active' : '' }}"
                                            href="{{ route('relatorios.atendimentos_tecnico') }}"><i class="bi bi-person-gear me-2"></i>Atendimentos por Técnico</a></li>
                                @endif
                                @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
                                    <li><a class="dropdown-item {{ request()->routeIs('relatorios.atendimentos_status') ? 'active' : '' }}"
                                            href="{{ route('relatorios.atendimentos_status') }}"><i class="bi bi-pie-chart-fill me-2"></i>Atendimentos por Status</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif
                     <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('consulta.index') ? 'active' : '' }}" href="{{ route('consulta.index') }}" target="_blank"><i class="bi bi-search me-1"></i>Consulta Cliente</a>
                    </li>
                @endauth
            </ul>

            {{-- Parte da direita da Navbar (Usuário Logado e Logout) --}}
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @auth
                    @if(Auth::user()->tipo_usuario == 'admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}"
                                href="{{ route('usuarios.index') }}">
                                <i class="bi bi-people-fill me-1"></i> Gerenciar Usuários
                            </a>
                        </li>
                    @endif
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="#" id="navbarDropdownUserMenu" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                            <span class="badge bg-info-subtle border border-info-subtle text-info-emphasis rounded-pill ms-1">{{ ucfirst(Auth::user()->tipo_usuario) }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUserMenu">
                            <li><a class="dropdown-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><i class="bi bi-person-badge me-2"></i>Meu Perfil</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                        <i class="bi bi-box-arrow-right me-2"></i> Sair
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}"
                            href="{{ route('login') }}">Login (Interno)</a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}"
                                href="{{ route('register') }}">Registrar (Interno)</a>
                        </li>
                    @endif
                @endauth
            </ul>
        </div>
    </div>
</nav>
{{-- Estilo para o badge pulsar levemente --}}
@push('styles')
    <style>
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .7; }
        }
        /* Para melhorar a aparência do badge de tipo de usuário na navbar */
        .navbar .badge {
            font-size: 0.7em;
            padding: 0.3em 0.5em;
        }
    </style>
@endpush
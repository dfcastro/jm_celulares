{{-- resources/views/layouts/partials/_navigation.blade.php --}}
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}"> <i class="bi bi-phone-vibrate-fill me-2" style="color: var(--jm-laranja, #FFA500);"></i>
            <span style="color: var(--jm-laranja, #FFA500); font-weight: bold;">JM</span>
            <span class="text-white">CELULARES</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @auth {{-- Todos os menus principais abaixo são para usuários logados --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" aria-current="page" href="{{ route('dashboard') }}">Painel</a>
                </li>
                {{-- ... ORÇAMENTOS ... --}}

                @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente'])) {{-- Ou a permissão que você definir --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('orcamentos.*') ? 'active' : '' }}" href="#" id="navbarDropdownOrcamentos" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-file-earmark-medical-fill"></i> Orçamentos
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownOrcamentos">
                        <li><a class="dropdown-item {{ request()->routeIs('orcamentos.index') ? 'active' : '' }}" href="{{ route('orcamentos.index') }}">Listar Orçamentos</a></li>
                        <li><a class="dropdown-item {{ request()->routeIs('orcamentos.create') ? 'active' : '' }}" href="{{ route('orcamentos.create') }}">Novo Orçamento</a></li>
                    </ul>
                </li>
                @endif

                {{-- ... outros itens de menu ... --}}

                {{-- SERVIÇOS (Atendimentos) --}}
                @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('atendimentos.*') ? 'active' : '' }}" href="{{ route('atendimentos.index') }}" id="navbarDropdownAtendimentos" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Atendimentos
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownAtendimentos">
                        <li><a class="dropdown-item" href="{{ route('atendimentos.index') }}">Listar Atendimentos</a></li>
                        <li><a class="dropdown-item" href="{{ route('atendimentos.create') }}">Novo Atendimento</a></li>
                    </ul>
                </li>
                @endif

                {{-- VENDAS DE ACESSÓRIOS --}}
                @if(in_array(Auth::user()->tipo_usuario, ['admin', 'atendente']))
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('vendas-acessorios.*') ? 'active' : '' }}" href="{{ route('vendas-acessorios.index') }}" id="navbarDropdownVendas" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Vendas
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownVendas">
                        <li><a class="dropdown-item" href="{{ route('vendas-acessorios.index') }}">Listar Vendas</a></li>
                        <li><a class="dropdown-item" href="{{ route('vendas-acessorios.create') }}">Nova Venda</a></li>
                    </ul>
                </li>
                @endif

                {{-- ESTOQUE --}}
                @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('estoque.index') || request()->routeIs('estoque.show') || request()->routeIs('entradas-estoque.*') || request()->routeIs('saidas-estoque.*') || request()->routeIs('relatorios.estoque_baixo') ? 'active' : '' }}" href="{{ route('estoque.index') }}" id="navbarDropdownEstoque" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Estoque
                        {{-- BADGE DE NOTIFICAÇÃO DE ESTOQUE BAIXO --}}
                        @if(isset($contagemItensEstoqueBaixoGlobal) && $contagemItensEstoqueBaixoGlobal > 0 && in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico']))
                        <span class="badge bg-danger rounded-pill ms-1 animate-pulse" title="{{ $contagemItensEstoqueBaixoGlobal }} itens com estoque baixo!">
                            {{ $contagemItensEstoqueBaixoGlobal }}
                        </span>
                        @endif
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownEstoque">
                        <li><a class="dropdown-item" href="{{ route('estoque.index') }}">Consultar Estoque</a></li>

                        @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente'])) {{-- Atendente pode criar itens e entradas --}}
                        <li><a class="dropdown-item" href="{{ route('estoque.create') }}">Novo Item de Estoque</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="{{ route('entradas-estoque.index') }}">Histórico de Entradas no Estoque</a></li>
                        <li><a class="dropdown-item" href="{{ route('entradas-estoque.create') }}">Nova Entrada</a></li>
                        @endif

                        @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico'])) {{-- Saídas avulsas e histórico mais restritos --}}
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="{{ route('saidas-estoque.index') }}">Histórico de Saídas do Estoque (Avulsas)</a></li>
                        <li><a class="dropdown-item" href="{{ route('saidas-estoque.create') }}">Nova Saída Avulsa</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="{{ route('estoque.historico_unificado') }}">Histórico Unificado</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item d-flex justify-content-between align-items-center {{ request()->routeIs('relatorios.estoque_baixo') ? 'active' : '' }}" href="{{ route('relatorios.estoque_baixo') }}">
                                <div><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Itens Estoque Baixo</div>
                                @if(isset($contagemItensEstoqueBaixoGlobal) && $contagemItensEstoqueBaixoGlobal > 0)
                                <span class="badge bg-warning text-dark rounded-pill">{{ $contagemItensEstoqueBaixoGlobal }}</span>
                                @endif
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                {{-- CADASTROS (Clientes) --}}
                @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('clientes.*') ? 'active' : '' }}" href="{{ route('clientes.index') }}" id="navbarDropdownClientes" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Clientes
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownClientes">
                        <li><a class="dropdown-item" href="{{ route('clientes.index') }}">Lista de Clientes</a></li>
                        <li><a class="dropdown-item" href="{{ route('clientes.create') }}">Novo Cliente</a></li>
                    </ul>
                </li>
                @endif

                {{-- CONSULTA CLIENTE --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('consulta.index') ? 'active' : '' }}" href="{{ route('consulta.index') }}">Consulta Atendimento</a>
                </li>

                {{-- RELATÓRIOS --}}
                @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('relatorios.*') && !request()->routeIs('relatorios.estoque_baixo') ? 'active' : '' }}" href="#" id="navbarDropdownRelatorios" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Relatórios
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownRelatorios">
                        {{-- O link para Estoque Baixo foi movido para dentro do dropdown de Estoque para admin/tecnico --}}
                        @if(in_array(Auth::user()->tipo_usuario, ['admin', 'atendente']))
                        <li><a class="dropdown-item {{ request()->routeIs('relatorios.vendas_acessorios') ? 'active' : '' }}" href="{{ route('relatorios.vendas_acessorios') }}">Vendas de Acessórios</a></li>
                        <li><a class="dropdown-item {{ request()->routeIs('relatorios.itens_mais_vendidos') ? 'active' : '' }}" href="{{ route('relatorios.itens_mais_vendidos') }}">Itens Mais Vendidos</a></li>
                        @endif
                        @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico']))
                        <li><a class="dropdown-item {{ request()->routeIs('relatorios.pecas_mais_utilizadas') ? 'active' : '' }}" href="{{ route('relatorios.pecas_mais_utilizadas') }}">Peças Mais Utilizadas</a></li>
                        <li><a class="dropdown-item {{ request()->routeIs('relatorios.atendimentos_tecnico') ? 'active' : '' }}" href="{{ route('relatorios.atendimentos_tecnico') }}">Atendimentos por Técnico</a></li>
                        @endif
                        @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
                        <li><a class="dropdown-item {{ request()->routeIs('relatorios.atendimentos_status') ? 'active' : '' }}" href="{{ route('relatorios.atendimentos_status') }}">Atendimentos por Status</a></li>
                        @endif
                    </ul>
                </li>
                @endif
                @endauth
            </ul>

            {{-- Parte da direita da Navbar (Usuário Logado e Logout) --}}
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @auth
                @if(Auth::user()->tipo_usuario == 'admin')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                        <i class="bi bi-people-fill"></i> Gerenciar Usuários
                    </a>
                </li>
                @endif
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUserMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                        <span class="badge bg-secondary">{{ ucfirst(Auth::user()->tipo_usuario) }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUserMenu">
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    <i class="bi bi-box-arrow-right"></i> Sair
                                </a>
                            </form>
                        </li>
                    </ul>
                </li>
                @else
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">Login (Interno)</a>
                </li>
                @if (Route::has('register'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">Registrar (Interno)</a>
                </li>
                @endif
                @endauth
            </ul>
        </div>
    </div>
</nav>
{{-- Adicionei um estilo para o badge pulsar levemente --}}
@push('styles')
<style>
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: .7;
        }
    }
</style>
@endpush
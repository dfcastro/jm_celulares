{{-- resources/views/layouts/partials/_navigation.blade.php --}}

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">JM Celulares</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                {{-- PAINEL PRINCIPAL (Visível para todos os logados) --}}
                @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" aria-current="page" href="{{ route('dashboard') }}">Painel</a>
                    </li>
                @endauth

                {{-- SERVIÇOS (Atendimentos) --}}
                @auth {{-- Só para usuários logados --}}
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
                @endauth

                {{-- VENDAS DE ACESSÓRIOS --}}
                @auth
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
                @endauth

                {{-- ESTOQUE --}}
                @auth
                    @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente'])) {{-- Atendente pode precisar consultar estoque --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('estoque.*') || request()->routeIs('entradas-estoque.*') || request()->routeIs('saidas-estoque.*') ? 'active' : '' }}" href="{{ route('estoque.index') }}" id="navbarDropdownEstoque" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Estoque
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownEstoque">
                            <li><a class="dropdown-item" href="{{ route('estoque.index') }}">Listar Itens</a></li>
                            @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico']))
                                <li><a class="dropdown-item" href="{{ route('estoque.create') }}">Novo Item de Estoque</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('entradas-estoque.index') }}">Entradas no Estoque</a></li>
                                {{-- O link para criar nova entrada pode estar na própria página de entradas ou aqui --}}
                                <li><a class="dropdown-item" href="{{ route('entradas-estoque.create') }}">Nova Entrada</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('saidas-estoque.index') }}">Saídas do Estoque</a></li>
                                {{-- O link para criar nova saída pode estar na própria página de saídas ou aqui --}}
                                <li><a class="dropdown-item" href="{{ route('saidas-estoque.create') }}">Nova Saída Avulsa</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('estoque.historico_unificado') }}">Histórico Unificado</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif
                @endauth

                {{-- CADASTROS (Clientes) --}}
                @auth
                    @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('clientes.*') ? 'active' : '' }}" href="{{ route('clientes.index') }}" id="navbarDropdownClientes" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Cadastros
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownClientes">
                            <li><a class="dropdown-item" href="{{ route('clientes.index') }}">Clientes</a></li>
                            <li><a class="dropdown-item" href="{{ route('clientes.create') }}">Novo Cliente</a></li>
                        </ul>
                    </li>
                    @endif
                @endauth

                {{-- CONSULTA PÚBLICA (Visível para todos, mesmo não logados, se o layout permitir) --}}
                {{-- Se este menu só aparece para logados, então o @auth já cobre. --}}
                {{-- Se este _navigation.blade.php fosse usado em um layout público, o @auth não estaria em volta de tudo. --}}
                @auth {{-- Mantendo dentro do @auth por enquanto, assumindo que este menu é para área logada --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('consulta.index') ? 'active' : '' }}" href="{{ route('consulta.index') }}">Consulta Cliente</a>
                    </li>
                @endauth


                {{-- RELATÓRIOS --}}
                @auth
                    @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente'])) {{-- Pelo menos atendente para ver algum relatório --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('relatorios.*') ? 'active' : '' }}" href="#" id="navbarDropdownRelatorios" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Relatórios
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownRelatorios">
                            @if(in_array(Auth::user()->tipo_usuario, ['admin', 'tecnico', 'atendente']))
                                <li><a class="dropdown-item {{ request()->routeIs('relatorios.estoque_baixo') ? 'active' : '' }}" href="{{ route('relatorios.estoque_baixo') }}">Estoque Abaixo do Mínimo</a></li>
                            @endif
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
                            {{-- Adicionar futuros relatórios aqui com suas devidas permissões --}}
                        </ul>
                    </li>
                    @endif
                @endauth
            </ul>

            {{-- Parte da direita da Navbar (Usuário Logado e Logout) --}}
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @auth
                    {{-- Link para Gerenciar Usuários (somente para admin) --}}
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
                            {{-- O link de perfil está comentado pois a rota 'profile.edit' não está definida no seu auth.php --}}
                            {{-- <li><a class="dropdown-item" href="{{-- route('profile.edit') --}}">Meu Perfil</a></li> --}}
                            {{-- @if(Auth::user()->tipo_usuario == 'admin' || Auth::user()->tipo_usuario == 'tecnico')
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#">Configurações da Loja (Admin/Tec)</a></li>
                            @endif --}}
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
                @else {{-- Se não estiver logado, mostra links de Login/Registro da página pública --}}
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

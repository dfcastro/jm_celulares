<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'JM Celulares - Assistência Técnica e Acessórios')</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    {{-- Google Fonts (Exemplo: Roboto) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">

    {{-- Vite --}}
    @vite(['resources/css/jm-celulares-site.css'])

    @stack('styles-public')
</head>
<body>

    <header class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('site.home') }}">
                {{-- <img src="{{ asset('images/logo_jm_celulares.png') }}" alt="JM Celulares Logo" style="max-height: 40px; margin-right: 10px;"> --}}
                <i class="bi bi-phone-vibrate-fill me-2" style="color: var(--jm-laranja, #FFA500);"></i>
                <span style="color: var(--jm-laranja, #FFA500); font-weight: bold;">JM</span> <span class="text-white">CELULARES</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavSite" aria-controls="navbarNavSite" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavSite">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a href="{{ route('site.home') }}" class="nav-link px-2 text-white @if(request()->routeIs('site.home')) active @endif">Início</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('site.home') }}#services" class="nav-link px-2 text-white">Serviços</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('site.home') }}#about" class="nav-link px-2 text-white">Sobre Nós</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('site.home') }}#contact" class="nav-link px-2 text-white">Contato</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('consulta.index') }}" class="nav-link px-2 text-warning fw-bold @if(request()->routeIs('consulta.index') || request()->routeIs('consulta.status')) active @endif">
                            Consultar Reparo
                        </a>
                    </li>
                </ul>
                <div class="text-end ms-lg-3 mt-2 mt-lg-0">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm">Painel Interno</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">Login (Interno)</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        @yield('content-public')
    </main>

    <footer class="footer-site text-center">
        <div class="container">
            <p class="mb-1">© {{ date('Y') }} JM Celulares. Todos os direitos reservados.</p>
            <p class="mb-1">
                <a href="https://instagram.com/Jmcelulares.mg" target="_blank" class="text-white text-decoration-none me-2">
                    <i class="bi bi-instagram"></i> @Jmcelulares.mg
                </a> |
                <a href="tel:+5538992696404" class="text-white text-decoration-none ms-2">
                    <i class="bi bi-telephone"></i> (38) 99269-6404
                </a>
            </p>
            <p class="mb-0"><small>Desenvolvido com <i class="bi bi-heart-fill text-danger"></i> por FrankDev Soluções</small></p>
        </div>
    </footer>

    {{-- jQuery CDN (adicione esta linha ANTES do Bootstrap JS e do @stack) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    {{-- jQuery Mask Plugin CDN (adicione se for usar máscaras) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    {{-- Bootstrap JS Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>


    @stack('scripts-public')
</body>
</html>
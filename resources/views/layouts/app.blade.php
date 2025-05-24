<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- Adicionado CSRF token --}}

    <title>@yield('title', config('app.name', 'JM Celulares'))</title> {{-- Título dinâmico --}}

    {{-- Bootstrap CSS (CDN) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    {{-- jQuery UI CSS (para autocompletes) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.min.css" />

    {{-- Seus estilos CSS personalizados podem vir do app.css compilado pelo Vite se desejar --}}
    {{-- OU estilos inline/específicos aqui --}}
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- Se você quer usar Vite para CSS/JS aqui também --}}

    <style>
        body {
            padding-top: 56px;
            /* Altura da navbar fixa no topo */
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
        }

        .footer {
            background-color: #343a40;
            /* bg-dark */
            color: white;
            padding: 1rem 0;
            font-size: 0.9em;
            margin-top: auto;
            /* Empurra o rodapé para baixo */
        }

        .ui-autocomplete {
            z-index: 1055 !important;
            /* Maior que o z-index da navbar do Bootstrap (1050) */
        }

        .visually-hidden {
            position: absolute !important;
            /* ... (mantido como antes) ... */
        }
    </style>

    @stack('styles') {{-- Para CSS específico da página --}}
</head>

<body>
    @include('layouts.partials._navigation') {{-- Seu menu Bootstrap personalizado --}}

    <main class="main-content py-4">
        <div class="container">
            <div class="container">
              
                
                {{-- FIM DA SESSÃO DE MENSAGENS FLASH --}}
                @yield('content') {{-- <<<< AQUI É ONDE O CONTEÚDO DAS SUAS VIEWS SERÁ INJETADO --}}
            </div>
    </main>

    <footer class="footer text-center">
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
            <p class="mb-0"><small>Desenvolvido por FrankDev Soluções <i class="bi bi-cpu text-success"></i> </small></p>
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    {{-- jQuery UI JS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    {{-- jQuery --}}

    {{-- Bootstrap JS Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    {{-- @vite(['resources/js/app.js']) --}}
    {{-- Para JS específico da página --}}
    @stack('scripts')
</body>

</html>
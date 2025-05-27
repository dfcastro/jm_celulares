{{-- resources/views/layouts/guest.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Acesso ao Sistema</title> {{-- Título mais específico --}}

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

        {{-- Se você criou um auth-custom.css e o adicionou ao vite.config.js,
             a linha @vite abaixo já deve incluí-lo junto com app.css e app.js.
             Se não, e você quer que app.css (Tailwind) e auth-custom.css sejam carregados:
             @vite(['resources/css/app.css', 'resources/css/auth-custom.css', 'resources/js/app.js'])
             Por padrão, o Breeze geralmente tem apenas: --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])

    </head>
    <body class="font-sans text-gray-900 antialiased">
        {{-- Sugestão: Mudar a cor de fundo para algo que combine mais com seu sistema interno --}}
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-100 dark:bg-gray-900">
            <div>
                <a href="/">
                    {{-- O componente do logo usará o que você definiu em application-logo.blade.php --}}
                    {{-- Ajuste as classes de tamanho aqui se necessário --}}
                    <x-application-logo class="w-auto h-12 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white  shadow-xl overflow-hidden sm:rounded-lg" style="border-top: 5px solid var(--jm-laranja, #FFA500);">
                {{-- Card com borda superior laranja --}}
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
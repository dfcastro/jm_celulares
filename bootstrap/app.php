<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class, // Exemplo que o Breeze pode adicionar
            'admin' => \App\Http\Middleware\AdminMiddleware::class,       // <<<<<<<<<<<<<< ADICIONE ESTA LINHA
            // Adicione outros aliases que você possa precisar
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

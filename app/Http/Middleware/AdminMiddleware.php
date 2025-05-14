<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Importar Auth
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o usuário está logado E se o tipo_usuario é 'admin'
        if (Auth::check() && Auth::user()->tipo_usuario == 'admin') {
            return $next($request); // Permite que a requisição prossiga
        }

        // Se não for admin, redireciona para o dashboard com uma mensagem de erro
        // Ou você pode lançar uma exceção de acesso negado: abort(403, 'Acesso não autorizado.');
        return redirect()->route('dashboard')->with('error', 'Acesso não autorizado. Apenas administradores podem acessar esta área.');
    }
}
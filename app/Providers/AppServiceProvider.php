<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View; // Adicione
use App\Models\Estoque;             // Adicione
use Illuminate\Support\Facades\Auth; // Adicione

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        // Compartilha a contagem de itens com estoque baixo com todas as views
        // que usam o layout que inclui a navegação.
        // É melhor fazer isso de forma condicional ou mais específica se não for sempre necessário.
        View::composer('layouts.partials._navigation', function ($view) {
            if (Auth::check()) { // Só calcula se o usuário estiver logado
                $contagemItensEstoqueBaixoGlobal = Estoque::whereColumn('quantidade', '<=', 'estoque_minimo')
                    ->where('estoque_minimo', '>', 0)
                    ->count();
                $view->with('contagemItensEstoqueBaixoGlobal', $contagemItensEstoqueBaixoGlobal);
            } else {
                $view->with('contagemItensEstoqueBaixoGlobal', 0);
            }
        });
    }

}

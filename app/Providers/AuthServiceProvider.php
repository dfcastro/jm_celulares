<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider; // <<<< GARANTA QUE ESTA LINHA ESTÁ CORRETA
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider // <<<< GARANTA QUE ESTENDE ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * Se você não estiver usando policies ainda, pode deixar este array vazio
     * ou comentar a chamada a `$this->registerPolicies();` no método boot().
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // $this->registerPolicies(); // Comente se não estiver usando policies de modelo

        Gate::define('is-admin', function ($user) {
            return $user && $user->tipo_usuario == 'admin';
        });

        Gate::define('is-tecnico', function ($user) {
            return $user && $user->tipo_usuario == 'tecnico';
        });

        Gate::define('is-atendente', function ($user) {
            return $user && $user->tipo_usuario == 'atendente';
        });

        // Gates combinados para facilitar
        Gate::define('is-admin-or-tecnico', function ($user) {
            return $user && ($user->tipo_usuario == 'admin' || $user->tipo_usuario == 'tecnico');
        });

        Gate::define('is-admin-or-atendente', function ($user) {
            return $user && ($user->tipo_usuario == 'admin' || $user->tipo_usuario == 'atendente');
        });
        Gate::define('can-manage-basic-stock', function ($user) {
            return $user && in_array($user->tipo_usuario, ['admin', 'tecnico', 'atendente']);
        });

        // Gate para todos os tipos de usuários internos (se necessário para alguma ação genérica)
        Gate::define('is-internal-user', function ($user) {
            return $user && in_array($user->tipo_usuario, ['admin', 'tecnico', 'atendente']);
        });
    }
}
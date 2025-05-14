<?php

namespace App\Http\Controllers;

use App\Models\Estoque; // Para buscar itens de estoque
use App\Models\Atendimento; // Para futuras estatísticas do dashboard
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Para informações do usuário logad


class DashboardController extends Controller
{
    public function index()
    {
        $contagemItensEstoqueBaixo = Estoque::whereColumn('quantidade', '<=', 'estoque_minimo')
            ->where('estoque_minimo', '>', 0)
            ->count(); // Apenas conta os itens

        // Passar a contagem para todas as views que usam o layout principal
        // A melhor forma de fazer isso é com um View Composer ou compartilhando globalmente
        // Por agora, para simplificar, vamos focar em como usar no dashboard,
        // mas o ideal é que essa contagem esteja disponível no _navigation.blade.php.

        // Para o dashboard, ainda podemos passar a lista se quisermos um link direto
        $itensEstoqueBaixo = Estoque::whereColumn('quantidade', '<=', 'estoque_minimo')
            ->where('estoque_minimo', '>', 0)
            ->orderBy('nome')
            ->get();

        $atendimentosAbertosCount = Atendimento::whereNotIn('status', ['Entregue', 'Cancelado', 'Reprovado'])->count(); // Ajuste os status conforme necessário
        
        return view('dashboard', compact(
            'itensEstoqueBaixo',
            'contagemItensEstoqueBaixo',
            'atendimentosAbertosCount' // Passe para a view
        ));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Estoque;
use App\Models\Atendimento;
use App\Models\VendaAcessorio; // Adicionar
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // Adicionar
use Illuminate\Support\Facades\DB; // Adicionar


class DashboardController extends Controller
{
    public function index()
    {
        $usuarioLogado = Auth::user();
        $dataAtual = Carbon::now();

        // Widget de Estoque Baixo (como já existe)
        $contagemItensEstoqueBaixo = Estoque::whereColumn('quantidade', '<=', 'estoque_minimo')
            ->where('estoque_minimo', '>', 0)
            ->count();
        $itensEstoqueBaixo = Estoque::whereColumn('quantidade', '<=', 'estoque_minimo')
            ->where('estoque_minimo', '>', 0)
            ->orderBy('nome')
            ->take(5) // Limitar para o widget
            ->get();

        // Novos Widgets de Contadores
        $atendimentosHoje = Atendimento::whereDate('data_entrada', $dataAtual->toDateString())->count();

        $atendimentosPendentes = Atendimento::whereNotIn('status', ['Entregue', 'Cancelado', 'Reprovado'])
            ->count(); // Ajuste os status finais/cancelados conforme sua lógica

        $vendasHojeValor = VendaAcessorio::whereDate('data_venda', $dataAtual->toDateString())
            ->sum('valor_total');

        // Dados para o Gráfico de Atendimentos por Status (últimos 30 dias)
        $dataInicioMes = $dataAtual->copy()->subDays(30)->startOfDay();
        $dataFimMes = $dataAtual->copy()->endOfDay();

        $atendimentosPorStatus = Atendimento::select('status', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$dataInicioMes, $dataFimMes]) // Ou data_entrada, dependendo do que faz mais sentido
            ->groupBy('status')
            ->pluck('total', 'status') // Gera um array associativo: ['Status' => total, ...]
            ->all(); // Converte para array PHP simples

        // Lista de todos os status possíveis para garantir que todos apareçam no gráfico
        $todosOsStatus = ['Em diagnóstico', 'Aguardando peça', 'Em manutenção', 'Pronto para entrega', 'Entregue', 'Cancelado', 'Reprovado']; // Adicione todos os seus status
        $labelsGraficoStatus = [];
        $dadosGraficoStatus = [];

        foreach ($todosOsStatus as $status) {
            $labelsGraficoStatus[] = $status;
            $dadosGraficoStatus[] = $atendimentosPorStatus[$status] ?? 0; // Se o status não tiver contagem, usa 0
        }

        return view('dashboard', compact(
            'itensEstoqueBaixo',
            'contagemItensEstoqueBaixo',
            'atendimentosHoje',
            'atendimentosPendentes',
            'vendasHojeValor',
            'labelsGraficoStatus', // Para Chart.js
            'dadosGraficoStatus'   // Para Chart.js
        ));
    }
}

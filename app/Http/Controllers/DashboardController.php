<?php

namespace App\Http\Controllers;

use App\Models\Estoque;
use App\Models\Atendimento;
use App\Models\VendaAcessorio; // Certifique-se que está importado
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // <<<< IMPORTANTE: GARANTA QUE ESTA LINHA ESTEJA AQUI E CORRETA
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $usuarioLogado = Auth::user();
        $dataAtual = Carbon::now();

        // --- Widgets de Estoque e Atendimento ---
        $contagemItensEstoqueBaixo = Estoque::whereColumn('quantidade', '<=', 'estoque_minimo')
            ->where('estoque_minimo', '>', 0)
            ->count();
        // $itensEstoqueBaixo = Estoque::whereColumn('quantidade', '<=', 'estoque_minimo') // Comentado pois não é usado diretamente na view passada
        //     ->where('estoque_minimo', '>', 0)
        //     ->orderBy('nome')
        //     ->take(5)
        //     ->get();
        $atendimentosHoje = Atendimento::whereDate('data_entrada', $dataAtual->toDateString())->count();
        $atendimentosPendentes = Atendimento::whereNotIn('status', ['Entregue', 'Cancelado', 'Reprovado'])
            ->count();

        // --- Widgets de Vendas ---
        $vendasHojeValor = VendaAcessorio::whereDate('data_venda', $dataAtual->toDateString())
            ->sum('valor_total');

        $inicioSemanaAtual = $dataAtual->copy()->startOfWeek(Carbon::MONDAY);
        // Para o widget "Vendas Semana", usamos o fim da semana atual (domingo)
        $fimSemanaAtualWidget = $dataAtual->copy()->endOfWeek(Carbon::SUNDAY);
        $vendasEstaSemanaValor = VendaAcessorio::whereBetween('data_venda', [$inicioSemanaAtual, $fimSemanaAtualWidget])
            ->sum('valor_total');

        $inicioMesAtual = $dataAtual->copy()->startOfMonth();
        $vendasEsteMesValor = VendaAcessorio::whereBetween('data_venda', [$inicioMesAtual, $dataAtual->copy()->endOfDay()])
            ->sum('valor_total');

        // (Opcional) Vendas Mês Anterior Completo
        // $inicioMesAnterior = $dataAtual->copy()->subMonthNoOverflow()->startOfMonth();
        // $fimMesAnterior = $dataAtual->copy()->subMonthNoOverflow()->endOfMonth();
        // $vendasMesAnteriorValor = VendaAcessorio::whereBetween('data_venda', [$inicioMesAnterior, $fimMesAnterior])
        //     ->sum('valor_total');


        // --- Dados para Gráfico de Atendimentos por Status (Últimos 30 dias) ---
        $dataInicioGraficoStatus = $dataAtual->copy()->subDays(30)->startOfDay();
        $dataFimGraficoStatus = $dataAtual->copy()->endOfDay();

        $contagemPorStatus = Atendimento::select('status', DB::raw('count(*) as total'))
            ->whereBetween('data_entrada', [$dataInicioGraficoStatus, $dataFimGraficoStatus]) // Usando data_entrada para consistência
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $todosOsStatus = method_exists(Atendimento::class, 'getPossibleStatuses')
            ? Atendimento::getPossibleStatuses()
            : ['Em diagnóstico', 'Aguardando peça', 'Em manutenção', 'Pronto para entrega', 'Entregue', 'Cancelado', 'Reprovado'];

        $labelsGraficoStatus = [];
        $dadosGraficoStatus = [];
        foreach ($todosOsStatus as $status) {
            $labelsGraficoStatus[] = $status;
            $dadosGraficoStatus[] = $contagemPorStatus[$status] ?? 0;
        }

        // --- Dados para Gráfico de Vendas da Semana (Últimos 7 dias) ---
        $labelsGraficoVendasSemana = [];
        $dadosGraficoVendasSemana = [];
        $dataReferenciaVendas = $dataAtual->copy(); // Usar uma cópia para não alterar $dataAtual

        for ($i = 6; $i >= 0; $i--) { // Itera dos 6 dias atrás até hoje
            $dia = $dataReferenciaVendas->copy()->subDays($i);
            $labelsGraficoVendasSemana[] = $dia->isoFormat('ddd DD/MM'); // Formato: Sex 10/05

            $totalVendidoNoDia = VendaAcessorio::whereDate('data_venda', $dia->toDateString())
                ->sum('valor_total');
            $dadosGraficoVendasSemana[] = $totalVendidoNoDia ?? 0;
        }


        return view('dashboard', compact(
            // 'itensEstoqueBaixo', // Não estamos usando na view passada na última interação
            'contagemItensEstoqueBaixo',
            'atendimentosHoje',
            'atendimentosPendentes',
            'vendasHojeValor',
            'vendasEstaSemanaValor',
            'vendasEsteMesValor',
            // 'vendasMesAnteriorValor', // Comentado pois não foi adicionado à view
            'labelsGraficoStatus',
            'dadosGraficoStatus',
            'labelsGraficoVendasSemana', // Nova variável
            'dadosGraficoVendasSemana'   // Nova variável
        ));
    }
}
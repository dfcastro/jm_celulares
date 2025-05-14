<?php

namespace App\Http\Controllers;

use App\Models\Estoque;
use App\Models\VendaAcessorio;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Atendimento;
use App\Models\User;

class RelatorioController extends Controller
{
    /**
     * Exibe o relatório de itens com estoque abaixo do mínimo.
     */
    public function estoqueAbaixoMinimo(Request $request)
    {
        $itensAbaixoMinimo = Estoque::whereColumn('quantidade', '<=', 'estoque_minimo')
            ->where('estoque_minimo', '>', 0)
            ->orderBy('nome')
            ->paginate(15);

        return view('relatorios.estoque_baixo', compact('itensAbaixoMinimo'));
    }

    /**
     * Exibe o relatório de vendas de acessórios por período.
     */
    public function vendasAcessoriosPeriodo(Request $request)
    {
        $dataInicial = $request->input('data_inicial') ? Carbon::parse($request->input('data_inicial'))->startOfDay() : Carbon::now()->startOfMonth()->startOfDay();
        $dataFinal = $request->input('data_final') ? Carbon::parse($request->input('data_final'))->endOfDay() : Carbon::now()->endOfDay();

        $clienteId = $request->input('cliente_id'); // O name do campo hidden é 'cliente_id'
        $formaPagamento = $request->input('forma_pagamento');

        // Validação
        if ($dataInicial->gt($dataFinal)) {
            return back()->withErrors(['data_inicial' => 'A data inicial não pode ser maior que a data final.'])->withInput();
        }

        $query = VendaAcessorio::with('cliente')
            ->whereBetween('data_venda', [$dataInicial, $dataFinal]);

        $clienteSelecionado = null; // Variável para armazenar dados do cliente filtrado
        if ($clienteId) {
            $query->where('cliente_id', $clienteId);
            $clienteSelecionado = Cliente::find($clienteId); // Busca o cliente para exibir o nome no formulário
        }

        if ($formaPagamento) {
            $query->where('forma_pagamento', $formaPagamento);
        }

        // Clonar a query para cálculos antes da paginação
        $queryParaCalculos = clone $query;

        $vendas = $query->orderBy('data_venda', 'desc')->paginate(15);

        // Calcular totais para o resumo
        $totalVendasValor = $queryParaCalculos->sum('valor_total');
        $numeroDeVendas = $queryParaCalculos->count();

        // Não precisamos mais carregar todos os $clientes para o select.
        // $clientes = Cliente::orderBy('nome_completo')->get();

        $formasPagamentoDisponiveis = VendaAcessorio::select('forma_pagamento')
            ->whereNotNull('forma_pagamento')
            ->where('forma_pagamento', '!=', '')
            ->distinct()
            ->orderBy('forma_pagamento')
            ->pluck('forma_pagamento');

        return view('relatorios.vendas_acessorios', compact(
            'vendas',
            'dataInicial',
            'dataFinal',
            'clienteId', // Mantém o ID do cliente para lógica interna, se necessário
            'clienteSelecionado', // Envia o objeto do cliente selecionado para a view
            'formaPagamento',
            // 'clientes', // Não mais necessário enviar a lista completa de clientes
            'formasPagamentoDisponiveis',
            'totalVendasValor',
            'numeroDeVendas'
        ));
    }
    public function itensMaisVendidos(Request $request)
    {
        $dataInicialPadrao = Carbon::now()->startOfMonth()->startOfDay();
        $dataFinalPadrao = Carbon::now()->endOfDay();

        $dataInicial = $request->input('data_inicial') ? Carbon::parse($request->input('data_inicial'))->startOfDay() : $dataInicialPadrao;
        $dataFinal = $request->input('data_final') ? Carbon::parse($request->input('data_final'))->endOfDay() : $dataFinalPadrao;
        $tipoItem = $request->input('tipo_item'); // Ex: 'ACESSORIO_VENDA'
        $limiteResultados = $request->input('limite_resultados', 10); // Padrão para Top 10

        if ($dataInicial->gt($dataFinal)) {
            return back()->withErrors(['data_inicial' => 'A data inicial não pode ser maior que a data final.'])->withInput();
        }

        // Query para buscar os itens mais vendidos
        $query = DB::table('venda_acessorio_estoque as vae')
            ->join('estoque as e', 'vae.estoque_id', '=', 'e.id')
            ->join('vendas_acessorios as va', 'vae.venda_acessorio_id', '=', 'va.id')
            ->select(
                'e.id as estoque_id',
                'e.nome as nome_item',
                'e.modelo_compativel',
                'e.marca',
                'e.tipo as tipo_item_estoque',
                DB::raw('SUM(vae.quantidade) as total_vendido') // Soma a quantidade da tabela pivô
            )
            ->whereBetween('va.data_venda', [$dataInicial, $dataFinal]);

        // Filtro opcional por tipo de item (do estoque)
        if ($tipoItem) {
            $query->where('e.tipo', $tipoItem);
        }

        $itensMaisVendidos = $query->groupBy(
            'e.id', // Agrupa por ID do item de estoque
            'e.nome',
            'e.modelo_compativel',
            'e.marca',
            'e.tipo'
        )
            ->orderByDesc('total_vendido') // Ordena pela quantidade total vendida
            ->orderBy('e.nome') // Critério de desempate
            ->limit($limiteResultados) // Limita o número de resultados (Top N)
            ->get();

        // Tipos de item para o dropdown de filtro
        $tiposDeItemParaFiltro = Estoque::select('tipo')
            ->whereNotNull('tipo')
            ->where('tipo', '!=', '')
            ->distinct()
            ->orderBy('tipo')
            ->pluck('tipo');

        return view('relatorios.itens_mais_vendidos', compact(
            'itensMaisVendidos',
            'dataInicial',
            'dataFinal',
            'tipoItem',
            'limiteResultados',
            'tiposDeItemParaFiltro'
        ));
    }
    public function pecasMaisUtilizadas(Request $request)
    {
        $dataInicialPadrao = Carbon::now()->startOfMonth()->startOfDay();
        $dataFinalPadrao = Carbon::now()->endOfDay();

        $dataInicial = $request->input('data_inicial') ? Carbon::parse($request->input('data_inicial'))->startOfDay() : $dataInicialPadrao;
        $dataFinal = $request->input('data_final') ? Carbon::parse($request->input('data_final'))->endOfDay() : $dataFinalPadrao;
        $tipoItem = $request->input('tipo_item'); // Ex: 'PECA_REPARO'
        $limiteResultados = $request->input('limite_resultados', 10);

        if ($dataInicial->gt($dataFinal)) {
            return back()->withErrors(['data_inicial' => 'A data inicial não pode ser maior que a data final.'])->withInput();
        }

        // Query para buscar as peças mais utilizadas
        // Focaremos em saídas que estão vinculadas a um atendimento (atendimento_id IS NOT NULL)
        // Se quiser incluir todas as saídas (mesmo as não vinculadas), remova ->whereNotNull('se.atendimento_id')
        $query = DB::table('saidas_estoque as se')
            ->join('estoque as e', 'se.estoque_id', '=', 'e.id')
            ->select(
                'e.id as estoque_id',
                'e.nome as nome_item',
                'e.modelo_compativel',
                'e.marca',
                'e.tipo as tipo_item_estoque',
                DB::raw('SUM(se.quantidade) as total_utilizado')
            )
            ->whereBetween('se.data_saida', [$dataInicial, $dataFinal])
            ->whereNotNull('se.atendimento_id'); // Considera apenas saídas vinculadas a atendimentos

        // Filtro opcional por tipo de item (do estoque)
        if ($tipoItem) {
            $query->where('e.tipo', $tipoItem);
        }

        $pecasMaisUtilizadas = $query->groupBy(
            'e.id',
            'e.nome',
            'e.modelo_compativel',
            'e.marca',
            'e.tipo'
        )
            ->orderByDesc('total_utilizado')
            ->orderBy('e.nome')
            ->limit($limiteResultados)
            ->get();

        $tiposDeItemParaFiltro = Estoque::select('tipo')
            ->whereNotNull('tipo')
            ->where('tipo', '!=', '')
            ->distinct()
            ->orderBy('tipo')
            ->pluck('tipo');

        return view('relatorios.pecas_mais_utilizadas', compact(
            'pecasMaisUtilizadas',
            'dataInicial',
            'dataFinal',
            'tipoItem',
            'limiteResultados',
            'tiposDeItemParaFiltro'
        ));
    }

    public function atendimentosPorStatus(Request $request)
    {
        // Datas padrão: últimos 30 dias
        $dataInicialPadrao = Carbon::now()->subDays(30)->startOfDay();
        $dataFinalPadrao = Carbon::now()->endOfDay();

        $dataInicial = $request->input('data_inicial') ? Carbon::parse($request->input('data_inicial'))->startOfDay() : $dataInicialPadrao;
        $dataFinal = $request->input('data_final') ? Carbon::parse($request->input('data_final'))->endOfDay() : $dataFinalPadrao;

        if ($dataInicial->gt($dataFinal)) {
            return back()->withErrors(['data_inicial' => 'A data inicial não pode ser maior que a data final.'])->withInput();
        }

        // Query para buscar a contagem de atendimentos por status dentro do período
        $atendimentosPorStatus = Atendimento::select('status', DB::raw('count(*) as total_atendimentos'))
            ->whereBetween('data_entrada', [$dataInicial, $dataFinal])
            ->groupBy('status')
            ->orderBy('status') // Ordena pelo nome do status
            ->get();

        // Lista de todos os status possíveis para garantir que todos apareçam, mesmo que com contagem 0
        // Baseado nos status que você tem no seu formulário de atendimento
        $todosOsStatus = [
            'Em diagnóstico',
            'Aguardando peça',
            'Em manutenção',
            'Pronto para entrega',
            'Entregue',
            // Adicione outros status se houver
        ];

        // Formata os dados para a view, garantindo que todos os status apareçam
        $statusComContagem = [];
        foreach ($todosOsStatus as $status) {
            $encontrado = $atendimentosPorStatus->firstWhere('status', $status);
            $statusComContagem[] = (object) [ // Converte para objeto para consistência com o resultado da query
                'status' => $status,
                'total_atendimentos' => $encontrado ? $encontrado->total_atendimentos : 0,
            ];
        }
        // Converte para coleção para facilitar o uso na view, se necessário, ou pode passar o array diretamente
        // $statusComContagem = collect($statusComContagem);


        // Opcional: buscar atendimentos de um status específico se clicado (para uma futura melhoria)
        $statusSelecionado = $request->input('ver_status');
        $atendimentosDoStatusSelecionado = null;
        if ($statusSelecionado) {
            $atendimentosDoStatusSelecionado = Atendimento::with('cliente', 'tecnico')
                ->where('status', $statusSelecionado)
                ->whereBetween('data_entrada', [$dataInicial, $dataFinal])
                ->orderBy('data_entrada', 'desc')
                ->paginate(10, ['*'], 'pagina_status'); // Nome da página para não conflitar com outra paginação
        }


        return view('relatorios.atendimentos_status', compact(
            'statusComContagem', // Alterado de atendimentosPorStatus
            'dataInicial',
            'dataFinal',
            'statusSelecionado',
            'atendimentosDoStatusSelecionado'
        ));
    }
    public function atendimentosPorTecnico(Request $request)
    {
        $dataInicialPadrao = Carbon::now()->startOfMonth()->startOfDay();
        $dataFinalPadrao = Carbon::now()->endOfDay();

        $dataInicial = $request->input('data_inicial') ? Carbon::parse($request->input('data_inicial'))->startOfDay() : $dataInicialPadrao;
        $dataFinal = $request->input('data_final') ? Carbon::parse($request->input('data_final'))->endOfDay() : $dataFinalPadrao;
        $tecnicoId = $request->input('tecnico_id');

        if ($dataInicial->gt($dataFinal)) {
            return back()->withErrors(['data_inicial' => 'A data inicial não pode ser maior que a data final.'])->withInput();
        }

        // Query base para atendimentos no período
        $queryAtendimentos = Atendimento::with('cliente', 'tecnico')
                                ->whereBetween('data_entrada', [$dataInicial, $dataFinal]);

        // Se um técnico específico foi selecionado, filtra por ele
        if ($tecnicoId) {
            $queryAtendimentos->where('tecnico_id', $tecnicoId);
        }

        // 1. Obter a contagem de atendimentos por técnico
        $atendimentosPorTecnico = (clone $queryAtendimentos) // Clonar para não afetar a query de listagem
                                    ->select('tecnico_id', DB::raw('count(*) as total_atendimentos'))
                                    ->groupBy('tecnico_id')
                                    ->get()
                                    ->mapWithKeys(function ($item) {
                                        // Carrega o nome do técnico. Se técnico_id for null, usa uma chave 'nao_atribuido'
                                        $tecnicoNome = $item->tecnico_id ? (User::find($item->tecnico_id)->name ?? 'Técnico Desconhecido') : 'Não Atribuído';
                                        return [$tecnicoNome => $item->total_atendimentos];
                                    });

        // 2. Obter a listagem detalhada de atendimentos (se um técnico foi selecionado ou para todos)
        //    Se nenhum técnico foi selecionado, pode-se optar por não mostrar a lista detalhada ou mostrar todos.
        //    Por ora, a lista detalhada aparecerá se um técnico for selecionado.
        $atendimentosDetalhados = null;
        if ($request->has('data_inicial')) { // Mostra detalhe apenas se o formulário foi submetido com datas
            if ($tecnicoId) { // E se um técnico foi selecionado para detalhamento
                 $atendimentosDetalhados = (clone $queryAtendimentos) // Já filtrado por tecnico_id se presente
                                            ->orderBy('data_entrada', 'desc')
                                            ->paginate(15, ['*'], 'pagina_detalhes');
            } elseif (!$tecnicoId && $request->input('mostrar_todos_detalhes') == '1') { // Mostrar todos se um checkbox for marcado (opcional)
                 $atendimentosDetalhados = (clone $queryAtendimentos)
                                            ->orderBy('data_entrada', 'desc')
                                            ->paginate(15, ['*'], 'pagina_detalhes');
            }
        }


        $tecnicos = User::where('tipo_usuario', 'tecnico')->orderBy('name')->get(); // Para o dropdown de filtro

        return view('relatorios.atendimentos_tecnico', compact(
            'atendimentosPorTecnico',
            'atendimentosDetalhados',
            'dataInicial',
            'dataFinal',
            'tecnicoId',
            'tecnicos'
        ));
    }
}
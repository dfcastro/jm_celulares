<?php

namespace App\Http\Controllers;

use App\Models\Estoque;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\EntradaEstoque; // Certifique-se de importar
use App\Models\SaidaEstoque;   // Certifique-se de importar
use Illuminate\Pagination\LengthAwarePaginator; // Importar para paginação manual de coleção
use Illuminate\Support\Collection; // Importar para manipular coleções
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EstoqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Estoque::query(); // Inicia a construção da query

        // Filtro de busca geral por termo (Nome, Modelo Compatível, Número de Série)
        if ($request->filled('busca')) { // Usar filled() para verificar se o campo não está vazio
            $searchTerm = $request->input('busca');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nome', 'like', '%' . $searchTerm . '%')
                    ->orWhere('modelo_compativel', 'like', '%' . $searchTerm . '%')
                    ->orWhere('numero_serie', 'like', '%' . $searchTerm . '%');
            });
        }

        // NOVO: Filtro por Tipo
        if ($request->filled('filtro_tipo')) {
            $query->where('tipo', $request->input('filtro_tipo'));
        }

        // NOVO: Filtro por Marca
        if ($request->filled('filtro_marca')) {
            $query->where('marca', 'like', '%' . $request->input('filtro_marca') . '%');
        }

        // Ordenar pelos mais recentes ou por nome, por exemplo
        $estoque = $query->orderBy('created_at', 'desc')->paginate(10);

        // Importante: Faz com que os links de paginação incluam os parâmetros de busca/filtro
        $estoque->appends($request->query());

        return view('estoque.index', compact('estoque'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Gate::denies('can-manage-basic-stock')) { // Ou 'is-internal-user'
            return redirect()->route('estoque.index')->with('error', 'Acesso não autorizado.');
        }
        return view('estoque.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Gate::denies('can-manage-basic-stock')) { // Ou 'is-internal-user'
            return redirect()->route('estoque.index')->with('error', 'Acesso não autorizado.');
        }
        $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('estoque')->where(function ($query) use ($request) {
                    return $query->where('nome', $request->nome)
                        ->where('modelo_compativel', $request->modelo_compativel);
                }),
            ],
            'tipo' => 'nullable|string|max:50', // Ex: Apenas permite string, ou 'required' se for obrigatório
            // 'tipo' => 'required|in:PECA_REPARO,ACESSORIO_VENDA,GERAL', // Se usar enum ou quiser validar os valores
            'marca' => 'nullable|string|max:100',
            'modelo_compativel' => 'nullable|string|max:255',
            'numero_serie' => 'nullable|string|max:100',
            'preco_custo' => 'nullable|numeric|min:0',
            'preco_venda' => 'nullable|numeric|min:0',
            'estoque_minimo' => 'nullable|integer|min:0',
        ]);

        $itemEstoque = Estoque::create($request->all()); // Supondo que você atribua o novo item a $itemEstoque
        return redirect()->route('estoque.index')
            ->with('success', "Item '{$itemEstoque->nome}' (ID: {$itemEstoque->id}) adicionado ao estoque! Lembre-se de registrar a entrada inicial.");

    }
    /**
     * Display the specified resource.
     */
    public function show(Estoque $estoque)
    {
        return view('estoque.show', compact('estoque')); // Para exibir detalhes da peça (opcional)
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Estoque $estoque)
    {
        if (Gate::denies('can-manage-basic-stock')) { // Ou 'is-internal-user'
            return redirect()->route('estoque.index')->with('error', 'Acesso não autorizado.');
        }
        return view('estoque.edit', compact('estoque'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Estoque $estoque)
    {

        // Permissão geral para editar o item de estoque (descrição, tipo, marca, etc. E AGORA PREÇOS)
        if (Gate::denies('can-manage-basic-stock')) { // Ou 'is-internal-user' se preferir
            return redirect()->route('estoque.index')->with('error', 'Acesso não autorizado para editar este item de estoque.');
        }
        // Validação dos dados do formulário
        // Validação básica dos campos que todos podem editar
        $validatedData = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('estoque')->where(function ($query) use ($request, $estoque) { // Adicionado $estoque aqui
                    return $query->where('nome', $request->nome)
                        ->where('modelo_compativel', $request->modelo_compativel);
                })->ignore($estoque->id),
            ],
            'tipo' => 'nullable|string|max:50',
            'marca' => 'nullable|string|max:150',
            'modelo_compativel' => 'nullable|string|max:255',
            'numero_serie' => 'nullable|string|max:100',
            'estoque_minimo' => 'nullable|integer|min:0',
            // Quantidade e preços são tratados separadamente ou com permissão mais restrita
        ]);

        // Permite que admin, tecnico, e atendente atualizem os dados validados acima
        if (Gate::denies('can-manage-basic-stock')) {
            return redirect()->route('estoque.index')->with('error', 'Acesso não autorizado para editar este item.');
        }

        // Atualiza apenas os campos permitidos para o perfil
        $estoque->fill($validatedData); // Preenche com os dados validados que todos podem mudar

        // Lógica para atualizar preços (SÓ ADMIN)
        if (Gate::allows('is-admin')) {
            if ($request->has('preco_custo')) { // Verifica se o campo foi enviado
                $request->validate(['preco_custo' => 'nullable|numeric|min:0']);
                $estoque->preco_custo = $request->preco_custo;
            }
            if ($request->has('preco_venda')) { // Verifica se o campo foi enviado
                $request->validate(['preco_venda' => 'nullable|numeric|min:0']);
                $estoque->preco_venda = $request->preco_venda;
            }
        }
        // Não permitir edição direta de quantidade aqui, deve ser via entradas/saídas
        // Se o campo 'quantidade' vier no request, ele será ignorado a menos que você o adicione explicitamente aqui
        // com uma verificação de permissão.

        $estoque->save();

        $estoque->save();
        return redirect()->route('estoque.index')
            ->with('success', "Item '{$estoque->nome}' (ID: {$estoque->id}) atualizado com sucesso!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Estoque $estoque)
    {
        if (Gate::denies('is-admin')) { // Ou Gate::allows('is-admin') e nega se for false
            return redirect()->route('estoque.index')->with('error', 'Apenas administradores podem excluir itens do estoque.');
        }
        try {
            // Verifica se existem entradas ou saídas de estoque relacionadas a esta peça
            if ($estoque->entradasEstoque()->exists() || $estoque->saidasEstoque()->exists()) {
                return redirect()->route('estoque.index')
                    ->with('error', 'Não é possível remover esta peça do estoque. Existem movimentações (entradas ou saídas) registradas para ela. Por favor, remova as movimentações primeiro.');
            }

            // Se não houver movimentações relacionadas, pode prosseguir com a exclusão
            $nomeItemExcluido = $estoque->nome;
            $itemIdExcluido = $estoque->id;
            $estoque->delete();
            return redirect()->route('estoque.index')
                ->with('success', "Item '{$nomeItemExcluido}' (ID: {$itemIdExcluido}) removido do estoque com sucesso!");

        } catch (\Illuminate\Database\QueryException $e) {
            // Captura a exceção de violação de integridade (Foreign Key constraint fails)
            // Embora o if acima já previna isso, é uma salvaguarda em caso de condição de corrida ou outros FKs.
            if ($e->getCode() == '23000') { // Código SQLSTATE para "Integrity constraint violation"
                return redirect()->route('estoque.index')
                    ->with('error', 'Erro de banco de dados: Não foi possível remover esta peça devido a registros vinculados. Por favor, remova as entradas e saídas de estoque associadas a esta peça primeiro.');
            }
            // Para outros tipos de erros de banco de dados, redireciona com a mensagem de erro
            return redirect()->route('estoque.index')
                ->with('error', 'Ocorreu um erro inesperado no banco de dados: ' . $e->getMessage());
        } catch (\Exception $e) {
            // Captura outras exceções genéricas que não sejam do banco de dados
            return redirect()->route('estoque.index')
                ->with('error', 'Ocorreu um erro inesperado: ' . $e->getMessage());
        }
    }
    public function historicoPeca(Request $request, Estoque $estoque) // Adicionado Request para filtros futuros
    {
        // Subquery para Entradas
        $entradasQuery = DB::table('entradas_estoque')
            ->select(
                DB::raw("'Entrada' as tipo_movimentacao_label"),
                'id as movimentacao_id', // Alias para padronizar
                'quantidade',
                'data_entrada as data_movimentacao',
                'observacoes',
                DB::raw("NULL as atendimento_id"), // Coluna para compatibilidade com saídas
                DB::raw("NULL as cliente_atendimento") // Coluna para compatibilidade
            )
            ->where('estoque_id', $estoque->id);

        // Subquery para Saídas
        $saidasQuery = DB::table('saidas_estoque')
            ->leftJoin('atendimentos', 'saidas_estoque.atendimento_id', '=', 'atendimentos.id')
            ->leftJoin('clientes', 'atendimentos.cliente_id', '=', 'clientes.id')
            ->select(
                DB::raw("'Saída' as tipo_movimentacao_label"),
                'saidas_estoque.id as movimentacao_id', // Alias
                'saidas_estoque.quantidade',
                'saidas_estoque.data_saida as data_movimentacao',
                'saidas_estoque.observacoes',
                'saidas_estoque.atendimento_id',
                'clientes.nome_completo as cliente_atendimento'
            )
            ->where('saidas_estoque.estoque_id', $estoque->id);

        // Aplicar filtros (opcional, mas recomendado para consistência)
        // Exemplo: Filtro por período
        if ($request->filled('data_inicial_hist') && $request->filled('data_final_hist')) {
            try {
                $dataInicial = Carbon::parse($request->input('data_inicial_hist'))->startOfDay();
                $dataFinal = Carbon::parse($request->input('data_final_hist'))->endOfDay();
                if ($dataInicial->lte($dataFinal)) {
                    $entradasQuery->whereBetween('data_entrada', [$dataInicial, $dataFinal]);
                    $saidasQuery->whereBetween('data_saida', [$dataInicial, $dataFinal]);
                }
            } catch (\Exception $e) { /* Lidar com datas inválidas se necessário */ }
        }

        // Unir as queries
        $movimentacoesQuery = $entradasQuery->unionAll($saidasQuery);

        // Ordenar o resultado da união e paginar
        // É preciso envolver a UNION em uma subquery para ordenar corretamente
        $movimentacoesPaginadas = DB::query()->fromSub($movimentacoesQuery, 'sub')
            ->orderBy('data_movimentacao', 'desc')
            ->paginate(15) // Ou o número de itens por página que desejar
            ->appends($request->query()); // Para manter os filtros na paginação

        return view('estoque.historico_peca', compact('estoque', 'movimentacoesPaginadas', 'request'));
    }

    /**
     * Busca peças de estoque para autocomplete.
     * Recebe 'search' na Request e retorna JSON.
     */
    public function autocomplete(Request $request)
    {
        $search = $request->get('search');
        $tiposFiltrar = $request->get('tipos_filtro');

        $query = Estoque::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }
                $q->orWhere('nome', 'LIKE', '%' . $search . '%')
                    ->orWhere('modelo_compativel', 'LIKE', '%' . $search . '%')
                    ->orWhere('numero_serie', 'LIKE', '%' . $search . '%');
            });
        }

        if (!empty($tiposFiltrar) && is_array($tiposFiltrar)) {
            $query->whereIn('tipo', $tiposFiltrar);
        }

        $itensEstoque = $query->limit(20)
            ->get(['id', 'nome', 'modelo_compativel', 'quantidade', 'preco_venda', 'tipo']); // Garanta que 'tipo' está aqui

        $formattedItems = $itensEstoque->map(function ($item) {
            $label = $item->nome . ($item->modelo_compativel ? ' (' . $item->modelo_compativel . ')' : '');
            $label .= ' - Qtd: ' . $item->quantidade;
            if ($item->tipo) {
                $tipoFormatado = '';
                if ($item->tipo == 'PECA_REPARO')
                    $tipoFormatado = 'Peça p/ Reparo';
                elseif ($item->tipo == 'ACESSORIO_VENDA')
                    $tipoFormatado = 'Acessório';
                elseif ($item->tipo == 'GERAL')
                    $tipoFormatado = 'Geral';
                else
                    $tipoFormatado = $item->tipo;
                $label .= ' - Tipo: ' . $tipoFormatado;
            }

            return [
                'label' => $label,
                'value' => $item->nome . ($item->modelo_compativel ? ' (' . $item->modelo_compativel . ')' : ''),
                'id' => $item->id,
                'preco_venda' => number_format($item->preco_venda ?? 0, 2, '.', ''),
                'quantidade_disponivel' => $item->quantidade,
                'tipo_formatado' => $tipoFormatado ?? ($item->tipo ?? 'N/D'), // <<< NOVO CAMPO para usar no JS
                'tipo_original' => $item->tipo // Mantém o tipo original se precisar
            ];
        });

        return response()->json($formattedItems);
    }

    /**
     * Exibe o histórico unificado de todas as movimentações (entradas e saídas).
     */
    public function historicoUnificado(Request $request)
    {
        $filtroDataInicial = $request->input('data_inicial_filtro');
        $filtroDataFinal = $request->input('data_final_filtro');
        $filtroTipoMovimentacao = $request->input('tipo_movimentacao_filtro');
        $filtroPecaId = $request->input('filtro_peca_id');
        $filtroObs = $request->input('busca_obs_filtro');

        // --- Subqueries para Entradas e Saídas com filtros aplicados ---
        $entradasQueryFiltrada = DB::table('entradas_estoque as mov')
            ->join('estoque as est', 'mov.estoque_id', '=', 'est.id')
            ->select(
                DB::raw("'Entrada' as tipo_movimentacao_label"),
                'mov.id as movimentacao_id',
                'mov.quantidade',
                'mov.data_entrada as data_movimentacao',
                'est.nome as nome_peca',
                'est.modelo_compativel as modelo_peca',
                'est.id as estoque_id_original',
                'mov.observacoes',
                DB::raw("NULL as atendimento_id"),
                DB::raw("NULL as cliente_atendimento")
            );

        $saidasQueryFiltrada = DB::table('saidas_estoque as mov')
            ->join('estoque as est', 'mov.estoque_id', '=', 'est.id')
            ->leftJoin('atendimentos as atend', 'mov.atendimento_id', '=', 'atend.id')
            ->leftJoin('clientes as cli', 'atend.cliente_id', '=', 'cli.id')
            ->select(
                DB::raw("'Saída' as tipo_movimentacao_label"),
                'mov.id as movimentacao_id',
                'mov.quantidade', // Quantidade é positiva, o tipo 'Saída' indica a natureza
                'mov.data_saida as data_movimentacao',
                'est.nome as nome_peca',
                'est.modelo_compativel as modelo_peca',
                'est.id as estoque_id_original',
                'mov.observacoes',
                'mov.atendimento_id',
                'cli.nome_completo as cliente_atendimento'
            );

        // Aplicar filtros comuns
        if ($filtroPecaId) {
            $entradasQueryFiltrada->where('mov.estoque_id', $filtroPecaId);
            $saidasQueryFiltrada->where('mov.estoque_id', $filtroPecaId);
        }
        if ($filtroDataInicial) {
            try {
                $dtInicio = Carbon::parse($filtroDataInicial)->startOfDay();
                $entradasQueryFiltrada->where('mov.data_entrada', '>=', $dtInicio);
                $saidasQueryFiltrada->where('mov.data_saida', '>=', $dtInicio);
            } catch (\Exception $e) {
            }
        }
        if ($filtroDataFinal) {
            try {
                $dtFim = Carbon::parse($filtroDataFinal)->endOfDay();
                $entradasQueryFiltrada->where('mov.data_entrada', '<=', $dtFim);
                $saidasQueryFiltrada->where('mov.data_saida', '<=', $dtFim);
            } catch (\Exception $e) {
            }
        }
        if ($filtroObs) {
            $entradasQueryFiltrada->where('mov.observacoes', 'like', '%' . $filtroObs . '%');
            $saidasQueryFiltrada->where('mov.observacoes', 'like', '%' . $filtroObs . '%');
        }

        // --- Cálculo dos Totalizadores ---
        $totalEntradasQuantidade = 0;
        $totalSaidasQuantidade = 0;

        if ($filtroTipoMovimentacao === 'Entrada' || empty($filtroTipoMovimentacao)) {
            // Clona a query de entradas ANTES de aplicar união para cálculo do total
            $totalEntradasQuantidade = (clone $entradasQueryFiltrada)->sum('mov.quantidade');
        }
        if ($filtroTipoMovimentacao === 'Saída' || empty($filtroTipoMovimentacao)) {
            // Clona a query de saídas ANTES de aplicar união para cálculo do total
            $totalSaidasQuantidade = (clone $saidasQueryFiltrada)->sum('mov.quantidade');
        }

        // Se o filtro for específico, o outro total será 0
        if ($filtroTipoMovimentacao === 'Entrada') {
            $totalSaidasQuantidade = 0;
        } elseif ($filtroTipoMovimentacao === 'Saída') {
            $totalEntradasQuantidade = 0;
        }

        $saldoMovimentacoes = $totalEntradasQuantidade - $totalSaidasQuantidade;

        // --- Construção da Query para Paginação ---
        if ($filtroTipoMovimentacao === 'Entrada') {
            $movimentacoesQueryPaginacao = $entradasQueryFiltrada;
        } elseif ($filtroTipoMovimentacao === 'Saída') {
            $movimentacoesQueryPaginacao = $saidasQueryFiltrada;
        } else {
            $movimentacoesQueryPaginacao = $entradasQueryFiltrada->unionAll($saidasQueryFiltrada);
        }

        // Para ordenar a query combinada, precisamos envolvê-la se estivermos usando UNION
        // Se não for UNION (filtro de tipo específico), podemos ordenar diretamente.
        if ($filtroTipoMovimentacao === 'Entrada' || $filtroTipoMovimentacao === 'Saída') {
            $movimentacoesPaginadas = $movimentacoesQueryPaginacao->orderBy('data_movimentacao', 'desc')->paginate(15)->appends($request->query());
        } else { // Caso de UNION ALL (Todas as movimentações)
            // Envolver a UNION em uma subquery para permitir ordenação no resultado combinado
            $queryBuilderOrdenada = DB::query()->fromSub($movimentacoesQueryPaginacao, 'sub')
                ->orderBy('data_movimentacao', 'desc');
            $movimentacoesPaginadas = $queryBuilderOrdenada->paginate(15)->appends($request->query());
        }


        $pecaSelecionadaNomeFiltro = null;
        if ($filtroPecaId) {
            $peca = Estoque::find($filtroPecaId);
            if ($peca) {
                $pecaSelecionadaNomeFiltro = $peca->nome . ($peca->modelo_compativel ? ' (' . $peca->modelo_compativel . ')' : '');
            }
        }

        return view('estoque.historico_unificado', compact(
            'movimentacoesPaginadas',
            'pecaSelecionadaNomeFiltro',
            'request', // Passa o objeto request para fácil acesso aos filtros na view
            'totalEntradasQuantidade',
            'totalSaidasQuantidade',
            'saldoMovimentacoes'
        ));
    }

}
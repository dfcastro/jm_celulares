<?php

namespace App\Http\Controllers;

use App\Models\Estoque;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $query = Estoque::query();

        // Implementar busca global por termo (Nome, Modelo Compatível, Número de Série)
        if ($request->has('busca') && !empty($request->input('busca'))) {
            $searchTerm = $request->input('busca');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nome', 'like', '%' . $searchTerm . '%')
                  ->orWhere('modelo_compativel', 'like', '%' . $searchTerm . '%')
                  ->orWhere('numero_serie', 'like', '%' . $searchTerm . '%');
            });
        }

        // Adicionar paginação
        // Você pode definir um número de itens por página, por exemplo, 10.
        // Se precisar de um número diferente, mude o '10' aqui.
        $estoque = $query->paginate(10);

        // Isso garante que os links de paginação incluam os parâmetros de busca
        $estoque->appends($request->query());

        return view('estoque.index', compact('estoque'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('estoque.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validação dos dados do formulário
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
            // REMOVA A REGRA 'quantidade' => 'required|integer|min:0',
            'preco_custo' => 'nullable|numeric|min:0',
            'preco_venda' => 'nullable|numeric|min:0',
            'estoque_minimo' => 'nullable|integer|min:0',
            // Mantenha a validação para 'modelo_compativel' se existir
            'modelo_compativel' => 'nullable|string|max:255', // Adicionei essa linha com base na sua migração
        ]);

        // Use create, o banco de dados cuidará do default(0) para 'quantidade'
        // Se você não adicionou default(0) na migração, pode adicionar 'quantidade' => 0
        // ao array antes de criar, mas o default na migração é mais robusto.
        Estoque::create($request->all());

        return redirect()->route('estoque.index')->with('success', 'Peça adicionada ao estoque com sucesso! Lembre-se de registrar uma entrada para adicionar a quantidade inicial.'); // Mensagem informativa
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
        return view('estoque.edit', compact('estoque'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Estoque $estoque)
    {
        // Validação dos dados do formulário
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
            'quantidade' => 'required|integer|min:0',
            'preco_custo' => 'nullable|numeric|min:0',
            'preco_venda' => 'nullable|numeric|min:0',
            'estoque_minimo' => 'nullable|integer|min:0',
        ]);

        $estoque->update($request->all());

        return redirect()->route('estoque.index')->with('success', 'Peça do estoque atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Estoque $estoque)
    {
        try {
            // Verifica se existem entradas ou saídas de estoque relacionadas a esta peça
            if ($estoque->entradasEstoque()->exists() || $estoque->saidasEstoque()->exists()) {
                return redirect()->route('estoque.index')
                    ->with('error', 'Não é possível remover esta peça do estoque. Existem movimentações (entradas ou saídas) registradas para ela. Por favor, remova as movimentações primeiro.');
            }

            // Se não houver movimentações relacionadas, pode prosseguir com a exclusão
            $estoque->delete();
            return redirect()->route('estoque.index')->with('success', 'Peça removida do estoque com sucesso!');

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
    public function historicoPeca(Estoque $estoque)
    {
        // Buscar todas as entradas relacionadas a esta peça
        $entradas = $estoque->entradasEstoque()->orderBy('data_entrada', 'desc')->get();

        // Buscar todas as saídas relacionadas a esta peça
        $saidas = $estoque->saidasEstoque()->with('atendimento')->orderBy('data_saida', 'desc')->get();

        // Vamos juntar as entradas e saídas para mostrar em ordem cronológica
        // Primeiro, formatamos cada uma para incluir o tipo e data/hora
        $movimentacoes = $entradas->map(function ($entrada) {
            return [
                'tipo' => 'Entrada',
                'quantidade' => $entrada->quantidade,
                'data' => $entrada->data_entrada, // Carbon instance
                'observacoes' => $entrada->observacoes,
                'relacionado' => null, // Entradas não estão diretamente ligadas a atendimentos na sua estrutura
            ];
        })->concat($saidas->map(function ($saida) {
            return [
                'tipo' => 'Saída',
                'quantidade' => $saida->quantidade,
                'data' => $saida->data_saida, // Carbon instance
                'observacoes' => $saida->observacoes,
                'relacionado' => $saida->atendimento ? 'Atendimento #' . $saida->atendimento->id : 'Não Vinculado',
            ];
        }));

        // Ordenar todas as movimentações por data (mais recente primeiro)
        $movimentacoes = $movimentacoes->sortByDesc('data');


        // Passa a peça e as movimentações para a view
        return view('estoque.historico_peca', compact('estoque', 'movimentacoes'));
    }

    /**
     * Busca peças de estoque para autocomplete.
     * Recebe 'search' na Request e retorna JSON.
     */
    public function autocomplete(Request $request)
    {
        $search = $request->get('search'); // Obtém o termo de busca da requisição

        // Busca peças cujo nome ou modelo compatível contenham o termo de busca
        $itensEstoque = Estoque::where('nome', 'LIKE', '%' . $search . '%')
            ->orWhere('modelo_compativel', 'LIKE', '%' . $search . '%')
            ->limit(20) // Limita o número de resultados para não sobrecarregar
            ->get(['id', 'nome', 'modelo_compativel', 'quantidade', 'preco_venda']); // Seleciona apenas os campos necessários

        // Retorna a lista de itens em formato JSON
        return response()->json($itensEstoque->toArray());
    }

    /**
     * Exibe o histórico unificado de todas as movimentações (entradas e saídas).
     */
    public function historicoUnificado()
    {
        // Busca todas as entradas, ordenadas pela data de entrada (mais recente primeiro)
        $entradas = \App\Models\EntradaEstoque::with('estoque')
            ->orderBy('data_entrada', 'desc')
            ->get();

        // Busca todas as saídas, ordenadas pela data de saída (mais recente primeiro)
        // Carregamos o relacionamento com Atendimento para exibir informações se houver
        $saidas = \App\Models\SaidaEstoque::with('estoque', 'atendimento')
            ->orderBy('data_saida', 'desc')
            ->get();

        // Mapeia as entradas para um formato comum
        $movimentacoesEntrada = $entradas->map(function ($entrada) {
            return [
                'tipo' => 'Entrada',
                'quantidade' => $entrada->quantidade,
                'data' => $entrada->data_entrada, // Já é um objeto Carbon devido ao $casts no model
                'peca' => $entrada->estoque->nome . ' (' . ($entrada->estoque->modelo_compativel ?? 'N/A') . ')',
                'observacoes' => $entrada->observacoes,
                'relacionado' => null, // Entradas não são diretamente ligadas a atendimentos
            ];
        });

        // Mapeia as saídas para um formato comum
        $movimentacoesSaida = $saidas->map(function ($saida) {
            return [
                'tipo' => 'Saída',
                'quantidade' => $saida->quantidade,
                'data' => $saida->data_saida, // Já é um objeto Carbon
                'peca' => $saida->estoque->nome . ' (' . ($saida->estoque->modelo_compativel ?? 'N/A') . ')',
                'observacoes' => $saida->observacoes,
                'relacionado' => $saida->atendimento ? 'Atendimento #' . $saida->atendimento->id : 'Não Vinculado',
            ];
        });

        // Combina as duas coleções de movimentações
        $movimentacoes = $movimentacoesEntrada->concat($movimentacoesSaida);

        // Ordena a coleção combinada pela data (mais recente primeiro)
        $movimentacoes = $movimentacoes->sortByDesc('data');

        // Retorna a view com os dados das movimentações
        return view('estoque.historico_unificado', compact('movimentacoes'));
    }
}
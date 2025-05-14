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

        Estoque::create($request->all());

        return redirect()->route('estoque.index')->with('success', 'Peça adicionada ao estoque com sucesso! Lembre-se de registrar uma entrada para adicionar a quantidade inicial.');
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

        return redirect()->route('estoque.index')->with('success', 'Peça do estoque atualizada com sucesso!');
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
        $search = $request->get('search');
        $tiposFiltrar = $request->get('tipos_filtro'); // Novo: Pega o parâmetro de filtro de tipos

        $query = Estoque::query();

        // Aplica o filtro de busca por nome/modelo/id
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

        // NOVO: Aplica o filtro por tipo, se fornecido
        if (!empty($tiposFiltrar) && is_array($tiposFiltrar)) {
            $query->whereIn('tipo', $tiposFiltrar);
        }

        // Opcional: Adicionar um filtro para mostrar apenas itens com quantidade > 0,
        // especialmente se este autocomplete for usado em contextos de saída/venda.
        // $query->where('quantidade', '>', 0);

        $itensEstoque = $query->limit(20)
            ->get(['id', 'nome', 'modelo_compativel', 'quantidade', 'preco_venda', 'tipo']); // Adicionado 'tipo'

        $formattedItems = $itensEstoque->map(function ($item) {
            $label = $item->nome . ' (' . ($item->modelo_compativel ?? 'N/A') . ')';
            $label .= ' - Qtd: ' . $item->quantidade;
            // Opcional: Adicionar o tipo ao label se desejar
            // if ($item->tipo) {
            //     $label .= ' - Tipo: ' . $item->tipo;
            // }

            return [
                'label' => $label,
                'value' => $item->nome . ' (' . ($item->modelo_compativel ?? 'N/A') . ')',
                'id' => $item->id,
                'preco_venda' => number_format($item->preco_venda ?? 0, 2, '.', ''),
                'quantidade_disponivel' => $item->quantidade,
            ];
        });

        return response()->json($formattedItems);
    }

    /**
     * Exibe o histórico unificado de todas as movimentações (entradas e saídas).
     */
    public function historicoUnificado(Request $request) // Receber Request para o parâmetro de paginação
    {
        // Busca todas as entradas
        $entradas = EntradaEstoque::with('estoque')
            ->get() // Coleta todas as entradas
            ->map(function ($entrada) {
                return [
                    'tipo' => 'Entrada',
                    'quantidade' => $entrada->quantidade,
                    'data' => $entrada->data_entrada, // Já é um objeto Carbon
                    'peca' => $entrada->estoque->nome . ' (' . ($entrada->estoque->modelo_compativel ?? 'N/A') . ')',
                    'observacoes' => $entrada->observacoes,
                    'relacionado' => null, // Entradas não são diretamente ligadas a atendimentos
                ];
            });

        // Busca todas as saídas
        $saidas = SaidaEstoque::with('estoque', 'atendimento')
            ->get() // Coleta todas as saídas
            ->map(function ($saida) {
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
        $movimentacoes = $entradas->concat($saidas);

        // Ordena a coleção combinada pela data (mais recente primeiro)
        $movimentacoes = $movimentacoes->sortByDesc('data');

        // Agora, fazemos a paginação manual da coleção combinada
        $perPage = 10; // Quantidade de itens por página
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $movimentacoes->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $movimentacoesPaginadas = new LengthAwarePaginator(
            $currentItems,
            $movimentacoes->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Retorna a view com os dados das movimentações paginadas
        return view('estoque.historico_unificado', compact('movimentacoesPaginadas')); // Mudamos o nome da variável
    }

}
<?php

namespace App\Http\Controllers;

use App\Models\Estoque;
use Illuminate\Http\Request;

class EstoqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Estoque::query();

        if ($request->has('modelo')) {
            $modelo = $request->input('modelo');
            $query->where('modelo_compativel', 'like', '%' . $modelo . '%');
        }

        $estoque = $query->get();

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
            'nome' => 'required|string|unique:estoque,nome|max:255',
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
            'nome' => 'required|string|max:255|unique:estoque,nome,' . $estoque->id,
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
        $estoque->delete();
        return redirect()->route('estoque.index')->with('success', 'Peça removida do estoque com sucesso!');
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
}
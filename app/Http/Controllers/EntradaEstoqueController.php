<?php

namespace App\Http\Controllers;

use App\Models\EntradaEstoque;
use App\Models\Estoque;
use Illuminate\Http\Request;

class EntradaEstoqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $entradas = EntradaEstoque::with('estoque')->latest()->paginate(10);
        return view('entradas_estoque.index', compact('entradas')); // <- Corrigir aqui também
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $estoques = Estoque::all();
        // Mude de 'entradas-estoque.create' para 'entradas_estoque.create'
        return view('entradas_estoque.create', compact('estoques'));
    }
    /**
     * Store a newly created resource in storage.
     */
    // app\Http\Controllers\EntradaEstoqueController.php - Método store
public function store(Request $request)
{
    $request->validate([
        'estoque_id' => 'required|exists:estoque,id',
        'quantidade' => 'required|integer|min:1', // <-- Esta linha já exige que seja um número >= 1
        'data_entrada' => 'required|date',
        'observacoes' => 'nullable|string|max:255',
    ]);

    // Aqui você cria a entrada. Como 'quantidade' é 'required' e 'integer'
    // na validação, $request->quantidade já deve ser um número válido.
    EntradaEstoque::create($request->all());

    $estoque = Estoque::findOrFail($request->estoque_id);
    $estoque->increment('quantidade', $request->quantidade); // $request->quantidade deve ser numérico aqui

    return redirect()->route('entradas-estoque.index')->with('success', 'Entrada de estoque registrada com sucesso!');
}

    /**
     * Display the specified resource.
     */
    public function show($id)
{
    // Buscamos a EntradaEstoque pelo ID, carregando o relacionamento estoque
    $entradaEstoque = EntradaEstoque::with('estoque')->findOrFail($id);

    // Confirme que o nome da view está com underscore "_"
    return view('entradas_estoque.show', compact('entradaEstoque'));
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Buscamos a EntradaEstoque pelo ID. findOrFail irá retornar 404 se não encontrar.
        $entradaEstoque = EntradaEstoque::findOrFail($id);

        // Carregamos os estoques para o dropdown
        $estoques = Estoque::all();

        // O load('estoque') não é estritamente necessário para a view de edição funcionar,
        // pois o relacionamento será carregado sob demanda (lazy loading) se você o usar.
        // Mas se quiser carregar antecipadamente, pode fazer assim:
        // $entradaEstoque = EntradaEstoque::with('estoque')->findOrFail($id);

        // Retornamos a view, passando o objeto $entradaEstoque (agora carregado) e os estoques
        return view('entradas_estoque.edit', compact('entradaEstoque', 'estoques'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EntradaEstoque $entradaEstoque)
    {
        $request->validate([
            'estoque_id' => 'required|exists:estoque,id',
            'quantidade' => 'required|integer|min:1',
            'data_entrada' => 'required|date',
            'observacoes' => 'nullable|string|max:255',
        ]);

        // Captura a quantidade ANTERIOR, garantindo que seja um número (0 se for null)
        $quantidadeAnterior = (int) $entradaEstoque->quantidade;

        // Atualiza a entrada de estoque com os novos dados do request
        $entradaEstoque->update($request->all());

        // Captura a NOVA quantidade do request, garantindo que seja um número
        $quantidadeNova = (int) $request->quantidade; // Cast adicional para segurança, embora a validação 'integer' já ajude

        // Encontra a peça de estoque relacionada
        $estoque = Estoque::findOrFail($request->estoque_id);

        // Ajuste a quantidade no estoque principal:
        // 1. Subtraia a quantidade ANTERIOR (desfaz o impacto da entrada original)
        $estoque->decrement('quantidade', $quantidadeAnterior);

        // 2. Adicione a quantidade NOVA (aplica o impacto da entrada atualizada)
        $estoque->increment('quantidade', $quantidadeNova);


        return redirect()->route('entradas-estoque.index')->with('success', 'Entrada de estoque atualizada com sucesso!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EntradaEstoque $entradaEstoque)
    {
        $estoque = Estoque::findOrFail($entradaEstoque->estoque_id);
        $estoque->decrement('quantidade', $entradaEstoque->quantidade);
        $entradaEstoque->delete();

        return redirect()->route('entradas-estoque.index')->with('success', 'Entrada de estoque excluída com sucesso!');
    }
}
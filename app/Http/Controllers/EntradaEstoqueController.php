<?php

namespace App\Http\Controllers;

use App\Models\EntradaEstoque;
use App\Models\Estoque;
use Illuminate\Http\Request;

use Carbon\Carbon;

class EntradaEstoqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $entradas = EntradaEstoque::with('estoque')->latest()->paginate(10);
        return view('entradas_estoque.index', compact('entradas')); 
    }

    /**
     * Show the form for creating a new resource.
     */
    // Aceita um parâmetro opcional 'estoque_id' vindo da URL
    public function create(Request $request)
    {
        $estoques = Estoque::all();
        // Pega o estoque_id da request, se existir. Usa null por padrão.
        $selectedEstoqueId = $request->input('estoque_id');

        // Passa $selectedEstoqueId para a view
        return view('entradas_estoque.create', compact('estoques', 'selectedEstoqueId'));
    }
    /**
     * Store a newly created resource in storage.
     */
    // app\Http\Controllers\EntradaEstoqueController.php - Método store
    public function store(Request $request)
    {
        $request->validate([
            'estoque_id' => 'required|exists:estoque,id',
            'quantidade' => 'required|integer|min:1',
            'data_entrada' => 'required|date', // Apenas validação de data, sem hora
            'observacoes' => 'nullable|string|max:255',
        ]);

        $estoque = Estoque::findOrFail($request->estoque_id);

        // --- MODIFICADO: Combinar a data selecionada com a hora atual ---
        $dataEntradaFormulario = $request->input('data_entrada');
        $dataCompleta = Carbon::parse($dataEntradaFormulario)
                              ->setTime(Carbon::now()->hour, Carbon::now()->minute, Carbon::now()->second);

        $dadosEntrada = $request->except('data_entrada');
        $dadosEntrada['data_entrada'] = $dataCompleta;

        EntradaEstoque::create($dadosEntrada);

        $estoque->increment('quantidade', $request->quantidade);

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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Buscamos a EntradaEstoque pelo ID. findOrFail irá retornar 404 se não encontrar.
        $entradaEstoque = EntradaEstoque::findOrFail($id); // findOrFail é importante para lançar 404 se o ID não existir

        // Precisamos do objeto $entradaEstoque carregado para acessar estoque_id e quantidade
        $estoque = Estoque::findOrFail($entradaEstoque->estoque_id);

        // Antes de decrementar, garantimos que a quantidade é numérica (caso haja algum registro antigo com null)
        $cantidadParaDecrement = (int) $entradaEstoque->quantidade;

        // Ajusta a quantidade no estoque principal (decrementa ao excluir uma entrada)
        $estoque->decrement('quantidade', $cantidadParaDecrement);

        // Exclui a entrada de estoque
        $entradaEstoque->delete();

        return redirect()->route('entradas-estoque.index')->with('success', 'Entrada de estoque excluída com sucesso!');
    }
}
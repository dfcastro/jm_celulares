<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\Estoque;
use App\Models\SaidaEstoque;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


class SaidaEstoqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Versão limpa do método index
        $saidas = SaidaEstoque::with('estoque', 'atendimento')->latest()->paginate(10);

        return view('saidas_estoque.index', compact('saidas'));
    }


    /**
     * Show the form for creating a new resource.
     */
    // Aceita um parâmetro opcional 'estoque_id' vindo da URL
    public function create(Request $request)
    {
        $estoques = Estoque::where('quantidade', '>', 0)->get(); // Mantém a condição de > 0 se aplicável
        $atendimentos = Atendimento::all();
        // Pega o estoque_id da request, se existir. Usa null por padrão.
        $selectedEstoqueId = $request->input('estoque_id');

        // Passa $selectedEstoqueId para a view
        return view('saidas_estoque.create', compact('estoques', 'atendimentos', 'selectedEstoqueId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         // 1. Buscar a peça de estoque selecionada
         $estoque = Estoque::find($request->estoque_id);

         // Verificamos se a peça foi encontrada (embora 'exists' na validação já garanta isso)
         if (!$estoque) {
              // Isso não deve acontecer se a validação 'exists' funcionar, mas é uma salvaguarda
              throw ValidationException::withMessages([
                 'estoque_id' => 'A peça selecionada não foi encontrada.',
              ]);
         }
 
         // 2. Comparar a quantidade solicitada com a quantidade em estoque
         // Garantimos que a quantidade em estoque é tratada como número
         $quantidadeEmEstoque = (int) $estoque->quantidade;
         $quantidadeSolicitada = (int) $request->quantidade;
 
         if ($quantidadeSolicitada > $quantidadeEmEstoque) {
             // Se a quantidade solicitada for maior que a disponível, lançamos um erro de validação
             throw ValidationException::withMessages([
                 'quantidade' => 'A quantidade solicitada (' . $quantidadeSolicitada . ') excede a quantidade disponível em estoque (' . $quantidadeEmEstoque . ').',
             ]);
         }
 
         // --- FIM NOVA VALIDAÇÃO DE ESTOQUE ---
 
 
         // Se a validação de estoque passou, podemos prosseguir para criar a saída
         SaidaEstoque::create($request->all());
 
         // Decrementar a quantidade no estoque principal
         // Usamos $quantidadeSolicitada que já foi validada e tratada como int
         $estoque->decrement('quantidade', $quantidadeSolicitada);
 
         return redirect()->route('saidas-estoque.index')->with('success', 'Saída de estoque registrada com sucesso!');
     
    }

    /**
     * Display the specified resource.
     */
    public function show(SaidaEstoque $saidaEstoque)
    {
        $saidaEstoque->load('estoque', 'atendimento');
        return view('saidas_estoque.show', compact('saidaEstoque'));
    }

    /**
     * Show the form for editing the new resource.
     */
   

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Buscamos a SaidaEstoque pelo ID. findOrFail irá retornar 404 se não encontrar.
        $saidaEstoque = SaidaEstoque::findOrFail($id); // findOrFail é importante para lançar 404 se o ID não existir

        // Precisamos do objeto $saidaEstoque carregado para acessar estoque_id e quantidade
        $estoque = Estoque::findOrFail($saidaEstoque->estoque_id);

        // Antes de incrementar, garantimos que a quantidade é numérica
        $cantidadParaIncrement = (int) $saidaEstoque->quantidade;

        // Ajusta a quantidade no estoque principal (incrementa ao excluir uma saída)
        $estoque->increment('quantidade', $cantidadParaIncrement);

        // Exclui a saída de estoque
        $saidaEstoque->delete();

        return redirect()->route('saidas-estoque.index')->with('success', 'Saída de estoque excluída com sucesso!');
    }
}
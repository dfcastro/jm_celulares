<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\Estoque;
use App\Models\SaidaEstoque;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;


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
 /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 0. Validação inicial dos campos do formulário
        $request->validate([
            'estoque_id' => 'required|exists:estoque,id',
            'atendimento_id' => 'nullable|exists:atendimentos,id',
            'quantidade' => 'required|integer|min:1',
            'data_saida' => 'required|date', // Apenas validação de data, sem hora
            'observacoes' => 'nullable|string|max:255',
        ]);

        // 1. Buscar a peça de estoque selecionada
        $estoque = Estoque::find($request->estoque_id);

        if (!$estoque) {
            throw ValidationException::withMessages([
                'estoque_id' => 'A peça selecionada não foi encontrada.',
            ]);
        }

        // 2. Comparar a quantidade solicitada com a quantidade em estoque
        $quantidadeEmEstoque = (int) $estoque->quantidade;
        $quantidadeSolicitada = (int) $request->quantidade;

        if ($quantidadeSolicitada > $quantidadeEmEstoque) {
            throw ValidationException::withMessages([
                'quantidade' => 'A quantidade solicitada (' . $quantidadeSolicitada . ') excede a quantidade disponível em estoque (' . $quantidadeEmEstoque . ').',
            ]);
        }

        // --- MODIFICADO: Combinar a data selecionada com a hora atual ---
        // Pega a data do formulário (ex: "2025-05-10")
        $dataSaidaFormulario = $request->input('data_saida');

        // Cria um objeto Carbon a partir da data do formulário e define a hora para a hora atual
        $dataCompleta = Carbon::parse($dataSaidaFormulario)
                              ->setTime(Carbon::now()->hour, Carbon::now()->minute, Carbon::now()->second);

        // Pega todos os outros dados da requisição
        $dadosSaida = $request->except('data_saida'); // Remove data_saida da requisição original

        // Adiciona a data_saida completa ao array de dados
        $dadosSaida['data_saida'] = $dataCompleta;

        // 3. Criar a saída de estoque com a data e hora ajustadas
        SaidaEstoque::create($dadosSaida); // Usa o array $dadosSaida com a data e hora completas

        // Decrementar a quantidade no estoque principal
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
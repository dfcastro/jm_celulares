<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\Estoque;
use App\Models\SaidaEstoque;
use Illuminate\Http\Request;

class SaidaEstoqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $saidas = SaidaEstoque::with('estoque', 'atendimento')->latest()->paginate(10);
        return view('saidas_estoque.index', compact('saidas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $estoques = Estoque::where('quantidade', '>', 0)->get();
        $atendimentos = Atendimento::all();
        return view('saidas_estoque.create', compact('estoques', 'atendimentos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'estoque_id' => 'required|exists:estoque,id',
            'atendimento_id' => 'nullable|exists:atendimentos,id',
            'quantidade' => 'required|integer|min:1',
            'data_saida' => 'required|date',
            'observacoes' => 'nullable|string|max:255',
        ]);

        $estoque = Estoque::findOrFail($request->estoque_id);

        if ($estoque->quantidade < $request->quantidade) {
            return back()->withErrors(['quantidade' => 'Quantidade indisponível no estoque.'])->withInput();
        }

        SaidaEstoque::create($request->all());

        $estoque->decrement('quantidade', $request->quantidade);

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
    public function edit(SaidaEstoque $saidaEstoque)
    {
        $estoques = Estoque::all();
        $atendimentos = Atendimento::all();
        $saidaEstoque->load('estoque', 'atendimento');
        return view('saidas_estoque.edit', compact('saidaEstoque', 'estoques', 'atendimentos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SaidaEstoque $saidaEstoque)
    {
        $request->validate([
            'estoque_id' => 'required|exists:estoque,id',
            'atendimento_id' => 'nullable|exists:atendimentos,id',
            'quantidade' => 'required|integer|min:1',
            'data_saida' => 'required|date',
            'observacoes' => 'nullable|string|max:255',
        ]);

        $quantidadeAnterior = $saidaEstoque->quantidade;
        $saidaEstoque->update($request->all());

        $estoque = Estoque::findOrFail($request->estoque_id);
        $estoque->increment('quantidade', $quantidadeAnterior);
        $estoque->decrement('quantidade', $request->quantidade);

        return redirect()->route('saidas-estoque.index')->with('success', 'Saída de estoque atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SaidaEstoque $saidaEstoque)
    {
        $estoque = Estoque::findOrFail($saidaEstoque->estoque_id);
        $estoque->increment('quantidade', $saidaEstoque->quantidade);
        $saidaEstoque->delete();

        return redirect()->route('saidas-estoque.index')->with('success', 'Saída de estoque excluída com sucesso!');
    }
}
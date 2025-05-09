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
        return view('entradas_estoque.index', compact('entradas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $estoques = Estoque::all();
        return view('entradas-estoque.create', compact('estoques'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'estoque_id' => 'required|exists:estoque,id',
            'quantidade' => 'required|integer|min:1',
            'data_entrada' => 'required|date',
            'observacoes' => 'nullable|string|max:255',
        ]);

        EntradaEstoque::create($request->all());

        $estoque = Estoque::findOrFail($request->estoque_id);
        $estoque->increment('quantidade', $request->quantidade);

        return redirect()->route('entradas-estoque.index')->with('success', 'Entrada de estoque registrada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(EntradaEstoque $entradaEstoque)
    {
        $entradaEstoque = EntradaEstoque::with('estoque')->findOrFail($entradaEstoque->id);
        return view('entradas_estoque.show', compact('entradaEstoque'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EntradaEstoque $entradaEstoque)
    {
        $estoques = Estoque::all();
        $entradaEstoque->load('estoque');
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

        $quantidadeAnterior = $entradaEstoque->quantidade;
        $entradaEstoque->update($request->all());

        $estoque = Estoque::findOrFail($request->estoque_id);
        $estoque->decrement('quantidade', $quantidadeAnterior);
        $estoque->increment('quantidade', $request->quantidade);

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
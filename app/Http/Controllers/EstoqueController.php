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
            'quantidade' => 'required|integer|min:0',
            'preco_custo' => 'nullable|numeric|min:0',
            'preco_venda' => 'nullable|numeric|min:0',
            'estoque_minimo' => 'nullable|integer|min:0',
        ]);

        Estoque::create($request->all());

        return redirect()->route('estoque.index')->with('success', 'Peça adicionada ao estoque com sucesso!');
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
}
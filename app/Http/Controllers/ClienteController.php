<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientes = Cliente::all(); // Buscar todos os clientes no banco de dados
        return view('clientes.index', compact('clientes')); // Retornar uma view com os clientes
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clientes.create'); // Retornar o formulário de cadastro
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Cliente::create($request->all()); // Criar um novo cliente com os dados do formulário
        return redirect()->route('clientes.index')->with('success', 'Cliente cadastrado com sucesso!'); // Redirecionar e exibir mensagem
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        return view('clientes.show', compact('cliente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente')); // Retornar o formulário de edição com os dados do cliente
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        $cliente->update($request->all()); // Atualizar os dados do cliente
        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado com sucesso!'); // Redirecionar e exibir mensagem
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        $cliente->delete(); // Excluir o cliente
        return redirect()->route('clientes.index')->with('success', 'Cliente excluído com sucesso!'); // Redirecionar e exibir mensagem
    }

    public function autocomplete(Request $request)
    {
        $search = $request->get('search');
        $clientes = Cliente::where('nome_completo', 'LIKE', '%' . $search . '%')
                           ->orWhere('cpf_cnpj', 'LIKE', '%' . $search . '%')
                           ->limit(10)
                           ->get(['id', 'nome_completo', 'cpf_cnpj', 'telefone']); // Selecionando também o telefone
    
        return response()->json($clientes->toArray());
    }

}
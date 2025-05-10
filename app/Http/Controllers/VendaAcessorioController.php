<?php

namespace App\Http\Controllers;

use App\Models\VendaAcessorio; // Importar o modelo VendaAcessorio
use App\Models\Estoque; // Precisaremos para o formulário de criação
use App\Models\Cliente; // Precisaremos para o formulário de criação
use Illuminate\Http\Request;

class VendaAcessorioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Buscar todas as vendas de acessórios, talvez com paginação
        // Carregamos o relacionamento 'cliente' para exibição na lista
        $vendas = VendaAcessorio::with('cliente')->latest()->paginate(10);

        // Retornar a view de listagem, passando as vendas
        return view('vendas_acessorios.index', compact('vendas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Buscar dados necessários para o formulário de criação (clientes e itens de estoque)
        $clientes = Cliente::all(); // Para o dropdown de cliente (opcional)
        $itensEstoque = Estoque::orderBy('nome')->get(); // Para a lista de itens a serem vendidos

        // Passar um estoque_id opcional se vier da URL (ex: clicando em 'Registrar Venda' na lista de peças)
        $selectedEstoqueId = $request->input('estoque_id');


        // Retornar a view do formulário de criação, passando os dados
        return view('vendas_acessorios.create', compact('clientes', 'itensEstoque', 'selectedEstoqueId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Lógica para validar os dados do formulário,
        // salvar a venda em 'vendas_acessorios',
        // adicionar os itens vendidos na tabela pivô 'venda_acessorio_estoque',
        // e decrementar a quantidade no estoque principal 'estoque'.
        // Esta é a parte mais complexa que desenvolveremos a seguir.
        // Por enquanto, apenas um placeholder:

        // throw new \Exception("Lógica de salvamento da venda ainda não implementada."); // Temporário para testar a rota

        // Exemplo Básico de Validação (Expandiremos depois)
        $request->validate([
            'data_venda' => 'required|date',
            'cliente_id' => 'nullable|exists:clientes,id',
            'forma_pagamento' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
            // Validação para os itens vendidos - ESSA PARTE É COMPLEXA e depende da estrutura do formulário
            // Ex: 'itens' => 'required|array|min:1',
            // 'itens.*.estoque_id' => 'required|exists:estoque,id',
            // 'itens.*.quantidade' => 'required|integer|min:1',
            // 'itens.*.preco_unitario_venda' => 'nullable|numeric|min:0',
        ]);

        // Por enquanto, vamos apenas criar um placeholder de venda sem itens ou estoque
        // A lógica completa virá depois.
        $venda = VendaAcessorio::create($request->only([
            'cliente_id', 'data_venda', 'forma_pagamento', 'observacoes'
        ]));
        $venda->valor_total = 0; // Placeholder, será calculado corretamente depois
        $venda->save();


        // Redirecionar para a lista de vendas com mensagem de sucesso
        return redirect()->route('vendas-acessorios.index')->with('success', 'Venda de acessório registrada com sucesso (placeholder)!');
    }

    /**
     * Display the specified resource.
     */
    public function show(VendaAcessorio $vendaAcessorio) // Model Binding para a venda
    {
        // Carregar os itens vendidos relacionados a esta venda
        $vendaAcessorio->load('itensVendidos.estoque'); // Carrega os itens e suas informações de estoque

        // Retornar a view de detalhes, passando a venda
        return view('vendas_acessorios.show', compact('vendaAcessorio'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VendaAcessorio $vendaAcessorio)
    {
        // Se decidir não permitir edição de vendas, remova este método e a rota.
        // Se permitir, a lógica aqui seria buscar a venda, itens vendidos, clientes, estoques
        // e retornar uma view de edição.
         throw new \Exception("Edição de venda de acessório ainda não implementada ou desabilitada.");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VendaAcessorio $vendaAcessorio)
    {
         // Se decidir não permitir atualização de vendas, remova este método e a rota.
         throw new \Exception("Atualização de venda de acessório ainda não implementada ou desabilitada.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VendaAcessorio $vendaAcessorio) // Model Binding para a venda
    {
        // Lógica para:
        // 1. Reverter o estoque para os itens vendidos (incrementar a quantidade no estoque principal)
        // 2. Remover os registros na tabela pivô
        // 3. Excluir o registro da venda
         throw new \Exception("Exclusão de venda de acessório ainda não implementada.");
    }
}
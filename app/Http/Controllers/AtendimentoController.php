<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\Cliente; // Precisamos para o formulário de novo atendimento
use App\Models\User; // Para listar os técnicos
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AtendimentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $atendimentos = Atendimento::with('cliente', 'tecnico')->get(); // Busca todos os atendimentos com os relacionamentos de cliente e técnico
        return view('atendimentos.index', compact('atendimentos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Cliente::all(); // Busca todos os clientes para o select no formulário
        $tecnicos = User::where('tipo_usuario', 'tecnico')->get(); // Busca todos os usuários que são técnicos
        return view('atendimentos.create', compact('clientes', 'tecnicos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'celular' => 'required|string|max:20',
            'problema_relatado' => 'required|string',
            'data_entrada' => 'required|date',
            'tecnico_id' => 'nullable|exists:users,id',
        ]);

        $atendimento = $request->all();
        $atendimento['codigo_consulta'] = Str::random(10); // Gera um código aleatório de 10 caracteres

        Atendimento::create($atendimento);

        return redirect()->route('atendimentos.index')->with('success', 'Atendimento registrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Atendimento $atendimento)
    {
        return view('atendimentos.show', compact('atendimento')); // Para exibir detalhes do atendimento
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Atendimento $atendimento)
    {
        $clientes = Cliente::all();
        $tecnicos = User::where('tipo_usuario', 'tecnico')->get();
        return view('atendimentos.edit', compact('atendimento', 'clientes', 'tecnicos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Atendimento $atendimento)
    {
        // Validação dos dados do formulário (opcional, mas recomendado)
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'celular' => 'required|string|max:255',
            'problema_relatado' => 'required|string',
            'data_entrada' => 'required|date',
            'status' => 'required|string|max:255',
            'tecnico_id' => 'nullable|exists:users,id',
            'data_conclusao' => 'nullable|date',
            'observacoes' => 'nullable|string',
        ]);

        $atendimento->update($request->all());

        return redirect()->route('atendimentos.index')->with('success', 'Atendimento atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Atendimento $atendimento)
    {
        $atendimento->delete();
        return redirect()->route('atendimentos.index')->with('success', 'Atendimento excluído com sucesso!');
    }
}
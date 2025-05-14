<?php

namespace App\Http\Controllers;

use App\Models\EntradaEstoque;
use App\Models\Estoque;
use App\Models\Atendimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
        if (Gate::denies('can-manage-basic-stock')) { // Ou 'is-internal-user'
            return redirect()->route('estoque.index')->with('error', 'Acesso não autorizado.');
        }
        $selectedEstoqueId = $request->input('estoque_id');

        // Não é mais necessário carregar todos os estoques, pois o autocomplete fará a busca.
        return view('entradas_estoque.create', compact('selectedEstoqueId'));
    }
    /**
     * Store a newly created resource in storage.
     */
    // app\Http\Controllers\EntradaEstoqueController.php - Método store
    public function store(Request $request)
    {
        if (Gate::denies('can-manage-basic-stock')) { // Ou 'is-internal-user'
            return redirect()->route('entradas-estoque.index')->with('error', 'Acesso não autorizado.');
        }
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

    public function update(Request $request, Atendimento $atendimento)
    {
        // Validações básicas
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'celular' => 'required|string|max:20',
            'problema_relatado' => 'required|string',
            'data_entrada' => 'required|date_format:Y-m-d\TH:i', // Se você usa datetime-local no form
            // 'data_entrada' => 'required|date', // Se você usa date no form e trata a hora no controller
            'status' => 'required|string|max:50',
            'tecnico_id' => 'nullable|exists:users,id',
            'data_conclusao' => 'nullable|date|after_or_equal:data_entrada',
            'observacoes' => 'nullable|string',
            'laudo_tecnico' => 'nullable|string',
            'valor_servico' => 'nullable|numeric|min:0',
            'desconto_servico' => 'nullable|numeric|min:0',
        ]);

        // Proteção específica para o campo laudo_tecnico
        // Se o campo 'laudo_tecnico' foi enviado no request E o usuário não tem permissão
        if ($request->has('laudo_tecnico') && Gate::denies('is-admin-or-tecnico')) {
            // Remove o laudo_tecnico do array de dados validados para não ser atualizado
            // Ou retorna um erro. Para este exemplo, vamos impedir a atualização do laudo.
            // unset($validatedData['laudo_tecnico']);
            // Ou melhor, redirecionar com erro se tentarem mudar sem permissão:
            if ($atendimento->laudo_tecnico !== $request->input('laudo_tecnico')) { // Se realmente houve tentativa de mudar o laudo
                return redirect()->back()->withErrors(['laudo_tecnico' => 'Você não tem permissão para alterar o laudo técnico.'])->withInput();
            }
        }
        // Se o campo 'valor_servico' ou 'desconto_servico' foi alterado e o usuário não é admin
        if (
            ($request->filled('valor_servico') && $atendimento->valor_servico != $request->valor_servico) ||
            ($request->filled('desconto_servico') && $atendimento->desconto_servico != $request->desconto_servico)
        ) {
            if (Gate::denies('is-admin')) { // Supondo que só admin pode mexer em valores finais
                return redirect()->back()->withErrors(['valor_servico' => 'Você não tem permissão para alterar valores financeiros.'])->withInput();
            }
        }


        if ($request->filled('desconto_servico') && $request->input('desconto_servico') > $request->input('valor_servico', $atendimento->valor_servico ?? 0)) {
            return back()->withErrors(['desconto_servico' => 'O desconto não pode ser maior que o valor do serviço.'])->withInput();
        }

        $atendimento->update($validatedData);

        return redirect()->route('atendimentos.show', $atendimento->id)->with('success', 'Atendimento atualizado com sucesso!');
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
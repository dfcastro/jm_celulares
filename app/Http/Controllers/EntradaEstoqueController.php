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
    public function index(Request $request)
    {
        $query = EntradaEstoque::with('estoque')->latest('data_entrada'); // Ordena pela data de entrada mais recente

        // Filtro por Período (Data de Entrada)
        if ($request->filled('data_inicial_filtro') && $request->filled('data_final_filtro')) {
            try {
                $dataInicial = Carbon::parse($request->input('data_inicial_filtro'))->startOfDay();
                $dataFinal = Carbon::parse($request->input('data_final_filtro'))->endOfDay();
                if ($dataInicial->lte($dataFinal)) {
                    $query->whereBetween('data_entrada', [$dataInicial, $dataFinal]);
                }
            } catch (\Exception $e) {
                // Lidar com data inválida, talvez com um flash message de erro
            }
        } elseif ($request->filled('data_inicial_filtro')) {
            try {
                $query->where('data_entrada', '>=', Carbon::parse($request->input('data_inicial_filtro'))->startOfDay());
            } catch (\Exception $e) {
            }
        } elseif ($request->filled('data_final_filtro')) {
            try {
                $query->where('data_entrada', '<=', Carbon::parse($request->input('data_final_filtro'))->endOfDay());
            } catch (\Exception $e) {
            }
        }

        // Filtro por Peça Específica (ID da peça vindo do autocomplete)
        if ($request->filled('filtro_peca_id')) {
            $query->where('estoque_id', $request->input('filtro_peca_id'));
        }

        // Filtro por Observações da Entrada
        if ($request->filled('busca_obs_entrada')) {
            $searchTerm = $request->input('busca_obs_entrada');
            $query->where('observacoes', 'like', '%' . $searchTerm . '%');
        }

        $entradas = $query->paginate(15); // Mantive 15, ajuste se necessário
        $entradas->appends($request->query()); // Importante para manter os filtros na paginação

        // Para o autocomplete no filtro de peças
        $pecaSelecionadaNome = null;
        if ($request->filled('filtro_peca_id') && $request->filled('filtro_peca_nome_display')) {
            // Se o ID está presente e o nome também (veio do submit do form com autocomplete preenchido),
            // usamos o nome que já veio para evitar uma nova consulta só para o display.
            $pecaSelecionadaNome = $request->input('filtro_peca_nome_display');
        } elseif ($request->filled('filtro_peca_id')) {
            // Se só o ID veio (ex: URL direta), busca o nome da peça.
            $peca = Estoque::find($request->input('filtro_peca_id'));
            if ($peca) {
                $pecaSelecionadaNome = $peca->nome . ($peca->modelo_compativel ? ' (' . $peca->modelo_compativel . ')' : '');
            }
        }


        return view('entradas_estoque.index', compact('entradas', 'pecaSelecionadaNome'));
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
        if (Gate::denies('can-manage-basic-stock')) {
            return redirect()->route('entradas-estoque.index')->with('error', 'Acesso não autorizado.');
        }
        $request->validate([
            'estoque_id' => 'required|exists:estoque,id',
            'quantidade' => 'required|integer|min:1',
            'data_entrada' => 'required|date',
            'observacoes' => 'nullable|string|max:255',
        ]);

        $estoque = Estoque::findOrFail($request->estoque_id);

        $dataEntradaFormulario = $request->input('data_entrada');
        $dataCompleta = Carbon::parse($dataEntradaFormulario)
            ->setTime(Carbon::now()->hour, Carbon::now()->minute, Carbon::now()->second);

        $dadosEntrada = $request->except('data_entrada');
        $dadosEntrada['data_entrada'] = $dataCompleta;

        EntradaEstoque::create($dadosEntrada);



        $estoque->increment('quantidade', $request->quantidade);

        return redirect()->route('entradas-estoque.index')
            ->with('success', "Entrada de {$request->quantidade} unidade(s) para '{$estoque->nome}' (ID Estoque: {$estoque->id}) registrada com sucesso!");
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
        $entradaEstoque = EntradaEstoque::findOrFail($id);
        $estoque = Estoque::findOrFail($entradaEstoque->estoque_id);
        $cantidadParaDecrement = (int) $entradaEstoque->quantidade;

        $nomePeca = $estoque->nome; // Melhor pegar o nome da peça diretamente do objeto $estoque
        $qtdEstornada = $entradaEstoque->quantidade;
        $idEntrada = $entradaEstoque->id;

        // VERIFICAÇÃO: Antes de decrementar, confira se o estoque ficaria negativo.
        if ($estoque->quantidade < $cantidadParaDecrement) {
            // Se a quantidade atual em estoque for menor que a quantidade da entrada
            // que está sendo excluída, a exclusão resultaria em estoque negativo.
            return redirect()->route('entradas-estoque.index')
                ->with('error', "Não é possível excluir a entrada #{$idEntrada} ({$qtdEstornada} unidade(s) de '{$nomePeca}'). A exclusão resultaria em estoque negativo ({$estoque->quantidade} - {$cantidadParaDecrement} = " . ($estoque->quantidade - $cantidadParaDecrement) . "). Verifique as saídas de estoque para este item.");
        }

        // Se a verificação passar, prossiga com o decremento e exclusão.
        $estoque->decrement('quantidade', $cantidadParaDecrement);
        $entradaEstoque->delete();

        return redirect()->route('entradas-estoque.index')
            ->with('success', "Entrada de estoque #{$idEntrada} ({$qtdEstornada} unidade(s) de '{$nomePeca}') excluída e estoque revertido com sucesso!");
    }
}
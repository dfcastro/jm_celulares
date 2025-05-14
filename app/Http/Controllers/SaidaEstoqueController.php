<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\Estoque;
use App\Models\SaidaEstoque;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;


class SaidaEstoqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SaidaEstoque::with(['estoque', 'atendimento.cliente']); // Eager load com nested relationship

        // Filtro por Período (Data da Saída)
        if ($request->filled('data_inicial_filtro') && $request->filled('data_final_filtro')) {
            $dataInicial = Carbon::parse($request->input('data_inicial_filtro'))->startOfDay();
            $dataFinal = Carbon::parse($request->input('data_final_filtro'))->endOfDay();
            if ($dataInicial->lte($dataFinal)) {
                $query->whereBetween('data_saida', [$dataInicial, $dataFinal]);
            }
        } elseif ($request->filled('data_inicial_filtro')) {
            $query->where('data_saida', '>=', Carbon::parse($request->input('data_inicial_filtro'))->startOfDay());
        } elseif ($request->filled('data_final_filtro')) {
            $query->where('data_saida', '<=', Carbon::parse($request->input('data_final_filtro'))->endOfDay());
        }

        // Filtro por Peça Específica (ID da peça)
        if ($request->filled('filtro_peca_id')) {
            $query->where('estoque_id', $request->input('filtro_peca_id'));
        }

        // Filtro por Atendimento Específico (ID do atendimento)
        if ($request->filled('filtro_atendimento_id')) {
            if ($request->input('filtro_atendimento_id') == '0') { // Para "Não Vinculado"
                $query->whereNull('atendimento_id');
            } else {
                $query->where('atendimento_id', $request->input('filtro_atendimento_id'));
            }
        }

        // Filtro por Observações da Saída
        if ($request->filled('busca_obs_saida')) {
            $searchTerm = $request->input('busca_obs_saida');
            $query->where('observacoes', 'like', '%' . $searchTerm . '%');
        }


        $saidas = $query->orderBy('data_saida', 'desc')->paginate(15);
        $saidas->appends($request->query());

        // Para os autocompletes/selects de filtro na view (opcional, mas bom para UX)
        // Se você for usar select para peça/atendimento em vez de autocomplete, precisaria passar $pecas e $atendimentos aqui.
        // Para autocomplete, não precisa passar a lista inteira.

        return view('saidas_estoque.index', compact('saidas'));
    }


    /**
     * Show the form for creating a new resource.
     */
    // Aceita um parâmetro opcional 'estoque_id' vindo da URL
    public function create(Request $request)
    {
        if (Gate::denies('is-admin-or-tecnico')) {
            // Se a saída puder ser iniciada a partir de um atendimento por um atendente,
            // essa lógica precisa ser mais flexível ou o Gate aqui deve ser diferente.
            // Por "Saídas Avulsas" entende-se saídas não diretamente ligadas a um fluxo de venda ou atendimento iniciado por atendente.
            return redirect()->route('estoque.index')->with('error', 'Acesso não autorizado.');
        }

        $selectedEstoqueId = $request->input('estoque_id');
        $selectedAtendimentoId = $request->input('atendimento_id');

        $atendimentoSelecionado = null;
        if ($selectedAtendimentoId) {
            $atendimentoSelecionado = Atendimento::with('cliente')->find($selectedAtendimentoId);
        }

        return view('saidas_estoque.create', compact(
            // 'atendimentos', // Não mais necessário
            'selectedEstoqueId',
            'selectedAtendimentoId', // Ainda útil para o campo hidden
            'atendimentoSelecionado' // Para preencher o nome no campo de texto
        ));
    }

    /* * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Gate::denies('is-admin-or-tecnico')) { // Ou a permissão apropriada
            return redirect()->route('saidas-estoque.index')->with('error', 'Acesso não autorizado.');
        }

        $request->validate([
            'estoque_id' => 'required|exists:estoque,id',
            'atendimento_id' => 'nullable|exists:atendimentos,id', // Mantém nullable
            'quantidade' => 'required|integer|min:1',
            'data_saida' => 'required|date',
            'observacoes' => 'nullable|string|max:255',
        ]);

        $estoque = Estoque::find($request->estoque_id);
        if (!$estoque || $request->quantidade > $estoque->quantidade) {
            throw ValidationException::withMessages([
                'quantidade' => 'Quantidade solicitada excede o estoque disponível ou peça inválida.',
            ]);
        }

        $dadosSaida = $request->all();
        if ($request->filled('data_saida')) {
            $dataDoFormulario = $request->input('data_saida');
            $dadosSaida['data_saida'] = Carbon::parse($dataDoFormulario)->setTimeFrom(Carbon::now());
        }

        SaidaEstoque::create($dadosSaida);
        $estoque->decrement('quantidade', $request->quantidade);

        // NOVO: Lógica de Redirecionamento Condicional
        if ($request->filled('atendimento_id')) {
            // Se a saída foi vinculada a um atendimento, volta para a tela desse atendimento
            return redirect()->route('atendimentos.show', $request->atendimento_id)
                ->with('success', 'Peça adicionada ao atendimento e saída de estoque registrada!');
        } else {
            // Caso contrário (saída avulsa), volta para a lista de saídas
            return redirect()->route('saidas-estoque.index')
                ->with('success', 'Saída de estoque avulsa registrada com sucesso!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SaidaEstoque $saidaEstoque) // Usando Route Model Binding
    {
        $saidaEstoque->load(['estoque', 'atendimento.cliente']);
        return view('saidas_estoque.show', compact('saidaEstoque')); // Garanta que 'saidaEstoque' está aqui
    }



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

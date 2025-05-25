<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\Estoque;
use App\Models\SaidaEstoque;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SaidaEstoqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SaidaEstoque::with(['estoque', 'atendimento.cliente']);

        // Filtros existentes (mantidos como antes)
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

        if ($request->filled('filtro_peca_id')) {
            $query->where('estoque_id', $request->input('filtro_peca_id'));
        }

        if ($request->filled('filtro_atendimento_id')) {
            if ($request->input('filtro_atendimento_id') == '0') {
                $query->whereNull('atendimento_id');
            } else {
                $query->where('atendimento_id', $request->input('filtro_atendimento_id'));
            }
        }

        if ($request->filled('busca_obs_saida')) {
            $searchTerm = $request->input('busca_obs_saida');
            $query->where('observacoes', 'like', '%' . $searchTerm . '%');
        }

        $saidas = $query->orderBy('data_saida', 'desc')->paginate(15);
        $saidas->appends($request->query());

        // Para repopular os campos de display dos autocompletes nos filtros
        $pecaSelecionadaNome = null;
        if ($request->filled('filtro_peca_id') && $request->filled('filtro_peca_nome_display')) {
            $pecaSelecionadaNome = $request->input('filtro_peca_nome_display');
        } elseif ($request->filled('filtro_peca_id')) {
            $peca = Estoque::find($request->input('filtro_peca_id'));
            if ($peca) {
                $pecaSelecionadaNome = $peca->nome . ($peca->modelo_compativel ? ' (' . $peca->modelo_compativel . ')' : '');
            }
        }

        $atendimentoSelecionadoInfo = null;
        if ($request->filled('filtro_atendimento_id') && $request->filled('filtro_atendimento_info')) {
            $atendimentoSelecionadoInfo = $request->input('filtro_atendimento_info');
        } elseif ($request->filled('filtro_atendimento_id') && $request->input('filtro_atendimento_id') != '0') {
            $atendimento = Atendimento::with('cliente')->find($request->input('filtro_atendimento_id'));
            if ($atendimento) {
                $atendimentoSelecionadoInfo = '#' . $atendimento->id . ($atendimento->cliente ? ' - ' . $atendimento->cliente->nome_completo : ' - Cliente não associado');
            }
        }

        return view('saidas_estoque.index', compact('saidas', 'pecaSelecionadaNome', 'atendimentoSelecionadoInfo'));
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
        // Permissão geral para dar saída de estoque
        if (Gate::denies('is-admin-or-tecnico')) {
            return redirect()->route('saidas-estoque.index')->with('error', 'Acesso não autorizado.');
        }

        $validatedData = $request->validate([ // Renomeado para $validatedData para clareza
            'estoque_id' => 'required|exists:estoque,id',
            'atendimento_id' => 'nullable|exists:atendimentos,id',
            'quantidade' => 'required|integer|min:1',
            'data_saida' => 'required|date',
            'observacoes' => 'nullable|string|max:255',
        ]);

        // VERIFICAÇÃO ADICIONAL SE ESTIVER VINCULANDO A UM ATENDIMENTO
        if ($request->filled('atendimento_id')) {
            $atendimentoVinculado = Atendimento::find($request->atendimento_id);
            if ($atendimentoVinculado && !$atendimentoVinculado->podeEditarPecas(Auth::user())) { // Usa o novo método
                Log::warning("Tentativa de adicionar peça ao Atendimento #{$atendimentoVinculado->id} que está em status '{$atendimentoVinculado->status}' ou sem permissão.");
                return redirect()->route('atendimentos.show', $atendimentoVinculado->id)
                    ->with('error', 'Não é possível adicionar/remover peças deste atendimento no status atual (' . $atendimentoVinculado->status . ') ou você não tem permissão.');
            }
        }
        // FIM DA VERIFICAÇÃO ADICIONAL

        $estoque = Estoque::find($validatedData['estoque_id']); // Usa $validatedData
        if (!$estoque || $validatedData['quantidade'] > $estoque->quantidade) { // Usa $validatedData
            throw ValidationException::withMessages([
                'quantidade' => 'Quantidade solicitada excede o estoque disponível ou peça inválida.',
            ]);
        }

        // Trata a data_saida como antes, para incluir a hora atual
        $dadosSaida = $validatedData; // Usa os dados já validados
        if ($request->filled('data_saida')) {
            $dataDoFormulario = $request->input('data_saida');
            $dadosSaida['data_saida'] = Carbon::parse($dataDoFormulario)->setTimeFrom(Carbon::now());
        }

        SaidaEstoque::create($dadosSaida);
        $estoque->decrement('quantidade', $validatedData['quantidade']); // Usa $validatedData

        if ($request->filled('atendimento_id')) {
            return redirect()->route('atendimentos.show', $request->atendimento_id)
                ->with('success', 'Peça adicionada ao atendimento e saída de estoque registrada!');
        } else {
            return redirect()->route('saidas-estoque.index')
                ->with('success', 'Saída de estoque avulsa registrada com sucesso!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SaidaEstoque $saidas_estoque) // <--- NOME DA VARIÁVEL IMPORTANTE
    {
        // $saidas_estoque já é a instância correta devido ao Route Model Binding.
        // Apenas garanta que as relações necessárias estão carregadas.
        $saidas_estoque->loadMissing(['estoque', 'atendimento.cliente']);

        // Passe para a view com o nome que a view espera.
        // Se a view espera $saidaEstoque, então use 'saidaEstoque'.
        return view('saidas_estoque.show', ['saidaEstoque' => $saidas_estoque]);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SaidaEstoque $saidas_estoque)
    {
        if (Gate::denies('is-admin-or-tecnico')) {
            return redirect()->route('saidas-estoque.index')->with('error', 'Acesso não autorizado para excluir saída.');
        }

        // VERIFICAÇÃO ADICIONAL SE A SAÍDA ESTAVA VINCULADA A UM ATENDIMENTO
        if ($saidas_estoque->atendimento_id) {
            $atendimentoVinculado = Atendimento::find($saidas_estoque->atendimento_id);
            // Se o atendimento existe E não pode ter peças editadas (está finalizado)
            if ($atendimentoVinculado && !$atendimentoVinculado->podeEditarPecas(Auth::user())) {
                Log::warning("Tentativa de excluir peça (SaidaEstoque #{$saidas_estoque->id}) do Atendimento #{$atendimentoVinculado->id} que está em status '{$atendimentoVinculado->status}'.");
                return redirect()->back() // Volta para a página anterior (lista de saídas ou detalhes do atendimento)
                    ->with('error', 'Não é possível excluir peças de um atendimento que está no status "' . $atendimentoVinculado->status . '".');
            }
        }
        // FIM DA VERIFICAÇÃO ADICIONAL

        $saidas_estoque->loadMissing('estoque');

        if (!$saidas_estoque->estoque) {
            $idSaidaExcluida = $saidas_estoque->id;
            $saidas_estoque->delete();
            Log::warning("Saída de estoque #{$idSaidaExcluida} excluída, mas a peça original não foi encontrada no estoque para estorno.");
            return redirect()->route('saidas-estoque.index')
                ->with('warning', "Saída de estoque #{$idSaidaExcluida} excluída. A peça original não foi encontrada para estorno.");
        }

        $estoque = $saidas_estoque->estoque;
        $quantidadeParaIncrementar = (int) $saidas_estoque->quantidade;
        $nomePeca = $estoque->nome;
        $idSaida = $saidas_estoque->id;

        DB::beginTransaction();
        try {
            $estoque = $saidas_estoque->estoque; // Já carregado ou verificado acima
            $quantidadeParaIncrementar = (int) $saidas_estoque->quantidade;
            $nomePeca = $estoque->nome ?? 'Peça Desconhecida';
            $idSaida = $saidas_estoque->id;

            $estoque->increment('quantidade', $quantidadeParaIncrementar);
            $saidas_estoque->delete();
            DB::commit();

            $mensagemSucesso = "Saída de estoque #{$idSaida} ({$quantidadeParaIncrementar} unidade(s) de '{$nomePeca}') excluída e estoque estornado com sucesso!";
            // Redireciona para a origem, se possível, ou para a lista de saídas
            if ($saidas_estoque->atendimento_id) {
                return redirect()->route('atendimentos.show', $saidas_estoque->atendimento_id)->with('success', $mensagemSucesso);
            }
            return redirect()->route('saidas-estoque.index')->with('success', $mensagemSucesso);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao excluir saída de estoque #{$saidas_estoque->id}: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('saidas-estoque.index')->with('error', 'Ocorreu um erro ao excluir a saída de estoque.');
        }
    }
}

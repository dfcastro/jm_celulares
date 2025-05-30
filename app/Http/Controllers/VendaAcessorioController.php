<?php

namespace App\Http\Controllers;

use App\Models\VendaAcessorio;
use App\Models\Estoque;
use App\Models\Cliente;
use App\Models\DevolucaoVenda; // <<<<<<< ADICIONE ESTA LINHA
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log; // Confirme que esta linha está aqui e sem a barra invertida (\)
use Carbon\Carbon; // <<<<<<< ADICIONE ESTA LINHA PARA TRABALHAR COM DATAS
use App\Models\Caixa; // Adicionar esta linha
use App\Models\MovimentacaoCaixa; // Adicionar esta linha
use Illuminate\Support\Facades\Auth; // Se não estiver lá, para pegar o usuário logado


class VendaAcessorioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = VendaAcessorio::with(['cliente', 'usuarioRegistrou']) // Adicionado 'usuarioRegistrou'
            ->latest('data_venda');

        // Filtro por Período
        if ($request->filled('data_inicial_filtro') && $request->filled('data_final_filtro')) {
            try {
                $dataInicial = Carbon::parse($request->input('data_inicial_filtro'))->startOfDay();
                $dataFinal = Carbon::parse($request->input('data_final_filtro'))->endOfDay();
                if ($dataInicial->lte($dataFinal)) {
                    $query->whereBetween('data_venda', [$dataInicial, $dataFinal]);
                }
            } catch (\Exception $e) { /* Tratar erro de data inválida se necessário */
            }
        } elseif ($request->filled('data_inicial_filtro')) {
            try {
                $query->where('data_venda', '>=', Carbon::parse($request->input('data_inicial_filtro'))->startOfDay());
            } catch (\Exception $e) {
            }
        } elseif ($request->filled('data_final_filtro')) {
            try {
                $query->where('data_venda', '<=', Carbon::parse($request->input('data_final_filtro'))->endOfDay());
            } catch (\Exception $e) {
            }
        }

        // Filtro por Cliente
        if ($request->filled('filtro_cliente_id')) {
            $query->where('cliente_id', $request->input('filtro_cliente_id'));
        }

        // Filtro por Forma de Pagamento
        if ($request->filled('filtro_forma_pagamento')) {
            $query->where('forma_pagamento', $request->input('filtro_forma_pagamento'));
        }

        // Filtro por Usuário que Registrou a Venda
        if ($request->filled('filtro_usuario_id')) {
            $query->where('user_id', $request->input('filtro_usuario_id'));
        }

        $vendas = $query->paginate(10)->appends($request->query());

        $clienteSelecionadoNome = null;
        if ($request->filled('filtro_cliente_id') && $request->filled('filtro_cliente_nome_display')) {
            $clienteSelecionadoNome = $request->input('filtro_cliente_nome_display');
        } elseif ($request->filled('filtro_cliente_id')) {
            $cliente = Cliente::find($request->input('filtro_cliente_id'));
            if ($cliente) {
                $clienteSelecionadoNome = $cliente->nome_completo . ($cliente->cpf_cnpj ? ' (' . $cliente->cpf_cnpj . ')' : '');
            }
        }

        $formasPagamentoDisponiveis = config('constants.formas_pagamento', []);

        // Para filtro de usuário (se você quiser adicionar)
        $usuariosSistema = \App\Models\User::whereIn('tipo_usuario', ['admin', 'atendente', 'tecnico'])
            ->orderBy('name')->get(['id', 'name']);


        return view('vendas_acessorios.index', compact(
            'vendas',
            'clienteSelecionadoNome',
            'formasPagamentoDisponiveis',
            'usuariosSistema', // Passa para o filtro de usuário
            'request'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $clientes = Cliente::all();
        $selectedEstoqueId = $request->input('estoque_id');

        // NOVO: Definição das formas de pagamento
        $formasPagamento = [
            'Dinheiro',
            'Cartão de Crédito',
            'Cartão de Débito',
            'Pix',
            'Transferência Bancária',
            'Boleto',
            'Outro'
        ];

        return view('vendas_acessorios.create', compact('clientes', 'selectedEstoqueId', 'formasPagamento'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'data_venda' => 'required|date', // Validar o formato da data do input
            'forma_pagamento' => 'nullable|string|max:50',
            'observacoes' => 'nullable|string',
            'itens' => 'required|array|min:1',
            'itens.*.item_estoque_id' => 'required|exists:estoque,id',
            'itens.*.quantidade' => 'required|integer|min:1',
            'itens.*.preco_unitario' => 'required|numeric|min:0',
            'itens.*.desconto' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $vendaAcessorio = new VendaAcessorio();
            $vendaAcessorio->cliente_id = $validatedData['cliente_id'];

            // --- CORREÇÃO DA DATA E HORA ---
            // Pega a data do formulário e adiciona a hora atual
            $dataDoFormulario = $validatedData['data_venda']; // Ex: "2025-05-25"
            $vendaAcessorio->data_venda = Carbon::parse($dataDoFormulario)->setTimeFrom(Carbon::now());
            // Ou, para ser mais explícito com o formato:
            // Ou, se quiser que seja exatamente a hora atual do servidor no momento do clique:
            // $vendaAcessorio->data_venda = Carbon::now(); // Isso ignora a data selecionada no formulário
            // A melhor abordagem é pegar a data do formulário e o horário do momento do registro:
            // $vendaAcessorio->data_venda = Carbon::createFromFormat('Y-m-d H:i:s', $dataDoFormulario . ' ' . Carbon::now()->format('H:i:s'));

            $vendaAcessorio->valor_total = 0; // Será calculado
            $vendaAcessorio->forma_pagamento = $validatedData['forma_pagamento'];
            $vendaAcessorio->observacoes = $validatedData['observacoes'];
            $vendaAcessorio->user_id = Auth::id();
            $vendaAcessorio->save();

            $valorTotalCalculado = 0;
            foreach ($validatedData['itens'] as $itemVendaData) {
                // ... (lógica de itens e estoque como antes) ...
                $itemEstoque = Estoque::findOrFail($itemVendaData['item_estoque_id']);
                $quantidadeVendida = (int) $itemVendaData['quantidade'];
                $precoUnitario = (float) $itemVendaData['preco_unitario'];
                $descontoItem = (float) ($itemVendaData['desconto'] ?? 0);

                if ($itemEstoque->quantidade < $quantidadeVendida) {
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', "Estoque insuficiente para o item '{$itemEstoque->nome}'. Disponível: {$itemEstoque->quantidade}, Solicitado: {$quantidadeVendida}.")
                        ->withInput();
                }

                $subtotalItem = ($quantidadeVendida * $precoUnitario) - $descontoItem;
                $valorTotalCalculado += $subtotalItem;

                $vendaAcessorio->itensEstoque()->attach($itemEstoque->id, [
                    'quantidade' => $quantidadeVendida,
                    'preco_unitario_venda' => $precoUnitario,
                    'desconto' => $descontoItem,
                    'created_at' => now(), // Adiciona timestamp para a tabela pivô
                    'updated_at' => now(), // Adiciona timestamp para a tabela pivô
                ]);
                $itemEstoque->decrement('quantidade', $quantidadeVendida);
            }

            $vendaAcessorio->valor_total = $valorTotalCalculado;
            $vendaAcessorio->save();

            // Lógica do Caixa (como antes)
            $caixaAberto = Caixa::getCaixaAbertoAtual();
            if ($caixaAberto) {
                if ($vendaAcessorio->valor_total > 0) {
                    MovimentacaoCaixa::create([
                        'caixa_id' => $caixaAberto->id,
                        'usuario_id' => Auth::id(),
                        'tipo' => 'ENTRADA',
                        'descricao' => "Recebimento Venda de Acessório #" . $vendaAcessorio->id,
                        'valor' => $vendaAcessorio->valor_total,
                        'forma_pagamento' => $vendaAcessorio->forma_pagamento,
                        'referencia_id' => $vendaAcessorio->id,
                        'referencia_tipo' => VendaAcessorio::class,
                        'data_movimentacao' => $vendaAcessorio->data_venda, // Usa a data_venda com horário
                        'observacoes' => 'Venda registrada pelo sistema.',
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('vendas-acessorios.show', $vendaAcessorio->id)
                ->with('success', 'Venda de acessório registrada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao registrar venda de acessório: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()
                ->with('error', 'Erro ao registrar a venda. Tente novamente. Detalhes: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(VendaAcessorio $vendas_acessorio) // O nome da variável DEVE ser igual ao parâmetro da rota
    {
        // A variável $vendas_acessorio já é a instância do model VendaAcessorio.
        // O Laravel já fez o findOrFail nos bastidores.

        $vendas_acessorio->load([
            'itensEstoque',
            'cliente',
            'usuarioRegistrou' // Se você tem esse relacionamento no model VendaAcessorio
        ]);

        return view('vendas_acessorios.show', ['vendaAcessorio' => $vendas_acessorio]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VendaAcessorio $vendaAcessorio)
    {
        throw new \Exception("Edição de venda de acessório ainda não implementada ou desabilitada.");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VendaAcessorio $vendaAcessorio)
    {
        throw new \Exception("Atualização de venda de acessório ainda não implementada ou desabilitada.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VendaAcessorio $vendas_acessorio) // Mudamos a variável para $vendas_acessorio
    {

        // Lógica para:
        // 1. Reverter o estoque para os itens vendidos (incrementar a quantidade no estoque principal)
        // 2. Remover os registros na tabela pivô
        // 3. Excluir o registro da venda

        // Esta é a versão correta do destroy, que deve estar lá.
        DB::beginTransaction();
        try {
            // Para cada item vendido nesta venda
            foreach ($vendas_acessorio->itensVendidos as $itemEstoque) {
                // Acessa a quantidade vendida do pivô
                $quantidadeVendida = $itemEstoque->pivot->quantidade;

                // Encontra a peça no estoque e incrementa a quantidade
                $estoque = Estoque::find($itemEstoque->id);
                if ($estoque) {
                    $estoque->increment('quantidade', $quantidadeVendida);
                }
            }

            // Apaga a venda, o que automaticamente deletará as entradas na tabela pivô
            // devido ao onDelete('cascade') definido na migração 'venda_acessorio_estoque_table'.
            $idVendaExcluida = $vendas_acessorio->id;
            $vendas_acessorio->delete(); // Após a lógica de reverter estoque
            DB::commit();
            return redirect()->route('vendas-acessorios.index')
                ->with('success', "Venda de acessório #{$idVendaExcluida} excluída e estoque revertido com sucesso!");
        } catch (\Exception $e) {
            DB::rollBack(); // Reverte a transação em caso de erro
            Log::error('Erro ao excluir venda de acessório: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Ocorreu um erro ao excluir a venda e reverter o estoque. Tente novamente mais tarde.');
        }
    }

    /**
     * Busca peças de estoque para autocomplete.
     * Recebe 'search' na Request e retorna JSON.
     */
    public function autocompleteItemEstoque(Request $request)
    {
        $search = $request->get('search'); // Obtém o termo de busca da requisição

        // Busca peças cujo nome ou modelo compatível contenham o termo de busca
        $itensEstoque = Estoque::where('nome', 'LIKE', '%' . $search . '%')
            ->orWhere('modelo_compativel', 'LIKE', '%' . $search . '%')
            ->limit(20) // Limita o número de resultados para não sobrecarregar
            ->get(['id', 'nome', 'modelo_compativel', 'quantidade', 'preco_venda']); // Seleciona apenas os campos necessários

        // Mapeia para o formato que o jQuery UI Autocomplete espera
        // E inclui os dados de quantidade e preco_venda nos atributos de dados
        $formattedItems = $itensEstoque->map(function ($item) {
            return [
                'label' => $item->nome . ' (' . ($item->modelo_compativel ?? 'N/A') . ') - Qtd Disponível: ' . $item->quantidade,
                'value' => $item->nome . ' (' . ($item->modelo_compativel ?? 'N/A') . ')', // Valor que preenche o input
                'id' => $item->id,
                'preco_venda' => number_format($item->preco_venda, 2, '.', ''), // Formata para 2 casas decimais
                'quantidade_disponivel' => $item->quantidade, // Quantidade atual em estoque
            ];
        });

        return response()->json($formattedItems);
    }


    /**
     * Exibe o formulário para registrar uma devolução de venda.
     *
     * @param  \App\Models\VendaAcessorio  $vendas_acessorio
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showDevolucaoForm(VendaAcessorio $vendas_acessorio)
    {
        $vendas_acessorio->load('itensEstoque', 'cliente', 'devolucoesVendas'); // Carrega os três relacionamentos
        // ou $vendas_acessorio->loadMissing(['itensEstoque', 'cliente', 'devolucoesVendas']);

        return view('vendas_acessorios.devolver', compact('vendas_acessorio'));
    }
    /**
     * Processa o registro de uma devolução de venda.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VendaAcessorio  $vendas_acessorio
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processarDevolucao(Request $request, VendaAcessorio $vendas_acessorio)
    {
        $request->validate([
            'itens_devolver' => 'required|array|min:1',
            'itens_devolver.*.estoque_id' => 'required|exists:estoque,id',
            'itens_devolver.*.quantidade_devolver' => 'required|integer|min:1',
            'observacoes_devolucao' => 'nullable|string|max:500',
        ], [
            'itens_devolver.required' => 'Selecione pelo menos um item para devolver.',
            'itens_devolver.*.quantidade_devolver.min' => 'A quantidade a devolver para cada item deve ser pelo menos 1.',
            'itens_devolver.*.estoque_id.exists' => 'O item de estoque selecionado para devolução não é válido.',
        ]);

        DB::beginTransaction();
        try {
            $valorTotalDevolvido = 0;
            $errosDevolucao = [];
            $itensParaAnexarADevolucao = []; // NOVO: Array para armazenar os itens a serem anexados à devolução

            foreach ($request->itens_devolver as $index => $itemDevolucaoInput) { // Adicionado $index
                $estoqueItem = Estoque::find($itemDevolucaoInput['estoque_id']);

                // 1. Validação da existência do item de estoque
                if (!$estoqueItem) {
                    $errosDevolucao[] = "Item de estoque com ID {$itemDevolucaoInput['estoque_id']} não encontrado no sistema.";
                    continue;
                }

                $quantidadeADevolver = (int) $itemDevolucaoInput['quantidade_devolver'];

                // 2. Encontrar o item na venda original para verificar a quantidade que foi vendida
                $itemVendidoNaVendaOriginal = $vendas_acessorio->itensVendidos->where('id', $estoqueItem->id)->first();

                if (!$itemVendidoNaVendaOriginal) {
                    $errosDevolucao[] = "O item '{$estoqueItem->nome}' não faz parte da venda original #{$vendas_acessorio->id}.";
                    continue;
                }

                $quantidadeOriginalVendida = $itemVendidoNaVendaOriginal->pivot->quantidade;

                // 3. CALCULAR A QUANTIDADE JÁ DEVOLVIDA PARA ESTE ITEM NESTA VENDA
                // Soma a quantidade de todas as devoluções anteriores para este item e esta venda
                $quantidadeJaDevolvida = DB::table('devolucao_venda_estoque')
                    ->join('devolucoes_vendas', 'devolucao_venda_estoque.devolucao_venda_id', '=', 'devolucoes_vendas.id')
                    ->where('devolucoes_vendas.venda_acessorio_id', $vendas_acessorio->id)
                    ->where('devolucao_venda_estoque.estoque_id', $estoqueItem->id)
                    ->sum('quantidade_devolvida');

                $quantidadeAindaDevolvivel = $quantidadeOriginalVendida - $quantidadeJaDevolvida;

                // 4. VALIDAÇÃO CRÍTICA: Não permitir devolver mais do que o vendível
                if ($quantidadeADevolver > $quantidadeAindaDevolvivel) {
                    $errosDevolucao[] = "Você tentou devolver {$quantidadeADevolver} unidades de '{$estoqueItem->nome}', mas apenas {$quantidadeAindaDevolvivel} podem ser devolvidas.";
                    continue; // Continua para o próximo item
                }

                if ($quantidadeADevolver <= 0) { // Validação para garantir que o usuário não envie 0 ou negativo
                    $errosDevolucao[] = "A quantidade a devolver para '{$estoqueItem->nome}' deve ser maior que zero.";
                    continue;
                }

                // 5. Calcular o valor a ser estornado/devolvido para este item
                $precoUnitarioOriginal = $itemVendidoNaVendaOriginal->pivot->preco_unitario_venda;
                $descontoOriginalPorItemVendido = $itemVendidoNaVendaOriginal->pivot->desconto / $quantidadeOriginalVendida; // Desconto proporcional por unidade vendida
                $valorUnitarioLiquidoOriginal = $precoUnitarioOriginal - $descontoOriginalPorItemVendido;

                $valorItemDevolvido = $quantidadeADevolver * $valorUnitarioLiquidoOriginal;
                $valorTotalDevolvido += $valorItemDevolvido;

                // 6. Estornar o estoque
                $estoqueItem->increment('quantidade', $quantidadeADevolver);

                // 7. Prepara os dados para anexar na tabela pivô da devolução
                $itensParaAnexarADevolucao[$estoqueItem->id] = [
                    'quantidade_devolvida' => $quantidadeADevolver,
                    'valor_unitario_devolvido' => $valorUnitarioLiquidoOriginal, // Use o valor líquido
                ];
            }

            if (!empty($errosDevolucao)) {
                DB::rollBack();
                // A ValidationException esperaria as mensagens no formato field => [errors]
                // Se for um erro geral, podemos lançar uma exceção genérica ou redirecionar com 'error'
                throw ValidationException::withMessages(['devolucao' => $errosDevolucao]);
            }

            // 8. Registrar a devolução na tabela devolucoes_vendas
            $devolucao = DevolucaoVenda::create([
                'venda_acessorio_id' => $vendas_acessorio->id,
                'valor_devolvido' => $valorTotalDevolvido,
                'data_devolucao' => Carbon::now(),
                'observacoes' => $request->observacoes_devolucao,
            ]);

            // 9. Anexar os itens devolvidos à devolução na tabela pivô
            $devolucao->itensDevolvidos()->attach($itensParaAnexarADevolucao);

            // Opcional: Atualizar o status da venda original (você pode adicionar um campo 'status_devolucao' na tabela vendas_acessorios)
            // Se você quiser, pode calcular se a venda está totalmente devolvida.
            // Ex:
            // $totalVendidoNaVenda = $vendas_acessorio->itensVendidos->sum(function ($item) {
            //     return $item->pivot->quantidade;
            // });
            // $totalDevolvidoNaVenda = DB::table('devolucao_venda_estoque')
            //     ->join('devolucoes_vendas', 'devolucao_venda_estoque.devolucao_venda_id', '=', 'devolucoes_vendas.id')
            //     ->where('devolucoes_vendas.venda_acessorio_id', $vendas_acessorio->id)
            //     ->sum('quantidade_devolvida');
            // if ($totalDevolvidoNaVenda >= $totalVendidoNaVenda) {
            //     $vendas_acessorio->status = 'devolvida'; // Assumindo que você tem um campo 'status' na venda
            //     $vendas_acessorio->save();
            // } else {
            //     $vendas_acessorio->status = 'parcialmente_devolvida';
            //     $vendas_acessorio->save();
            // }


            DB::commit();

            return redirect()->route('vendas-acessorios.show', $vendas_acessorio->id)->with('success', 'Devolução registrada com sucesso e estoque estornado!');

        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar devolução de venda: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Ocorreu um erro inesperado ao processar a devolução. Tente novamente mais tarde.')->withInput();
        }
    }
}
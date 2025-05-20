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
    public function index()
    {
        $vendas = VendaAcessorio::with('cliente')->latest()->paginate(10);
        return view('vendas_acessorios.index', compact('vendas'));
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validação dos dados da venda (você já deve ter isso)
        $validatedData = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'data_venda' => 'required|date',
            'forma_pagamento' => 'nullable|string|max:50',
            'observacoes' => 'nullable|string',
            'itens' => 'required|array|min:1',
            'itens.*.item_estoque_id' => 'required|exists:estoque,id',
            'itens.*.quantidade' => 'required|integer|min:1',
            'itens.*.preco_unitario' => 'required|numeric|min:0',
            'itens.*.desconto' => 'nullable|numeric|min:0',
        ]);

        // ---- INÍCIO DA LÓGICA DE TRANSAÇÃO (OPCIONAL MAS RECOMENDADO) ----
        // DB::beginTransaction();
        // try {
        // ---- FIM DA LÓGICA DE TRANSAÇÃO ----

        // 2. Criação da VendaAcessorio (você já deve ter isso)
        $vendaAcessorio = new VendaAcessorio();
        $vendaAcessorio->cliente_id = $validatedData['cliente_id'];
        // A data_venda já vem formatada do formulário create (Y-m-d),
        // mas se vier como d/m/Y, precisa converter: Carbon::createFromFormat('d/m/Y', $validatedData['data_venda'])->toDateString();
        // Para datetime, se o campo for datetime:
        $vendaAcessorio->data_venda = Carbon::parse($validatedData['data_venda'])->toDateTimeString();
        $vendaAcessorio->valor_total = 0; // Será calculado abaixo
        $vendaAcessorio->forma_pagamento = $validatedData['forma_pagamento'];
        $vendaAcessorio->observacoes = $validatedData['observacoes'];
        $vendaAcessorio->user_id = Auth::id(); // Usuário que registrou a venda
        $vendaAcessorio->save();

        // 3. Processamento dos Itens da Venda e Cálculo do Valor Total (você já deve ter isso)
        $valorTotalCalculado = 0;
        foreach ($validatedData['itens'] as $itemVendaData) {
            $itemEstoque = Estoque::findOrFail($itemVendaData['item_estoque_id']);
            $quantidadeVendida = (int) $itemVendaData['quantidade'];
            $precoUnitario = (float) $itemVendaData['preco_unitario'];
            $descontoItem = (float) ($itemVendaData['desconto'] ?? 0);

            // Validação de estoque (IMPORTANTE)
            if ($itemEstoque->quantidade < $quantidadeVendida) {
                // DB::rollBack(); // Se estiver usando transação
                return redirect()->back()
                    ->with('error', "Estoque insuficiente para o item '{$itemEstoque->nome}'. Disponível: {$itemEstoque->quantidade}, Solicitado: {$quantidadeVendida}.")
                    ->withInput();
            }

            $subtotalItem = ($quantidadeVendida * $precoUnitario) - $descontoItem;
            $valorTotalCalculado += $subtotalItem;

            // Adicionar item à tabela pivot venda_acessorio_estoque
            $vendaAcessorio->itensEstoque()->attach($itemEstoque->id, [
                'quantidade' => $quantidadeVendida,
                'preco_unitario_venda' => $precoUnitario,
                'desconto' => $descontoItem,
            ]);

            // Decrementar estoque
            $itemEstoque->decrement('quantidade', $quantidadeVendida);

            // Registrar Saída de Estoque (se você tiver essa lógica separada também)
            // SaidaEstoque::create([...]);
        }

        // Atualizar o valor total da venda
        $vendaAcessorio->valor_total = $valorTotalCalculado;
        $vendaAcessorio->save();


        // 4. ***** NOVA LÓGICA: REGISTRAR MOVIMENTAÇÃO NO CAIXA *****
        $caixaAberto = Caixa::getCaixaAbertoAtual();

        if ($caixaAberto) {
            if ($vendaAcessorio->valor_total > 0) { // Só registra se houver valor
                MovimentacaoCaixa::create([
                    'caixa_id' => $caixaAberto->id,
                    'usuario_id' => Auth::id(), // Usuário que fez a venda/registrou no caixa
                    'tipo' => 'ENTRADA',
                    'descricao' => "Recebimento Venda de Acessório #" . $vendaAcessorio->id,
                    'valor' => $vendaAcessorio->valor_total,
                    'forma_pagamento' => $vendaAcessorio->forma_pagamento, // Assume que a forma de pagamento da venda é a do caixa
                    'referencia_id' => $vendaAcessorio->id,
                    'referencia_tipo' => VendaAcessorio::class, // Eloquent usará o namespace completo
                    'data_movimentacao' => Carbon::now(), // Ou $vendaAcessorio->data_venda se preferir
                    'observacoes' => 'Venda registrada pelo sistema.',
                ]);
            }
        } else {
            // Opcional: Lidar com o caso de não haver caixa aberto.
            // Poderia ser um warning na sessão, um log, ou impedir a venda se o caixa aberto for obrigatório.
            // Por enquanto, apenas não registra no caixa se não houver um aberto.
            // Session::flash('warning', 'Nenhum caixa aberto. A movimentação financeira desta venda não foi registrada no caixa.');
        }
        // ***** FIM DA NOVA LÓGICA *****


        // ---- INÍCIO DA LÓGICA DE TRANSAÇÃO (OPCIONAL MAS RECOMENDADO) ----
        // DB::commit();
        return redirect()->route('vendas-acessorios.show', $vendaAcessorio->id)
            ->with('success', 'Venda de acessório registrada com sucesso!');
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     // Log::error("Erro ao registrar venda de acessório: " . $e->getMessage());
        //     return redirect()->back()
        //                      ->with('error', 'Erro ao registrar a venda. Tente novamente. Detalhes: ' . $e->getMessage())
        //                      ->withInput();
        // }
        // ---- FIM DA LÓGICA DE TRANSAÇÃO ----
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
        // Carrega os itens vendidos e o cliente para exibir no formulário
        $vendas_acessorio->load('itensVendidos', 'cliente');

        // Você também pode carregar as devoluções existentes para essa venda, se houver
        $vendas_acessorio->load('devolucoesVendas');

        // Passa a venda para a view
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
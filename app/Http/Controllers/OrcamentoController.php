<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\OrcamentoItem;
use App\Models\AtendimentoServico;
use App\Models\Cliente;
use App\Models\Estoque;
use App\Models\User;
use App\Models\Atendimento;
use App\Models\SaidaEstoque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Notifications\OrcamentoParaClienteNotification; // Importar a nova notificação
use Illuminate\Support\Facades\Notification; // Para enviar a notificação
use Illuminate\Support\Facades\Gate;

class OrcamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Orcamento::with(['cliente', 'criadoPor'])->latest();

        if ($request->filled('filtro_status')) {
            $query->where('status', $request->input('filtro_status'));
        }

        if ($request->filled('filtro_cliente_id')) {
            $query->where('cliente_id', $request->input('filtro_cliente_id'));
        } elseif ($request->filled('filtro_cliente_nome_avulso')) {
            $query->whereNull('cliente_id')
                ->where('nome_cliente_avulso', 'like', '%' . $request->input('filtro_cliente_nome_avulso') . '%');
        }

        if ($request->filled('data_emissao_de') && $request->filled('data_emissao_ate')) {
            try {
                $dataDe = Carbon::parse($request->input('data_emissao_de'))->startOfDay();
                $dataAte = Carbon::parse($request->input('data_emissao_ate'))->endOfDay();
                if ($dataDe->lte($dataAte)) {
                    $query->whereBetween('data_emissao', [$dataDe, $dataAte]);
                }
            } catch (\Exception $e) {
                Log::warning('Data inválida no filtro de orçamento: ' . $e->getMessage());
            }
        } elseif ($request->filled('data_emissao_de')) {
            try {
                $query->where('data_emissao', '>=', Carbon::parse($request->input('data_emissao_de'))->startOfDay());
            } catch (\Exception $e) {
            }
        } elseif ($request->filled('data_emissao_ate')) {
            try {
                $query->where('data_emissao', '<=', Carbon::parse($request->input('data_emissao_ate'))->endOfDay());
            } catch (\Exception $e) {
            }
        }

        if ($request->filled('filtro_criado_por_id')) {
            $query->where('criado_por_id', $request->input('filtro_criado_por_id'));
        }

        $orcamentos = $query->paginate(15)->appends($request->query());

        $usuariosParaFiltro = User::whereIn('tipo_usuario', ['admin', 'tecnico', 'atendente'])
            ->orderBy('name')->get(['id', 'name']);
        $statusParaFiltro = Orcamento::getPossibleStatuses();

        return view('orcamentos.index', compact(
            'orcamentos',
            'usuariosParaFiltro',
            'statusParaFiltro',
            'request'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $clientes = Cliente::orderBy('nome_completo')->get();
        $usuarios = User::whereIn('tipo_usuario', ['admin', 'tecnico', 'atendente'])->orderBy('name')->get();
        $statusOrcamento = Orcamento::getPossibleStatuses();
        $tiposDesconto = ['percentual' => 'Percentual (%)', 'fixo' => 'Valor Fixo (R$)'];

        return view('orcamentos.create', compact(
            'clientes',
            'usuarios',
            'statusOrcamento',
            'tiposDesconto'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'nome_cliente_avulso' => 'required_without:cliente_id|nullable|string|max:255',
            'telefone_cliente_avulso' => 'nullable|string|max:20',
            'email_cliente_avulso' => 'nullable|email|max:255',
            'descricao_aparelho' => 'required|string|max:255',
            'problema_relatado_cliente' => 'required|string',
            'data_emissao' => 'required|date',
            'validade_dias' => 'nullable|integer|min:0',
            'tempo_estimado_servico' => 'nullable|string|max:100',
            'observacoes_internas' => 'nullable|string',
            'termos_condicoes' => 'nullable|string',
            'desconto_tipo' => ['nullable', Rule::in(['percentual', 'fixo'])],
            'desconto_valor' => 'nullable|numeric|min:0',
            'itens' => 'required|array|min:1',
            'itens.*.tipo_item' => ['required', Rule::in(['peca', 'servico'])],
            'itens.*.estoque_id' => 'required_if:itens.*.tipo_item,peca|nullable|exists:estoque,id',
            'itens.*.descricao_item_manual' => 'required_if:itens.*.tipo_item,servico|nullable|string|max:255',
            'itens.*.quantidade' => 'required|integer|min:1',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
        ], [
            'nome_cliente_avulso.required_without' => 'O nome do cliente é obrigatório se nenhum cliente cadastrado for selecionado.',
            'itens.required' => 'É necessário adicionar pelo menos um item ao orçamento.',
            'itens.*.tipo_item.required' => 'O tipo do item (peça/serviço) é obrigatório.',
            'itens.*.estoque_id.required_if' => 'Uma peça do estoque deve ser selecionada se o tipo do item for "peça".',
            'itens.*.descricao_item_manual.required_if' => 'A descrição do serviço é obrigatória se o tipo do item for "serviço".',
            'itens.*.quantidade.min' => 'A quantidade de cada item deve ser pelo menos 1.',
            'itens.*.valor_unitario.min' => 'O valor unitário de cada item não pode ser negativo.',
            'desconto_valor.min' => 'O valor do desconto não pode ser negativo.',
        ]);

        DB::beginTransaction();
        try {
            $orcamento = new Orcamento();
            $orcamento->fill($request->only([
                'cliente_id',
                'nome_cliente_avulso',
                'telefone_cliente_avulso',
                'email_cliente_avulso',
                'descricao_aparelho',
                'problema_relatado_cliente',
                'data_emissao',
                'validade_dias',
                'tempo_estimado_servico',
                'observacoes_internas',
                'termos_condicoes',
                'desconto_tipo',
                'desconto_valor'
            ]));

            if ($request->filled('validade_dias')) {
                $diasValidade = (int) $request->validade_dias;
                if ($diasValidade > 0) {
                    $orcamento->data_validade = Carbon::parse($request->data_emissao)->addDays($diasValidade);
                } else {
                    $orcamento->data_validade = null;
                }
            } else {
                $orcamento->data_validade = null;
            }

            $orcamento->status = 'Em Elaboração';
            $orcamento->criado_por_id = Auth::id();
            $valorTotalServicos = 0;
            $valorTotalPecas = 0;
            $orcamento->save();

            foreach ($request->itens as $itemData) {
                $subtotalItem = $itemData['quantidade'] * $itemData['valor_unitario'];
                $orcamentoItem = new OrcamentoItem([
                    'tipo_item' => $itemData['tipo_item'],
                    'estoque_id' => $itemData['tipo_item'] == 'peca' ? $itemData['estoque_id'] : null,
                    'descricao_item_manual' => $itemData['tipo_item'] == 'servico' ? $itemData['descricao_item_manual'] : ($itemData['tipo_item'] == 'peca' && isset($itemData['descricao_item_manual']) ? $itemData['descricao_item_manual'] : null),
                    'quantidade' => $itemData['quantidade'],
                    'valor_unitario' => $itemData['valor_unitario'],
                    'subtotal_item' => $subtotalItem,
                ]);
                $orcamento->itens()->save($orcamentoItem);

                if ($itemData['tipo_item'] == 'servico') {
                    $valorTotalServicos += $subtotalItem;
                } else {
                    $valorTotalPecas += $subtotalItem;
                }
            }

            $orcamento->valor_total_servicos = $valorTotalServicos;
            $orcamento->valor_total_pecas = $valorTotalPecas;
            $orcamento->calcularValorFinal();
            $orcamento->save();

            DB::commit();
            return redirect()->route('orcamentos.show', $orcamento->id)->with('success', 'Orçamento criado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar orçamento: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Ocorreu um erro ao criar o orçamento: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Orcamento $orcamento)
    {
        $orcamento->load(['cliente', 'criadoPor', 'aprovadoPor', 'atendimentoConvertido', 'itens.estoque']);
        return view('orcamentos.show', compact('orcamento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Orcamento $orcamento)
    {
        $orcamentoIdSessao = session()->get('orcamento_edit_cliente_id');
        $statusOrcamentoAtual = $orcamento->status;
        $clienteIdOrcamentoAtual = $orcamento->cliente_id;

        Log::info("OrcamentoController@edit: Entrando para Orçamento ID {$orcamento->id}");
        Log::info(" - ID na Sessão ('orcamento_edit_cliente_id'): " . ($orcamentoIdSessao ?? 'NULO'));
        Log::info(" - Status Atual do Orçamento: {$statusOrcamentoAtual}");
        Log::info(" - Cliente ID Atual do Orçamento: " . ($clienteIdOrcamentoAtual ?? 'NULO'));

        $permitirEdicaoCompleta = $statusOrcamentoAtual === 'Em Elaboração';
        $permitirEdicaoApenasCliente = ($orcamentoIdSessao === $orcamento->id) &&
            ($statusOrcamentoAtual === 'Aprovado') &&
            !$clienteIdOrcamentoAtual;

        Log::info(" - Permitir Edição Completa? " . ($permitirEdicaoCompleta ? 'SIM' : 'NÃO'));
        Log::info(" - Permitir Edição Apenas Cliente? " . ($permitirEdicaoApenasCliente ? 'SIM' : 'NÃO'));

        if (!$permitirEdicaoCompleta && !$permitirEdicaoApenasCliente) {
            Log::warning("OrcamentoController@edit: Bloqueando edição para Orçamento ID {$orcamento->id}. Status: {$statusOrcamentoAtual}, Flag Edição Cliente: " . ($permitirEdicaoApenasCliente ? 'SIM' : 'NÃO'));
            if ($orcamentoIdSessao === $orcamento->id) {
                session()->forget('orcamento_edit_cliente_id');
                Log::info(" - Flag de sessão 'orcamento_edit_cliente_id' para ID {$orcamento->id} removida.");
            }
            return redirect()->route('orcamentos.show', $orcamento->id)
                ->with('error', 'Este orçamento não pode ser editado no status atual (status: ' . $statusOrcamentoAtual . '), a menos que seja para vincular um cliente antes da conversão para OS.');
        }

        $clientes = Cliente::orderBy('nome_completo')->get();
        $usuarios = User::whereIn('tipo_usuario', ['admin', 'tecnico', 'atendente'])->orderBy('name')->get();
        $statusOrcamentoSelect = Orcamento::getPossibleStatuses();
        $tiposDesconto = ['percentual' => 'Percentual (%)', 'fixo' => 'Valor Fixo (R$)'];
        $orcamento->load('itens.estoque');
        $edicaoApenasCliente = $permitirEdicaoApenasCliente;

        Log::info(" - Passando para view edit: edicaoApenasCliente = " . ($edicaoApenasCliente ? 'true' : 'false'));

        return view('orcamentos.edit', compact(
            'orcamento',
            'clientes',
            'usuarios',
            'statusOrcamentoSelect',
            'tiposDesconto',
            'edicaoApenasCliente'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Orcamento $orcamento)
    {
        // Verifica se a intenção original, ao vir para a edição, era apenas adicionar um cliente
        // a um orçamento que já estava APROVADO e não tinha cliente_id.
        $intencaoOriginalEraAdicionarCliente = session()->get('orcamento_edit_cliente_id') === $orcamento->id &&
            $orcamento->status === 'Aprovado';

        Log::info("OrcamentoController@update: Entrando para Orçamento ID {$orcamento->id}");
        Log::info(" - ID na Sessão ('orcamento_edit_cliente_id'): " . (session()->get('orcamento_edit_cliente_id') ?? 'NULO'));
        Log::info(" - Status Atual do Orçamento: {$orcamento->status}");
        Log::info(" - Intenção Original Era Adicionar Cliente? " . ($intencaoOriginalEraAdicionarCliente ? 'SIM' : 'NÃO'));

        // Se NÃO for o cenário especial de adicionar cliente E o status NÃO for 'Em Elaboração',
        // então bloqueia a edição completa.
        if (!$intencaoOriginalEraAdicionarCliente && $orcamento->status !== 'Em Elaboração') {
            Log::warning("OrcamentoController@update: Bloqueando atualização para Orçamento ID {$orcamento->id}. Status: {$orcamento->status}. Não é edição apenas de cliente nem está 'Em Elaboração'.");
            return redirect()->route('orcamentos.show', $orcamento->id)
                ->with('error', 'Orçamentos já processados ou em status avançado não podem ser totalmente editados desta forma.');
        }

        // Define as regras de validação base (apenas para cliente se for o caso especial)
        $regrasValidacao = [
            'cliente_id' => ($intencaoOriginalEraAdicionarCliente ? 'required' : 'nullable') . '|exists:clientes,id',
        ];
        // Define os campos que podem ser preenchidos (inicialmente, apenas cliente_id)
        $camposParaPreencher = ['cliente_id'];


        if (!$intencaoOriginalEraAdicionarCliente) {
            // Se for uma edição completa (orçamento estava 'Em Elaboração')
            // Adiciona todas as outras regras de validação
            $regrasValidacao = array_merge($regrasValidacao, [
                'nome_cliente_avulso' => 'required_without:cliente_id|nullable|string|max:255',
                'telefone_cliente_avulso' => 'nullable|string|max:20',
                'email_cliente_avulso' => 'nullable|email|max:255',
                'descricao_aparelho' => 'required|string|max:255',
                'problema_relatado_cliente' => 'required|string',
                'data_emissao' => 'required|date',
                'validade_dias' => 'nullable|integer|min:0',
                'status' => ['required', Rule::in(Orcamento::getPossibleStatuses())], // Permite mudar o status se estiver 'Em Elaboração'
                'tempo_estimado_servico' => 'nullable|string|max:100',
                'observacoes_internas' => 'nullable|string',
                'termos_condicoes' => 'nullable|string',
                'desconto_tipo' => ['nullable', Rule::in(['percentual', 'fixo'])],
                'desconto_valor' => 'nullable|numeric|min:0',
                'itens' => 'required|array|min:1',
                'itens.*.id' => 'nullable|integer|exists:orcamento_items,id,orcamento_id,' . $orcamento->id,
                'itens.*.tipo_item' => ['required', Rule::in(['peca', 'servico'])],
                'itens.*.estoque_id' => 'required_if:itens.*.tipo_item,peca|nullable|exists:estoque,id',
                'itens.*.descricao_item_manual' => 'required_if:itens.*.tipo_item,servico|nullable|string|max:255',
                'itens.*.quantidade' => 'required|integer|min:1',
                'itens.*.valor_unitario' => 'required|numeric|min:0',
            ]);
            // Adiciona os outros campos que podem ser preenchidos
            $camposParaPreencher = array_merge($camposParaPreencher, [
                'nome_cliente_avulso',
                'telefone_cliente_avulso',
                'email_cliente_avulso',
                'descricao_aparelho',
                'problema_relatado_cliente',
                'data_emissao',
                'validade_dias',
                'status',
                'tempo_estimado_servico',
                'observacoes_internas',
                'termos_condicoes',
                'desconto_tipo',
                'desconto_valor'
            ]);
        } else {
            // Se a intenção é apenas adicionar cliente, os campos avulsos não são 'required_without'
            // mas ainda podem ser enviados se o usuário os preencheu antes de selecionar um cliente cadastrado.
            // O ideal é que o JS da view limpe os campos avulsos quando um cliente é selecionado.
            $regrasValidacao['nome_cliente_avulso'] = 'nullable|string|max:255';
            $regrasValidacao['telefone_cliente_avulso'] = 'nullable|string|max:20';
            $regrasValidacao['email_cliente_avulso'] = 'nullable|email|max:255';
            // Adiciona campos avulsos aos que podem ser preenchidos
            $camposParaPreencher = array_merge($camposParaPreencher, ['nome_cliente_avulso', 'telefone_cliente_avulso', 'email_cliente_avulso']);
        }

        // Mensagens de validação personalizadas
        $mensagens = [
            'cliente_id.required' => 'Um cliente cadastrado é necessário para prosseguir com a conversão.',
            'nome_cliente_avulso.required_without' => 'O nome do cliente é obrigatório se nenhum cliente cadastrado for selecionado (em edição normal).',
            'itens.required' => 'É necessário adicionar pelo menos um item ao orçamento (em edição normal).',
            'itens.*.id.exists' => 'Um dos itens editados não pertence a este orçamento ou não existe (em edição normal).',
        ];

        $validatedData = $request->validate($regrasValidacao, $mensagens);

        DB::beginTransaction();
        try {
            // Pega apenas os campos que foram validados e permitidos para preenchimento
            $orcamentoData = [];
            foreach ($camposParaPreencher as $campo) {
                if ($request->has($campo) || ($campo === 'cliente_id' && $request->filled('cliente_id'))) { // cliente_id é especial
                    $orcamentoData[$campo] = $request->input($campo);
                }
            }

            // Se um cliente_id foi efetivamente selecionado e enviado, limpa os campos de cliente avulso.
            if ($request->filled('cliente_id')) {
                $orcamentoData['nome_cliente_avulso'] = null;
                $orcamentoData['telefone_cliente_avulso'] = null;
                $orcamentoData['email_cliente_avulso'] = null;
            }

            // Apenas atualiza data_validade e status se NÃO for edição apenas de cliente
            if (!$intencaoOriginalEraAdicionarCliente) {
                if ($request->filled('validade_dias')) {
                    $diasValidade = (int) $request->validade_dias;
                    $orcamentoData['data_validade'] = ($diasValidade > 0) ? Carbon::parse($request->data_emissao)->addDays($diasValidade) : null;
                } else {
                    // Se validade_dias não foi enviado ou está vazio, mas data_emissao foi,
                    // e não estamos no modo "apenas cliente", então data_validade deve ser null.
                    if ($request->has('data_emissao')) { // Garante que só anula se data_emissao foi parte da edição
                        $orcamentoData['data_validade'] = null;
                    }
                }
                // O status já está em $orcamentoData se foi validado e é parte de $camposParaPreencher
            }
            // Se for $intencaoOriginalEraAdicionarCliente, não mexemos no status nem na data de validade aqui.
            // O status permanece 'Aprovado'.

            $orcamento->update($orcamentoData);

            // Lógica para atualizar/adicionar/remover itens SÓ SE NÃO FOR EDIÇÃO APENAS DE CLIENTE
            if (!$intencaoOriginalEraAdicionarCliente) {
                $valorTotalServicos = 0;
                $valorTotalPecas = 0;
                $idsItensMantidosOuAdicionados = [];

                if ($request->has('itens')) {
                    foreach ($request->itens as $itemData) {
                        $subtotalItem = $itemData['quantidade'] * $itemData['valor_unitario'];
                        $dadosDoItem = [
                            'tipo_item' => $itemData['tipo_item'],
                            'estoque_id' => $itemData['tipo_item'] == 'peca' ? $itemData['estoque_id'] : null,
                            'descricao_item_manual' => $itemData['tipo_item'] == 'servico' ? $itemData['descricao_item_manual'] : ($itemData['tipo_item'] == 'peca' && isset($itemData['descricao_item_manual']) ? $itemData['descricao_item_manual'] : null),
                            'quantidade' => $itemData['quantidade'],
                            'valor_unitario' => $itemData['valor_unitario'],
                            'subtotal_item' => $subtotalItem,
                        ];

                        if (isset($itemData['id']) && $itemData['id']) {
                            $itemExistente = OrcamentoItem::find($itemData['id']);
                            if ($itemExistente && $itemExistente->orcamento_id == $orcamento->id) {
                                $itemExistente->update($dadosDoItem);
                                $idsItensMantidosOuAdicionados[] = $itemExistente->id;
                            }
                        } else {
                            $novoItem = $orcamento->itens()->create($dadosDoItem);
                            $idsItensMantidosOuAdicionados[] = $novoItem->id;
                        }

                        if ($dadosDoItem['tipo_item'] == 'servico') {
                            $valorTotalServicos += $subtotalItem;
                        } else {
                            $valorTotalPecas += $subtotalItem;
                        }
                    }
                }
                // Remove itens que não estão mais na lista (foram removidos no formulário)
                $orcamento->itens()->whereNotIn('id', $idsItensMantidosOuAdicionados)->delete();

                // Atualiza os totais do orçamento
                $orcamento->valor_total_servicos = $valorTotalServicos;
                $orcamento->valor_total_pecas = $valorTotalPecas;
                $orcamento->calcularValorFinal(); // Recalcula sub_total e valor_final
            }
            // Fim da lógica de itens

            $orcamento->save(); // Salva o orçamento principal com as alterações
            DB::commit();

            // Limpa a flag da sessão APÓS o commit bem-sucedido
            if ($intencaoOriginalEraAdicionarCliente) {
                session()->forget('orcamento_edit_cliente_id');
                Log::info(" - Cliente ID {$orcamento->cliente_id} associado ao Orçamento #{$orcamento->id} via update. Flag 'orcamento_edit_cliente_id' removida.");
                // Redireciona para a página SHOW com uma mensagem para o usuário tentar converter novamente.
                return redirect()->route('orcamentos.show', $orcamento->id)
                    ->with('success', 'Cliente associado ao orçamento com sucesso! Você já pode tentar convertê-lo em Ordem de Serviço.');
            }

            session()->forget('orcamento_edit_cliente_id'); // Limpa a flag em caso de edição normal também
            Log::info(" - Orçamento ID {$orcamento->id} atualizado (edição normal). Redirecionando para show.");
            return redirect()->route('orcamentos.show', $orcamento->id)->with('success', 'Orçamento atualizado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error("Erro de VALIDAÇÃO ao atualizar orçamento #{$orcamento->id}: ", $e->errors());
            if ($intencaoOriginalEraAdicionarCliente) { // Recoloca a flag se a validação falhar neste fluxo
                session()->put('orcamento_edit_cliente_id', $orcamento->id);
                Log::info(" - Validação falhou, recolocando flag 'orcamento_edit_cliente_id' para ID {$orcamento->id}.");
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            if ($intencaoOriginalEraAdicionarCliente) { // Recoloca a flag se der erro neste fluxo
                session()->put('orcamento_edit_cliente_id', $orcamento->id);
                Log::info(" - Exceção ocorreu, recolocando flag 'orcamento_edit_cliente_id' para ID {$orcamento->id}.");
            }
            Log::error('Erro GERAL ao atualizar orçamento #' . $orcamento->id . ': ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Ocorreu um erro ao atualizar o orçamento: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * "Cancela" o orçamento.
     */
    public function destroy(Orcamento $orcamento)
    {
        if (in_array($orcamento->status, ['Convertido em OS', 'Aprovado', 'Reprovado', 'Cancelado'])) {
            return redirect()->route('orcamentos.show', $orcamento->id)->with('error', 'Este orçamento não pode ser cancelado neste status.');
        }
        $orcamento->status = 'Cancelado';
        $orcamento->data_cancelamento = now();
        $orcamento->save();
        return redirect()->route('orcamentos.index')->with('success', 'Orçamento cancelado com sucesso.');
    }

    public function marcarComoAguardando(Request $request, Orcamento $orcamento)
    {
        if ($orcamento->status !== 'Em Elaboração') {
            return redirect()->route('orcamentos.show', $orcamento->id)
                ->with('error', 'Apenas orçamentos "Em Elaboração" podem ser enviados para aprovação.');
        }
        $orcamento->status = 'Aguardando Aprovação';
        $orcamento->save();
        // TODO: Adicionar lógica de envio de e-mail/notificação aqui
        return redirect()->route('orcamentos.show', $orcamento->id)
            ->with('success', 'Orçamento finalizado e aguardando aprovação do cliente.');
    }

    public function aprovarOrcamento(Request $request, Orcamento $orcamento)
    {
        $usuarioLogado = Auth::user();

        // Usar o Gate que define quem pode manipular orçamentos neste nível
        // Ex: 'gerenciar-orcamentos' ou um mais específico como 'aprovar-orcamento'
        // Por agora, vamos usar um exemplo genérico:
        if (Gate::denies('is-admin-or-atendente')) { // Apenas Admin ou Atendente podem registrar aprovação
            return redirect()->route('orcamentos.show', $orcamento->id)
                ->with('error', 'Você não tem permissão para aprovar este orçamento.');
        }

        // Usar o método do Model para verificar se a transição é permitida
        if (!$orcamento->canTransitionTo('Aprovado', $usuarioLogado)) {
            return redirect()->route('orcamentos.show', $orcamento->id)
                ->with('error', 'Este orçamento não pode ser aprovado a partir do status atual (' . $orcamento->status . ') ou você não tem permissão para esta transição específica.');
        }

        // Se chegou aqui, a transição é permitida
        $orcamento->status = 'Aprovado';
        $orcamento->data_aprovacao = Carbon::now();
        $orcamento->aprovado_por_id = $usuarioLogado->id;
        $orcamento->save();

        return redirect()->route('orcamentos.show', $orcamento->id)
            ->with('success', 'Orçamento aprovado com sucesso!');
    }

    public function reprovarOrcamento(Request $request, Orcamento $orcamento)
    {
        if ($orcamento->status !== 'Aguardando Aprovação') {
            return redirect()->route('orcamentos.show', $orcamento->id)
                ->with('error', 'Apenas orçamentos "Aguardando Aprovação" podem ser reprovados.');
        }
        $orcamento->status = 'Reprovado';
        $orcamento->data_reprovacao = Carbon::now();
        $orcamento->save();
        return redirect()->route('orcamentos.show', $orcamento->id)
            ->with('success', 'Orçamento reprovado.');
    }

    public function converterEmOs(Request $request, Orcamento $orcamento)
    {
        Log::info("OrcamentoController@converterEmOs: Iniciando conversão para Orçamento ID {$orcamento->id}");

        // Validações iniciais (status, cliente_id, etc.)
        if ($orcamento->status !== 'Aprovado') {
            Log::warning(" - Bloqueado: Status não é 'Aprovado'. Status atual: {$orcamento->status}");
            return redirect()->route('orcamentos.show', $orcamento->id)
                ->with('error', 'Apenas orçamentos com status "Aprovado" podem ser convertidos.');
        }
        if ($orcamento->atendimento_id_convertido) {
            Log::info(" - Bloqueado: Orçamento já convertido para OS #{$orcamento->atendimento_id_convertido}.");
            return redirect()->route('atendimentos.show', $orcamento->atendimento_id_convertido)
                ->with('info', 'Este orçamento já foi convertido na OS #' . $orcamento->atendimento_id_convertido);
        }
        if (!$orcamento->cliente_id) {
            // DB::rollBack(); // Não há transação DB iniciada neste ponto ainda.
            session()->put('orcamento_edit_cliente_id', $orcamento->id);
            Log::warning(" - Bloqueado: Cliente ID nulo. Setando flag 'orcamento_edit_cliente_id' para {$orcamento->id} e redirecionando para edit.");
            return redirect()->route('orcamentos.edit', $orcamento->id)
                ->with('error', 'Para converter em OS, associe este orçamento a um cliente cadastrado.');
        }

        DB::beginTransaction();
        try {
            $novoAtendimento = new Atendimento();
            $novoAtendimento->cliente_id = $orcamento->cliente_id;
            $novoAtendimento->descricao_aparelho = $orcamento->descricao_aparelho;
            $novoAtendimento->problema_relatado = "Problema relatado no orçamento #" . $orcamento->id . ":\n" . $orcamento->problema_relatado_cliente;

            // Laudo técnico inicial pode ser uma referência ao orçamento ou observações gerais
            // Os serviços detalhados irão para a tabela 'atendimento_servicos'
            $novoAtendimento->laudo_tecnico = "Serviços e peças conforme Orçamento #" . $orcamento->id . ".";
            if(!empty($orcamento->observacoes_internas)){ // Adiciona observações internas do orçamento, se houver
                 $novoAtendimento->observacoes = "Observações do Orçamento #".$orcamento->id.":\n".$orcamento->observacoes_internas;
            }


            $novoAtendimento->data_entrada = Carbon::now();
            $novoAtendimento->status = 'Em diagnóstico'; // Ou 'Aguardando peça' ou 'Em manutenção' dependendo do fluxo
            $novoAtendimento->tecnico_id = $orcamento->criado_por_id ?? Auth::id();

            // O valor_servico do Atendimento será a SOMA dos AtendimentoServico.
            // O desconto_servico do Atendimento será o desconto GLOBAL da OS.
            // Estes serão calculados e atualizados após criar os AtendimentoServico.
            $novoAtendimento->valor_servico = 0; // Inicializa, será recalculado
            $novoAtendimento->desconto_servico = 0; // Desconto da OS inicia zerado
            $novoAtendimento->status_pagamento = 'Pendente';

            // Gerar código de consulta único para a OS
            $anoAtual = now()->year;
            $codigoUnicoOs = false;
            $novoCodigoConsultaOs = '';
            while (!$codigoUnicoOs) {
                $parteNumericaOs = random_int(10000, 99999);
                $novoCodigoConsultaOs = $parteNumericaOs . '-' . $anoAtual;
                if (!Atendimento::where('codigo_consulta', $novoCodigoConsultaOs)->exists()) {
                    $codigoUnicoOs = true;
                }
            }
            $novoAtendimento->codigo_consulta = $novoCodigoConsultaOs;
            $novoAtendimento->save(); // Salva o Atendimento principal para obter o ID
            Log::info(" - Nova OS #{$novoAtendimento->id} criada com sucesso. Código Consulta OS: {$novoAtendimento->codigo_consulta}.");

            $totalServicosCalculadoParaOS = 0;

            // Iterar sobre os itens do orçamento para criar SaidasEstoque (para peças)
            // E AtendimentoServico (para serviços)
            foreach ($orcamento->itens as $itemOrcado) {
                if ($itemOrcado->tipo_item == 'peca' && $itemOrcado->estoque_id) {
                    $pecaEstoque = Estoque::find($itemOrcado->estoque_id);
                    if ($pecaEstoque) {
                        if ($pecaEstoque->quantidade < $itemOrcado->quantidade) {
                            DB::rollBack();
                            Log::error(" - Erro de estoque para peça '{$pecaEstoque->nome}' (ID Estoque: {$pecaEstoque->id}) na conversão do Orçamento #{$orcamento->id}. Disponível: {$pecaEstoque->quantidade}, Solicitado: {$itemOrcado->quantidade}.");
                            return redirect()->route('orcamentos.show', $orcamento->id)
                                ->with('error', "Estoque insuficiente para a peça '{$pecaEstoque->nome}'. Disponível: {$pecaEstoque->quantidade}, Solicitado: {$itemOrcado->quantidade}.");
                        }
                        SaidaEstoque::create([
                            'estoque_id' => $pecaEstoque->id,
                            'atendimento_id' => $novoAtendimento->id,
                            'quantidade' => $itemOrcado->quantidade,
                            'data_saida' => Carbon::now(),
                            'observacoes' => 'Saída automática via conversão do orçamento #' . $orcamento->id . ' para OS #' . $novoAtendimento->id,
                        ]);
                        $pecaEstoque->decrement('quantidade', $itemOrcado->quantidade);
                        Log::info(" - Saída de estoque de {$itemOrcado->quantidade} unidade(s) da peça '{$pecaEstoque->nome}' (ID Estoque: {$pecaEstoque->id}) registrada para OS #{$novoAtendimento->id}.");
                    } else {
                        // Tratar erro: peça do orçamento não encontrada no estoque
                        DB::rollBack();
                        Log::error(" - Peça com ID Estoque {$itemOrcado->estoque_id} (do orçamento #{$orcamento->id}) não encontrada no estoque durante a conversão para OS.");
                        return redirect()->route('orcamentos.show', $orcamento->id)
                            ->with('error', "Peça ID {$itemOrcado->estoque_id} (listada no orçamento) não foi encontrada no estoque. Verifique o cadastro da peça.");
                    }
                } elseif ($itemOrcado->tipo_item == 'servico') {
                    // ***** AQUI CRIAMOS O AtendimentoServico *****
                    $subtotalServicoItem = (float)$itemOrcado->quantidade * (float)$itemOrcado->valor_unitario;
                    AtendimentoServico::create([
                        'atendimento_id' => $novoAtendimento->id,
                        'descricao_servico' => $itemOrcado->descricao_item_manual,
                        'quantidade' => $itemOrcado->quantidade,
                        'valor_unitario' => $itemOrcado->valor_unitario,
                        'subtotal_servico' => $subtotalServicoItem,
                    ]);
                    $totalServicosCalculadoParaOS += $subtotalServicoItem;
                    Log::info(" - Serviço Detalhado '{$itemOrcado->descricao_item_manual}' adicionado à OS #{$novoAtendimento->id}. Valor: {$subtotalServicoItem}");
                }
            }

            // Atualizar o campo valor_servico do Atendimento principal com a soma dos serviços detalhados
            // Se houver um desconto GERAL no orçamento, precisamos decidir como aplicá-lo.
            // Opção 1: O valor_servico da OS já é líquido de descontos do orçamento.
            // Opção 2: O valor_servico da OS é a soma bruta, e o desconto_servico da OS reflete o desconto proporcional.
            // A última versão do método usava:
            // $valorMaoDeObraOS = $orcamento->valor_final - $orcamento->valor_total_pecas;
            // $novoAtendimento->valor_servico = max(0, $valorMaoDeObraOS);
            // $novoAtendimento->desconto_servico = 0;

            // Agora, com $totalServicosCalculadoParaOS (soma dos itens de serviço do orçamento):
            $novoAtendimento->valor_servico = $totalServicosCalculadoParaOS;

            // Como aplicar o desconto do orçamento?
            // Se o orçamento tinha um desconto que afetava o valor_final,
            // e $valor_final = (servicos_brutos + pecas_brutas) - desconto_orcamento
            // E o valor_servico da OS agora é a soma dos servicos_brutos do orçamento,
            // precisamos aplicar um desconto_servico na OS se o desconto do orçamento era para serviços.
            // Para simplificar por agora: se o valor_final do orçamento é menor que (soma_servicos_orc + soma_pecas_orc),
            // a diferença é o desconto total do orçamento.
            $subtotalBrutoOrcamento = $orcamento->valor_total_servicos + $orcamento->valor_total_pecas;
            $descontoTotalNoOrcamento = $subtotalBrutoOrcamento - $orcamento->valor_final;

            if ($descontoTotalNoOrcamento > 0 && $orcamento->valor_total_servicos > 0) {
                // Se o desconto no orçamento era apenas sobre serviços (ou proporcionalmente)
                // E o orçamento tinha apenas serviços, o desconto é totalmente para os serviços.
                if ($orcamento->valor_total_pecas == 0) {
                     $novoAtendimento->desconto_servico = $descontoTotalNoOrcamento;
                } else if ($orcamento->valor_total_servicos > 0) {
                    // Se havia peças e serviços, e queremos aplicar o desconto proporcionalmente
                    // apenas aos serviços da OS.
                    // Essa parte pode ser complexa. Uma forma mais simples é se o `valor_total_servicos` do orçamento já era líquido.
                    // Vamos assumir por ora que o `valor_total_servicos` do ORÇAMENTO era o valor bruto dos serviços,
                    // e o `desconto_valor` do ORÇAMENTO era o desconto A SER APLICADO.

                    if ($orcamento->desconto_tipo && $orcamento->desconto_valor > 0) {
                        if ($orcamento->desconto_tipo == 'fixo') {
                            // Se o desconto fixo do orçamento for menor ou igual ao total dos serviços da OS
                            if ($orcamento->desconto_valor <= $totalServicosCalculadoParaOS) {
                                $novoAtendimento->desconto_servico = $orcamento->desconto_valor;
                            } else {
                                // Se o desconto fixo era maior que os serviços, aplica o máximo possível
                                $novoAtendimento->desconto_servico = $totalServicosCalculadoParaOS;
                                // A diferença deveria ter sido aplicada às peças, o que não é comum.
                                // Log::warning("Desconto fixo do orçamento #{$orcamento->id} era maior que o total de serviços. Aplicado parcialmente à OS #{$novoAtendimento->id}.");
                            }
                        } elseif ($orcamento->desconto_tipo == 'percentual') {
                            $novoAtendimento->desconto_servico = ($totalServicosCalculadoParaOS * $orcamento->desconto_valor) / 100;
                        }
                    }
                }
            }
            $novoAtendimento->valor_servico = round($novoAtendimento->valor_servico, 2);
            $novoAtendimento->desconto_servico = round($novoAtendimento->desconto_servico ?? 0, 2);
            $novoAtendimento->save(); // Salva o valor_servico e desconto_servico atualizados na OS

            // Atualizar o status do orçamento
            $orcamento->status = 'Convertido em OS';
            $orcamento->atendimento_id_convertido = $novoAtendimento->id;
            $orcamento->save();
            Log::info(" - Orçamento #{$orcamento->id} atualizado para 'Convertido em OS' e vinculado à OS #{$novoAtendimento->id}.");

            DB::commit();
            Log::info(" - Transação commitada. Conversão do Orçamento #{$orcamento->id} para OS #{$novoAtendimento->id} concluída com sucesso.");
            session()->forget('orcamento_edit_cliente_id');

            return redirect()->route('atendimentos.show', $novoAtendimento->id)
                ->with('success', 'Orçamento #' . $orcamento->id . ' convertido com sucesso na OS #' . $novoAtendimento->id . '! Código da OS: ' . $novoAtendimento->codigo_consulta);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro CRÍTICO ao converter orçamento #{$orcamento->id} em OS: " . $e->getMessage() . " no arquivo " . $e->getFile() . " linha " . $e->getLine(), ['exception_trace' => $e->getTraceAsString()]);
            return redirect()->route('orcamentos.show', $orcamento->id)
                ->with('error', 'Ocorreu um erro crítico ao converter o orçamento: ' . $e->getMessage());
        }
    }


    public function gerarPdf(Orcamento $orcamento)
    {
        $orcamento->load(['cliente', 'criadoPor', 'itens.estoque']);
        $dadosParaPdf = [
            'orcamento' => $orcamento,
            'dataImpressao' => Carbon::now(),
            'nomeEmpresa' => 'JM Celulares',
            'enderecoEmpresa' => 'Alameda Capitão José Custódio, 130, Centro - Monte Azul - MG',
            'telefoneEmpresa' => '(38) 99269-6404',
            'emailEmpresa' => 'contato@jmcelulares.com.br',
        ];
        $pdf = Pdf::loadView('orcamentos.pdf_template', $dadosParaPdf);
        $nomeClienteSlug = Str::slug($orcamento->cliente->nome_completo ?? ($orcamento->nome_cliente_avulso ?? 'orcamento'), '_');
        $nomeArquivo = 'Orcamento_' . $orcamento->id . '_' . $nomeClienteSlug . '.pdf';
        return $pdf->stream($nomeArquivo);
    }

    /**
     * Envia o orçamento por e-mail para o cliente.
     */
    public function enviarEmail(Request $request, Orcamento $orcamento)
    {
        // Garantir que o orçamento tem um cliente e um e-mail para envio
        $destinatarioEmail = null;
        if ($orcamento->cliente && $orcamento->cliente->email) {
            $destinatarioEmail = $orcamento->cliente->email;
        } elseif ($orcamento->email_cliente_avulso) {
            $destinatarioEmail = $orcamento->email_cliente_avulso;
        }

        if (!$destinatarioEmail) {
            return redirect()->route('orcamentos.show', $orcamento->id)
                ->with('error', 'Não foi possível enviar o e-mail. O cliente não possui um endereço de e-mail associado a este orçamento.');
        }


        try {
            // ANTES de enviar a notificação, vamos inspecionar os dados
            Log::debug("Dados do Orçamento #{$orcamento->id} para notificação:");
            Log::debug("Cliente Nome: " . ($orcamento->cliente->nome_completo ?? $orcamento->nome_cliente_avulso ?? 'N/A'));
            Log::debug("Descrição Aparelho: " . $orcamento->descricao_aparelho);
            // Adicione logs para outros campos que vão para a notificação, especialmente os que podem ter texto livre
            Log::debug("Problema Relatado: " . $orcamento->problema_relatado_cliente);
            foreach ($orcamento->itens as $item) {
                Log::debug("Item Descrição: " . ($item->estoque->nome ?? $item->descricao_item_manual ?? 'N/A'));
            }

            // Tentar codificar para JSON aqui para ver se o erro acontece antes da fila
            try {
                $jsonPayloadTest = json_encode(new OrcamentoParaClienteNotification($orcamento));
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error("Erro de JSON encode ANTES da fila: " . json_last_error_msg());
                } else {
                    Log::info("Payload da notificação codificado para JSON com sucesso ANTES da fila.");
                }
            } catch (\Exception $e) {
                Log::error("Exceção ao tentar codificar payload da notificação para JSON ANTES da fila: " . $e->getMessage());
            }


            if ($orcamento->cliente && method_exists($orcamento->cliente, 'notify')) {
                Notification::send($orcamento->cliente, new OrcamentoParaClienteNotification($orcamento));
            } else {
                Notification::route('mail', $destinatarioEmail)
                    ->notify(new OrcamentoParaClienteNotification($orcamento));
            }

            // Mudar status para "Aguardando Aprovação" se ainda estiver "Em Elaboração"
            if ($orcamento->status === 'Em Elaboração') {
                $orcamento->status = 'Aguardando Aprovação';
                $orcamento->save();
                return redirect()->route('orcamentos.show', $orcamento->id)
                    ->with('success', "Orçamento #{$orcamento->id} enviado para {$destinatarioEmail} e status atualizado para 'Aguardando Aprovação'.");
            }

            return redirect()->route('orcamentos.show', $orcamento->id)
                ->with('success', "Orçamento #{$orcamento->id} reenviado para {$destinatarioEmail} com sucesso!");
        } catch (\Exception $e) {
            Log::error("Erro ao enviar e-mail do orçamento #{$orcamento->id} para {$destinatarioEmail}: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('orcamentos.show', $orcamento->id)
                ->with('error', 'Ocorreu um erro ao tentar enviar o e-mail do orçamento. Verifique os logs para mais detalhes.');
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\Cliente;
use App\Models\User;
// Removido: use App\Models\AtendimentoServico; // Não precisamos manipular AtendimentoServico diretamente no update principal
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Caixa;
use App\Models\MovimentacaoCaixa;
use App\Notifications\AtendimentoProntoNotification;
use Illuminate\Support\Facades\Validator;
use App\Models\Estoque;
use App\Models\SaidaEstoque;

class AtendimentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Atendimento::with('cliente', 'tecnico');

        if ($request->filled('filtro_status')) {
            $query->where('status', $request->input('filtro_status'));
        }

        if ($request->filled('filtro_tecnico_id')) {
            $filtroTecnicoId = $request->input('filtro_tecnico_id');
            if ($filtroTecnicoId === '0') {
                $query->whereNull('tecnico_id');
            } else {
                $query->where('tecnico_id', $filtroTecnicoId);
            }
        }

        if ($request->filled('data_inicial_filtro') && $request->filled('data_final_filtro')) {
            $dataInicial = Carbon::parse($request->input('data_inicial_filtro'))->startOfDay();
            $dataFinal = Carbon::parse($request->input('data_final_filtro'))->endOfDay();
            if ($dataInicial->lte($dataFinal)) {
                $query->whereBetween('data_entrada', [$dataInicial, $dataFinal]);
            }
        } elseif ($request->filled('data_inicial_filtro')) {
            $dataInicial = Carbon::parse($request->input('data_inicial_filtro'))->startOfDay();
            $query->where('data_entrada', '>=', $dataInicial);
        } elseif ($request->filled('data_final_filtro')) {
            $dataFinal = Carbon::parse($request->input('data_final_filtro'))->endOfDay();
            $query->where('data_entrada', '<=', $dataFinal);
        }

        if ($request->filled('busca_atendimento')) {
            $searchTerm = $request->input('busca_atendimento');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', '%' . $searchTerm . '%')
                    ->orWhere('codigo_consulta', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('cliente', function ($clienteQuery) use ($searchTerm) {
                        $clienteQuery->where('nome_completo', 'like', '%' . $searchTerm . '%')
                            ->orWhere('cpf_cnpj', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        if ($request->input('filtro_status_aberto') === 'sim') {
            $query->whereNotIn('status', ['Entregue', 'Cancelado', 'Reprovado']);
        }

        if ($request->filled('filtro_status_pagamento')) {
            $query->where('status_pagamento', $request->input('filtro_status_pagamento'));
        }

        $atendimentos = $query->orderBy('data_entrada', 'desc')->paginate(15);
        $atendimentos->appends($request->query());

        $todosOsStatus = Atendimento::getPossibleStatuses();
        $tecnicos = User::where('tipo_usuario', 'tecnico')->orderBy('name')->get();

        return view('atendimentos.index', compact(
            'atendimentos',
            'todosOsStatus',
            'tecnicos'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $tecnicos = User::where('tipo_usuario', 'tecnico')->orderBy('name')->get();
        $selectedClienteId = $request->input('cliente_id');
        $clienteSelecionado = null;
        if ($selectedClienteId) {
            $clienteSelecionado = Cliente::find($selectedClienteId);
        }

        return view('atendimentos.create', compact('tecnicos', 'clienteSelecionado', 'selectedClienteId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validação dos campos principais do atendimento
        // Agora inclui valor_servico e desconto_servico, mas não mais 'servicos_detalhados'
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'descricao_aparelho' => 'required|string|max:255',
            'problema_relatado' => 'required|string|max:255',
            'data_entrada' => 'required|date',
            'tecnico_id' => 'nullable|exists:users,id',
            'valor_servico' => 'nullable|numeric|min:0', // Valor estimado da mão de obra
            'desconto_servico' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    $valorServicoParaValidacao = $request->input('valor_servico', 0);
                    if (is_numeric($value) && is_numeric($valorServicoParaValidacao) && (float) $value > (float) $valorServicoParaValidacao) {
                        $fail('O desconto não pode ser maior que o valor do serviço.');
                    }
                }
            ],
            'forma_pagamento' => ['nullable', 'string', 'max:50', Rule::in(config('constants.formas_pagamento', []))],
            // 'status_pagamento' será 'Pendente' por padrão, conforme definido abaixo.
        ], [
            'cliente_id.required' => 'O cliente é obrigatório. Por favor, selecione um cliente da lista ou cadastre um novo.',
            'descricao_aparelho.required' => 'A descrição do aparelho é obrigatória.',
            'problema_relatado.required' => 'O problema relatado é obrigatório.',
            'data_entrada.required' => 'A data de entrada é obrigatória.',
            'valor_servico.numeric' => 'O valor do serviço deve ser um número.',
            'valor_servico.min' => 'O valor do serviço não pode ser negativo.',
            'desconto_servico.numeric' => 'O valor do desconto deve ser um número.',
            'desconto_servico.min' => 'O valor do desconto não pode ser negativo.',
        ]);

        DB::beginTransaction();
        try {
            // Prepara os dados do Atendimento principal
            $atendimentoDataPrincipal = $request->except(['data_entrada', 'cliente_nome_display']); // Remove campos que não são do model ou são tratados separadamente

            // Define a data_entrada com a hora atual
            if ($request->filled('data_entrada')) {
                $dataDoFormulario = $request->input('data_entrada');
                $atendimentoDataPrincipal['data_entrada'] = Carbon::parse($dataDoFormulario)->setTimeFrom(Carbon::now());
            } else {
                $atendimentoDataPrincipal['data_entrada'] = Carbon::now(); // Fallback, embora seja 'required'
            }

            // Gerar código de consulta
            $anoAtual = now()->year;
            $codigoUnico = false;
            $novoCodigoConsulta = '';
            while (!$codigoUnico) {
                $parteNumerica = random_int(10000, 99999);
                $novoCodigoConsulta = $parteNumerica . '-' . $anoAtual;
                if (!Atendimento::where('codigo_consulta', $novoCodigoConsulta)->exists()) {
                    $codigoUnico = true;
                }
            }
            $atendimentoDataPrincipal['codigo_consulta'] = $novoCodigoConsulta;
            $atendimentoDataPrincipal['status'] = 'Em diagnóstico'; // Status inicial padrão
            $atendimentoDataPrincipal['status_pagamento'] = $request->input('status_pagamento', 'Pendente'); // Pega do form (hidden) ou default

            // Garante que valor_servico e desconto_servico sejam 0.00 se não preenchidos
            $atendimentoDataPrincipal['valor_servico'] = $request->input('valor_servico', 0.00);
            $atendimentoDataPrincipal['desconto_servico'] = $request->input('desconto_servico', 0.00);
            $atendimentoDataPrincipal['forma_pagamento'] = $request->input('forma_pagamento', null);


            // Criar o Atendimento principal
            $atendimento = Atendimento::create($atendimentoDataPrincipal);

            // Não há mais 'servicos_detalhados' para processar aqui neste fluxo de criação simplificado.
            // Eles serão adicionados/gerenciados na tela de show ou edit.

            DB::commit();

            Log::info("Atendimento #{$atendimento->id} (Cód: {$novoCodigoConsulta}) criado com sucesso via formulário simplificado.");

            return redirect()->route('atendimentos.show', $atendimento->id)
                ->with('success', "Atendimento #{$atendimento->id} registrado! Cód. Consulta: {$novoCodigoConsulta}. Adicione serviços detalhados e peças conforme necessário na tela de visualização ou edição.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            // Log já ocorre automaticamente pelo handler de exceções do Laravel
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar atendimento (fluxo simplificado): ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Ocorreu um erro ao criar o atendimento: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Atendimento $atendimento)
    {
        $atendimento->load(['cliente', 'tecnico', 'saidasEstoque.estoque']);

        $valorTotalPecasModal = 0;
        if ($atendimento->saidasEstoque && $atendimento->saidasEstoque->isNotEmpty()) {
            foreach ($atendimento->saidasEstoque as $saida) {
                if ($saida->estoque) {
                    $valorTotalPecasModal += $saida->quantidade * ($saida->estoque->preco_venda ?? 0);
                }
            }
        }

        $formasPagamentoDisponiveis = config('constants.formas_pagamento', ['Dinheiro', 'Cartão de Débito', 'Cartão de Crédito', 'PIX', 'Boleto', 'Outro']);
        $valorServicoModal = $atendimento->valor_servico ?? 0;
        $descontoServicoModal = $atendimento->desconto_servico ?? 0;
        $valorServicoLiquidoModal = $valorServicoModal - $descontoServicoModal;
        $valorTotalDevidoModal = $valorServicoLiquidoModal + $valorTotalPecasModal;

        return view('atendimentos.show', compact(
            'atendimento',
            'formasPagamentoDisponiveis',
            'valorTotalPecasModal',
            'valorServicoModal',
            'descontoServicoModal',
            'valorServicoLiquidoModal',
            'valorTotalDevidoModal'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Atendimento $atendimento)
    {
        if (Gate::denies('is-internal-user')) {
            return redirect()->route('atendimentos.index')->with('error', 'Acesso não autorizado.');
        }
        $clientes = Cliente::orderBy('nome_completo')->get();
        $tecnicos = User::where('tipo_usuario', 'tecnico')->orderBy('name')->get();
        // Carrega os relacionamentos para que os accessors (como valor_total_pecas) funcionem corretamente na view de edição
        $atendimento->load('servicosDetalhados', 'saidasEstoque.estoque');
        $formasPagamento = config('constants.formas_pagamento', ['Dinheiro', 'Cartão de Débito', 'Cartão de Crédito', 'PIX', 'Boleto', 'Outro']);

        return view('atendimentos.edit', compact('atendimento', 'clientes', 'tecnicos', 'formasPagamento'));
    }


    public function update(Request $request, Atendimento $atendimento)
    {
        if (Gate::denies('is-internal-user')) {
            return redirect()->route('atendimentos.show', $atendimento->id)->with('error', 'Acesso não autorizado para editar.');
        }

        $statusDePagamentoRecebido = ['Pago']; // Status que indicam pagamento efetivo

        // Validações dos campos principais do atendimento.
        // 'valor_servico' e 'servicos_detalhados' não são validados aqui, pois são gerenciados em outro lugar.
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'descricao_aparelho' => 'required|string|max:255',
            'problema_relatado' => 'required|string|max:255',
            'status' => ['required', 'string', Rule::in(Atendimento::getPossibleStatuses())],
            'status_pagamento' => ['required', 'string', Rule::in(Atendimento::getPossiblePaymentStatuses())],
            'tecnico_id' => 'nullable|exists:users,id',
            'data_conclusao' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($atendimento) {
                    $dataEntradaOriginal = $atendimento->data_entrada;
                    if ($value && $dataEntradaOriginal && Carbon::parse($value)->lt($dataEntradaOriginal)) {
                        $fail('A data de conclusão não pode ser anterior à data de entrada original (' . $dataEntradaOriginal->format('d/m/Y') . ').');
                    }
                }
            ],
            'observacoes' => 'nullable|string',
            'laudo_tecnico' => 'nullable|string',
            'desconto_servico' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($atendimento, $request) {
                    // O valor_servico para validação é o que já existe no atendimento (soma dos itens)
                    // ou o que foi enviado no campo hidden 'valor_servico' do formulário de edição
                    $valorServicoAtualParaValidacao = $request->input('valor_servico', $atendimento->valor_servico ?? 0);
                    if (is_numeric($value) && (float) $value > (float) $valorServicoAtualParaValidacao) {
                        $fail('O desconto global não pode ser maior que o valor total da mão de obra (R$ ' . number_format((float) $valorServicoAtualParaValidacao, 2, ',', '.') . ').');
                    }
                }
            ],
            'forma_pagamento' => ['nullable', 'string', 'max:50', Rule::in(array_merge(config('constants.formas_pagamento', []), ['']))],
            // O campo 'valor_servico' do formulário (vindo do input hidden) é opcional aqui.
            // Não será usado para definir $atendimento->valor_servico, mas pode ser usado na validação do desconto.
            'valor_servico' => 'sometimes|numeric|min:0',
        ], [
            'cliente_id.required' => 'O cliente é obrigatório.',
            'descricao_aparelho.required' => 'A descrição do aparelho é obrigatória.',
            'problema_relatado.required' => 'O problema relatado é obrigatório.',
            'status.required' => 'O status do serviço é obrigatório.',
            'status_pagamento.required' => 'O status do pagamento é obrigatório.',
            'desconto_servico.min' => 'O desconto não pode ser negativo.',
            // Adicione outras mensagens de validação conforme necessário
        ]);

        // Lógica de permissão para mudança de status
        $statusAnterior = $atendimento->getOriginal('status');
        $novoStatus = $validatedData['status'];
        $usuarioAtual = Auth::user();

        if ($statusAnterior !== $novoStatus) {
            if (!$atendimento->canTransitionTo($novoStatus, $usuarioAtual)) {
                return redirect()->back()
                    ->withErrors(['status' => 'Você não tem permissão para alterar o atendimento do status "' . $statusAnterior . '" para: "' . $novoStatus . '" ou a transição é inválida.'])
                    ->withInput();
            }
        }
        $statusPagamentoAnterior = $atendimento->getOriginal('status_pagamento');
        $novoStatusPagamento = $validatedData['status_pagamento'];
        if ($statusPagamentoAnterior !== $novoStatusPagamento) {
            if (!$atendimento->canTransitionPaymentTo($novoStatusPagamento, $usuarioAtual)) {
                return redirect()->back()
                    ->withErrors(['status_pagamento' => 'A alteração do status de pagamento de "' . ($statusPagamentoAnterior ?? 'N/D') . '" para "' . $novoStatusPagamento . '" não é permitida ou você não tem permissão.'])
                    ->withInput();
            }
        }
        if ($novoStatus === 'Entregue' && $novoStatusPagamento === 'Pendente') {
            return redirect()->back()->withErrors(['status_pagamento' => 'Não é possível marcar o serviço como "Entregue" com pagamento "Pendente". Finalize o pagamento primeiro.'])->withInput();
        }
        if (in_array($novoStatus, ['Cancelado', 'Reprovado']) && $novoStatusPagamento === 'Pago') {
            return redirect()->back()->withErrors(['status_pagamento' => 'Para serviços "Cancelados" ou "Reprovados", o status de pagamento não pode ser "Pago". Considere "Devolvido" ou ajuste o status do serviço.'])->withInput();
        }

        // Permissão para campos sensíveis
        if ($request->has('laudo_tecnico') && $atendimento->laudo_tecnico !== $request->input('laudo_tecnico')) {
            if (Gate::denies('is-admin-or-tecnico')) {
                return redirect()->back()->withErrors(['laudo_tecnico' => 'Você não tem permissão para alterar o laudo técnico.'])->withInput();
            }
        }
        $descontoServicoOriginal = (float) ($atendimento->getOriginal('desconto_servico') ?? 0);
        $descontoServicoRequest = (float) ($request->desconto_servico ?? 0); // Pega o valor do request
        if (bccomp((string) $descontoServicoRequest, (string) $descontoServicoOriginal, 2) !== 0) { // Compara com precisão
            if (Gate::denies('is-admin')) {
                return redirect()->back()->withErrors(['desconto_servico' => 'Você não tem permissão para alterar o desconto global.'])->withInput();
            }
        }


        DB::beginTransaction();
        try {
            // Prepara os dados para atualização principal.
            // 'valor_servico' não é pego do request para definir $atendimento->valor_servico,
            // pois ele é a soma dos AtendimentoServico.
            $dadosUpdatePrincipal = collect($validatedData)->except(['valor_servico'])->toArray();

            if ($request->filled('data_conclusao')) {
                $dadosUpdatePrincipal['data_conclusao'] = Carbon::parse($request->data_conclusao);
            } else {
                $dadosUpdatePrincipal['data_conclusao'] = null; // Garante que seja nulo se não preenchido
            }
            // O campo 'desconto_servico' já está em $dadosUpdatePrincipal se passou na validação.
            // Certifique-se de que está sendo tratado corretamente (ex: como float).
            $dadosUpdatePrincipal['desconto_servico'] = round((float) ($request->input('desconto_servico', $atendimento->desconto_servico ?? 0)), 2);


            // Atualiza o atendimento principal
            $atendimento->update($dadosUpdatePrincipal);

            // NÃO HÁ processamento de 'servicos_detalhados' neste método,
            // pois são gerenciados na tela 'show' via AJAX (atualizarServicosDetalhadosAjax)
            // ou na conversão do orçamento.
            // O 'valor_servico' do $atendimento já reflete a soma dos AtendimentoServico existentes.

            // Lógica de Caixa e Notificações
            $mensagemParaUsuario = "Atendimento #{$atendimento->id} atualizado com sucesso!";
            $feedbackTipo = 'success';

            // É importante usar os valores ANTES do $atendimento->refresh() se você precisa comparar
            // o estado anterior com o estado atual para a lógica do caixa.
            // No entanto, para o valor a ser registrado no caixa, queremos o valor APÓS as atualizações.
            $atendimento->refresh(); // Garante que temos os dados mais recentes, incluindo accessors

            $valorCobradoEfetivamenteAposUpdate = $atendimento->valor_total_atendimento; // Usa o accessor
            $formaPagamentoOriginal = $atendimento->getOriginal('forma_pagamento'); // do $atendimento ANTES do refresh, se precisar. Mas após o refresh, este é o valor atual.
            // Para a lógica de caixa, é melhor pegar os valores antes do update para comparar com os valores depois.
            // Ou, se o refresh já aconteceu, getOriginal pode não ser mais útil para o estado pré-update.

            $deveRegistrarNoCaixa = false;
            $mensagemAdicionalCaixa = '';

            if ($statusPagamentoAnterior !== 'Pago' && $atendimento->status_pagamento === 'Pago') {
                $deveRegistrarNoCaixa = true;
            } elseif ($statusPagamentoAnterior === 'Pago' && $atendimento->status_pagamento === 'Pago') {
                // Recalcular o valor líquido original (antes deste update específico)
                // Precisamos dos valores de serviço, desconto e peças ANTES da alteração do desconto atual.
                $valorServicoOriginalDb = (float) ($atendimento->getOriginal('valor_servico') ?? 0);
                $descontoServicoOriginalDb = (float) ($atendimento->getOriginal('desconto_servico') ?? 0);
                // O total de peças não muda neste fluxo de 'update'
                $valorTotalPecasDb = $atendimento->saidasEstoque->sum(fn($saida) => $saida->quantidade * ($saida->estoque->preco_venda ?? 0));
                $valorLiquidoOriginalCompleto = ($valorServicoOriginalDb - $descontoServicoOriginalDb) + $valorTotalPecasDb;

                if (
                    bccomp((string) $valorCobradoEfetivamenteAposUpdate, (string) $valorLiquidoOriginalCompleto, 2) != 0 ||
                    $atendimento->forma_pagamento !== $atendimento->getOriginal('forma_pagamento')
                ) { // Compara com a forma de pagamento original

                    $movimentacaoExistenteComNovosValores = MovimentacaoCaixa::where('referencia_tipo', Atendimento::class)
                        ->where('referencia_id', $atendimento->id)
                        ->where('valor', $valorCobradoEfetivamenteAposUpdate)
                        ->where('forma_pagamento', $atendimento->forma_pagamento)
                        ->latest('data_movimentacao')->first();

                    if (!$movimentacaoExistenteComNovosValores) {
                        $deveRegistrarNoCaixa = true;
                        session()->flash('warning_caixa_update', 'Os detalhes financeiros de um atendimento já pago foram alterados. Uma nova movimentação de caixa será gerada para refletir a alteração. Verifique o caixa.');
                        Log::info("Caixa Update: OS#{$atendimento->id}. Vlr Liq Original: {$valorLiquidoOriginalCompleto}, Vlr Atual: {$valorCobradoEfetivamenteAposUpdate}. Forma Pag Original: {$atendimento->getOriginal('forma_pagamento')}, Forma Atual: {$atendimento->forma_pagamento}");
                    }
                }
            }

            if ($deveRegistrarNoCaixa) {
                if ($valorCobradoEfetivamenteAposUpdate > 0 && !empty($atendimento->forma_pagamento)) {
                    $caixaAberto = Caixa::getCaixaAbertoAtual();
                    if ($caixaAberto) {
                        MovimentacaoCaixa::create([
                            'caixa_id' => $caixaAberto->id,
                            'usuario_id' => Auth::id(),
                            'tipo' => 'ENTRADA',
                            'descricao' => "Recebimento OS/Atendimento #" . $atendimento->id . " (após edição completa)",
                            'valor' => $valorCobradoEfetivamenteAposUpdate,
                            'forma_pagamento' => $atendimento->forma_pagamento,
                            'referencia_id' => $atendimento->id,
                            'referencia_tipo' => Atendimento::class,
                            'data_movimentacao' => Carbon::now(),
                            'observacoes' => 'Pagamento de serviço (via Edição Completa).',
                        ]);
                        $mensagemAdicionalCaixa = ' Recebimento de R$ ' . number_format($valorCobradoEfetivamenteAposUpdate, 2, ',', '.') . ' registrado no caixa #' . $caixaAberto->id . '.';
                        if (session()->has('warning_caixa_update')) {
                            $mensagemParaUsuario = session('warning_caixa_update') . $mensagemAdicionalCaixa;
                            $feedbackTipo = 'warning';
                        } else {
                            $mensagemParaUsuario .= $mensagemAdicionalCaixa;
                        }
                    } else {
                        $mensagemParaUsuario .= ' ATENÇÃO: Nenhum caixa aberto, o recebimento não foi automaticamente registrado no caixa.';
                        $feedbackTipo = 'warning';
                    }
                } elseif ($atendimento->status_pagamento === 'Pago') {
                    $mensagemParaUsuario .= ' Status de Pagamento definido como "Pago", mas o valor cobrado é zero ou a forma de pagamento não foi informada. Nenhuma entrada no caixa foi realizada.';
                    $feedbackTipo = 'info';
                }
            }
            session()->forget('warning_caixa_update');

            // Notificação
            if ($atendimento->status == 'Pronto para entrega' && $statusAnterior != 'Pronto para entrega') {
                if ($atendimento->cliente && $atendimento->cliente->email && class_exists(AtendimentoProntoNotification::class)) {
                    try {
                        $atendimento->cliente->notify(new AtendimentoProntoNotification($atendimento));
                    } catch (\Exception $e) {
                        Log::error("Erro ao enviar notificação 'Pronto' (update) para atend. #{$atendimento->id}: " . $e->getMessage());
                    }
                }
            }

            DB::commit();
            return redirect()->route('atendimentos.show', $atendimento->id)->with($feedbackTipo, $mensagemParaUsuario);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao atualizar atendimento #{$atendimento->id}: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Ocorreu um erro ao atualizar o atendimento: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Atendimento $atendimento)
    {
        if (Gate::denies('is-admin')) {
            return redirect()->route('atendimentos.index')
                ->with('error', 'Apenas administradores podem excluir atendimentos.');
        }

        if ($atendimento->status_pagamento === 'Pago' || in_array($atendimento->status, ['Entregue', 'Finalizado e Pago'])) {
            Log::warning("Tentativa de exclusão bloqueada para Atendimento #{$atendimento->id} devido ao status: {$atendimento->status} / status_pagamento: {$atendimento->status_pagamento}.");
            return redirect()->route('atendimentos.show', $atendimento->id)
                ->with('error', 'Atendimentos pagos ou já entregues não podem ser excluídos diretamente. Avalie um processo de estorno ou cancelamento apropriado com o administrador.');
        }

        DB::beginTransaction();
        try {
            $atendimentoIdExcluido = $atendimento->id;
            $nomeCliente = $atendimento->cliente->nome_completo ?? 'N/A';
            $pecasEstornadasInfo = [];

            $atendimento->loadMissing('saidasEstoque.estoque');

            if ($atendimento->saidasEstoque->isNotEmpty()) {
                Log::info("Iniciando estorno de peças para Atendimento #{$atendimentoIdExcluido}. Peças a serem processadas: " . $atendimento->saidasEstoque->count());
                foreach ($atendimento->saidasEstoque as $saida) {
                    if ($saida->estoque) {
                        $peca = $saida->estoque;
                        $quantidadeEstornada = $saida->quantidade;
                        $peca->increment('quantidade', $quantidadeEstornada);
                        $pecasEstornadasInfo[] = "{$quantidadeEstornada}x {$peca->nome} (ID Estoque: {$peca->id})";
                        Log::info("Estornado: {$quantidadeEstornada}x {$peca->nome} (ID Estoque: {$peca->id}) para Atendimento #{$atendimentoIdExcluido}");
                        $saida->delete();
                    } else {
                        Log::warning("Peça da SaidaEstoque ID {$saida->id} não encontrada no estoque durante a exclusão do Atendimento #{$atendimentoIdExcluido}. A saída será removida, mas o estoque não pôde ser incrementado.");
                        $saida->delete();
                    }
                }
            }

            $atendimento->delete();
            Log::info("Atendimento #{$atendimentoIdExcluido} para cliente '{$nomeCliente}' excluído com sucesso.");
            DB::commit();
            $mensagem = "Atendimento #{$atendimentoIdExcluido} (Cliente: {$nomeCliente}) excluído com sucesso.";
            if (!empty($pecasEstornadasInfo)) {
                $mensagem .= " Peças estornadas ao estoque: " . implode('; ', $pecasEstornadasInfo) . ".";
            }
            return redirect()->route('atendimentos.index')->with('success', $mensagem);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro crítico ao excluir atendimento #{$atendimento->id} com estorno de peças: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('atendimentos.index')
                ->with('error', 'Ocorreu um erro crítico ao tentar excluir o atendimento e estornar as peças. Consulte os logs para mais detalhes.');
        }
    }

    /**
     * Autocomplete para atendimentos.
     */
    public function autocomplete(Request $request)
    {
        $search = $request->get('search_autocomplete');
        $query = Atendimento::with('cliente');

        if ($search) {
            $query->where(function ($q) use ($search) {
                if (is_numeric($search)) {
                    $q->where('id', (int) $search);
                }
                $q->orWhere('id', 'like', $search . '%')
                    ->orWhere('codigo_consulta', 'like', $search . '%')
                    ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                        $clienteQuery->where('nome_completo', 'like', '%' . $search . '%')
                            ->orWhere('cpf_cnpj', 'like', '%' . $search . '%');
                    })
                    ->orWhere('descricao_aparelho', 'like', '%' . $search . '%');
            });
        }

        $atendimentos = $query->orderBy('data_entrada', 'desc')->limit(15)->get();
        return response()->json($atendimentos);
    }

    /**
     * Gera o PDF/OS para o atendimento.
     */
    public function gerarPdf(Atendimento $atendimento)
    {
        $atendimento->load(['cliente', 'tecnico', 'saidasEstoque.estoque']);
        $valorTotalPecas = 0;
        if ($atendimento->saidasEstoque) {
            foreach ($atendimento->saidasEstoque as $saida) {
                if ($saida->estoque) {
                    $precoVendaPeca = $saida->estoque->preco_venda ?? 0;
                    $valorTotalPecas += $saida->quantidade * $precoVendaPeca;
                }
            }
        }
        $valorServicoLiquido = ($atendimento->valor_servico ?? 0) - ($atendimento->desconto_servico ?? 0);
        $valorTotalAtendimento = $valorServicoLiquido + $valorTotalPecas;
        $urlConsulta = route('consulta.index');
        $dadosParaPdf = [
            'atendimento' => $atendimento,
            'valorTotalPecas' => $valorTotalPecas,
            'valorServicoLiquido' => $valorServicoLiquido,
            'valorTotalAtendimento' => $valorTotalAtendimento,
            'dataImpressao' => Carbon::now(),
            'nomeEmpresa' => 'JM Celulares',
            'enderecoEmpresa' => 'Alameda Capitão José Custódio, 130, Centro - Monte Azul - MG',
            'telefoneEmpresa' => '(38) 99269-6404',
            'emailEmpresa' => 'contato@jmcelulares.com.br',
            'urlConsultaSite' => $urlConsulta,
        ];
        $pdf = Pdf::loadView('atendimentos.pdf_template', $dadosParaPdf);
        $nomeArquivo = 'OS_Atendimento_' . $atendimento->id . '_' . Str::slug($atendimento->cliente->nome_completo ?? 'cliente', '_') . '.pdf';
        return $pdf->stream($nomeArquivo);
    }

    /**
     * Atualiza o status de um atendimento via AJAX.
     */
    public function atualizarStatus(Request $request, Atendimento $atendimento)
    {
        $request->validate([
            'status' => ['required', 'string', Rule::in(Atendimento::getPossibleStatuses())],
        ], [], ['status' => 'novo status']);

        $statusAnterior = $atendimento->status;
        $novoStatus = $request->input('status');
        $usuarioAtual = Auth::user();

        if (!$atendimento->canTransitionTo($novoStatus, $usuarioAtual)) {
            $errorMessage = 'Você não tem permissão para alterar o status de "' . $statusAnterior . '" para: "' . $novoStatus . '" ou a transição é inválida.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage, 'feedback_tipo' => 'danger'], 403);
            }
            return redirect()->route('atendimentos.show', $atendimento->id)->with('error', $errorMessage);
        }

        if ($novoStatus === 'Entregue' && $atendimento->status_pagamento === 'Pendente') {
            $errorMessage = 'Não é possível marcar o serviço como "Entregue" com pagamento "Pendente" através da atualização rápida. Utilize a edição completa ou o modal de pagamento.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMessage, 'feedback_tipo' => 'warning'], 422);
            }
            return redirect()->route('atendimentos.show', $atendimento->id)->with('warning', $errorMessage);
        }

        $atendimento->status = $novoStatus;
        $atendimento->save();

        $mensagemParaUsuario = 'Status geral atualizado para ' . $novoStatus . '!';
        $feedbackTipo = 'success';

        if ($novoStatus === 'Pronto para entrega' && $atendimento->status_pagamento === 'Pendente') {
            $mensagemParaUsuario .= ' Para registrar o recebimento e finalizar, utilize o botão "Registrar Pagamento" nesta tela.';
            $feedbackTipo = 'info';
        } elseif ($novoStatus === 'Entregue' && $atendimento->status_pagamento !== 'Pago' && $atendimento->status_pagamento !== 'Não Aplicável') {
            $mensagemParaUsuario .= ' ATENÇÃO: O serviço foi marcado como "Entregue", mas o status de pagamento é "' . $atendimento->status_pagamento . '". Verifique a consistência.';
            $feedbackTipo = 'warning';
        }

        if ($atendimento->status == 'Pronto para entrega' && $statusAnterior != 'Pronto para entrega') {
            if ($atendimento->cliente && $atendimento->cliente->email) {
                try {
                    if (class_exists(AtendimentoProntoNotification::class)) {
                        $atendimento->cliente->notify(new AtendimentoProntoNotification($atendimento));
                    } else {
                        Log::warning("Classe de notificação AtendimentoProntoNotification não encontrada (atualizarStatus) para atendimento ID: {$atendimento->id}");
                    }
                } catch (\Exception $e) {
                    Log::error("Erro ao enviar notificação 'Atendimento Pronto' (atualizarStatus) para atendimento ID: {$atendimento->id} - Erro: " . $e->getMessage());
                }
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $mensagemParaUsuario,
                'novo_status_geral' => $novoStatus,
                'novo_status_geral_classe_completa' => Atendimento::getStatusClass($novoStatus),
                'novo_status_geral_icon' => Atendimento::getStatusIcon($novoStatus),
                'feedback_tipo' => $feedbackTipo
            ]);
        }
        return redirect()->route('atendimentos.show', $atendimento->id)->with($feedbackTipo, $mensagemParaUsuario);
    }

    /**
     * Atualiza um campo específico de um atendimento via AJAX.
     */
    public function atualizarCampoAjax(Request $request, Atendimento $atendimento, $campo = null)
    {
        $dadosParaAtualizar = [];
        $regrasDeValidacao = [];
        $camposEditadosNomes = [];

        if ($request->has('valor_servico') || $request->has('desconto_servico')) {
            if (Gate::denies('is-admin')) {
                return response()->json(['success' => false, 'message' => 'Você não tem permissão para alterar valores financeiros.'], 403);
            }
            if ($request->has('valor_servico')) {
                $regrasDeValidacao['valor_servico'] = ['required', 'numeric', 'min:0'];
                $dadosParaAtualizar['valor_servico'] = $request->input('valor_servico');
                $camposEditadosNomes[] = 'Valor do Serviço';
            }
            if ($request->has('desconto_servico')) {
                $regrasDeValidacao['desconto_servico'] = ['required', 'numeric', 'min:0'];
                $valorServicoParaValidacao = $request->input('valor_servico', $atendimento->valor_servico);
                $regrasDeValidacao['desconto_servico'][] = function ($attribute, $value, $fail) use ($valorServicoParaValidacao) {
                    if (floatval($value) > floatval($valorServicoParaValidacao)) {
                        $fail('O desconto não pode ser maior que o valor do serviço.');
                    }
                };
                $dadosParaAtualizar['desconto_servico'] = $request->input('desconto_servico');
                $camposEditadosNomes[] = 'Desconto';
            }
        } elseif ($campo && in_array($campo, ['observacoes', 'laudo_tecnico'])) {
            $camposPermitidosEValidacao = [
                'observacoes' => ['nullable', 'string'],
                'laudo_tecnico' => ['nullable', 'string'],
            ];
            if (!array_key_exists($campo, $camposPermitidosEValidacao)) {
                return response()->json(['success' => false, 'message' => 'Campo inválido para atualização.'], 400);
            }
            if (Gate::denies('is-admin-or-tecnico')) {
                return response()->json(['success' => false, 'message' => 'Você não tem permissão para editar este campo.'], 403);
            }
            $regrasDeValidacao[$campo] = $camposPermitidosEValidacao[$campo];
            $dadosParaAtualizar[$campo] = $request->input($campo);
            $camposEditadosNomes[] = ucfirst(str_replace('_', ' ', $campo));
        } else {
            return response()->json(['success' => false, 'message' => 'Nenhum campo válido para atualização fornecido.'], 400);
        }

        if (empty($dadosParaAtualizar)) {
            return response()->json(['success' => false, 'message' => 'Nenhum dado para atualizar.'], 400);
        }

        $request->validate($regrasDeValidacao);

        try {
            $atendimento->update($dadosParaAtualizar);
            $valorServicoAtualizado = $atendimento->valor_servico ?? 0;
            $descontoServicoAtualizado = $atendimento->desconto_servico ?? 0;
            $valorServicoLiquidoAtualizado = $valorServicoAtualizado - $descontoServicoAtualizado;
            $valorTotalPecas = 0;
            if ($atendimento->saidasEstoque) {
                foreach ($atendimento->saidasEstoque as $saida) {
                    if ($saida->estoque) {
                        $valorTotalPecas += $saida->quantidade * ($saida->estoque->preco_venda ?? 0);
                    }
                }
            }
            $valorTotalAtendimentoAtualizado = $valorServicoLiquidoAtualizado + $valorTotalPecas;

            return response()->json([
                'success' => true,
                'message' => implode(' e ', $camposEditadosNomes) . ' atualizado(s) com sucesso!',
                'novos_valores' => [
                    'valor_servico' => number_format($valorServicoAtualizado, 2, ',', '.'),
                    'desconto_servico' => number_format($descontoServicoAtualizado, 2, ',', '.'),
                    'subtotal_servico' => number_format($valorServicoLiquidoAtualizado, 2, ',', '.'),
                    'valor_total_pecas' => number_format($valorTotalPecas, 2, ',', '.'),
                    'valor_total_atendimento' => number_format($valorTotalAtendimentoAtualizado, 2, ',', '.'),
                    'observacoes' => $atendimento->observacoes,
                    'laudo_tecnico' => $atendimento->laudo_tecnico
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Erro de validação.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error("Erro ao atualizar campo(s) do atendimento {$atendimento->id} via AJAX: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno ao salvar. Tente novamente.'], 500);
        }
    }

    /**
     * Atualiza valores de serviço via AJAX.
     */
    public function atualizarValoresServicoAjax(Request $request, Atendimento $atendimento)
    {
        if (Gate::denies('is-admin')) {
            return response()->json(['success' => false, 'message' => 'Você não tem permissão para alterar valores financeiros.'], 403);
        }

        $regrasDeValidacao = [
            'valor_servico' => ['required', 'numeric', 'min:0'],
            'desconto_servico' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    $valorServicoParaValidacao = $request->input('valor_servico', 0);
                    if (is_numeric($value) && is_numeric($valorServicoParaValidacao) && (float) $value > (float) $valorServicoParaValidacao) {
                        $fail('O desconto não pode ser maior que o valor do serviço.');
                    }
                }
            ],
        ];
        $validatedData = $request->validate($regrasDeValidacao);

        try {
            $atendimento->update([
                'valor_servico' => $validatedData['valor_servico'],
                'desconto_servico' => $validatedData['desconto_servico'],
            ]);

            $valorServicoAtualizado = $atendimento->valor_servico ?? 0;
            $descontoServicoAtualizado = $atendimento->desconto_servico ?? 0;
            $valorServicoLiquidoAtualizado = $valorServicoAtualizado - $descontoServicoAtualizado;
            $valorTotalPecas = 0;
            if ($atendimento->saidasEstoque()->exists()) {
                foreach ($atendimento->saidasEstoque as $saida) {
                    if ($saida->estoque) {
                        $valorTotalPecas += $saida->quantidade * ($saida->estoque->preco_venda ?? 0);
                    }
                }
            }
            $valorTotalAtendimentoAtualizado = $valorServicoLiquidoAtualizado + $valorTotalPecas;

            return response()->json([
                'success' => true,
                'message' => 'Valores de serviço e desconto atualizados com sucesso!',
                'novos_valores' => [
                    'valor_servico' => number_format($valorServicoAtualizado, 2, ',', '.'),
                    'desconto_servico' => number_format($descontoServicoAtualizado, 2, ',', '.'),
                    'subtotal_servico' => number_format($valorServicoLiquidoAtualizado, 2, ',', '.'),
                    'valor_total_pecas' => number_format($valorTotalPecas, 2, ',', '.'),
                    'valor_total_atendimento' => number_format($valorTotalAtendimentoAtualizado, 2, ',', '.'),
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Erro de validação.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error("Erro ao atualizar valores do atendimento {$atendimento->id} via AJAX: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno ao salvar. Tente novamente.'], 500);
        }
    }

    /**
     * Registra o pagamento de um atendimento via AJAX.
     */
    public function registrarPagamentoAjax(Request $request, Atendimento $atendimento)
    {
        if (Gate::denies('gerenciar-caixa')) {
            return response()->json(['success' => false, 'message' => 'Você não tem permissão para registrar pagamentos.'], 403);
        }

        $statusGeraisPermitemPagamento = ['Pronto para entrega', 'Aguardando aprovação cliente'];
        if (!in_array($atendimento->status, $statusGeraisPermitemPagamento)) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível registrar o pagamento neste status atual do serviço (' . $atendimento->status . '). Avance o serviço primeiro.'
            ], 400);
        }

        if (!in_array($atendimento->status_pagamento, ['Pendente', 'Parcialmente Pago'])) {
            return response()->json(['success' => false, 'message' => 'O pagamento para este atendimento não está pendente ou já foi processado de outra forma.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'valor_servico' => ['required', 'numeric', 'min:0'],
            'desconto_servico' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    $valorServicoParaValidacao = $request->input('valor_servico', 0);
                    if (floatval($value) > floatval($valorServicoParaValidacao)) {
                        $fail('O desconto não pode ser maior que o valor do serviço.');
                    }
                }
            ],
            'forma_pagamento' => ['required', 'string', 'max:50', Rule::in(config('constants.formas_pagamento', []))],
            'observacoes_pagamento' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Erro de validação.', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $atendimento->valor_servico = (float) $request->input('valor_servico');
            $atendimento->desconto_servico = (float) $request->input('desconto_servico');
            $atendimento->forma_pagamento = $request->input('forma_pagamento');
            $atendimento->status_pagamento = 'Pago';

            if ($request->filled('observacoes_pagamento')) {
                $obsPagamento = "Pagamento Registrado (" . Carbon::now()->format('d/m/Y H:i') . "):\n" .
                    $request->input('observacoes_pagamento') . "\n---";
                $atendimento->observacoes = $atendimento->observacoes ? $atendimento->observacoes . "\n" . $obsPagamento : $obsPagamento;
            }
            $atendimento->save();

            $valorCobradoEfetivamente = $atendimento->valor_servico - $atendimento->desconto_servico;
            $mensagemAdicionalCaixa = '';

            if ($valorCobradoEfetivamente > 0) {
                $caixaAberto = Caixa::getCaixaAbertoAtual();
                if ($caixaAberto) {
                    MovimentacaoCaixa::create([
                        'caixa_id' => $caixaAberto->id,
                        'usuario_id' => Auth::id(),
                        'tipo' => 'ENTRADA',
                        'descricao' => "Recebimento Atendimento #" . $atendimento->id,
                        'valor' => $valorCobradoEfetivamente,
                        'forma_pagamento' => $atendimento->forma_pagamento,
                        'referencia_id' => $atendimento->id,
                        'referencia_tipo' => Atendimento::class,
                        'data_movimentacao' => Carbon::now(),
                        'observacoes' => 'Pagamento de serviço (via modal). ' . ($request->input('observacoes_pagamento') ?? ''),
                    ]);
                    $mensagemAdicionalCaixa = ' Recebimento de R$ ' . number_format($valorCobradoEfetivamente, 2, ',', '.') . ' registrado no caixa #' . $caixaAberto->id . '.';
                } else {
                    $mensagemAdicionalCaixa = ' ATENÇÃO: Nenhum caixa aberto, o recebimento não foi registrado no caixa.';
                }
            }

            $mensagemAdicionalStatusServico = '';
            if ($atendimento->status === 'Pronto para entrega' && $atendimento->isTotalmentePago()) {
                if (Auth::user()->tipo_usuario === 'admin' || Auth::user()->tipo_usuario === 'atendente') {
                    if ($atendimento->canTransitionTo('Entregue', Auth::user())) {
                        $atendimento->status = 'Entregue';
                        $atendimento->save();
                        $mensagemAdicionalStatusServico = ' Status do serviço atualizado para "Entregue".';
                    }
                } else {
                    $mensagemAdicionalStatusServico = ' O serviço está pronto para ser entregue.';
                }
            }
            DB::commit();

            $atendimento->refresh()->load(['cliente', 'saidasEstoque.estoque']);
            $valorTotalPecasAtualizado = 0;
            if ($atendimento->saidasEstoque->isNotEmpty()) {
                foreach ($atendimento->saidasEstoque as $saida) {
                    if ($saida->estoque) {
                        $valorTotalPecasAtualizado += $saida->quantidade * ($saida->estoque->preco_venda ?? 0);
                    }
                }
            }
            $valorServicoLiquidoAtualizado = ($atendimento->valor_servico ?? 0) - ($atendimento->desconto_servico ?? 0);
            $valorTotalAtendimentoAtualizado = $valorServicoLiquidoAtualizado + $valorTotalPecasAtualizado;

            $htmlBadgePagamento = view('atendimentos.partials._status_pagamento_badge', ['status_pagamento' => $atendimento->status_pagamento])->render();

            // Preparar o HTML para o badge de status do serviço
            $htmlBadgeServico = ""; // Default para o caso de não precisar atualizar
            if ($mensagemAdicionalStatusServico) { // Se o status do serviço mudou, renderiza o novo badge
                // Supondo que você criará um partial para o status do serviço, similar ao de pagamento.
                // Se não, você pode construir o HTML diretamente aqui ou omitir esta parte da resposta JSON.
                // Por enquanto, vamos mockar a criação do partial (você precisaria criar este arquivo)
                $htmlBadgeServico = view('atendimentos.partials._status_servico_badge', ['status_servico' => $atendimento->status])->render();
            }


            return response()->json([
                'success' => true,
                'message' => 'Pagamento registrado com sucesso!' . $mensagemAdicionalCaixa . $mensagemAdicionalStatusServico,
                'novos_valores_atendimento' => [
                    'valor_servico' => number_format($atendimento->valor_servico ?? 0, 2, ',', '.'),
                    'desconto_servico' => number_format($atendimento->desconto_servico ?? 0, 2, ',', '.'),
                    'subtotal_servico' => number_format($valorServicoLiquidoAtualizado, 2, ',', '.'),
                    'valor_total_pecas' => number_format($valorTotalPecasAtualizado, 2, ',', '.'),
                    'valor_total_atendimento' => number_format($valorTotalAtendimentoAtualizado, 2, ',', '.'),
                ],
                'novo_status_pagamento_html' => $htmlBadgePagamento,
                'novo_status_pagamento_texto' => $atendimento->status_pagamento,
                'novo_status_servico_texto' => $atendimento->status, // Novo status do serviço
                'novo_status_servico_html' => $htmlBadgeServico, // HTML do novo badge de serviço
                'observacoes_atualizadas' => nl2br(e($atendimento->observacoes ?? '')),
                'atendimento_id' => $atendimento->id
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error("Erro de VALIDAÇÃO ao registrar pagamento AJAX para Atendimento #{$atendimento->id}: ", ['errors' => $e->errors()]);
            return response()->json(['success' => false, 'message' => 'Erro de validação ao registrar pagamento.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro GERAL ao registrar pagamento AJAX para Atendimento #{$atendimento->id}: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Erro interno ao registrar o pagamento. Detalhes: ' . $e->getMessage()], 500);
        }
    }
    public function atualizarServicosDetalhadosAjax(Request $request, Atendimento $atendimento): \Illuminate\Http\JsonResponse
    {
        if (Gate::denies('is-admin-or-tecnico')) { // Ou a permissão apropriada
            return response()->json(['success' => false, 'message' => 'Você não tem permissão para editar os serviços.'], 403);
        }

        $validatedData = $request->validate([
            'servicos_detalhados' => 'nullable|array',
            'servicos_detalhados.*.id' => 'nullable|integer|exists:atendimento_servicos,id,atendimento_id,' . $atendimento->id, // Garante que o ID do item pertença ao atendimento
            'servicos_detalhados.*.descricao_servico' => 'required_with:servicos_detalhados|string|max:255',
            'servicos_detalhados.*.quantidade' => 'required_with:servicos_detalhados|integer|min:1',
            'servicos_detalhados.*.valor_unitario' => 'required_with:servicos_detalhados|numeric|min:0',
        ], [
            'servicos_detalhados.*.descricao_servico.required_with' => 'A descrição de cada serviço é obrigatória.',
            'servicos_detalhados.*.quantidade.required_with' => 'A quantidade de cada serviço é obrigatória (mínimo 1).',
            'servicos_detalhados.*.valor_unitario.required_with' => 'O valor unitário de cada serviço é obrigatório.',
            'servicos_detalhados.*.id.exists' => 'Um dos itens de serviço editados é inválido.',
        ]);

        DB::beginTransaction();
        try {
            $itensRecebidosIds = [];
            $novoTotalValorServicoPrincipal = 0;

            if (!empty($validatedData['servicos_detalhados'])) {
                foreach ($validatedData['servicos_detalhados'] as $itemData) {
                    $subtotalItem = (float) $itemData['quantidade'] * (float) $itemData['valor_unitario'];
                    $dadosItemServico = [
                        'descricao_servico' => $itemData['descricao_servico'],
                        'quantidade' => $itemData['quantidade'],
                        'valor_unitario' => $itemData['valor_unitario'],
                        'subtotal_servico' => $subtotalItem,
                    ];

                    if (!empty($itemData['id'])) { // Atualiza item existente
                        $itemServico = AtendimentoServico::find($itemData['id']);
                        if ($itemServico && $itemServico->atendimento_id == $atendimento->id) {
                            $itemServico->update($dadosItemServico);
                            $itensRecebidosIds[] = $itemServico->id;
                        }
                    } else { // Cria novo item
                        $novoItem = $atendimento->servicosDetalhados()->create($dadosItemServico);
                        $itensRecebidosIds[] = $novoItem->id;
                    }
                    $novoTotalValorServicoPrincipal += $subtotalItem;
                }
            }

            // Remover itens que não estão mais na lista (foram removidos no formulário)
            $atendimento->servicosDetalhados()->whereNotIn('id', $itensRecebidosIds)->delete();

            // Atualizar o campo 'valor_servico' do atendimento principal
            // O campo 'desconto_servico' é o desconto GLOBAL da OS, não é afetado pela soma dos itens aqui.
            $atendimento->valor_servico = $novoTotalValorServicoPrincipal;
            $atendimento->save(); // Salva a atualização do valor_servico no atendimento principal

            DB::commit();

            // Recarregar para obter os valores formatados pelos accessors do model
            $atendimento->refresh()->load(['servicosDetalhados']);

            return response()->json([
                'success' => true,
                'message' => 'Serviços detalhados atualizados com sucesso!',
                'novos_valores_atendimento' => [ // Para atualizar os displays na página
                    'valor_servico_formatado' => number_format($atendimento->valor_servico ?? 0, 2, ',', '.'),
                    'desconto_servico_formatado' => number_format($atendimento->desconto_servico ?? 0, 2, ',', '.'),
                    'subtotal_servico_formatado' => number_format($atendimento->valor_servico_liquido, 2, ',', '.'), // Usa o accessor
                    'valor_total_atendimento_formatado' => number_format($atendimento->valor_total_atendimento, 2, ',', '.'), // Usa o accessor
                ],
                'itens_servico_atualizados' => $atendimento->servicosDetalhados->toArray() // Para atualizar IDs no frontend se necessário
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erro de validação ao atualizar serviços.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao atualizar serviços detalhados para Atendimento #{$atendimento->id} via AJAX: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno ao salvar os serviços. Tente novamente.'], 500);
        }
    }
}
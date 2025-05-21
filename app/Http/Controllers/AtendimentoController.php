<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\Cliente;
use App\Models\MovimentacaoCaixa;
use App\Models\Caixa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth; // Importar Auth
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Notifications\AtendimentoProntoNotification;
use Illuminate\Support\Facades\Validator; // NOVO: Importar o Facade Validator
use Illuminate\Support\Facades\DB;
// Removido: use Illuminate\Support\Facades\Notification; // Não é necessário aqui se usar $atendimento->cliente->notify(...)

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

        // Filtro para atendimentos em aberto para o link do dashboard
        if ($request->input('filtro_status_aberto') === 'sim') {
            $query->whereNotIn('status', ['Entregue', 'Cancelado', 'Reprovado']);
        }
        //filtro para status de pagamento
        if ($request->filled('filtro_status_pagamento')) {
            $query->where('status_pagamento', $request->input('filtro_status_pagamento'));
        }

        $atendimentos = $query->orderBy('data_entrada', 'desc')->paginate(15);
        $atendimentos->appends($request->query());

        $todosOsStatus = Atendimento::getPossibleStatuses(); // Usando o método do Model
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
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'descricao_aparelho' => 'required|string|max:255',
            'problema_relatado' => 'required|string|max:255',
            'data_entrada' => 'required|date',
            'tecnico_id' => 'nullable|exists:users,id',
            'status_pagamento' => ['nullable', 'string', Rule::in(Atendimento::getPossiblePaymentStatuses())],
        ], [
            'cliente_id.required' => 'O cliente é obrigatório. Por favor, selecione um cliente da lista ou cadastre um novo.',
            'descricao_aparelho.required' => 'A descrição do aparelho é obrigatória.',
            'problema_relatado.required' => 'O problema relatado é obrigatório.',
            'data_entrada.required' => 'A data de entrada é obrigatória.',
            'status_pagamento.in' => 'O status de pagamento selecionado é inválido.'
        ]);

        $atendimentoData = $request->except('data_entrada');

        if ($request->filled('data_entrada')) {
            $dataDoFormulario = $request->input('data_entrada');
            $atendimentoData['data_entrada'] = Carbon::parse($dataDoFormulario)->setTimeFrom(Carbon::now());
        } else {
            $atendimentoData['data_entrada'] = Carbon::now();
        }

        $anoAtual = now()->year;
        $codigoUnico = false;
        $novoCodigoConsulta = '';
        while (!$codigoUnico) {
            $parteNumerica = random_int(10000, 99999);
            $novoCodigoConsulta = $parteNumerica . '-' . $anoAtual;
            $existe = Atendimento::where('codigo_consulta', $novoCodigoConsulta)->exists();
            if (!$existe) {
                $codigoUnico = true;
            }
        }
        $atendimentoData['codigo_consulta'] = $novoCodigoConsulta;
        $atendimentoData['status'] = 'Em diagnóstico'; // Status GERAL inicial padrão

        // Define o status_pagamento inicial. Se não vier do form, usa o default do BD ('Pendente').
        // Se vier do form e for válido, usa o valor do form.
        if (!$request->filled('status_pagamento')) {
            // Não precisa fazer nada aqui, o default do banco será 'Pendente'
            // Mas se você quiser garantir que seja 'Pendente' via código:
            // $atendimentoData['status_pagamento'] = 'Pendente';
        } else {
            $atendimentoData['status_pagamento'] = $request->input('status_pagamento');
        }


        $atendimento = Atendimento::create($atendimentoData); // Cria o atendimento com todos os dados

        return redirect()->route('atendimentos.show', $atendimento->id)
            ->with('success', "Atendimento #{$atendimento->id} registrado! Cód. Consulta: {$novoCodigoConsulta}");
    }

    /**
     * Display the specified resource.
     */
    public function show(Atendimento $atendimento)
    {
        $atendimento->load('cliente', 'tecnico', 'saidasEstoque.estoque');
        return view('atendimentos.show', compact('atendimento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Atendimento $atendimento)
    {
        if (Gate::denies('is-internal-user')) { // Ou uma permissão mais específica como 'editar-atendimento'
            return redirect()->route('atendimentos.index')->with('error', 'Acesso não autorizado.');
        }
        $clientes = Cliente::orderBy('nome_completo')->get();
        $tecnicos = User::where('tipo_usuario', 'tecnico')->orderBy('name')->get();
        $formasPagamento = ['Dinheiro', 'Cartão de Débito', 'Cartão de Crédito', 'PIX', 'Boleto', 'Outro']; // Ou de um config/enum
        return view('atendimentos.edit', compact('atendimento', 'clientes', 'tecnicos', 'formasPagamento'));
    }
    // Em app/Http/Controllers/AtendimentoController.php
    public function update(Request $request, Atendimento $atendimento)
    {
        // Verificação de permissão básica para editar
        if (Gate::denies('is-internal-user')) {
            return redirect()->route('atendimentos.show', $atendimento->id)->with('error', 'Acesso não autorizado para editar.');
        }

        // Defina aqui os status que indicam que o pagamento foi efetivamente recebido
        $statusDePagamentoRecebido = ['Entregue', 'Finalizado e Pago', 'Pago']; // <<<< AJUSTE ESTA LISTA CONFORME SEUS STATUS

        // Validações dos dados recebidos
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'descricao_aparelho' => 'required|string|max:255',
            'problema_relatado' => 'required|string|max:255',
            // 'data_entrada' geralmente não é editável após a criação.
            'status' => ['required', 'string', Rule::in(Atendimento::getPossibleStatuses())], // Garanta que Atendimento::getPossibleStatuses() exista e retorne um array de strings
            'status_pagamento' => ['required', 'string', Rule::in(Atendimento::getPossiblePaymentStatuses())], // <<<< NOVO
            'tecnico_id' => 'nullable|exists:users,id',
            'data_conclusao' => [
                'nullable',
                'date', // Se o input for do tipo date. Se for datetime-local, use 'date_format:Y-m-d\TH:i'
                function ($attribute, $value, $fail) use ($atendimento) {
                    $dataEntradaOriginal = $atendimento->data_entrada; // Assumindo que data_entrada é um objeto Carbon devido a casts no model
                    if ($value && $dataEntradaOriginal && Carbon::parse($value)->lt($dataEntradaOriginal)) {
                        $fail('A data de conclusão não pode ser anterior à data de entrada original (' . $dataEntradaOriginal->format('d/m/Y') . ').');
                    }
                }
            ],
            'observacoes' => 'nullable|string', // Este campo pode ser 'observacoes_cliente' ou 'observacoes_tecnicas' dependendo do seu form. Ajuste se necessário.
            'laudo_tecnico' => 'nullable|string',
            'valor_servico' => ['nullable', 'numeric', 'min:0'],
            'desconto_servico' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request, $atendimento) {
                    // Pega o valor do serviço do request (se estiver sendo alterado) ou o valor original do atendimento.
                    $valorServicoParaValidacao = $request->input('valor_servico', $atendimento->getOriginal('valor_servico') ?? 0);
                    if (is_numeric($value) && is_numeric($valorServicoParaValidacao) && (float)$value > (float)$valorServicoParaValidacao) {
                        $fail('O desconto não pode ser maior que o valor do serviço.');
                    }
                }
            ],
            'forma_pagamento' => [
                Rule::requiredIf(function () use ($request, $statusDePagamentoRecebido) {
                    $valorServico = $request->input('valor_servico', 0);
                    $descontoServico = $request->input('desconto_servico', 0);
                    $valorCobrado = (float)$valorServico - (float)$descontoServico;
                    return in_array($request->input('status'), $statusDePagamentoRecebido) && $valorCobrado > 0;
                }),
                'nullable', // Permite ser nulo se não for um status de pagamento ou se valor for zero
                'string',
                'max:50',
                // Certifique-se que estas opções são as mesmas do seu <select> no formulário
                Rule::in(['Dinheiro', 'Cartão de Débito', 'Cartão de Crédito', 'PIX', 'Boleto', 'Outro', ''])
            ],
        ], [
            'status_pagamento.required' => 'O status do pagamento é obrigatório.',
            'status_pagamento.in' => 'O status de pagamento selecionado é inválido.',
            'forma_pagamento.required' => 'A forma de pagamento é obrigatória quando o status do pagamento é "Pago" e há valor a ser cobrado.',
        ]);

        // Captura valores ANTES do update para comparação posterior
        $statusAnterior = $atendimento->getOriginal('status');
        $statusPagamentoAnterior = $atendimento->getOriginal('status_pagamento'); // <<<< NOVO
        $valorServicoAnterior = (float)($atendimento->getOriginal('valor_servico') ?? 0);
        $descontoAnterior = (float)($atendimento->getOriginal('desconto_servico') ?? 0);
        $formaPagamentoAnterior = $atendimento->getOriginal('forma_pagamento');
        $valorLiquidoAnterior = $valorServicoAnterior - $descontoAnterior;

        // Lógica de permissão para campos específicos (laudo, valores)
        if ($request->has('laudo_tecnico') && $atendimento->laudo_tecnico !== $request->input('laudo_tecnico')) {
            if (Gate::denies('is-admin-or-tecnico')) {
                return redirect()->back()->withErrors(['laudo_tecnico' => 'Você não tem permissão para alterar o laudo técnico.'])->withInput();
            }
        }

        $valorServicoMudou = $request->filled('valor_servico') && (float)$atendimento->getOriginal('valor_servico') != (float)$request->valor_servico;
        $descontoServicoMudou = $request->filled('desconto_servico') && (float)($atendimento->getOriginal('desconto_servico') ?? 0) != (float)($request->desconto_servico ?? 0);

        if (($valorServicoMudou || $descontoServicoMudou) && Gate::denies('is-admin')) {
            return redirect()->back()->withErrors(['valor_servico' => 'Você não tem permissão para alterar valores financeiros.'])->withInput();
        }

        // Lógica de permissão para mudança de status (mantendo sua lógica original)
        $novoStatus = $validatedData['status'];
        if ($statusAnterior !== $novoStatus) {
            $permitidoMudarStatus = false;
            $usuarioAtual = Auth::user();
            // IMPORTANTE: Ajuste $usuarioAtual->tipo_usuario para $usuarioAtual->role ou o campo correto do seu User Model
            if ($usuarioAtual->tipo_usuario == 'admin') {
                $permitidoMudarStatus = true;
            } elseif ($usuarioAtual->tipo_usuario == 'tecnico' && !in_array($novoStatus, ['Entregue', 'Cancelado', 'Finalizado e Pago', 'Pago'])) { // Evita que técnico finalize pagamento
                $permitidoMudarStatus = true;
            } elseif ($usuarioAtual->tipo_usuario == 'atendente') {
                // Adapte sua lógica de transições permitidas para atendentes
                $transicoesAtendentePermitidas = [
                    'Em aberto' => ['Em diagnóstico', 'Cancelado'],
                    'Em diagnóstico' => ['Aguardando peça', 'Aguardando aprovação cliente', 'Em manutenção', 'Cancelado'],
                    'Aguardando peça' => ['Em manutenção', 'Cancelado', 'Pronto para entrega'],
                    'Aguardando aprovação cliente' => ['Em manutenção', 'Cancelado', 'Recusado pelo cliente'],
                    'Em manutenção' => ['Pronto para entrega', 'Aguardando peça', 'Cancelado'],
                    'Pronto para entrega' => ['Entregue', 'Pago', 'Finalizado e Pago', 'Cancelado'],
                ];
                if (isset($transicoesAtendentePermitidas[$statusAnterior]) && in_array($novoStatus, $transicoesAtendentePermitidas[$statusAnterior])) {
                    $permitidoMudarStatus = true;
                }
                // Se for um status inicial (ex: atendente criando um atendimento novo e definindo o primeiro status)
                if (Atendimento::getInitialStatuses() && in_array($novoStatus, Atendimento::getInitialStatuses())) {
                    $permitidoMudarStatus = true;
                }
            }

            if (!$permitidoMudarStatus) {
                return redirect()->back()->withErrors(['status' => 'Você não tem permissão para alterar o atendimento do status "' . $statusAnterior . '" para o status: "' . $novoStatus . '"'])->withInput();
            }
        }

        // Atualiza o atendimento com todos os dados validados
        $atendimento->update($validatedData);

        // ----- INÍCIO DA LÓGICA DE REGISTRO NO CAIXA -----
        $mensagemParaUsuario = "Atendimento #{$atendimento->id} atualizado com sucesso!";
        $feedbackTipo = 'success'; // Tipo de feedback padrão

        $novoStatusPagamentoAposUpdate = $atendimento->status_pagamento;
        $novoValorServico = (float)($atendimento->valor_servico ?? 0);
        $novoDesconto = (float)($atendimento->desconto_servico ?? 0);
        $novaFormaPagamento = $atendimento->forma_pagamento;
        $novoValorCobradoEfetivamente = $novoValorServico - $novoDesconto;

        $deveRegistrarNoCaixa = false;
        $mensagemAdicionalCaixa = '';

        Log::info("[CAIXA UPDATE EDICAO COMPLETA] Atendimento #{$atendimento->id}: Status Pgto Anterior: {$statusPagamentoAnterior}, Novo Status Pgto: {$novoStatusPagamentoAposUpdate}");
        Log::info("[CAIXA UPDATE] Atendimento #{$atendimento->id}: Valor Liq. Anterior: {$valorLiquidoAnterior}, Novo Valor Liq.: {$novoValorCobradoEfetivamente}");
        Log::info("[CAIXA UPDATE] Atendimento #{$atendimento->id}: Forma Pgto Anterior: {$formaPagamentoAnterior}, Nova Forma Pgto: {$novaFormaPagamento}");

        // Condição 1: Mudou de um status NÃO PAGO para um status PAGO nesta atualização
        if ($statusPagamentoAnterior !== 'Pago' && $novoStatusPagamentoAposUpdate === 'Pago') {
            $deveRegistrarNoCaixa = true;
            Log::info("[CAIXA UPDATE EDICAO COMPLETA] Atendimento #{$atendimento->id}: CONDIÇÃO PRINCIPAL MET - status_pagamento mudou para 'Pago'.");
        }
        // Condição Secundária: Já estava 'Pago' e continua 'Pago', mas os detalhes financeiros mudaram.
        elseif ($statusPagamentoAnterior === 'Pago' && $novoStatusPagamentoAposUpdate === 'Pago') {
            if (bccomp((string)$novoValorCobradoEfetivamente, (string)$valorLiquidoAnterior, 2) != 0 || $novaFormaPagamento !== $formaPagamentoAnterior) {
                $movimentacaoExistenteComNovosValores = MovimentacaoCaixa::where('referencia_tipo', Atendimento::class)
                    ->where('referencia_id', $atendimento->id)
                    ->where('valor', $novoValorCobradoEfetivamente)
                    ->where('forma_pagamento', $novaFormaPagamento)
                    ->latest('data_movimentacao')
                    ->first();

                if (!$movimentacaoExistenteComNovosValores) {
                    $deveRegistrarNoCaixa = true;
                    Log::info("[CAIXA UPDATE EDICAO COMPLETA] Atendimento #{$atendimento->id}: CONDIÇÃO SECUNDÁRIA MET - Já estava 'Pago', valor/forma mudou E NÃO existe mov. idêntica com os novos dados.");
                    session()->flash('warning_caixa_update', 'Os detalhes financeiros de um atendimento já pago foram alterados. Uma nova movimentação de caixa será gerada para refletir a alteração. Verifique o caixa.');
                } else {
                    Log::info("[CAIXA UPDATE EDICAO COMPLETA] Atendimento #{$atendimento->id}: Condição Secundária NÃO MET - Já estava 'Pago', valor/forma mudou, MAS JÁ EXISTE mov. idêntica.");
                }
            } else {
                Log::info("[CAIXA UPDATE EDICAO COMPLETA] Atendimento #{$atendimento->id}: Condição Secundária NÃO MET - Já estava 'Pago', e valor/forma NÃO mudaram.");
            }
        }

        if ($deveRegistrarNoCaixa) {
            if ($novoValorCobradoEfetivamente > 0 && !empty($novaFormaPagamento)) {
                Log::info("[CAIXA UPDATE EDICAO COMPLETA] Atendimento #{$atendimento->id}: Dados financeiros OK para registrar no caixa (Valor: {$novoValorCobradoEfetivamente}, Forma: {$novaFormaPagamento}).");
                $caixaAberto = Caixa::getCaixaAbertoAtual();

                if ($caixaAberto) {
                    // ... (mesma lógica de criação da MovimentacaoCaixa que você já tinha) ...
                    MovimentacaoCaixa::create([
                        'caixa_id' => $caixaAberto->id,
                        'usuario_id' => Auth::id(),
                        'tipo' => 'ENTRADA',
                        'descricao' => "Recebimento OS/Atendimento #" . $atendimento->id,
                        'valor' => $novoValorCobradoEfetivamente,
                        'forma_pagamento' => $novaFormaPagamento,
                        'referencia_id' => $atendimento->id,
                        'referencia_tipo' => Atendimento::class,
                        'data_movimentacao' => Carbon::now(),
                        'observacoes' => 'Pagamento de serviço (via Edição Completa).',
                    ]);
                    $mensagemAdicionalCaixa = ' Recebimento de R$ ' . number_format($novoValorCobradoEfetivamente, 2, ',', '.') . ' registrado no caixa #' . $caixaAberto->id . '.';

                    if (session()->has('warning_caixa_update')) {
                        $mensagemParaUsuario = session('warning_caixa_update') . $mensagemAdicionalCaixa;
                        $feedbackTipo = 'warning';
                    } else {
                        $mensagemParaUsuario .= $mensagemAdicionalCaixa;
                    }
                    Log::info("[CAIXA UPDATE EDICAO COMPLETA] Atendimento #{$atendimento->id}: Movimentação de R$ {$novoValorCobradoEfetivamente} registrada no caixa #{$caixaAberto->id}.");
                } else {
                    Log::warning("[CAIXA UPDATE EDICAO COMPLETA] Atendimento #{$atendimento->id}: Status Pagamento é 'Pago', mas caixa NÃO encontrado. Não registrou no caixa.");
                    $mensagemParaUsuario .= ' ATENÇÃO: Nenhum caixa aberto, o recebimento não foi automaticamente registrado no caixa.';
                    $feedbackTipo = 'warning';
                }
            } elseif ($novoStatusPagamentoAposUpdate === 'Pago') {
                Log::warning("[CAIXA UPDATE EDICAO COMPLETA] Atendimento #{$atendimento->id}: status_pagamento é 'Pago', mas valor é zero ou forma de pagamento não informada. Valor: {$novoValorCobradoEfetivamente}, FormaPgto: " . ($novaFormaPagamento ?? 'NULA/VAZIA'));
                $mensagemParaUsuario .= ' Status de Pagamento definido como "Pago", mas o valor cobrado é zero ou a forma de pagamento não foi informada. Nenhuma entrada no caixa foi realizada.';
                $feedbackTipo = 'info'; // Ou 'warning' dependendo da severidade que você quer dar
            }
        } else {
            Log::info("[CAIXA UPDATE EDICAO COMPLETA] Atendimento #{$atendimento->id}: Condição 'deveRegistrarNoCaixa' é FALSE. Nenhuma ação de caixa.");
        }
        // ----- FIM DA LÓGICA DE REGISTRO NO CAIXA -----

        // Lógica de notificação
        if ($atendimento->status == 'Pronto para entrega' && $statusAnterior != 'Pronto para entrega') {
            if ($atendimento->cliente && $atendimento->cliente->email) {
                try {
                    if (class_exists(AtendimentoProntoNotification::class)) {
                        $atendimento->cliente->notify(new AtendimentoProntoNotification($atendimento));
                    } else {
                        Log::warning("Classe de notificação AtendimentoProntoNotification não encontrada para atendimento ID: {$atendimento->id}");
                    }
                } catch (\Exception $e) {
                    Log::error("Erro ao enviar notificação 'Atendimento Pronto' para atendimento ID: {$atendimento->id} - Erro: " . $e->getMessage());
                }
            }
        }

        // Limpa a mensagem flash de warning_caixa para não persistir se não for mais necessária
        session()->forget('warning_caixa');

        return redirect()->route('atendimentos.show', $atendimento->id)
            ->with($feedbackTipo, $mensagemParaUsuario);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Atendimento $atendimento)
    {
        if (Gate::denies('is-admin-or-atendente')) {
            return redirect()->route('atendimentos.index')->with('error', 'Acesso não autorizado para excluir atendimentos.');
        }
        // As saídas de estoque vinculadas terão 'atendimento_id' setado para null devido ao ->nullable() na FK
        $atendimento->delete();
        return redirect()->route('atendimentos.index')->with('success', 'Atendimento excluído com sucesso!');
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
                if (is_numeric($search)) { // Permite buscar diretamente pelo ID do atendimento
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
        // O JavaScript na view de saidas_estoque/create fará o mapeamento para o formato label/value
        return response()->json($atendimentos);
    }

    /**
     * Gera o PDF/OS para o atendimento.
     */
    public function gerarPdf(Atendimento $atendimento)
    {
        $atendimento->load(['cliente', 'tecnico', 'saidasEstoque.estoque']);

        // Cálculos de valores
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

        // URL para o link clicável
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
            'emailEmpresa' => 'contato@jmcelulares.com.br', // Adicione o email da sua empresa
            'urlConsultaSite' => $urlConsulta,
            // Não precisamos mais de 'qrCodeBase64' se a imagem for estática
        ];

        $pdf = Pdf::loadView('atendimentos.pdf_template', $dadosParaPdf);
        // Opção para habilitar carregamento de imagens remotas e CSS se necessário (cuidado com segurança)
        // $pdf->setOption('isRemoteEnabled', true);
        // $pdf->setOption('isHtml5ParserEnabled', true);


        $nomeArquivo = 'OS_Atendimento_' . $atendimento->id . '_' . Str::slug($atendimento->cliente->nome_completo ?? 'cliente', '_') . '.pdf';
        return $pdf->stream($nomeArquivo);
    }

    /**
     * Atualiza o status de um atendimento via AJAX (usado pelo formulário rápido na view show).
     */
    public function atualizarStatus(Request $request, Atendimento $atendimento)
    {
        $request->validate([
            'status' => ['required', 'string', Rule::in(Atendimento::getPossibleStatuses())],
        ], [], ['status' => 'novo status']);

        $statusAnterior = $atendimento->status;
        $novoStatus = $request->input('status');

        // --- Sua lógica de permissão para mudança de status ---
        $permitidoMudarStatus = false;
        $usuarioAtual = Auth::user();
        // Adapte esta lógica para $usuarioAtual->role se for o caso
        if ($usuarioAtual->tipo_usuario == 'admin') {
            $permitidoMudarStatus = true;
        } elseif ($usuarioAtual->tipo_usuario == 'tecnico') {
            if (!in_array($novoStatus, ['Entregue', 'Cancelado', 'Finalizado e Pago', 'Pago'])) {
                $permitidoMudarStatus = true;
            }
        } elseif ($usuarioAtual->tipo_usuario == 'atendente') {
            $transicoesAtendentePermitidas = [
                'Em aberto' => ['Em diagnóstico', 'Cancelado'],
                'Em diagnóstico' => ['Aguardando peça', 'Aguardando aprovação cliente', 'Em manutenção', 'Cancelado'],
                'Aguardando peça' => ['Em manutenção', 'Cancelado', 'Pronto para entrega'],
                'Aguardando aprovação cliente' => ['Em manutenção', 'Cancelado', 'Recusado pelo cliente'],
                'Em manutenção' => ['Pronto para entrega', 'Aguardando peça', 'Cancelado'],
                'Pronto para entrega' => ['Entregue', 'Pago', 'Finalizado e Pago', 'Cancelado'],
            ];
            if (isset($transicoesAtendentePermitidas[$statusAnterior]) && in_array($novoStatus, $transicoesAtendentePermitidas[$statusAnterior])) {
                $permitidoMudarStatus = true;
            }
            if (Atendimento::getInitialStatuses() && in_array($novoStatus, Atendimento::getInitialStatuses())) {
                $permitidoMudarStatus = true;
            }
        }

        if (!$permitidoMudarStatus) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Você não tem permissão para alterar o status de "' . $statusAnterior . '" para: "' . $novoStatus . '"'], 403);
            }
            return redirect()->route('atendimentos.show', $atendimento->id)
                ->with('error', 'Você não tem permissão para alterar para este status.');
        }
        // --- Fim da lógica de permissão ---

        $atendimento->status = $novoStatus; // Atualiza o status GERAL
        $atendimento->save();

        $mensagemParaUsuario = 'Status geral atualizado para ' . $novoStatus . '!';
        $feedbackTipo = 'success';
        // $redirectToEditUrl = null;

        // Lógica de sugestão para edição completa se o status geral implica pagamento e o status de pagamento ainda está pendente.
        if ($novoStatus === 'Pronto para entrega' && $atendimento->status_pagamento === 'Pendente') {
            // MENSAGEM AJUSTADA: Instruir a usar o botão/modal de pagamento na própria tela show.
            $mensagemParaUsuario .= ' Para registrar o recebimento e finalizar, utilize o botão "Registrar Pagamento" nesta tela.';
            $feedbackTipo = 'info'; // Info é mais apropriado que warning aqui, pois é uma instrução.
            // REMOVER: $redirectToEditUrl = route('atendimentos.edit', $atendimento->id);
            Log::info("[AtualizarStatus Rápido] Atendimento #{$atendimento->id}: Status GERAL mudou para 'Pronto para entrega', status_pagamento é '{$atendimento->status_pagamento}'. Instruindo para usar modal de pagamento.");
        }


        // Lógica de notificação para "Pronto para entrega" (status GERAL)
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
            $responseData = [
                'success' => ($feedbackTipo !== 'danger'),
                'message' => $mensagemParaUsuario,
                'novo_status_geral' => $novoStatus,
                'novo_status_geral_classe_completa' => Atendimento::getStatusClass($novoStatus),
                'novo_status_geral_icon' => Atendimento::getStatusIcon($novoStatus),
                'feedback_tipo' => $feedbackTipo
                // REMOVER: 'redirect_to_edit' daqui, a menos que seja para outro cenário futuro.
            ];
            // if ($redirectToEditUrl) { // Esta condição não será mais atendida para o fluxo de "Pronto para entrega"
            //     $responseData['redirect_to_edit'] = $redirectToEditUrl;
            // }
            return response()->json($responseData);
        }

        // REMOVER: Redirecionamento para edit a partir daqui para este caso específico
        // if ($redirectToEditUrl) {
        //     return redirect($redirectToEditUrl)->with($feedbackTipo, $mensagemParaUsuario);
        // }

        return redirect()->route('atendimentos.show', $atendimento->id)
            ->with($feedbackTipo, $mensagemParaUsuario);
    }
    /**
     * Atualiza um campo específico de um atendimento via AJAX (usado para edição inline).
     */
    // Em app/Http/Controllers/AtendimentoController.php

    public function atualizarCampoAjax(Request $request, Atendimento $atendimento, $campo = null) // $campo agora é opcional
    {
        $dadosParaAtualizar = [];
        $regrasDeValidacao = [];
        $camposEditadosNomes = []; // Para a mensagem de sucesso

        // Se for uma requisição para atualizar múltiplos campos de valores
        if ($request->has('valor_servico') || $request->has('desconto_servico')) {
            if (Gate::denies('is-admin')) {
                return response()->json(['success' => false, 'message' => 'Você não tem permissão para alterar valores financeiros.'], 403);
            }

            if ($request->has('valor_servico') || $request->has('desconto_servico')) {
                $regrasDeValidacao['valor_servico'] = ['required', 'numeric', 'min:0'];
                $dadosParaAtualizar['valor_servico'] = $request->input('valor_servico');
                $camposEditadosNomes[] = 'Valor do Serviço';
            }
            if ($request->has('desconto_servico')) {
                $regrasDeValidacao['desconto_servico'] = ['required', 'numeric', 'min:0'];
                // Validação para garantir que o desconto não seja maior que o valor do serviço
                $valorServicoParaValidacao = $request->input('valor_servico', $atendimento->valor_servico); // Pega o novo valor do serviço se enviado, senão o atual
                $regrasDeValidacao['desconto_servico'][] = function ($attribute, $value, $fail) use ($valorServicoParaValidacao) {
                    if (floatval($value) > floatval($valorServicoParaValidacao)) {
                        $fail('O desconto não pode ser maior que o valor do serviço.');
                    }
                };
                $dadosParaAtualizar['desconto_servico'] = $request->input('desconto_servico');
                $camposEditadosNomes[] = 'Desconto';
            }
        }
        // Se for uma requisição para atualizar um campo único (Observações ou Laudo)
        elseif ($campo && in_array($campo, ['observacoes', 'laudo_tecnico'])) { // Exemplo de verificação mais explícita
            $camposPermitidosEValidacao = [
                'observacoes' => ['nullable', 'string'],
                'laudo_tecnico' => ['nullable', 'string'],
            ];

            if (!array_key_exists($campo, $camposPermitidosEValidacao)) {
                return response()->json(['success' => false, 'message' => 'Campo inválido para atualização.'], 400);
            }

            // Permissão específica para o campo
            $permissaoNecessaria = 'is-admin-or-tecnico'; // Ajuste conforme necessário
            if (Gate::denies($permissaoNecessaria)) {
                return response()->json(['success' => false, 'message' => 'Você não tem permissão para editar este campo.'], 403);
            }

            $regrasDeValidacao[$campo] = $camposPermitidosEValidacao[$campo];
            $dadosParaAtualizar[$campo] = $request->input($campo);
            $camposEditadosNomes[] = ucfirst(str_replace('_', ' ', $campo));
        }
        // Se nenhum campo válido foi fornecido
        else {
            return response()->json(['success' => false, 'message' => 'Nenhum campo válido para atualização fornecido.'], 400);
        }

        if (empty($dadosParaAtualizar)) {
            return response()->json(['success' => false, 'message' => 'Nenhum dado para atualizar.'], 400);
        }

        $request->validate($regrasDeValidacao); // Valida os dados que foram preparados

        try {
            $atendimento->update($dadosParaAtualizar); // Atualiza apenas os campos presentes em $dadosParaAtualizar

            // Recalcular valores para a resposta JSON
            $valorServicoAtualizado = $atendimento->valor_servico ?? 0;
            $descontoServicoAtualizado = $atendimento->desconto_servico ?? 0;
            $valorServicoLiquidoAtualizado = $valorServicoAtualizado - $descontoServicoAtualizado;

            $valorTotalPecas = 0; // Recalcular valor das peças
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
                'novos_valores' => [ // Envia todos os valores relevantes de volta
                    'valor_servico' => number_format($valorServicoAtualizado, 2, ',', '.'),
                    'desconto_servico' => number_format($descontoServicoAtualizado, 2, ',', '.'),
                    'subtotal_servico' => number_format($valorServicoLiquidoAtualizado, 2, ',', '.'),
                    'valor_total_pecas' => number_format($valorTotalPecas, 2, ',', '.'), // Pode não mudar, mas bom ter
                    'valor_total_atendimento' => number_format($valorTotalAtendimentoAtualizado, 2, ',', '.'),
                    'observacoes' => $atendimento->observacoes, // Envia também para caso o update seja misto
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

    public function atualizarValoresServicoAjax(Request $request, Atendimento $atendimento)
    {
        if (Gate::denies('is-admin')) {
            return response()->json(['success' => false, 'message' => 'Você não tem permissão para alterar valores financeiros.'], 403);
        }

        $regrasDeValidacao = [
            'valor_servico' => ['required', 'numeric', 'min:0'],
            'desconto_servico' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    $valorServicoParaValidacao = $request->input('valor_servico');
                    if (floatval($value) > floatval($valorServicoParaValidacao)) {
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

            // Recalcular totais para a resposta
            $valorServicoAtualizado = $atendimento->valor_servico ?? 0;
            $descontoServicoAtualizado = $atendimento->desconto_servico ?? 0;
            $valorServicoLiquidoAtualizado = $valorServicoAtualizado - $descontoServicoAtualizado;

            $valorTotalPecas = 0;
            if ($atendimento->saidasEstoque()->exists()) { // Verifica se há saídas
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
     * Registra o pagamento de um atendimento via AJAX a partir de um modal.
     * Atualiza o status_pagamento para 'Pago', os valores financeiros e registra no caixa.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Atendimento  $atendimento
     * @return \Illuminate\Http\JsonResponse
     */
    public function registrarPagamentoAjax(Request $request, Atendimento $atendimento)
    {
        if (Gate::denies('gerenciar-caixa')) { // Ou uma permissão mais específica
            return response()->json(['success' => false, 'message' => 'Você não tem permissão para registrar pagamentos.'], 403);
        }
        // VERIFICAÇÃO ADICIONAL PARA OPÇÃO 1
        $statusGeraisPermitemPagamento = ['Pronto para entrega', 'Aguardando aprovação cliente']; // Mesma lista da view
        if (!in_array($atendimento->status, $statusGeraisPermitemPagamento)) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível registrar o pagamento neste status atual do serviço (' . $atendimento->status . '). Avance o serviço primeiro.'
            ], 400); // Bad Request
        }

        if (!in_array($atendimento->status_pagamento, ['Pendente', 'Parcialmente Pago'])) {
            return response()->json(['success' => false, 'message' => 'O pagamento para este atendimento não está pendente ou já foi processado de outra forma.'], 400);
        }

        // Validação dos dados do modal
        $validator = Validator::make($request->all(), [
            'valor_servico' => ['required', 'numeric', 'min:0'], // Admin pode ajustar
            'desconto_servico' => [
                'required', // Mesmo que seja 0, é bom ter o campo
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    $valorServicoParaValidacao = $request->input('valor_servico', 0);
                    if (floatval($value) > floatval($valorServicoParaValidacao)) {
                        $fail('O desconto não pode ser maior que o valor do serviço.');
                    }
                }
            ],
            'forma_pagamento' => ['required', 'string', 'max:50', Rule::in(config('constants.formas_pagamento', ['Dinheiro', 'Cartão de Débito', 'Cartão de Crédito', 'PIX', 'Boleto', 'Outro']))], // Carregue as opções de forma consistente
            'observacoes_pagamento' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Erro de validação.', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $valorServicoAntigo = (float) ($atendimento->valor_servico ?? 0);
            $descontoServicoAntigo = (float) ($atendimento->desconto_servico ?? 0);

            // Atualiza os dados financeiros do atendimento
            $atendimento->valor_servico = (float) $request->input('valor_servico');
            $atendimento->desconto_servico = (float) $request->input('desconto_servico');
            $atendimento->forma_pagamento = $request->input('forma_pagamento');
            $atendimento->status_pagamento = 'Pago'; // Define como Pago

            // Adiciona observação do pagamento às observações gerais do atendimento
            if ($request->filled('observacoes_pagamento')) {
                $obsPagamento = "Pagamento Registrado (" . Carbon::now()->format('d/m/Y H:i') . "):\n" .
                    $request->input('observacoes_pagamento') . "\n---";
                $atendimento->observacoes = $atendimento->observacoes ? $atendimento->observacoes . "\n" . $obsPagamento : $obsPagamento;
            }
            $atendimento->save();

            // Calcula o valor efetivamente cobrado
            $valorCobradoEfetivamente = $atendimento->valor_servico - $atendimento->desconto_servico;

            // Lógica de registro no caixa (apenas se houver valor)
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
                    // Pode-se decidir se isso é um erro que impede o commit ou apenas um aviso.
                    // Por segurança, se o caixa for crucial, talvez devesse impedir aqui.
                    // DB::rollBack();
                    // return response()->json(['success' => false, 'message' => 'Nenhum caixa aberto para registrar o pagamento.'], 400);
                }
            }

            // Notificação de "Pronto para entrega" (se o status geral permitir e ainda não foi enviado)
            if ($atendimento->status == 'Pronto para entrega') {
                // Aqui você pode adicionar uma verificação para não reenviar a notificação se já foi enviada para este status.
                // Ex: if (!$atendimento->notificacao_pronto_enviada) { ... $atendimento->notificacao_pronto_enviada = true; $atendimento->save(); }
                if ($atendimento->cliente && $atendimento->cliente->email) {
                    try {
                        $atendimento->cliente->notify(new AtendimentoProntoNotification($atendimento));
                    } catch (\Exception $e) {
                        Log::error("Erro ao enviar notificação 'Pronto' (pagamento AJAX) para atend. #{$atendimento->id}: " . $e->getMessage());
                    }
                }
            }

            DB::commit();

            // Prepara dados para atualizar a view dinamicamente
            // RECARREGAR o atendimento com as relações necessárias para a resposta JSON
            $atendimento->refresh(); // Atualiza o modelo com os dados do BD (incluindo o status_pagamento salvo)
            $atendimento->load(['cliente', 'saidasEstoque.estoque']); // Garante que as relações estão carregadas

            // Prepara dados para atualizar a view dinamicamente
            $valorTotalPecasAtualizado = 0;
            // Agora é seguro acessar saidasEstoque e estoque pois foram carregados
            if ($atendimento->saidasEstoque && $atendimento->saidasEstoque->isNotEmpty()) {
                foreach ($atendimento->saidasEstoque as $saida) {
                    if ($saida->estoque) {
                        $valorTotalPecasAtualizado += $saida->quantidade * ($saida->estoque->preco_venda ?? 0);
                    }
                }
            }
            $valorServicoLiquidoAtualizado = ($atendimento->valor_servico ?? 0) - ($atendimento->desconto_servico ?? 0);
            $valorTotalAtendimentoAtualizado = $valorServicoLiquidoAtualizado + $valorTotalPecasAtualizado;


            return response()->json([
                'success' => true,
                'message' => 'Pagamento registrado com sucesso!' . $mensagemAdicionalCaixa,
                'novos_valores_atendimento' => [
                    'valor_servico' => number_format($atendimento->valor_servico ?? 0, 2, ',', '.'),
                    'desconto_servico' => number_format($atendimento->desconto_servico ?? 0, 2, ',', '.'),
                    'subtotal_servico' => number_format($valorServicoLiquidoAtualizado, 2, ',', '.'),
                    'valor_total_pecas' => number_format($valorTotalPecasAtualizado, 2, ',', '.'),
                    'valor_total_atendimento' => number_format($valorTotalAtendimentoAtualizado, 2, ',', '.'),
                ],
                'novo_status_pagamento_html' => view('atendimentos.partials._status_pagamento_badge', ['status_pagamento' => $atendimento->status_pagamento])->render(),
                'observacoes_atualizadas' => nl2br(e($atendimento->observacoes ?? '')), // Adicionado ?? '' para garantir string
                'atendimento_id' => $atendimento->id
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error("Erro de VALIDAÇÃO ao registrar pagamento AJAX para Atendimento #{$atendimento->id}: ", $e->errors());
            return response()->json(['success' => false, 'message' => 'Erro de validação ao registrar pagamento.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro GERAL ao registrar pagamento AJAX para Atendimento #{$atendimento->id}: " . $e->getMessage());
            return response()->json([
                'success' => true,
                'message' => 'Pagamento registrado com sucesso!' . $mensagemAdicionalCaixa,
                'novos_valores_atendimento' => [ /* ... */],
                'novo_status_pagamento_html' => view('atendimentos.partials._status_pagamento_badge', ['status_pagamento' => $atendimento->status_pagamento])->render(),
                'novo_status_pagamento_texto' => $atendimento->status_pagamento, // <<<< ADICIONAR ESTA LINHA
                'observacoes_atualizadas' => nl2br(e($atendimento->observacoes ?? '')),
                'atendimento_id' => $atendimento->id
            ]);
        }
    }
}

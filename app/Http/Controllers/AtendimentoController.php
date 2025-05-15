<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\Cliente;
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
            'problema_relatado' => 'required|string',
            'data_entrada' => 'required|date',
            'tecnico_id' => 'nullable|exists:users,id',
        ], [
            'cliente_id.required' => 'O cliente é obrigatório. Por favor, selecione um cliente da lista ou cadastre um novo.',
            'descricao_aparelho.required' => 'A descrição do aparelho é obrigatória.',
            'problema_relatado.required' => 'O problema relatado é obrigatório.',
            'data_entrada.required' => 'A data de entrada é obrigatória.',
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
        $atendimentoData['status'] = 'Em diagnóstico'; // Status inicial padrão

        $atendimento = Atendimento::create($atendimentoData);

        return redirect()->route('atendimentos.show', $atendimento->id)
            ->with('success', 'Atendimento Nº ' . $atendimento->id . ' registrado com sucesso! Código de consulta: ' . $novoCodigoConsulta);
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
        return view('atendimentos.edit', compact('atendimento', 'clientes', 'tecnicos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Atendimento $atendimento)
    {
        if (Gate::denies('is-internal-user')) {
            return redirect()->route('atendimentos.show', $atendimento->id)->with('error', 'Acesso não autorizado para editar.');
        }

        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'descricao_aparelho' => 'required|string|max:255',
            'problema_relatado' => 'required|string',
            // 'data_entrada' não é editável aqui
            'status' => ['required', 'string', Rule::in(Atendimento::getPossibleStatuses())],
            'tecnico_id' => 'nullable|exists:users,id',
            'data_conclusao' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($atendimento) { // Removido $request aqui
                    $dataEntradaOriginal = $atendimento->data_entrada;
                    if ($value && $dataEntradaOriginal && Carbon::parse($value)->lt($dataEntradaOriginal)) {
                        $fail('A data de conclusão não pode ser anterior à data de entrada original (' . $dataEntradaOriginal->format('d/m/Y') . ').');
                    }
                }
            ],
            'observacoes' => 'nullable|string',
            'laudo_tecnico' => 'nullable|string',
            'valor_servico' => 'nullable|numeric|min:0',
            'desconto_servico' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request, $atendimento) {
                    $valorServico = $request->input('valor_servico', $atendimento->valor_servico ?? 0);
                    if ($value > $valorServico) {
                        $fail('O desconto não pode ser maior que o valor do serviço.');
                    }
                }
            ],
        ]);

        $statusAntigo = $atendimento->status;
        $novoStatus = $validatedData['status'];

        // Permissões para campos específicos
        if ($request->has('laudo_tecnico') && $atendimento->laudo_tecnico !== $request->input('laudo_tecnico')) {
            if (Gate::denies('is-admin-or-tecnico')) {
                return redirect()->back()->withErrors(['laudo_tecnico' => 'Você não tem permissão para alterar o laudo técnico.'])->withInput();
            }
        }

        $valorServicoMudou = $request->filled('valor_servico') && $atendimento->valor_servico != $request->valor_servico;
        $descontoServicoMudou = $request->filled('desconto_servico') && $atendimento->desconto_servico != $request->desconto_servico;

        if (($valorServicoMudou || $descontoServicoMudou) && Gate::denies('is-admin')) {
            return redirect()->back()->withErrors(['valor_servico' => 'Você não tem permissão para alterar valores financeiros.'])->withInput();
        }

        // Lógica de permissão para mudança de status
        if ($statusAntigo !== $novoStatus) {
            $permitidoMudarStatus = false;
            $usuarioAtual = Auth::user();
            if ($usuarioAtual->tipo_usuario == 'admin') {
                $permitidoMudarStatus = true;
            } elseif ($usuarioAtual->tipo_usuario == 'tecnico' && !in_array($novoStatus, ['Entregue', 'Cancelado'])) { // Exemplo de restrição para técnico
                $permitidoMudarStatus = true;
            } elseif ($usuarioAtual->tipo_usuario == 'atendente' && in_array($novoStatus, ['Em diagnóstico', 'Aguardando peça', 'Pronto para entrega', 'Entregue'])) {
                $permitidoMudarStatus = true;
                if (($statusAntigo == 'Pronto para entrega' || $statusAntigo == 'Em diagnóstico' || $statusAntigo == 'Aguardando peça' || $statusAntigo == 'Em manutenção') && $novoStatus == 'Entregue')
                    $permitidoMudarStatus = true;
                if (($statusAntigo == 'Em diagnóstico' || $statusAntigo == 'Aguardando peça') && $novoStatus == 'Pronto para entrega')
                    $permitidoMudarStatus = true;


            }
            if (!$permitidoMudarStatus) {
                return redirect()->back()->withErrors(['status' => 'Você não tem permissão para alterar o atendimento para o status: ' . $novoStatus])->withInput();
            }
        }

        $atendimento->update($validatedData);

        if ($atendimento->status == 'Pronto para entrega' && $statusAntigo != 'Pronto para entrega') {
            if ($atendimento->cliente && $atendimento->cliente->email) {
                try {
                    $atendimento->cliente->notify(new AtendimentoProntoNotification($atendimento));
                } catch (\Exception $e) {
                    Log::error("Erro ao enviar notificação 'Atendimento Pronto' para atendimento ID: {$atendimento->id} - Erro: " . $e->getMessage());
                    // Considerar adicionar um flash message para o usuário sobre o erro de notificação, mas continuar com o sucesso da atualização.
                }
            }
        }

        return redirect()->route('atendimentos.show', $atendimento->id)->with('success', 'Atendimento atualizado com sucesso!');
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

        $dadosParaPdf = [
            'atendimento' => $atendimento,
            'valorTotalPecas' => $valorTotalPecas,
            'valorServicoLiquido' => $valorServicoLiquido,
            'valorTotalAtendimento' => $valorTotalAtendimento,
            'dataImpressao' => Carbon::now(),
        ];
        $pdf = Pdf::loadView('atendimentos.pdf_template', $dadosParaPdf);
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
        ], [], ['status' => 'novo status']); // Custom attribute name para mensagens de erro

        $statusAntigo = $atendimento->status;
        $novoStatus = $request->input('status');

        // Lógica de permissão para mudança de status
        $permitidoMudarStatus = false;
        $usuarioAtual = Auth::user();
        // Adapte esta lógica de permissão conforme suas regras de negócio detalhadas
        if ($usuarioAtual->tipo_usuario == 'admin') {
            $permitidoMudarStatus = true;
        } elseif ($usuarioAtual->tipo_usuario == 'tecnico') {
            if (!in_array($novoStatus, ['Entregue', 'Cancelado'])) {
                $permitidoMudarStatus = true;
            } // Exemplo
        } elseif ($usuarioAtual->tipo_usuario == 'atendente') {
            if (in_array($novoStatus, ['Em diagnóstico', 'Pronto para entrega', 'Entregue'])) { // Exemplo
                $permitidoMudarStatus = true;
            }
            if (($statusAntigo == 'Pronto para entrega' || $statusAntigo == 'Em diagnóstico' || $statusAntigo == 'Aguardando peça' || $statusAntigo == 'Em manutenção') && $novoStatus == 'Entregue')
                $permitidoMudarStatus = true;
            if (($statusAntigo == 'Em diagnóstico' || $statusAntigo == 'Aguardando peça') && $novoStatus == 'Pronto para entrega')
                $permitidoMudarStatus = true;
        }

        if (!$permitidoMudarStatus) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Você não tem permissão para alterar o status para: ' . $novoStatus], 403);
            }
            return redirect()->route('atendimentos.show', $atendimento->id)
                ->with('error_status', 'Você não tem permissão para alterar para este status.');
        }

        $atendimento->status = $novoStatus;
        $atendimento->save();

        if ($atendimento->status == 'Pronto para entrega' && $statusAntigo != 'Pronto para entrega') {
            if ($atendimento->cliente && $atendimento->cliente->email) {
                try {
                    $atendimento->cliente->notify(new AtendimentoProntoNotification($atendimento));
                } catch (\Exception $e) {
                    Log::error("Erro ao enviar notificação 'Atendimento Pronto' (atualizarStatus) para atendimento ID: {$atendimento->id} - Erro: " . $e->getMessage());
                }
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status atualizado para ' . $novoStatus . '!',
                'novo_status' => $novoStatus,
                'novo_status_classe_completa' => Atendimento::getStatusClass($novoStatus),
                'novo_status_icon' => Atendimento::getStatusIcon($novoStatus), // Se você quiser atualizar o ícone também
            ]);
        }

        return redirect()->route('atendimentos.show', $atendimento->id)
            ->with('success', 'Status do atendimento atualizado com sucesso!');
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
}
<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use App\Models\Cliente; // Precisamos para o formulário de novo atendimento
use App\Models\User; // Para listar os técnicos
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;     // Para manipulação de datas
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Auth;
use App\Notifications\AtendimentoProntoNotification;
use Illuminate\Support\Facades\Notification;

class AtendimentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Atendimento::with('cliente', 'tecnico'); // Eager load

        // Filtro por Status
        if ($request->filled('filtro_status')) {
            $query->where('status', $request->input('filtro_status'));
        }

        // Filtro por Técnico Responsável
        if ($request->filled('filtro_tecnico_id')) {
            $filtroTecnicoId = $request->input('filtro_tecnico_id');
            if ($filtroTecnicoId === '0') { // String '0' para "Não Atribuído"
                $query->whereNull('tecnico_id');
            } else {
                $query->where('tecnico_id', $filtroTecnicoId);
            }
        }

        // Filtro por Período (Data de Entrada)
        if ($request->filled('data_inicial_filtro') && $request->filled('data_final_filtro')) {
            $dataInicial = Carbon::parse($request->input('data_inicial_filtro'))->startOfDay();
            $dataFinal = Carbon::parse($request->input('data_final_filtro'))->endOfDay();
            if ($dataInicial->lte($dataFinal)) { // lte = Less than or equal to
                $query->whereBetween('data_entrada', [$dataInicial, $dataFinal]);
            } else {
                // Opcional: Adicionar um erro se a data inicial for maior que a final
                // return back()->withErrors(['data_inicial_filtro' => 'A data inicial não pode ser maior que a data final.'])->withInput();
            }
        } elseif ($request->filled('data_inicial_filtro')) { // Apenas data inicial
            $dataInicial = Carbon::parse($request->input('data_inicial_filtro'))->startOfDay();
            $query->where('data_entrada', '>=', $dataInicial);
        } elseif ($request->filled('data_final_filtro')) { // Apenas data final
            $dataFinal = Carbon::parse($request->input('data_final_filtro'))->endOfDay();
            $query->where('data_entrada', '<=', $dataFinal);
        }


        // Filtro por Busca (Nome do Cliente, CPF/CNPJ do Cliente, Código do Atendimento, ID do Atendimento)
        if ($request->filled('busca_atendimento')) {
            $searchTerm = $request->input('busca_atendimento');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', '%' . $searchTerm . '%') // Busca por ID do atendimento
                    ->orWhere('codigo_consulta', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('cliente', function ($clienteQuery) use ($searchTerm) {
                        $clienteQuery->where('nome_completo', 'like', '%' . $searchTerm . '%')
                            ->orWhere('cpf_cnpj', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        $atendimentos = $query->orderBy('data_entrada', 'desc')->paginate(15); // Ordena pelos mais recentes
        $atendimentos->appends($request->query()); // Mantém os filtros na paginação

        // Para os dropdowns de filtro na view
        $todosOsStatus = Atendimento::select('status')->distinct()->orderBy('status')->pluck('status');
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
    public function create(Request $request) // Adicionado Request
    {
        // $clientes = Cliente::all(); // Não é mais necessário para o select
        $tecnicos = User::where('tipo_usuario', 'tecnico')->get();

        // Para pré-selecionar cliente se vier de outra página (ex: botão "Novo Atendimento" na ficha do cliente)
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
            'descricao_aparelho' => 'required|string|max:20',
            'problema_relatado' => 'required|string',
            'data_entrada' => 'required|date',
            'tecnico_id' => 'nullable|exists:users,id',
        ]);

        $atendimentoData = $request->except('data_entrada');

        // Processar a data_entrada para incluir a hora atual (como já fizemos)
        if ($request->filled('data_entrada')) {
            $dataDoFormulario = $request->input('data_entrada');
            $atendimentoData['data_entrada'] = Carbon::parse($dataDoFormulario)->setTimeFrom(Carbon::now());
        } else {
            $atendimentoData['data_entrada'] = Carbon::now();
        }

        // --- NOVA LÓGICA PARA GERAR O CÓDIGO DE CONSULTA ---
        $anoAtual = now()->year; // Pega o ano atual (ex: 2025)
        $codigoUnico = false;
        $novoCodigoConsulta = '';

        // Loop para garantir unicidade (embora colisões sejam raras com 5 dígitos + ano)
        while (!$codigoUnico) {
            $parteNumerica = random_int(10000, 99999); // Gera um número aleatório de 5 dígitos
            // Você pode querer mais ou menos dígitos. Ex: random_int(1000, 9999) para 4 dígitos.
            // Ou usar Str::random(5) se preferir alfanumérico, mas números são mais fáceis de ditar.
            // Se usar Str::upper(Str::random(5)) para alfanumérico:
            // $parteNumerica = Str::upper(Str::random(5));

            $novoCodigoConsulta = $parteNumerica . '-' . $anoAtual;

            // Verifica se o código já existe no banco
            $existe = Atendimento::where('codigo_consulta', $novoCodigoConsulta)->exists();
            if (!$existe) {
                $codigoUnico = true;
            }
        }
        $atendimentoData['codigo_consulta'] = $novoCodigoConsulta;
        // --- FIM DA NOVA LÓGICA ---

        Atendimento::create($atendimentoData);

        return redirect()->route('atendimentos.index')->with('success', 'Atendimento registrado com sucesso! Código de consulta: ' . $novoCodigoConsulta);
    }
    /**
     * Display the specified resource.
     */
    public function show(Atendimento $atendimento)
    {
        // Carrega o cliente, o técnico e as saídas de estoque com os detalhes da peça de cada saída
        $atendimento->load('cliente', 'tecnico', 'saidasEstoque.estoque');

        return view('atendimentos.show', compact('atendimento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Atendimento $atendimento)
    {
        $clientes = Cliente::all();
        $tecnicos = User::where('tipo_usuario', 'tecnico')->get();
        return view('atendimentos.edit', compact('atendimento', 'clientes', 'tecnicos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Atendimento $atendimento)
    {
        // Validações - Remova a regra para 'data_entrada'
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'descricao_aparelho' => 'required|string|max:20',
            'problema_relatado' => 'required|string',
            // 'data_entrada' => 'required|date_format:Y-m-d\TH:i', // REMOVIDO DA VALIDAÇÃO
            'status' => 'required|string|max:50',
            'tecnico_id' => 'nullable|exists:users,id',
            'data_conclusao' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($request, $atendimento) {
                    // Pega a data de entrada do modelo, já que não vem mais do request para update
                    $dataEntrada = $atendimento->data_entrada;
                    if ($value && $dataEntrada && Carbon::parse($value)->lt($dataEntrada)) {
                        $fail('A data de conclusão não pode ser anterior à data de entrada.');
                    }
                }
            ],
            'observacoes' => 'nullable|string',
            'laudo_tecnico' => 'nullable|string',
            'valor_servico' => 'nullable|numeric|min:0',
            'desconto_servico' => 'nullable|numeric|min:0',
        ]);

        $statusAntigo = $atendimento->status;
        // Proteção para o campo laudo_tecnico
        // Se o campo 'laudo_tecnico' FOI ENVIADO no request E O VALOR MUDOU
        if ($request->has('laudo_tecnico') && $atendimento->laudo_tecnico !== $request->input('laudo_tecnico')) {
            if (Gate::denies('is-admin-or-tecnico')) {
                // Log para depuração
                // \Illuminate\Support\Facades\Log::warning('Tentativa não autorizada de alterar laudo por: ' . Auth::id());
                return redirect()->back()->withErrors(['laudo_tecnico' => 'Você não tem permissão para alterar o laudo técnico.'])->withInput();
            }
        }

        // Proteção para campos de valor (só admin)
        // Se os campos de valor FORAM ENVIADOS e os valores MUDARAM
        $valorServicoMudou = $request->filled('valor_servico') && $atendimento->valor_servico != $request->valor_servico;
        $descontoServicoMudou = $request->filled('desconto_servico') && $atendimento->desconto_servico != $request->desconto_servico;

        if ($valorServicoMudou || $descontoServicoMudou) {
            if (Gate::denies('is-admin')) {
                // Log para depuração
                // \Illuminate\Support\Facades\Log::warning('Tentativa não autorizada de alterar valores por: ' . Auth::id());
                return redirect()->back()->withErrors(['valor_servico' => 'Você não tem permissão para alterar valores financeiros.'])->withInput();
            }
        }

        // Validação do desconto (não pode ser maior que o valor do serviço)
        if ($request->filled('desconto_servico') && $request->input('desconto_servico') > $request->input('valor_servico', $atendimento->valor_servico ?? 0)) {
            return back()->withErrors(['desconto_servico' => 'O desconto não pode ser maior que o valor do serviço.'])->withInput();
        }

        // Se chegou até aqui, as permissões para laudo e valor (se alterados) foram verificadas.
        // Agora, vamos verificar a permissão para alterar o STATUS.
        // Quem pode alterar o status? Vamos definir algumas regras:
        // - Admin: Pode alterar para qualquer status.
        // - Técnico: Pode alterar para a maioria dos status (exceto talvez cancelar/excluir).
        // - Atendente: Pode alterar para status iniciais ou para "Pronto para entrega" se essa for a política.

        $novoStatus = $request->input('status');
        if ($statusAntigo !== $novoStatus) { // Só verifica permissão de status se o status realmente mudou
            $permitidoMudarStatus = false;
            $usuarioAtual = Auth::user();

            if ($usuarioAtual->tipo_usuario == 'admin') {
                $permitidoMudarStatus = true;
            } elseif ($usuarioAtual->tipo_usuario == 'tecnico') {
                // Técnico pode mudar para a maioria dos status, exceto talvez alguns finais ou de cancelamento.
                // Exemplo: Técnico não pode mudar para "Entregue" (só admin/atendente)
                if (!in_array($novoStatus, ['Entregue'])) { // Adicione outros status restritos ao técnico aqui
                    $permitidoMudarStatus = true;
                }
            } elseif ($usuarioAtual->tipo_usuario == 'atendente') {
                // Atendente pode mudar para status iniciais ou para "Pronto para entrega"
                if (in_array($novoStatus, ['Em diagnóstico', 'Aguardando peça', 'Pronto para entrega'])) { // Adicione outros permitidos
                    $permitidoMudarStatus = true;
                }
                // Se o status antigo já era "Pronto para entrega", o atendente talvez possa mudar para "Entregue"
                if ($statusAntigo == 'Pronto para entrega' && $novoStatus == 'Entregue') {
                    $permitidoMudarStatus = true;
                }
            }

            if (!$permitidoMudarStatus) {
                // Log para depuração
                // \Illuminate\Support\Facades\Log::warning('Tentativa não autorizada de alterar status para "'.$novoStatus.'" por: ' . Auth::id() . ' (Tipo: ' . $usuarioAtual->tipo_usuario . ')');
                return redirect()->back()->withErrors(['status' => 'Você não tem permissão para alterar o atendimento para este status: ' . $novoStatus])->withInput();
            }
        }


        // Se todas as verificações de permissão passaram (para laudo, valor, status):
        $dadosParaUpdate = [
            'cliente_id' => $validatedData['cliente_id'],
            'celular' => $validatedData['celular'],
            'problema_relatado' => $validatedData['problema_relatado'],
            'status' => $validatedData['status'],
            'tecnico_id' => $validatedData['tecnico_id'] ?? null, // Garante null se não presente
            'data_conclusao' => $validatedData['data_conclusao'] ?? null,
            'observacoes' => $validatedData['observacoes'] ?? null,
            'laudo_tecnico' => $validatedData['laudo_tecnico'] ?? null,
            'valor_servico' => $validatedData['valor_servico'] ?? 0.00,
            'desconto_servico' => $validatedData['desconto_servico'] ?? 0.00,
        ];

        // Re-validação de desconto (se necessário, agora com $dadosParaUpdate)
        if (isset($dadosParaUpdate['desconto_servico']) && isset($dadosParaUpdate['valor_servico']) && $dadosParaUpdate['desconto_servico'] > $dadosParaUpdate['valor_servico']) {
            return back()->withErrors(['desconto_servico' => 'O desconto não pode ser maior que o valor do serviço.'])->withInput();
        }

        // Ajuste na validação de 'data_conclusao' que agora usa a data de entrada do modelo
        if (isset($validatedData['data_conclusao']) && $validatedData['data_conclusao']) {
            $dataEntradaOriginal = $atendimento->data_entrada;
            if (Carbon::parse($validatedData['data_conclusao'])->lt($dataEntradaOriginal)) {
                return back()->withErrors(['data_conclusao' => 'A data de conclusão não pode ser anterior à data de entrada original (' . $dataEntradaOriginal->format('d/m/Y') . ').'])->withInput();
            }
        }


        $atendimento->update($dadosParaUpdate);

        // Lógica para enviar notificação de "Pronto para entrega" (já implementada)
        if ($atendimento->status == 'Pronto para entrega' && $statusAntigo != 'Pronto para entrega') {
            try {
                if ($atendimento->cliente && $atendimento->cliente->email) {
                    $atendimento->cliente->notify(new AtendimentoProntoNotification($atendimento));
                    // Log::info("Notificação 'Atendimento Pronto' enviada para cliente ID: {$atendimento->cliente->id} para atendimento ID: {$atendimento->id}");
                } else {
                    // Log::warning("Cliente ou email do cliente não encontrado para notificação do atendimento ID: {$atendimento->id}");
                }
            } catch (\Exception $e) {
                // Log::error("Erro ao enviar notificação 'Atendimento Pronto' para atendimento ID: {$atendimento->id} - Erro: " . $e->getMessage());
            }
        }

        return redirect()->route('atendimentos.show', $atendimento->id)->with('success', 'Atendimento atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Atendimento $atendimento)
    {
        // Proteção com Gate
        if (Gate::denies('is-admin-or-atendente')) { // Usando o novo Gate combinado
            return redirect()->route('atendimentos.index')->with('error', 'Acesso não autorizado.');
            // ou abort(403);
        }

        // Adicionar lógica para verificar se o atendimento tem saídas de estoque vinculadas
        // e decidir o que fazer (impedir exclusão, desvincular saídas, etc.)
        // Por enquanto, vamos apenas deletar.
        // CUIDADO: Se houver saídas de estoque vinculadas, e a FK não tiver onDelete('set null') ou onDelete('cascade'),
        // a exclusão do atendimento pode falhar devido à restrição de integridade do banco.
        // Sua migration de saidas_estoque tem: $table->foreignId('atendimento_id')->nullable()->constrained('atendimentos');
        // Isso significa que ao deletar um atendimento, o atendimento_id nas saidas_estoque associadas se tornará NULL. Isso é bom.

        $atendimento->delete();
        return redirect()->route('atendimentos.index')->with('success', 'Atendimento excluído com sucesso!');
    }

    public function autocomplete(Request $request)
    {
        $search = $request->get('search_autocomplete'); // Usando o parâmetro que definimos no JS
        $query = Atendimento::with('cliente');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', $search . '%') // Busca por ID começando com
                    ->orWhere('codigo_consulta', 'like', $search . '%')
                    ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                        $clienteQuery->where('nome_completo', 'like', '%' . $search . '%')
                            ->orWhere('cpf_cnpj', 'like', '%' . $search . '%');
                    })
                    ->orWhere('celular', 'like', '%' . $search . '%'); // Buscar no celular do atendimento
            });
        }

        $atendimentos = $query->limit(15)->get();

        // Formatar para o jQuery UI Autocomplete
        // O JavaScript na view de saidas_estoque/index já faz um mapeamento,
        // então podemos retornar a coleção diretamente ou um formato mais simples.
        // Para este exemplo, retornarei a coleção e o JS na view fará o map.
        return response()->json($atendimentos);
    }
    public function gerarPdf(Atendimento $atendimento)
    {
        // Carregar todos os dados necessários para o PDF
        $atendimento->load(['cliente', 'tecnico', 'saidasEstoque.estoque']);

        // Calcular valores (lógica similar à da view show)
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

        // Dados que serão passados para a view do PDF
        $dadosParaPdf = [
            'atendimento' => $atendimento,
            'valorTotalPecas' => $valorTotalPecas,
            'valorServicoLiquido' => $valorServicoLiquido,
            'valorTotalAtendimento' => $valorTotalAtendimento,
            'dataImpressao' => Carbon::now(),
            // Adicione o nome da sua empresa ou outros dados fixos aqui se desejar
            'nomeEmpresa' => 'JM Celulares',
            'enderecoEmpresa' => 'Rua Exemplo, 123 - Cidade - UF',
            'telefoneEmpresa' => '(XX) XXXXX-XXXX',
        ];

        // Carrega a view Blade que criaremos para o PDF e passa os dados
        $pdf = Pdf::loadView('atendimentos.pdf_template', $dadosParaPdf);

        // Define o nome do arquivo para download
        $nomeArquivo = 'OS_Atendimento_' . $atendimento->id . '_' . Str::slug($atendimento->cliente->nome_completo ?? 'cliente') . '.pdf';

        // Opção 1: Forçar o download do PDF
        return $pdf->stream($nomeArquivo);

        // Opção 2: Mostrar o PDF no navegador (inline)
        // return $pdf->stream($nomeArquivo);
    }
}
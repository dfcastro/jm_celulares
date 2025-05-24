<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\MovimentacaoCaixa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log; 
use Illuminate\Http\JsonResponse;

// Dentro de app/Http/Controllers/CaixaController.php

class CaixaController extends Controller
{
    public function index()
    {
        // 1. Verificar permissão para visualizar o histórico de caixas
        //    Vamos usar o mesmo Gate 'gerenciar-caixa' por enquanto.
        //    Você pode criar um Gate mais específico como 'visualizar-historico-caixa' se necessário.
        if (Gate::denies('gerenciar-caixa')) {
            return redirect()->route('dashboard')->with('error', 'Você não tem permissão para acessar o histórico de caixas.');
        }

        // 2. Buscar os caixas do banco de dados
        //    - Ordenar pelos mais recentes primeiro (data_abertura descendente).
        //    - Carregar os relacionamentos com os usuários de abertura e fechamento para exibir seus nomes.
        //    - Usar paginação para não carregar todos de uma vez.
        $caixas = Caixa::with(['usuarioAbertura', 'usuarioFechamento'])
            ->orderBy('data_abertura', 'desc')
            ->paginate(15); // Exibe 15 caixas por página

        // 3. Obter o caixa atualmente aberto (se houver) para exibir um botão de "Abrir Caixa" condicionalmente.
        $caixaAberto = $this->getCaixaAberto();

        // 4. Passar os dados para a view
        return view('caixa.index', compact('caixas', 'caixaAberto'));
    }

    /**
     * Verifica se existe algum caixa com status 'Aberto'.
     * Retorna o caixa aberto ou null.
     */
    private function getCaixaAberto(): ?Caixa
    {
        return Caixa::where('status', 'Aberto')->first();
    }
    // Dentro de app/Http/Controllers/CaixaController.php

    public function create()
    {
        // 1. Verificar permissão
        if (Gate::denies('gerenciar-caixa')) {
            return redirect()->route('dashboard')->with('error', 'Você não tem permissão para abrir o caixa.');
        }

        // 2. Verificar se já existe um caixa aberto
        $caixaAberto = $this->getCaixaAberto();
        if ($caixaAberto) {
            // Se já existe um caixa aberto, redireciona para a visualização dele
            return redirect()->route('caixa.show', $caixaAberto->id)
                ->with('warning', 'Já existe um caixa aberto. Feche o caixa atual antes de abrir um novo.');
        }

        // 3. Se não houver caixa aberto e o usuário tiver permissão, mostra o formulário
        return view('caixa.create');
    }
    // Dentro de app/Http/Controllers/CaixaController.php

    public function store(Request $request)
    {
        // 1. Verificar permissão
        if (Gate::denies('gerenciar-caixa')) {
            // Teoricamente, o create já bloquearia, mas é bom ter a verificação aqui também.
            return redirect()->route('dashboard')->with('error', 'Você não tem permissão para abrir o caixa.');
        }

        // 2. Verificar se já existe um caixa aberto (prevenção extra caso o usuário burle o fluxo normal)
        if ($this->getCaixaAberto()) {
            return redirect()->route('caixa.show', $this->getCaixaAberto()->id)
                ->with('warning', 'Operação não permitida. Já existe um caixa aberto.');
        }

        // 3. Validar os dados do formulário
        $validatedData = $request->validate([
            'saldo_inicial' => 'required|numeric|min:0',
            'observacoes_abertura' => 'nullable|string|max:1000',
        ]);

        // 4. Criar o registro do novo caixa
        $caixa = Caixa::create([
            'usuario_abertura_id' => Auth::id(),
            'data_abertura' => Carbon::now(),
            'saldo_inicial' => $validatedData['saldo_inicial'],
            'status' => 'Aberto',
            'observacoes_abertura' => $validatedData['observacoes_abertura'],
        ]);

        // 5. Registrar o saldo inicial como a primeira movimentação do caixa
        if ($caixa) {
            MovimentacaoCaixa::create([
                'caixa_id' => $caixa->id,
                'usuario_id' => Auth::id(),
                'tipo' => 'ENTRADA',
                'descricao' => 'Suprimento Inicial (Abertura de Caixa)',
                'valor' => $caixa->saldo_inicial,
                'forma_pagamento' => 'Dinheiro', // Ou a forma como o suprimento é feito
                'data_movimentacao' => $caixa->data_abertura,
                'observacoes' => 'Valor inicial ao abrir o caixa.',
            ]);

            return redirect()->route('caixa.show', $caixa->id)
                ->with('success', 'Caixa aberto com sucesso com saldo inicial de R$ ' . number_format($caixa->saldo_inicial, 2, ',', '.'));
        }

        return redirect()->back()->with('error', 'Não foi possível abrir o caixa. Tente novamente.')->withInput();
    }
    // Dentro de app/Http/Controllers/CaixaController.php

    public function show(Caixa $caixa) // Route-Model Binding
    {
        // 1. Verificar permissão para visualizar (pode ser a mesma de gerenciar ou uma mais genérica)
        // Por enquanto, vamos assumir que se pode ver, mas as ações dentro da view serão controladas por Gate
        if (Gate::denies('gerenciar-caixa')) { // Ou crie um Gate 'visualizar-caixa' se necessário
            return redirect()->route('dashboard')->with('error', 'Você não tem permissão para visualizar este caixa.');
        }

        // 2. Carregar relacionamentos necessários (movimentações, usuários)
        $caixa->load([
            'usuarioAbertura',
            'usuarioFechamento',
            'movimentacoes' => function ($query) {
                $query->orderBy('data_movimentacao', 'asc')->orderBy('id', 'asc'); // Ordena as movimentações
            }
        ]);

        // 3. Passar o caixa para a view
        return view('caixa.show', compact('caixa'));
    }
    /**
     * Mostra o formulário para criar uma nova movimentação manual (entrada/saída) em um caixa aberto.
     *
     * @param Caixa $caixa O caixa ao qual a movimentação será adicionada.
     * @param string $tipo O tipo de movimentação ('entrada' ou 'saida').
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function createMovimentacao(Caixa $caixa, string $tipo)
    {
        // 1. Verificar permissão
        if (Gate::denies('gerenciar-caixa')) {
            return redirect()->route('caixa.show', $caixa->id)->with('error', 'Você não tem permissão para registrar movimentações neste caixa.');
        }

        // 2. Verificar se o caixa está realmente aberto
        if (!$caixa->estaAberto()) {
            return redirect()->route('caixa.show', $caixa->id)->with('error', 'Este caixa não está aberto. Não é possível adicionar movimentações.');
        }

        // 3. Validar o tipo de movimentação
        if (!in_array($tipo, ['entrada', 'saida'])) {
            return redirect()->route('caixa.show', $caixa->id)->with('error', 'Tipo de movimentação inválido.');
        }

        // 4. Passar o caixa e o tipo para a view do formulário
        return view('caixa.movimentacao.create', compact('caixa', 'tipo'));
    }

    /**
     * Armazena uma nova movimentação manual no caixa.
     *
     * @param Request $request
     * @param Caixa $caixa O caixa ao qual a movimentação será adicionada.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeMovimentacao(Request $request, Caixa $caixa)
    {
        // 1. Verificar permissão
        if (Gate::denies('gerenciar-caixa')) {
            return redirect()->route('caixa.show', $caixa->id)->with('error', 'Você não tem permissão para registrar movimentações neste caixa.');
        }

        // 2. Verificar se o caixa está realmente aberto
        if (!$caixa->estaAberto()) {
            return redirect()->route('caixa.show', $caixa->id)->with('error', 'Este caixa não está aberto. Não é possível adicionar movimentações.');
        }

        // 3. Validar os dados do formulário
        $validatedData = $request->validate([
            'tipo_movimentacao' => 'required|in:ENTRADA,SAIDA', // Vem de um campo hidden no form
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01', // Valor deve ser positivo
            'forma_pagamento' => 'nullable|string|max:50', // Opcional para sangrias/despesas, pode ser sempre 'Dinheiro'
            'observacoes' => 'nullable|string|max:1000',
        ]);

        // 4. Criar e salvar a movimentação
        try {
            MovimentacaoCaixa::create([
                'caixa_id' => $caixa->id,
                'usuario_id' => Auth::id(),
                'tipo' => $validatedData['tipo_movimentacao'],
                'descricao' => $validatedData['descricao'],
                'valor' => $validatedData['valor'],
                'forma_pagamento' => $validatedData['forma_pagamento'] ?? 'Dinheiro', // Default para Dinheiro se não informado
                'data_movimentacao' => Carbon::now(),
                'observacoes' => $validatedData['observacoes'],
                // 'referencia_id' e 'referencia_tipo' serão null para movimentações manuais
            ]);

            return redirect()->route('caixa.show', $caixa->id)
                ->with('success', 'Movimentação registrada com sucesso!');

        } catch (\Exception $e) {
            // Log::error("Erro ao registrar movimentação no caixa {$caixa->id}: " . $e->getMessage()); // Opcional: Logar o erro
            return redirect()->back()
                ->with('error', 'Erro ao registrar a movimentação. Tente novamente.')
                ->withInput();
        }
    }
    /**
     * Processa o fechamento de um caixa.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Caixa  $caixa  // Route Model Binding
     * @return \Illuminate\Http\RedirectResponse
     */
    public function fechar(Request $request, Caixa $caixa)
    {
        // 1. Verificar permissão
        if (Gate::denies('gerenciar-caixa')) {
            return redirect()->route('caixa.show', $caixa->id)->with('error', 'Você não tem permissão para fechar este caixa.');
        }

        // 2. Verificar se o caixa está realmente aberto (o objeto $caixa já está carregado pelo Route Model Binding)
        if (!$caixa->estaAberto()) {
            return redirect()->route('caixa.show', $caixa->id)->with('error', 'Este caixa não está aberto ou já foi fechado. Não pode ser fechado novamente.');
        }

        // 3. Validar os dados do formulário
        $validatedData = $request->validate([
            'saldo_final_informado' => 'required|numeric|min:0',
            'observacoes_fechamento' => 'nullable|string|max:1000',
        ], [
            'saldo_final_informado.required' => 'O saldo final contado é obrigatório.',
            'saldo_final_informado.numeric' => 'O saldo final contado deve ser um valor numérico.',
            'saldo_final_informado.min' => 'O saldo final contado não pode ser negativo.',
        ]);

        // Iniciar uma transação de banco de dados para garantir a atomicidade
        DB::beginTransaction();
        try {
            // 4. Recalcular o saldo final do sistema NO MOMENTO DO FECHAMENTO
            // Bloquear o caixa para escrita pode ser uma boa ideia aqui se o seu SGBD suportar LOCK FOR UPDATE
            // $caixaLocked = Caixa::lockForUpdate()->find($caixa->id); // Opcional, para alta concorrência

            $totalEntradas = $caixa->movimentacoes()->where('tipo', 'ENTRADA')->sum('valor');
            $totalSaidas = $caixa->movimentacoes()->where('tipo', 'SAIDA')->sum('valor');
            // O saldo_inicial já é a primeira movimentação de ENTRADA,
            // então o cálculo direto de entradas - saídas reflete o saldo final.
            $saldoFinalCalculadoSistema = $totalEntradas - $totalSaidas;

            // 5. Calcular a diferença
            $saldoFinalInformadoUsuario = (float) $validatedData['saldo_final_informado'];
            // A diferença é o que o usuário contou MENOS o que o sistema calculou.
            // Se positivo: sobrou dinheiro. Se negativo: faltou dinheiro.
            $diferenca = $saldoFinalInformadoUsuario - $saldoFinalCalculadoSistema;

            // 6. Atualizar o registro do caixa
            $caixa->usuario_fechamento_id = Auth::id();
            $caixa->data_fechamento = Carbon::now();
            $caixa->saldo_final_calculado = $saldoFinalCalculadoSistema;
            $caixa->saldo_final_informado = $saldoFinalInformadoUsuario;
            $caixa->diferenca = $diferenca;
            $caixa->status = 'Fechado';
            $caixa->observacoes_fechamento = $validatedData['observacoes_fechamento'];

            $caixa->save();

            DB::commit(); // Confirma as alterações no banco

            $mensagemFeedback = 'Caixa #' . $caixa->id . ' fechado com sucesso!';
            $tipoFeedback = 'success';

            if (bccomp((string) $diferenca, '0.00', 2) != 0) { // Compara números decimais com precisão
                $tipoDiferenca = $diferenca > 0 ? "SOBRA" : "FALTA";
                $mensagemFeedback .= " Atenção: Diferença de R$ " . number_format(abs($diferenca), 2, ',', '.') . " ({$tipoDiferenca}).";
                $tipoFeedback = 'warning'; // Muda para warning para destacar a diferença
            }

            return redirect()->route('caixa.show', $caixa->id)->with($tipoFeedback, $mensagemFeedback);

        } catch (ValidationException $e) {
            DB::rollBack(); // Reverte a transação em caso de erro de validação (embora já validado acima)
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack(); // Reverte a transação em caso de qualquer outro erro
            Log::error("Erro crítico ao fechar o caixa #{$caixa->id}: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('caixa.show', $caixa->id)->with('error', 'Ocorreu um erro crítico ao tentar fechar o caixa. Por favor, tente novamente ou contate o suporte. Detalhes: ' . $e->getMessage());
        }
    }
    /**
     * Mostra o formulário para fechar um caixa específico.
     *
     * @param  \App\Models\Caixa  $caixa // Route Model Binding
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function editFechar(Caixa $caixa)
    {
        // 1. Verificar permissão
        if (Gate::denies('gerenciar-caixa')) {
            return redirect()->route('caixa.show', $caixa->id)->with('error', 'Você não tem permissão para fechar este caixa.');
        }

        // 2. Verificar se o caixa está realmente aberto
        // O objeto $caixa já é carregado pelo Route Model Binding.
        // O método estaAberto() deve existir no seu Model Caixa.
        if (!$caixa->estaAberto()) {
            return redirect()->route('caixa.show', $caixa->id)->with('error', 'Este caixa não está aberto ou já foi fechado.');
        }

        // 3. Calcular o saldo final com base nas movimentações
        $totalEntradas = $caixa->movimentacoes()->where('tipo', 'ENTRADA')->sum('valor');
        $totalSaidas = $caixa->movimentacoes()->where('tipo', 'SAIDA')->sum('valor');

        // O saldo_final_calculado é o saldo_inicial + (todas as outras entradas) - (todas as saídas)
        // Como o saldo_inicial já está incluído no $totalEntradas (como a primeira movimentação),
        // a fórmula simplifica para:
        $saldoFinalCalculado = $totalEntradas - $totalSaidas;

        // 4. Passar o caixa e o saldo calculado para a view
        // Esta view nós criamos na mensagem anterior.
        return view('caixa.editFechar', compact('caixa', 'saldoFinalCalculado'));
    }
    /**
     * Verifica o status do caixa atual via AJAX.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarStatusAjax(): JsonResponse
    {
        $caixaAberto = Caixa::where('status', 'Aberto')->first();
        return response()->json([
            'caixa_aberto' => (bool) $caixaAberto,
            'caixa_id' => $caixaAberto ? $caixaAberto->id : null,
        ]);
    }
    // ...
}
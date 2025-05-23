<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Models\SaidaEstoque; // Exemplo de model para binding
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CaixaController;
use App\Http\Controllers\MovimentacaoCaixaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AtendimentoController;
use App\Http\Controllers\ConsultaStatusController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\EntradaEstoqueController;
use App\Http\Controllers\SaidaEstoqueController;
use App\Http\Controllers\VendaAcessorioController;
use App\Http\Controllers\RelatorioController;
use Illuminate\Http\Request; // Se usado em closures de rota
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrcamentoController;

Route::get('/', function () {
    return view('welcome');
});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::model('saida_estoque', SaidaEstoque::class);
// Adicione outros bindings explícitos se o Laravel não estiver resolvendo-os automaticamente
// Route::model('atendimento', \App\Models\Atendimento::class); // Exemplo

// ------------------------- ROTAS PÚBLICAS -------------------------
Route::get('/', function () {
    return view('site.index'); // Sua página inicial/institucional
})->name('site.home');

Route::get('/consultar-status', [ConsultaStatusController::class, 'index'])->name('consulta.index');
Route::post('/consultar-status', [ConsultaStatusController::class, 'consultar'])->name('consulta.status');

// Autocompletes - Avalie se precisam ser públicos ou se podem ir para o grupo 'auth'
// Se forem chamados por JavaScript em páginas públicas, precisam ficar aqui.
// Se forem apenas para a área interna, mova para o grupo 'auth'.
Route::get('/busca-clientes', [ClienteController::class, 'autocomplete'])->name('clientes.autocomplete');
Route::get('/atendimentos-autocomplete', [AtendimentoController::class, 'autocomplete'])->name('atendimentos.autocomplete');
Route::get('/estoque-autocomplete', [EstoqueController::class, 'autocomplete'])->name('estoque.autocomplete');


// ------------------------- ROTAS DE AUTENTICAÇÃO (Geradas pelo Breeze) -------------------------
// Esta linha é adicionada pelo Breeze e carrega as rotas de login, registro, logout, etc.
// Geralmente, o Breeze a coloca no final do arquivo ou no bootstrap/app.php.
// Se não estiver aqui e você instalou o Breeze, verifique o final do arquivo ou o bootstrap/app.php
// require __DIR__.'/auth.php'; // Movido para o final para melhor leitura após todas as suas rotas.

// ------------------------- ÁREA RESTRITA / SISTEMA INTERNO -------------------------
Route::middleware(['auth'])->group(function () {
    //  Route::get('/dashboard', function () {
    //      return view('dashboard');
    //  })->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard'); // <<<< NOVA ROTA

    // ROTAS EXCLUSIVAS PARA ADMINISTRADORES
    Route::middleware(['admin'])->group(function () {
        Route::resource('usuarios', UsuarioController::class); // GERENCIAMENTO DE USUÁRIOS
        // Ex: Route::get('/configuracoes-sistema', [ConfigController::class, 'index'])->name('config.index');
    });

    Route::resource('orcamentos', OrcamentoController::class);

    // Rotas para ações de status do orçamento
    Route::post('/orcamentos/{orcamento}/marcar-aguardando', [OrcamentoController::class, 'marcarComoAguardando'])
        ->name('orcamentos.marcarAguardando'); // Status: Em Elaboração -> Aguardando Aprovação
    Route::post('/orcamentos/{orcamento}/aprovar', [OrcamentoController::class, 'aprovarOrcamento'])
        ->name('orcamentos.aprovar'); // Status: Aguardando Aprovação -> Aprovado
    Route::post('/orcamentos/{orcamento}/reprovar', [OrcamentoController::class, 'reprovarOrcamento'])
        ->name('orcamentos.reprovar'); // Status: Aguardando Aprovação -> Reprovado
    Route::get('/orcamentos/{orcamento}/pdf', [OrcamentoController::class, 'gerarPdf'])
        ->name('orcamentos.pdf');    // Rota para gerar PDF do orçamento
    Route::post('/orcamentos/{orcamento}/converter-os', [OrcamentoController::class, 'converterEmOs'])
        ->name('orcamentos.converterEmOs'); //rota para converocamento em OS
    Route::post('/orcamentos/{orcamento}/enviar-email', [OrcamentoController::class, 'enviarEmail'])
        ->name('orcamentos.enviarEmail'); // Rota para Enviar E-mail do Orçamento



    // Clientes
    Route::resource('clientes', ClienteController::class);
    // A rota de autocomplete de clientes está pública acima, mas se for só para uso interno, poderia estar aqui.

    // Atendimentos
    Route::get('/atendimentos/{atendimento}/gerar-pdf', [AtendimentoController::class, 'gerarPdf'])->name('atendimentos.pdf');
    Route::resource('atendimentos', AtendimentoController::class);
    Route::patch('/atendimentos/{atendimento}/atualizar-status', [AtendimentoController::class, 'atualizarStatus'])
        ->name('atendimentos.atualizarStatus');
    Route::patch('/atendimentos/{atendimento}/atualizar-campo/{campo}', [App\Http\Controllers\AtendimentoController::class, 'atualizarCampoAjax'])
        ->name('atendimentos.atualizarCampoAjax');
    // Dentro do grupo autenticado
    Route::patch('/atendimentos/{atendimento}/atualizar-valores-servico', [App\Http\Controllers\AtendimentoController::class, 'atualizarValoresServicoAjax'])
        ->name('atendimentos.atualizarValoresServicoAjax');
    // A rota de autocomplete de atendimentos está pública acima.
    Route::post('/atendimentos/{atendimento}/registrar-pagamento-ajax', [AtendimentoController::class, 'registrarPagamentoAjax'])->name('atendimentos.registrarPagamentoAjax');
    Route::post('/atendimentos/{atendimento}/atualizar-servicos-detalhados', [AtendimentoController::class, 'atualizarServicosDetalhadosAjax'])
    ->name('atendimentos.atualizarServicosDetalhadosAjax');
    
    // Rotas para Controle de Caixa
    Route::prefix('caixa')->name('caixa.')->group(function () {
        Route::get('/', [CaixaController::class, 'index'])->name('index'); // << Deve chamar CaixaController@index
        Route::get('/abrir', [CaixaController::class, 'create'])->name('create'); // Formulário para abrir caixa
        Route::post('/abrir', [CaixaController::class, 'store'])->name('store'); // Processar abertura do caixa
        Route::get('/{caixa}/visualizar', [CaixaController::class, 'show'])->name('show'); // Ver caixa aberto ou detalhes de um fechado
        // Futuras rotas para fechamento, movimentações manuais, etc.
        Route::get('/{caixa}/fechar', [CaixaController::class, 'editFechar'])->name('editFechar');
        Route::post('/{caixa}/fechar', [CaixaController::class, 'fechar'])->name('fechar');
        Route::get('/{caixa}/movimentacao/nova/{tipo}', [MovimentacaoCaixaController::class, 'create'])->name('movimentacao.create');
        Route::post('/{caixa}/movimentacao', [MovimentacaoCaixaController::class, 'store'])->name('movimentacao.store');
        // NOVAS ROTAS PARA MOVIMENTAÇÕES MANUAIS
        Route::get('/{caixa}/movimentacao/nova/{tipo}', [CaixaController::class, 'createMovimentacao'])->name('movimentacao.create'); // 'tipo' pode ser 'entrada' ou 'saida'
        Route::post('/{caixa}/movimentacao', [CaixaController::class, 'storeMovimentacao'])->name('movimentacao.store');
        Route::get('/verificar-status-ajax', [CaixaController::class, 'verificarStatusAjax'])->name('verificarStatusAjax');
    });

    // Estoque
    Route::get('/estoque/historico-unificado', [EstoqueController::class, 'historicoUnificado'])->name('estoque.historico_unificado');
    Route::get('/estoque/{estoque}/historico', [EstoqueController::class, 'historicoPeca'])->name('estoque.historico_peca');
    Route::resource('estoque', EstoqueController::class);
    // A rota de autocomplete de estoque está pública acima.

    // Entradas de Estoque
    Route::resource('entradas-estoque', EntradaEstoqueController::class)->except(['edit', 'update']);

    // Saídas de Estoque
    Route::resource('saidas-estoque', SaidaEstoqueController::class)->except(['edit', 'update']);

    // Vendas de Acessórios
    Route::get('/vendas-acessorios/item-template', function (Request $request) {
        $index = $request->query('index');
        $itemData = $request->query('itemData') ? json_decode($request->query('itemData'), true) : [];
        if (!is_array($itemData)) {
            $itemData = [];
        }
        return view('vendas_acessorios._item_venda_template', compact('index', 'itemData'))->render();
    })->name('vendas-acessorios.item_template');
    Route::get('/vendas-acessorios/{vendas_acessorio}/devolver', [VendaAcessorioController::class, 'showDevolucaoForm'])->name('vendas-acessorios.devolver.form');
    Route::post('/vendas-acessorios/{vendas_acessorio}/devolver', [VendaAcessorioController::class, 'processarDevolucao'])->name('vendas-acessorios.devolver.processar');
    Route::resource('vendas-acessorios', VendaAcessorioController::class);

    // Relatórios
    Route::prefix('relatorios')->name('relatorios.')->group(function () {
        Route::get('/estoque-baixo', [RelatorioController::class, 'estoqueAbaixoMinimo'])->name('estoque_baixo');
        Route::get('/vendas-acessorios', [RelatorioController::class, 'vendasAcessoriosPeriodo'])->name('vendas_acessorios');
        Route::get('/itens-mais-vendidos', [RelatorioController::class, 'itensMaisVendidos'])->name('itens_mais_vendidos');
        Route::get('/pecas-mais-utilizadas', [RelatorioController::class, 'pecasMaisUtilizadas'])->name('pecas_mais_utilizadas');
        Route::get('/atendimentos-status', [RelatorioController::class, 'atendimentosPorStatus'])->name('atendimentos_status');
        Route::get('/atendimentos-tecnico', [RelatorioController::class, 'atendimentosPorTecnico'])->name('atendimentos_tecnico');
    });

    // Futuras rotas de gerenciamento de usuários
    // Route::resource('usuarios', UsuarioController::class);
});


// Rotas de autenticação do Breeze (geralmente o Breeze adiciona esta linha)
// É importante que esta linha exista para que as rotas de login, registro, etc., funcionem.
// Se você instalou o Breeze, ele deve ter criado um arquivo routes/auth.php.
if (file_exists(__DIR__ . '/auth.php')) {
    require __DIR__ . '/auth.php';
}

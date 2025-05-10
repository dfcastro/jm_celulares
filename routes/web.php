<?php

use App\Http\Controllers\AtendimentoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\EntradaEstoqueController;
use App\Http\Controllers\SaidaEstoqueController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConsultaStatusController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Enjoy building your API!
|
*/

Route::get('/', function () {
    return view('welcome'); // Página inicial (pode ser alterada depois)
});

// Rotas para Clientes
Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index'); // Listar clientes
Route::get('/clientes/novo', [ClienteController::class, 'create'])->name('clientes.create'); // Exibir formulário de cadastro
Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store'); // Salvar novo cliente
Route::get('/clientes/{cliente}/editar', [ClienteController::class, 'edit'])->name('clientes.edit'); // Exibir formulário de edição
Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update'); // Salvar cliente editado
Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy'); // Excluir cliente
Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');
Route::get('/busca-clientes', [ClienteController::class, 'autocomplete'])->name('clientes.autocomplete');

// Rotas para Atendimentos
Route::resource('atendimentos', AtendimentoController::class);
Route::get('/consultar-status', [ConsultaStatusController::class, 'index'])->name('consulta.index');
Route::post('/consultar-status', [ConsultaStatusController::class, 'consultar'])->name('consulta.status');

// Rotas para Estoque
Route::get('/estoque', [EstoqueController::class, 'index'])->name('estoque.index'); // Listar estoque
Route::get('/estoque/novo', [EstoqueController::class, 'create'])->name('estoque.create'); // Exibir formulário de nova peça
Route::post('/estoque', [EstoqueController::class, 'store'])->name('estoque.store'); // Salvar nova peça

// MUDE A ORDEM AQUI: A ROTA ESPECÍFICA DEVE VIR ANTES DO WILDCARD {estoque}
Route::get('/estoque/historico-unificado', [EstoqueController::class, 'historicoUnificado'])->name('estoque.historico_unificado'); // Rota para o histórico de movimentações unificado

Route::get('/estoque/{estoque}/editar', [EstoqueController::class, 'edit'])->name('estoque.edit'); // Exibir formulário de edição
Route::put('/estoque/{estoque}', [EstoqueController::class, 'update'])->name('estoque.update'); // Salvar peça editada
Route::delete('/estoque/{estoque}', [EstoqueController::class, 'destroy'])->name('estoque.destroy'); // Excluir peça
Route::get('/estoque/{estoque}', [EstoqueController::class, 'show'])->name('estoque.show'); // Mantenha esta depois da anterior
Route::get('/estoque/{estoque}/historico', [EstoqueController::class, 'historicoPeca'])->name('estoque.historico_peca');


// Rotas para Entradas de Estoque
Route::get('/entradas-estoque', [EntradaEstoqueController::class, 'index'])->name('entradas-estoque.index');
Route::get('/entradas-estoque/create', [EntradaEstoqueController::class, 'create'])->name('entradas-estoque.create');
Route::post('/entradas-estoque', [EntradaEstoqueController::class, 'store'])->name('entradas-estoque.store');
Route::get('/entradas-estoque/{entradas_estoque}', [EntradaEstoqueController::class, 'show'])->name('entradas-estoque.show');
//Route::get('/entradas-estoque/{entradas_estoque}/edit', [EntradaEstoqueController::class, 'edit'])->name('entradas-estoque.edit');
//Route::put('/entradas-estoque/{entradas_estoque}', [EntradaEstoqueController::class, 'update'])->name('entradas-estoque.update')->where('entradas_estoque', '[0-9]+');
Route::delete('/entradas-estoque/{entradas_estoque}', [App\Http\Controllers\EntradaEstoqueController::class, 'destroy'])->name('entradas-estoque.destroy');


// Rotas para Saídas de Estoque
Route::resource('saidas-estoque', SaidaEstoqueController::class)->except([
    'edit', 'update'
]);

// Rotas de Recurso para Vendas de Acessórios
Route::get('/vendas-acessorios/item-template', function (Request $request) {
    $index = $request->query('index'); // Pega o índice do request
    $itemData = $request->query('itemData'); // Pega dados para pré-popular (se vier)
    $itensEstoque = App\Models\Estoque::orderBy('nome')->get(); // Busca itens de estoque

    // Retorna a view parcial renderizada
    return view('vendas_acessorios._item_venda_template', compact('index', 'itemData', 'itensEstoque'))->render();
})->name('vendas-acessorios.item_template');


// ... rotas de recurso para vendas-acessorios ...
Route::resource('vendas-acessorios', App\Http\Controllers\VendaAcessorioController::class);
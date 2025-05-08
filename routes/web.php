<?php

use App\Http\Controllers\AtendimentoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EstoqueController;
use Illuminate\Support\Facades\Route;

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

// Rotas para Atendimentos
Route::get('/atendimentos', [AtendimentoController::class, 'index'])->name('atendimentos.index'); // Listar atendimentos
Route::get('/atendimentos/novo', [AtendimentoController::class, 'create'])->name('atendimentos.create'); // Exibir formulário de novo atendimento
Route::post('/atendimentos', [AtendimentoController::class, 'store'])->name('atendimentos.store'); // Salvar novo atendimento
Route::get('/atendimentos/{atendimento}/editar', [AtendimentoController::class, 'edit'])->name('atendimentos.edit'); // Exibir formulário de edição
Route::put('/atendimentos/{atendimento}', [AtendimentoController::class, 'update'])->name('atendimentos.update'); // Salvar atendimento editado
Route::delete('/atendimentos/{atendimento}', [AtendimentoController::class, 'destroy'])->name('atendimentos.destroy'); // Excluir atendimento

// Rotas para Estoque
Route::get('/estoque', [EstoqueController::class, 'index'])->name('estoque.index'); // Listar estoque
Route::get('/estoque/novo', [EstoqueController::class, 'create'])->name('estoque.create'); // Exibir formulário de nova peça
Route::post('/estoque', [EstoqueController::class, 'store'])->name('estoque.store'); // Salvar nova peça
Route::get('/estoque/{estoque}/editar', [EstoqueController::class, 'edit'])->name('estoque.edit'); // Exibir formulário de edição
Route::put('/estoque/{estoque}', [EstoqueController::class, 'update'])->name('estoque.update'); // Salvar peça editada
Route::delete('/estoque/{estoque}', [EstoqueController::class, 'destroy'])->name('estoque.destroy'); // Excluir peça
Route::get('/estoque/{estoque}', [EstoqueController::class, 'show'])->name('estoque.show');
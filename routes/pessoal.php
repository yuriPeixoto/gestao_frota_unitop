<?php

use App\Http\Controllers\Admin\ContratoFornecedorController;
use App\Http\Controllers\Admin\ContratoModeloController;
use App\Http\Controllers\Admin\FornecedorController;
use App\Http\Controllers\Admin\PessoalController;
use Illuminate\Support\Facades\Route;

// Rotas de Pessoas
Route::group(['prefix' => 'pessoas'], function () {
    Route::get('/', [PessoalController::class, 'index'])
        ->name('pessoas.index');
    Route::get('criar', [PessoalController::class, 'create'])
        ->name('pessoas.create');
    Route::get('{pessoas}', [PessoalController::class, 'show'])
        ->name('pessoas.show');

    Route::post('/', [PessoalController::class, 'store'])
        ->name('pessoas.store');
    Route::get('{pessoas}/editar', [PessoalController::class, 'edit'])
        ->name('pessoas.edit');
    Route::put('{pessoas}', [PessoalController::class, 'update'])
        ->name('pessoas.update');

    Route::delete('{pessoas}', [PessoalController::class, 'destroy'])
        ->name('pessoas.destroy');
});

// Contratos de Fornecedores
Route::resource('contratos', ContratoFornecedorController::class);
Route::delete('contratos/{id}', [ContratoFornecedorController::class, 'destroyContrato'])
    ->name('contratos.destroy');
Route::get('contratos/{id}/download-documento', [ContratoFornecedorController::class, 'downloadDocumento'])
    ->name('contratos.download-documento');
Route::post('contratos/{id}/clonar', [ContratoFornecedorController::class, 'clonar'])
    ->name('contratos.clonar');
Route::post('contratos/{id}/atualizar-saldo', [ContratoFornecedorController::class, 'atualizarSaldo'])
    ->name('contratos.atualizar-saldo');
Route::get('contratos/por-fornecedor/{fornecedorId}', [ContratoFornecedorController::class, 'listarPorFornecedor'])
    ->name('contratos.por-fornecedor');


// Vínculos Contrato-Modelo
Route::resource('contratosmodelo', ContratoModeloController::class);
Route::post('contratosmodelo/{id}/clonar', [ContratoModeloController::class, 'clonar'])
    ->name('contratosmodelo.clonar');
Route::get('contratosmodelo/por-contrato/{contratoId}', [ContratoModeloController::class, 'listarPorContrato'])
    ->name('contratosmodelo.por-contrato');
Route::get('contratosmodelo/por-fornecedor/{fornecedorId}', [ContratoModeloController::class, 'listarPorFornecedor'])
    ->name('contratosmodelo.por-fornecedor');
Route::get('contratosmodelo/modelos-disponiveis/{contratoId}', [ContratoModeloController::class, 'buscarModelosDisponiveis'])
    ->name('contratosmodelo.modelos-disponiveis');

// Rotas API para AJAX/Fetch
Route::prefix('api')->name('api.')->group(function () {
    Route::get('contratos/{id}', [ContratoFornecedorController::class, 'show'])
        ->name('contratos.show');
    Route::get('contratos/fornecedor/{fornecedorId}', [ContratoFornecedorController::class, 'listarPorFornecedor'])
        ->name('contratos.por-fornecedor');
    Route::get('contratosmodelo/contrato/{contratoId}', [ContratoModeloController::class, 'listarPorContrato'])
        ->name('contratosmodelo.por-contrato');
    Route::get('contratosmodelo/fornecedor/{fornecedorId}', [ContratoModeloController::class, 'listarPorFornecedor'])
        ->name('contratosmodelo.por-fornecedor');
});

Route::group(['prefix' => 'fornecedores'], function () {
    Route::get('contratos/{id}', [FornecedorController::class, 'getContrato'])->name('fornecedor.getContrato');
    Route::get('contratos/clonar/{id}', [FornecedorController::class, 'cloneContrato'])->name('fornecedores.clone');
    Route::delete('contratos/destroy/{id}', [FornecedorController::class, 'destroyContrato'])->name('fornecedores.destroy');
    Route::get('servicos/grupo/{id}', [FornecedorController::class, 'getByGrupo'])->name('fornecedores.byGrupo');
    Route::get('pecas/grupo/{id}', [FornecedorController::class, 'getByPecas'])->name('fornecedor.ByPecas');
    Route::delete('servicos/destroy/{id}', [FornecedorController::class, 'destroyServicoFornecedor'])->name('fornecedor.destroyServico');
    Route::delete('pecas/destroy/{id}', [FornecedorController::class, 'destroyPecaFornecedor'])->name('fornecedor.destroyPecas');

    Route::delete('contrato/modelo/{id}', [FornecedorController::class, 'destroyContratoModelo'])->name('fornecedor.destroyModelo');

    Route::delete('endereco/{id}', [FornecedorController::class, 'destroyEndereco'])->name('fornecedor.destroyEndereco');
});

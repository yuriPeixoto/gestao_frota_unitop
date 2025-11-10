<?php

use App\Http\Controllers\Admin\ControleManutancaoFortaController;
use App\Http\Controllers\Admin\DepartamentoController;
use App\Http\Controllers\Admin\FornecedorController;
use App\Http\Controllers\Admin\ModeloVeiculoController;
use App\Http\Controllers\Admin\VeiculoController;
use Illuminate\Support\Facades\Route;

// Controle  de manutanção de frotas
Route::group(['prefix' => 'controlemanutancaofrota'], function () {
    Route::get('/', [ControleManutancaoFortaController::class, 'index'])
        ->name('controlemanutancaofrota.index');

    // Primeiro declare todas as rotas específicas (sem parâmetros dinâmicos)
    // Exportação
    Route::get('/export-csv', [ControleManutancaoFortaController::class, 'exportCsv'])
        ->name('controlemanutancaofrota.exportCsv');
    Route::get('/export-xls', [ControleManutancaoFortaController::class, 'exportXls'])
        ->name('controlemanutancaofrota.exportXls');
    Route::get('/export-pdf', [ControleManutancaoFortaController::class, 'exportPdf'])
        ->name('controlemanutancaofrota.exportPdf');
    Route::get('/export-xml', [ControleManutancaoFortaController::class, 'exportXml'])
        ->name('controlemanutancaofrota.exportXml');
});

Route::prefix('api')->group(function () {
    // Fornecedor
    Route::get('/fornecedor/search', [FornecedorController::class, 'search'])->name('api.fornecedor.search');
    Route::get('/fornecedor/single/{id}', [FornecedorController::class, 'getById'])->name('api.fornecedor.single');

    Route::get('/modeloveiculo/search', [ModeloVeiculoController::class, 'search'])->name('api.modeloveiculo.search');
    Route::get('/modeloveiculo/single/{id}', [ModeloVeiculoController::class, 'getById'])->name('api.modeloveiculo.single');

    // Route::get('/users/search', [UserController::class, 'search'])->name('api.users.search');
    // Route::get('/users/single/{id}', [UserController::class, 'getById'])->name('api.users.single');

    // Departamento
    Route::get('/departamento/search', [DepartamentoController::class, 'search'])->name('api.departamento.search');
    Route::get('/departamento/single/{id}', [DepartamentoController::class, 'getById'])->name('api.departamento.single');
    // Produtos Imobilizados
    // Route::get('/produtosimobilizados/search', [ProdutosImobilizadosController::class, 'search'])->name('api.produtosimobilizados.search');
    // Route::get('/produtosimobilizados/single/{id}', [ProdutosImobilizadosController::class, 'getById'])->name('api.produtosimobilizados.single');
    // // Pessoal
    // Route::get('/pessoal/search', [PessoalController::class, 'search'])->name('api.pessoal.search');
    // Route::get('/pessoal/single/{id}', [PessoalController::class, 'getById'])->name('api.pessoal.single');

    Route::get('/veiculos/search', [VeiculoController::class, 'search'])->name('api.veiculos.search');
    Route::get('/veiculos/single/{id}', [VeiculoController::class, 'getById'])->name('api.veiculos.single');
});

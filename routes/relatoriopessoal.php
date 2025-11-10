<?php

use App\Http\Controllers\Admin\DepartamentoController;
use App\Http\Controllers\Admin\FornecedorController;
use App\Http\Controllers\Admin\PessoalController;
use App\Http\Controllers\Admin\ProdutosImobilizadosController;
use App\Http\Controllers\Admin\RelatorioContratoFornecedores;
use App\Http\Controllers\Admin\RelatorioServicosFornecedores;
use App\Http\Controllers\Admin\ServicoController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'relatorioservicosfornecedores'], function () {
    Route::get('/', [RelatorioServicosFornecedores::class, 'index'])->name('relatorioservicosfornecedores.index');
    Route::post('/gerarpdf', [RelatorioServicosFornecedores::class, 'gerarPdf'])->name('relatorioservicosfornecedores.gerarpdf');
    Route::post('/gerarexcel', [RelatorioServicosFornecedores::class, 'gerarExcel'])->name('relatorioservicosfornecedores.gerarexcel');
});

Route::group(['prefix' => 'relatoriocontratofornecedores'], function () {
    Route::get('/', [RelatorioContratoFornecedores::class, 'index'])->name('relatoriocontratofornecedores.index');
    Route::post('/gerarpdf', [RelatorioContratoFornecedores::class, 'gerarPdf'])->name('relatoriocontratofornecedores.gerarpdf');
    Route::post('/gerarexcel', [RelatorioContratoFornecedores::class, 'gerarExcel'])->name('relatoriocontratofornecedores.gerarexcel');
});

Route::prefix('api')->group(function () {
    // Fornecedor
    Route::get('/fornecedor/search', [FornecedorController::class, 'search'])->name('api.fornecedor.search');
    Route::get('/fornecedor/single/{id}', [FornecedorController::class, 'getById'])->name('api.fornecedor.single');

    Route::get('/users/search', [UserController::class, 'search'])->name('api.users.search');
    Route::get('/users/single/{id}', [UserController::class, 'getById'])->name('api.users.single');
    // Servico

    Route::get('/servico/search', [ServicoController::class, 'search'])->name('api.servico.search');
    Route::get('/servico/single/{id}', [ServicoController::class, 'getById'])->name('api.servico.single');

    // Departamento
    Route::get('/departamento/search', [DepartamentoController::class, 'search'])->name('api.departamento.search');
    Route::get('/departamento/single/{id}', [DepartamentoController::class, 'getById'])->name('api.departamento.single');
    // Produtos Imobilizados
    Route::get('/produtosimobilizados/search', [ProdutosImobilizadosController::class, 'search'])->name('api.produtosimobilizados.search');
    Route::get('/produtosimobilizados/single/{id}', [ProdutosImobilizadosController::class, 'getById'])->name('api.produtosimobilizados.single');
    // Pessoal
    Route::get('/pessoal/search', [PessoalController::class, 'search'])->name('api.pessoal.search');
    Route::get('/pessoal/single/{id}', [PessoalController::class, 'getById'])->name('api.pessoal.single');
});

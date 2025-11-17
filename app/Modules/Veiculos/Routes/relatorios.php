<?php

use App\Http\Controllers\Admin\DepartamentoController;
use App\Http\Controllers\Admin\FornecedorController;
use App\Http\Controllers\Admin\PessoalController;
use App\Http\Controllers\Admin\ProdutosImobilizadosController;
use App\Http\Controllers\Admin\RelatorioCertificadoVeiculo;
use App\Modules\Veiculos\Controllers\Relatorios\RelatorioCompraVendaVeiculo;
use App\Modules\Veiculos\Controllers\Relatorios\RelatorioConsultarVeiculo;
use App\Http\Controllers\Admin\RelatorioContCorrenteFornecedor;
use App\Http\Controllers\Admin\RelatorioExtratoIpva;
use App\Modules\Veiculos\Controllers\Relatorios\RelatorioHistoricoKm;
use App\Http\Controllers\Admin\RelatorioIpvaLicenciamentoVeiculo;
use App\Modules\Multas\Controllers\Relatorios\RelatorioMultas;
use App\Modules\Veiculos\Controllers\Relatorios\RelatorioTransferenciaVeiculo;
use App\Modules\Veiculos\Controllers\Relatorios\RelatorioVeiculos;
use App\Http\Controllers\Admin\UserController;
use App\Modules\Veiculos\Controllers\Admin\VeiculoController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'relatorioconsultarveiculo'], function () {
    Route::get('/', [RelatorioConsultarVeiculo::class, 'index'])->name('relatorioconsultarveiculo.index');
    Route::post('/gerarpdf', [RelatorioConsultarVeiculo::class, 'gerarPdf'])->name('relatorioconsultarveiculo.gerarpdf');
    Route::post('/gerarexcel', [RelatorioConsultarVeiculo::class, 'gerarExcel'])->name('relatorioconsultarveiculo.gerarexcel');
    Route::get('/abrirModal/{id}', [RelatorioConsultarVeiculo::class, 'abrirModal'])->name('relatorioconsultarveiculo.abrirmodal');
});

Route::group(['prefix' => 'relatoriocompraevendaveiculo'], function () {
    Route::get('/', [RelatorioCompraVendaVeiculo::class, 'index'])->name('relatoriocompraevendaveiculo.index');
    Route::get('/exportPdf', [RelatorioCompraVendaVeiculo::class, 'exportPdf'])->name('relatoriocompraevendaveiculo.exportPdf');
    Route::get('/exportXls', [RelatorioCompraVendaVeiculo::class, 'exportXls'])->name('relatoriocompraevendaveiculo.exportXls');
});

Route::group(['prefix' => 'relatoriocontacorrentefornecedor'], function () {
    Route::get('/', [RelatorioContCorrenteFornecedor::class, 'index'])->name('relatoriocontacorrentefornecedor.index');
    Route::post('/gerarpdf', [RelatorioContCorrenteFornecedor::class, 'gerarPdf'])->name('relatoriocontacorrentefornecedor.gerarpdf');
    Route::post('/gerarexcel', [RelatorioContCorrenteFornecedor::class, 'gerarExcel'])->name('relatoriocontacorrentefornecedor.gerarexcel');
});

Route::group(['prefix' => 'relatoriohistoricokm'], function () {
    Route::get('/', [RelatorioHistoricoKm::class, 'index'])->name('relatoriohistoricokm.index');
    Route::post('/gerarpdf', [RelatorioHistoricoKm::class, 'gerarPdf'])->name('relatoriohistoricokm.gerarpdf');
    Route::post('/gerarexcel', [RelatorioHistoricoKm::class, 'gerarExcel'])->name('relatoriohistoricokm.gerarexcel');
});

Route::group(['prefix' => 'relatorioipvalicenciamento'], function () {
    Route::get('/', [RelatorioIpvaLicenciamentoVeiculo::class, 'index'])->name('relatorioipvalicenciamento.index');
    Route::post('/gerarpdf', [RelatorioIpvaLicenciamentoVeiculo::class, 'gerarPdf'])->name('relatorioipvalicenciamento.gerarpdf');
    Route::post('/gerarexcel', [RelatorioIpvaLicenciamentoVeiculo::class, 'gerarExcel'])->name('relatorioipvalicenciamento.gerarexcel');
});

Route::group(['prefix' => 'relatoriocertificadoveiculo'], function () {
    Route::get('/', [RelatorioCertificadoVeiculo::class, 'index'])->name('relatoriocertificadoveiculo.index');
    Route::post('/gerarpdf', [RelatorioCertificadoVeiculo::class, 'gerarPdf'])->name('relatoriocertificadoveiculo.gerarpdf');
    Route::post('/gerarexcel', [RelatorioCertificadoVeiculo::class, 'gerarExcel'])->name('relatoriocertificadoveiculo.gerarexcel');
});

Route::group(['prefix' => 'relatoriomultas'], function () {
    Route::get('/', [RelatorioMultas::class, 'index'])->name('relatoriomultas.index');
    Route::post('/gerarpdf', [RelatorioMultas::class, 'gerarPdf'])->name('relatoriomultas.gerarpdf');
    Route::post('/gerarexcel', [RelatorioMultas::class, 'gerarExcel'])->name('relatoriomultas.gerarexcel');
});

Route::group(['prefix' => 'relatorioveiculos'], function () {
    Route::get('/', [RelatorioVeiculos::class, 'index'])->name('relatorioveiculos.index');
    Route::post('/gerarpdf', [RelatorioVeiculos::class, 'gerarPdf'])->name('relatorioveiculos.gerarpdf');
    Route::post('/gerarexcel', [RelatorioVeiculos::class, 'gerarExcel'])->name('relatorioveiculos.gerarexcel');
});

Route::group(['prefix' => 'relatoriotransferenciaveiculo'], function () {
    Route::get('/', [RelatorioTransferenciaVeiculo::class, 'index'])->name('relatoriotransferenciaveiculo.index');
    Route::get('/exportPdf', [RelatorioTransferenciaVeiculo::class, 'exportPdf'])->name('relatoriotransferenciaveiculo.exportPdf');
    Route::get('/exportXls', [RelatorioTransferenciaVeiculo::class, 'exportXls'])->name('relatoriotransferenciaveiculo.exportXls');
});

Route::group(['prefix' => 'relatorioextratoipva'], function () {
    Route::get('/', [RelatorioExtratoIpva::class, 'index'])->name('relatorioextratoipva.index');
    Route::post('/gerarpdf', [RelatorioExtratoIpva::class, 'gerarPdf'])->name('relatorioextratoipva.gerarpdf');
    Route::post('/gerarexcel', [RelatorioExtratoIpva::class, 'gerarExcel'])->name('relatorioextratoipva.gerarexcel');
});

Route::prefix('api')->group(function () {
    // Fornecedor
    Route::get('/fornecedor/search', [FornecedorController::class, 'search'])->name('api.fornecedor.search');
    Route::get('/fornecedor/single/{id}', [FornecedorController::class, 'getById'])->name('api.fornecedor.single');

    Route::get('/users/search', [UserController::class, 'search'])->name('api.users.search');
    Route::get('/users/single/{id}', [UserController::class, 'getById'])->name('api.users.single');

    // Departamento
    Route::get('/departamento/search', [DepartamentoController::class, 'search'])->name('api.departamento.search');
    Route::get('/departamento/single/{id}', [DepartamentoController::class, 'getById'])->name('api.departamento.single');
    // Produtos Imobilizados
    Route::get('/produtosimobilizados/search', [ProdutosImobilizadosController::class, 'search'])->name('api.produtosimobilizados.search');
    Route::get('/produtosimobilizados/single/{id}', [ProdutosImobilizadosController::class, 'getById'])->name('api.produtosimobilizados.single');
    // Pessoal
    Route::get('/pessoal/search', [PessoalController::class, 'search'])->name('api.pessoal.search');
    Route::get('/pessoal/single/{id}', [PessoalController::class, 'getById'])->name('api.pessoal.single');

    Route::get('/veiculos/search', [VeiculoController::class, 'search'])->name('api.veiculos.search');
    Route::get('/veiculos/single/{id}', [VeiculoController::class, 'getById'])->name('api.veiculos.single');
});

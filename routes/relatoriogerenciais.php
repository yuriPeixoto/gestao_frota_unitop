<?php

use App\Http\Controllers\Admin\FornecedorController;
use App\Http\Controllers\Admin\RelatorioAbastecimentoTotais;
use App\Http\Controllers\Admin\RelatorioCustosVariaveisPorDepartamento;
use App\Http\Controllers\Admin\RelatorioDuracaoManutencoesOS;
use App\Http\Controllers\Admin\RelatorioEntradaProdutos;
use App\Http\Controllers\Admin\RelatorioExtratoContaFornecedor;
use App\Http\Controllers\Admin\RelatorioFechamentoMensalControladoria;
use App\Http\Controllers\Admin\RelatorioFornecedorSemNF;
use App\Http\Controllers\Admin\RelatorioInventarioPneus;
use App\Http\Controllers\Admin\RelatorioNfsManutencaoRealizadas;
use App\Http\Controllers\Admin\RelatorioRecebimentoCombustivel;
use App\Http\Controllers\Admin\RelatorioUltimaMovimentacaoDespesas;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'relatorioduracaodasmanutencoes'], function () {
    Route::get('/', [RelatorioDuracaoManutencoesOS::class, 'index'])->name('relatorioduracaodasmanutencoes.index');
    Route::post('/gerarpdf', [RelatorioDuracaoManutencoesOS::class, 'gerarPdf'])->name('relatorioduracaodasmanutencoes.gerarpdf');
    Route::post('/gerarexcel', [RelatorioDuracaoManutencoesOS::class, 'gerarExcel'])->name('relatorioduracaodasmanutencoes.gerarexcel');
});

Route::prefix('api')->group(function () {
    // Fornecedor
    Route::get('/fornecedor/search', [FornecedorController::class, 'search'])->name('api.fornecedor.search');
    Route::get('/fornecedor/single/{id}', [FornecedorController::class, 'getById'])->name('api.fornecedor.single');
});

// Route::prefix('api')->group(function () {
//     // Fornecedor
//     Route::get('/fornecedor/search', [FornecedorController::class, 'search'])->name('api.fornecedor.search');
//     Route::get('/fornecedor/single/{id}', [FornecedorController::class, 'getById'])->name('api.fornecedor.single');
// });

Route::group(['prefix' => 'relatoriorecebimentocombustivel'], function () {
    Route::get('/', [RelatorioRecebimentoCombustivel::class, 'index'])->name('relatoriorecebimentocombustivel.index');
    Route::post('/gerarpdf', [RelatorioRecebimentoCombustivel::class, 'gerarPdf'])->name('relatoriorecebimentocombustivel.gerarpdf');
    Route::post('/gerarexcel', [RelatorioRecebimentoCombustivel::class, 'gerarExcel'])->name('relatoriorecebimentocombustivel.gerarexcel');
});

Route::group(['prefix' => 'relatorionfsmanutencaorealizadas'], function () {
    Route::get('/', [RelatorioNfsManutencaoRealizadas::class, 'index'])->name('relatorionfsmanutencaorealizadas.index');
    Route::post('/gerarpdf', [RelatorioNfsManutencaoRealizadas::class, 'gerarPdf'])->name('relatorionfsmanutencaorealizadas.gerarpdf');
    Route::post('/gerarexcel', [RelatorioNfsManutencaoRealizadas::class, 'gerarExcel'])->name('relatorionfsmanutencaorealizadas.gerarexcel');
});

Route::group(['prefix' => 'relatoriofornecedorsemnf'], function () {
    Route::get('/', [RelatorioFornecedorSemNF::class, 'index'])->name('relatoriofornecedorsemnf.index');
    Route::post('/gerarpdf', [RelatorioFornecedorSemNF::class, 'gerarPdf'])->name('relatoriofornecedorsemnf.gerarpdf');
    Route::post('/gerarexcel', [RelatorioFornecedorSemNF::class, 'gerarExcel'])->name('relatoriofornecedorsemnf.gerarexcel');
});

Route::group(['prefix' => 'relatorioabastecimentototais'], function () {
    Route::get('/', [RelatorioAbastecimentoTotais::class, 'index'])->name('relatorioabastecimentototais.index');
    Route::post('/gerarpdf', [RelatorioAbastecimentoTotais::class, 'gerarPdf'])->name('relatorioabastecimentototais.gerarpdf');
    Route::post('/gerarexcel', [RelatorioAbastecimentoTotais::class, 'gerarExcel'])->name('relatorioabastecimentototais.gerarexcel');
});

Route::group(['prefix' => 'relatorioentradaprodutos'], function () {
    Route::get('/', [RelatorioEntradaProdutos::class, 'index'])->name('relatorioentradaprodutos.index');
    Route::post('/gerarpdf', [RelatorioEntradaProdutos::class, 'gerarPdf'])->name('relatorioentradaprodutos.gerarpdf');
    Route::post('/gerarexcel', [RelatorioEntradaProdutos::class, 'gerarExcel'])->name('relatorioentradaprodutos.gerarexcel');
});

Route::group(['prefix' => 'relatoriocustospordepartamento'], function () {
    Route::get('/', [RelatorioCustosVariaveisPorDepartamento::class, 'index'])->name('relatoriocustospordepartamento.index');
    Route::post('/gerarpdf', [RelatorioCustosVariaveisPorDepartamento::class, 'gerarPdf'])->name('relatoriocustospordepartamento.gerarpdf');
    Route::post('/gerarexcel', [RelatorioCustosVariaveisPorDepartamento::class, 'gerarExcel'])->name('relatoriocustospordepartamento.gerarexcel');
});

Route::group(['prefix' => 'relatoriofechamentomensalcontroladoria'], function () {
    Route::get('/', [RelatorioFechamentoMensalControladoria::class, 'index'])->name('relatoriofechamentomensalcontroladoria.index');
    Route::post('/gerarexcel', [RelatorioFechamentoMensalControladoria::class, 'gerarExcel'])->name('relatoriofechamentomensalcontroladoria.gerarexcel');
});

Route::group(['prefix' => 'relatorioextratocontafornecedor'], function () {
    Route::get('/', [RelatorioExtratoContaFornecedor::class, 'index'])->name('relatorioextratocontafornecedor.index');
    Route::post('/gerarexcel', [RelatorioExtratoContaFornecedor::class, 'gerarExcel'])->name('relatorioextratocontafornecedor.gerarexcel');
});

Route::group(['prefix' => 'relatorioultimamovimentacaodespesas'], function () {
    Route::get('/', [RelatorioUltimaMovimentacaoDespesas::class, 'index'])->name('relatorioultimamovimentacaodespesas.index');
    Route::post('/gerarpdf', [RelatorioUltimaMovimentacaoDespesas::class, 'gerarPdf'])->name('relatorioultimamovimentacaodespesas.gerarpdf');
    Route::post('/gerarexcel', [RelatorioUltimaMovimentacaoDespesas::class, 'gerarExcel'])->name('relatorioultimamovimentacaodespesas.gerarexcel');
});

Route::group(['prefix' => 'relatorioinventariopneus'], function () {
    Route::get('/', [RelatorioInventarioPneus::class, 'index'])->name('relatorioinventariopneus.index');
    Route::post('/gerarpdf', [RelatorioInventarioPneus::class, 'gerarPdf'])->name('relatorioinventariopneus.gerarpdf');
    Route::post('/gerarexcel', [RelatorioInventarioPneus::class, 'gerarExcel'])->name('relatorioinventariopneus.gerarexcel');
});

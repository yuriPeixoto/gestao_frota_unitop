<?php

use App\Http\Controllers\Admin\AbastecimentoEquipamentoController;
use App\Http\Controllers\Admin\AbastecimentoManualRelatorioController;
use App\Http\Controllers\Admin\AbastecimentoPlacaTotalizado;
use App\Http\Controllers\Admin\ConsultarLancamentosKmManualController;
use App\Http\Controllers\Admin\ExtratoAbastecimentoTerceirosController;
use App\Http\Controllers\Admin\FaturamentoAbastecimentoController;
use App\Http\Controllers\Admin\FechamentoAbastecimentoMediaController;
use App\Http\Controllers\Admin\FornecedorController;
use App\Http\Controllers\Admin\Integracao486SswController;
use App\Http\Controllers\Admin\ListagemEncerrantesController;
use App\Http\Controllers\Admin\ListagemKmHistoricoController;
use App\Http\Controllers\Admin\RelatorioAbastecimentoBombaPosto;
use App\Http\Controllers\Admin\VeiculoController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'abastecimentomanualrelatorio'], function () {
    // === GET: Páginas de visualização ===
    Route::get('/', [AbastecimentoManualRelatorioController::class, 'index'])->name('abastecimentomanualrelatorio.index');

    Route::post('/imprimir', [AbastecimentoManualRelatorioController::class, 'onImprimir'])->name('abastecimentomanualrelatorio.imprimir');
    Route::post('/imprimirexcel', [AbastecimentoManualRelatorioController::class, 'onImprimirExcel'])->name('abastecimentomanualrelatorio.imprimirexcel');
});

Route::group(['prefix' => 'abastecimentoplacatotalizado'], function () {
    // === GET: Páginas de visualização ===
    Route::get('/', [AbastecimentoPlacaTotalizado::class, 'index'])->name('abastecimentoplacatotalizado.index');

    Route::post('/imprimir', [AbastecimentoPlacaTotalizado::class, 'onImprimir'])->name('abastecimentoplacatotalizado.imprimir');
    Route::post('/imprimirexcel', [AbastecimentoPlacaTotalizado::class, 'onImprimirExcel'])->name('abastecimentoplacatotalizado.imprimirexcel');
});

Route::group(['prefix' => 'consultarlancamentoskmmanual'], function () {
    // === GET: Páginas de visualização ===
    Route::get('/', [ConsultarLancamentosKmManualController::class, 'index'])->name('consultarlancamentoskmmanual.index');

    Route::post('/imprimir', [ConsultarLancamentosKmManualController::class, 'onImprimir'])->name('consultarlancamentoskmmanual.imprimir');
    Route::post('/imprimirexcel', [ConsultarLancamentosKmManualController::class, 'onImprimirExcel'])->name('consultarlancamentoskmmanual.imprimirexcel');
});

Route::group(['prefix' => 'abastecimentoequipamento'], function () {
    // === GET: Páginas de visualização ===
    Route::get('/', [AbastecimentoEquipamentoController::class, 'index'])->name('abastecimentoequipamento.index');

    Route::post('/imprimir', [AbastecimentoEquipamentoController::class, 'onImprimir'])->name('abastecimentoequipamento.imprimir');
    Route::post('/imprimirexcel', [AbastecimentoEquipamentoController::class, 'onImprimirExcel'])->name('abastecimentoequipamento.imprimirexcel');
});

Route::group(['prefix' => 'extratoabastecimentoterceiros'], function () {
    // === GET: Páginas de visualização ===
    Route::get('/', [ExtratoAbastecimentoTerceirosController::class, 'index'])->name('extratoabastecimentoterceiros.index');

    Route::post('/imprimir', [ExtratoAbastecimentoTerceirosController::class, 'onImprimir'])->name('extratoabastecimentoterceiros.imprimir');
    Route::post('/imprimirexcel', [ExtratoAbastecimentoTerceirosController::class, 'onImprimirExcel'])->name('extratoabastecimentoterceiros.imprimirexcel');
});

Route::group(['prefix' => 'fechamentoabastecimentomedia'], function () {
    // === GET: Páginas de visualização ===
    Route::get('/', [FechamentoAbastecimentoMediaController::class, 'index'])->name('fechamentoabastecimentomedia.index');

    Route::post('/imprimir', [FechamentoAbastecimentoMediaController::class, 'onImprimir'])->name('fechamentoabastecimentomedia.imprimir');
    Route::post('/imprimirexcel', [FechamentoAbastecimentoMediaController::class, 'onImprimirExcel'])->name('fechamentoabastecimentomedia.imprimirexcel');
});

Route::group(['prefix' => 'integracao486Ssw'], function () {
    // === GET: Páginas de visualização ===
    Route::get('/', [Integracao486SswController::class, 'index'])->name('integracao486Ssw.index');

    Route::post('/imprimir', [Integracao486SswController::class, 'onImprimir'])->name('integracao486Ssw.imprimir');
    Route::post('/imprimirexcel', [Integracao486SswController::class, 'onImprimirExcel'])->name('integracao486Ssw.imprimirexcel');
});

Route::group(['prefix' => 'listagemencerrantes'], function () {
    // === GET: Páginas de visualização ===
    Route::get('/', [ListagemEncerrantesController::class, 'index'])->name('listagemencerrantes.index');

    Route::post('/imprimir', [ListagemEncerrantesController::class, 'onImprimir'])->name('listagemencerrantes.imprimir');
    Route::post('/imprimirexcel', [ListagemEncerrantesController::class, 'onImprimirExcel'])->name('listagemencerrantes.imprimirexcel');
});

Route::group(['prefix' => 'listagemkmhistorico'], function () {
    // === GET: Páginas de visualização ===
    Route::get('/', [ListagemKmHistoricoController::class, 'index'])->name('listagemkmhistorico.index');

    Route::post('/imprimir', [ListagemKmHistoricoController::class, 'onImprimir'])->name('listagemkmhistorico.imprimir');
});

Route::group(['prefix' => 'faturamentoabastecimento'], function () {
    // === GET: Páginas de visualização ===
    Route::get('/', [FaturamentoAbastecimentoController::class, 'index'])->name('faturamentoabastecimento.index');

    Route::post('/imprimir', [FaturamentoAbastecimentoController::class, 'onImprimir'])->name('faturamentoabastecimento.imprimir');
    Route::post('/imprimirexcel', [FaturamentoAbastecimentoController::class, 'onImprimirExcel'])->name('faturamentoabastecimento.imprimirexcel');
});


Route::prefix('api')->group(function () {
    // Veículos
    Route::get('/veiculos/search', [VeiculoController::class, 'search'])->name('api.veiculos.search');
    Route::get('/veiculos/single/{id}', [VeiculoController::class, 'getById'])->name('api.veiculos.single');

    // Fornecedor
    Route::get('/fornecedor/search', [FornecedorController::class, 'search'])->name('api.fornecedor.search');
    Route::get('/fornecedor/single/{id}', [FornecedorController::class, 'getById'])->name('api.fornecedor.single');
});

Route::group(['prefix' => 'abastecimentoporbomposto'], function () {
    Route::get('/', [RelatorioAbastecimentoBombaPosto::class, 'index'])->name('abastecimentoporbomposto.index');
    Route::get('/gerarpdf', [RelatorioAbastecimentoBombaPosto::class, 'exportPdf'])->name('abastecimentoporbomposto.gerarpdf');
    Route::get('/gerarexcel', [RelatorioAbastecimentoBombaPosto::class, 'exportXls'])->name('abastecimentoporbomposto.gerarexcel');
});

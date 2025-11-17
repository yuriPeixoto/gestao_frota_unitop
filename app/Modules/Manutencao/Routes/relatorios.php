<?php
/**
 * Rotas de Relatórios de Manutenção
 */
use App\Modules\Manutencao\Controllers\Relatorios\RelatorioManutencaoDetalhadasController;
use App\Modules\Manutencao\Controllers\Relatorios\RelatorioManutencaoVencidasController;
use App\Modules\Manutencao\Controllers\Relatorios\RelatorioNfsManutencaoRealizadas;
use App\Modules\Manutencao\Controllers\Relatorios\RelatorioOrdemServicoStatusController;
use App\Modules\Manutencao\Controllers\Relatorios\RelatorioPeçasUtilizadasOsController;
use App\Modules\Manutencao\Controllers\Relatorios\RelatorioServicosFornecedores;
use App\Modules\Manutencao\Controllers\Relatorios\RelatorioServicosUtilizadasOsController;
use App\Modules\Manutencao\Controllers\Relatorios\RelatorioSinteticoNfOsController;
use Illuminate\Support\Facades\Route;

// Relatório de Manutenção Detalhada
Route::group(['prefix' => 'relatoriomanutencaodetalhada'], function () {
    Route::get('/', [RelatorioManutencaoDetalhadasController::class, 'index'])->name('relatoriomanutencaodetalhada.index');
    Route::post('/gerarpdf', [RelatorioManutencaoDetalhadasController::class, 'gerarPdf'])->name('relatoriomanutencaodetalhada.gerarpdf');
    Route::post('/gerarexcel', [RelatorioManutencaoDetalhadasController::class, 'gerarExcel'])->name('relatoriomanutencaodetalhada.gerarexcel');
});

// Relatório de Manutenção Vencidas
Route::group(['prefix' => 'relatoriomanutencaovencidas'], function () {
    Route::get('/', [RelatorioManutencaoVencidasController::class, 'index'])->name('relatoriomanutencaovencidas.index');
    Route::post('/gerarpdf', [RelatorioManutencaoVencidasController::class, 'gerarPdf'])->name('relatoriomanutencaovencidas.gerarpdf');
    Route::post('/gerarexcel', [RelatorioManutencaoVencidasController::class, 'gerarExcel'])->name('relatoriomanutencaovencidas.gerarexcel');
});

// Relatório de NFs de Manutenção Realizadas
Route::group(['prefix' => 'relatorionfsmanutencaorealizadas'], function () {
    Route::get('/', [RelatorioNfsManutencaoRealizadas::class, 'index'])->name('relatorionfsmanutencaorealizadas.index');
    Route::post('/gerarpdf', [RelatorioNfsManutencaoRealizadas::class, 'gerarPdf'])->name('relatorionfsmanutencaorealizadas.gerarpdf');
    Route::post('/gerarexcel', [RelatorioNfsManutencaoRealizadas::class, 'gerarExcel'])->name('relatorionfsmanutencaorealizadas.gerarexcel');
});

// Relatório de Ordem de Serviço por Status
Route::group(['prefix' => 'relatorioordemservicostatus'], function () {
    Route::get('/', [RelatorioOrdemServicoStatusController::class, 'index'])->name('relatorioordemservicostatus.index');
    Route::post('/gerarpdf', [RelatorioOrdemServicoStatusController::class, 'gerarPdf'])->name('relatorioordemservicostatus.gerarpdf');
    Route::post('/gerarexcel', [RelatorioOrdemServicoStatusController::class, 'gerarExcel'])->name('relatorioordemservicostatus.gerarexcel');
});

// Relatório de Peças Utilizadas em OS
Route::group(['prefix' => 'relatoriopecasutilizadasos'], function () {
    Route::get('/', [RelatorioPeçasUtilizadasOsController::class, 'index'])->name('relatoriopecasutilizadasos.index');
    Route::post('/gerarpdf', [RelatorioPeçasUtilizadasOsController::class, 'gerarPdf'])->name('relatoriopecasutilizadasos.gerarpdf');
    Route::post('/gerarexcel', [RelatorioPeçasUtilizadasOsController::class, 'gerarExcel'])->name('relatoriopecasutilizadasos.gerarexcel');
});

// Relatório de Serviços por Fornecedores
Route::group(['prefix' => 'relatorioservicosfornecedores'], function () {
    Route::get('/', [RelatorioServicosFornecedores::class, 'index'])->name('relatorioservicosfornecedores.index');
    Route::post('/gerarpdf', [RelatorioServicosFornecedores::class, 'gerarPdf'])->name('relatorioservicosfornecedores.gerarpdf');
    Route::post('/gerarexcel', [RelatorioServicosFornecedores::class, 'gerarExcel'])->name('relatorioservicosfornecedores.gerarexcel');
});

// Relatório de Serviços Utilizados em OS
Route::group(['prefix' => 'relatorioservicosutilizadasos'], function () {
    Route::get('/', [RelatorioServicosUtilizadasOsController::class, 'index'])->name('relatorioservicosutilizadasos.index');
    Route::post('/gerarpdf', [RelatorioServicosUtilizadasOsController::class, 'gerarPdf'])->name('relatorioservicosutilizadasos.gerarpdf');
    Route::post('/gerarexcel', [RelatorioServicosUtilizadasOsController::class, 'gerarExcel'])->name('relatorioservicosutilizadasos.gerarexcel');
});

// Relatório Sintético de NF x OS
Route::group(['prefix' => 'relatoriosinteticonfos'], function () {
    Route::get('/', [RelatorioSinteticoNfOsController::class, 'index'])->name('relatoriosinteticonfos.index');
    Route::post('/gerarpdf', [RelatorioSinteticoNfOsController::class, 'gerarPdf'])->name('relatoriosinteticonfos.gerarpdf');
    Route::post('/gerarexcel', [RelatorioSinteticoNfOsController::class, 'gerarExcel'])->name('relatoriosinteticonfos.gerarexcel');
});

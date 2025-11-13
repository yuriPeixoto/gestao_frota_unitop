<?php
/**
 * NOTA: Este arquivo contém rotas mistas que serão movidas gradualmente:
 * - Relatórios Gerenciais: Movidos para app/Modules/RelatoriosGerenciais
 * - Relatórios de Abastecimentos: Já movidos para app/Modules/Abastecimentos
 * - Relatórios de Pneus: Serão movidos para app/Modules/Pneus (pendente)
 * - Relatórios de Manutenção: Serão movidos para app/Modules/Manutencao (pendente)
 * - FornecedorController: Será movido para app/Modules/Compras (pendente)
 */

use App\Http\Controllers\Admin\FornecedorController;
use App\Modules\Abastecimentos\Controllers\Relatorios\RelatorioAbastecimentoTotais;
use App\Http\Controllers\Admin\RelatorioDuracaoManutencoesOS;
use App\Http\Controllers\Admin\RelatorioInventarioPneus;
use App\Http\Controllers\Admin\RelatorioNfsManutencaoRealizadas;
use App\Modules\Abastecimentos\Controllers\Relatorios\RelatorioRecebimentoCombustivel;
use Illuminate\Support\Facades\Route;

// Inclui rotas do módulo de Relatórios Gerenciais (estrutura modular)
require __DIR__ . '/modules/relatoriosgerenciais.php';

Route::group(['prefix' => 'relatorioduracaodasmanutencoes'], function () {
    Route::get('/', [RelatorioDuracaoManutencoesOS::class, 'index'])->name('relatorioduracaodasmanutencoes.index');
    Route::post('/gerarpdf', [RelatorioDuracaoManutencoesOS::class, 'gerarPdf'])->name('relatorioduracaodasmanutencoes.gerarpdf');
    Route::post('/gerarexcel', [RelatorioDuracaoManutencoesOS::class, 'gerarExcel'])->name('relatorioduracaodasmanutencoes.gerarexcel');
});

// API Fornecedor (será movido para módulo de Compras)
Route::prefix('api')->group(function () {
    Route::get('/fornecedor/search', [FornecedorController::class, 'search'])->name('api.fornecedor.search');
    Route::get('/fornecedor/single/{id}', [FornecedorController::class, 'getById'])->name('api.fornecedor.single');
});

// Relatórios de Abastecimentos (já movidos para o módulo)
Route::group(['prefix' => 'relatoriorecebimentocombustivel'], function () {
    Route::get('/', [RelatorioRecebimentoCombustivel::class, 'index'])->name('relatoriorecebimentocombustivel.index');
    Route::post('/gerarpdf', [RelatorioRecebimentoCombustivel::class, 'gerarPdf'])->name('relatoriorecebimentocombustivel.gerarpdf');
    Route::post('/gerarexcel', [RelatorioRecebimentoCombustivel::class, 'gerarExcel'])->name('relatoriorecebimentocombustivel.gerarexcel');
});

// Relatórios de Manutenção (serão movidos para o módulo)
Route::group(['prefix' => 'relatorionfsmanutencaorealizadas'], function () {
    Route::get('/', [RelatorioNfsManutencaoRealizadas::class, 'index'])->name('relatorionfsmanutencaorealizadas.index');
    Route::post('/gerarpdf', [RelatorioNfsManutencaoRealizadas::class, 'gerarPdf'])->name('relatorionfsmanutencaorealizadas.gerarpdf');
    Route::post('/gerarexcel', [RelatorioNfsManutencaoRealizadas::class, 'gerarExcel'])->name('relatorionfsmanutencaorealizadas.gerarexcel');
});

Route::group(['prefix' => 'relatorioabastecimentototais'], function () {
    Route::get('/', [RelatorioAbastecimentoTotais::class, 'index'])->name('relatorioabastecimentototais.index');
    Route::post('/gerarpdf', [RelatorioAbastecimentoTotais::class, 'gerarPdf'])->name('relatorioabastecimentototais.gerarpdf');
    Route::post('/gerarexcel', [RelatorioAbastecimentoTotais::class, 'gerarExcel'])->name('relatorioabastecimentototais.gerarexcel');
});

// Relatórios de Pneus (serão movidos para o módulo)
Route::group(['prefix' => 'relatorioinventariopneus'], function () {
    Route::get('/', [RelatorioInventarioPneus::class, 'index'])->name('relatorioinventariopneus.index');
    Route::post('/gerarpdf', [RelatorioInventarioPneus::class, 'gerarPdf'])->name('relatorioinventariopneus.gerarpdf');
    Route::post('/gerarexcel', [RelatorioInventarioPneus::class, 'gerarExcel'])->name('relatorioinventariopneus.gerarexcel');
});

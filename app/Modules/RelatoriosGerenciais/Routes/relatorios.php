<?php
/**
 * Rotas do Módulo de Relatórios Gerenciais
 * Estrutura modular implementada em: 2025-11-13
 */
use App\Modules\RelatoriosGerenciais\Controllers\Relatorios\RelatorioCustosVariaveisPorDepartamento;
use App\Modules\RelatoriosGerenciais\Controllers\Relatorios\RelatorioEntradaProdutos;
use App\Modules\RelatoriosGerenciais\Controllers\Relatorios\RelatorioExtratoContaFornecedor;
use App\Modules\RelatoriosGerenciais\Controllers\Relatorios\RelatorioFechamentoMensalControladoria;
use App\Modules\RelatoriosGerenciais\Controllers\Relatorios\RelatorioFornecedorSemNF;
use App\Modules\RelatoriosGerenciais\Controllers\Relatorios\RelatorioUltimaMovimentacaoDespesas;
use Illuminate\Support\Facades\Route;

// Relatório de Custos Variáveis por Departamento
Route::group(['prefix' => 'relatoriocustospordepartamento'], function () {
    Route::get('/', [RelatorioCustosVariaveisPorDepartamento::class, 'index'])->name('relatoriocustospordepartamento.index');
    Route::post('/gerarpdf', [RelatorioCustosVariaveisPorDepartamento::class, 'gerarPdf'])->name('relatoriocustospordepartamento.gerarpdf');
    Route::post('/gerarexcel', [RelatorioCustosVariaveisPorDepartamento::class, 'gerarExcel'])->name('relatoriocustospordepartamento.gerarexcel');
});

// Relatório de Entrada de Produtos
Route::group(['prefix' => 'relatorioentradaprodutos'], function () {
    Route::get('/', [RelatorioEntradaProdutos::class, 'index'])->name('relatorioentradaprodutos.index');
    Route::post('/gerarpdf', [RelatorioEntradaProdutos::class, 'gerarPdf'])->name('relatorioentradaprodutos.gerarpdf');
    Route::post('/gerarexcel', [RelatorioEntradaProdutos::class, 'gerarExcel'])->name('relatorioentradaprodutos.gerarexcel');
});

// Relatório de Extrato Conta Fornecedor
Route::group(['prefix' => 'relatorioextratocontafornecedor'], function () {
    Route::get('/', [RelatorioExtratoContaFornecedor::class, 'index'])->name('relatorioextratocontafornecedor.index');
    Route::post('/gerarexcel', [RelatorioExtratoContaFornecedor::class, 'gerarExcel'])->name('relatorioextratocontafornecedor.gerarexcel');
});

// Relatório de Fechamento Mensal Controladoria
Route::group(['prefix' => 'relatoriofechamentomensalcontroladoria'], function () {
    Route::get('/', [RelatorioFechamentoMensalControladoria::class, 'index'])->name('relatoriofechamentomensalcontroladoria.index');
    Route::post('/gerarexcel', [RelatorioFechamentoMensalControladoria::class, 'gerarExcel'])->name('relatoriofechamentomensalcontroladoria.gerarexcel');
});

// Relatório de Fornecedor sem NF
Route::group(['prefix' => 'relatoriofornecedorsemnf'], function () {
    Route::get('/', [RelatorioFornecedorSemNF::class, 'index'])->name('relatoriofornecedorsemnf.index');
    Route::post('/gerarpdf', [RelatorioFornecedorSemNF::class, 'gerarPdf'])->name('relatoriofornecedorsemnf.gerarpdf');
    Route::post('/gerarexcel', [RelatorioFornecedorSemNF::class, 'gerarExcel'])->name('relatoriofornecedorsemnf.gerarexcel');
});

// Relatório de Última Movimentação de Despesas
Route::group(['prefix' => 'relatorioultimamovimentacaodespesas'], function () {
    Route::get('/', [RelatorioUltimaMovimentacaoDespesas::class, 'index'])->name('relatorioultimamovimentacaodespesas.index');
    Route::post('/gerarpdf', [RelatorioUltimaMovimentacaoDespesas::class, 'gerarPdf'])->name('relatorioultimamovimentacaodespesas.gerarpdf');
    Route::post('/gerarexcel', [RelatorioUltimaMovimentacaoDespesas::class, 'gerarExcel'])->name('relatorioultimamovimentacaodespesas.gerarexcel');
});

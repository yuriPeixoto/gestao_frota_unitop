<?php

use App\Http\Controllers\Admin\DepartamentoController;
use App\Http\Controllers\Admin\FornecedorController;
use App\Http\Controllers\Admin\NotaFiscalController;
use App\Http\Controllers\Admin\NotaFiscalEntradaController;
use App\Http\Controllers\Admin\PessoalController;
use App\Http\Controllers\Admin\ProdutoController;
use App\Http\Controllers\Admin\ProdutosImobilizadosController;
use App\Http\Controllers\Admin\RelatorioAtendimentoCompra;
use App\Http\Controllers\Admin\RelatorioControleCompras;
use App\Http\Controllers\Admin\RelatorioDataEntrega;
use App\Http\Controllers\Admin\RelatorioGastosFilialeDepartamento;
use App\Http\Controllers\Admin\RelatorioNotasFiscais;
use App\Http\Controllers\Admin\RelatorioSolicitacao;
use App\Http\Controllers\Admin\RelatorioSolicitacaoCompra;
use App\Http\Controllers\Admin\ServicoController;
use App\Http\Controllers\Admin\SolicitacaoCompraController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'relatoriocontrolecompras'], function () {
    Route::get('/', [RelatorioControleCompras::class, 'index'])->name('relatoriocontrolecompras.index');
    Route::post('/gerarpdf', [RelatorioControleCompras::class, 'gerarPdf'])->name('relatoriocontrolecompras.gerarpdf');
    Route::post('/gerarexcel', [RelatorioControleCompras::class, 'gerarExcel'])->name('relatoriocontrolecompras.gerarexcel');
});

Route::group(['prefix' => 'relatoriodataentregapedidos'], function () {
    Route::get('/', [RelatorioDataEntrega::class, 'index'])->name('relatoriodataentregapedidos.index');
    Route::post('/gerarpdf', [RelatorioDataEntrega::class, 'gerarPdf'])->name('relatoriodataentregapedidos.gerarpdf');
    Route::post('/gerarexcel', [RelatorioDataEntrega::class, 'gerarExcel'])->name('relatoriodataentregapedidos.gerarexcel');
});

Route::group(['prefix' => 'relatoriogastosfilialedepartamento'], function () {
    Route::get('/', [RelatorioGastosFilialeDepartamento::class, 'index'])->name('relatoriogastosfilialedepartamento.index');
    Route::post('/gerarpdf', [RelatorioGastosFilialeDepartamento::class, 'gerarPdf'])->name('relatoriogastosfilialedepartamento.gerarpdf');
    Route::post('/gerarexcel', [RelatorioGastosFilialeDepartamento::class, 'gerarExcel'])->name('relatoriogastosfilialedepartamento.gerarexcel');
});

Route::group(['prefix' => 'relatoriosolicitacaocompra'], function () {
    Route::get('/', [RelatorioSolicitacaoCompra::class, 'index'])->name('relatoriosolicitacaocompra.index');
    Route::post('/gerarpdf', [RelatorioSolicitacaoCompra::class, 'gerarPdf'])->name('relatoriosolicitacaocompra.gerarpdf');
    Route::post('/gerarexcel', [RelatorioSolicitacaoCompra::class, 'gerarExcel'])->name('relatoriosolicitacaocompra.gerarexcel');
});

Route::group(['prefix' => 'relatorioentradanotasfiscais'], function () {
    Route::get('/', [RelatorioNotasFiscais::class, 'index'])->name('relatorioentradanotasfiscais.index');
    Route::post('/gerarpdf', [RelatorioNotasFiscais::class, 'gerarPdf'])->name('relatorioentradanotasfiscais.gerarpdf');
    Route::post('/gerarexcel', [RelatorioNotasFiscais::class, 'gerarExcel'])->name('relatorioentradanotasfiscais.gerarexcel');
    Route::get('/notasfiscais/search', [NotaFiscalController::class, 'search'])->name('notasfiscais.search');
    Route::get('/notasfiscais/single/{id}', [NotaFiscalController::class, 'getById'])->name('notasfiscais.single');
});

Route::group(['prefix' => 'relatoriosolicitacao'], function () {
    Route::get('/', [RelatorioSolicitacao::class, 'index'])->name('relatoriosolicitacao.index');
    Route::post('/gerarpdf', [RelatorioSolicitacao::class, 'gerarPdf'])->name('relatoriosolicitacao.gerarpdf');
    Route::post('/gerarexcel', [RelatorioSolicitacao::class, 'gerarExcel'])->name('relatoriosolicitacao.gerarexcel');
});

Route::group(['prefix' => 'relatorioatendimentocompra'], function () {
    Route::get('/', [RelatorioAtendimentoCompra::class, 'index'])->name('relatorioatendimentocompra.index');
    Route::post('/gerarpdf', [RelatorioAtendimentoCompra::class, 'gerarPdf'])->name('relatorioatendimentocompra.gerarpdf');
    Route::post('/gerarexcel', [RelatorioAtendimentoCompra::class, 'gerarExcel'])->name('relatorioatendimentocompra.gerarexcel');
});

Route::prefix('api')->group(function () {
    // Fornecedor
    Route::get('/fornecedor/search', [FornecedorController::class, 'search'])->name('api.fornecedor.search');
    Route::get('/fornecedor/single/{id}', [FornecedorController::class, 'getById'])->name('api.fornecedor.single');

    Route::get('/servico/search', [ServicoController::class, 'search'])->name('api.servico.search');
    Route::get('/servico/single/{id}', [ServicoController::class, 'getById'])->name('api.servico.single');

    Route::get('/produto/search', [ProdutoController::class, 'search'])->name('api.produto.search');
    Route::get('/produto/single/{id}', [ProdutoController::class, 'getById'])->name('api.produto.single');

    Route::get('/notasfiscais/search', [NotaFiscalEntradaController::class, 'search'])->name('api.notasfiscais.search');
    Route::get('/notasfiscais/single/{id}', [NotaFiscalEntradaController::class, 'getById'])->name('api.notasfiscais.single');

    // Busca de solicitações
    Route::get('/solicitacoes/search', [SolicitacaoCompraController::class, 'buscar'])
        ->name('api.solicitacoes.search');

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
});

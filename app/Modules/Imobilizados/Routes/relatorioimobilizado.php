<?php

use App\Modules\Imobilizados\Controllers\Admin\DepartamentoController;
use App\Modules\Imobilizados\Controllers\Admin\FornecedorController;
use App\Modules\Imobilizados\Controllers\Admin\PessoalController;
use App\Modules\Imobilizados\Controllers\Admin\ProdutoController;
use App\Modules\Imobilizados\Controllers\Admin\ProdutosImobilizadosController;
use App\Modules\Imobilizados\Controllers\Admin\RelatorioHistoricoImobilizado;
use App\Modules\Imobilizados\Controllers\Admin\RelatorioOrigemBaixasPecas;
use App\Modules\Imobilizados\Controllers\Admin\RelatorioProdutoImobilizado;
use App\Modules\Imobilizados\Controllers\Admin\ServicoController;
use App\Modules\Imobilizados\Controllers\Admin\SolicitacaoCompraController;
use App\Modules\Imobilizados\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'relatorioprodutoimobilizado'], function () {
    Route::get('/', [RelatorioProdutoImobilizado::class, 'index'])->name('relatorioprodutoimobilizado.index');
    Route::post('/gerarpdf', [RelatorioProdutoImobilizado::class, 'gerarPdf'])->name('relatorioprodutoimobilizado.gerarpdf');
    Route::post('/gerarexcel', [RelatorioProdutoImobilizado::class, 'gerarExcel'])->name('relatorioprodutoimobilizado.gerarexcel');
});

Route::group(['prefix' => 'relatorioorigembaixas'], function () {
    Route::get('/', [RelatorioOrigemBaixasPecas::class, 'index'])->name('relatorioorigembaixas.index');
    Route::post('/gerarpdf', [RelatorioOrigemBaixasPecas::class, 'gerarPdf'])->name('relatorioorigembaixas.gerarpdf');
    Route::post('/gerarexcel', [RelatorioOrigemBaixasPecas::class, 'gerarExcel'])->name('relatorioorigembaixas.gerarexcel');
});

Route::group(['prefix' => 'relatoriohistoricoimobilizado'], function () {
    Route::get('/', [RelatorioHistoricoImobilizado::class, 'index'])->name('relatoriohistoricoimobilizado.index');
    Route::post('/gerarpdf', [RelatorioHistoricoImobilizado::class, 'gerarPdf'])->name('relatoriohistoricoimobilizado.gerarpdf');
    Route::post('/gerarexcel', [RelatorioHistoricoImobilizado::class, 'gerarExcel'])->name('relatoriohistoricoimobilizado.gerarexcel');
});

Route::prefix('api')->group(function () {
    // Fornecedor
    Route::get('/fornecedor/search', [FornecedorController::class, 'search'])->name('api.fornecedor.search');
    Route::get('/fornecedor/single/{id}', [FornecedorController::class, 'getById'])->name('api.fornecedor.single');

    Route::get('/servico/search', [ServicoController::class, 'search'])->name('api.servico.search');
    Route::get('/servico/single/{id}', [ServicoController::class, 'getById'])->name('api.servico.single');

    // Route::get('/produto/search', [ProdutoController::class, 'search'])->name('api.produto.search');
    // Route::get('/produto/single/{id}', [ProdutoController::class, 'getById'])->name('api.produto.single');

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

    Route::get('/produto/search', [ProdutoController::class, 'search'])->name('api.produto.search');
    Route::get('/produto/single/{id}', [ProdutoController::class, 'getById'])->name('api.produto.single');
    // Pessoal
    Route::get('/pessoal/search', [PessoalController::class, 'search'])->name('api.pessoal.search');
    Route::get('/pessoal/single/{id}', [PessoalController::class, 'getById'])->name('api.pessoal.single');
});

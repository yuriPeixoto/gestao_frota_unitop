<?php

use App\Http\Controllers\Admin\ConsultaProdutosTransferencia;
use App\Http\Controllers\Admin\DepartamentoController;
use App\Http\Controllers\Admin\FornecedorController;
use App\Http\Controllers\Admin\PessoalController;
use App\Http\Controllers\Admin\ProdutoController;
use App\Http\Controllers\Admin\ProdutosImobilizadosController;
use App\Http\Controllers\Admin\RelacaoSolicitacaoPecaController;
use App\Modules\Estoque\Controllers\Relatorios\RelatorioBaixaEstoque;
use App\Http\Controllers\Admin\RelatorioCheckListFornecedor;
use App\Http\Controllers\Admin\RelatorioConferenciaRotativo;
use App\Modules\Estoque\Controllers\Relatorios\RelatorioEstoqueMaxMin;
use App\Modules\Estoque\Controllers\Relatorios\RelatorioFichaControleEstoque;
use App\Http\Controllers\Admin\RelatorioHistoricoTransferencia;
use App\Modules\Estoque\Controllers\Relatorios\RelatorioIndiceCoberturaEstoque;
use App\Modules\Estoque\Controllers\Relatorios\RelatorioProdutoemEstoque;
use App\Http\Controllers\Admin\RelatorioProdutosCadastrados;
use App\Http\Controllers\Admin\RelatorioSaidaDepartamento;
use App\Http\Controllers\Admin\ServicoController;
use App\Http\Controllers\Admin\SolicitacaoCompraController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'consultaprodutostransferencia'], function () {
    Route::get('/', [ConsultaProdutosTransferencia::class, 'index'])->name('consultaprodutostransferencia.index');
    Route::post('/gerarpdf', [ConsultaProdutosTransferencia::class, 'gerarPdf'])->name('consultaprodutostransferencia.gerarpdf');
    Route::post('/gerarexcel', [ConsultaProdutosTransferencia::class, 'gerarExcel'])->name('consultaprodutostransferencia.gerarexcel');
});
Route::group(['prefix' => 'relatoriofichacontroleestoque'], function () {
    Route::get('/', [RelatorioFichaControleEstoque::class, 'index'])->name('relatoriofichacontroleestoque.index');
    Route::post('/gerarpdf', [RelatorioFichaControleEstoque::class, 'gerarPdf'])->name('relatoriofichacontroleestoque.gerarpdf');
    Route::post('/gerarexcel', [RelatorioFichaControleEstoque::class, 'gerarExcel'])->name('relatoriofichacontroleestoque.gerarexcel');
});

Route::group(['prefix' => 'relatorioindicecoberturaestoque'], function () {
    Route::get('/', [RelatorioIndiceCoberturaEstoque::class, 'index'])->name('relatorioindicecoberturaestoque.index');
    Route::post('/gerarpdf', [RelatorioIndiceCoberturaEstoque::class, 'gerarPdf'])->name('relatorioindicecoberturaestoque.gerarpdf');
    Route::post('/gerarexcel', [RelatorioIndiceCoberturaEstoque::class, 'gerarExcel'])->name('relatorioindicecoberturaestoque.gerarexcel');
});

Route::group(['prefix' => 'relatoriosaidadepartamento'], function () {
    Route::get('/', [RelatorioSaidaDepartamento::class, 'index'])->name('relatoriosaidadepartamento.index');
    Route::post('/gerarpdf', [RelatorioSaidaDepartamento::class, 'gerarPdf'])->name('relatoriosaidadepartamento.gerarpdf');
    Route::post('/gerarexcel', [RelatorioSaidaDepartamento::class, 'gerarExcel'])->name('relatoriosaidadepartamento.gerarexcel');
});

Route::group(['prefix' => 'relatorioconferenciarotativo'], function () {
    Route::get('/', [RelatorioConferenciaRotativo::class, 'index'])->name('relatorioconferenciarotativo.index');
    Route::get('/exportPdf', [RelatorioConferenciaRotativo::class, 'exportPdf'])->name('relatorioconferenciarotativo.exportPdf');
    Route::get('/exportXls', [RelatorioConferenciaRotativo::class, 'exportXls'])->name('relatorioconferenciarotativo.exportXls');
});

Route::group(['prefix' => 'relatorioprodutoscadastrados'], function () {
    Route::get('/', [RelatorioProdutosCadastrados::class, 'index'])->name('relatorioprodutoscadastrados.index');
    Route::post('/gerarpdf', [RelatorioProdutosCadastrados::class, 'gerarpdf'])->name('relatorioprodutoscadastrados.gerarpdf');
    Route::post('/gerarexcel', [RelatorioProdutosCadastrados::class, 'gerarexcel'])->name('relatorioprodutoscadastrados.gerarexcel');
});

Route::group(['prefix' => 'relatoriochecklistfornecedor'], function () {
    Route::get('/', [RelatorioCheckListFornecedor::class, 'index'])->name('relatoriochecklistfornecedor.index');
    Route::post('/gerarpdf', [RelatorioCheckListFornecedor::class, 'gerarPdf'])->name('relatoriochecklistfornecedor.gerarpdf');
    Route::post('/gerarexcel', [RelatorioCheckListFornecedor::class, 'gerarExcel'])->name('relatoriochecklistfornecedor.gerarexcel');
});

Route::group(['prefix' => 'relatoriobaixaestoque'], function () {
    Route::get('/', [RelatorioBaixaEstoque::class, 'index'])->name('relatoriobaixaestoque.index');
    Route::post('/gerarpdf', [RelatorioBaixaEstoque::class, 'gerarPdf'])->name('relatoriobaixaestoque.gerarpdf');
    Route::post('/gerarexcel', [RelatorioBaixaEstoque::class, 'gerarExcel'])->name('relatoriobaixaestoque.gerarexcel');
});
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
Route::group(['prefix' => 'relatorioprodutoemestoque'], function () {
    Route::get('/', [RelatorioProdutoemEstoque::class, 'index'])->name('relatorioprodutoemestoque.index');
    Route::post('/gerarpdf', [RelatorioProdutoemEstoque::class, 'gerarPdf'])->name('relatorioprodutoemestoque.gerarpdf');
    Route::post('/gerarexcel', [RelatorioProdutoemEstoque::class, 'gerarExcel'])->name('relatorioprodutoemestoque.gerarexcel');
});
Route::group(['prefix' => 'relatoriohistoricotransferencia'], function () {
    Route::get('/', [RelatorioHistoricoTransferencia::class, 'index'])->name('relatoriohistoricotransferencia.index');
    Route::post('/gerarpdf', [RelatorioHistoricoTransferencia::class, 'gerarPdf'])->name('relatoriohistoricotransferencia.gerarpdf');
    Route::post('/gerarexcel', [RelatorioHistoricoTransferencia::class, 'gerarExcel'])->name('relatoriohistoricotransferencia.gerarexcel');
});
Route::group(['prefix' => 'relatoriomaximoeminimo'], function () {
    Route::get('/', [RelatorioEstoqueMaxMin::class, 'index'])->name('relatoriomaximoeminimo.index');
    Route::post('/gerarpdf', [RelatorioEstoqueMaxMin::class, 'gerarPdf'])->name('relatoriomaximoeminimo.gerarpdf');
    Route::post('/gerarexcel', [RelatorioEstoqueMaxMin::class, 'gerarExcel'])->name('relatoriomaximoeminimo.gerarexcel');
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
    //Produto
    Route::get('/produto/search', [ProdutoController::class, 'search'])->name('api.produto.search');
    Route::get('/produto/single/{id}', [ProdutoController::class, 'getById'])->name('api.produto.single');
    // Pessoal
    Route::get('/pessoal/search', [PessoalController::class, 'search'])->name('api.pessoal.search');
    Route::get('/pessoal/single/{id}', [PessoalController::class, 'getById'])->name('api.pessoal.single');
    // Solicitacao
    Route::get('/solicitacaopecas/search', [RelacaoSolicitacaoPecaController::class, 'search'])->name('api.solicitacaopecas.search');
    Route::get('/solicitacaopecas/single/{id}', [RelacaoSolicitacaoPecaController::class, 'getById'])->name('api.solicitacaopecas.single');
});

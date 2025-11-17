<?php

use App\Http\Controllers\Admin\BaseVeiculoController;
use App\Http\Controllers\Admin\DepartamentoController;
use App\Http\Controllers\Admin\FornecedorController;
use App\Http\Controllers\Admin\ModeloPneuController;
use App\Http\Controllers\Admin\PessoalController;
use App\Http\Controllers\Admin\PneuController;
use App\Http\Controllers\Admin\ProdutosImobilizadosController;
use App\Modules\Pneus\Controllers\Relatorios\RelatorioCalibracao;
use App\Modules\Estoque\Controllers\Relatorios\RelatorioControleeMovimentacaoEstoqueDosPneus;
use App\Modules\Pneus\Controllers\Relatorios\RelatorioEntradaManutencaoPneus;
use App\Modules\Pneus\Controllers\Relatorios\RelatorioHistoricoMovimentacaoPneu;
use App\Modules\Pneus\Controllers\Relatorios\RelatorioInventarioPneusAplicados;
use App\Modules\Pneus\Controllers\Relatorios\RelatorioListagemPneusDescartados;
use App\Modules\Pneus\Controllers\Relatorios\RelatorioListagemPneusManutencao;
use App\Modules\Pneus\Controllers\Relatorios\RelatorioPneusAplicado;
use App\Modules\Estoque\Controllers\Relatorios\RelatorioPneusEmEstoque;
use App\Modules\Pneus\Controllers\Relatorios\RelatorioPneusNaoAplicado;
use App\Modules\Pneus\Controllers\Relatorios\RelatorioPneusPorStatus;
use App\Modules\Pneus\Controllers\Relatorios\RelatorioQuantidadePneusPorFilial;
use App\Modules\Pneus\Controllers\Relatorios\RelatorioRequisicaoPneusFinalizadas;
use App\Modules\Pneus\Controllers\Relatorios\RelatorioRodizioPneus;
use App\Modules\Pneus\Controllers\Relatorios\RelatorioVendaPneus;
use App\Http\Controllers\Admin\RequisicaoPneusVendasController;
use App\Http\Controllers\Admin\UserController;
use App\Models\BaseVeiculo;
use Illuminate\Support\Facades\Route;



Route::group(['prefix' => 'relatorioquantidadepneusporfilial'], function () {
    Route::get('/', [RelatorioQuantidadePneusPorFilial::class, 'index'])->name('relatorioquantidadepneusporfilial.index');
    Route::post('/gerarpdf', [RelatorioQuantidadePneusPorFilial::class, 'gerarPdf'])->name('relatorioquantidadepneusporfilial.gerarpdf');
    Route::post('/gerarexcel', [RelatorioQuantidadePneusPorFilial::class, 'gerarExcel'])->name('relatorioquantidadepneusporfilial.gerarexcel');
});

Route::group(['prefix' => 'relatoriocontroleemovimentacaodeestoquedospneus'], function () {
    Route::get('/', [RelatorioControleeMovimentacaoEstoqueDosPneus::class, 'index'])->name('relatoriocontroleemovimentacaodeestoquedospneus.index');
    Route::post('/gerarpdf', [RelatorioControleeMovimentacaoEstoqueDosPneus::class, 'gerarPdf'])->name('relatoriocontroleemovimentacaodeestoquedospneus.gerarpdf');
    Route::post('/gerarexcel', [RelatorioControleeMovimentacaoEstoqueDosPneus::class, 'gerarExcel'])->name('relatoriocontroleemovimentacaodeestoquedospneus.gerarexcel');
});

Route::group(['prefix' => 'relatorioentradadepneumanutencao'], function () {
    Route::get('/', [RelatorioEntradaManutencaoPneus::class, 'index'])->name('relatorioentradadepneumanutencao.index');
    Route::post('/gerarpdf', [RelatorioEntradaManutencaoPneus::class, 'gerarPdf'])->name('relatorioentradadepneumanutencao.gerarpdf');
    Route::post('/gerarexcel', [RelatorioEntradaManutencaoPneus::class, 'gerarExcel'])->name('relatorioentradadepneumanutencao.gerarexcel');
});

Route::group(['prefix' => 'relatoriolistagempneusdescartados'], function () {
    Route::get('/', [RelatorioListagemPneusDescartados::class, 'index'])->name('relatoriolistagempneusdescartados.index');
    Route::post('/gerarpdf', [RelatorioListagemPneusDescartados::class, 'gerarPdf'])->name('relatoriolistagempneusdescartados.gerarpdf');
    Route::post('/gerarexcel', [RelatorioListagemPneusDescartados::class, 'gerarExcel'])->name('relatoriolistagempneusdescartados.gerarexcel');
});

Route::group(['prefix' => 'relatoriolistagempneusmanutencao'], function () {
    Route::get('/', [RelatorioListagemPneusManutencao::class, 'index'])->name('relatoriolistagempneusmanutencao.index');
    Route::post('/gerarpdf', [RelatorioListagemPneusManutencao::class, 'gerarPdf'])->name('relatoriolistagempneusmanutencao.gerarpdf');
    Route::post('/gerarexcel', [RelatorioListagemPneusManutencao::class, 'gerarExcel'])->name('relatoriolistagempneusmanutencao.gerarexcel');
});

Route::group(['prefix' => 'relatoriopneusnaoaplicado'], function () {
    Route::get('/', [RelatorioPneusNaoAplicado::class, 'index'])->name('relatoriopneusnaoaplicado.index');
    Route::post('/gerarpdf', [RelatorioPneusNaoAplicado::class, 'gerarPdf'])->name('relatoriopneusnaoaplicado.gerarpdf');
    Route::post('/gerarexcel', [RelatorioPneusNaoAplicado::class, 'gerarExcel'])->name('relatoriopneusnaoaplicado.gerarexcel');
});

Route::group(['prefix' => 'relatoriopneusaplicado'], function () {
    Route::get('/', [RelatorioPneusAplicado::class, 'index'])->name('relatoriopneusaplicado.index');
    Route::post('/gerarpdf', [RelatorioPneusAplicado::class, 'gerarPdf'])->name('relatoriopneusaplicado.gerarpdf');
    Route::post('/gerarexcel', [RelatorioPneusAplicado::class, 'gerarExcel'])->name('relatoriopneusaplicado.gerarexcel');
});

Route::group(['prefix' => 'relatoriopneusstatus'], function () {
    Route::get('/', [RelatorioPneusPorStatus::class, 'index'])->name('relatoriopneusstatus.index');
    Route::post('/gerarpdf', [RelatorioPneusPorStatus::class, 'gerarPdf'])->name('relatoriopneusstatus.gerarpdf');
    Route::post('/gerarexcel', [RelatorioPneusPorStatus::class, 'gerarExcel'])->name('relatoriopneusstatus.gerarexcel');
});

Route::group(['prefix' => 'relatoriopneusestoque'], function () {
    Route::get('/', [RelatorioPneusEmEstoque::class, 'index'])->name('relatoriopneusestoque.index');
    Route::post('/gerarpdf', [RelatorioPneusEmEstoque::class, 'gerarPdf'])->name('relatoriopneusestoque.gerarpdf');
    Route::post('/gerarexcel', [RelatorioPneusEmEstoque::class, 'gerarExcel'])->name('relatoriopneusestoque.gerarexcel');
});

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Route::group(['prefix' => 'relatoriocalibracao'], function () {
    Route::get('/', [RelatorioCalibracao::class, 'index'])->name('relatoriocalibracao.index');
    Route::post('/gerarpdf', [RelatorioCalibracao::class, 'gerarPdf'])->name('relatoriocalibracao.gerarpdf');
    Route::post('/gerarexcel', [RelatorioCalibracao::class, 'gerarExcel'])->name('relatoriocalibracao.gerarexcel');
});

Route::group(['prefix' => 'relatoriodehistoricomovimentacaopneus'], function () {
    Route::get('/', [RelatorioHistoricoMovimentacaoPneu::class, 'index'])->name('relatoriodehistoricomovimentacaopneus.index');
    Route::post('/gerarpdf', [RelatorioHistoricoMovimentacaoPneu::class, 'gerarPdf'])->name('relatoriodehistoricomovimentacaopneus.gerarpdf');
    Route::post('/gerarexcel', [RelatorioHistoricoMovimentacaoPneu::class, 'gerarExcel'])->name('relatoriodehistoricomovimentacaopneus.gerarexcel');
});

Route::group(['prefix' => 'relatoriorequisicaopneusfinalizadas'], function () {
    Route::get('/', [RelatorioRequisicaoPneusFinalizadas::class, 'index'])->name('relatoriorequisicaopneusfinalizadas.index');
    Route::post('/gerarpdf', [RelatorioRequisicaoPneusFinalizadas::class, 'gerarPdf'])->name('relatoriorequisicaopneusfinalizadas.gerarpdf');
    Route::post('/gerarexcel', [RelatorioRequisicaoPneusFinalizadas::class, 'gerarExcel'])->name('relatoriorequisicaopneusfinalizadas.gerarexcel');
});

Route::group(['prefix' => 'relatoriovendapneus'], function () {
    Route::get('/', [RelatorioVendaPneus::class, 'index'])->name('relatoriovendapneus.index');
    Route::post('/gerarpdf', [RelatorioVendaPneus::class, 'gerarPdf'])->name('relatoriovendapneus.gerarpdf');
    Route::post('/gerarexcel', [RelatorioVendaPneus::class, 'gerarExcel'])->name('relatoriovendapneus.gerarexcel');
});

Route::group(['prefix' => 'relatoriorodiziopneus'], function () {
    Route::get('/', [RelatorioRodizioPneus::class, 'index'])->name('relatoriorodiziopneus.index');
    Route::post('/gerarpdf', [RelatorioRodizioPneus::class, 'gerarPdf'])->name('relatoriorodiziopneus.gerarpdf');
    Route::post('/gerarexcel', [RelatorioRodizioPneus::class, 'gerarExcel'])->name('relatoriorodiziopneus.gerarexcel');
});

Route::group(['prefix' => 'relatorioinventariopneusaplicados'], function () {
    Route::get('/', [RelatorioInventarioPneusAplicados::class, 'index'])->name('relatorioinventariopneusaplicados.index');
    Route::post('/gerarpdf', [RelatorioInventarioPneusAplicados::class, 'gerarPdf'])->name('relatorioinventariopneusaplicados.gerarpdf');
    Route::post('/gerarexcel', [RelatorioInventarioPneusAplicados::class, 'gerarExcel'])->name('relatorioinventariopneusaplicados.gerarexcel');
});

Route::prefix('api')->group(function () {
    // Fornecedor
    Route::get('/fornecedor/search', [FornecedorController::class, 'search'])->name('api.fornecedor.search');
    Route::get('/fornecedor/single/{id}', [FornecedorController::class, 'getById'])->name('api.fornecedor.single');

    // Base
    Route::get('/baseveiculo/search', [BaseVeiculoController::class, 'search'])->name('api.baseveiculo.search');
    Route::get('/baseveiculo/single/{id}', [BaseVeiculoController::class, 'getById'])->name('api.baseveiculo.single');
    // Usuario
    Route::get('/users/search', [UserController::class, 'search'])->name('api.users.search');
    Route::get('/users/single/{id}', [UserController::class, 'getById'])->name('api.users.single');

    // Usuario
    Route::get('/requisicao/search', [RequisicaoPneusVendasController::class, 'search'])->name('api.requisicao.search');
    Route::get('/requisicao/single/{id}', [RequisicaoPneusVendasController::class, 'getById'])->name('api.requisicao.single');

    // Departamento
    Route::get('/departamento/search', [DepartamentoController::class, 'search'])->name('api.departamento.search');
    Route::get('/departamento/single/{id}', [DepartamentoController::class, 'getById'])->name('api.departamento.single');
    // Produtos Imobilizados
    Route::get('/produtosimobilizados/search', [ProdutosImobilizadosController::class, 'search'])->name('api.produtosimobilizados.search');
    Route::get('/produtosimobilizados/single/{id}', [ProdutosImobilizadosController::class, 'getById'])->name('api.produtosimobilizados.single');
    // Pessoal
    Route::get('/pessoal/search', [PessoalController::class, 'search'])->name('api.pessoal.search');
    Route::get('/pessoal/single/{id}', [PessoalController::class, 'getById'])->name('api.pessoal.single');
    // Modelo Pneu
    Route::get('/modelopneu/search', [ModeloPneuController::class, 'search'])->name('api.modelopneu.search');
    Route::get('/modelopneu/single/{id}', [ModeloPneuController::class, 'getById'])->name('api.modelopneu.single');
    // Pneu
    Route::get('/pneu/search', [PneuController::class, 'search'])->name('api.pneu.search');
    Route::get('/pneu/single/{id}', [PneuController::class, 'getById'])->name('api.pneu.single');
});

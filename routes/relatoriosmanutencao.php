<?php

use App\Http\Controllers\Admin\FornecedorComissionadosRelController;
use App\Http\Controllers\Admin\FornecedorController;
use App\Http\Controllers\Admin\ServicoController;
use App\Http\Controllers\Admin\HistoricoMantVeiculoRelController;
use App\Http\Controllers\Admin\ManutencaoController;
use App\Http\Controllers\Admin\MotoristaController;
use App\Http\Controllers\Admin\NotaFiscalController;
use App\Http\Controllers\Admin\OrdemServicoController;
use App\Http\Controllers\Admin\OrdemServicoServicosController;
use App\Http\Controllers\Admin\ProdutoController;
use App\Http\Controllers\Admin\RelatorioChecklistController;
use App\Http\Controllers\Admin\RelatorioGeralChecklistController;
use App\Http\Controllers\Admin\RelatorioManutencaoDetalhadasController;
use App\Http\Controllers\Admin\RelatorioManutencaoVencidasController;
use App\Http\Controllers\Admin\RelatorioNotaFiscalExternaController;
use App\Http\Controllers\Admin\RelatorioOrdemServicoStatusController;
use App\Http\Controllers\Admin\RelatorioPeçasUtilizadasOsController;
use App\Http\Controllers\Admin\RelatorioServicosUtilizadasOsController;
use App\Http\Controllers\Admin\RelatorioSinteticoNfOsController;
use App\Http\Controllers\Admin\VeiculoController;
use App\Models\StatusOrdemServico;
use Illuminate\Support\Facades\Route;


// Fornecedores Comissionados
Route::group(['prefix' => 'fornecedorescomissionadosrelatorio'], function () {
    Route::get('/', [FornecedorComissionadosRelController::class, 'index'])->name('fornecedorescomissionadosrelatorio.index');
    Route::post('/gerarpdf', [FornecedorComissionadosRelController::class, 'gerarPdf'])->name('fornecedorcomissionadosrel.gerarpdf');
    Route::post('/gerarexcel', [FornecedorComissionadosRelController::class, 'gerarExcel'])->name('fornecedorcomissionadosrel.gerarexcel');
});



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Historico manutenção Veiculo
Route::group(['prefix' => 'historicomanutencaoveiculo'], function () {
    Route::get('/', [HistoricoMantVeiculoRelController::class, 'index'])->name('historicomanutencaoveiculo.index');
    Route::post('/gerarpdf', [HistoricoMantVeiculoRelController::class, 'gerarPdf'])->name('historicomanutencaoveiculo.gerarpdf');
    Route::post('/gerarexcel', [HistoricoMantVeiculoRelController::class, 'gerarExcel'])->name('historicomanutencaoveiculo.gerarexcel');
});

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Relatorio Tabela Checklist
Route::group(['prefix' => 'relatoriochecklist'], function () {
    Route::get('/', [RelatorioChecklistController::class, 'index'])->name('relatoriochecklist.index');
    Route::post('/gerarpdf', [RelatorioChecklistController::class, 'gerarPdf'])->name('relatoriochecklist.gerarpdf');
    Route::post('/gerarexcel', [RelatorioChecklistController::class, 'gerarExcel'])->name('relatoriochecklist.gerarexcel');
});

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Route::group(['prefix' => 'relatorionotafiscalexterna'], function () {
    Route::get('/', [RelatorioNotaFiscalExternaController::class, 'index'])->name('relatorionotafiscalexterna.index');
    Route::post('/gerarpdf', [RelatorioNotaFiscalExternaController::class, 'gerarPdf'])->name('relatorionotafiscalexterna.gerarpdf');
    Route::post('/gerarexcel', [RelatorioNotaFiscalExternaController::class, 'gerarExcel'])->name('relatorionotafiscalexterna.gerarexcel');
});

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Route::group(['prefix' => 'relatoriomanutencaodetalhada'], function () {
    Route::get('/', [RelatorioManutencaoDetalhadasController::class, 'index'])->name('relatoriomanutencaodetalhada.index');
    Route::post('/gerarpdf', [RelatorioManutencaoDetalhadasController::class, 'gerarPdf'])->name('relatoriomanutencaodetalhada.gerarpdf');
    Route::post('/gerarexcel', [RelatorioManutencaoDetalhadasController::class, 'gerarExcel'])->name('relatoriomanutencaodetalhada.gerarexcel');
});

Route::group(['prefix' => 'relatoriomanutencaovencidas'], function () {
    Route::get('/', [RelatorioManutencaoVencidasController::class, 'index'])->name('relatoriomanutencaovencidas.index');
    Route::post('/gerarpdf', [RelatorioManutencaoVencidasController::class, 'gerarPdf'])->name('relatoriomanutencaovencidas.gerarpdf');
    Route::post('/gerarexcel', [RelatorioManutencaoVencidasController::class, 'gerarExcel'])->name('relatoriomanutencaovencidas.gerarexcel');
});

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Route::group(['prefix' => 'relatoriosinteticonfos'], function () {
    Route::get('/', [RelatorioSinteticoNfOsController::class, 'index'])->name('relatoriosinteticonfos.index');
    Route::post('/gerarpdf', [RelatorioSinteticoNfOsController::class, 'gerarPdf'])->name('relatoriosinteticonfos.gerarpdf');
    Route::post('/gerarexcel', [RelatorioSinteticoNfOsController::class, 'gerarExcel'])->name('relatoriosinteticonfos.gerarexcel');
});

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Route::group(['prefix' => 'relatoriogeralchecklist'], function () {
    Route::get('/', [RelatorioGeralChecklistController::class, 'index'])->name('relatoriogeralchecklist.index');
    Route::post('/gerarpdf', [RelatorioGeralChecklistController::class, 'gerarPdf'])->name('relatoriogeralchecklist.gerarpdf');
    Route::post('/gerarexcel', [RelatorioGeralChecklistController::class, 'gerarExcel'])->name('relatoriogeralchecklist.gerarexcel');
});

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Route::group(['prefix' => 'relatorioordemservicostatus'], function () {
    Route::get('/', [RelatorioOrdemServicoStatusController::class, 'index'])->name('relatorioordemservicostatus.index');
    Route::post('/gerarpdf', [RelatorioOrdemServicoStatusController::class, 'gerarPdf'])->name('relatorioordemservicostatus.gerarpdf');
    Route::post('/gerarexcel', [RelatorioOrdemServicoStatusController::class, 'gerarExcel'])->name('relatorioordemservicostatus.gerarexcel');
});

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Route::group(['prefix' => 'relatoriopecasutilizadasos'], function () {
    Route::get('/', [RelatorioPeçasUtilizadasOsController::class, 'index'])->name('relatoriopecasutilizadasos.index');
    Route::post('/gerarpdf', [RelatorioPeçasUtilizadasOsController::class, 'gerarPdf'])->name('relatoriopecasutilizadasos.gerarpdf');
    Route::post('/gerarexcel', [RelatorioPeçasUtilizadasOsController::class, 'gerarExcel'])->name('relatoriopecasutilizadasos.gerarexcel');
});

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Route::group(['prefix' => 'relatorioservicosutilizadasos'], function () {
    Route::get('/', [RelatorioServicosUtilizadasOsController::class, 'index'])->name('relatorioservicosutilizadasos.index');
    Route::post('/gerarpdf', [RelatorioServicosUtilizadasOsController::class, 'gerarPdf'])->name('relatorioservicosutilizadasos.gerarpdf');
    Route::post('/gerarexcel', [RelatorioServicosUtilizadasOsController::class, 'gerarExcel'])->name('relatorioservicosutilizadasos.gerarexcel');
});


Route::prefix('api')->group(function () {
    // Servico
    Route::get('/servico/search', [ServicoController::class, 'search'])->name('api.servico.search');
    Route::get('/servico/single/{id}', [ServicoController::class, 'getById'])->name('api.servico.single');

    // // Servico
    // Route::get('/servico/search', [ServicoController::class, 'search'])->name('api.servico.search');
    // Route::get('/servico/single/{id}', [ServicoController::class, 'getById'])->name('api.servico.single');

    // Fornecedor
    Route::get('/fornecedor/search', [FornecedorController::class, 'search'])->name('api.fornecedor.search');
    Route::get('/fornecedor/single/{id}', [FornecedorController::class, 'getById'])->name('api.fornecedor.single');

    // veiculo
    Route::get('/veiculo/search', [VeiculoController::class, 'search'])->name('api.veiculo.search');
    Route::get('/veiculo/single/{id}', [VeiculoController::class, 'getById'])->name('api.veiculo.single');

    //OrdemServico
    Route::get('/ordemservico/search', [OrdemServicoController::class, 'search'])->name('api.ordemservico.search');
    Route::get('/ordemservico/single/{id}', [OrdemServicoController::class, 'getById'])->name('api.ordemservico.single');

    //NotaFiscal 
    Route::get('/notasfiscais/search', [NotaFiscalController::class, 'search'])->name('notasfiscais.search');
    Route::get('/notasfiscais/single/{id}', [NotaFiscalController::class, 'getById'])->name('api.notasfiscais.single');

    //Motorista
    Route::get('/motorista/search', [MotoristaController::class, 'search'])->name('api.motorista.search');
    Route::get('/motorista/single/{id}', [MotoristaController::class, 'getById'])->name('api.motorista.single');

    //Produto
    Route::get('/produto/search', [ProdutoController::class, 'search'])->name('api.produto.search');
    Route::get('/produto/single/{id}', [ProdutoController::class, 'getById'])->name('api.produto.single');

    //Motorista
    // Route::get('/produto/search', [ProdutoController::class, 'search'])->name('api.produto.search');
    // Route::get('/produto/single/{id}', [ProdutoController::class, 'getById'])->name('api.produto.single');
});

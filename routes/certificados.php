<?php

use App\Http\Controllers\Admin\AutorizacoesEspTransitoController;
use App\Http\Controllers\Admin\TipoCertificadoController;
use App\Http\Controllers\Admin\CronotacografoController;
use App\Http\Controllers\Admin\TesteFrioController;
use App\Http\Controllers\Admin\TesteFumacaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\VeiculoController;

// Rotas de AutorizacoesEspTransitoController
Route::group(['prefix' => 'autorizacoesesptransitos'], function () {
    Route::get('/', [AutorizacoesEspTransitoController::class, 'index'])->name('autorizacoesesptransitos.index');
    Route::get('criar', [AutorizacoesEspTransitoController::class, 'create'])->name('autorizacoesesptransitos.create');

    // Exportação
    Route::get('/export-csv', [AutorizacoesEspTransitoController::class, 'exportCsv'])->name('autorizacoesesptransitos.exportCsv');
    Route::get('/export-xls', [AutorizacoesEspTransitoController::class, 'exportXls'])->name('autorizacoesesptransitos.exportXls');
    Route::get('/export-pdf', [AutorizacoesEspTransitoController::class, 'exportPdf'])->name('autorizacoesesptransitos.exportPdf');
    Route::get('/export-xml', [AutorizacoesEspTransitoController::class, 'exportXml'])->name('autorizacoesesptransitos.exportXml');

    Route::post('/pega-renavam-data', [AutorizacoesEspTransitoController::class, 'pegaRenavamData'])
        ->name('autorizacoesesptransitos.pega-renavam-data');

    Route::post('/', [AutorizacoesEspTransitoController::class, 'store'])->name('autorizacoesesptransitos.store');

    Route::get('{autorizacoesesptransitos}/editar', [AutorizacoesEspTransitoController::class, 'edit'])
        ->name('autorizacoesesptransitos.edit');

    Route::put('{autorizacoesesptransitos}', [AutorizacoesEspTransitoController::class, 'update'])
        ->name('autorizacoesesptransitos.update');

    Route::delete('{autorizacoesesptransitos}', [AutorizacoesEspTransitoController::class, 'destroy'])
        ->name('autorizacoesesptransitos.destroy');

    Route::put('/{id}/replicarUpdate', [AutorizacoesEspTransitoController::class, 'replicarUpdate'])
        ->name('autorizacoesesptransitos.replicarUpdate');

    Route::get('/{id}/replicar', [AutorizacoesEspTransitoController::class, 'replicar'])
        ->name('autorizacoesesptransitos.replicar');
});

// Rotas de TipoCertificadoController
Route::group(['prefix' => 'tipocertificados'], function () {
    Route::get('/', [TipoCertificadoController::class, 'index'])->name('tipocertificados.index');
    Route::get('criar', [TipoCertificadoController::class, 'create'])->name('tipocertificados.create');
    Route::get('{tipocertificados}', [TipoCertificadoController::class, 'show'])->name('tipocertificados.show');
    Route::post('/', [TipoCertificadoController::class, 'store'])->name('tipocertificados.store');
    Route::get('{tipocertificados}/editar', [TipoCertificadoController::class, 'edit'])
        ->name('tipocertificados.edit');
    Route::put('{tipocertificados}', [TipoCertificadoController::class, 'update'])->name('tipocertificados.update');

    Route::delete('{tipocertificados}', [TipoCertificadoController::class, 'destroy'])
        ->name('tipocertificados.destroy');
});

// Rotas de CronotacografoController
Route::group(['prefix' => 'cronotacografos'], function () {
    Route::get('/', [CronotacografoController::class, 'index'])
        ->name('cronotacografos.index');

    // Exportação
    Route::get('/export-csv', [CronotacografoController::class, 'exportCsv'])->name('cronotacografos.exportCsv');
    Route::get('/export-xls', [CronotacografoController::class, 'exportXls'])->name('cronotacografos.exportXls');
    Route::get('/export-pdf', [CronotacografoController::class, 'exportPdf'])->name('cronotacografos.exportPdf');
    Route::get('/export-xml', [CronotacografoController::class, 'exportXml'])->name('cronotacografos.exportXml');

    // CRUD
    Route::get('/create', [CronotacografoController::class, 'create'])
        ->name('cronotacografos.create');
    Route::post('/store', [CronotacografoController::class, 'store'])
        ->name('cronotacografos.store');
    Route::get('/{id}/edit', [CronotacografoController::class, 'edit'])
        ->name('cronotacografos.edit');
    Route::put('/{id}', [CronotacografoController::class, 'update'])
        ->name('cronotacografos.update');

    Route::delete('/{id}', [CronotacografoController::class, 'destroy'])
        ->name('cronotacografos.destroy');

    Route::put('/{id}/replicarUpdate', [CronotacografoController::class, 'replicarUpdate'])
        ->name('cronotacografos.replicarUpdate');

    Route::get('/{id}/replicar', [CronotacografoController::class, 'replicar'])
        ->name('cronotacografos.replicar');

    // Método para dados de veículo
    Route::get('/veiculo-dados/{id}', [CronotacografoController::class, 'getDadosVeiculo'])
        ->name('cronotacografos.veiculo-dados');
});

// API para busca e dados
Route::get('/api/veiculos/search', [VeiculoController::class, 'search'])
    ->name('api.veiculos.search');
Route::get('/api/veiculos/single/{id}', [VeiculoController::class, 'getById'])
    ->name('api.veiculos.single');
Route::get('/admin/api/veiculos/{id}/dados', [VeiculoController::class, 'getDados'])
    ->name('api.veiculos.dados');

// Rotas de Teste Frio
Route::group(['prefix' => 'testefrios'], function () {
    Route::get('/', [TesteFrioController::class, 'index'])->name('testefrios.index');
    Route::get('criar', [TesteFrioController::class, 'create'])->name('testefrios.create');

    Route::post('/', [TesteFrioController::class, 'store'])->name('testefrios.store');
    Route::get('{testefrios}/editar', [TesteFrioController::class, 'edit'])->name('testefrios.edit');
    Route::put('{testefrios}', [TesteFrioController::class, 'update'])->name('testefrios.update');


    Route::put('/{id}/replicarUpdate', [TesteFrioController::class, 'replicarUpdate'])
        ->name('testeFrioController.replicarUpdate');

    Route::get('/{id}/replicar', [TesteFrioController::class, 'replicar'])
        ->name('testeFrioController.replicar');

    // Exportação
    Route::get('/export-csv', [TesteFrioController::class, 'exportCsv'])->name('testefrios.exportCsv');
    Route::get('/export-xls', [TesteFrioController::class, 'exportXls'])->name('testefrios.exportXls');
    Route::get('/export-pdf', [TesteFrioController::class, 'exportPdf'])->name('testefrios.exportPdf');
    Route::get('/export-xml', [TesteFrioController::class, 'exportXml'])->name('testefrios.exportXml');


    Route::delete('{testefrios}', [TesteFrioController::class, 'destroy'])
        ->name('testefrios.destroy');
});

Route::get('/testefrios/getDadosVeiculo/{id}', [TesteFrioController::class, 'getDadosVeiculo'])
    ->name('testefrios.getDadosVeiculo');

// Rotas de Teste Fumaça
Route::group(['prefix' => 'testefumacas'], function () {
    Route::get('/', [TesteFumacaController::class, 'index'])->name('testefumacas.index');
    Route::get('criar', [TesteFumacaController::class, 'create'])->name('testefumacas.create');

    Route::get('{testefumacas}/getFilial', [TesteFumacaController::class, 'getFilial'])->name('testefumacas.getFilial');


    // Exportação
    Route::get('/export-csv', [TesteFumacaController::class, 'exportCsv'])->name('testefumacas.exportCsv');
    Route::get('/export-xls', [TesteFumacaController::class, 'exportXls'])->name('testefumacas.exportXls');
    Route::get('/export-pdf', [TesteFumacaController::class, 'exportPdf'])->name('testefumacas.exportPdf');
    Route::get('/export-xml', [TesteFumacaController::class, 'exportXml'])->name('testefumacas.exportXml');

    Route::post('{testefumacas}/replica', [TesteFumacaController::class, 'cloneCertificado'])->name('testefumacas.replica');
    Route::post('/', [TesteFumacaController::class, 'store'])->name('testefumacas.store');
    Route::get('{testefumacas}/editar', [TesteFumacaController::class, 'edit'])->name('testefumacas.edit');
    Route::put('{testefumacas}', [TesteFumacaController::class, 'update'])->name('testefumacas.update');

    Route::delete('{testefumacas}', [TesteFumacaController::class, 'destroy'])
        ->name('testefumacas.destroy');
});

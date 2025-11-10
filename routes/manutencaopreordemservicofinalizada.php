<?php

use App\Http\Controllers\Admin\MotoristaController;
use App\Http\Controllers\Admin\PreOrdemListagemFinalizadasController;
use App\Http\Controllers\Admin\VeiculoController;
use Illuminate\Support\Facades\Route;

// Manutencao servicos mecanico
Route::group(['prefix' => 'manutencaopreordemservicofinalizada'], function () {
    Route::get('/', [PreOrdemListagemFinalizadasController::class, 'index'])
        ->name('manutencaopreordemservicofinalizada.index');


    // Route::get('/create', [PreOrdemListagemFinalizadasController::class, 'create'])
    //     ->name('manutencaopreordemservicofinalizada.create');
    Route::get('/{id}/edit', [PreOrdemListagemFinalizadasController::class, 'edit'])
        ->name('manutencaopreordemservicofinalizada.edit');
    Route::put('/{id}', [PreOrdemListagemFinalizadasController::class, 'update'])
        ->name('manutencaopreordemservicofinalizada.update');

    // Rotas API
    // Route::get('/veiculos/search', [VeiculoController::class, 'search'])
    //     ->name('api.veiculos.search');
    Route::get('/veiculos/single/{id}', [VeiculoController::class, 'getById'])
        ->name('api.veiculos.single');

    Route::get('/motoristas/search', [MotoristaController::class, 'search'])
        ->name('api.motoristas.search');
    Route::get('/motoristas/single/{id}', [MotoristaController::class, 'getById'])
        ->name('api.motoristas.single');

    // Exportação
    Route::get('/export-csv', [PreOrdemListagemFinalizadasController::class, 'exportCsv'])
        ->name('manutencaopreordemservicofinalizada.exportCsv');
    Route::get('/export-xls', [PreOrdemListagemFinalizadasController::class, 'exportXls'])
        ->name('manutencaopreordemservicofinalizada.exportXls');
    Route::get('/export-pdf', [PreOrdemListagemFinalizadasController::class, 'exportPdf'])
        ->name('manutencaopreordemservicofinalizada.exportPdf');
    Route::get('/export-xml', [PreOrdemListagemFinalizadasController::class, 'exportXml'])
        ->name('manutencaopreordemservicofinalizada.exportXml');
});

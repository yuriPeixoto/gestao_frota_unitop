<?php

use App\Http\Controllers\Admin\IpvaVeiculoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\VeiculoController;

// Rotas de IpvaVeiculo
Route::group(['prefix' => 'ipvaveiculos', 'as' => 'ipvaveiculos.'], function () {
    Route::get('/', [IpvaVeiculoController::class, 'index'])->name('index');
    Route::get('criar', [IpvaVeiculoController::class, 'create'])->name('create');
    // Route::get('{ipvaveiculos}', [IpvaVeiculoController::class, 'show'])->name('show');

    // Rotas de exportação
    Route::get('/export-pdf', [IpvaVeiculoController::class, 'exportPdf'])->name('exportPdf');
    Route::get('/export-csv', [IpvaVeiculoController::class, 'exportCsv'])->name('exportCsv');
    Route::get('/export-xls', [IpvaVeiculoController::class, 'exportXls'])->name('exportXls');
    Route::get('/export-xml', [IpvaVeiculoController::class, 'exportXml'])->name('exportXml');

    // API
    Route::post('/get-renavam-data', [IpvaVeiculoController::class, 'getDadosRenavam'])->name('get-renavam-data');
    Route::post('/gerar-parcelas-ipva', [IpvaVeiculoController::class, 'gerarParcelasIPVA'])->name('gerar-parcelas-ipva');

    Route::post('/', [IpvaVeiculoController::class, 'store'])->name('store');
    Route::get('{ipvaveiculos}/editar', [IpvaVeiculoController::class, 'edit'])->name('edit');
    Route::put('{ipvaveiculos}', [IpvaVeiculoController::class, 'update'])->name('update');

    Route::delete('{ipvaveiculos}', [IpvaVeiculoController::class, 'destroy'])
        ->name('destroy');
});

// Rotas API adicionais para o módulo
Route::prefix('api')->name('api.')->group(function () {
    // Busca de veículos para select
    Route::get('/veiculos/search', [VeiculoController::class, 'search'])
        ->name('veiculos.search');

    // Dados do veículo
    Route::get('/veiculos/{id}/dados-renavam', [VeiculoController::class, 'getDados'])
        ->name('veiculos.dados-renavam');
});

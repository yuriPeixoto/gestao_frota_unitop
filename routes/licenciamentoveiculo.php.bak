<?php

use App\Http\Controllers\Admin\LicenciamentoVeiculoController;
use Illuminate\Support\Facades\Route;

// Rotas de Licenciamento
Route::group(['prefix' => 'licenciamentoveiculos'], function () {
    // Exportações
    Route::get('/export-csv', [LicenciamentoVeiculoController::class, 'exportCsv'])->name('licenciamentoveiculos.exportCsv');
    Route::get('/export-xls', [LicenciamentoVeiculoController::class, 'exportXls'])->name('licenciamentoveiculos.exportXls');
    Route::get('/export-pdf', [LicenciamentoVeiculoController::class, 'exportPdf'])->name('licenciamentoveiculos.exportPdf');
    Route::get('/export-xml', [LicenciamentoVeiculoController::class, 'exportXml'])->name('licenciamentoveiculos.exportXml');

    // Listagem e visualização
    Route::get('/', [LicenciamentoVeiculoController::class, 'index'])->name('licenciamentoveiculos.index');
    Route::get('criar', [LicenciamentoVeiculoController::class, 'create'])->name('licenciamentoveiculos.create');
    Route::get('{licenciamentoveiculos}', [LicenciamentoVeiculoController::class, 'show'])->name('licenciamentoveiculos.show');


    // Criação
    Route::post('/', [LicenciamentoVeiculoController::class, 'store'])->name('licenciamentoveiculos.store');
    Route::post('{licenciamentoveiculos}/replica', [LicenciamentoVeiculoController::class, 'cloneLicenciamento'])->name('licenciamentoveiculos.replica');

    // Edição
    Route::get('{id}/edit', [LicenciamentoVeiculoController::class, 'edit'])->name('licenciamentoveiculos.edit');
    Route::put('{licenciamentoveiculos}', [LicenciamentoVeiculoController::class, 'update'])->name('licenciamentoveiculos.update');

    // Remoção
    Route::delete('{licenciamentoveiculos}', [LicenciamentoVeiculoController::class, 'destroy'])->name('licenciamentoveiculos.destroy');

    // Ações auxiliares
    Route::post('/get-vehicle-data', [LicenciamentoVeiculoController::class, 'getVehicleData'])->name('licenciamentoveiculos.getVehicleData');
});

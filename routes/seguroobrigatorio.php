<?php

use App\Http\Controllers\Admin\SeguroObrigatorioController;
use App\Http\Controllers\Admin\VeiculoController;
use Illuminate\Support\Facades\Route;

// Seguro Obrigatório
Route::group(['prefix' => 'seguroobrigatorio'], function () {
    Route::get('/', [SeguroObrigatorioController::class, 'index'])
        ->name('seguroobrigatorio.index');

    // Exportação
    Route::get('/export-csv', [SeguroObrigatorioController::class, 'exportCsv'])->name('seguroobrigatorio.exportCsv');
    Route::get('/export-xls', [SeguroObrigatorioController::class, 'exportXls'])->name('seguroobrigatorio.exportXls');
    Route::get('/export-pdf', [SeguroObrigatorioController::class, 'exportPdf'])->name('seguroobrigatorio.exportPdf');
    Route::get('/export-xml', [SeguroObrigatorioController::class, 'exportXml'])->name('seguroobrigatorio.exportXml');

    // CRUD
    Route::get('/create', [SeguroObrigatorioController::class, 'create'])
        ->name('seguroobrigatorio.create');
    Route::post('/store', [SeguroObrigatorioController::class, 'store'])
        ->name('seguroobrigatorio.store');
    Route::get('/{id}/edit', [SeguroObrigatorioController::class, 'edit'])
        ->name('seguroobrigatorio.edit');
    Route::put('/{id}', [SeguroObrigatorioController::class, 'update'])
        ->name('seguroobrigatorio.update');
    Route::delete('/{id}', [SeguroObrigatorioController::class, 'destroy'])
        ->name('seguroobrigatorio.destroy');
});

// API para o Seguro Obrigatório
Route::get('/api/veiculos/{id}/dados', [VeiculoController::class, 'getDados'])
    ->name('api.veiculos.dados');

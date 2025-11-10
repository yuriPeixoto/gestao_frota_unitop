<?php

use App\Http\Controllers\Admin\ManutencaoNotasFicaisRateioController;
use Illuminate\Support\Facades\Route;

// Lancamentos de os NF 
Route::group(['prefix' => 'listagemoslacamentoservicorateio'], function () {
    Route::get('/', [ManutencaoNotasFicaisRateioController::class, 'index'])
        ->name('listagemoslacamentoservicorateio.index');
    Route::get('/create', [ManutencaoNotasFicaisRateioController::class, 'create'])
        ->name('listagemoslacamentoservicorateio.create');
    Route::get('/{id}/edit', [ManutencaoNotasFicaisRateioController::class, 'edit'])
        ->name('listagemoslacamentoservicorateio.edit');
    Route::put('/{id}', [ManutencaoNotasFicaisRateioController::class, 'update'])
        ->name('listagemoslacamentoservicorateio.update');
    Route::post('/', [ManutencaoNotasFicaisRateioController::class, 'store'])
        ->name('listagemoslacamentoservicorateio.store');
    Route::delete('/{id}', [ManutencaoNotasFicaisRateioController::class, 'destroy'])
        ->name('listagemoslacamentoservicorateio.destroy');

});

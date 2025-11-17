<?php

use App\Http\Controllers\Admin\StatusOrdemServicoController;
use Illuminate\Support\Facades\Route;

// Lancamentos de os NF
Route::group(['prefix' => 'statusordemservico'], function () {
    Route::get('/', [StatusOrdemServicoController::class, 'index'])
        ->name('statusordemservico.index');
    Route::get('/create', [StatusOrdemServicoController::class, 'create'])
        ->name('statusordemservico.create');
    Route::get('/{id}/edit', [StatusOrdemServicoController::class, 'edit'])
        ->name('statusordemservico.edit');
    Route::put('/{id}', [StatusOrdemServicoController::class, 'update'])
        ->name('statusordemservico.update');
    Route::post('/', [StatusOrdemServicoController::class, 'store'])
        ->name('statusordemservico.store');
    Route::delete('/{id}', [StatusOrdemServicoController::class, 'destroy'])
        ->name('statusordemservico.destroy');
});

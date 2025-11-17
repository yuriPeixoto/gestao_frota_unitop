<?php

use App\Http\Controllers\Admin\ManutencaoServicoController;
use Illuminate\Support\Facades\Route;

// Manutencao x servico
Route::group(['prefix' => 'manutencaoservicos'], function () {
    Route::get('/', [ManutencaoServicoController::class, 'index'])
        ->name('manutencaoservicos.index');
    Route::get('/create', [ManutencaoServicoController::class, 'create'])
        ->name('manutencaoservicos.create');
    Route::get('/{id}/edit', [ManutencaoServicoController::class, 'edit'])
        ->name('manutencaoservicos.edit');
    Route::put('/{id}', [ManutencaoServicoController::class, 'update'])
        ->name('manutencaoservicos.update');
    Route::post('/', [ManutencaoServicoController::class, 'store'])
        ->name('manutencaoservicos.store');
    Route::delete('/{id}', [ManutencaoServicoController::class, 'destroy'])
        ->name('manutencaoservicos.destroy');

});

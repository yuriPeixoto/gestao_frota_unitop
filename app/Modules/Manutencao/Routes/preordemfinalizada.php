<?php
/**
 * Rotas de Pré-Ordem de Serviço Finalizada
 */
use App\Modules\Manutencao\Controllers\Admin\PreOrdemListagemFinalizadasController;
use Illuminate\Support\Facades\Route;

// Pré-Ordem de Serviço - Finalizadas
Route::group(['prefix' => 'manutencaopreordemservicofinalizada'], function () {
    Route::get('/', [PreOrdemListagemFinalizadasController::class, 'index'])
        ->name('manutencaopreordemservicofinalizada.index');

    Route::get('/{id}/edit', [PreOrdemListagemFinalizadasController::class, 'edit'])
        ->name('manutencaopreordemservicofinalizada.edit');
    Route::put('/{id}', [PreOrdemListagemFinalizadasController::class, 'update'])
        ->name('manutencaopreordemservicofinalizada.update');
});

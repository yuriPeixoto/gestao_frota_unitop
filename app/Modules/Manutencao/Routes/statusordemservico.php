<?php
/**
 * Rotas de Status de Ordem de Serviço
 */
use App\Modules\Manutencao\Controllers\Admin\StatusOrdemServicoController;
use Illuminate\Support\Facades\Route;

// Status de Ordem de Serviço
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

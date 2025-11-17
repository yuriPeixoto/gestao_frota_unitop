<?php

use App\Modules\Veiculos\Controllers\Admin\AtrelamentoVeiculoController;
use Illuminate\Support\Facades\Route;

// Rotas de Atrelamento de Veiculo
Route::group(['prefix' => 'atrelamentoveiculos'], function () {
    Route::get('/', [AtrelamentoVeiculoController::class, 'index'])->name('atrelamentoveiculos.index');
    Route::get('criar', [AtrelamentoVeiculoController::class, 'create'])->name('atrelamentoveiculos.create');
    Route::get('{atrelamentoveiculos}', [AtrelamentoVeiculoController::class, 'show'])
        ->name('atrelamentoveiculos.show');
    Route::post('/get-kmhrinicialcavalo-data', [AtrelamentoVeiculoController::class, 'getInicialCavalo'])
        ->name('atrelamentoveiculos.get-kmhrinicialcavalo-data');

    Route::post('/', [AtrelamentoVeiculoController::class, 'store'])->name('atrelamentoveiculos.store');
    Route::get('{atrelamentoveiculos}/editar', [AtrelamentoVeiculoController::class, 'edit'])
        ->name('atrelamentoveiculos.edit');
    Route::put('{atrelamentoveiculos}', [AtrelamentoVeiculoController::class, 'update'])
        ->name('atrelamentoveiculos.update');

    Route::delete('{atrelamentoveiculos}', [AtrelamentoVeiculoController::class, 'destroy'])
        ->name('atrelamentoveiculos.destroy');
});

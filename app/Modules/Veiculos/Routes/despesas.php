<?php

use App\Modules\Veiculos\Controllers\Admin\ManutencaoRelacaoDespesasVeiculosController;
use App\Http\Controllers\Admin\VeiculoController;
use Illuminate\Support\Facades\Route;

// Relacao despesas veiculos
Route::group(['prefix' => 'relacaodespesasveiculos'], function () {
    Route::get('/', [ManutencaoRelacaoDespesasVeiculosController::class, 'index'])
        ->name('relacaodespesasveiculos.index');
    Route::get('/create', [ManutencaoRelacaoDespesasVeiculosController::class, 'create'])
        ->name('relacaodespesasveiculos.create');
    Route::get('/{id}/edit', [ManutencaoRelacaoDespesasVeiculosController::class, 'edit'])
        ->name('relacaodespesasveiculos.edit');
    Route::put('/{id}', [ManutencaoRelacaoDespesasVeiculosController::class, 'update'])
        ->name('relacaodespesasveiculos.update');
    Route::post('/', [ManutencaoRelacaoDespesasVeiculosController::class, 'store'])
        ->name('relacaodespesasveiculos.store');
    Route::delete('/{id}', [ManutencaoRelacaoDespesasVeiculosController::class, 'destroy'])
        ->name('relacaodespesasveiculos.destroy');

    // Você pode adicionar rotas semelhantes para outros tipos de entidades
    // Route::get('/veiculos/search', [VeiculoController::class, 'search'])
    //     ->name('api.veiculos.search');
    Route::get('/veiculos/single/{id}', [VeiculoController::class, 'getById'])
        ->name('api.veiculos.single');
});

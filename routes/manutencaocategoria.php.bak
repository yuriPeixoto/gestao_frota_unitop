<?php

use App\Http\Controllers\Admin\ConfigManutencaoXCategoriaController;
use Illuminate\Support\Facades\Route;

// Manutencao x categoria
Route::group(['prefix' => 'manutencaocategoria'], function () {
    Route::get('/', [ConfigManutencaoXCategoriaController::class, 'index'])
        ->name('manutencaocategoria.index');
    Route::get('/create', [ConfigManutencaoXCategoriaController::class, 'create'])
        ->name('manutencaocategoria.create');
    Route::get('/{id}/edit', [ConfigManutencaoXCategoriaController::class, 'edit'])
        ->name('manutencaocategoria.edit');
    Route::put('/{id}', [ConfigManutencaoXCategoriaController::class, 'update'])
        ->name('manutencaocategoria.update');
    Route::post('/', [ConfigManutencaoXCategoriaController::class, 'store'])
        ->name('manutencaocategoria.store');
    Route::delete('/{id}', [ConfigManutencaoXCategoriaController::class, 'destroy'])
        ->name('manutencaocategoria.destroy');

});

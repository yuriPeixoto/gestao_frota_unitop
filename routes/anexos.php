<?php

use App\Http\Controllers\Admin\AnexoController;
use Illuminate\Support\Facades\Route;

// Rotas para Anexos
Route::group(['prefix' => 'anexos'], function () {
    // Upload de anexos
    Route::post('/upload', [AnexoController::class, 'upload'])
        ->name('anexos.upload');

    // Download de anexos
    Route::get('/download/{id}', [AnexoController::class, 'download'])
        ->name('anexos.download');

    // Visualização de anexos
    Route::get('/show/{id}', [AnexoController::class, 'show'])
        ->name('anexos.show');

    // Exclusão de anexos
    Route::delete('/{id}', [AnexoController::class, 'destroy'])
        ->name('anexos.destroy');

    // API para listar anexos por entidade
    Route::get('/list', [AnexoController::class, 'listByEntity'])
        ->name('anexos.list');
});

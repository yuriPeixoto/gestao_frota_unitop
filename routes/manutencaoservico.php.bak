<?php

use App\Http\Controllers\Admin\ManutencaoController;
use App\Http\Controllers\Admin\ManutencaoXServicoController;
use App\Http\Controllers\Admin\ServicoController;
use Illuminate\Support\Facades\Route;

// Manutencao x servico
Route::group(['prefix' => 'manutencaoservico'], function () {
    Route::get('/', [ManutencaoXServicoController::class, 'index'])
        ->name('manutencaoservico.index');
    Route::get('/create', [ManutencaoXServicoController::class, 'create'])
        ->name('manutencaoservico.create');
    Route::get('/{id}/edit', [ManutencaoXServicoController::class, 'edit'])
        ->name('manutencaoservico.edit');
    Route::put('/{id}', [ManutencaoXServicoController::class, 'update'])
        ->name('manutencaoservico.update');
    Route::post('/', [ManutencaoXServicoController::class, 'store'])
        ->name('manutencaoservico.store');
    Route::delete('/{id}', [ManutencaoXServicoController::class, 'destroy'])
        ->name('manutencaoservico.destroy');

});

Route::prefix('api')->group(function () {
    Route::get('/servico/search', [ServicoController::class, 'search'])->name('api.servico.search');
    Route::get('/servico/single/{id}', [ServicoController::class, 'getById'])->name('api.servico.single');
});

Route::prefix('api')->group(function () {
    Route::get('/manutencao/search', [ManutencaoController::class, 'search'])->name('api.manutencao.search');
    Route::get('/manutencao/single/{id}', [ManutencaoController::class, 'getById'])->name('api.manutencao.single');
});
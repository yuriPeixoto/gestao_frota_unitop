<?php

use App\Modules\Pessoal\Controllers\Admin\PessoalController;
use App\Modules\Pessoal\Controllers\Admin\TipoPessoalController;
use Illuminate\Support\Facades\Route;

// Rotas de Pessoas
Route::group(['prefix' => 'pessoas'], function () {
    Route::get('/', [PessoalController::class, 'index'])
        ->name('pessoas.index');
    Route::get('criar', [PessoalController::class, 'create'])
        ->name('pessoas.create');
    Route::get('{pessoas}', [PessoalController::class, 'show'])
        ->name('pessoas.show');

    Route::post('/', [PessoalController::class, 'store'])
        ->name('pessoas.store');
    Route::get('{pessoas}/editar', [PessoalController::class, 'edit'])
        ->name('pessoas.edit');
    Route::put('{pessoas}', [PessoalController::class, 'update'])
        ->name('pessoas.update');

    Route::delete('{pessoas}', [PessoalController::class, 'destroy'])
        ->name('pessoas.destroy');
});

// Rotas de API para Pessoal
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/pessoal/search', [PessoalController::class, 'search'])->name('pessoal.search');
    Route::get('/pessoal/single/{id}', [PessoalController::class, 'getById'])->name('pessoal.single');
});

// Rotas de Tipo Pessoal (Configuração)
Route::resource('tipopessoal', TipoPessoalController::class);

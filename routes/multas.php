<?php

use App\Http\Controllers\Admin\MultaController;
use App\Http\Controllers\Admin\ClassificacaoMultaController;
use App\Http\Controllers\Admin\MunicipioController;
use App\Http\Controllers\Admin\PessoalController;
use App\Http\Controllers\Admin\VeiculoController;
use Illuminate\Support\Facades\Route;

// Rotas de Multas
Route::group(['prefix' => 'multas'], function () {
    Route::get('/', [MultaController::class, 'index'])->name('multas.index');
    Route::get('criar', [MultaController::class, 'create'])->name('multas.create');
    Route::get('{multas}', [MultaController::class, 'show'])->name('multas.show');
    Route::post('/get-vehicle-data', [MultaController::class, 'getVehicleData'])->name('multas.get-vehicle-data');

    Route::post('/', [MultaController::class, 'store'])->name('multas.store');
    Route::get('{multas}/editar', [MultaController::class, 'edit'])->name('multas.edit');
    Route::put('{multas}', [MultaController::class, 'update'])->name('multas.update');

    Route::delete('{multas}', [MultaController::class, 'destroy'])
        ->name('multas.destroy');

    Route::post('/get-vehicle-data', [MultaController::class, 'getVehicleData'])
        ->name('multas.getVehicleData');
});

// Rotas para APIs de pesquisa usadas pelos smart-selects
Route::get('veiculos/search', [VeiculoController::class, 'search'])
    ->name('veiculos.search');

Route::get('condutores/search', [PessoalController::class, 'search'])
    ->name('condutores.search');

Route::get('municipios/search', [MunicipioController::class, 'search'])
    ->name('municipios.search');

Route::get('municipios/single/{id}', [MunicipioController::class, 'single'])
    ->name('municipios.single');


// Rotas de Classificação Multa
Route::group(['prefix' => 'classificacaomultas'], function () {
    Route::get('/', [ClassificacaoMultaController::class, 'index'])->name('classificacaomultas.index');
    Route::get('criar', [ClassificacaoMultaController::class, 'create'])->name('classificacaomultas.create');
    Route::get('{classificacaomultas}', [ClassificacaoMultaController::class, 'show'])
        ->name('classificacaomultas.show');

    Route::post('/', [ClassificacaoMultaController::class, 'store'])->name('classificacaomultas.store');
    Route::get('{classificacaomultas}/editar', [ClassificacaoMultaController::class, 'edit'])
        ->name('classificacaomultas.edit');
    Route::put('{classificacaomultas}', [ClassificacaoMultaController::class, 'update'])
        ->name('classificacaomultas.update');

    Route::delete('{classificacaomultas}', [ClassificacaoMultaController::class, 'destroy'])
        ->name('classificacaomultas.destroy');
});

<?php

use App\Http\Controllers\Admin\MotoristaController;
use App\Http\Controllers\Admin\PreOrdemListagemNovaController;
use App\Http\Controllers\Admin\VeiculoController;
use Illuminate\Support\Facades\Route;

// Manutencao servicos mecanico
Route::group(['prefix' => 'manutencaopreordemserviconova'], function () {
    Route::get('/', [PreOrdemListagemNovaController::class, 'index'])
        ->name('manutencaopreordemserviconova.index');
    Route::get('/{id}/preventiva', [PreOrdemListagemNovaController::class, 'preventiva'])
        ->name('manutencaopreordemserviconova.preventiva');

    Route::get('/{id}/historico', [PreOrdemListagemNovaController::class, 'historico'])
        ->name('manutencaopreordemserviconova.historico');

    Route::get('/create', [PreOrdemListagemNovaController::class, 'create'])
        ->name('manutencaopreordemserviconova.create');
    Route::get('/{ids}', [PreOrdemListagemNovaController::class, 'gerarPreventiva'])
        ->name('manutencaopreordemserviconova.gerarpreventiva');
    Route::get('/{id}/edit', [PreOrdemListagemNovaController::class, 'edit'])
        ->name('manutencaopreordemserviconova.edit');
    Route::put('/{id}', [PreOrdemListagemNovaController::class, 'update'])
        ->name('manutencaopreordemserviconova.update');
    Route::post('/', [PreOrdemListagemNovaController::class, 'store'])
        ->name('manutencaopreordemserviconova.store');
    Route::delete('/{id}', [PreOrdemListagemNovaController::class, 'destroy'])
        ->name('manutencaopreordemserviconova.destroy');
    Route::post('/assumirpreos/{id}', [PreOrdemListagemNovaController::class, 'assumirPreOs'])
        ->name('manutencaopreordemserviconova.assumirpreos');
    Route::post('/gerarcorretiva/{id}', [PreOrdemListagemNovaController::class, 'gerarCorretiva'])
        ->name('manutencaopreordemserviconova.gerarcorretiva');
    Route::post('/finalizaros/{id}', [PreOrdemListagemNovaController::class, 'finalizarOs'])
        ->name('manutencaopreordemserviconova.finalizaros');
    Route::post('/getInfoVeiculo', [PreOrdemListagemNovaController::class, 'getInfoVeiculo'])
        ->name('manutencaopreordemserviconova.getInfoVeiculo');
    Route::post('/getTelefoneMotorista', [PreOrdemListagemNovaController::class, 'getTelefoneMotorista'])
        ->name('manutencaopreordemserviconova.getTelefoneMotorista');
    Route::post('/imprimir', [PreOrdemListagemNovaController::class, 'onImprimir'])
        ->name('manutencaopreordemserviconova.imprimir');
    // Rotas API
    // Route::get('/veiculos/search', [VeiculoController::class, 'search'])
    //     ->name('api.veiculos.search');
    Route::get('/veiculos/single/{id}', [VeiculoController::class, 'getById'])
        ->name('api.veiculos.single');

    Route::get('/motoristas/search', [MotoristaController::class, 'search'])
        ->name('api.motoristas.search');
    Route::get('/motoristas/single/{id}', [MotoristaController::class, 'getById'])
        ->name('api.motoristas.single');
});

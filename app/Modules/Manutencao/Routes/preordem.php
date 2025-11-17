<?php
/**
 * Rotas de Pré-Ordem de Serviço
 */
use App\Modules\Manutencao\Controllers\Admin\PreOrdemListagemNovaController;
use Illuminate\Support\Facades\Route;

// Pré-Ordem de Serviço - Nova
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

    Route::post('/', [PreOrdemListagemNovaController::class, 'store'])
        ->name('manutencaopreordemserviconova.store');
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

    Route::put('/{id}', [PreOrdemListagemNovaController::class, 'update'])
        ->name('manutencaopreordemserviconova.update');

    Route::delete('/{id}', [PreOrdemListagemNovaController::class, 'destroy'])
        ->name('manutencaopreordemserviconova.destroy');
});

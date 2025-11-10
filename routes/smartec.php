<?php

use App\Http\Controllers\SmartecController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Smartec API Routes
|--------------------------------------------------------------------------
|
| Rotas para integração com a API da Smartec
|
*/

Route::prefix('smartec')->name('smartec.')->group(function () {

    // Consultas de veículos
    Route::post('/veiculo/consultar', [SmartecController::class, 'consultarVeiculo'])
        ->name('veiculo.consultar');

    // Gestão de infrações
    Route::post('/infracao/indicar', [SmartecController::class, 'indicarInfracao'])
        ->name('infracao.indicar');

    Route::post('/infracao/consultar', [SmartecController::class, 'consultarInfracoes'])
        ->name('infracao.consultar');

    Route::post('/infracao/desconto', [SmartecController::class, 'solicitarDesconto'])
        ->name('infracao.desconto');

    // Gestão de CNH
    Route::post('/cnh/consultar', [SmartecController::class, 'consultarCnh'])
        ->name('cnh.consultar');

    // Documentos
    Route::post('/fici/gerar', [SmartecController::class, 'gerarFici'])
        ->name('fici.gerar');
});

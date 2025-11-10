<?php

use App\Http\Controllers\Admin\LancIpvaLicenciamentoSeguroController;
use Illuminate\Support\Facades\Route;

// Rotas de LancamentoIpvaLicenciamentoSeguro
Route::group(['prefix' => 'lancipvalicenciamentoseguros'], function () {
    Route::get('/', [LancIpvaLicenciamentoSeguroController::class, 'index'])
        ->name('lancipvalicenciamentoseguros.index');
    Route::get('criar', [LancIpvaLicenciamentoSeguroController::class, 'create'])
        ->name('lancipvalicenciamentoseguros.create');
    Route::get('Editar/{id}', [LancIpvaLicenciamentoSeguroController::class, 'edit'])
        ->name('lancipvalicenciamentoseguros.edit');
    Route::put('{id}', [LancIpvaLicenciamentoSeguroController::class, 'update'])->name('lancipvalicenciamentoseguros.update');

    Route::post('/lancar-licenciamento', [LancIpvaLicenciamentoSeguroController::class, 'lancarLicenciamento'])
        ->name('lancipvalicenciamentoseguros.lancar-licenciamento');
    Route::post('/lancar-ipva', [LancIpvaLicenciamentoSeguroController::class, 'lancaripva'])
        ->name('lancipvalicenciamentoseguros.lancar-ipva');
    Route::post('/lancar-seguro', [LancIpvaLicenciamentoSeguroController::class, 'lancarseguro'])
        ->name('lancipvalicenciamentoseguros.lancar-seguro');

    Route::post('licenciamento/update', [LancIpvaLicenciamentoSeguroController::class, 'updateLicenciamento'])->name('lancipvalicenciamentoseguros.updatelicenciamento');
});

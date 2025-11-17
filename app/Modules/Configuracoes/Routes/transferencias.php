<?php
/**
 * Rotas de Transferências (Departamento e Telefone)
 */
use App\Modules\Configuracoes\Controllers\Admin\{
    DepartamentoTransferenciaController,
    TelefoneTransferenciaController
};
use Illuminate\Support\Facades\Route;

// Rotas de Telefone Transferência
Route::group(['prefix' => 'telefonetransferencia'], function () {
    Route::get('/', [TelefoneTransferenciaController::class, 'index'])->name('telefonetransferencia.index');
    Route::get('criar', [TelefoneTransferenciaController::class, 'create'])->name('telefonetransferencia.create');
    Route::get('{telefonetransferencia}/editar', [TelefoneTransferenciaController::class, 'edit'])
        ->name('telefonetransferencia.edit');


    Route::post('/', [TelefoneTransferenciaController::class, 'store'])->name('telefonetransferencia.store');

    Route::put('{telefonetransferencia}', [TelefoneTransferenciaController::class, 'update'])
        ->name('telefonetransferencia.update');


    Route::delete('{telefonetransferencia}', [TelefoneTransferenciaController::class, 'destroy'])
        ->name('telefonetransferencia.destroy');
});

// Rotas de Departamento Transferência
Route::group(['prefix' => 'departamentotransferencia'], function () {
    Route::get('/', [DepartamentoTransferenciaController::class, 'index'])->name('departamentotransferencia.index');
    Route::get('criar', [DepartamentoTransferenciaController::class, 'create'])->name('departamentotransferencia.create');

    Route::post('/', [DepartamentoTransferenciaController::class, 'store'])->name('departamentotransferencia.store');
    Route::get('{departamentotransferencia}/editar', [DepartamentoTransferenciaController::class, 'edit'])
        ->name('departamentotransferencia.edit');
    Route::put('{departamentotransferencia}', [DepartamentoTransferenciaController::class, 'update'])
        ->name('departamentotransferencia.update');

    Route::delete('{departamentotransferencia}', [DepartamentoTransferenciaController::class, 'destroy'])
        ->name('departamentotransferencia.destroy');
});

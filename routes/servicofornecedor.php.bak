<?php

use App\Http\Controllers\Admin\ServicoXFornecedorController;
use Illuminate\Support\Facades\Route;

// Servico X Fornecedor
Route::group(['prefix' => 'servicofornecedor'], function () {
    Route::get('/', [ServicoXFornecedorController::class, 'index'])
        ->name('servicofornecedor.index');
    Route::get('/create', [ServicoXFornecedorController::class, 'create'])
        ->name('servicofornecedor.create');
    Route::get('/{id}/edit', [ServicoXFornecedorController::class, 'edit'])
        ->name('servicofornecedor.edit');
    Route::put('/{id}', [ServicoXFornecedorController::class, 'update'])
        ->name('servicofornecedor.update');
    Route::post('/', [ServicoXFornecedorController::class, 'store'])
        ->name('servicofornecedor.store');
    Route::delete('/{id}', [ServicoXFornecedorController::class, 'destroy'])
        ->name('servicofornecedor.destroy');
});

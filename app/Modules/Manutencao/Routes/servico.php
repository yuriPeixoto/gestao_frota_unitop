<?php
/**
 * Rotas de Serviços
 */
use App\Modules\Manutencao\Controllers\Admin\ServicoController;
use App\Modules\Manutencao\Controllers\Admin\GrupoServicoController;
use App\Modules\Manutencao\Controllers\Admin\SubgrupoServicoController;
use App\Modules\Manutencao\Controllers\Admin\ServicoXFornecedorController;
use Illuminate\Support\Facades\Route;

// Rotas de Serviços
Route::group(['prefix' => 'manutencao'], function () {
    Route::group(['prefix' => 'servicos'], function () {
        Route::get('/', [ServicoController::class, 'index'])->name('servicos.index');
        Route::get('/criar', [ServicoController::class, 'create'])->name('servicos.create');
        Route::get('/{id}', [ServicoController::class, 'edit'])->name('servicos.edit');
        Route::put('/{id}', [ServicoController::class, 'update'])->name('servicos.update');
        Route::post('/', [ServicoController::class, 'store'])->name('servicos.store');
        Route::delete('/{id}', [ServicoController::class, 'destroy'])->name('servicos.destroy');

        // Rotas para busca de registros
        Route::get('servicos/search', [ServicoController::class, 'search'])->name('servicos.search');
        Route::get('servicos/single/{id}', [ServicoController::class, 'single'])->name('servicos.single');
    });
});

// Serviço X Fornecedor
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

// Grupo de Serviço
Route::group(['prefix' => 'gruposervico'], function () {
    Route::get('/', [GrupoServicoController::class, 'index'])->name('gruposervico.index');
    Route::get('/create', [GrupoServicoController::class, 'create'])->name('gruposervico.create');
    Route::get('/{id}/edit', [GrupoServicoController::class, 'edit'])->name('gruposervico.edit');
    Route::put('/{id}', [GrupoServicoController::class, 'update'])->name('gruposervico.update');
    Route::post('/', [GrupoServicoController::class, 'store'])->name('gruposervico.store');
    Route::delete('/{id}', [GrupoServicoController::class, 'destroy'])->name('gruposervico.destroy');
});

// Subgrupo de Serviço
Route::group(['prefix' => 'subgruposervico'], function () {
    Route::get('/', [SubgrupoServicoController::class, 'index'])->name('subgruposervico.index');
    Route::get('/create', [SubgrupoServicoController::class, 'create'])->name('subgruposervico.create');
    Route::get('/{id}/edit', [SubgrupoServicoController::class, 'edit'])->name('subgruposervico.edit');
    Route::put('/{id}', [SubgrupoServicoController::class, 'update'])->name('subgruposervico.update');
    Route::post('/', [SubgrupoServicoController::class, 'store'])->name('subgruposervico.store');
    Route::delete('/{id}', [SubgrupoServicoController::class, 'destroy'])->name('subgruposervico.destroy');
});

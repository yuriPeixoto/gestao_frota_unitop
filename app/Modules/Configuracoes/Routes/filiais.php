<?php
/**
 * Rotas de Filiais (Branches)
 */
use App\Modules\Configuracoes\Controllers\Admin\BranchController;
use Illuminate\Support\Facades\Route;

// Rotas de Filiais
Route::group(['prefix' => 'filiais'], function () {
    Route::get('/', [BranchController::class, 'index'])->name('filiais.index');

    Route::get('criar', [BranchController::class, 'create'])->name('filiais.create');
    Route::post('/', [BranchController::class, 'store'])->name('filiais.store');

    Route::get('{branch}/editar', [BranchController::class, 'edit'])->name('filiais.edit');
    Route::put('{branch}', [BranchController::class, 'update'])->name('filiais.update');

    Route::delete('{branch}', [BranchController::class, 'destroy'])
        ->name('filiais.destroy');
});

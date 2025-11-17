<?php
/**
 * Rotas de Tipos (Categoria, Equipamento, Unidade Produto)
 */
use App\Modules\Configuracoes\Controllers\Admin\{
    TipoCategoriaController,
    TipoEquipamentoController,
    UnidadeProdutoController
};
use Illuminate\Support\Facades\Route;

// Rotas de Tipo Categoria
Route::group(['prefix' => 'tipocategorias'], function () {
    Route::get('/', [TipoCategoriaController::class, 'index'])->name('tipocategorias.index');
    Route::get('criar', [TipoCategoriaController::class, 'create'])->name('tipocategorias.create');
    Route::get('{tipocategoria}', [TipoCategoriaController::class, 'show'])->name('tipocategorias.show');

    Route::post('/', [TipoCategoriaController::class, 'store'])->name('tipocategorias.store');
    Route::get('{tipocategoria}/editar', [TipoCategoriaController::class, 'edit'])->name('tipocategorias.edit');
    Route::put('{tipocategoria}', [TipoCategoriaController::class, 'update'])->name('tipocategorias.update');

    Route::delete('{tipocategoria}', [TipoCategoriaController::class, 'destroy'])
        ->name('tipocategorias.destroy');
});

// Rotas de Tipo Equipamento
Route::group(['prefix' => 'tipoequipamentos'], function () {
    Route::get('/', [TipoEquipamentoController::class, 'index'])->name('tipoequipamentos.index');
    Route::get('criar', [TipoEquipamentoController::class, 'create'])->name('tipoequipamentos.create');
    Route::get('{tipoequipamento}', [TipoEquipamentoController::class, 'show'])->name('tipoequipamentos.show');

    Route::post('/', [TipoEquipamentoController::class, 'store'])->name('tipoequipamentos.store');
    Route::get('{tipoequipamento}/editar', [TipoEquipamentoController::class, 'edit'])
        ->name('tipoequipamentos.edit');
    Route::put('{tipoequipamento}', [TipoEquipamentoController::class, 'update'])
        ->name('tipoequipamentos.update');

    Route::delete('{tipoequipamento}', [TipoEquipamentoController::class, 'destroy'])
        ->name('tipoequipamentos.destroy');
});

// Rotas de Unidade Produto
Route::group(['prefix' => 'unidadeprodutos'], function () {
    Route::get('/', [UnidadeProdutoController::class, 'index'])->name('unidadeprodutos.index');
    Route::get('criar', [UnidadeProdutoController::class, 'create'])->name('unidadeprodutos.create');
    Route::get('{unidadeprodutos}', [UnidadeProdutoController::class, 'show'])->name('unidadeprodutos.show');

    Route::post('/', [UnidadeProdutoController::class, 'store'])->name('unidadeprodutos.store');
    Route::get('{unidadeprodutos}/editar', [UnidadeProdutoController::class, 'edit'])
        ->name('unidadeprodutos.edit');
    Route::put('{unidadeprodutos}', [UnidadeProdutoController::class, 'update')
        ->name('unidadeprodutos.update');

    Route::delete('{unidadeprodutos}', [UnidadeProdutoController::class, 'destroy'])
        ->name('unidadeprodutos.destroy');
});

<?php
/**
 * Rotas de Manutenção
 */
use App\Modules\Manutencao\Controllers\Admin\ManutencaoController;
use App\Modules\Manutencao\Controllers\Admin\TipoManutencaoController;
use App\Modules\Manutencao\Controllers\Admin\ManutencaoXServicoController;
use App\Modules\Manutencao\Controllers\Admin\ManutencaoServicoController;
use App\Modules\Manutencao\Controllers\Admin\ConfigManutencaoXCategoriaController;
use Illuminate\Support\Facades\Route;

// Rotas de Manutenção
Route::group(['prefix' => 'manutencao'], function () {

    Route::group(['prefix' => 'manutencoes'], function () {
        Route::get('/criar', [ManutencaoController::class, 'create'])->name('manutencoes.create');
        Route::post('/', [ManutencaoController::class, 'store'])->name('manutencoes.store');
        Route::get('/', [ManutencaoController::class, 'index'])->name('manutencoes.index');
        Route::get('/{id}', [ManutencaoController::class, 'edit'])->name('manutencoes.edit');
        Route::put('/{id}', [ManutencaoController::class, 'update'])->name('manutencoes.update');
    });

    Route::group(['prefix' => 'monitoramento-das-manutencoes'], function () {
        Route::get('/', [\App\Modules\Manutencao\Controllers\Admin\MonitoramentoManutencoesController::class, 'index'])
            ->name('monitoramentoDasManutencoes.index');
    });
});

// Manutenção x Categoria
Route::group(['prefix' => 'manutencaocategoria'], function () {
    Route::get('/', [ConfigManutencaoXCategoriaController::class, 'index'])
        ->name('manutencaocategoria.index');
    Route::get('/create', [ConfigManutencaoXCategoriaController::class, 'create'])
        ->name('manutencaocategoria.create');
    Route::get('/{id}/edit', [ConfigManutencaoXCategoriaController::class, 'edit'])
        ->name('manutencaocategoria.edit');
    Route::put('/{id}', [ConfigManutencaoXCategoriaController::class, 'update'])
        ->name('manutencaocategoria.update');
    Route::post('/', [ConfigManutencaoXCategoriaController::class, 'store'])
        ->name('manutencaocategoria.store');
    Route::delete('/{id}', [ConfigManutencaoXCategoriaController::class, 'destroy'])
        ->name('manutencaocategoria.destroy');
});

// Manutenção x Serviço
Route::group(['prefix' => 'manutencaoservico'], function () {
    Route::get('/', [ManutencaoXServicoController::class, 'index'])
        ->name('manutencaoservico.index');
    Route::get('/create', [ManutencaoXServicoController::class, 'create'])
        ->name('manutencaoservico.create');
    Route::get('/{id}/edit', [ManutencaoXServicoController::class, 'edit'])
        ->name('manutencaoservico.edit');
    Route::put('/{id}', [ManutencaoXServicoController::class, 'update'])
        ->name('manutencaoservico.update');
    Route::post('/', [ManutencaoXServicoController::class, 'store'])
        ->name('manutencaoservico.store');
    Route::delete('/{id}', [ManutencaoXServicoController::class, 'destroy'])
        ->name('manutencaoservico.destroy');
});

// Manutenção Serviços
Route::group(['prefix' => 'manutencaoservicos'], function () {
    Route::get('/', [ManutencaoServicoController::class, 'index'])
        ->name('manutencaoservicos.index');
    Route::get('/create', [ManutencaoServicoController::class, 'create'])
        ->name('manutencaoservicos.create');
    Route::get('/{id}/edit', [ManutencaoServicoController::class, 'edit'])
        ->name('manutencaoservicos.edit');
    Route::put('/{id}', [ManutencaoServicoController::class, 'update'])
        ->name('manutencaoservicos.update');
    Route::post('/', [ManutencaoServicoController::class, 'store'])
        ->name('manutencaoservicos.store');
    Route::delete('/{id}', [ManutencaoServicoController::class, 'destroy'])
        ->name('manutencaoservicos.destroy');
});

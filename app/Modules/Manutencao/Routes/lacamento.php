<?php
/**
 * Rotas de Lançamento de NF de Serviço
 */
use App\Modules\Manutencao\Controllers\Admin\ManutencaoListagemOsLacamentoNFServicoController;
use App\Modules\Manutencao\Controllers\Admin\ManutencaoNotasFicaisRateioController;
use Illuminate\Support\Facades\Route;

// Listagem de OS para Lançamento de NF de Serviço
Route::group(['prefix' => 'listagemoslacamentoservico'], function () {
    Route::get('/', [ManutencaoListagemOsLacamentoNFServicoController::class, 'index'])
        ->name('listagemoslacamentoservico.index');
});

// Listagem de OS para Lançamento de NF de Serviço com Rateio
Route::group(['prefix' => 'listagemoslacamentoservicorateio'], function () {
    Route::get('/', [ManutencaoNotasFicaisRateioController::class, 'index'])
        ->name('listagemoslacamentoservicorateio.index');
    Route::get('/create', [ManutencaoNotasFicaisRateioController::class, 'create'])
        ->name('listagemoslacamentoservicorateio.create');
    Route::get('/{id}/edit', [ManutencaoNotasFicaisRateioController::class, 'edit'])
        ->name('listagemoslacamentoservicorateio.edit');
    Route::put('/{id}', [ManutencaoNotasFicaisRateioController::class, 'update'])
        ->name('listagemoslacamentoservicorateio.update');
    Route::post('/', [ManutencaoNotasFicaisRateioController::class, 'store'])
        ->name('listagemoslacamentoservicorateio.store');
    Route::delete('/{id}', [ManutencaoNotasFicaisRateioController::class, 'destroy'])
        ->name('listagemoslacamentoservicorateio.destroy');
});

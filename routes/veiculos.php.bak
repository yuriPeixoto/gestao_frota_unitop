<?php

use App\Http\Controllers\Admin\FornecedorController;
use App\Http\Controllers\Admin\MunicipioController;
use App\Http\Controllers\Admin\VeiculoController;
use Illuminate\Support\Facades\Route;

// Rotas de Veiculo
Route::group(['prefix' => 'veiculos'], function () {
    // Exportações
    Route::get('/export-csv', [VeiculoController::class, 'exportCsv'])->name('veiculos.exportCsv');
    Route::get('/export-xls', [VeiculoController::class, 'exportXls'])->name('veiculos.exportXls');
    Route::get('/export-pdf', [VeiculoController::class, 'exportPdf'])->name('veiculos.exportPdf');
    Route::get('/export-xml', [VeiculoController::class, 'exportXml'])->name('veiculos.exportXml');

    Route::get('/', [VeiculoController::class, 'index'])->name('veiculos.index');
    Route::get('criar', [VeiculoController::class, 'create'])->name('veiculos.create');
    Route::post('/', [VeiculoController::class, 'store'])->name('veiculos.store');
    Route::get('{veiculo}', [VeiculoController::class, 'show'])->name('veiculos.show');
    Route::get('{veiculo}/editar', [VeiculoController::class, 'edit'])->name('veiculos.edit');
    Route::put('{veiculo}', [VeiculoController::class, 'update'])->name('veiculos.update');
    Route::delete('{veiculo}', [VeiculoController::class, 'destroy'])->name('veiculos.destroy');

    Route::post('/baixar', [VeiculoController::class, 'onActionBaixarVeiculo'])->name('veiculos.baixar');
});

Route::prefix('api')->group(function () {
    // Fornecedor
    Route::get('/fornecedor/search', [FornecedorController::class, 'search'])->name('api.fornecedor.search');
    Route::get('/fornecedor/single/{id}', [FornecedorController::class, 'getById'])->name('api.fornecedor.single');

    // Municipio
    Route::get('/municipio/search', [MunicipioController::class, 'search'])->name('api.municipio.search');
    Route::get('/municipio/single/{id}', [MunicipioController::class, 'getById'])->name('api.municipio.single');
});

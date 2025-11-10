<?php

use App\Modules\Sinistros\Controllers\Relatorios\SinistroRelatorioController;
use App\Modules\Sinistros\Controllers\Relatorios\SinistroGeralRelatorioController;
use App\Modules\Sinistros\Controllers\Relatorios\SinistroRelController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'relatorios'], function () {
    //Sinistro
    Route::get('/sinistro/', [SinistroRelatorioController::class, 'index'])->name('relatorios.sinistro.index');
    Route::post('/sinistro/onGeneratePdf', [SinistroRelatorioController::class, 'onGeneratePdf'])->name('relatorios.sinistro.onGeneratePdf');
    Route::post('/sinistro/onGenerateTotalizador', [SinistroRelatorioController::class, 'onGenerateTotalizador'])->name('relatorios.sinistro.onGenerateTotalizador');
    Route::post('/sinistro/onGenerateXls', [SinistroRelatorioController::class, 'onGenerateXls'])->name('relatorios.sinistro.onGenerateXls');

    //Relatório Geral Sinistro
    Route::get('/sinistrogeral/', [SinistroGeralRelatorioController::class, 'index'])->name('relatorios.sinistrogeral.index');
    Route::post('/sinistrogeral/onGeneratePdf', [SinistroGeralRelatorioController::class, 'onGenerateGeralPdf'])->name('relatorios.sinistrogeral.onGeneratePdf');
    Route::post('/sinistrogeral/onGenerateXls', [SinistroGeralRelatorioController::class, 'onGenerateXls'])->name('relatorios.sinistrogeral.onGenerateXls');

    //Relatório Sinistro
    Route::get('/relatoriosinistro/', [SinistroRelController::class, 'index'])->name('relatorios.relatoriosinistro.index');
    Route::post('/relatoriosinistro/onGeneratePdf', [SinistroRelController::class, 'onGeneratePdf'])->name('relatorios.relatoriosinistro.onGeneratePdf');
    Route::post('/relatoriosinistro/onGenerateXls', [SinistroRelController::class, 'onGenerateXls'])->name('relatorios.relatoriosinistro.onGenerateXls');
});

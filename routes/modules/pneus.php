<?php
/**
 * Entry Point do Módulo de Pneus
 * Estrutura modular implementada em: 2025-11-13
 */
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    require app_path('Modules/Pneus/Routes/pneus.php');
    require app_path('Modules/Pneus/Routes/relatorios.php');
});

<?php
/**
 * Rotas do Módulo de Imobilizados
 * Estrutura modular implementada em: 2025-11-11
 */
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    require app_path('Modules/Imobilizados/Routes/imobilizado.php');
    require app_path('Modules/Imobilizados/Routes/relatorioimobilizado.php');
});

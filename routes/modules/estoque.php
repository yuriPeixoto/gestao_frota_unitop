<?php
/**
 * Rotas do Módulo de Estoque
 * Estrutura modular implementada em: 2025-11-11
 */
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    require app_path('Modules/Estoque/Routes/estoque.php');
    require app_path('Modules/Estoque/Routes/relatoriosestoque.php');
});

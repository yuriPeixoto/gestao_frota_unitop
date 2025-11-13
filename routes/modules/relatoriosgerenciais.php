<?php
/**
 * Rotas do Módulo de Relatórios Gerenciais
 * Estrutura modular implementada em: 2025-11-13
 */
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    require app_path('Modules/RelatoriosGerenciais/Routes/relatorios.php');
});

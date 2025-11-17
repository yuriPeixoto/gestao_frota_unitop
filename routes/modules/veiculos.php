<?php
/**
 * Entry Point do Módulo de Veículos
 * Estrutura modular implementada em: 2025-11-13
 */
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    require app_path('Modules/Veiculos/Routes/veiculos.php');
    require app_path('Modules/Veiculos/Routes/atrelamento.php');
    require app_path('Modules/Veiculos/Routes/ipva.php');
    require app_path('Modules/Veiculos/Routes/licenciamento.php');
    require app_path('Modules/Veiculos/Routes/despesas.php');
    require app_path('Modules/Veiculos/Routes/relatorios.php');
});

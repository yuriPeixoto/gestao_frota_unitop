<?php
/**
 * Entry Point do Módulo de Manutenção
 * Estrutura modular implementada em: 2025-11-17
 */
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    require app_path('Modules/Manutencao/Routes/ordemservico.php');
    require app_path('Modules/Manutencao/Routes/manutencao.php');
    require app_path('Modules/Manutencao/Routes/servico.php');
    require app_path('Modules/Manutencao/Routes/preordem.php');
    require app_path('Modules/Manutencao/Routes/preordemfinalizada.php');
    require app_path('Modules/Manutencao/Routes/statusordemservico.php');
    require app_path('Modules/Manutencao/Routes/mecanico.php');
    require app_path('Modules/Manutencao/Routes/lacamento.php');
    require app_path('Modules/Manutencao/Routes/relatorios.php');
});

<?php

/**
 * Rotas do Módulo de Abastecimentos
 *
 * Este arquivo centraliza todas as rotas relacionadas ao módulo de Abastecimentos,
 * incluindo rotas principais e relatórios.
 *
 * Estrutura modular implementada em: 2025-11-10
 */

use Illuminate\Support\Facades\Route;

// Middleware de autenticação e permissões do módulo
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {

    // Rotas principais do módulo de Abastecimentos
    require app_path('Modules/Abastecimentos/Routes/abastecimentos.php');

    // Rotas de relatórios do módulo de Abastecimentos
    require app_path('Modules/Abastecimentos/Routes/relatorios.php');

});

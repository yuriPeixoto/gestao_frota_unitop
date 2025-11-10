<?php

/**
 * Rotas do Módulo de Sinistros
 *
 * Este arquivo centraliza todas as rotas relacionadas ao módulo de Sinistros,
 * incluindo rotas principais e relatórios.
 *
 * Estrutura modular implementada em: 2025-11-10
 */

use Illuminate\Support\Facades\Route;

// Middleware de autenticação e permissões do módulo
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {

    // Rotas principais do módulo de Sinistros
    require app_path('Modules/Sinistros/Routes/sinistros.php');

    // Rotas de relatórios do módulo de Sinistros
    require app_path('Modules/Sinistros/Routes/relatorios.php');

});

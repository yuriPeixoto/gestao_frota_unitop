<?php

/**
 * Rotas do Módulo de Certificados e Vencimentário
 *
 * Este arquivo centraliza todas as rotas relacionadas aos módulos de Certificados
 * e Vencimentário (Licenciamentos, IPVA, Multas, etc).
 *
 * Estrutura modular implementada em: 2025-11-10
 */

use Illuminate\Support\Facades\Route;

// Middleware de autenticação e permissões do módulo
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {

    // Rotas do submódulo de Certificados (AETs, Cronotacógrafos, Testes)
    require app_path('Modules/Certificados/Routes/certificados.php');

    // Rotas do submódulo de Vencimentário (IPVA, Licenciamentos, Multas, ANTT, etc)
    require app_path('Modules/Certificados/Routes/vencimentario.php');

});

<?php
/**
 * Entry Point do Módulo de Configurações
 * Estrutura modular implementada em: 2025-11-17
 *
 * Este módulo contém:
 * - Gestão de Filiais (Branches)
 * - Log de Atividades do Sistema
 * - Usuários, Cargos e Permissões
 * - Transferências (Departamento e Telefone)
 * - Tipos base (Categoria, Equipamento, Unidade Produto)
 */
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Filiais
    require __DIR__ . '/../../app/Modules/Configuracoes/Routes/filiais.php';

    // Log de Atividades
    require __DIR__ . '/../../app/Modules/Configuracoes/Routes/log.php';

    // Usuários, Cargos e Permissões
    require __DIR__ . '/../../app/Modules/Configuracoes/Routes/usuarios.php';

    // Transferências
    require __DIR__ . '/../../app/Modules/Configuracoes/Routes/transferencias.php';

    // Tipos base
    require __DIR__ . '/../../app/Modules/Configuracoes/Routes/tipos.php';
});

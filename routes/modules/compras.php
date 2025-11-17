<?php
/**
 * Entry Point do Módulo de Compras
 * Estrutura modular implementada em: 2025-11-17
 *
 * NOTA: Este módulo possui 622 linhas de rotas consolidadas temporariamente
 * em um único arquivo. Refatoração futura pode dividir em:
 * - dashboard.php, pedidos.php, orcamentos.php, cotacoes.php,
 * - solicitacoes.php, notasfiscais.php, fornecedores.php, contratos.php
 */
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Rotas principais de Compras (consolidadas temporariamente)
    require __DIR__ . '/../compras.php';

    // Relatórios de Compras
    require __DIR__ . '/../relatoriocompras.php';
});

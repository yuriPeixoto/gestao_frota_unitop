<?php

/**
 * Rotas do Módulo de Checklist
 *
 * Este arquivo centraliza todas as rotas relacionadas ao módulo de Checklist,
 * incluindo API bridge para Lumen e dashboard React.
 *
 * Estrutura modular implementada em: 2025-11-10
 */

use Illuminate\Support\Facades\Route;

// Rotas do módulo Checklist (API Bridge + Dashboard React)
require app_path('Modules/Checklist/Routes/checklist.php');

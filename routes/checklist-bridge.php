<?php

use App\Http\Controllers\Api\ChecklistApiController;
use Illuminate\Support\Facades\Route;

// ========================================
// ROTA PRINCIPAL DO DASHBOARD REACT
// ========================================

/**
 * Rota principal que serve o dashboard React
 * Captura todas as subrotas para o React Router funcionar
 */
Route::get('/admin/checklist/{any?}', function () {
    return view('checklist.dashboard');
})->where('any', '.*')->name('admin.checklist');

// ========================================
// API BRIDGE ROUTES - PROXY PARA LUMEN
// ========================================

/**
 * Todas as rotas de API que fazem proxy para o Lumen
 * Prefix: /api/checklist
 * 
 * IMPORTANTE: JavaScript vai chamar estas rotas Laravel
 * que internamente fazem proxy para 10.10.1.5:8443
 */
Route::prefix('api/v2/checklist')->name('api.checklist.')->group(function () {

    // ========================================
    // HEALTH & STATISTICS
    // ========================================

    Route::get('/health', [ChecklistApiController::class, 'health'])
        ->name('health');

    Route::get('/statistics/overview', [ChecklistApiController::class, 'statisticsOverview'])
        ->name('statistics.overview');

    Route::get('/statistics/by-type/{typeId}', [ChecklistApiController::class, 'statisticsByType'])
        ->name('statistics.by-type');

    Route::get('/statistics/completion-rate', [ChecklistApiController::class, 'completionRate'])
        ->name('statistics.completion-rate');

    Route::get('/statistics/quality-analysis', [ChecklistApiController::class, 'qualityAnalysis'])
        ->name('statistics.quality-analysis');

    // ========================================
    // TIPOS DE CHECKLIST
    // ========================================

    Route::get('/types', [ChecklistApiController::class, 'types'])
        ->name('types.index');

    Route::get('/types/{id}', [ChecklistApiController::class, 'typeShow'])
        ->name('types.show');

    Route::post('/types', [ChecklistApiController::class, 'typeStore'])
        ->name('types.store');

    Route::put('/types/{id}', [ChecklistApiController::class, 'typeUpdate'])
        ->name('types.update');

    Route::delete('/types/{id}', [ChecklistApiController::class, 'typeDestroy'])
        ->name('types.destroy');

    // ========================================
    // CHECKLISTS
    // ========================================

    Route::get('/checklists', [ChecklistApiController::class, 'checklists'])
        ->name('checklists.index');

    Route::get('/checklists/{id}', [ChecklistApiController::class, 'checklistShow'])
        ->name('checklists.show');

    Route::post('/checklists', [ChecklistApiController::class, 'checklistStore'])
        ->name('checklists.store');

    Route::put('/checklists/{id}', [ChecklistApiController::class, 'checklistUpdate'])
        ->name('checklists.update');

    Route::delete('/checklists/{id}', [ChecklistApiController::class, 'checklistDestroy'])
        ->name('checklists.destroy');

    // ========================================
    // INTEGRAÇÃO EMPRESA
    // ========================================

    // Usuários
    Route::get('/users', [ChecklistApiController::class, 'users'])
        ->name('users.index');

    Route::get('/users/for-selection', [ChecklistApiController::class, 'usersForSelection'])
        ->name('users.for-selection');

    Route::get('/users/search', [ChecklistApiController::class, 'usersSearch'])
        ->name('users.search');

    // Veículos
    Route::get('/vehicles', [ChecklistApiController::class, 'vehicles'])
        ->name('vehicles.index');

    Route::get('/vehicles/for-selection', [ChecklistApiController::class, 'vehiclesForSelection'])
        ->name('vehicles.for-selection');

    Route::get('/vehicles/search', [ChecklistApiController::class, 'vehiclesSearch'])
        ->name('vehicles.search');

    // Departamentos
    Route::get('/departments', [ChecklistApiController::class, 'departments'])
        ->name('departments.index');

    Route::get('/departments/for-selection', [ChecklistApiController::class, 'departmentsForSelection'])
        ->name('departments.for-selection');

    Route::get('/departments/hierarchy', [ChecklistApiController::class, 'departmentsHierarchy'])
        ->name('departments.hierarchy');

    // Filiais
    Route::get('/branches', [ChecklistApiController::class, 'branches'])
        ->name('branches.index');

    Route::get('/branches/for-selection', [ChecklistApiController::class, 'branchesForSelection'])
        ->name('branches.for-selection');

    Route::get('/branches/organizational-structure', [ChecklistApiController::class, 'organizationalStructure'])
        ->name('branches.organizational-structure');

    // ========================================
    // TEMPLATES
    // ========================================

    Route::get('/templates', [ChecklistApiController::class, 'templates'])
        ->name('templates.index');

    Route::post('/templates', [ChecklistApiController::class, 'templateStore'])
        ->name('templates.store');

    // ========================================
    // SEÇÕES E ITENS (WIZARD)
    // ========================================

    Route::get('/types/{typeId}/sections', [ChecklistApiController::class, 'typeSections'])
        ->name('types.sections');

    Route::post('/types/{typeId}/sections', [ChecklistApiController::class, 'sectionStore'])
        ->name('sections.store');

    Route::get('/sections/{sectionId}/items', [ChecklistApiController::class, 'sectionItems'])
        ->name('sections.items');

    Route::post('/sections/{sectionId}/items', [ChecklistApiController::class, 'itemStore'])
        ->name('items.store');

    // ========================================
    // SISTEMA MULTI-STAGE
    // ========================================

    Route::get('/stage-assignments/available-assignees', [ChecklistApiController::class, 'availableAssignees'])
        ->name('stage-assignments.available-assignees');

    Route::post('/stage-assignments/assign-multiple', [ChecklistApiController::class, 'assignMultiple'])
        ->name('stage-assignments.assign-multiple');

    // ========================================
    // CATCH-ALL PARA ENDPOINTS NÃO MAPEADOS
    // ========================================

    /**
     * Rota catch-all para endpoints que não foram explicitamente mapeados
     * DEVE SER A ÚLTIMA ROTA para não interferir com as específicas
     */
    Route::any('/{path}', [ChecklistApiController::class, 'proxy'])
        ->where('path', '.*')
        ->name('proxy');
});

// ========================================
// ROTAS DE DESENVOLVIMENTO/DEBUG (OPCIONAL)
// ========================================

if (config('app.debug')) {
    /**
     * Rotas apenas em desenvolvimento para debug
     */
    Route::prefix('debug/checklist')->group(function () {

        Route::get('/test-api', function () {
            $controller = new ChecklistApiController();
            return $controller->health();
        })->name('debug.checklist.test-api');

        Route::get('/config', function () {
            return response()->json([
                'api_base_url' => 'https://carvalima.unitopconsultoria.com.br/api/v2/checklist',
                'environment' => app()->environment(),
                'debug' => config('app.debug'),
                'app_url' => config('app.url'),
                'user' => auth()->user() ?? 'não logado'
            ]);
        })->name('debug.checklist.config');
    });
}

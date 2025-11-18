<?php
/**
 * Rotas de Log de Atividades
 */
use App\Modules\Configuracoes\Controllers\Admin\LogController;
use Illuminate\Support\Facades\Route;

// Rotas de Log de Atividades
Route::prefix('log-atividades')->name('log-atividades.')->group(function () {
    Route::get('/', [LogController::class, 'index'])->name('index');
    Route::get('/dashboard', [LogController::class, 'dashboard'])->name('dashboard');
    Route::get('/export', [LogController::class, 'export'])->name('export');
    Route::get('/{log}', [LogController::class, 'show'])->name('show');
    Route::post('/cleanup', [LogController::class, 'cleanup'])->name('cleanup');
    Route::get('/api/critical-alerts', [LogController::class, 'getCriticalAlerts'])->name('critical-alerts');
});

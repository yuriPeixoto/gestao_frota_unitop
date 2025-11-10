<?php

use App\Http\Controllers\QualityController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tickets Routes
|--------------------------------------------------------------------------
|
| Rotas para o sistema de chamados/tickets de suporte
|
*/

Route::middleware(['auth', '2fa'])->group(function () {
    // Rotas de Tickets
    Route::prefix('tickets')->name('tickets.')->group(function () {
        // Listagem e Dashboard
        Route::get('/', [TicketController::class, 'index'])->name('index');

        // Criar ticket
        Route::get('/create', [TicketController::class, 'create'])->name('create');
        Route::post('/', [TicketController::class, 'store'])->name('store');

        // Visualizar ticket
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');

        // Ações no ticket
        Route::post('/{ticket}/responses', [TicketController::class, 'addResponse'])->name('add-response');
        Route::patch('/{ticket}/status', [TicketController::class, 'updateStatus'])->name('update-status');
        Route::post('/{ticket}/assign', [TicketController::class, 'assign'])->name('assign');
        Route::post('/{ticket}/estimate', [TicketController::class, 'setEstimate'])->name('set-estimate');
        Route::post('/{ticket}/watch', [TicketController::class, 'toggleWatcher'])->name('toggle-watcher');
        Route::post('/{ticket}/rate', [TicketController::class, 'rate'])->name('rate');

        // Download de anexo
        Route::get('/attachments/{attachment}/download', [TicketController::class, 'downloadAttachment'])
            ->name('download-attachment');
    });

    // Rotas da Equipe de Qualidade
    Route::prefix('quality')->name('quality.')->group(function () {
        Route::get('/', [QualityController::class, 'index'])->name('index');
        Route::post('/tickets/{ticket}/review', [QualityController::class, 'review'])->name('review');
        Route::get('/report', [QualityController::class, 'report'])->name('report');
        Route::get('/report/pdf', [QualityController::class, 'exportPdf'])->name('report.pdf');
    });
});

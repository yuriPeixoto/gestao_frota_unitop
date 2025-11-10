<?php

use App\Http\Controllers\Admin\SinistroController;
use App\Http\Controllers\Admin\UploadController;
use Illuminate\Support\Facades\Route;

// Rotas de Sinistros
Route::group(['prefix' => 'sinistros'], function () {
    // Rotas básicas de sinistros
    Route::get('/', [SinistroController::class, 'index'])->name('sinistros.index');
    Route::get('criar', [SinistroController::class, 'create'])->name('sinistros.create');
    Route::get('{sinistro}', [SinistroController::class, 'show'])->name('sinistros.show');
    Route::get('{veiculo}/categoria', [SinistroController::class, 'getCategoria'])->name('sinistros.categoria');

    Route::post('/', [SinistroController::class, 'store'])->name('sinistros.store');
    Route::get('{sinistro}/editar', [SinistroController::class, 'edit'])->name('sinistros.edit');
    Route::put('{sinistro}', [SinistroController::class, 'update'])->name('sinistros.update');
    Route::delete('{sinitro}', [SinistroController::class, 'destroy'])->name('sinistros.destroy');

    // Rota para histórico (manter compatibilidade)
    Route::post('/admin/sinistros/store-historico', [SinistroController::class, 'storeHistorico'])
        ->name('admin.sinistros.store-historico');

    // Rotas para gerenciamento de uploads de documentos
    Route::group(['prefix' => 'documentos'], function () {
        // Upload temporário
        Route::post('/upload', [UploadController::class, 'storeTemp'])
            ->name('sinistros.documentos.upload');

        // Mover para pasta de sinistro
        Route::post('/mover-para-sinistro', [UploadController::class, 'moveToSinistro'])
            ->name('sinistros.documentos.move');

        // Excluir arquivo
        Route::delete('/excluir', [UploadController::class, 'deleteFile'])
            ->name('sinistros.documentos.delete');

        // Ver arquivo
        Route::get('/arquivo/{path}', [UploadController::class, 'getFile'])
            ->name('sinistros.documentos.view');

        // Limpar arquivos temporários (acesso restrito)
        Route::get('/limpar-temp', [UploadController::class, 'cleanupTempFiles'])
            ->middleware('auth')
            ->name('sinistros.documentos.cleanup');
    });
});

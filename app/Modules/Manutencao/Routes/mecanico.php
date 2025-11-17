<?php
/**
 * Rotas de Serviços de Mecânico
 */
use App\Modules\Manutencao\Controllers\Admin\ManutencaoServicosMecanicosControlller;
use Illuminate\Support\Facades\Route;

// Manutenção Serviços Mecânico
Route::group(['prefix' => 'manutencaoservicosmecanico'], function () {
    Route::get('/', [ManutencaoServicosMecanicosControlller::class, 'index'])->name('manutencaoservicosmecanico.index');

    // Exportação (declarar rotas específicas primeiro)
    Route::get('/export-csv', [ManutencaoServicosMecanicosControlller::class, 'exportCsv'])->name('manutencaoservicosmecanico.exportCsv');
    Route::get('/export-xls', [ManutencaoServicosMecanicosControlller::class, 'exportXls'])->name('manutencaoservicosmecanico.exportXls');
    Route::get('/export-pdf', [ManutencaoServicosMecanicosControlller::class, 'exportPdf'])->name('manutencaoservicosmecanico.exportPdf');
    Route::get('/export-xml', [ManutencaoServicosMecanicosControlller::class, 'exportXml'])->name('manutencaoservicosmecanico.exportXml');

    // Rotas com parâmetros (declarar depois das específicas)
    Route::get('/{manutencaoservicosmecanico}/edit', [ManutencaoServicosMecanicosControlller::class, 'edit'])->name('manutencaoservicosmecanico.edit');
    Route::put('/{manutencaoservicosmecanico}', [ManutencaoServicosMecanicosControlller::class, 'update'])->name('manutencaoservicosmecanico.update');
    Route::get('/{ids}', [ManutencaoServicosMecanicosControlller::class, 'finalizarTodos'])->name('manutencaoservicosmecanico.finalizartodos');
});

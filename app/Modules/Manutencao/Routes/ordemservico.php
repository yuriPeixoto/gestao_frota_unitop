<?php
/**
 * Rotas de Ordem de Serviço
 */
use App\Modules\Manutencao\Controllers\Admin\OrdemServicoController;
use App\Modules\Manutencao\Controllers\Admin\OrdemServicosAuxiliarController;
use App\Modules\Manutencao\Controllers\Admin\OrdemServicoServicosController;
use App\Modules\Manutencao\Controllers\Admin\MonitoramentoManutencoesController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'ordemservicos'], function () {

    // === GET: Páginas de visualização ===
    Route::get('/', [OrdemServicoController::class, 'index'])->name('ordemservicos.index');
    Route::get('/create', [OrdemServicoController::class, 'create'])->name('ordemservicos.create');
    Route::get('/create_preventiva', [OrdemServicoController::class, 'create_preventiva'])->name('ordemservicos.create_preventiva');
    Route::get('/imprimir/{ordemservicos}', [OrdemServicoController::class, 'onImprimir'])->name('ordemservicos.imprimir');
    Route::get('/imprimirservpec/{ordemservicos}', [OrdemServicoController::class, 'onImprimirServPec'])->name('ordemservicos.onImprimirServPec');
    Route::get('/{ordemservicos}/edit', [OrdemServicoController::class, 'edit'])->name('ordemservicos.edit');
    Route::get('/{ordemservicos}/edit_preventiva', [OrdemServicoController::class, 'edit_preventiva'])->name('ordemservicos.edit_preventiva');
    Route::get('/{ordemservicos}/edit_diagnostico', [OrdemServicoController::class, 'edit_diagnostico'])->name('ordemservicos.edit_diagnostico');
    Route::get('show', [OrdemServicoController::class, 'show'])->name('ordemservicos.show');
    Route::get('/getServicosSearch', [OrdemServicoController::class, 'getServicosSearch'])->name('ordemservicos.getServicosSearch');
    Route::get('/getProdutosSearch', [OrdemServicoController::class, 'getProdutosSearch'])->name('ordemservicos.getProdutosSearch');


    // === POST: Criação e ações ===
    Route::post('/store', [OrdemServicoController::class, 'store'])->name('ordemservicos.store');
    Route::post('/cancelar-os', [OrdemServicoController::class, 'onCancelarOS'])->name('ordemservicos.cancelar-os');
    Route::post('/finalizar-os', [OrdemServicoController::class, 'onFinalizar'])->name('ordemservicos.finalizar-os');
    Route::post('/solicitar-servicos-os', [OrdemServicoController::class, 'onSolicitarServicos'])->name('ordemservicos.solicitar-servicos-os');
    Route::post('/solicitar-pecas', [OrdemServicoController::class, 'onActionSolicitarPecas'])->name('ordemservicos.solicitar-pecas');
    Route::post('/encerrar-os', [OrdemServicoController::class, 'onActionEncerrar'])->name('ordemservicos.encerrar-os');
    Route::post('/getDadosVeiculo', [OrdemServicoController::class, 'getDadosVeiculo'])->name('ordemservicos.getDadosVeiculo');
    Route::post('/carregarUnidadeProduto', [OrdemServicoController::class, 'carregarUnidadeProduto'])->name('ordemservicos.carregarUnidadeProduto');
    Route::post('/carregarKm', [OrdemServicoController::class, 'carregarKm'])->name('ordemservicos.carregarKm');
    Route::post('/inserirServicosePecas', [OrdemServicoController::class, 'inserirServicosePecas'])->name('ordemservicos.inserirServicosePecas');
    Route::post('/valorServicoxfornecedor', [OrdemServicoController::class, 'ValorServicoXFornecedor'])->name('ordemservicos.valorServicoxfornecedor');
    Route::post('/onFinalizarServico', [OrdemServicoController::class, 'onFinalizarServico'])->name('ordemservicos.onFinalizarServico');
    Route::post('/onDeletarServico', [OrdemServicoController::class, 'onYesDestroyServico'])->name('ordemservicos.onDeletarServico');
    Route::post('/onDeletarPecas', [OrdemServicoController::class, 'onDeletarPecas'])->name('ordemservicos.onDeletarPecas');
    Route::post('/onimprimirkm', [OrdemServicoController::class, 'onimprimirkm'])->name('ordemservicos.onimprimirkm');
    Route::post('/reabriros', [OrdemServicoController::class, 'reabirOS'])->name('ordemservicos.reabriros');
    Route::post('/validarKMAtual', [OrdemServicoController::class, 'validarKMAtual'])->name('ordemservicos.validarKMAtual');
    Route::post('/getServicos', [OrdemServicoController::class, 'getServicosBorracharia'])->name('ordemservicos.getServicos');
    Route::post('/getProdutos', [OrdemServicoController::class, 'getProdutosBorracharia'])->name('ordemservicos.getProdutos');
    Route::post('/marcar', [OrdemServicoController::class, 'marcarMarcacao'])->name('ordemservicos.marcar');
    Route::post('/marcar-todos', [OrdemServicoController::class, 'marcarTodosMarcacoes'])->name('ordemservicos.marcar-todos');
    Route::post('/getManutencao', [OrdemServicoController::class, 'getManutencao'])->name('ordemservicos.getManutencao');


    // === PUT: Atualizações - CORRIGIDO ===
    Route::put('/{ordemservicos}/update', [OrdemServicoController::class, 'update'])->name('ordemservicos.update');
    Route::put('/{ordemservicos}/update_preventiva', [OrdemServicoController::class, 'update_preventiva'])->name('ordemservicos.update_preventiva');
    Route::put('/{ordemservicos}/update_diagnostico', [OrdemServicoController::class, 'update_diagnostico'])->name('ordemservicos.update_diagnostico');

    // === DELETE: Exclusão ===
    Route::delete('/{ordemservicos}', [OrdemServicoController::class, 'destroy'])->name('ordemservicos.destroy');
});

// Rotas para OrdemServico Preventiva (separadas para evitar conflitos)
Route::group(['prefix' => 'ordemservicos_preventiva'], function () {
    Route::get('/{ordemservicos}/edit_preventiva', [OrdemServicoController::class, 'edit_preventiva'])
        ->name('ordemservicos.edit_preventiva');
    Route::put('/{ordemservicos}', [OrdemServicoController::class, 'update_preventiva'])
        ->name('ordemservicos.update_preventiva');

    Route::post('/store_preventiva', [OrdemServicoController::class, 'store_preventiva'])->name('ordemservicos.store_preventiva');
    Route::post('/cancelar-os', [OrdemServicoController::class, 'onCancelarOS'])->name('ordemservicos.cancelar-os');
    Route::post('/finalizar-os', [OrdemServicoController::class, 'onFinalizar'])->name('ordemservicos.finalizar-os');
    Route::post('/solicitar-pecas', [OrdemServicoController::class, 'onActionSolicitarPecas'])
        ->name('ordemservicos.solicitar-pecas');
    Route::post('/encerrar-os', [OrdemServicoController::class, 'onActionEncerrar'])->name('ordemservicos.encerrar-os');
});

// Rotas para Lançamento de NF de Serviço
Route::group(['prefix' => 'ordemservicoservicos'], function () {
    Route::get('/', [OrdemServicoServicosController::class, 'index'])
        ->name('ordemservicoservicos.index');

    // Exportação
    Route::get('/export-pdf', [OrdemServicoServicosController::class, 'exportPdf'])
        ->name('ordemservicoservicos.exportPdf');
    Route::get('/export-csv', [OrdemServicoServicosController::class, 'exportCsv'])
        ->name('ordemservicoservicos.exportCsv');
    Route::get('/export-xls', [OrdemServicoServicosController::class, 'exportXls'])
        ->name('ordemservicoservicos.exportXls');
    Route::get('/export-xml', [OrdemServicoServicosController::class, 'exportXml'])
        ->name('ordemservicoservicos.exportXml');

    // Lançamento de NF
    Route::post('/lancar-nf', [OrdemServicoServicosController::class, 'lancarNF'])
        ->name('ordemservicoservicos.lancar-nf');
    Route::post('/gravar-nf', [OrdemServicoServicosController::class, 'gravarNF'])
        ->name('ordemservicoservicos.gravar-nf');
});

// Rotas para Ordem de Serviço Auxiliares
Route::group(['prefix' => 'ordemservicoauxiliares'], function () {
    Route::get('/', [OrdemServicosAuxiliarController::class, 'index'])->name('ordemservicoauxiliares.index');
    Route::get('/create', [OrdemServicosAuxiliarController::class, 'create'])->name('ordemservicoauxiliares.create');
    Route::post('/store', [OrdemServicosAuxiliarController::class, 'store'])->name('ordemservicoauxiliares.store');
    Route::get('/{ordemservicoauxiliares}/edit', [OrdemServicosAuxiliarController::class, 'edit'])
        ->name('ordemservicoauxiliares.edit');
    Route::put('/{ordemservicoauxiliares}', [OrdemServicosAuxiliarController::class, 'update'])
        ->name('ordemservicoauxiliares.update');
    Route::delete('/{ordemservicoauxiliares}', [OrdemServicosAuxiliarController::class, 'destroy'])
        ->name('ordemservicoauxiliares.destroy');
    Route::post('gerar-os-auxiliar', [OrdemServicosAuxiliarController::class, 'onGerarOsAuxiliar'])->name('ordemservicoauxiliares.gerar-os-auxiliar');
    Route::post('onimprimir-historico', [OrdemServicosAuxiliarController::class, 'onimprimirhistorico'])->name('ordemservicoauxiliares.onimprimir-historico');
    Route::get('{departamento}', [OrdemServicosAuxiliarController::class, 'show'])->name('ordemservicoauxiliares.show');

    // Rota para impressão da OS
    Route::get('/admin/ordemservicoauxiliares/{id}/imprimir', [OrdemServicosAuxiliarController::class, 'imprimir'])
        ->name('admin.ordemservicoauxiliares.imprimir');

    Route::post('/validarKMAtual', [OrdemServicosAuxiliarController::class, 'validarKMAtual'])->name('ordemservicoauxiliares.validarKMAtual');
    Route::get('/veiculos/{id}/ultimo-km', [OrdemServicosAuxiliarController::class, 'buscarKmVeiculo'])
        ->name('ordemservicoauxiliares.ultimoKm');
});

// Monitoramento das Manutenções
Route::group(['prefix' => 'monitoramentoDasManutencoes'], function () {
    Route::get('/', [MonitoramentoManutencoesController::class, 'index'])->name('monitoramentoDasManutencoes.index');
});

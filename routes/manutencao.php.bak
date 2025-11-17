<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MonitoramentoManutencoesController;
use App\Http\Controllers\Admin\ManutencaoController;
use App\Http\Controllers\Admin\ServicoController;
use App\Http\Controllers\Admin\OrdemServicoServicosController;
use App\Http\Controllers\Admin\OrdemServicoController;
use App\Http\Controllers\Admin\MotoristaController;
use App\Http\Controllers\Admin\PreOrdemListagemNovaController;
use App\Http\Controllers\Admin\VeiculoController;
// use App\Http\Controllers\Admin\OrdemServicoCanceladasController;
use App\Http\Controllers\Admin\OrdemServicosAuxiliarController;
use App\Http\Controllers\Admin\ManutencaoKmVeiculoComodatoController;
use App\Http\Controllers\Admin\ManutencaoServicosMecanicosControlller;

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


Route::group(['prefix' => 'manutencao'], function () {

    Route::group(['prefix' => 'manutencoes'], function () {
        Route::get('/criar', [ManutencaoController::class, 'create'])->name('manutencoes.create');
        Route::post('/', [ManutencaoController::class, 'store'])->name('manutencoes.store');
        Route::get('/', [ManutencaoController::class, 'index'])->name('manutencoes.index');
        Route::get('/{id}', [ManutencaoController::class, 'edit'])->name('manutencoes.edit');
        Route::put('/{id}', [ManutencaoController::class, 'update'])->name('manutencoes.update');
    });

    Route::group(['prefix' => 'servicos'], function () {
        Route::get('/', [ServicoController::class, 'index'])->name('servicos.index');
        Route::get('/criar', [ServicoController::class, 'create'])->name('servicos.create');
        Route::get('/{id}', [ServicoController::class, 'edit'])->name('servicos.edit');
        // Rotas para busca de registros
        Route::get('servicos/search', [ServicoController::class, 'search'])->name('servicos.search');
        Route::get('servicos/single/{id}', [ServicoController::class, 'single'])->name('servicos.single');
    });


    Route::group(['prefix' => 'monitoramento-das-manutencoes'], function () {
        Route::get('/', [MonitoramentoManutencoesController::class, 'index'])
            ->name('monitoramentoDasManutencoes.index');
    });
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

Route::group(['prefix' => 'manutencao'], function () {

    Route::group(['prefix' => 'manutencoes'], function () {
        Route::get('/criar', [ManutencaoController::class, 'create'])->name('manutencoes.create');
        Route::post('/', [ManutencaoController::class, 'store'])->name('manutencoes.store');
        Route::get('/', [ManutencaoController::class, 'index'])->name('manutencoes.index');
        Route::get('/{id}', [ManutencaoController::class, 'edit'])->name('manutencoes.edit');
        Route::put('/{id}', [ManutencaoController::class, 'update'])->name('manutencoes.update');
    });

    Route::group(['prefix' => 'servicos'], function () {
        Route::get('/', [ServicoController::class, 'index'])->name('servicos.index');
        Route::get('/criar', [ServicoController::class, 'create'])->name('servicos.create');
        Route::get('/{id}', [ServicoController::class, 'edit'])->name('servicos.edit');
    });


    Route::group(['prefix' => 'monitoramento-das-manutencoes'], function () {
        Route::get('/', [MonitoramentoManutencoesController::class, 'index'])->name('monitoramentoDasManutencoes.index');
    });
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

Route::group(['prefix' => 'manutencao'], function () {

    Route::group(['prefix' => 'manutencoes'], function () {
        Route::get('/criar', [ManutencaoController::class, 'create'])->name('manutencoes.create');
        Route::post('/', [ManutencaoController::class, 'store'])->name('manutencoes.store');
        Route::get('/', [ManutencaoController::class, 'index'])->name('manutencoes.index');
        Route::get('/{id}', [ManutencaoController::class, 'edit'])->name('manutencoes.edit');
        Route::put('/{id}', [ManutencaoController::class, 'update'])->name('manutencoes.update');
    });

    Route::group(['prefix' => 'servicos'], function () {
        Route::get('/', [ServicoController::class, 'index'])->name('servicos.index');
        Route::get('/criar', [ServicoController::class, 'create'])->name('servicos.create');
        Route::get('/{id}', [ServicoController::class, 'edit'])->name('servicos.edit');
    });


    Route::group(['prefix' => 'monitoramento-das-manutencoes'], function () {
        Route::get('/', [MonitoramentoManutencoesController::class, 'index'])->name('monitoramentoDasManutencoes.index');
    });
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

//Tive que dividir o update e o edit porque estava dando conflito de rotas da Corretiva com a Preventiva - Marcelo Augusto 20/03/2025
Route::group(['prefix' => 'ordemservicos_preventiva'], function () {
    Route::get('/{ordemservicos}/edit_preventiva', [OrdemServicoController::class, 'edit_preventiva'])
        ->name('ordemservicos.edit_preventiva');
    Route::put('/{ordemservicos}', [OrdemServicoController::class, 'update_preventiva'])
        ->name('ordemservicos.update_preventiva');

    Route::post('/store_preventiva', [OrdemServicoController::class, 'store_preventiva'])->name('ordemservicos.store_preventiva');
    Route::post('/cancelar-os', [OrdemServicoController::class, 'onCancelarOS'])->name('ordemservicos.cancelar-os');
    Route::post('/finalizar-os', [OrdemServicoController::class, 'onFinalizar'])->name('ordemservicos.finalizar-os');
    // Route::post('/solicitar-servicos-os', [OrdemServicoController::class, 'onSolicitarServicos'])
    //     ->name('ordemservicos.solicitar-servicos-os');
    Route::post('/solicitar-pecas', [OrdemServicoController::class, 'onActionSolicitarPecas'])
        ->name('ordemservicos.solicitar-pecas');
    Route::post('/encerrar-os', [OrdemServicoController::class, 'onActionEncerrar'])->name('ordemservicos.encerrar-os');
});

// Route::group(['prefix' => 'ordemservicocanceladas'], function () {
//     Route::get('/', [OrdemServicoCanceladasController::class, 'index'])->name('ordemservicocanceladas.index');
//     Route::get('/{ordemservicocanceladas}/retornaros', [OrdemServicoCanceladasController::class, 'onRetornarOSFinalizada'])
//         ->name('ordemservicocanceladas.retornaros');
//     Route::get('/export-pdf', [OrdemServicoCanceladasController::class, 'exportPdf'])
//         ->name('ordemservicocanceladas.exportPdf');

//     Route::delete('/{ordemservicocanceladas}', [OrdemServicoCanceladasController::class, 'destroy'])
//         ->name('ordemservicocanceladas.destroy');
// });

Route::group(['prefix' => 'monitoramentoDasManutencoes'], function () {
    Route::get('/', [MonitoramentoManutencoesController::class, 'index'])->name('monitoramentoDasManutencoes.index');
});

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
    // Route::get('ordemservicoauxiliares/{id}', [OrdemServicosAuxiliarController::class, 'show'])->name('ordemservicoauxiliares.show');
    Route::get('{departamento}', [OrdemServicosAuxiliarController::class, 'show'])->name('ordemservicoauxiliares.show');

    // Rota para impressão da OS
    Route::get('/admin/ordemservicoauxiliares/{id}/imprimir', [OrdemServicosAuxiliarController::class, 'imprimir'])
        ->name('admin.ordemservicoauxiliares.imprimir');

    Route::post('/validarKMAtual', [OrdemServicosAuxiliarController::class, 'validarKMAtual'])->name('ordemservicoauxiliares.validarKMAtual');
    Route::get('/veiculos/{id}/ultimo-km', [OrdemServicosAuxiliarController::class, 'buscarKmVeiculo'])
        ->name('ordemservicoauxiliares.ultimoKm');
});

Route::group(['prefix' => 'manutencao'], function () {

    Route::group(['prefix' => 'manutencoes'], function () {
        Route::get('/criar', [ManutencaoController::class, 'create'])->name('manutencoes.create');
        Route::post('/', [ManutencaoController::class, 'store'])->name('manutencoes.store');
        Route::get('/', [ManutencaoController::class, 'index'])->name('manutencoes.index');
        Route::get('/{id}', [ManutencaoController::class, 'edit'])->name('manutencoes.edit');
        Route::put('/{id}', [ManutencaoController::class, 'update'])->name('manutencoes.update');
    });

    Route::group(['prefix' => 'servicos'], function () {
        Route::get('/', [ServicoController::class, 'index'])->name('servicos.index');
        Route::get('/criar', [ServicoController::class, 'create'])->name('servicos.create');
        Route::get('/{id}', [ServicoController::class, 'edit'])->name('servicos.edit');
    });

    /*Route::group(['prefix' => 'monitoramento-das-manutencoes'], function () {
        Route::get('/', [MonitoramentoManutencoesController::class, 'index'])
            ->name('monitoramentoDasManutencoes.index');
    });*/
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

// Manutencao servicos mecanico
Route::group(['prefix' => 'manutencaopreordemserviconova'], function () {
    Route::get('/', [PreOrdemListagemNovaController::class, 'index'])->name('manutencaopreordemserviconova.index');
    Route::get('/{id}/preventiva', [PreOrdemListagemNovaController::class, 'preventiva'])->name('manutencaopreordemserviconova.preventiva');
    Route::get('/{id}/historico', [PreOrdemListagemNovaController::class, 'historico'])->name('manutencaopreordemserviconova.historico');
    Route::get('/create', [PreOrdemListagemNovaController::class, 'create'])->name('manutencaopreordemserviconova.create');
    Route::get('/{id}/edit', [PreOrdemListagemNovaController::class, 'edit'])->name('manutencaopreordemserviconova.edit');

    Route::post('/assumirpreos/{id}', [PreOrdemListagemNovaController::class, 'assumirPreOs'])->name('manutencaopreordemserviconova.assumirpreos');
    Route::get('/gerarpreventiva', [PreOrdemListagemNovaController::class, 'gerarPreventiva'])->name('manutencaopreordemserviconova.gerarpreventiva');
    Route::post('/gerarcorretiva/{id}', [PreOrdemListagemNovaController::class, 'gerarCorretiva'])->name('manutencaopreordemserviconova.gerarcorretiva');
    Route::post('/finalizaros/{id}', [PreOrdemListagemNovaController::class, 'finalizarOs'])->name('manutencaopreordemserviconova.finalizaros');
    Route::post('/getInfoVeiculo', [PreOrdemListagemNovaController::class, 'getInfoVeiculo'])->name('manutencaopreordemserviconova.getInfoVeiculo');
    Route::post('/getTelefoneMotorista', [PreOrdemListagemNovaController::class, 'getTelefoneMotorista'])->name('manutencaopreordemserviconova.getTelefoneMotorista');
    Route::post('/imprimir', [PreOrdemListagemNovaController::class, 'onImprimir'])->name('manutencaopreordemserviconova.imprimir');

    Route::put('/{id}', [PreOrdemListagemNovaController::class, 'update'])->name('manutencaopreordemserviconova.update');
    Route::post('/', [PreOrdemListagemNovaController::class, 'store'])->name('manutencaopreordemserviconova.store');
    Route::delete('/{id}', [PreOrdemListagemNovaController::class, 'destroy'])->name('manutencaopreordemserviconova.destroy');
});

// Abastecimento para Manutencao KM Veiculo Comodato
Route::group(['prefix' => 'manutencaokmveiculocomodato'], function () {
    Route::get('/', [ManutencaoKmVeiculoComodatoController::class, 'index'])->name('manutencaokmveiculocomodato.index');

    // CRUD
    Route::get('/create', [ManutencaoKmVeiculoComodatoController::class, 'create'])->name('manutencaokmveiculocomodato.create');
    Route::post('/store', [ManutencaoKmVeiculoComodatoController::class, 'store'])->name('manutencaokmveiculocomodato.store');

    Route::delete('/{id}', [ManutencaoKmVeiculoComodatoController::class, 'destroy'])->name('manutencaokmveiculocomodato.destroy');

    // Exportação
    Route::get('/export-csv', [ManutencaoKmVeiculoComodatoController::class, 'exportCsv'])->name('manutencaokmveiculocomodato.exportCsv');
    Route::get('/export-xls', [ManutencaoKmVeiculoComodatoController::class, 'exportXls'])->name('manutencaokmveiculocomodato.exportXls');
    Route::get('/export-pdf', [ManutencaoKmVeiculoComodatoController::class, 'exportPdf'])->name('manutencaokmveiculocomodato.exportPdf');
    Route::get('/export-xml', [ManutencaoKmVeiculoComodatoController::class, 'exportXml'])->name('manutencaokmveiculocomodato.exportXml');
});

// Manutencao servicos mecanico
Route::group(['prefix' => 'manutencaoservicosmecanico'], function () {
    Route::get('/', [ManutencaoServicosMecanicosControlller::class, 'index'])->name('manutencaoservicosmecanico.index');

    // Primeiro declare todas as rotas específicas (sem parâmetros dinâmicos)
    // Exportação
    Route::get('/export-csv', [ManutencaoServicosMecanicosControlller::class, 'exportCsv'])->name('manutencaoservicosmecanico.exportCsv');
    Route::get('/export-xls', [ManutencaoServicosMecanicosControlller::class, 'exportXls'])->name('manutencaoservicosmecanico.exportXls');
    Route::get('/export-pdf', [ManutencaoServicosMecanicosControlller::class, 'exportPdf'])->name('manutencaoservicosmecanico.exportPdf');
    Route::get('/export-xml', [ManutencaoServicosMecanicosControlller::class, 'exportXml'])->name('manutencaoservicosmecanico.exportXml');

    // Depois declare as rotas com parâmetros
    Route::get('/{manutencaoservicosmecanico}/edit', [ManutencaoServicosMecanicosControlller::class, 'edit'])->name('manutencaoservicosmecanico.edit');
    Route::put('/{manutencaoservicosmecanico}', [ManutencaoServicosMecanicosControlller::class, 'update'])->name('manutencaoservicosmecanico.update');
    Route::get('/{ids}', [ManutencaoServicosMecanicosControlller::class, 'finalizarTodos'])->name('manutencaoservicosmecanico.finalizartodos');
});

Route::prefix('api')->group(function () {
    // Fornecedor
    Route::get('/ordemservico/search', [OrdemServicoController::class, 'search'])->name('api.ordemservico.search');
    Route::get('/ordemservico/single/{id}', [OrdemServicoController::class, 'getById'])->name('api.ordemservico.single');
});

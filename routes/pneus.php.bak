<?php

use App\Http\Controllers\Admin\BorrachariaOSController;
use App\Http\Controllers\Admin\CalibragemPneusController;
use App\Http\Controllers\Admin\ContagemPneuCotroller;
use App\Http\Controllers\Admin\DescartePneuController;
use App\Http\Controllers\Admin\DescarteTipoController;
use App\Http\Controllers\Admin\EnvioeRecebimento;
use App\Http\Controllers\Admin\ManutencaoPneusController;
use App\Http\Controllers\Admin\ManutencaoPneusEntradaController;
use App\Http\Controllers\Admin\ModeloPneuController;
use App\Http\Controllers\Admin\MovimentacaoPneusController;
use App\Http\Controllers\Admin\NotaFiscalEntradaController;
use App\Http\Controllers\Admin\PneuController;
use App\Http\Controllers\Admin\PneuHistoricoController;
use App\Http\Controllers\Admin\PneusDepositoController;
use App\Http\Controllers\Admin\RequisicaoPneusVendasController;
use App\Http\Controllers\Admin\RequisicaoPneusVendasSaidaController;
use App\Http\Controllers\Admin\SaidaPneuController;
use App\Http\Controllers\Admin\ServicoController;
use App\Http\Controllers\Admin\TipoDescarteController;
use App\Http\Controllers\Admin\TransferenciaPneusController;
use App\Http\Controllers\Admin\VeiculoController;
use Illuminate\Support\Facades\Route;

// Rotas de Pneus
Route::group(['prefix' => 'pneus'], function () {
    // Borracharia - Lista OS
    Route::group(['prefix' => 'borracharia-os'], function () {
        Route::get('/', [BorrachariaOSController::class, 'index'])->name('pneus.borracharia.index');
        Route::get('/{id}/imprimir', [BorrachariaOSController::class, 'print'])->name('pneus.borracharia.print');

        Route::post('/{id}/assumir', [BorrachariaOSController::class, 'assume'])->name('pneus.borracharia.assume');
        Route::post('/{id}/reabrir', [BorrachariaOSController::class, 'reopen'])->name('pneus.borracharia.reopen');
        Route::post('/{id}/cancelar', [BorrachariaOSController::class, 'cancel'])->name('pneus.borracharia.cancel');

        Route::delete('/{id}/excluir', [BorrachariaOSController::class, 'delete'])->name('pneus.borracharia.delete');
    });

    // Saída de Pneus - Lista de Requisições
    Route::group(['prefix' => 'saida-pneus'], function () {
        Route::get('/', [SaidaPneuController::class, 'index'])->name('saidaPneus.index');
        Route::get('/{id}/editar', [SaidaPneuController::class, 'edit'])->name('saidaPneus.edit');
        Route::put('/{id}', [SaidaPneuController::class, 'update'])->name('saidaPneus.update');
        Route::get('/{id}/visualizar', [SaidaPneuController::class, 'visualizar'])->name('saidaPneus.visualizar');
        Route::post('/{id}/assumir-baixa', [SaidaPneuController::class, 'assumirBaixa'])->name('saidaPneus.assumir-baixa');
        Route::post('/{id}/baixar-pneus', [SaidaPneuController::class, 'baixarPneus'])->name('saidaPneus.baixar-pneus');
        Route::get('/{id}/estornar', [SaidaPneuController::class, 'estornar'])->name('saidaPneus.estornar');
        Route::get('/{id}/finalizar', [SaidaPneuController::class, 'finalizarSaida'])->name('saidaPneus.finalizar');
        Route::get('/{id}/imprimir', [SaidaPneuController::class, 'imprimir'])->name('saidaPneus.imprimir');

        // Novas rotas AJAX para funcionalidades do legado
        Route::post('/carregar-pneus', [SaidaPneuController::class, 'carregarPneus'])->name('saidaPneus.carregar-pneus');
        Route::post('/ajax/carregar-pneus', [SaidaPneuController::class, 'carregarPneus'])->name('saidaPneus.ajax.carregar-pneus');
        Route::post('/adicionar-item-detalhe', [SaidaPneuController::class, 'adicionarItemDetalhe'])->name('saidaPneus.adicionar-item-detalhe');
        Route::post('/ajax/adicionar-item', [SaidaPneuController::class, 'adicionarItemDetalhe'])->name('saidaPneus.ajax.adicionar-item');
        Route::delete('/estornar-modelo/{id}', [SaidaPneuController::class, 'estornarModelo'])->name('saidaPneus.estornar-modelo');
        Route::post('/editar-detalhe-modelo', [SaidaPneuController::class, 'editarDetalheModelo'])->name('saidaPneus.editar-detalhe-modelo');
        Route::post('/ajax/editar-detalhe', [SaidaPneuController::class, 'editarDetalheModelo'])->name('saidaPneus.ajax.editar-detalhe');
        Route::post('/validar-requisicao-terceiro', [SaidaPneuController::class, 'validarRequisicaoTerceiro'])->name('saidaPneus.validar-terceiro');
        Route::post('/limpar-formulario', [SaidaPneuController::class, 'limparFormulario'])->name('saidaPneus.limpar-formulario');
        Route::get('/validar-baixa-iniciada/{id}', [SaidaPneuController::class, 'validarBaixaIniciada'])->name('saidaPneus.validar-baixa-iniciada');
        Route::get('/{id}/obter-dados-edicao/{idModelo}', [SaidaPneuController::class, 'obterDadosEdicao'])->name('saidaPneus.obter-dados-edicao');

        // Export routes
        Route::get('/export/csv', [SaidaPneuController::class, 'exportCsv'])->name('saidaPneus.exportCsv');
        Route::get('/export/xls', [SaidaPneuController::class, 'exportXls'])->name('saidaPneus.exportXls');
        Route::get('/export/pdf', [SaidaPneuController::class, 'exportPdf'])->name('saidaPneus.exportPdf');
        Route::get('/export/xml', [SaidaPneuController::class, 'exportXml'])->name('saidaPneus.exportXml');
    });

    // Histórico de Vida dos Pneus
    Route::group(['prefix' => 'historico'], function () {
        Route::get('/', [PneuHistoricoController::class, 'index'])->name('pneuhistorico.index');
        Route::get('/{id}', [PneuHistoricoController::class, 'show'])->name('pneuhistorico.show');
        Route::get('/{id}/export/pdf', [PneuHistoricoController::class, 'exportPdf'])->name('pneuhistorico.exportPdf');
    });

    // Rotas que NÃO aceitam parâmetros (devem vir PRIMEIRO)
    Route::get('/', [PneuController::class, 'index'])->name('pneus.index');
    Route::get('criar', [PneuController::class, 'create'])->name('pneus.create');
    Route::post('/', [PneuController::class, 'store'])->name('pneus.store');

    // Rotas com parâmetros específicos (devem vir ANTES das rotas genéricas)
    Route::get('/info/{pneus}', [PneuController::class, 'getInfoPneu'])->name('pneus.get-info');

    // API para obter todas as informações do pneu
    Route::get('/api/{pneus}', [PneuController::class, 'apiPneu'])->name('pneus.apipneu');
    Route::get('/api/lista/{ids}', [PneuController::class, 'pneus.apiListaPorIds']);
    Route::get('/api', [PneuController::class, 'apiLista'])->name('pneus.api-lista');

    Route::get('/editar/{pneus}', [PneuController::class, 'edit'])->name('pneus.edit');

    // Rotas de modificação (devem vir por último)
    Route::put('/{pneus}', [PneuController::class, 'update'])->name('pneus.update');
    Route::delete('/{pneus}', [PneuController::class, 'destroy'])->name('pneus.destroy');
});

// API para pesquisa de NF Entrada
Route::prefix('api')->group(function () {
    // Pesquisa Nota fiscal Entrada do Pneu
    Route::get('/api/nfentradapneu/search', [NotaFiscalEntradaController::class, 'search'])->name('api.nfentradapneu.search');
    Route::get('/api/nfentradapneu/single/{id}', [NotaFiscalEntradaController::class, 'getById'])->name('api.nfentradapneu.single');
});

// Rotas de Transferencia de Pneus
Route::group(['prefix' => 'transferenciapneus'], function () {
    // Rota para buscar por transferência e modelo específico (deve vir antes da rota genérica)
    Route::get('/por-modelo/{transferenciaId}/{modeloId}', [TransferenciaPneusController::class, 'getPneusByModelo'])
        ->name('transferenciapneus.getPneusByModeloEspecifico');

    Route::get('/finalizar/{transferenciaId}', [TransferenciaPneusController::class, 'onYesFinalizarBaixaPneu'])
        ->name('transferenciapneus.onYesFinalizarBaixaPneu');

    // Outras rotas...
    Route::get('/', [TransferenciaPneusController::class, 'index'])->name('transferenciapneus.index');
    Route::get('criar', [TransferenciaPneusController::class, 'create'])->name('transferenciapneus.create');
    Route::post('/', [TransferenciaPneusController::class, 'store'])->name('transferenciapneus.store');
    Route::get('{transferenciapneus}/editar', [TransferenciaPneusController::class, 'edit'])->name('transferenciapneus.edit');
    Route::put('{transferenciapneus}', [TransferenciaPneusController::class, 'update'])->name('transferenciapneus.update');
    Route::delete('{transferenciapneus}', [TransferenciaPneusController::class, 'destroy'])->name('transferenciapneus.destroy');
});

// Rotas de aprovacao de pneus para venda
Route::group(['prefix' => 'requisicaopneusvendas'], function () {
    Route::get('/', [RequisicaoPneusVendasController::class, 'index'])->name('requisicaopneusvendas.index');
    Route::get('{id}/dados', [RequisicaoPneusVendasController::class, 'getDados'])->name('requisicaopneusvendas.dados');
    Route::post('/{requisicaoId}/{acao}', [RequisicaoPneusVendasController::class, 'onAction'])
        ->whereIn('acao', ['aprovar', 'reprovar'])
        ->name('requisicaopneusvendas.action');

    // Rota para buscar pneus para modal de valores
    Route::get('{id}/valores-venda', [RequisicaoPneusVendasController::class, 'obterPneusParaValores'])->name('requisicaopneusvendas.pneus-valores');

    // Rota para salvar valores via modal
    Route::post('{id}/atualizar-valores', [RequisicaoPneusVendasController::class, 'atualizarValores'])->name('requisicao-pneus-vendas.atualizar-valores');
});

// Rotas de saida de pneus para venda
Route::group(['prefix' => 'requisicaopneusvendassaida'], function () {
    Route::get('/', [RequisicaoPneusVendasSaidaController::class, 'index'])->name('requisicaopneusvendassaida.index');
    Route::get('{requisicaopneusvendassaida}/editar', [RequisicaoPneusVendasSaidaController::class, 'edit'])->name('requisicaopneusvendassaida.edit');
    Route::get('/imprimir/{requisicaopneusvendassaida}', [RequisicaoPneusVendasSaidaController::class, 'imprimir'])->name('requisicaopneusvendassaida.imprimir');
    Route::get('/finalizar/{requisicaopneusvendassaida}', [RequisicaoPneusVendasSaidaController::class, 'onFinalizarSaida'])->name('requisicaopneusvendassaida.finalizar');
    Route::get('/por-modelo/{requisicaopneusvendassaida}/{modeloId}', [RequisicaoPneusVendasSaidaController::class, 'getPneusByModelo'])
        ->name('requisicaopneusvendassaida.getPneusByModeloEspecifico');

    // Exportação
    Route::get('/export-csv', [RequisicaoPneusVendasSaidaController::class, 'exportCsv'])->name('requisicaopneusvendassaida.exportCsv');
    Route::get('/export-xls', [RequisicaoPneusVendasSaidaController::class, 'exportXls'])->name('requisicaopneusvendassaida.exportXls');
    Route::get('/export-pdf', [RequisicaoPneusVendasSaidaController::class, 'exportPdf'])->name('requisicaopneusvendassaida.exportPdf');
    Route::get('/export-xml', [RequisicaoPneusVendasSaidaController::class, 'exportXml'])->name('requisicaopneusvendassaida.exportXml');

    Route::put('{requisicaopneusvendassaida}', [RequisicaoPneusVendasSaidaController::class, 'update'])->name('requisicaopneusvendassaida.update');
    Route::post('/cancelar/{requisicaopneusvendassaida}', [RequisicaoPneusVendasSaidaController::class, 'onCancel'])->name('requisicaopneusvendassaida.cancel');
});

// Rotas de contagem de pneus
Route::group(['prefix' => 'contagempneus'], function () {
    Route::get('/', [ContagemPneuCotroller::class, 'index'])->name('contagempneus.index');
    Route::get('criar', [ContagemPneuCotroller::class, 'create'])->name('contagempneus.create');
    Route::get('{contagempneus}', [ContagemPneuCotroller::class, 'show'])->name('contagempneus.show');

    Route::post('/', [ContagemPneuCotroller::class, 'store'])->name('contagempneus.store');
    Route::get('{contagempneus}/editar', [ContagemPneuCotroller::class, 'edit'])->name('contagempneus.edit');
    Route::put('{contagempneus}', [ContagemPneuCotroller::class, 'update'])->name('contagempneus.update');

    Route::delete('{contagempneus}', [ContagemPneuCotroller::class, 'destroy'])
        ->name('contagempneus.destroy');
});

// Rotas de Envio de pneus para manutenção
Route::group(['prefix' => 'manutencaopneus'], function () {
    Route::get('/', [ManutencaoPneusController::class, 'index'])->name('manutencaopneus.index');
    Route::get('criar', [ManutencaoPneusController::class, 'create'])->name('manutencaopneus.create');
    Route::post('/', [ManutencaoPneusController::class, 'store'])->name('manutencaopneus.store');
    Route::get('imprimir/{manutencaopneus}', [ManutencaoPneusController::class, 'onImprimir'])->name('manutencaopneus.imprimir');
    Route::get('{manutencaopneus}/editar', [ManutencaoPneusController::class, 'edit'])->name('manutencaopneus.edit');
    Route::get('{manutencaopneus}/assumir', [ManutencaoPneusController::class, 'onAssumir'])->name('manutencaopneus.assumir');
    Route::put('{manutencaopneus}', [ManutencaoPneusController::class, 'update'])->name('manutencaopneus.update');
    Route::delete('{manutencaopneus}', [ManutencaoPneusController::class, 'destroy'])->name('manutencaopneus.destroy');
    Route::get('/movimentacao/{id}', [ManutencaoPneusController::class, 'getStatus'])->name('manutencaopneus.getStatus');
    Route::put('/aprovar/{id}', [ManutencaoPneusController::class, 'aprovar'])->name('manutencaopneus.aprovar');
    Route::get('/download/{arquivo}', [ManutencaoPneusController::class, 'download'])->name('manutencaopneus.download');
});

// Rotas da API
Route::prefix('api')->group(function () {
    Route::get('/modelopneu/search', [ModeloPneuController::class, 'search'])->name('api.modelopneu.search');
    Route::get('/modelopneu/single/{id}', [ModeloPneuController::class, 'getById'])->name('api.modelopneu.single');

    Route::get('/pneu/search', [PneuController::class, 'search'])->name('api.pneu.search');
    Route::get('/pneu/single/{id}', [PneuController::class, 'getById'])->name('api.pneu.single');
});

// Rotas de chegada dos pneus da manutenção
Route::group(['prefix' => 'manutencaopneusentrada', 'as' => 'manutencaopneusentrada.'], function () {
    // Rotas API (colocadas ANTES das rotas dinâmicas para evitar colisões)
    Route::prefix('api')->as('api.')->group(function () {
        Route::get('/{manutencaopneusentrada}', [ManutencaoPneusEntradaController::class, 'onObterManutencaoPneu'])
            ->name('obter');
        Route::get('/desenho/{manutencaopneusentrada}', [ManutencaoPneusEntradaController::class, 'onGetDesenhoPneu'])
            ->name('desenho');
        Route::get('/pneus-search', [ManutencaoPneusEntradaController::class, 'searchPneusDiagnostico'])
            ->name('pneus-search');
    });

    // Rotas de visualização
    Route::get('/', [ManutencaoPneusEntradaController::class, 'index'])->name('index');
    Route::get('criar', [ManutencaoPneusEntradaController::class, 'create'])->name('create');
    Route::get('/{id}/checklist/{nf_entrada}', [ManutencaoPneusEntradaController::class, 'checklist'])
        ->name('checklist');
    Route::get('{manutencaopneusentrada}/editar', [ManutencaoPneusEntradaController::class, 'edit'])
        ->name('edit');

    // Rotas de ação
    Route::post('/', [ManutencaoPneusEntradaController::class, 'store'])->name('store');
    Route::post('/checklist', [ManutencaoPneusEntradaController::class, 'checklist_store'])->name('checklist.store');
    Route::put('{manutencaopneusentrada}', [ManutencaoPneusEntradaController::class, 'update'])
        ->name('update');
    Route::delete('{manutencaopneusentrada}', [ManutencaoPneusEntradaController::class, 'destroy'])
        ->name('destroy');
});

// Rotas de movimentação de pmneus
Route::group(['prefix' => 'movimentacaopneus', 'name' => 'admin.movimentacaopneus.'], function () {

    Route::get('/', [MovimentacaoPneusController::class, 'index'])->name('movimentacaopneus.index');
    Route::post('/get-ordemservico-data', [MovimentacaoPneusController::class, 'getOrdemServicoData'])->name('movimentacaopneus.get-ordemservico-data');
    Route::post('/get-pneu-data', [MovimentacaoPneusController::class, 'getPneuData'])->name('movimentacaopneus.get-pneu-data');
    Route::post('/salvar-dados', [MovimentacaoPneusController::class, 'getSalvarData'])->name('movimentacaopneus.store');

    // Novas rotas auto-save
    Route::post('/auto-save-status', [MovimentacaoPneusController::class, 'autoSaveStatus'])->name('movimentacaopneus.auto-save-status');

    Route::post('/restore-session', [MovimentacaoPneusController::class, 'restoreSession'])->name('movimentacaopneus.restore-session');
    Route::get('/api/pneu/search', [MovimentacaoPneusController::class, 'searchPneus'])->name('movimentacaopneus.api.pneu.search');
    Route::get('/api/pneu/search-by-os', [MovimentacaoPneusController::class, 'searchPneusPorOrdemServico'])->name('api.pneu.search-by-os');

    // Rota para status detalhado
    Route::get('/status-detalhado', [MovimentacaoPneusController::class, 'statusDetalhado'])->name('movimentacaopneus.status-detalhado');

    // Rota para teste do auto-save com banco
    Route::post('/teste-auto-save-com-banco', [MovimentacaoPneusController::class, 'testeAutoSaveComBanco'])->name('movimentacaopneus.teste-auto-save-com-banco');

    // Rota para debug do auto-save
    Route::post('/debug-auto-save', [MovimentacaoPneusController::class, 'debugAutoSave'])->name('movimentacaopneus.debug-auto-save');

    // Rota para teste direto no banco
    Route::post('/teste-direto-banco', [MovimentacaoPneusController::class, 'testeDirectoBanco'])->name('movimentacaopneus.teste-direto-banco');

    // Rota para finalizar aplicação de pneu
    Route::post('/finalizar-aplicacao', [MovimentacaoPneusController::class, 'finalizarAplicacao'])->name('movimentacaopneus.finalizar-aplicacao');

    // Rota para obter localizações obrigatórias do veículo
    Route::get('/localizacoes-obrigatorias/{idVeiculo}', [MovimentacaoPneusController::class, 'getLocalizacoesObrigatorias'])->name('movimentacaopneus.localizacoes-obrigatorias');

    // Rota para verificar se pode finalizar (debug)
    Route::post('/verificar-finalizacao', [MovimentacaoPneusController::class, 'verificarFinalizacao'])->name('movimentacaopneus.verificar-finalizacao');

    Route::post('/admin/movimentacaopneus/debug-regras', [MovimentacaoPneusController::class, 'debugRegrasNegocio'])->name('admin.movimentacaopneus.debug-regras');

    Route::post('/limpar-conflitos', [MovimentacaoPneusController::class, 'limparConflitosExistentes'])->name('movimentacaopneus.limpar-conflitos');
});

// ✅ ROTAS DE DESCARTE/BAIXA DE PNEUS - ATUALIZADAS
Route::group(['prefix' => 'descartepneus'], function () {
    Route::get('/', [DescartePneuController::class, 'index'])->name('descartepneus.index');
    Route::get('criar', [DescartePneuController::class, 'create'])->name('descartepneus.create');
    Route::get('{descartepneus}', [DescartePneuController::class, 'show'])->name('descartepneus.show');
    Route::post('/', [DescartePneuController::class, 'store'])->name('descartepneus.store');
    Route::get('{descartepneus}/editar', [DescartePneuController::class, 'edit'])->name('descartepneus.edit');
    Route::put('{descartepneus}', [DescartePneuController::class, 'update'])->name('descartepneus.update');
    Route::delete('{descartepneus}', [DescartePneuController::class, 'destroy'])->name('descartepneus.destroy');

    // ✅ NOVAS ROTAS PARA FUNCIONALIDADES IMPLEMENTADAS
    Route::post('/anexar-laudo-multiplo', [DescartePneuController::class, 'anexarLaudoMultiplo'])->name('descartepneus.anexar-laudo-multiplo');
    Route::post('/{id}/finalizar', [DescartePneuController::class, 'finalizar'])->name('descartepneus.finalizar');
    Route::get('/{id}/obter-laudo', [DescartePneuController::class, 'obterLaudo'])->name('descartepneus.obter-laudo');
    Route::get('/pneus-aguardando', [DescartePneuController::class, 'pneusAguardando'])->name('descartepneus.pneus-aguardando');
});

Route::prefix('api')->group(function () {
    // Você pode adicionar rotas semelhantes para outros tipos de entidades
    // Route::get('/pneu/search', [PneuController::class, 'search'])
    //     ->name('api.pneu.search');
    // Route::get('/pneu/single/{id}', [PneuController::class, 'getById'])
    //     ->name('api.pneu.single');
    // Você pode adicionar rotas semelhantes para outros tipos de entidades
    Route::get('/tipodescarte/search', [TipoDescarteController::class, 'search'])
        ->name('api.tipodescarte.search');
    Route::get('/tipodescarte/single/{id}', [TipoDescarteController::class, 'getById'])
        ->name('api.tipodescarte.single');
});

// Rotas Calibragem pneus
Route::group(['prefix' => 'calibragempneus'], function () {
    Route::get('/', [CalibragemPneusController::class, 'index'])/* index é de acordo com os métodos no controller */->name('calibragempneus.index');
    Route::get('criar', [CalibragemPneusController::class, 'create'])/* create é de acordo com os métodos no controller */->name('calibragempneus.create');
    Route::post('/', [CalibragemPneusController::class, 'store'])->name('calibragempneus.store');
    Route::get('{calibragempneus}/editar', [CalibragemPneusController::class, 'edit'])->name('calibragempneus.edit');
    Route::put('{calibragempneus}', [CalibragemPneusController::class, 'update'])->name('calibragempneus.update');
    Route::get('/ultima-data/{idVeiculo}', [CalibragemPneusController::class, 'getUltimaDataCalibragem']);
    Route::get('/validar-calibragem/{idVeiculo}', [CalibragemPneusController::class, 'verificaCalibragemRecente']);
    Route::get('/historico/{id_calibragem_pneus_itens}', [CalibragemPneusController::class, 'mostrarHistorico']);
    // Route::put('/restaurar/{id_calibragem_pneus_itens}', [CalibragemPneusController::class, 'restaurarHistorico']);
    // routes/web.php ou routes/admin.php
    Route::get('pneus-veiculo/{idVeiculo}', [CalibragemPneusController::class, 'getPneusVeiculo']);
});

Route::prefix('api')->group(function () {
    // veiculo
    Route::get('/veiculos/search', [VeiculoController::class, 'search'])->name('api.veiculos.search');
    Route::get('/veiculos/single/{id}', [VeiculoController::class, 'getById'])->name('api.veiculos.single');

    Route::get('/servicos/search', [ServicoController::class, 'search'])->name('api.servicos.search');
    Route::get('/servicos/single/{id}', [ServicoController::class, 'getById'])->name('api.servicos.single');
    // ordem de serviço
    Route::get('/ordemservico/search', [MovimentacaoPneusController::class, 'searchOrdemServico'])->name('api.ordemservico.search');
});

Route::group(['prefix' => 'pneusdeposito'], function () {
    Route::get('/', [PneusDepositoController::class, 'index'])->name('pneusdeposito.index');
    Route::post('manutencao', [PneusDepositoController::class, 'EnviarManutencao'])->name('pneusdeposito.manutencao');
    Route::post('estoque', [PneusDepositoController::class, 'EnviarEstoque'])->name('pneusdeposito.estoque');
    Route::post('descarte', [PneusDepositoController::class, 'EnviarDescarte'])->name('pneusdeposito.descarte');
});

Route::group(['prefix' => 'descartetipopneu'], function () {

    Route::get('/', [DescarteTipoController::class, 'index'])->name('descartetipopneu.index');
    Route::get('criar', [DescarteTipoController::class, 'create'])->name('descartetipopneu.create');

    Route::post('/', [DescarteTipoController::class, 'store'])->name('descartetipopneu.store');
    Route::delete('descarte/tipo/{id}', [DescarteTipoController::class, 'destroy'])
        ->name('descartetipopneu.destroy');
    Route::get('{descartetipopneu}/editar', [DescarteTipoController::class, 'edit'])->name('descartetipopneu.edit');
    Route::put('{descartetipopneu}', [DescarteTipoController::class, 'update'])->name('descartetipopneu.update');
});


Route::group(['prefix' => 'envioerecebimentopneus'], function () {
    Route::get('/', [EnvioeRecebimento::class, 'index'])->name('envioerecebimentopneus.index');
});

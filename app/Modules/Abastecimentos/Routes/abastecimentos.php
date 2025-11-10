<?php

use App\Modules\Abastecimentos\Controllers\AbastecimentoManualController;
use App\Modules\Abastecimentos\Controllers\AbastecimentoAtsTruckpagManualController;
use App\Modules\Abastecimentos\Controllers\AjusteKmAbastecimentoController;
use App\Modules\Abastecimentos\Controllers\BombaController;
use App\Modules\Abastecimentos\Controllers\PermissaoKmManualController;
use App\Modules\Abastecimentos\Controllers\TanqueController;
use App\Modules\Abastecimentos\Controllers\AbastecimentosFaturamentoController;
use App\Modules\Abastecimentos\Controllers\AfericaoBombaController;
use App\Modules\Abastecimentos\Controllers\EncerranteController;
use App\Modules\Abastecimentos\Controllers\EstoqueCombustivelController;
use App\Http\Controllers\Admin\FornecedorController;
use App\Modules\Abastecimentos\Controllers\InconsistenciasController;
use App\Http\Controllers\Admin\VeiculoController;
use App\Http\Controllers\Admin\MetaTipoEquipamentoController;
use App\Modules\Abastecimentos\Controllers\RecebimentoCombustivelController;
use App\Modules\Abastecimentos\Controllers\ValorCombustivelTerceiroController;
use App\Modules\Abastecimentos\Controllers\AbastecimentoTruckPagController;
use App\Modules\Abastecimentos\Controllers\ReprocessarIntegracaoController;
use Illuminate\Support\Facades\Route;

Route::post('/abastecimentomanual/processar-lote', [App\Modules\Abastecimentos\Controllers\AbastecimentoLoteController::class, 'processarLote']);

// Bombas de Abastecimento
Route::group(['prefix' => 'bombas'], function () {
    Route::get('/', [BombaController::class, 'index'])
        ->name('bombas.index');

    // CRUD
    Route::get('/create', [BombaController::class, 'create'])
        ->name('bombas.create');
    Route::post('/', [BombaController::class, 'store'])
        ->name('bombas.store');
    Route::get('/{id}/edit', [BombaController::class, 'edit'])
        ->name('bombas.edit');
    Route::put('/{id}', [BombaController::class, 'update'])
        ->name('bombas.update');
    Route::delete('/{id}', [BombaController::class, 'destroy'])
        ->name('bombas.destroy');

    Route::post('/{id}/toggle-status', [BombaController::class, 'toggleStatus'])
        ->name('bombas.toggle-status');


    // Exportação
    Route::get('/export-csv', [BombaController::class, 'exportCsv'])
        ->name('bombas.exportCsv');
    Route::get('/export-xls', [BombaController::class, 'exportXls'])
        ->name('bombas.exportXls');
    Route::get('/export-pdf', [BombaController::class, 'exportPdf'])
        ->name('bombas.exportPdf');
    Route::get('/export-xml', [BombaController::class, 'exportXml'])
        ->name('bombas.exportXml');
});

// API para Tanques e Filiais (usado pela busca de bombas)
Route::prefix('api')->group(function () {
    // Rotas para busca de tanques
    Route::get('/tanques/search', [TanqueController::class, 'search'])
        ->name('api.tanques.search');
    Route::get('/tanques/single/{id}', [TanqueController::class, 'getById'])
        ->name('api.tanques.single');
});

// Rotas Tanque
Route::group(['prefix' => 'tanques'], function () {
    Route::get('/', [TanqueController::class, 'index'])->name('tanques.index');
    Route::get('criar', [TanqueController::class, 'create'])->name('tanques.create');
    Route::get('{tanque}', [TanqueController::class, 'show'])->name('tanques.show');

    Route::post('/', [TanqueController::class, 'store'])->name('tanques.store');
    Route::get('{tanque}/editar', [TanqueController::class, 'edit'])
        ->name('tanques.edit');
    Route::put('{tanque}', [TanqueController::class, 'update'])
        ->name('tanques.update');

    Route::delete('{tanque}', [TanqueController::class, 'destroy'])
        ->name('tanques.destroy');

    Route::patch('/{id}/toggle-active', [TanqueController::class, 'toggleActive'])->name('tanques.toggle-active');
});


// PermissaoKmManual
Route::group(['prefix' => 'permissaokmmanuals'], function () {
    Route::get('/', [PermissaoKmManualController::class, 'index'])->name('permissaokmmanuals.index');
    Route::get('criar', [PermissaoKmManualController::class, 'create'])->name('permissaokmmanuals.create');
    Route::get('{permissaokmmanual}', [PermissaoKmManualController::class, 'show'])->name('permissaokmmanuals.show');

    Route::post('/importar-por-departamento', [PermissaoKmManualController::class, 'importarPorDepartamento'])
        ->name('permissaokmmanuals.importar-por-departamento');
    Route::post('/importar-por-categoria', [PermissaoKmManualController::class, 'importarPorCategoria'])
        ->name('permissaokmmanuals.importar-por-categoria');

    Route::post('/', [PermissaoKmManualController::class, 'store'])->name('permissaokmmanuals.store');
    Route::get('{permissaokmmanual}/editar', [PermissaoKmManualController::class, 'edit'])
        ->name('permissaokmmanuals.edit');
    Route::put('{permissaokmmanual}', [PermissaoKmManualController::class, 'update'])
        ->name('permissaokmmanuals.update');

    Route::delete('{permissaokmmanual}', [PermissaoKmManualController::class, 'destroy'])
        ->name('permissaokmmanuals.destroy');
});

// Abastecimentos ATS/TRUCKPAG/MANUAL
Route::group(['prefix' => 'abastecimentosatstruckpagmanual'], function () {
    Route::get('/', [AbastecimentoAtsTruckpagManualController::class, 'index'])
        ->name('abastecimentosatstruckpagmanual.index');
    Route::post('{id}/enviar-inconsistencia', [AbastecimentoAtsTruckpagManualController::class, 'enviarInconsistencia'])
        ->name('abastecimentosatstruckpagmanual.enviarInconsistencia');
    Route::get('/export-pdf', [AbastecimentoAtsTruckpagManualController::class, 'exportPdf'])
        ->name('abastecimentosatstruckpagmanual.exportPdf');
    Route::get('/export-csv', [AbastecimentoAtsTruckpagManualController::class, 'exportCsv'])
        ->name('abastecimentosatstruckpagmanual.exportCsv');
    Route::get('/export-xls', [AbastecimentoAtsTruckpagManualController::class, 'exportXls'])
        ->name('abastecimentosatstruckpagmanual.exportXls');
    Route::get('/export-xml', [AbastecimentoAtsTruckpagManualController::class, 'exportXml'])
        ->name('abastecimentosatstruckpagmanual.exportXml');
});

Route::get('/inconsistencias/getVeiculoInfo/{id}', [InconsistenciasController::class, 'getVeiculoInfo'])
    ->name('admin.inconsistencias.getVeiculoInfo');
Route::post('/inconsistencias/getKmInfo', [InconsistenciasController::class, 'getKmInfo'])
    ->name('admin.inconsistencias.getKmInfo');

// Abastecimento Manual
Route::group(['prefix' => 'abastecimentomanual'], function () {

    // Página principal
    Route::get('/', [AbastecimentoManualController::class, 'index'])
        ->name('abastecimentomanual.index');

    /**
     * Ações auxiliares (AJAX / Dinâmicas)
     */
    Route::post('/get-combustivel-data', [AbastecimentoManualController::class, 'getCombustivelData'])
        ->name('abastecimentomanual.getCombustivelData');

    Route::post('/get-combustivel-bomba', [AbastecimentoManualController::class, 'getCombustivelBomba'])
        ->name('abastecimentomanual.getCombustivelBomba');

    Route::get('/getVeiculo', [AbastecimentoManualController::class, 'getVeiculos'])->name('api.abastecimentomanual.getVeiculo');
    Route::get('/getPosto', [AbastecimentoManualController::class, 'retornarPosto'])->name('api.abastecimentomanual.getPosto');

    /**
     * CRUD
     */
    Route::get('/create', [AbastecimentoManualController::class, 'create'])
        ->name('abastecimentomanual.create');

    Route::post('/store', [AbastecimentoManualController::class, 'store'])
        ->name('abastecimentomanual.store');

    Route::get('/{id}/edit', [AbastecimentoManualController::class, 'edit'])
        ->name('abastecimentomanual.edit');

    Route::put('/{id}', [AbastecimentoManualController::class, 'update'])
        ->name('abastecimentomanual.update');

    Route::delete('/{id}', [AbastecimentoManualController::class, 'destroy'])
        ->name('abastecimentomanual.destroy');

    /**
     * Exportações
     */
    Route::get('/export-csv', [AbastecimentoManualController::class, 'exportCsv'])
        ->name('abastecimentomanual.exportCsv');

    Route::get('/export-xls', [AbastecimentoManualController::class, 'exportXls'])
        ->name('abastecimentomanual.exportXls');

    Route::get('/export-pdf', [AbastecimentoManualController::class, 'exportPdf'])
        ->name('abastecimentomanual.exportPdf');

    Route::get('/export-xml', [AbastecimentoManualController::class, 'exportXml'])
        ->name('abastecimentomanual.exportXml');
});

// Pesquisa Fornecedores e Veículos do Abastecimento Manual
Route::get('/api/fornecedores/search', [FornecedorController::class, 'search'])
    ->name('api.fornecedores.search');
Route::get('/api/fornecedores/single/{id}', [FornecedorController::class, 'getById'])
    ->name('api.fornecedores.single');
Route::get('/veiculos/search', [VeiculoController::class, 'search'])
    ->name('api.veiculos.search');
Route::get('/veiculos/single/{id}', [VeiculoController::class, 'getById'])
    ->name('api.veiculos.single');
// Rotas para AJAX do abastecimento manual
Route::get('/ajax-get-veiculo-dados', [AbastecimentoManualController::class, 'ajaxGetVeiculoDados'])
    ->name('ajax-get-veiculo-dados');

// Rotas para valores de bombas e combustíveis
Route::get('/api/bombas/valor-unitario', [AbastecimentoManualController::class, 'getBombaValorUnitario'])
    ->name('api.bombas.valor');
Route::get('/api/bombas/get-bomba-data', [AbastecimentoManualController::class, 'getBombaData'])
    ->name('api.bombas.getBombaData');
Route::get('/api/bombas/por-combustivel', [AbastecimentoManualController::class, 'getBombasPorCombustivel'])
    ->name('api.bombas.por-combustivel');

// Rotas para departamentos (se ainda não existirem)
Route::get('/api/departamentos/single/{id}', [AbastecimentoManualController::class, 'getDepartamento'])
    ->name('api.departamentos.single');

// Rotas para filiais (se ainda não existirem)
Route::get('/api/filiais/single/{id}', [AbastecimentoManualController::class, 'getFilial'])
    ->name('api.filiais.single');

// Rotas para motoristas (se ainda não existirem)
Route::get('/api/motoristas/single/{id}', [AbastecimentoManualController::class, 'getMotorista'])
    ->name('api.motoristas.single');


// Ajuste Km Abastecimento
Route::group(['prefix' => 'ajustekm'], function () {
    Route::get('/', [AjusteKmAbastecimentoController::class, 'index'])->name('ajustekm.index');

    // Rotas de exportação
    Route::get('/export-pdf', [AjusteKmAbastecimentoController::class, 'exportPdf'])->name('ajustekm.exportPdf');
    Route::get('/export-csv', [AjusteKmAbastecimentoController::class, 'exportCsv'])->name('ajustekm.exportCsv');
    Route::get('/export-xls', [AjusteKmAbastecimentoController::class, 'exportXls'])->name('ajustekm.exportXls');
    Route::get('/export-xml', [AjusteKmAbastecimentoController::class, 'exportXml'])->name('ajustekm.exportXml');

    // CRUD
    Route::get('/create', [AjusteKmAbastecimentoController::class, 'create'])->name('ajustekm.create');
    Route::post('/store', [AjusteKmAbastecimentoController::class, 'store'])->name('ajustekm.store');
    Route::get('/{ajusteKm}/edit', [AjusteKmAbastecimentoController::class, 'edit'])->name('ajustekm.edit');
    Route::put('/{ajusteKm}', [AjusteKmAbastecimentoController::class, 'update'])->name('ajustekm.update');

    // Informar o KM do Abastecimento
    Route::get('/informar-km', [AjusteKmAbastecimentoController::class, 'informarKm'])->name('ajustekm.informar-km');
    Route::post('/salvar-km', [AjusteKmAbastecimentoController::class, 'salvarKm'])->name('ajustekm.salvar-km');
});


// Rotas para API do Veículo
Route::get('/api/veiculos/{id}/dados', [VeiculoController::class, 'getDados'])
    ->name('api.veiculos.dados');

// Rotas para Dashboard de Estoque de combustível
Route::middleware(['can:ver_estoquecombustivel'])->group(function () {
    Route::get('/estoque-combustivel', [EstoqueCombustivelController::class, 'dashboard'])
        ->name('estoque-combustivel.dashboard');

    Route::get('/estoque-combustivel/refresh', [EstoqueCombustivelController::class, 'refreshData'])
        ->middleware(['can:editar_estoquecombustivel'])
        ->name('estoque-combustivel.refresh');

    Route::get('/exportPdf', [EstoqueCombustivelController::class, 'exportPdf'])->name('estoque-combustivel.exportPdf');
    Route::get('/exportXls', [EstoqueCombustivelController::class, 'exportXls'])->name('estoque-combustivel.exportXls');
    Route::get('/exportCsv', [EstoqueCombustivelController::class, 'exportCsv'])->name('estoque-combustivel.exportCsv');
});


// Abastecimento para Abastecimento Faturamento
Route::group(['prefix' => 'abastecimentosfaturamento'], function () {
    Route::get('/', [AbastecimentosFaturamentoController::class, 'index'])
        ->name('abastecimentosfaturamento.index');

    // CRUD
    Route::get('/create', [AbastecimentosFaturamentoController::class, 'create'])
        ->name('abastecimentosfaturamento.create');
    Route::post('/store', [AbastecimentosFaturamentoController::class, 'store'])
        ->name('abastecimentosfaturamento.store');

    // Rota para processamento de chave NF
    Route::post('/processar-chave-nf', [AbastecimentosFaturamentoController::class, 'processarChaveNf'])
        ->name('abastecimentosfaturamento.processar-chave-nf');

    // Rota para limpar seleções na sessão
    Route::post('/limpar-selecoes', [AbastecimentosFaturamentoController::class, 'limparSelecoes'])
        ->name('abastecimentosfaturamento.limpar-selecoes');

    Route::post('/buscar-por-codigos', [AbastecimentosFaturamentoController::class, 'buscarPorCodigos'])
        ->name('api.abastecimentosfaturamento.buscar-por-codigos');
});



// Entrada por aferição de bombas
Route::group(['prefix' => 'afericaobombas'], function () {
    Route::get('/', [AfericaoBombaController::class, 'index'])
        ->name('afericaobombas.index');

    // Create e Store (para nova entrada direta)
    Route::get('/create', [AfericaoBombaController::class, 'create'])
        ->name('afericaobombas.create');
    Route::post('/store', [AfericaoBombaController::class, 'store'])
        ->name('afericaobombas.store');

    // Edit e Update (para gerar entrada a partir de abastecimento existente)
    Route::get('/{id}/edit', [AfericaoBombaController::class, 'edit'])
        ->name('afericaobombas.edit');
    Route::put('/{id}', [AfericaoBombaController::class, 'update'])
        ->name('afericaobombas.update');

    // Exportação
    Route::get('/export-csv', [AfericaoBombaController::class, 'exportCsv'])
        ->name('afericaobombas.exportCsv');
    Route::get('/export-xls', [AfericaoBombaController::class, 'exportXls'])
        ->name('afericaobombas.exportXls');
    Route::get('/export-pdf', [AfericaoBombaController::class, 'exportPdf'])
        ->name('afericaobombas.exportPdf');
    Route::get('/export-xml', [AfericaoBombaController::class, 'exportXml'])
        ->name('afericaobombas.exportXml');
});

// API para busca de tanques (se necessário)
Route::get('/api/tanques/search', [TanqueController::class, 'search'])
    ->name('api.tanques.search');
Route::get('/api/tanques/single/{id}', [TanqueController::class, 'getById'])
    ->name('api.tanques.single');

// Rotas Encerrante
Route::group(['prefix' => 'encerrantes'], function () {
    Route::get('/', [EncerranteController::class, 'index'])->name('encerrantes.index');
    Route::get('/create', [EncerranteController::class, 'create'])->name('encerrantes.create');

    // NOVA ROTA AJAX para cascata tanque → bomba
    Route::get('/bombas-por-tanque/{tanque}', [EncerranteController::class, 'bombasPorTanque'])
        ->name('encerrantes.bombas-por-tanque');

    Route::post('/', [EncerranteController::class, 'store'])->name('encerrantes.store');
    Route::get('/{encerrante}/edit', [EncerranteController::class, 'edit'])->name('encerrantes.edit');
    Route::put('/{encerrante}', [EncerranteController::class, 'update'])->name('encerrantes.update');

    Route::delete('/{encerrante}', [EncerranteController::class, 'destroy'])
        ->name('encerrantes.destroy');
});

// Meta por Tipo de Equipamento de bombas ⛽
Route::group(['prefix' => 'metatipoequipamentos'], function () {
    Route::get('/', [MetaTipoEquipamentoController::class, 'index'])
        ->name('metatipoequipamentos.index');

    // CRUD
    Route::get('/create', [MetaTipoEquipamentoController::class, 'create'])
        ->name('metatipoequipamentos.create');
    Route::post('/store', [MetaTipoEquipamentoController::class, 'store'])
        ->name('metatipoequipamentos.store');
    Route::get('/{id}/edit', [MetaTipoEquipamentoController::class, 'edit'])
        ->name('metatipoequipamentos.edit');
    Route::put('/{id}', [MetaTipoEquipamentoController::class, 'update'])
        ->name('metatipoequipamentos.update');
    Route::delete('/{id}', [MetaTipoEquipamentoController::class, 'destroy'])
        ->name('metatipoequipamentos.destroy');

    // Exportação
    Route::get('/export-csv', [MetaTipoEquipamentoController::class, 'exportCsv'])
        ->name('metatipoequipamentos.exportCsv');
    Route::get('/export-xls', [MetaTipoEquipamentoController::class, 'exportXls'])
        ->name('metatipoequipamentos.exportXls');
    Route::get('/export-pdf', [MetaTipoEquipamentoController::class, 'exportPdf'])
        ->name('metatipoequipamentos.exportPdf');
    Route::get('/export-xml', [MetaTipoEquipamentoController::class, 'exportXml'])
        ->name('metatipoequipamentos.exportXml');
});

// Recebimento de Combustivel
Route::group(['prefix' => 'recebimentocombustiveis'], function () {
    Route::get('/', [RecebimentoCombustivelController::class, 'index'])->name('recebimentocombustiveis.index');
    Route::get('/create', [RecebimentoCombustivelController::class, 'create'])->name('recebimentocombustiveis.create');
    Route::post('/get-tank-data', [RecebimentoCombustivelController::class, 'getTankData'])->name('valorcombustiveis.get-tank-data');

    Route::match(['get', 'post'], '/getFornecedores', [RecebimentoCombustivelController::class, 'getFornecedores'])->name('recebimentocombustiveis.getFornecedores');
    Route::post('/store', [RecebimentoCombustivelController::class, 'store'])->name('recebimentocombustiveis.store');
    Route::post('/get-pedido', [RecebimentoCombustivelController::class, 'getpedido'])->name('recebimentocombustiveis.getPedido');
    Route::post('/pedido-ja-baixado', [RecebimentoCombustivelController::class, 'pedidoJaBaixado'])->name('recebimentocombustiveis.pedidoJaBaixado');
    Route::get('/{recebimentocombustiveis}/edit', [RecebimentoCombustivelController::class, 'edit'])->name('recebimentocombustiveis.edit');
    Route::put('/{recebimentocombustiveis}', [RecebimentoCombustivelController::class, 'update'])->name('recebimentocombustiveis.update');
    Route::delete('/{recebimentocombustiveis}', [RecebimentoCombustivelController::class, 'destroy'])->name('recebimentocombustiveis.destroy');
});

// Valor Combustiveis (Correção)
Route::group(['prefix' => 'valorcombustiveis'], function () {
    Route::get('/', [ValorCombustivelTerceiroController::class, 'index'])->name('valorcombustiveis.index');
    Route::get('/create', [ValorCombustivelTerceiroController::class, 'create'])->name('valorcombustiveis.create');
    Route::post('/store', [ValorCombustivelTerceiroController::class, 'store'])->name('valorcombustiveis.store');
    Route::get('/{valorcombustivel}/show', [ValorCombustivelTerceiroController::class, 'show'])->name('valorcombustiveis.show');
    Route::get('/{valorcombustivel}/edit', [ValorCombustivelTerceiroController::class, 'edit'])->name('valorcombustiveis.edit');
    Route::put('/{valorcombustivel}', [ValorCombustivelTerceiroController::class, 'update'])->name('valorcombustiveis.update');
    Route::delete('/{valorcombustivel}', [ValorCombustivelTerceiroController::class, 'destroy'])->name('valorcombustiveis.destroy');

    // Rota adicional para API
    Route::post('/get-valor-bomba', [ValorCombustivelTerceiroController::class, 'getValorBomba'])->name('valorcombustiveis.get-Valor-Bomba');
});

// Inconsistências
Route::group(['prefix' => 'inconsistencias'], function () {
    Route::get('/', [InconsistenciasController::class, 'index'])->name('inconsistencias.index');

    // ATS
    Route::match(['get', 'post'], '/ats/search', [InconsistenciasController::class, 'searchAts'])
        ->name('inconsistencias.ats.search');
    Route::get('/ats/{id}/edit', [InconsistenciasController::class, 'editAts'])
        ->name('inconsistencias.ats.edit');
    Route::put('/ats/{id}', [InconsistenciasController::class, 'updateAts'])
        ->name('inconsistencias.ats.update');
    Route::post('/ats/{id}/remover', [InconsistenciasController::class, 'removerAts'])
        ->name('inconsistencias.ats.remover');
    Route::post('/ats/{id}/reprocessar', [InconsistenciasController::class, 'reprocessarAts'])
        ->name('inconsistencias.ats.reprocessar');

    // TruckPag
    Route::match(['get', 'post'], '/truckpag/search', [InconsistenciasController::class, 'searchTruckPag'])
        ->name('inconsistencias.truckpag.search');
    Route::get('/truckpag/{id}/edit', [InconsistenciasController::class, 'editTruckPag'])
        ->name('inconsistencias.truckpag.edit');
    Route::put('/truckpag/{id}', [InconsistenciasController::class, 'updateTruckPag'])
        ->name('inconsistencias.truckpag.update');
    Route::post('/truckpag/{id}/remover', [InconsistenciasController::class, 'removerTruckPag'])
        ->name('inconsistencias.truckpag.remover');

    // Nova rota para buscar informações de KM
    Route::post('/get-km-info', [InconsistenciasController::class, 'getKmInfo'])
        ->name('inconsistencias.getKmInfo');
});


// Rotas para Reprocessar Integrações
Route::group(['prefix' => 'reprocessar', 'as' => 'reprocessar.'], function () {
    Route::get('/', [ReprocessarIntegracaoController::class, 'index'])->name('index');

    // ATS
    Route::post('/ats', [ReprocessarIntegracaoController::class, 'processarAts'])
        ->name('ats');

    // TruckPag
    Route::post('/truckpag', [ReprocessarIntegracaoController::class, 'processarTruckPag'])
        ->name('truckpag');
});

// Abastecimento Truck Pag
Route::group(['prefix' => 'abastecimentostruckpag'], function () {
    Route::get('/', [AbastecimentoTruckPagController::class, 'index'])->name('abastecimentostruckpag.index');
    Route::get('/create', [AbastecimentoTruckPagController::class, 'create'])->name('abastecimentostruckpag.create');
    Route::post('/store', [AbastecimentoTruckPagController::class, 'store'])->name('abastecimentostruckpag.store');
    Route::get('/processamento', [AbastecimentoTruckPagController::class, 'onProcessarTruckPag'])->name('abastecimentostruckpag.onProcessarTruckPag');
    Route::get('/processamentoats', [AbastecimentoTruckPagController::class, 'onProcessarATS'])->name('abastecimentostruckpag.onProcessarATS');
    Route::get('/{abastecimentostruckpag}/edit', [AbastecimentoTruckPagController::class, 'edit'])->name('abastecimentostruckpag.edit');
    Route::put('/{abastecimentostruckpag}', [AbastecimentoTruckPagController::class, 'update'])->name('abastecimentostruckpag.update');
    Route::delete('/{abastecimentostruckpag}', [AbastecimentoTruckPagController::class, 'destroy'])->name('abastecimentostruckpag.destroy');
});

<?php

use App\Http\Controllers\Admin\{
    CadastroVeiculoVencimentarioController,
    CondutoresVencimantarioController,
    ControleLicencaVencimentarioController,
    CronotacografoVencimentarioController,
    LicenciamentosController,
    ListagemAnttController,
    ListagemIpvaController,
    ListagemMultasController,
    ListagemNotificacoesController,
    RestricoesBloqueiosController,
};

use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'cadastroveiculovencimentario'], function () {
    Route::get('/', [CadastroVeiculoVencimentarioController::class, 'index'])->name('cadastroveiculovencimentario.index');

    Route::post('/imprimir', [CadastroVeiculoVencimentarioController::class, 'onImprimir'])->name('cadastroveiculovencimentario.imprimir');

    Route::post('/onAction', [CadastroVeiculoVencimentarioController::class, 'onAction'])->name('cadastroveiculovencimentario.onAction');

    Route::get('/crlv/{id}', [CadastroVeiculoVencimentarioController::class, 'show']);

    Route::post('/consultar', [CadastroVeiculoVencimentarioController::class, 'consultarVeiculo'])
        ->name('cadastroveiculovencimentario.consultar');
});

Route::group(['prefix' => 'condutores'], function () {
    // Listagem
    Route::get('/', [CondutoresVencimantarioController::class, 'index'])->name('condutores.index');

    // Formulário de criação
    Route::get('criar', [CondutoresVencimantarioController::class, 'create'])->name('condutores.create');

    // Armazenar novo registro
    Route::post('/', [CondutoresVencimantarioController::class, 'store'])->name('condutores.store');

    // Exibição de um registro
    Route::get('{condutoresvencimantario}', [CondutoresVencimantarioController::class, 'show'])->name('condutores.show');

    // Formulário de edição
    Route::get('{condutoresvencimantario}/editar', [CondutoresVencimantarioController::class, 'edit'])->name('condutores.edit');

    // Atualizar registro
    Route::put('{condutoresvencimantario}', [CondutoresVencimantarioController::class, 'update'])->name('condutores.update');

    // Deletar registro
    Route::delete('{condutoresvencimantario}', [CondutoresVencimantarioController::class, 'destroy'])->name('condutores.destroy');
});

Route::group(['prefix' => 'controlelicencavencimentario'], function () {
    Route::get('/', [ControleLicencaVencimentarioController::class, 'index'])->name('controlelicencavencimentario.index');

    Route::get('/export-csv', [ControleLicencaVencimentarioController::class, 'exportCsv'])->name('controlelicencavencimentario.exportCsv');
    Route::get('/export-xls', [ControleLicencaVencimentarioController::class, 'exportXls'])->name('controlelicencavencimentario.exportXls');
    Route::get('/export-pdf', [ControleLicencaVencimentarioController::class, 'exportPdf'])->name('controlelicencavencimentario.exportPdf');
    Route::get('/export-xml', [ControleLicencaVencimentarioController::class, 'exportXml'])->name('controlelicencavencimentario.exportXml');
});

Route::group(['prefix' => 'cronotacografovencimentario'], function () {
    Route::get('/', [CronotacografoVencimentarioController::class, 'index'])->name('cronotacografovencimentario.index');

    Route::get('/export-csv', [CronotacografoVencimentarioController::class, 'exportCsv'])->name('cronotacografovencimentario.exportCsv');
    Route::get('/export-xls', [CronotacografoVencimentarioController::class, 'exportXls'])->name('cronotacografovencimentario.exportXls');
    Route::get('/export-pdf', [CronotacografoVencimentarioController::class, 'exportPdf'])->name('cronotacografovencimentario.exportPdf');
    Route::get('/export-xml', [CronotacografoVencimentarioController::class, 'exportXml'])->name('cronotacografovencimentario.exportXml');
});

Route::group(['prefix' => 'restricoesbloqueios'], function () {
    Route::get('/', [RestricoesBloqueiosController::class, 'index'])->name('restricoesbloqueios.index');
    Route::get('criar', [RestricoesBloqueiosController::class, 'create'])->name('restricoesbloqueios.create');
    Route::get('{restricoesbloqueios}', [RestricoesBloqueiosController::class, 'show'])->name('restricoesbloqueios.show');

    Route::post('/', [RestricoesBloqueiosController::class, 'store'])->name('restricoesbloqueios.store');
    Route::get('{restricoesbloqueios}/editar', [RestricoesBloqueiosController::class, 'edit'])
        ->name('restricoesbloqueios.edit');
    Route::put('{restricoesbloqueios}', [RestricoesBloqueiosController::class, 'update'])
        ->name('restricoesbloqueios.update');

    Route::delete('{restricoesbloqueios}', [RestricoesBloqueiosController::class, 'destroy'])
        ->name('restricoesbloqueios.destroy');
});

Route::group(['prefix' => 'licenciamentos'], function () {
    Route::get('/export-csv', [LicenciamentosController::class, 'exportCsv'])->name('licenciamentos.exportCsv');
    Route::get('/export-xls', [LicenciamentosController::class, 'exportXls'])->name('licenciamentos.exportXls');
    Route::get('/export-pdf', [LicenciamentosController::class, 'exportPdf'])->name('licenciamentos.exportPdf');
    Route::get('/export-xml', [LicenciamentosController::class, 'exportXml'])->name('licenciamentos.exportXml');

    Route::get('/', [LicenciamentosController::class, 'index'])->name('licenciamentos.index');

    Route::get('/baixarlote', [LicenciamentosController::class, 'baixarLote'])->name('licenciamentos.baixarlote');
});

Route::group(['prefix' => 'listagemipva'], function () {
    Route::get('/export-csv', [ListagemIpvaController::class, 'exportCsv'])->name('listagemipva.exportCsv');
    Route::get('/export-xls', [ListagemIpvaController::class, 'exportXls'])->name('listagemipva.exportXls');
    Route::get('/export-pdf', [ListagemIpvaController::class, 'exportPdf'])->name('listagemipva.exportPdf');
    Route::get('/export-xml', [ListagemIpvaController::class, 'exportXml'])->name('listagemipva.exportXml');

    Route::get('/', [ListagemIpvaController::class, 'index'])->name('listagemipva.index');

    Route::get('/baixarlote', [ListagemIpvaController::class, 'baixarLote'])->name('listagemipva.baixarlote');
});

Route::group(['prefix' => 'listagemmultas'], function () {
    Route::get('/export-csv', action: [ListagemMultasController::class, 'exportCsv'])->name('listagemmultas.exportCsv');
    Route::get('/export-xls', [ListagemMultasController::class, 'exportXls'])->name('listagemmultas.exportXls');
    Route::get('/export-pdf', [ListagemMultasController::class, 'exportPdf'])->name('listagemmultas.exportPdf');
    Route::get('/export-xml', [ListagemMultasController::class, 'exportXml'])->name('listagemmultas.exportXml');

    Route::get('/', [ListagemMultasController::class, 'index'])->name('listagemmultas.index');

    Route::get('/baixarlote', action: [ListagemMultasController::class, 'baixarLote'])->name('listagemmultas.baixarlote');

    Route::post('/indicar-motorista', [ListagemMultasController::class, 'indicarMotorista'])
        ->name('listagemmultas.indicar-motorista');

    Route::post('/remover-motorista', [ListagemMultasController::class, 'removerMotorista'])
        ->name('listagemmultas.remover-motorista');

    Route::post('/gerar-fici', [ListagemMultasController::class, 'gerarFici'])
        ->name('listagemmultas.gerarFici');

    Route::post('/solicitar-desconto-40', [ListagemMultasController::class, 'solicitarDescontoQuarenta'])->name('listagemmultas.solicitarDescontoQuarenta');
});

Route::group(['prefix' => 'listagemnotificacoes'], function () {
    Route::get('/export-csv', action: [ListagemNotificacoesController::class, 'exportCsv'])->name('listagemnotificacoes.exportCsv');
    Route::get('/export-xls', [ListagemNotificacoesController::class, 'exportXls'])->name('listagemnotificacoes.exportXls');
    Route::get('/export-pdf', [ListagemNotificacoesController::class, 'exportPdf'])->name('listagemnotificacoes.exportPdf');
    Route::get('/export-xml', [ListagemNotificacoesController::class, 'exportXml'])->name('listagemnotificacoes.exportXml');

    Route::get('/', [ListagemNotificacoesController::class, 'index'])->name('listagemnotificacoes.index');

    Route::get('/baixarlote', action: [ListagemNotificacoesController::class, 'baixarLote'])->name('listagemnotificacoes.baixarlote');

    Route::post('/indicar-motorista', [ListagemNotificacoesController::class, 'indicarMotorista'])
        ->name('listagemnotificacoes.indicar-motorista');

    Route::post('/remover-motorista', [ListagemNotificacoesController::class, 'removerMotorista'])
        ->name('listagemnotificacoes.remover-motorista');

    Route::post('/solicitar-desconto-40', [ListagemNotificacoesController::class, 'solicitarQuarentena'])->name('listagemnotificacoes.solicitarDescontoQuarenta');

    Route::post('/gerar-fici', [ListagemNotificacoesController::class, 'gerarFici'])->name('listagemnotificacoes.gerarFici');

    Route::post('/consultar-notificacao', [ListagemNotificacoesController::class, 'consultarNot'])
        ->name('listagemnotificacoes.consultar-notificacao');
});

Route::group(['prefix' => 'listagemantt'], function () {
    Route::get('/export-csv', [ListagemAnttController::class, 'exportCsv'])->name('listagemantt.exportCsv');
    Route::get('/export-xls', [ListagemAnttController::class, 'exportXls'])->name('listagemantt.exportXls');
    Route::get('/export-pdf', [ListagemAnttController::class, 'exportPdf'])->name('listagemantt.exportPdf');
    Route::get('/export-xml', [ListagemAnttController::class, 'exportXml'])->name('listagemantt.exportXml');

    Route::get('/', [ListagemAnttController::class, 'index'])->name('listagemantt.index');
});

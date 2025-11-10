<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\{
    AprovacaoRelacaoImobilizadoController,
    AprovacaoImobilizadoGestorController,
    CadastroImobilizadoController,
    DepartamentoController,
    DescarteImobilizadoController,
    DevolucaoImobilizadoVeiculoController,
    PessoalController,
    ProdutoController,
    ProdutosImobilizadosController,
    RequisicaoImobilizadosController,
    SaidaRelacaoImobilizadoController,
    VeiculoController,
    EstoqueImobilizadoController,
    FornecedorController,
    OrdemServicoController,
    RecebimentoImobilizadoController,
    SolicitacaoImobilizadoController,
    OrdemServicoImobilizadoController,
    TransfImobilizadoVeiculoController
};

/**
 * Descarte Imobilizado
 */
Route::prefix('descarteimobilizado')->group(function () {
    // Exportações
    Route::get('/export-csv', [DescarteImobilizadoController::class, 'exportCsv'])->name('descarteimobilizado.exportCsv');
    Route::get('/export-xls', [DescarteImobilizadoController::class, 'exportXls'])->name('descarteimobilizado.exportXls');
    Route::get('/export-pdf', [DescarteImobilizadoController::class, 'exportPdf'])->name('descarteimobilizado.exportPdf');
    Route::get('/export-xml', [DescarteImobilizadoController::class, 'exportXml'])->name('descarteimobilizado.exportXml');


    Route::get('/', [DescarteImobilizadoController::class, 'index'])->name('descarteimobilizado.index');
    Route::get('/create', [DescarteImobilizadoController::class, 'create'])->name('descarteimobilizado.create');
    Route::post('/store', [DescarteImobilizadoController::class, 'store'])->name('descarteimobilizado.store');
    Route::get('/{descarteimobilizado}/edit', [DescarteImobilizadoController::class, 'edit'])->name('descarteimobilizado.edit');
    Route::put('/{descarteimobilizado}', [DescarteImobilizadoController::class, 'update'])->name('descarteimobilizado.update');
    Route::delete('/{descarteimobilizado}', [DescarteImobilizadoController::class, 'destroy'])->name('descarteimobilizado.destroy');
});

/**
 * Produtos Imobilizados
 */
Route::prefix('produtosimobilizados')->group(function () {
    Route::get('/export-csv', [ProdutosImobilizadosController::class, 'exportCsv'])->name('produtosimobilizados.exportCsv');
    Route::get('/export-xls', [produtosimobilizadosController::class, 'exportXls'])->name('produtosimobilizados.exportXls');
    Route::get('/export-pdf', [produtosimobilizadosController::class, 'exportPdf'])->name('produtosimobilizados.exportPdf');
    Route::get('/export-xml', [produtosimobilizadosController::class, 'exportXml'])->name('produtosimobilizados.exportXml');

    Route::get('/', [ProdutosImobilizadosController::class, 'index'])->name('produtosimobilizados.index');
    Route::get('/create', [ProdutosImobilizadosController::class, 'create'])->name('produtosimobilizados.create');
    Route::post('/store', [ProdutosImobilizadosController::class, 'store'])->name('produtosimobilizados.store');
    Route::get('/{produtosimobilizados}/edit', [ProdutosImobilizadosController::class, 'edit'])->name('produtosimobilizados.edit');
    Route::put('/{produtosimobilizados}', [ProdutosImobilizadosController::class, 'update'])->name('produtosimobilizados.update');
    Route::delete('/{produtosimobilizados}', [ProdutosImobilizadosController::class, 'destroy'])->name('produtosimobilizados.destroy');
});

/**
 * Aprovação de Relação de Imobilizado
 */
Route::prefix('aprovacaorelacaoimobilizado')->group(function () {
    Route::get('/', [AprovacaoRelacaoImobilizadoController::class, 'index'])->name('aprovacaorelacaoimobilizado.index');
    Route::get('/{aprovacaorelacaoimobilizado}/edit', [AprovacaoRelacaoImobilizadoController::class, 'edit'])->name('aprovacaorelacaoimobilizado.edit');
    Route::post('/aprovar', [AprovacaoRelacaoImobilizadoController::class, 'onAprovar'])->name('aprovacaorelacaoimobilizado.aprovar');
    Route::post('/reprovar', [AprovacaoRelacaoImobilizadoController::class, 'onReprovar'])->name('aprovacaorelacaoimobilizado.reprovar');
});

/**
 * Aprovação de Imobilizado pelo Gestor
 */
Route::prefix('aprovacaoimobilizadogestor')->group(function () {
    Route::get('/', [AprovacaoImobilizadoGestorController::class, 'index'])->name('aprovacaoimobilizadogestor.index');
    Route::get('/{aprovacaoimobilizadogestor}/edit', [AprovacaoImobilizadoGestorController::class, 'edit'])->name('aprovacaoimobilizadogestor.edit');
    Route::post('/aprovar', [AprovacaoImobilizadoGestorController::class, 'onAprovar'])->name('aprovacaoimobilizadogestor.aprovar');
    Route::post('/reprovar', [AprovacaoImobilizadoGestorController::class, 'onReprovar'])->name('aprovacaoimobilizadogestor.reprovar');
});

/**
 * Requisição de Imobilizados
 */
Route::prefix('requisicaoimobilizados')->group(function () {
    Route::get('/', [RequisicaoImobilizadosController::class, 'index'])->name('requisicaoimobilizados.index');
    Route::get('/create', [RequisicaoImobilizadosController::class, 'create'])->name('requisicaoimobilizados.create');
    Route::post('/store', [RequisicaoImobilizadosController::class, 'store'])->name('requisicaoimobilizados.store');
    Route::get('/{requisicaoimobilizados}/edit', [RequisicaoImobilizadosController::class, 'edit'])->name('requisicaoimobilizados.edit');
    Route::put('/{requisicaoimobilizados}', [RequisicaoImobilizadosController::class, 'update'])->name('requisicaoimobilizados.update');
    Route::delete('/{requisicaoimobilizados}', [RequisicaoImobilizadosController::class, 'destroy'])->name('requisicaoimobilizados.destroy');
    Route::post('/enviarAprovacao', [RequisicaoImobilizadosController::class, 'onEnviaraprovacao'])->name('requisicaoimobilizados.enviarAprovacao');
});

/**
 * Saída da Relação de Imobilizado
 */
Route::prefix('saidarelacaoimobilizado')->group(function () {
    Route::get('/', [SaidaRelacaoImobilizadoController::class, 'index'])->name('saidarelacaoimobilizado.index');
    Route::get('/{saidarelacaoimobilizado}/edit', [SaidaRelacaoImobilizadoController::class, 'edit'])->name('saidarelacaoimobilizado.edit');
    Route::put('/{saidarelacaoimobilizado}', [SaidaRelacaoImobilizadoController::class, 'update'])->name('saidarelacaoimobilizado.update');

    Route::post('/finalizar', [SaidaRelacaoImobilizadoController::class, 'onFinalizar'])->name('saidarelacaoimobilizado.finalizar');
    Route::post('/salvar-produto', [SaidaRelacaoImobilizadoController::class, 'onSalvarProduto'])->name('saidarelacaoimobilizado.salvar-produto');

    Route::post('/estornar-os', [SaidaRelacaoImobilizadoController::class, 'onEstornarOs'])->name('saidarelacaoimobilizado.estornar-os');

    Route::post('/salvar-termo', [SaidaRelacaoImobilizadoController::class, 'onSalvarTermo'])->name('saidarelacaoimobilizado.salvar-termo');
});

Route::prefix('solicitacaoimobilizado')->group(function () {
    Route::get('/', [SolicitacaoImobilizadoController::class, 'index'])->name('solicitacaoimobilizado.index');
    Route::get('/create', [SolicitacaoImobilizadoController::class, 'create'])->name('solicitacaoimobilizado.create');
    Route::post('/store', [SolicitacaoImobilizadoController::class, 'store'])->name('solicitacaoimobilizado.store');
    Route::delete('/{solicitacaoimobilizado}', [SolicitacaoImobilizadoController::class, 'destroy'])->name('solicitacaoimobilizado.destroy');
});

Route::prefix('recebimentoimobilizado')->group(function () {
    Route::get('/', [RecebimentoImobilizadoController::class, 'index'])->name('recebimentoimobilizado.index');
    Route::get('/create', [RecebimentoImobilizadoController::class, 'create'])->name('recebimentoimobilizado.create');
    Route::post('/store', [RecebimentoImobilizadoController::class, 'store'])->name('recebimentoimobilizado.store');
    Route::delete('/{recebimentoimobilizado}', [RecebimentoImobilizadoController::class, 'destroy'])->name('recebimentoimobilizado.destroy');
});

Route::prefix('ordemservicoimobilizado')->group(function () {
    Route::get('/', [OrdemServicoImobilizadoController::class, 'index'])->name('ordemservicoimobilizado.index');
    Route::get('/create', [OrdemServicoImobilizadoController::class, 'create'])->name('ordemservicoimobilizado.create');
    Route::post('/store', [OrdemServicoImobilizadoController::class, 'store'])->name('ordemservicoimobilizado.store');
    Route::get('/{ordemservicoimobilizado}/edit', [OrdemServicoImobilizadoController::class, 'edit'])->name('ordemservicoimobilizado.edit');
    Route::put('/{ordemservicoimobilizado}', [OrdemServicoImobilizadoController::class, 'update'])->name('ordemservicoimobilizado.update');

    Route::post('/finalizar', [OrdemServicoImobilizadoController::class, 'onFinalizar'])->name('ordemservicoimobilizado.finalizar');
    Route::post('/solicitar/{id}', [OrdemServicoImobilizadoController::class, 'solicitarPecas'])->name('ordemservicoimobilizado.solicitar');
    Route::post('/voltar-estoque', [OrdemServicoImobilizadoController::class, 'onVoltarEstoque'])->name('ordemservicoimobilizado.voltar-estoque');
});

Route::prefix('estoqueimobilizado')->group(function () {
    Route::get('/', [EstoqueImobilizadoController::class, 'index'])->name('estoqueimobilizado.index');

    // Exportações
    Route::get('/export-csv', [EstoqueImobilizadoController::class, 'exportCsv'])->name('estoqueimobilizado.exportCsv');
    Route::get('/export-xls', [EstoqueImobilizadoController::class, 'exportXls'])->name('estoqueimobilizado.exportXls');
    Route::get('/export-pdf', [EstoqueImobilizadoController::class, 'exportPdf'])->name('estoqueimobilizado.exportPdf');
    Route::get('/export-xml', [EstoqueImobilizadoController::class, 'exportXml'])->name('estoqueimobilizado.exportXml');
});

Route::prefix('cadastroimobilizado')->group(function () {
    Route::get('/', [CadastroImobilizadoController::class, 'index'])->name('cadastroimobilizado.index');
    Route::get('/create', [CadastroImobilizadoController::class, 'create'])->name('cadastroimobilizado.create');
    Route::post('/store', [CadastroImobilizadoController::class, 'store'])->name('cadastroimobilizado.store');

    Route::get('{veiculo}/edit', [VeiculoController::class, 'edit'])->name('cadastroimobilizado.edit');

    Route::get('/{cadastroimobilizado}/edit', [CadastroImobilizadoController::class, 'edit'])->name('cadastroimobilizado.edit');

    Route::delete('/{cadastroimobilizado}', [CadastroImobilizadoController::class, 'destroy'])->name('cadastroimobilizado.destroy');

    Route::put('/{cadastroimobilizado}', [CadastroImobilizadoController::class, 'update'])->name('cadastroimobilizado.update');
});

Route::prefix('transfimobilizadoveiculo')->group(function () {
    Route::get('/', [TransfImobilizadoVeiculoController::class, 'index'])
        ->name('transfimobilizadoveiculo.index');
    Route::get('/create', [TransfImobilizadoVeiculoController::class, 'create'])
        ->name('transfimobilizadoveiculo.create');
    Route::post('/store', [TransfImobilizadoVeiculoController::class, 'store'])
        ->name('transfimobilizadoveiculo.store');

    Route::post('/verificarSituacao', [TransfImobilizadoVeiculoController::class, 'onVerificarSituacao'])
        ->name('transfimobilizadoveiculo.verificarSituacao');
    Route::post('/reprovar', [TransfImobilizadoVeiculoController::class, 'onReprovar'])
        ->name('transfimobilizadoveiculo.reprovar');

    // Ações auxiliares
    Route::post('/get-vehicle-data', [TransfImobilizadoVeiculoController::class, 'getVehicleData'])
        ->name('transfimobilizadoveiculo.getVehicleData');

    Route::post('/onJuridico', [TransfImobilizadoVeiculoController::class, 'onJuridico'])
        ->name('transfimobilizadoveiculo.juridico');

    Route::post('/onFrota', [TransfImobilizadoVeiculoController::class, 'onFrota'])
        ->name('transfimobilizadoveiculo.frota');

    Route::post('/onPatrimonio', [TransfImobilizadoVeiculoController::class, 'onPatrimonio'])
        ->name('transfimobilizadoveiculo.patrimonio');

    Route::post('/onFilial', [TransfImobilizadoVeiculoController::class, 'onFilial'])
        ->name('transfimobilizadoveiculo.filial');

    Route::post('/onConcluir', [TransfImobilizadoVeiculoController::class, 'onConcluir'])
        ->name('transfimobilizadoveiculo.concluir');

    Route::get('/{id}', [TransfImobilizadoVeiculoController::class, 'show'])
        ->name('transfimobilizadoveiculo.show');

    Route::get('/{transfimobilizadoveiculo}/edit', [TransfImobilizadoVeiculoController::class, 'edit'])
        ->name('transfimobilizadoveiculo.edit');

    Route::delete('/{transfimobilizadoveiculo}', [TransfImobilizadoVeiculoController::class, 'destroy'])
        ->name('transfimobilizadoveiculo.destroy');

    Route::put('/{transfimobilizadoveiculo}', [TransfImobilizadoVeiculoController::class, 'update'])
        ->name('transfimobilizadoveiculo.update');
});

Route::prefix('devolucaoimobilizadoveiculo')->group(function () {
    // Rotas básicas CRUD
    Route::get('/', [DevolucaoImobilizadoVeiculoController::class, 'index'])->name('devolucaoimobilizadoveiculo.index');
    Route::get('/create', [DevolucaoImobilizadoVeiculoController::class, 'create'])->name('devolucaoimobilizadoveiculo.create');
    Route::post('/store', [DevolucaoImobilizadoVeiculoController::class, 'store'])->name('devolucaoimobilizadoveiculo.store');
    Route::get('/{devolucaoimobilizadoveiculo}/edit', [DevolucaoImobilizadoVeiculoController::class, 'edit'])->name('devolucaoimobilizadoveiculo.edit');
    Route::put('/{devolucaoimobilizadoveiculo}', [DevolucaoImobilizadoVeiculoController::class, 'update'])->name('devolucaoimobilizadoveiculo.update');
    Route::delete('/{devolucaoimobilizadoveiculo}', [DevolucaoImobilizadoVeiculoController::class, 'destroy'])->name('devolucaoimobilizadoveiculo.destroy');

    // Ações do processo
    Route::post('/verificarSituacao', [DevolucaoImobilizadoVeiculoController::class, 'onVerificarSituacao'])->name('devolucaoimobilizadoveiculo.verificarsituacao');
    Route::post('/reprovar', [DevolucaoImobilizadoVeiculoController::class, 'onReprovar'])->name('devolucaoimobilizadoveiculo.reprovar');
    Route::post('/emitirOrdemServico', [DevolucaoImobilizadoVeiculoController::class, 'onEmitirOrdemServico'])->name('devolucaoimobilizadoveiculo.emitirordemservico');
    Route::post('/emitirSinistro', [DevolucaoImobilizadoVeiculoController::class, 'onEmitirSinistro'])->name('devolucaoimobilizadoveiculo.emitirsinistro');

    // Ações auxiliares
    Route::post('/get-vehicle-data', [DevolucaoImobilizadoVeiculoController::class, 'getVehicleData'])
        ->name('devolucaoimobilizadoveiculo.getVehicleData');
});

Route::prefix('ordemservicodiagnostico')->group(function () {
    Route::get('/', [OrdemServicoController::class, 'indexDiagnostico'])->name('ordemservicodiagnostico.indexDiagnostico');
});

/**
 * Rotas da API
 */
Route::prefix('api')->group(function () {
    // Produto
    Route::get('/produto/search', [ProdutoController::class, 'search'])->name('api.produto.search');
    Route::get('/produto/single/{id}', [ProdutoController::class, 'getById'])->name('api.produto.single');

    // Veículos
    Route::get('/veiculos/search', [VeiculoController::class, 'search'])->name('api.veiculos.search');
    Route::get('/veiculos/single/{id}', [VeiculoController::class, 'getById'])->name('api.veiculos.single');

    // Departamento
    Route::get('/departamento/search', [DepartamentoController::class, 'search'])->name('api.departamento.search');
    Route::get('/departamento/single/{id}', [DepartamentoController::class, 'getById'])->name('api.departamento.single');

    // Produtos Imobilizados
    Route::get('/produtosimobilizados/search', [ProdutosImobilizadosController::class, 'search'])->name('api.produtosimobilizados.search');
    Route::get('/produtosimobilizados/single/{id}', [ProdutosImobilizadosController::class, 'getById'])->name('api.produtosimobilizados.single');

    // Pessoal
    Route::get('/pessoal/search', [PessoalController::class, 'search'])->name('api.pessoal.search');
    Route::get('/pessoal/single/{id}', [PessoalController::class, 'getById'])->name('api.pessoal.single');

    // Fornecedor
    Route::get('/fornecedor/search', [FornecedorController::class, 'search'])->name('api.fornecedor.search');
    Route::get('/fornecedor/single/{id}', [FornecedorController::class, 'getById'])->name('api.fornecedor.single');
});

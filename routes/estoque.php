<?php

use App\Http\Controllers\Admin\RelacaoSolicitacaoPecaController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\EstoqueController;
use App\Http\Controllers\Admin\AjusteEstoqueController;
use App\Http\Controllers\Admin\AlmoxarifadoController;
use App\Http\Controllers\Admin\DevolucaoTransferenciaDiretaEstoqueController;
use App\Http\Controllers\Admin\DevolucaoSaidaEstoqueController;
use App\Http\Controllers\Admin\DevolucoesController;
use App\Http\Controllers\Admin\TransferenciaEntreEstoqueController;
use App\Http\Controllers\Admin\TransferenciaDiretaEstoqueController;
use App\Http\Controllers\Admin\CadastroProdutosEstoqueController;
use App\Http\Controllers\Admin\NotaFiscalEntradaController;
use App\Http\Controllers\Admin\SaidaProdutosEstoqueController;
use App\Http\Controllers\Admin\CheckListRecebimentoFornecedorController;
use App\Http\Controllers\Admin\EmissaoQrCodeProdutoController;
use App\Http\Controllers\Admin\EstoqueGraficoController;
use App\Http\Controllers\Admin\FornecedorController;
use App\Http\Controllers\Admin\ItensParaCompraController;
use App\Http\Controllers\Admin\ListaPedidoComprasController;
use App\Http\Controllers\Admin\ProdutoController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RequisicaoMaterialController;
use App\Models\Produto;


Route::get('/', function () {
    return view('auth.login');
});

// API para verificação e integração com módulo de compras
Route::get('/api/verificar-disponibilidade', [EstoqueController::class, 'verificarDisponibilidade'])
    ->name('estoque.verificar-disponibilidade');
Route::post('/api/verificar-e-gerar-solicitacao', [EstoqueController::class, 'verificarEGerarSolicitacao'])
    ->name('estoque.verificar-e-gerar-solicitacao');
Route::post('/api/entrada-pedido-compra', [EstoqueController::class, 'entradaPedidoCompra'])
    ->name('estoque.entrada-pedido-compra');
Route::get('/api/produtos/{id}', [EstoqueController::class, 'getProduto'])
    ->name('estoque.get-produto');

// Páginas especiais e listagens sem parâmetros
Route::group(['prefix' => 'cadastro-estoque'], function () {
    Route::get('/estoque-dashboard', [EstoqueController::class, 'dashboard'])->name('estoque.dashboard');
    Route::get('/', [EstoqueController::class, 'index'])->name('estoque.index');
    Route::get('/create', [EstoqueController::class, 'create'])->name('estoque.create');
    Route::get('/estoque-baixo', [EstoqueController::class, 'estoqueBaixo'])->name('estoque.estoque-baixo');
    Route::match(['GET', 'POST'], '/visualizar-transferencia', [EstoqueController::class, 'visualizarTransferencia'])->name('estoque.visualizar-transferencia');
    Route::post('/', [EstoqueController::class, 'store'])->name('estoque.store');
    Route::get('/transferir/{id}', [EstoqueController::class, 'transferenciaPage'])->name('estoque.transferir');
    Route::post('/transferir', [EstoqueController::class, 'enviarTransferencia'])->name('estoque.enviartransferencia');
});

// Rotas de grupos específicos
Route::group(['prefix' => 'ajuste-estoque'], function () {
    // ✅ Rotas GET - das mais específicas para as menos específicas
    Route::get('/', [AjusteEstoqueController::class, 'index'])->name('ajusteEstoque.index');
    Route::get('/criar', [AjusteEstoqueController::class, 'create'])->name('ajusteEstoque.create');

    // ✅ Rotas específicas ANTES da rota dinâmica /{id}
    Route::get('/codes', [AjusteEstoqueController::class, 'codes'])->name('ajusteEstoque.codes');
    Route::get('/gerar-pdf', [AjusteEstoqueController::class, 'gerarPDF'])->name('ajusteEstoque.geradorpdf');
    Route::get('/gerar-csv', [AjusteEstoqueController::class, 'gerarCsv'])->name('ajusteEstoque.geradoCsv');

    // ✅ Rotas com múltiplos parâmetros
    Route::get('/getEstoqueByFilial/{id_filial}', [AjusteEstoqueController::class, 'getEstoqueByFilial'])->name('ajusteEstoque.getEstoqueByFilial');
    Route::get('/getProdutoByEstoque/{id_filial}/{id_estoque}', [AjusteEstoqueController::class, 'getProdutoByEstoque'])->name('ajusteEstoque.getProdutoByEstoque');
    Route::get('/getEstoqueByProduto/{id_filial}/{id_estoque}', [AjusteEstoqueController::class, 'getEstoqueByProduto'])->name('ajusteEstoque.getEstoqueByProduto');
    Route::get('/produto/{id_estoque}', [AjusteEstoqueController::class, 'getProdutoPorFetch'])->name('ajusteEstoque.getProdutoPorFetch');
    Route::get('/produto/quantidade/{id_produto}', [AjusteEstoqueController::class, 'getQuantidadeProdutoPorFetch'])->name('ajusteEstoque.getQuantidadeProdutoPorFetch');

    // ✅ Rota dinâmica por último
    Route::get('/{id}', [AjusteEstoqueController::class, 'edit'])->name('ajusteEstoque.edit');

    // ✅ Rotas POST, PUT, DELETE
    Route::post('/', [AjusteEstoqueController::class, 'store'])->name('ajusteEstoque.store');
    Route::post('/gerar-codigo', [AjusteEstoqueController::class, 'codeGenerate'])->name('ajusteEstoque.codeGenerate');
    Route::put('/{id}', [AjusteEstoqueController::class, 'update'])->name('ajusteEstoque.update');
    Route::delete('/{id}', [AjusteEstoqueController::class, 'destroy'])->name('ajusteEstoque.delete');
});


Route::group(['prefix' => 'transferenciaDiretoEstoque'], function () {
    Route::get('export/csv', [TransferenciaDiretaEstoqueController::class, 'exportCsv'])->name('export.csv');
    Route::get('export/xls', [TransferenciaDiretaEstoqueController::class, 'exportXls'])->name('export.xls');
    Route::get('export/xml', [TransferenciaDiretaEstoqueController::class, 'exportXml'])->name('export.xml');
    Route::get('/transferencias/exportar-pdf', [TransferenciaDiretaEstoqueController::class, 'exportPdf'])->name('transferenciaDiretoEstoque.pdf');
    Route::get('/', [TransferenciaDiretaEstoqueController::class, 'index'])->name('transferenciaDiretoEstoque.index');
    Route::get('criar', [TransferenciaDiretaEstoqueController::class, 'create'])->name('transferenciaDiretoEstoque.create');
    Route::get('/visualizar/{id}', [TransferenciaDiretaEstoqueController::class, 'show'])->name('transferenciaDiretoEstoque.visualizar');
    Route::get('/gerar/{id}', [TransferenciaDiretaEstoqueController::class, 'gerarPdf'])->name('transferenciaDiretoEstoque.gerarPDF');
    Route::get('/envio/{id}', [TransferenciaDiretaEstoqueController::class, 'envio'])->name('transferenciaDiretoEstoque.envio');
    Route::get('/envio-transferencia/{id}', [TransferenciaDiretaEstoqueController::class, 'envioTransferencia'])->name('transferenciaDiretoEstoque.envioTransferencia');
    Route::get('/produtos-por-filial', [TransferenciaDiretaEstoqueController::class, 'getEstoquePorProduto']);
    Route::get('/{id}', [TransferenciaDiretaEstoqueController::class, 'edit'])->name('transferenciaDiretoEstoque.edit');
    Route::get('baixar/{id}', [TransferenciaDiretaEstoqueController::class, 'baixar'])->name('transferenciaDiretoEstoque.baixarview');
    Route::get('visualizar-modal/{id}', [TransferenciaDiretaEstoqueController::class, 'visualizarModal']);
    Route::get('confirmarRecebimento/{id}', [TransferenciaDiretaEstoqueController::class, 'confirmar'])->name('transferenciaDiretoEstoque.confirmarRecebimento');
    Route::get('recebimento/{id}', [TransferenciaDiretaEstoqueController::class, 'recebimento'])->name('transferenciaDiretoEstoque.recebimento');

    //Route::put('/{id}', [TransferenciaDiretaEstoqueController::class, 'update'])
    //  ->name('transferenciaDiretoEstoque.update');

    Route::post('/', [TransferenciaDiretaEstoqueController::class, 'store'])->name('transferenciaDiretoEstoque.store');
    Route::post('{id}/editar', [TransferenciaDiretaEstoqueController::class, 'updateTransferencia'])->name('transferenciaDiretaEstoque.updateTransferencia');
    Route::post('/transferencias/{id}/baixarTransferencia', [TransferenciaDiretaEstoqueController::class, 'processarBaixa'])->name('transferenciaDiretoEstoque.baixar');
    Route::post('{id}/confirmar-recebimento', [TransferenciaDiretaEstoqueController::class, 'confirmarRecebimento'])->name('transferenciaDiretoEstoque.confirmar');
    Route::post('solicitar/{id}', [TransferenciaDiretaEstoqueController::class, 'solicitar'])->name('transferenciaDiretoEstoque.solicitar');
});

Route::prefix('api')->group(function () {
    Route::get('/produtos/search', [ProdutoController::class, 'search'])->name('api.produtos.search');
    Route::get('/produtos/single/{id}', [ProdutoController::class, 'getById'])->name('api.produtos.single');

    Route::get('/users/search', [UserController::class, 'search'])->name('api.users.search');
    Route::get('/users/single/{id}', [UserController::class, 'getById'])->name('api.users.single');

    Route::get('/fornecedores/search', [FornecedorController::class, 'search'])->name('api.fornecedores.search');
    Route::get('/fornecedores/single/{id}', [FornecedorController::class, 'getById'])->name('api.fornecedores.single');
});
// Gestão Materiais

Route::resource('solicitacoes-materiais', RelacaoSolicitacaoPecaController::class);
//

Route::group(['prefix' => 'transferencia-entre-estoque'], function () {
    Route::get('export/csv', [TransferenciaEntreEstoqueController::class, 'exportCsv'])->name('transferenciaEntreEstoque.export.csv');
    Route::get('export/xls', [TransferenciaEntreEstoqueController::class, 'exportXls'])->name('transferenciaEntreEstoque.export.xls');
    Route::get('export/xml', [TransferenciaEntreEstoqueController::class, 'exportXml'])->name('transferenciaEntreEstoque.export.xml');
    Route::get('export/pdf', [TransferenciaEntreEstoqueController::class, 'exportPdf'])->name('transferenciaEntreEstoque.export.pdf');
    Route::get('visualizar/{id}', [TransferenciaEntreEstoqueController::class, 'visualizarModal']);
    Route::get('/', [TransferenciaEntreEstoqueController::class, 'index'])->name('transferenciaEntreEstoque.index');
    Route::get('/{id}', [TransferenciaEntreEstoqueController::class, 'edit'])->name('transferenciaEntreEstoque.edit');

    Route::post('/{id}', [TransferenciaEntreEstoqueController::class, 'storeProduto'])->name('transferenciaEntreEstoque.storeProduto');

    Route::put('/{id_item}', [TransferenciaEntreEstoqueController::class, 'update'])->name('transferenciaEntreEstoque.update');
});

Route::group(['prefix' => 'transferencia-direta-estoque-list'], function () {
    Route::get('/', [DevolucaoTransferenciaDiretaEstoqueController::class, 'index'])->name('transferenciaDiretaEstoqueList.index');
    Route::get('/gerar-pdf', [DevolucaoTransferenciaDiretaEstoqueController::class, 'gerarPDF'])->name('transferenciaDiretaEstoqueList.gerarPFD');
    Route::get('/gerar-csv', [DevolucaoTransferenciaDiretaEstoqueController::class, 'gerarCSV'])->name('transferenciaDiretaEstoqueList.gerarCSV');
});

// Rotas de devolucao saida de estoque
Route::group(['prefix' => 'devolucaosaidaestoque'], function () {
    Route::get('/', [DevolucaoSaidaEstoqueController::class, 'index'])->name('devolucaosaidaestoque.index');
    Route::get('criar', [DevolucaoSaidaEstoqueController::class, 'createDevProdutos'])->name('devolucaosaidaestoque.create_devProdutos');
    Route::get('editar/{id}', [DevolucaoSaidaEstoqueController::class, 'edit'])->name('devolucaosaidaestoque.edit');

    Route::get('criar/materiais', [DevolucaoSaidaEstoqueController::class, 'createDevMateriais'])->name('devolucaosaidaestoque.create_devMateriais');
    Route::get('/getProduto/{devolucaosaidaestoque}', [DevolucaoSaidaEstoqueController::class, 'getProdutosPorOrdemServico'])->name('devolucaosaidaestoque.getProduto');
    Route::get('/getMateriais/{devolucaosaidaestoque}', [DevolucaoSaidaEstoqueController::class, 'getProdutosPorSolicitacao'])->name('devolucaosaidaestoque.getMateriais');

    Route::post('/', [DevolucaoSaidaEstoqueController::class, 'storeDevProdutos'])->name('devolucaosaidaestoque.store_DevProdutos');
    Route::post('/store_devMats', [DevolucaoSaidaEstoqueController::class, 'storeDevMateriais'])->name('devolucaosaidaestoque.store_DevMateriais');

    Route::delete('excluir/{id}', [DevolucaoSaidaEstoqueController::class, 'onDeletePecas'])->name('devolucaosaidaestoque.excluir');
});

// Rotas com formulários específicos
Route::get('/{id}/entrada-form', [EstoqueController::class, 'entradaForm'])->name('estoque.entrada-form');
Route::post('/{id}/registrar-entrada', [EstoqueController::class, 'registrarEntrada'])->name('estoque.registrar-entrada');
Route::get('/{id}/saida-form', [EstoqueController::class, 'saidaForm'])->name('estoque.saida-form');
Route::post('/{id}/registrar-saida', [EstoqueController::class, 'registrarSaida'])->name('estoque.registrar-saida');
Route::get('/{id}/transferencia-form', [EstoqueController::class, 'transferenciaForm'])->name('estoque.transferencia-form');
Route::post('/{id}/registrar-transferencia', [EstoqueController::class, 'registrarTransferencia'])->name('estoque.registrar-transferencia');

// Gestão de itens de estoque
Route::get('/{id}/itens', [EstoqueController::class, 'itens'])->name('estoque.itens');
Route::post('/{id}/adicionar-item', [EstoqueController::class, 'adicionarItem'])->name('estoque.adicionar-item');
Route::get('/{id}/itens/{idItem}/movimentacoes', [EstoqueController::class, 'movimentacoes'])->name('estoque.movimentacoes');
Route::put('/{id}/itens/{idItem}', [EstoqueController::class, 'atualizarItem'])->name('estoque.atualizar-item');
Route::delete('/{id}/itens/{idItem}', [EstoqueController::class, 'removerItem'])->name('estoque.remover-item');
Route::post('/{id}/itens/{idItem}/ajuste-inventario', [EstoqueController::class, 'ajusteInventario'])->name('estoque.ajuste-inventario');

// No final, colocamos as rotas mais genéricas de CRUD
Route::get('/{id}/edit', [EstoqueController::class, 'edit'])->name('estoque.edit')->where('id', '[0-9]+');
Route::put('/{id}', [EstoqueController::class, 'update'])->name('estoque.update')->where('id', '[0-9]+');
Route::delete('/{id}', [EstoqueController::class, 'destroy'])->name('estoque.delete')->where('id', '[0-9]+');
Route::get('/{id}', [EstoqueController::class, 'show'])->name('estoque.show')->where('id', '[0-9]+');


// Rotas de cadastro de produtos no estoque
Route::group(['prefix' => 'cadastroprodutosestoque'], function () {
    Route::get('/', [CadastroProdutosEstoqueController::class, 'index'])->name('cadastroprodutosestoque.index');
    Route::get('criar', [CadastroProdutosEstoqueController::class, 'create'])->name('cadastroprodutosestoque.create');
    Route::get('{cadastroprodutosestoque}', [CadastroProdutosEstoqueController::class, 'show'])
        ->name('cadastroprodutosestoque.show');

    Route::post('/', [CadastroProdutosEstoqueController::class, 'store'])->name('cadastroprodutosestoque.store');
    Route::get('{cadastroprodutosestoque}/editar', [CadastroProdutosEstoqueController::class, 'edit'])->name('cadastroprodutosestoque.edit');
    Route::put('{cadastroprodutosestoque}', [CadastroProdutosEstoqueController::class, 'update'])
        ->name('cadastroprodutosestoque.update');

    Route::delete('{cadastroprodutosestoque}', [CadastroProdutosEstoqueController::class, 'destroy'])
        ->name('cadastroprodutosestoque.destroy');
});

// Rotas de Saida do Produto do Estoque
Route::group(['prefix' => 'saidaprodutosestoque'], function () {
    // ✅ Rotas GET - páginas principais e listagens
    Route::get('/', [SaidaProdutosEstoqueController::class, 'index'])->name('saidaprodutosestoque.index');

    // ✅ Rotas GET - funcionalidades específicas ANTES das rotas dinâmicas
    Route::get('/produtos-por-filial', [SaidaProdutosEstoqueController::class, 'getEstoquePorProduto']);
    Route::get('/unificar-pecas', [SaidaProdutosEstoqueController::class, 'onEditBatchArray'])->name('saidaprodutosestoque.unificar');

    // ✅ Rotas GET - visualizações específicas por ID
    Route::get('/view-unificado/{id}', [SaidaProdutosEstoqueController::class, 'viewUnificados'])->name('saidaprodutosestoque.viewunificado');
    Route::get('/view-materiais/{id}', [SaidaProdutosEstoqueController::class, 'viewMateriais'])->name('saidaprodutosestoque.viewmateriais');
    Route::get('/view-pecas/{id}', [SaidaProdutosEstoqueController::class, 'viewPecas'])->name('saidaprodutosestoque.viewpecas');

    // ✅ Rotas GET - impressões e relatórios
    Route::get('/imprimir/{saidaprodutosestoque}', [SaidaProdutosEstoqueController::class, 'onImprimir'])->name('saidaprodutosestoque.imprimir');
    Route::get('/imprimir-pecas/{saidaprodutosestoque}', [SaidaProdutosEstoqueController::class, 'onImprimirPecas'])->name('saidaprodutosestoque.imprimir-pecas');

    // ✅ Rotas GET - ações específicas por registro
    Route::get('/assumir/{saidaprodutosestoque}', [SaidaProdutosEstoqueController::class, 'onAssumir'])->name('saidaprodutosestoque.assumir');
    Route::get('/{saidaprodutosestoque}/visualizar', [SaidaProdutosEstoqueController::class, 'onVizualizar'])->name('saidaprodutosestoque.visualizar');
    Route::get('{saidaprodutosestoque}/editar', [SaidaProdutosEstoqueController::class, 'edit'])->name('saidaprodutosestoque.edit');
    Route::get('{saidaprodutosestoque}/editarTransferencia', [SaidaProdutosEstoqueController::class, 'editTransferencia'])->name('saidaprodutosestoque.editTransferencia');

    // ✅ Rotas POST - finalizações gerais
    Route::post('/finalizarbaixa', [SaidaProdutosEstoqueController::class, 'finalizarBaixa'])
        ->name('saidaprodutosestoque.finalizarbaixa');
    Route::post('/finalizarbaixaconsultamateriais', [SaidaProdutosEstoqueController::class, 'finalizarbaixaconsultamateriais'])
        ->name('saidaprodutosestoque.finalizarbaixaconsultamateriais');

    // ✅ Rotas POST - finalizações específicas (sem parâmetro ID)
    Route::post('/finalizarBaixaTransferencia', [SaidaProdutosEstoqueController::class, 'finalizarBaixaTransferencia'])->name('saidaprodutosestoque.finalizarBaixaTransferencia');

    // ✅ Rotas POST - ações específicas por ID
    Route::post('/processarBaixa/{id}', [SaidaProdutosEstoqueController::class, 'processarBaixa'])->name('saidaprodutosestoque.processarBaixa');
    Route::post('/transferirProduto/{id}', [SaidaProdutosEstoqueController::class, 'transferirProduto'])->name('saidaprodutosestoque.processartransferirProdutoBaixa');
    Route::post('/estornarBaixa/{id}', [SaidaProdutosEstoqueController::class, 'estornarBaixa'])->name('saidaprodutosestoque.estornarBaixa');
    Route::post('/estornarTransferencia/{id}', [SaidaProdutosEstoqueController::class, 'estornarTransferencia'])->name('saidaprodutosestoque.estornarTransferencia');
    Route::post('/cancelarTransferencia/{id}', [SaidaProdutosEstoqueController::class, 'cancelarTransferencia'])->name('saidaprodutosestoque.cancelarTransferencia');
});


//devoluções
Route::group(['prefix' => 'devolucoes'], function () {
    Route::get('/', [DevolucoesController::class, 'index'])->name('devolucoes.index');
    Route::get('{devolucoes}/edit_devTransfDireta', [DevolucoesController::class, 'edit_devTransfDireta'])->name('devolucoes.edit_devTransfDireta');
    Route::get('{devolucoes}/edit_devRequisicaoPecas', [DevolucoesController::class, 'edit_devRequisicaoPecas'])->name('devolucoes.edit_devRequisicaoPecas');
    Route::get('{devolucoes}/edit_devMatsMatriz', [DevolucoesController::class, 'edit_devMatsMatriz'])->name('devolucoes.edit_devMatsMatriz');
    Route::get('/{id}/dados', [DevolucoesController::class, 'getDadosDevolucaoMatriz'])->name('devolucoes.matriz.dados');

    Route::post('{devolucoes}/onGerarDevolucao', [DevolucoesController::class, 'onGerarDevolucao'])->name('devolucoes.onGerarDevolucao');
    Route::post('/onGerarTransferencia', [DevolucoesController::class, 'onGerarTransferencia'])->name('devolucoes.onGerarTransferencia');

    Route::put('{devolucoes}', [DevolucoesController::class, 'update_devTransfDireta'])->name('devolucoes.update_devTransfDireta');
    Route::put('RequisicaoPecas/{devolucoes}', [DevolucoesController::class, 'update_devRequisicaoPecas'])->name('devolucoes.update_devRequisicaoPecas');
    Route::put('DevMatriz/{devolucoes}', [DevolucoesController::class, 'update_devMatsMatriz'])->name('devolucoes.update_devMatsMatriz');
});

// Rotas de nota fiscal de entrada
Route::group(['prefix' => 'notafiscalentrada'], function () {
    Route::get('/', [NotaFiscalEntradaController::class, 'index'])->name('notafiscalentrada.index');
    Route::get('criar', [NotaFiscalEntradaController::class, 'create'])->name('notafiscalentrada.create');
    Route::get('/{id}/dados', [NotaFiscalEntradaController::class, 'getNotaFiscalEntradaItens'])->name('notafiscalentrada.dados');
    Route::get('/{id}/gerarNumFogo', [NotaFiscalEntradaController::class, 'indexGerarNumFogo'])->name('notafiscalentrada.gerarNumFogo');

    Route::post('/{id}/LancarNumFogo', [NotaFiscalEntradaController::class, 'onLancarNumFogoPneus'])->name('notafiscalentrada.lancarNumFogo');
    Route::post('/', [NotaFiscalEntradaController::class, 'store'])->name('notafiscalentrada.store');
    Route::post('/buscarDadosNFe', [NotaFiscalEntradaController::class, 'onCarregarDados'])->name('notafiscalentrada.buscarDadosNFe');
    Route::post('/buscarPedido', [NotaFiscalEntradaController::class, 'buscarPedido'])->name('notafiscalentrada.buscarPedido');
    Route::post('/atualizaEstoque', [NotaFiscalEntradaController::class, 'onAtualizarEstoque'])->name('notafiscalentrada.atualizaEstoque');
    Route::post('/handleConfirmation', [NotaFiscalEntradaController::class, 'onHandleUserConfirmation'])->name('notafiscalentrada.handleConfirmation');
    Route::post('/relNumFogo', [NotaFiscalEntradaController::class, 'onImprimirNumFogoGerado'])->name('notafiscalentrada.relNumFogo');
    Route::get('{notafiscalentrada}/edit', [NotaFiscalEntradaController::class, 'edit'])->name('notafiscalentrada.edit');
    Route::get('{notafiscalentrada}/devolucao', [NotaFiscalEntradaController::class, 'devolucao'])->name('notafiscalentrada.devolucao');
    Route::put('{notafiscalentrada}/devolve', [NotaFiscalEntradaController::class, 'devolve'])->name('notafiscalentrada.devolve');
    Route::put('{notafiscalentrada}', [NotaFiscalEntradaController::class, 'update'])->name('notafiscalentrada.update');

    Route::delete('{notafiscalentrada}', [NotaFiscalEntradaController::class, 'destroy'])->name('notafiscalentrada.destroy');
});

Route::group(['prefix' => 'checklistrecebimentofornecedor'], function () {
    Route::get('/{idNotaFiscalEntrada}', [CheckListRecebimentoFornecedorController::class, 'index'])->name('checklistrecebimentofornecedor.index');

    Route::post('/', [CheckListRecebimentoFornecedorController::class, 'store'])->name('checklistrecebimentofornecedor.store');
});

Route::group(['prefix' => 'listapedidocompra'], function () {
    Route::get('/', [ListaPedidoComprasController::class, 'index'])->name('listapedidocompra.index');
    Route::get('/{id}', [ListaPedidoComprasController::class, 'visualizarModal'])->name('listapedidocompra.visualizar');
    Route::get('/listapedidocompra/{id}', [ListaPedidoComprasController::class, 'gerarPdf'])->name('listapedidocompra.pdf');
});

Route::group(['prefix' => 'requisicaoMaterial'], function () {
    // ✅ Rotas GET - das mais específicas para as menos específicas
    Route::get('/', [RequisicaoMaterialController::class, 'index'])->name('requisicaoMaterial.index');
    Route::get('/criar', [RequisicaoMaterialController::class, 'create'])->name('requisicaoMaterial.create');

    // ✅ Rotas específicas de produto ANTES das rotas dinâmicas
    Route::get('/produto/busca', [RequisicaoMaterialController::class, 'searchProdutos'])->name('requisicaoMaterial.searchProdutos');
    Route::get('/produto/disponibilidade', [RequisicaoMaterialController::class, 'getDisponibilidadeProduto'])->name('requisicaoMaterial.disponibilidadeProduto');
    Route::get('/produto/debug-disponibilidade', [RequisicaoMaterialController::class, 'debugDisponibilidade'])->name('requisicaoMaterial.debugDisponibilidade');

    // ✅ Rotas com parâmetros específicos
    Route::get('/getProdutosPorRequisicao/{requisicaoMaterial}/dados', [RequisicaoMaterialController::class, 'getProdutosPorRequisicao'])->name('requisicaoMaterial.getProdutosPorRequisicao');
    Route::get('{requisicaoMaterial}/edit', [RequisicaoMaterialController::class, 'edit'])->name('requisicaoMaterial.edit');

    // ✅ Rota dinâmica por último
    Route::get('/{id}', [RequisicaoMaterialController::class, 'show'])->name('requisicaoMaterial.show');

    // ✅ Rotas POST
    Route::post('/', [RequisicaoMaterialController::class, 'store'])->name('requisicaoMaterial.store');
    Route::post('/getProdutosPorTipo', [RequisicaoMaterialController::class, 'getProdutosPorTipo'])->name('requisicaoMaterial.getProdutosPorTipo');
    Route::post('/enviarAprovacao', [RequisicaoMaterialController::class, 'enviarAprovacao'])->name('requisicaoMaterial.enviarAprovacao');
    Route::post('/aprovar', [RequisicaoMaterialController::class, 'aprovar'])->name('requisicaoMaterial.aprovar');
    Route::post('/aprovar-sem-transferencia', [RequisicaoMaterialController::class, 'aprovarSemTransferencia'])->name('requisicaoMaterial.aprovarSemTransferencia');
    Route::post('/aprovar-com-transferencia', [RequisicaoMaterialController::class, 'processarAprovacaoComTransferencia'])->name('requisicaoMaterial.aprovarComTransferencia');
    Route::post('/teste-transferencia', [RequisicaoMaterialController::class, 'testarTransferencia'])->name('requisicaoMaterial.testarTransferencia'); // ROTA TEMPORÁRIA PARA DEBUG
    Route::get('/teste-transferencia-get', [RequisicaoMaterialController::class, 'testarTransferencia'])->name('requisicaoMaterial.testarTransferenciaGet'); // ROTA TEMPORÁRIA PARA DEBUG GET
    Route::get('/debug-basic', function () {
        return response()->json(['status' => 'OK', 'user' => Auth::user()->name ?? 'Não autenticado', 'time' => now()]);
    })->name('requisicaoMaterial.debugBasic'); // ROTA TEMPORÁRIA PARA DEBUG
    Route::post('/revisar', [RequisicaoMaterialController::class, 'revisar'])->name('requisicaoMaterial.revisar');
    Route::post('/reprovar', [RequisicaoMaterialController::class, 'reprovar'])->name('requisicaoMaterial.reprovar');
    Route::post('/onProduto/{requisicaoMaterial}', [RequisicaoMaterialController::class, 'onProdutos'])->name('requisicaoMaterial.onProduto');

    // ✅ Rotas PUT
    Route::put('{requisicaoMaterial}', [RequisicaoMaterialController::class, 'update'])->name('requisicaoMaterial.update');

    // ✅ Rotas DELETE
    Route::delete('{requisicaoMaterial}', [RequisicaoMaterialController::class, 'destroy'])->name('requisicaoMaterial.destroy');
});


Route::group(['prefix'  => 'itensparacompra'], function () {
    Route::get('/', [ItensParaCompraController::class, 'index'])->name('itensparacompra.index');
    Route::get('/subgrupos', [ItensParaCompraController::class, 'getSubgrupos'])->name('itensparacompra.subgrupos');
    Route::post('/criar-solicitacao', [ItensParaCompraController::class, 'criarSolicitacao'])->name('itens-para-compra.criar-solicitacao');
});

Route::group(['prefix'  => 'emissaoqrcode'], function () {
    Route::get('/', [EmissaoQrCodeProdutoController::class, 'index'])->name('emissaoqrcode.index');
});

Route::group(['prefix'  => 'consultaprodutografico'], function () {
    Route::get('/', [EstoqueGraficoController::class, 'index'])->name('consultaprodutografico.index');
    Route::get('/estoque-grafico/dados/{id}', [EstoqueGraficoController::class, 'getDadosProduto'])->name('consultaprodutografico.dados');
    Route::post('/estoque-produtos', [EstoqueGraficoController::class, 'getEstoqueProdutos'])->name('consultaprodutografico.produtos');
});

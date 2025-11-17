<?php

use App\Http\Controllers\Admin\AprovarPedidoController;
use App\Http\Controllers\Admin\ContratoFornecedorController;
use App\Http\Controllers\Admin\CotacoesController;
use App\Http\Controllers\Admin\NotaFiscalController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\FornecedorController;
use App\Http\Controllers\Admin\ItemSolicitacaoCompraController;
use App\Http\Controllers\Admin\OrcamentoController;
use App\Http\Controllers\Admin\PedidoCompraController;
use App\Http\Controllers\Admin\SolicitacaoCompraController;
use App\Http\Controllers\Admin\NotaFiscalAvulsaController;
use App\Http\Controllers\Admin\PedidosNotasController;
use App\Http\Controllers\Admin\NotasLancadasController;
use App\Http\Controllers\Admin\LancamentoNotasController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ValidarCotacaoController;
use Illuminate\Support\Facades\Auth;

// Agrupamento para o módulo de compras
Route::group(['prefix' => 'compras', 'as' => 'compras.'], function () {
    // Dashboard do módulo de compras
    Route::get('/', function () {
        // Capturar dados para o dashboard
        $solicitacoesPendentes = \App\Models\SolicitacaoCompra::pendentes()->count();
        $pedidosPendentes = \App\Models\PedidoCompra::pendentesAprovacao()->count();
        $pedidosAprovados = \App\Models\PedidoCompra::aprovados()->count();
        $valorTotalMes = \App\Models\PedidoCompra::where('data_inclusao', '>=', now()->startOfMonth())
            ->where('situacao_pedido', '!=', 6)
            ->sum('valor_total');

        $pedidosRecentes = \App\Models\PedidoCompra::with(['fornecedor', 'comprador'])
            ->orderBy('data_inclusao', 'desc')
            ->limit(5)
            ->get();

        $meusPedidos = \App\Models\PedidoCompra::with(['fornecedor'])
            ->where('id_comprador', Auth::id())
            ->orderBy('data_inclusao', 'desc')
            ->limit(5)
            ->get();

        $pedidosPendentesAprovacao = \App\Models\PedidoCompra::with(['fornecedor', 'solicitacaoCompra.solicitante'])
            ->pendentesAprovacao()
            ->limit(5)
            ->get();

        $ultimasAtividades = \App\Models\PedidoCompra::with(['comprador'])
            ->orderBy('data_inclusao', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($pedido) {
                return (object) [
                    'id' => $pedido->id_pedido_compras,
                    'tipo' => 'pedido',
                    'descricao' => "Pedido #{$pedido->numero} {$pedido->status}",
                    'usuario' => $pedido->comprador,
                    'created_at' => $pedido->data_inclusao
                ];
            });

        return view('admin.compras.dashboard', compact(
            'solicitacoesPendentes',
            'pedidosPendentes',
            'pedidosAprovados',
            'valorTotalMes',
            'pedidosRecentes',
            'meusPedidos',
            'pedidosPendentesAprovacao',
            'ultimasAtividades'
        ));
    })->name('dashboard');


    // Listagens específicas
    Route::get('/aprovados', [PedidoCompraController::class, 'listarAprovados'])
        ->name('pedidos.aprovados');
    Route::get('/cancelados', [PedidoCompraController::class, 'listarCancelados'])
        ->name('pedidos.cancelados');
    Route::get('/faturados', [PedidoCompraController::class, 'listarFaturados'])
        ->name('pedidos.faturados');
    Route::get('/pendentes-aprovacao', [PedidoCompraController::class, 'listarPendentesAprovacao'])
        ->name('pedidos.pendentes-aprovacao');

    // Rotas para lançamento de notas (consolidado mais abaixo)

    // Rotas para pedidos
    Route::group(['prefix' => 'pedidos'], function () {
        // Listagens principais
        Route::get('/', [PedidoCompraController::class, 'index'])
            ->name('pedidos.index');

        // CRUD básico
        Route::get('/create', [PedidoCompraController::class, 'create'])
            ->name('pedidos.create');
        Route::post('/store', [PedidoCompraController::class, 'store'])
            ->name('pedidos.store');
        Route::get('/{pedido}', [PedidoCompraController::class, 'show'])
            ->name('pedidos.show');
        Route::get('/{pedido}/edit', [PedidoCompraController::class, 'edit'])
            ->name('pedidos.edit');
        Route::put('/{pedido}', [PedidoCompraController::class, 'update'])
            ->name('pedidos.update');
        Route::delete('/{pedido}', [PedidoCompraController::class, 'destroy'])
            ->name('pedidos.destroy');

        // Gestão de status
        Route::post('/{pedido}/aprovar', [PedidoCompraController::class, 'aprovar'])
            ->name('pedidos.aprovar');
        Route::post('/{pedido}/rejeitar', [PedidoCompraController::class, 'rejeitar'])
            ->name('pedidos.rejeitar');
        Route::post('/{pedido}/enviar', [PedidoCompraController::class, 'enviar'])
            ->name('pedidos.enviar');
        Route::post('/{pedido}/cancelar', [PedidoCompraController::class, 'cancelar'])
            ->name('pedidos.cancelar');
        Route::post('/{pedido}/finalizar', [PedidoCompraController::class, 'finalizar'])
            ->name('pedidos.finalizar');


        // Aprovações em lote
        Route::post('/aprovar-lote', [PedidoCompraController::class, 'aprovarLote'])
            ->name('pedidos.aprovar-lote');
        Route::post('/finalizar-lote', [PedidoCompraController::class, 'finalizarLote'])
            ->name('pedidos.finalizar-lote');

        // Impressão e exportações
        Route::get('/{pedido}/imprimir', [PedidoCompraController::class, 'imprimir'])
            ->name('pedidos.imprimir');
        Route::get('/export-csv', [PedidoCompraController::class, 'exportCsv'])
            ->name('pedidos.exportCsv');
        Route::get('/export-xls', [PedidoCompraController::class, 'exportXls'])
            ->name('pedidos.exportXls');
        Route::get('/export-pdf', [PedidoCompraController::class, 'exportPdf'])
            ->name('pedidos.exportPdf');

        // Gestão de itens
        Route::get('/{pedido}/itens', [PedidoCompraController::class, 'listarItens'])
            ->name('pedidos.itens');
        Route::post('/{pedido}/itens/adicionar', [PedidoCompraController::class, 'adicionarItem'])
            ->name('pedidos.itens.adicionar');
        Route::put('/{pedido}/itens/{item}', [PedidoCompraController::class, 'atualizarItem'])
            ->name('pedidos.itens.atualizar');
        Route::delete('/{pedido}/itens/{item}', [PedidoCompraController::class, 'removerItem'])
            ->name('pedidos.itens.remover');
    });

    // Rotas para orçamentos
    Route::group(['prefix' => 'orcamentos'], function () {
        // CRUD básico
        Route::get('/', [OrcamentoController::class, 'index'])
            ->name('orcamentos.index');
        Route::get('/create', [OrcamentoController::class, 'create'])
            ->name('orcamentos.create');
        Route::post('/store', [OrcamentoController::class, 'store'])
            ->name('orcamentos.store');

        // Funcionalidades específicas (devem vir ANTES das rotas com parâmetros dinâmicos)
        Route::get('/comparativo', [OrcamentoController::class, 'comparativo'])
            ->name('orcamentos.comparativo');

        // Exportações
        Route::get('/export-pdf', [OrcamentoController::class, 'exportPdf'])
            ->name('orcamentos.export-pdf');
        Route::get('/export-excel', [OrcamentoController::class, 'exportExcel'])
            ->name('orcamentos.export-excel');
        Route::get('/export-csv', [OrcamentoController::class, 'exportCsv'])
            ->name('orcamentos.export-csv');
        Route::get('/export-comparativo', [OrcamentoController::class, 'exportComparativo'])
            ->name('orcamentos.export-comparativo');

        // Rotas com parâmetros dinâmicos
        Route::get('/{orcamento}', [OrcamentoController::class, 'show'])
            ->name('orcamentos.show');
        Route::get('/{orcamento}/edit', [OrcamentoController::class, 'edit'])
            ->name('orcamentos.edit');
        Route::put('/{orcamento}', [OrcamentoController::class, 'update'])
            ->name('orcamentos.update');
        Route::delete('/{orcamento}', [OrcamentoController::class, 'destroy'])
            ->name('orcamentos.destroy');
        Route::post('/{orcamento}/selecionar', [OrcamentoController::class, 'selecionar'])
            ->name('orcamentos.selecionar');
        Route::post('/{orcamento}/rejeitar', [OrcamentoController::class, 'rejeitar'])
            ->name('orcamentos.rejeitar');
    });

    // Rotas para validação de cotações
    Route::group(['prefix' => 'validarcotacoes', 'as' => 'validarcotacoes.'], function () {
        // Listagem principal
        Route::get('/', [ValidarCotacaoController::class, 'index'])
            ->name('index');

        // CRUD básico
        Route::post('/', [ValidarCotacaoController::class, 'store'])
            ->name('store');
        Route::get('/{id}/edit', [ValidarCotacaoController::class, 'edit'])
            ->name('edit');
        Route::put('/{id}', [ValidarCotacaoController::class, 'update'])
            ->name('update');

        // Rota para buscar cotações via AJAX
        Route::get('/cotacoes/{id}', [ValidarCotacaoController::class, 'getCotacoes'])
            ->name('cotacoes');

        // Rotas para ações de validação
        Route::post('/validar', [ValidarCotacaoController::class, 'validarCotacao'])
            ->name('validar');
        Route::post('/recusar', [ValidarCotacaoController::class, 'recusarCotacao'])
            ->name('recusar');
        Route::post('/cancelar', [ValidarCotacaoController::class, 'cancelarCotacao'])
            ->name('cancelar');
    });

    Route::group(['prefix' => 'aprovarpedido', 'as' => 'aprovarpedido.'], function () {
        // Listagem principal
        Route::get('/', [AprovarPedidoController::class, 'index'])
            ->name('index');
        // Rota específica para aprovação (definida antes de rotas com parâmetros para evitar conflitos)
        Route::get('/aprovarCotacoes', [AprovarPedidoController::class, 'aprovarCotacao'])
            ->name('aprovarCotacoes');
        // Aceita POST para receber JSON do front-end
        Route::post('/aprovarCotacoes', [AprovarPedidoController::class, 'aprovarCotacao'])
            ->name('aprovarCotacoes.post');

        Route::get('/{id}',  [AprovarPedidoController::class, 'show'])
            ->name('show');

        // CRUD básico
        Route::post('/', [ValidarCotacaoController::class, 'store'])
            ->name('store');
        Route::get('/{id}/edit', [AprovarPedidoController::class, 'edit'])
            ->name('edit');
        Route::put('/{id}', [AprovarPedidoController::class, 'update'])
            ->name('update');

        // Rota para buscar cotações via AJAX
        Route::get('/cotacoes/{id}', [AprovarPedidoController::class, 'getCotacoes'])
            ->name('cotacoes');

        // Rota para buscar cotações completas para o modal
        Route::get('/cotacoes-completas/{id}', [AprovarPedidoController::class, 'getCotacoesCompletas'])
            ->name('cotacoes.completas');

        // Rota para gerar cotação com itens selecionados
        Route::post('/gerar-cotacao', [AprovarPedidoController::class, 'gerarCotacao'])
            ->name('gerar.cotacao');

        Route::post('/cancelar', [AprovarPedidoController::class, 'onCancelar'])
            ->name('cancelar');
    });

    // Rotas para solicitações de compra
    Route::group(['prefix' => 'solicitacoes'], function () {
        /**
         * Listagens principais
         */
        Route::get('/', [SolicitacaoCompraController::class, 'index'])->name('solicitacoes.index');
        Route::get('/aprovadas', [SolicitacaoCompraController::class, 'aprovadas'])->name('solicitacoes.aprovadas');
        Route::get('/pendentes', [SolicitacaoCompraController::class, 'listarPendentes'])->name('solicitacoes.pendentes');
        Route::get('/peruser', [SolicitacaoCompraController::class, 'listarPerUser'])->name('solicitacoes.peruser');

        /**
         * Rotas de criação (devem vir antes das rotas com {id})
         */
        Route::get('/create', [SolicitacaoCompraController::class, 'create'])->name('solicitacoes.create');

        /**
         * CRUD básico
         */
        Route::post('/', [SolicitacaoCompraController::class, 'store'])->name('solicitacoes.store');
        Route::get('/{id}', [SolicitacaoCompraController::class, 'show'])->name('solicitacoes.show');
        Route::get('/{id}/edit', [SolicitacaoCompraController::class, 'edit'])->name('solicitacoes.edit');
        Route::put('/{id}', [SolicitacaoCompraController::class, 'update'])->name('solicitacoes.update');
        Route::delete('/{id}', [SolicitacaoCompraController::class, 'destroy'])->name('solicitacoes.destroy');

        /**
         * Fluxo de aprovação e status
         */
        Route::post('/{id}/enviar-aprovacao', [SolicitacaoCompraController::class, 'enviarParaAprovacao'])->name('solicitacoes.enviar-aprovacao');
        Route::post('/{id}/aprovar-gestor', [SolicitacaoCompraController::class, 'aprovarGestor'])->name('solicitacoes.aprovar-gestor');
        Route::post('/{id}/reprovar-gestor', [SolicitacaoCompraController::class, 'reprovarGestor'])->name('solicitacoes.reprovar-gestor');

        /**
         * Rotas antigas (mantidas para compatibilidade)
         */
        Route::post('/{id}/aprovar', [SolicitacaoCompraController::class, 'aprovar'])->name('solicitacoes.aprovar');
        Route::post('/{id}/reprovar', [SolicitacaoCompraController::class, 'reprovar'])->name('solicitacoes.reprovar');
        Route::post('/{id}/cancelar', [SolicitacaoCompraController::class, 'cancelar'])->name('solicitacoes.cancelar');
        Route::post('/{id}/finalizar', [SolicitacaoCompraController::class, 'finalizar'])->name('solicitacoes.finalizar');
        Route::post('/{id}/rejeitar', [SolicitacaoCompraController::class, 'rejeitar'])->name('solicitacoes.rejeitar');
        /**
         * Gestão de unidade (extra)
         */
        Route::post('/pega-unidade', [SolicitacaoCompraController::class, 'pegaUnidade'])->name('solicitacoes.pega-unidade');

        /**
         * Gestão de itens
         */
        Route::post('/itens/store', [SolicitacaoCompraController::class, 'storeItem'])->name('solicitacoes.itens.store');
        Route::put('/itens/{item}', [SolicitacaoCompraController::class, 'updateItem'])->name('solicitacoes.itens.update');
        Route::delete('/itens/{item}', [SolicitacaoCompraController::class, 'destroyItem'])->name('solicitacoes.itens.destroy');

        /**
         * Geração de pedido
         */
        Route::post('/{id}/gerar-pedido', [SolicitacaoCompraController::class, 'gerarPedido'])->name('solicitacoes.gerar-pedido');

        /**
         * Desmembramento
         */
        Route::get('/{id}/desmembrar', [SolicitacaoCompraController::class, 'formDesmembrar'])->name('solicitacoes.form-desmembrar');
        Route::post('/{id}/desmembrar', [SolicitacaoCompraController::class, 'desmembrar'])->name('solicitacoes.desmembrar');
    });


    // Rotas para notasfiscais
    Route::group(['prefix' => 'notasfiscais'], function () {
        Route::get('/', [NotaFiscalController::class, 'index'])
            ->name('notasfiscais.index');
        Route::get('/create', [NotaFiscalController::class, 'create'])
            ->name('notasfiscais.create');
        Route::post('/store', [NotaFiscalController::class, 'store'])
            ->name('notasfiscais.store');
        Route::get('/{nota}', [NotaFiscalController::class, 'show'])
            ->name('notasfiscais.show');
        Route::get('/{nota}/edit', [NotaFiscalController::class, 'edit'])
            ->name('notasfiscais.edit');
        Route::put('/{nota}', [NotaFiscalController::class, 'update'])
            ->name('notasfiscais.update');
        Route::delete('/{nota}', [NotaFiscalController::class, 'destroy'])
            ->name('notasfiscais.destroy');

        // Exportação
        Route::get('/export-pdf', [NotaFiscalController::class, 'exportPdf'])
            ->name('notasfiscais.export-pdf');

        // Rotas para notas avulsas
        Route::group(['prefix' => 'avulsas', 'as' => 'avulsas.'], function () {
            Route::get('/', [NotaFiscalAvulsaController::class, 'index'])
                ->name('index');

            Route::get('/create', [NotaFiscalAvulsaController::class, 'create'])
                ->name('create');

            Route::post('/store', [NotaFiscalAvulsaController::class, 'store'])
                ->name('store');

            Route::get('/{nota}', [NotaFiscalAvulsaController::class, 'show'])
                ->name('show');

            Route::get('/{nota}/edit', [NotaFiscalAvulsaController::class, 'edit'])
                ->name('edit');

            Route::put('/{nota}', [NotaFiscalAvulsaController::class, 'update'])
                ->name('update');

            Route::delete('/{nota}', [NotaFiscalAvulsaController::class, 'destroy'])
                ->name('destroy');

            // Notas Avulsas
            Route::get('/notasfiscais/avulsas/buscar-pedido', [NotaFiscalAvulsaController::class, 'buscarPedido'])
                ->name('notasfiscais.avulsas.buscar-pedido');

            // Exportação
            Route::get('/export-pdf', [NotaFiscalAvulsaController::class, 'exportPdf'])
                ->name('export-pdf');

            Route::delete('/notafiscal/avulsas/{id}/desvincular', [NotaFiscalAvulsaController::class, 'desvincularPedido'])
                ->name('notasfiscais.avulsas.desvincular-pedido');
        });
    });

    // Rota para pedidos com notas
    Route::group(['prefix' => 'pedidos-notas'], function () {
        Route::get('/', [PedidosNotasController::class, 'index'])
            ->name('pedidos-notas.index');

        // Exportações
        Route::get('/export-csv', [PedidosNotasController::class, 'exportCsv'])->name('pedidos-notas.exportCsv');
        Route::get('/export-pdf', [PedidosNotasController::class, 'exportPdf'])->name('pedidos-notas.exportPdf');
        Route::get('/export-xls', [PedidosNotasController::class, 'exportXls'])->name('pedidos-notas.exportXls');
        Route::get('/export-xml', [PedidosNotasController::class, 'exportXml'])->name('pedidos-notas.exportXml');




        Route::delete('excluir/{id}', [PedidosNotasController::class, 'excluirNota'])
            ->name('pedidos-notas.excluir-nota');

        Route::get('/chaves/search', [PedidosNotasController::class, 'searchChaves'])->name('chaves.search');

        Route::get('/{pedido}', [PedidosNotasController::class, 'show'])
            ->name('pedidos-notas.show');
    });

    // Rotas para lançamento de notas
    Route::group(['prefix' => 'lancamento-notas'], function () {
        Route::get('/', [LancamentoNotasController::class, 'index'])
            ->name('lancamento-notas.index');
        Route::post('/confirmar', [LancamentoNotasController::class, 'confirmarSelecao'])
            ->name('lancamento-notas.confirmar');

        // Exportação
        Route::get('/export-pdf', [LancamentoNotasController::class, 'exportPdf'])
            ->name('lancamento-notas.exportPdf');
        Route::get('/export-csv', [LancamentoNotasController::class, 'exportCsv'])->name('lancamento-notas.exportCsv');
        Route::get('/export-xls', [LancamentoNotasController::class, 'exportXls'])->name('lancamento-notas.exportXls');
        Route::get('/export-xml', [LancamentoNotasController::class, 'exportXml'])->name('lancamento-notas.exportXml');
        Route::get('/visualizar-modal/{id}', [LancamentoNotasController::class, 'visualizarModalReforma'])
            ->name('lancamento-notas.visualizarModalReforma');
        Route::get('/visualizar-modal-lancamento/{id}', [LancamentoNotasController::class, 'visualizarModalReformaLancamento'])
            ->name('lancamento-notas.visualizarModalReformaLancamento');

        Route::post('/lancarnota/{id}', [LancamentoNotasController::class, 'lancarNotaFiscalReforma'])->name('lancamento-notas.lancarnota');

        Route::get('/listacompra', [LancamentoNotasController::class, 'listaCompra'])->name('lancamento-notas.listacompra');
    });

    // Notas lançadas
    Route::group(['prefix' => 'notas-lancadas'], function () {
        Route::get('/', [NotasLancadasController::class, 'index'])
            ->name('notas-lancadas.index');
        Route::get('/{nota}/{tipo}', [NotasLancadasController::class, 'show'])
            ->name('notas-lancadas.show');

        // Exportação
        // Exportações
        Route::get('/export-csv', [NotasLancadasController::class, 'exportCsv'])->name('notas-lancadas.exportCsv');
        Route::get('/export-pdf', [NotasLancadasController::class, 'exportPdf'])->name('notas-lancadas.exportPdf');
        Route::get('/export-xls', [NotasLancadasController::class, 'exportXls'])->name('notas-lancadas.exportXls');
        Route::get('/export-xml', [NotasLancadasController::class, 'exportXml'])->name('notas-lancadas.exportXml');
        Route::get('/listacompra', [NotasLancadasController::class, 'listaCompra'])->name('notas-lancadas.listacompra');
    });

    Route::group(['prefix' => 'fornecedores'], function () {
        Route::get('/', [FornecedorController::class, 'index'])->name('fornecedores.index');
        Route::get('criar', [FornecedorController::class, 'create'])->name('fornecedores.create');
        Route::get('search', [FornecedorController::class, 'search'])->name('fornecedores.search');
        Route::get('fornecedores/single/{id}', [FornecedorController::class, 'single'])->name('fornecedores.single');

        Route::post('/', [FornecedorController::class, 'store'])->name('fornecedores.store');
        Route::post('/getCNPJ', [FornecedorController::class, 'getFornecedores'])->name('fornecedores.getCNPJ');
        Route::get('{fornecedores}/editar', [FornecedorController::class, 'edit'])->name('fornecedores.edit');
        Route::put('{fornecedores}', [FornecedorController::class, 'update'])->name('fornecedores.update');

        Route::delete('{fornecedores}', [FornecedorController::class, 'destroy'])->name('fornecedores.destroy');
    });

    // Rotas para contratos de fornecedores
    Route::group(['prefix' => 'contratos'], function () {
        // Listagem principal
        Route::get('/', [ContratoFornecedorController::class, 'index'])
            ->name('contratos.index');

        // Busca e detalhes (devem vir antes das rotas com parâmetros)
        Route::get('create', [ContratoFornecedorController::class, 'create'])
            ->name('contratos.create');
        Route::get('search', [ContratoFornecedorController::class, 'search'])
            ->name('contratos.search');
        Route::get('single/{id}', [ContratoFornecedorController::class, 'single'])
            ->name('contratos.single');

        // CRUD básico
        Route::post('/', [ContratoFornecedorController::class, 'store'])
            ->name('contratos.store');

        Route::get('{contratos}/editar', [ContratoFornecedorController::class, 'edit'])
            ->name('contratos.edit');
        Route::put('{contratos}', [ContratoFornecedorController::class, 'update'])
            ->name('contratos.update');
        Route::delete('{contratos}', [ContratoFornecedorController::class, 'destroy'])
            ->name('contratos.destroy');
    });

    Route::group(['prefix' => 'cotacoes', 'as' => 'cotacoes.'], function () {
        // === LISTAGEM E NAVEGAÇÃO ===
        Route::get('/', [CotacoesController::class, 'index'])->name('index');
        Route::get('criar', [CotacoesController::class, 'create'])->name('create');
        Route::get('search', [CotacoesController::class, 'search'])->name('search');

        // === UNIFICAÇÃO DE COTAÇÕES ===
        Route::get('unificar', [CotacoesController::class, 'exibirFormularioUnificacao'])->name('unificar.form');
        Route::post('unificar', [CotacoesController::class, 'unificarCotacoes'])->name('unificar');
        Route::post('{id}/desmembrar', [CotacoesController::class, 'desmembrarCotacao'])->name('desmembrar');

        // === UNIFICAÇÃO DE ITENS ===
        Route::get('unificar-itens', [CotacoesController::class, 'exibirFormularioUnificacaoItens'])->name('unificar-itens.form');
        Route::post('unificar-itens', [CotacoesController::class, 'unificarItens'])->name('unificar-itens');

        // === BUSCA E DETALHES / AJAX ===
        Route::get('single/{id}', [CotacoesController::class, 'getCotacao'])->name('single');
        Route::get('buscar-item/{id?}', [CotacoesController::class, 'buscarItem'])->name('buscaritem');

        // === CRUD BÁSICO ===
        Route::post('/', [CotacoesController::class, 'store'])->name('store');
        // manter rotas estáticas antes das rotas com parâmetro para evitar conflitos
        Route::get('{cotacao}/editar', [CotacoesController::class, 'edit'])->name('edit');
        Route::put('{cotacao}', [CotacoesController::class, 'update'])->name('update');
        Route::delete('{cotacao}', [CotacoesController::class, 'destroy'])->name('destroy');
        // rota de exibição deve vir por último entre as rotas com parâmetro
        Route::get('{cotacao}', [CotacoesController::class, 'show'])->name('show');

        // === AÇÕES DE NEGÓCIO ===
        Route::post('incluir', [CotacoesController::class, 'incluirCotacao'])->name('incluircotacao');
        Route::post('salvaritenscotacao', [CotacoesController::class, 'salvarItensCotacao'])
            ->name('salvaritenscotacao');

        // Gestão de status
        Route::post('mudarstatus', [CotacoesController::class, 'mudarStatus'])
            ->name('mudarstatus');
        Route::post('mudarstatussolicitante', [CotacoesController::class, 'mudarStatusSolicitante'])
            ->name('mudarstatussolicitante');

        // Assumir solicitação
        Route::post('assumir', [CotacoesController::class, 'assumirSolicitacao'])->name('assumir');
        Route::get('getSolicitacao/{id}', [CotacoesController::class, 'getSolicitacao'])->name('getSolicitacao');
        Route::post('trocarComprador/{id}', [CotacoesController::class, 'trocarComprador'])->name('trocarComprador');
        Route::post('ondevolver', [CotacoesController::class, 'onDevolver'])->name('ondevolver');
        Route::post('adiar/{id}', [CotacoesController::class, 'adiar'])->name('adiar');
        Route::post('remover-adiamento/{id}', [CotacoesController::class, 'removerAdiamento'])->name('removerAdiamento');

        // === AÇÕES DE SAÍDA ===
        Route::post('imprimir', [CotacoesController::class, 'imprimirCotacao'])->name('imprimir');
        Route::post('enviar', [CotacoesController::class, 'onEnviarCotacoes'])->name('enviar');
    });

    // API para selects e autocomplete
    Route::prefix('api')->name('api.')->group(function () {
        // Pedidos
        Route::get('/pedidos/search', [PedidoCompraController::class, 'search'])
            ->name('pedidos.search');
        Route::get('/pedidos/single/{id}', [PedidoCompraController::class, 'getById'])
            ->name('pedidos.single');
        Route::get('/pedidos/itens/{pedido}', [PedidoCompraController::class, 'getItens'])
            ->name('pedidos.itens');

        // Fornecedores
        Route::get('/fornecedores/search', [FornecedorController::class, 'search'])
            ->name('fornecedores.search');
        Route::get('/fornecedores/single/{id}', [FornecedorController::class, 'getById'])
            ->name('fornecedores.single');

        // Solicitações
        Route::get('/solicitacoes/search', [SolicitacaoCompraController::class, 'search'])
            ->name('solicitacoes.search');
        Route::get('/solicitacoes/single/{id}', [SolicitacaoCompraController::class, 'getById'])
            ->name('solicitacoes.single');

        // Orçamentos
        Route::get('/orcamentos/search', [OrcamentoController::class, 'search'])
            ->name('orcamentos.search');
        Route::get('/orcamentos/single/{id}', [OrcamentoController::class, 'getById'])
            ->name('orcamentos.single');
        Route::get('/orcamentos/por-pedido/{pedido_id}', [OrcamentoController::class, 'getByPedido'])
            ->name('orcamentos.por-pedido');

        // Notas Fiscais
        Route::get('/notasfiscais/search', [NotaFiscalController::class, 'search'])->name('notasfiscais.search');
        Route::get('/notasfiscais/single/{id}', [NotaFiscalController::class, 'getById'])->name('notasfiscais.single');


        Route::get('/chaves/search', [PedidosNotasController::class, 'searchChaves'])->name('chaves.search');
        Route::get('/chaves/{id}', [PedidosNotasController::class, 'getChaveById'])->name('chaves.get');
    });
});

// Rotas para Itens de Solicitação de Compra
Route::group(['prefix' => 'itens-solicitacao'], function () {
    // CRUD básico para itens
    Route::get('/{solicitacao_id}', [ItemSolicitacaoCompraController::class, 'index'])
        ->name('itens-solicitacao.index');

    Route::post('/{solicitacao_id}', [ItemSolicitacaoCompraController::class, 'store'])
        ->name('itens-solicitacao.store');

    Route::put('/{id}', [ItemSolicitacaoCompraController::class, 'update'])
        ->name('itens-solicitacao.update');

    Route::delete('/{id}', [ItemSolicitacaoCompraController::class, 'destroy'])
        ->name('itens-solicitacao.destroy');
});

// API para Produtos e Serviços (usado pela busca de itens)
Route::prefix('api')->group(function () {
    // Rotas para busca de produtos/serviços
    Route::get('/produtos/search', [ItemSolicitacaoCompraController::class, 'searchProdutos'])
        ->name('api.produtos.search');

    Route::get('/servicos/search', [ItemSolicitacaoCompraController::class, 'searchServicos'])
        ->name('api.servicos.search');

    // Busca genérica (produtos e serviços)
    Route::get('/produtos-servicos/search', [ItemSolicitacaoCompraController::class, 'search'])
        ->name('api.produtos-servicos.search');

    // Busca de solicitações
    Route::get('/solicitacoes/search', [SolicitacaoCompraController::class, 'buscar'])
        ->name('api.solicitacoes.search');

    Route::get('/notasfiscais/search', [NotaFiscalController::class, 'search'])->name('notasfiscais.search');
    Route::get('/notasfiscais/single/{id}', [NotaFiscalController::class, 'getById'])->name('notasfiscais.single');

    Route::get('/users/search', [UserController::class, 'search'])->name('api.users.search');
    Route::get('/users/single/{id}', [UserController::class, 'getById'])->name('api.users.single');

    // Rotas para pré-cadastro de produtos
    Route::get('/estoques/list', function () {
        return \App\Models\Estoque::select('id_estoque as id', 'descricao_estoque as nome')->get();
    })->name('api.estoques.list');

    Route::get('/unidades/list', function () {
        return \App\Models\UnidadeProduto::select('id_unidade_produto as id', 'descricao_unidade as descricao')->get();
    })->name('api.unidades.list');

    Route::get('/grupos-servico/list', function () {
        return \App\Models\GrupoServico::select('id_grupo as id', 'descricao_grupo as descricao')->get();
    })->name('api.grupos-servico.list');
});

// Rota para pré-cadastro de produtos
Route::post('/produtos/pre-cadastro', [ItemSolicitacaoCompraController::class, 'preCadastroProduto'])
    ->name('produtos.pre-cadastro');

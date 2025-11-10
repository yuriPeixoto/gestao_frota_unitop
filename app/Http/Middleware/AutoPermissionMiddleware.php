<?php

namespace App\Http\Middleware;

use App\Helpers\PermissionHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware automático de verificação de permissões baseado em convenções
 *
 * Convenções:
 * - admin/{module}/* -> requer permissão ver_{module}
 * - admin/{module}/create -> requer permissão criar_{module}
 * - admin/{module}/edit -> requer permissão editar_{module}
 * - admin/{module}/destroy -> requer permissão excluir_{module}
 */
class AutoPermissionMiddleware
{
    /**
     * Mapeamento de métodos HTTP e ações para permissões
     */
    private const ACTION_MAPPING = [
        'GET' => [
            'index' => 'ver',
            'show' => 'ver',
            'create' => 'criar',
            'create_preventiva' => 'criar',
            'create_diagnostico' => 'criar',
            'edit' => 'editar',
            'edit_preventiva' => 'editar',
            'edit_diagnostico' => 'editar',
            'report' => 'relatorio',
            'search' => 'ver',
            'searchAts' => 'ver',
            'searchTruckPag' => 'ver',
            'searchMotoristas' => 'ver',
            'searchFornecedores' => 'ver',
            'searchVeiculos' => 'ver',
            'searchDepartamentos' => 'ver',
            'searchFiliais' => 'ver',
            'exportPdf' => 'ver', // Exportação de PDF usa permissão de visualização
            'exportCsv' => 'ver', // Exportação de CSV usa permissão de visualização
            'exportXls' => 'ver', // Exportação de XLS usa permissão de visualização
            'exportXml' => 'ver', // Exportação de XML usa permissão de visualização
            'export' => 'ver', // Exportação genérica usa permissão de visualização
            'onImprimir' => 'ver', // Imprimir usa permissão de visualização
            'onImprimirServPec' => 'ver', // Imprimir serviços e peças usa permissão de visualização
            'getVeiculos' => 'ver', // Buscar veículos usa permissão de visualização
            'retornarPosto' => 'ver', // Buscar posto usa permissão de visualização
            'ajaxGetVeiculoDados' => 'ver', // Buscar dados do veículo (AJAX) usa permissão de visualização
            'getBombaValorUnitario' => 'ver', // Buscar valor unitário da bomba usa permissão de visualização
            'getBombaData' => 'ver', // Buscar dados da bomba usa permissão de visualização
            'getBombasPorCombustivel' => 'ver', // Buscar bombas por combustível usa permissão de visualização
            'getVeiculoInfo' => 'ver', // Buscar informações do veículo usa permissão de visualização
            'getDepartamento' => 'ver', // Buscar departamento usa permissão de visualização
            'getFilial' => 'ver', // Buscar filial usa permissão de visualização
            'getFiliais' => 'ver', // Buscar filiais usa permissão de visualização
            'getDepartamentos' => 'ver', // Buscar departamentos usa permissão de visualização
            'getFornecedor' => 'ver', // Buscar fornecedor usa permissão de visualização
            'getMotorista' => 'ver', // Buscar motorista usa permissão de visualização
            'getById' => 'ver', // Buscar por ID usa permissão de visualização
            'getVehicleData' => 'ver', // Buscar dados do veículo usa permissão de visualização
            'getKmInfo' => 'ver', // Buscar informações de KM usa permissão de visualização
            'getDadosRenavam' => 'ver', // Buscar dados do RENAVAM usa permissão de visualização
            'getPneuData' => 'ver', // Buscar dados do pneu usa permissão de visualização
            'getValorBomba' => 'ver', // Buscar valor da bomba usa permissão de visualização
            'getPedido' => 'ver', // Buscar pedido usa permissão de visualização
            'getOrdemServicoData' => 'ver', // Buscar dados da ordem de serviço usa permissão de visualização
            'getTiposEquipamento' => 'ver', // Buscar tipos de equipamento usa permissão de visualização
            'getCategoriaVeiculo' => 'ver', // Buscar categoria de veículo usa permissão de visualização
            'getTipoCombustivel' => 'ver', // Buscar tipo de combustível usa permissão de visualização
            'getVeiculosFrequentes' => 'ver', // Buscar veículos frequentes usa permissão de visualização
            'getManutencao' => 'ver', // Buscar manutenção usa permissão de visualização
            'getServicosSearch' => 'ver', // Buscar serviços (search) usa permissão de visualização
            'getProdutosSearch' => 'ver', // Buscar produtos (search) usa permissão de visualização
            'getEstoqueByFilial' => 'ver', // Buscar estoque por filial usa permissão de visualização
            'getProdutoByEstoque' => 'ver', // Buscar produto por estoque usa permissão de visualização
            'getEstoqueByProduto' => 'ver', // Buscar estoque por produto usa permissão de visualização
            'getProdutosPorOrdemServico' => 'ver', // Buscar produtos por ordem de serviço usa permissão de visualização
            'getProdutosPorSolicitacao' => 'ver', // Buscar produtos por solicitação usa permissão de visualização
            'getProdutosPorRequisicao' => 'ver', // Buscar produtos por requisição usa permissão de visualização
            'getDadosVeiculo' => 'ver', // Buscar dados do veículo usa permissão de visualização
            'carregarUnidadeProduto' => 'ver', // Carregar unidade de produto usa permissão de visualização
            'carregarKm' => 'ver', // Carregar KM usa permissão de visualização
            'abrirModal' => 'ver', // Abrir modal usa permissão de visualização
        ],
        'POST' => [
            'store' => 'criar',
            'store_preventiva' => 'criar',
            'store_diagnostico' => 'criar',
            'baixar' => 'baixar',
            'baixarLote' => 'baixar',
            'baixarItens' => 'baixar',
            'baixarItensUnificado' => 'baixar',
            'baixarItensMateriais' => 'baixar',
            'baixarItensPecas' => 'baixar',
            'baixarPneus' => 'baixar',
            'aprovar' => 'aprovar',
            'reprovar' => 'reprovar',
            'finalizar' => 'finalizar',
            'finalizarOs' => 'finalizar',
            'onFinalizar' => 'finalizar',
            'onFinalizarServico' => 'finalizar',
            'cancelar' => 'cancelar',
            'onCancelarOS' => 'cancelar',
            'reabrir' => 'reabrir',
            'reabirOS' => 'reabrir',
            'validar' => 'validar',
            'validarRequisicaoTerceiro' => 'validar',
            'transferir' => 'transferir',
            'ajustar' => 'ajustar',
            'inserirServicosePecas' => 'editar',
            'onDeletarServico' => 'excluir',
            'onDeletarPecas' => 'excluir',
            'onSolicitarServicos' => 'editar',
            'onActionSolicitarPecas' => 'editar',
            'onActionEncerrar' => 'editar',
            'marcarMarcacao' => 'editar',
            'marcarTodosMarcacoes' => 'editar',
            'search' => 'ver',
            'searchAts' => 'ver',
            'searchTruckPag' => 'ver',
            'searchMotoristas' => 'ver',
            'searchFornecedores' => 'ver',
            'searchVeiculos' => 'ver',
            'searchDepartamentos' => 'ver',
            'searchFiliais' => 'ver',
            'gerarPdf' => 'ver', // Geração de PDF usa permissão de visualização
            'gerarExcel' => 'ver', // Geração de Excel usa permissão de visualização
            'onGeneratePdf' => 'ver', // Geração de PDF de relatório usa permissão de visualização
            'onGenerateXls' => 'ver', // Geração de XLS de relatório usa permissão de visualização
            'onGenerateTotalizador' => 'ver', // Geração de PDF totalizado usa permissão de visualização
            'gerarPdfTotalizado' => 'ver', // Geração de PDF totalizado (método alternativo) usa permissão de visualização
            'onImprimir' => 'ver', // Impressão de relatórios usa permissão de visualização
            'onImprimirExcel' => 'ver', // Impressão de relatórios Excel usa permissão de visualização
            'pedidoJaBaixado' => 'ver', // Verificação de pedido usa permissão de visualização
            'getpedido' => 'ver', // Buscar pedido usa permissão de visualização
            'getPedido' => 'ver', // Buscar pedido usa permissão de visualização
            'getTankData' => 'ver', // Buscar dados do tanque usa permissão de visualização
            'getFornecedores' => 'ver', // Buscar fornecedores usa permissão de visualização
            'getCombustivelData' => 'ver', // Buscar dados do combustível usa permissão de visualização
            'getCombustivelBomba' => 'ver', // Buscar combustível da bomba usa permissão de visualização
            'getBombaData' => 'ver', // Buscar dados da bomba usa permissão de visualização
            'ajaxGetVeiculoDados' => 'ver', // Buscar dados do veículo usa permissão de visualização
            'getVehicleData' => 'ver', // Buscar dados do veículo usa permissão de visualização
            'getInfoVeiculo' => 'ver', // Buscar informações do veículo usa permissão de visualização
            'getTelefoneMotorista' => 'ver', // Buscar telefone do motorista usa permissão de visualização
            'getKmInfo' => 'ver', // Buscar informações de KM usa permissão de visualização
            'getDadosRenavam' => 'ver', // Buscar dados do RENAVAM usa permissão de visualização
            'getPneuData' => 'ver', // Buscar dados do pneu usa permissão de visualização
            'getValorBomba' => 'ver', // Buscar valor da bomba usa permissão de visualização
            'getOrdemServicoData' => 'ver', // Buscar dados da ordem de serviço usa permissão de visualização
            'getDadosVeiculo' => 'ver', // Buscar dados do veículo usa permissão de visualização
            'carregarUnidadeProduto' => 'ver', // Carregar unidade de produto usa permissão de visualização
            'carregarKm' => 'ver', // Carregar KM usa permissão de visualização
            'validarKMAtual' => 'ver', // Validar KM atual usa permissão de visualização
            'getServicos' => 'ver', // Buscar serviços usa permissão de visualização
            'getProdutos' => 'ver', // Buscar produtos usa permissão de visualização
            'valorServicoxfornecedor' => 'ver', // Buscar valor de serviço por fornecedor usa permissão de visualização
            'ValorServicoXFornecedor' => 'ver', // Buscar valor de serviço por fornecedor (alternativo) usa permissão de visualização
            'getServicosBorracharia' => 'ver', // Buscar serviços de borracharia usa permissão de visualização
            'getProdutosBorracharia' => 'ver', // Buscar produtos de borracharia usa permissão de visualização
            'onimprimirkm' => 'ver', // Imprimir KM usa permissão de visualização
            'storeTemp' => 'criar', // Upload temporário de documentos usa permissão de criar
            'moveToSinistro' => 'editar', // Mover arquivo para sinistro usa permissão de editar
            'getFile' => 'ver', // Visualizar arquivo usa permissão de ver
        ],
        'PUT' => [
            'update' => 'editar',
            'update_preventiva' => 'editar',
            'update_diagnostico' => 'editar',
        ],
        'PATCH' => [
            'update' => 'editar',
        ],
        'DELETE' => [
            'destroy' => 'excluir',
            'deleteFile' => 'excluir', // Excluir arquivo de documento usa permissão de excluir
        ],
    ];

    /**
     * Rotas que devem ser ignoradas pelo middleware
     */
    private const EXCLUDED_ROUTES = [
        'admin.dashboard',
        'admin.profile.*',
        'admin.logout',
        'admin.api.*',
        'admin.search.*',
    ];

    /**
     * Controllers que têm lógica de permissão própria e devem ser ignorados
     */
    private const EXCLUDED_CONTROLLERS = [
        'DashboardController',
        'ProfileController',
        'AuthController',
        'ApiController',
        'SearchController',
        'PermissionController',
        'PermissionDiscoveryController',
    ];

    /**
     * Mapeamento de módulos para suas permissões reais
     * Para casos onde a URL não corresponde ao nome da permissão
     */
    private const MODULE_PERMISSION_MAPPING = [
        // ===== COMPRAS =====
        'solicitacoes' => 'solicitacao_compra',
        'solicitacoes-materiais' => 'requisicao_material',
        'pedidos' => 'pedido_compra',
        'orcamentos' => 'orcamento',
        'fornecedores' => 'fornecedor',
        'contratos' => 'contrato',
        'contratosmodelo' => 'contrato_modelo',
        'notas-fiscais' => 'nota_fiscal',
        'relatorios' => 'relatorios_compras',
        'itens-solicitacao' => 'item_solicitacao_compra',
        'itensparacompra' => 'itens_para_compra',
        'listapedidocompra' => 'lista_pedido_compras',

        // ===== ABASTECIMENTO =====
        'abastecimentomanual' => 'abastecimento_manual',
        'abastecimentomanualrelatorio' => 'abastecimento_manual_relatorio',
        'abastecimentoplacatotalizado' => 'abastecimento_placa_totalizado',
        'abastecimentoporbomposto' => 'abastecimento',
        'abastecimentoequipamento' => 'abastecimento_equipamento',
        'abastecimentosatstruckpagmanual' => 'abastecimentoatstruckpagmanual',
        'abastecimentosfaturamento' => 'abastecimentos_faturamento',
        'abastecimentostruckpag' => 'abastecimentotruckpag',
        'extratoabastecimentoterceiros' => 'extrato_abastecimento_terceiros',
        'fechamentoabastecimentomedia' => 'fechamento_abastecimento_media',
        'faturamentoabastecimento' => 'faturamento_abastecimento',
        'ajustekm' => 'ajuste_km_abastecimento',
        'consultarlancamentoskmmanual' => 'ajuste_km_abastecimento',
        'recebimentocombustiveis' => 'recebimentocombustivel',
        'estoque-combustivel' => 'estoquecombustivel',
        'bombas' => 'bomba',
        'afericaobombas' => 'afericao_bomba',
        'encerrantes' => 'encerrante',
        'listagemencerrantes' => 'encerrante',
        'valorcombustiveis' => 'valorcombustivelterceiro',
        'tanques' => 'tanque',
        'reprocessar' => 'reprocessar_integracao',
        'integracao486ssw' => 'reprocessar_integracao',
        'integracao486Ssw' => 'reprocessar_integracao',
        'listagemkmhistorico' => 'ajuste_km_abastecimento',

        // ===== VEÍCULOS =====
        'veiculos' => 'veiculo',
        'modeloveiculos' => 'modelo_veiculo',
        'tipoveiculos' => 'tipo_veiculo',
        'subcategoriaveiculos' => 'sub_categoria_veiculo',
        'atrelamentoveiculos' => 'atrelamento_veiculo',
        'cadastroveiculovencimentario' => 'cadastro_veiculo_vencimentario',
        'autorizacoesesptransitos' => 'autorizacoes_esp_transito',

        // ===== LICENCIAMENTO / IPVA / SEGUROS =====
        'licenciamentoveiculos' => 'licenciamento_veiculo',
        'licenciamentos' => 'licenciamentos',
        'ipvaveiculos' => 'ipva_veiculo',
        'lancipvalicenciamentoseguros' => 'lanc_ipva_licenciamento_seguro',
        'seguroobrigatorio' => 'seguro_obrigatorio',
        'controlelicencavencimentario' => 'controle_licenca_vencimentario',
        'listagemipva' => 'listagem_ipva',
        'listagemantt' => 'listagem_antt',

        // ===== MULTAS =====
        'multas' => 'multa',
        'listagemmultas' => 'multa',
        'classificacaomultas' => 'classificacao_multa',
        'dashboard-multas' => 'dashboard_multas',

        // ===== CRONOTACÓGRAFO E TESTES =====
        'cronotacografos' => 'cronotacografo',
        'cronotacografovencimentario' => 'cronotacografo_vencimentario',
        'testefrios' => 'teste_frio',
        'testefumacas' => 'teste_fumaca',

        // ===== SINISTROS =====
        'sinistros' => 'sinistro',
        'documentos' => 'sinistro', // Documentos de sinistros usam permissão de sinistro
        'relatoriosinistro' => 'sinistro', // Relatórios de sinistros usam permissão de sinistro
        'relatoriogeralsinistro' => 'sinistro', // Relatórios gerais de sinistros usam permissão de sinistro
        'relatoriosinistroll' => 'sinistro', // Relatórios sinistroll usam permissão de sinistro

        // ===== PNEUS =====
        'pneus' => 'pneu',
        'pneusdeposito' => 'pneus_deposito',
        'movimentacaopneus' => 'movimentacao_pneus',
        'manutencaopneus' => 'manutencao_pneus',
        'manutencaopneusentrada' => 'manutencao_pneus_entrada',
        'calibragempneus' => 'calibragem_pneus',
        'contagempneus' => 'contagempneu',
        'descartepneus' => 'descarte_pneu',
        'descartetipopneu' => 'descarte_tipo',
        'transferenciapneus' => 'transferencia_pneus',
        'envioerecebimentopneus' => 'envioe_recebimento',
        'requisicaopneusvendas' => 'requisicao_pneus_vendas',
        'requisicaopneusvendassaida' => 'requisicao_pneus_vendas_saida',
        'tipoborrachapneus' => 'tipo_borracha_pneu',
        'tipodesenhopneus' => 'tipo_desenho_pneu',
        'tipodimensaopneus' => 'tipo_dimensao_pneu',
        'tiporeformapneus' => 'tipo_reforma_pneu',

        // ===== MANUTENÇÃO =====
        'manutencao' => 'manutencao',
        'ordemservicos' => 'ordem_servico',
        'ordemservicos_preventiva' => 'ordem_servico',
        'ordemservicoauxiliares' => 'ordem_servicos_auxiliar',
        'ordemservicoservicos' => 'ordem_servico_servicos',
        'ordemservicodiagnostico' => 'ordemservicodiagnostico',
        'statusordemservico' => 'status_ordem_servico',
        'controlemanutancaofrota' => 'controle_manutancao_forta',
        'manutencaocategoria' => 'categoriaservico',
        'manutencaoservico' => 'manutencao_servico',
        'manutencaoservicos' => 'servicos',
        'manutencaoservicosmecanico' => 'servicosmecanico',
        'manutencaopremio' => 'manutencaopremio',
        'manutencaopreordemservicofinalizada' => 'pre_ordem_listagem_finalizadas',
        'manutencaopreordemserviconova' => 'pre_ordem_listagem_nova',
        'manutencaokmveiculocomodato' => 'manutencao_km_veiculo_comodato',
        'servicofornecedor' => 'servico_xfornecedor',
        'servicos' => 'servico',
        'gruposervicos' => 'grupo_servico',
        'subgruposervicos' => 'subgrupo_servico',
        'monitoramentoDasManutencoes' => 'monitoramento_manutencoes',
        'listagemoslacamentoservico' => 'manutencao_listagem_os_lacamento_nfservico',
        'listagemoslacamentoservicorateio' => 'manutencao_notas_ficais_rateio',

        // ===== ESTOQUE / IMOBILIZADO =====
        'ajuste-estoque' => 'ajuste_estoque',
        'cadastro-estoque' => 'estoque',
        'cadastroprodutosestoque' => 'cadastro_produtos_estoque',
        'saidaprodutosestoque' => 'saida_produtos_estoque',
        'devolucaosaidaestoque' => 'devolucao_saida_estoque',
        'transferencia-direta-estoque-list' => 'transferencia_direta_estoque',
        'transferenciaDiretoEstoque' => 'transferenciadiretaestoque',
        'transferencia-entre-estoque' => 'transferencia_entre_estoque',
        'consultaprodutostransferencia' => 'consulta_produtos_transferencia',
        'produtosimobilizados' => 'produtos_imobilizados',
        'cadastroimobilizado' => 'cadastro_imobilizado',
        'estoqueimobilizado' => 'estoque_imobilizado',
        'ordemservicoimobilizado' => 'ordem_servico_imobilizado',
        'transfimobilizadoveiculo' => 'transf_imobilizado_veiculo',
        'devolucaoimobilizadoveiculo' => 'devolucao_imobilizado_veiculo',
        'descarteimobilizado' => 'descarte_imobilizado',
        'recebimentoimobilizado' => 'recebimento_imobilizado',
        'solicitacaoimobilizado' => 'solicitacao_imobilizado',
        'aprovacaoimobilizadogestor' => 'aprovacao_imobilizado_gestor',
        'aprovacaorelacaoimobilizado' => 'aprovacao_relacao_imobilizado',
        'saidarelacaoimobilizado' => 'saida_relacao_imobilizado',
        'requisicaoimobilizados' => 'requisicao_imobilizados',
        'statuscadastroimobilizado' => 'status_cadastro_imobilizado',
        'tipoimobilizados' => 'tipo_imobilizado',
        'tipomanutencaoimobilizados' => 'tipo_manutencao_imobilizado',
        'requisicaoMaterial' => 'requisicao_material',
        'notafiscalentrada' => 'nota_fiscal_entrada',
        'pecas' => 'pecasservicos',
        'grupos-pecas' => 'gruporesolvedor',

        // ===== PESSOAL =====
        'pessoas' => 'pessoal',
        'condutores' => 'condutores_vencimantario',
        'cargos' => 'cargousuario',
        'tipopessoal' => 'tipo_pessoal',

        // ===== CERTIFICADOS E CHECKLIST =====
        'checklist' => 'checklist',
        'checklistrecebimentofornecedor' => 'check_list_recebimento_fornecedor',
        'tipocertificados' => 'tipo_certificado',

        // ===== CONFIGURAÇÕES =====
        'departamentos' => 'departamento',
        'departamentotransferencia' => 'departamento_transferencia',
        'filiais' => 'filial',
        'empresas' => 'empresa',
        'usuarios' => 'user',
        'permissoes' => 'permission',
        'telefones' => 'telefone',
        'telefonetransferencia' => 'telefone_transferencia',
        'municipios' => 'municipio',
        'trocafilial' => 'trocafilial',
        'log-atividades' => 'activitylog',
        'logs' => 'log',
        'listagemnotificacoes' => 'listagem_notificacoes',

        // ===== TIPOS GENÉRICOS =====
        'tipoacertoestoque' => 'tipo_acerto_estoque',
        'tipocategorias' => 'tipo_categoria',
        'tipocombustiveis' => 'tipo_combustivel',
        'tipoequipamentos' => 'tipo_equipamento',
        'tipofornecedores' => 'tipo_fornecedor',
        'tipomanutencoes' => 'tipo_manutencao',
        'tipomotivosinistros' => 'tipo_motivo_sinistro',
        'tipoocorrencias' => 'tipo_ocorrencia',
        'tipooperacao' => 'tipooperacao',
        'tipoorgaosinistros' => 'tipo_orgao_sinistro',
        'tiposolicitacao' => 'tipo_solicitacao',
        'unidadeprodutos' => 'unidade_produto',
        'metatipoequipamentos' => 'meta_tipo_equipamento',

        // ===== RELATÓRIOS =====
        'historicomanutencaoveiculo' => 'historico_mant_veiculo_rel',
        'relatorioveiculos' => 'relatorio_veiculos',
        'relatoriomultas' => 'relatorio_multas',
        'relatoriocertificadoveiculo' => 'relatorio_certificado_veiculo',
        'relatoriocompraevendaveiculo' => 'relatorio_compra_venda_veiculo',
        'relatorioconsultarveiculo' => 'relatorio_consultar_veiculo',
        'relatoriotransferenciaveiculo' => 'relatorio_transferencia_veiculo',
        'relatorioduracaodasmanutencoes' => 'relatorio_duracao_manutenções_os',
        'relatoriofornecedorsemnf' => 'relatorio_fornecedor_sem_nf',
        'relatoriorecebimentocombustivel' => 'relatorio_recebimento_combustivel',
        'relatorioabastecimentototais' => 'relatorio_abastecimento_totais',
        'relatorioentradaprodutos' => 'relatorio_entrada_produtos',
        'relatoriocustospordepartamento' => 'relatorio_custos_variaveis_por_departamento',
        'relatoriofechamentomensalcontroladoria' => 'relatorio_fechamento_mensal_controladoria',
        'relatorioextratocontafornecedor' => 'relatorio_extrato_conta_fornecedor',
        'relatorioultimamovimentacaodespesas' => 'relatorio_ultima_movimentacao_despesas',
        'relatorioinventariopneus' => 'relatorio_inventario_pneus',
        'relatorioinventariopneusaplicados' => 'relatorio_inventario_pneus_aplicados',
        'relatorionfsmanutencaorealizadas' => 'relatorio_nfs_manutencao_realizadas',
        'relatoriocontacorrentefornecedor' => 'relatorio_cont_corrente_fornecedor',
        'relatorioextratoipva' => 'relatorio_extrato_ipva',
        'relatoriohistoricokm' => 'relatorio_historico_km',
        'relatorioipvalicenciamento' => 'relatorio_ipva_licenciamento_veiculo',
        'relatorioatendimentocompra' => 'relatorio_atendimento_compra',
        'relatoriobaixaestoque' => 'relatorio_baixa_estoque',
        'relatoriocalibracao' => 'relatorio_calibracao',
        'relatoriochecklist' => 'relatorio_checklist',
        'relatoriochecklistfornecedor' => 'relatorio_check_list_fornecedor',
        'relatoriocontrolecompras' => 'relatorio_controle_compras',
        'relatoriocontroleemovimentacaodeestoquedospneus' => 'relatorio_controlee_movimentacao_estoque_dos_pneus',
        'relatoriodataentregapedidos' => 'relatorio_data_entrega',
        'relatoriohistoricotransferencia' => 'relatorio_historico_transferencia',
        'relatoriodehistoricomovimentacaopneus' => 'relatorio_historico_movimentacao_pneu',
        'relatoriolistagempneusdescartados' => 'relatorio_listagem_pneus_descartados',
        'relatoriolistagempneusmanutencao' => 'relatorio_listagem_pneus_manutencao',
        'relatoriomanutencaodetalhada' => 'relatorio_manutencao_detalhadas',
        'relatoriomanutencaovencidas' => 'relatorio_manutencao_vencidas',
        'relatorioordemservicostatus' => 'relatorio_ordem_servico_status',
        'relatoriopecasutilizadasos' => 'relatorio_peças_utilizadas_os',
        'relatoriopneusaplicado' => 'relatorio_pneus_aplicado',
        'relatoriopneusestoque' => 'relatorio_pneus_em_estoque',
        'relatoriopneusnaoaplicado' => 'relatorio_pneus_nao_aplicado',
        'relatoriopneusstatus' => 'relatorio_pneus_por_status',
        'relatorioprodutoimobilizado' => 'relatorio_produto_imobilizado',
        'relatorioprodutoemestoque' => 'relatorio_produtoem_estoque',
        'relatorioprodutoscadastrados' => 'relatorio_produtos_cadastrados',
        'relatorioquantidadepneusporfilial' => 'relatorio_quantidade_pneus_por_filial',
        'relatoriorequisicaopneusfinalizadas' => 'relatorio_requisicao_pneus_finalizadas',
        'relatoriorodiziopneus' => 'relatorio_rodizio_pneus',
        'relatoriosaidadepartamento' => 'relatorio_saida_departamento',
        'relatorioservicosfornecedores' => 'relatorio_servicos_fornecedores',
        'relatorioservicosutilizadasos' => 'relatorio_servicos_utilizadas_os',
        'relatoriosinteticonfos' => 'relatorio_sintetico_nf_os',
        'relatoriosolicitacao' => 'relatorio_solicitacao',
        'relatoriosolicitacaocompra' => 'relatorio_solicitacao_compra',
        'relatoriovendapneus' => 'relatorio_venda_pneus',
        'relatoriogeralchecklist' => 'relatorio_geral_checklist',
        'relatoriohistoricoimobilizado' => 'relatorio_historico_imobilizado',
        'relatorionotafiscalexterna' => 'relatorio_nota_fiscal_externa',
        'relatorioentradanotasfiscais' => 'relatorio_notas_fiscais',
        'relatorioorigembaixas' => 'relatorio_origem_baixas_pecas',
        'relatorioindicecoberturaestoque' => 'relatorio_indice_cobertura_estoque',
        'relatoriofichacontroleestoque' => 'relatorio_ficha_controle_estoque',
        'relatoriomaximoeminimo' => 'relatorio_estoque_max_min',
        'relatorioconferenciarotativo' => 'relatorio_conferencia_rotativo',
        'fornecedorescomissionadosrelatorio' => 'fornecedor_comissionados_rel',
        'relatoriogastosfilialedepartamento' => 'relatorio_gastos_filiale_departamento',
        'relatoriocontratofornecedores' => 'relatorio_contrato_fornecedores',
        'relatorioextratomotoristarh' => 'relatorioextratomotoristarh',
        'relatorioentradadepneumanutencao' => 'relatorio_entrada_manutencao_pneus',
        'relatorioconferenciapremiorvmensal' => 'relatorioconferenciapremiorvmensal',
        'relatoriopremioconferencia' => 'relatoriopremioconferencia',
        'relatoriopremiodeflatores' => 'relatoriopremiodeflatores',
        'relatoriopremiacaomotorista' => 'relatoriopremiacaomotorista',
        'deflatoreseventospormotoristas' => 'deflatoreseventospormotoristas',
        'deflatorescarvalima' => 'deflatorescarvalima',
        'relatoriomotoristanaocalculado' => 'relatoriomotoristanaocalculado',
        'relatorioconferenciatabelao' => 'relatorioconferenciatabelao',
        'premioupocoracao' => 'premioupocoracao',
        'franquiapremiorv' => 'franquiapremiorv',
        'franquiapremiosmensal' => 'franquiapremiosmensal',
        'relatorioveiculosemlogin' => 'relatorioveiculosemlogin',
        'relatoriorelacaodespesasveiculos' => 'manutencao_relacao_despesas_veiculos',
        'relatoriovaloresexcedentes' => 'relatoriovaloresexcedentes',

        // ===== OUTROS =====
        'anexos' => 'anexo',
        'arquivo' => 'arquivo',
        'emissaoqrcode' => 'emissao_qr_code_produto',
        'devolucoes' => 'devolucoes',
        'restricoesbloqueios' => 'restricoes_bloqueios',
        'inconsistencias' => 'inconsistencias',
        'permissaokmmanuals' => 'permissao_km_manual',
        'jornadaferiado' => 'jornadaferiado',
        'ats' => 'inconsistenciaats',
        'truckpag' => 'inconsistenciatruckpag',
    ];

    /**
     * Fallback: mapeamento antigo para permissões que seguem o padrão ver_*
     */
    // Adicione estes mapeamentos no AutoPermissionMiddleware.php
    // Na constante LEGACY_MODULE_MAPPING (linha ~89)

    private const LEGACY_MODULE_MAPPING = [
        'solicitacoes' => 'solicitacaocompras',
        'pedidos' => 'pedidocompras',
        'orcamentos' => 'orcamento',
        'fornecedores' => 'fornecedor',
        'contratos' => 'contrato',
        'notas-fiscais' => 'nota_fiscal',
        'abastecimentosatstruckpagmanual' => 'abastecimentoatstruckpagmanual',

        // ===== CORREÇÕES DE PLURAL/SINGULAR =====
        // 'recebimentocombustiveis' => 'recebimentocombustivel', // Movido para MODULE_PERMISSION_MAPPING
        'afericaobombas' => 'afericao_bomba',
        'valorcombustiveis' => 'valorcombustivelterceiro',
        'tanques' => 'tanque',

        // ===== MÓDULOS DE ABASTECIMENTO =====
        'ajustekm' => 'ajuste_km_abastecimento',
        'reprocessar' => 'reprocessar_integracao',

        // ===== RELATÓRIOS =====
        'abastecimentomanualrelatorio' => 'abastecimento_manual_relatorio',
        'abastecimentoplacatotalizado' => 'abastecimento_placa_totalizado',
        'abastecimentoporbomposto' => 'abastecimento', // relatório de abastecimento por bomba/posto
        'consultarlancamentoskmmanual' => 'ajuste_km_abastecimento',
        'abastecimentoequipamento' => 'abastecimento_equipamento',
        'extratoabastecimentoterceiros' => 'extrato_abastecimento_terceiros',
        'fechamentoabastecimentomedia' => 'fechamento_abastecimento_media',
        'integracao486ssw' => 'reprocessar_integracao', // relatório usa permissão de integração
        'integracao486Ssw' => 'reprocessar_integracao', // relatório usa permissão de integração (case-sensitive)
        'listagemencerrantes' => 'encerrante',
        'listagemkmhistorico' => 'ajuste_km_abastecimento',
        'faturamentoabastecimento' => 'faturamento_abastecimento',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {


        // Ignorar se não estiver autenticado
        if (! Auth::check()) {
            // Log::info(message: 'AutoPermissionMiddleware: Usuário não autenticado, passando adiante');
            return $next($request);
        }

        $user = Auth::user();



        // Superuser sempre tem acesso - CORREÇÃO CRÍTICA: Estava comentado!
        if ($user->is_superuser) {
            return $next($request);
        }

        // Verificar se a rota deve ser ignorada
        if ($this->shouldSkipRoute($request)) {
            // Log::info('AutoPermissionMiddleware: Rota deve ser ignorada', [
            //     'user_id' => $user->id,
            //     'url' => $request->url()
            // ]);
            return $next($request);
        }

        // Extrair informações da rota
        $routeInfo = $this->extractRouteInfo($request);

        if (! $routeInfo) {
            // Log::info('AutoPermissionMiddleware: Não foi possível extrair informações da rota', [
            //     'user_id' => $user->id,
            //     'url' => $request->url()
            // ]);
            return $next($request);
        }

        // LOG AQUI - depois do routeInfo ser extraído
        // Log::info('AutoPermission Debug - RouteInfo', [
        //     'route_info' => $routeInfo,
        // ]);

        // Verificar permissão
        $hasPermission = $this->checkPermission($user, $routeInfo);

        if (! $hasPermission) {
            $this->logAccessDenied($user, $request, $routeInfo);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Acesso negado.',
                    'message' => 'Você não tem permissão para acessar este recurso.',
                ], 403);
            }

            abort(403, 'Você não tem permissão para acessar esta funcionalidade.');
        }

        // Log::info('AutoPermissionMiddleware: Acesso permitido, continuando', [
        //     'user_id' => $user->id
        // ]);

        return $next($request);
    }

    /**
     * Verifica se a rota deve ser ignorada
     */
    private function shouldSkipRoute(Request $request): bool
    {
        $routeName = $request->route()->getName();
        $controller = $this->getControllerName($request);

        // Verificar rotas excluídas por nome
        foreach (self::EXCLUDED_ROUTES as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return true;
            }
        }

        // Verificar controllers excluídos
        if (in_array($controller, self::EXCLUDED_CONTROLLERS)) {
            return true;
        }

        return false;
    }

    /**
     * Extrai informações relevantes da rota
     */
    private function extractRouteInfo(Request $request): ?array
    {
        $route = $request->route();
        $action = $route->getAction();
        $uri = $request->path();

        // Verificar se é uma rota admin
        if (! Str::startsWith($uri, 'admin/')) {
            return null;
        }

        // Extrair módulo da URI (admin/{parent_module}/{module}/... ou admin/{module}/...)
        $pathParts = explode('/', $uri);
        if (count($pathParts) < 2) {
            return null;
        }



        // Lista de ações conhecidas que não são módulos
        $knownActions = [
            'create',
            'create_preventiva',
            'create_diagnostico',
            'edit',
            'edit_preventiva',
            'edit_diagnostico',
            'show',
            'store',
            'store_preventiva',
            'store_diagnostico',
            'update',
            'update_preventiva',
            'update_diagnostico',
            'destroy',
            'export-pdf',
            'export-csv',
            'export-xls',
            'export-xml',
            'export',
            'gerarpdf',
            'gerarpdftotalizado',
            'gerarexcel',
            'onGeneratePdf',
            'onGenerateXls',
            'onGenerateTotalizador',
            'onImprimir',
            'onImprimirServPec',
            'onImprimirExcel',
            'onimprimirkm',
            'exportPdf',
            'exportCsv',
            'exportXls',
            'exportXml',
            'informar-km',
            'salvar-km',
            'search',
            'pedido-ja-baixado',
            'get-pedido',
            'get-tank-data',
            'getFornecedores',
            'get-combustivel-data',
            'get-combustivel-bomba',
            'get-vehicle-data',
            'get-km-info',
            'get-renavam-data',
            'get-ordemservico-data',
            'get-pneu-data',
            'get-valor-bomba',
            'get-kmhrinicialcavalo-data',
            'getDadosVeiculo',
            'getManutencao',
            'getServicosSearch',
            'getProdutosSearch',
            'getEstoqueByFilial',
            'getProdutoByEstoque',
            'getEstoqueByProduto',
            'getProduto',
            'getMateriais',
            'getProdutosPorRequisicao',
            'getVeiculo',
            'getPosto',
            'getServicos',
            'getProdutos',
            'getInfoVeiculo',
            'getTelefoneMotorista',
            'carregarUnidadeProduto',
            'carregarKm',
            'valorServicoxfornecedor',
            'ajax-get-veiculo-dados',
            'abrirModal',
            'baixar',
            'baixarLote',
            'baixarItens',
            'baixarItensUnificado',
            'baixarItensMateriais',
            'baixarItensPecas',
            'baixarPneus',
            'aprovar',
            'reprovar',
            'finalizar',
            'finalizarOs',
            'onFinalizar',
            'onFinalizarServico',
            'cancelar',
            'cancelar-os',
            'onCancelarOS',
            'reabrir',
            'reabriros',
            'reabirOS',
            'validar',
            'validarKMAtual',
            'validarRequisicaoTerceiro',
            'validar-requisicao-terceiro',
            'transferir',
            'ajustar',
            'inserirServicosePecas',
            'onDeletarServico',
            'onDeletarPecas',
            'onSolicitarServicos',
            'solicitar-servicos-os',
            'onActionSolicitarPecas',
            'solicitar-pecas',
            'onActionEncerrar',
            'encerrar-os',
            'marcar',
            'marcarMarcacao',
            'marcar-todos',
            'marcarTodosMarcacoes',
            'finalizar-os',
            'imprimir',
            'imprimirservpec',
        ];

        // Para URLs aninhadas como admin/compras/solicitacoes/create
        // queremos pegar 'solicitacoes' como o módulo real
        $module = null;
        if (count($pathParts) >= 3) {
            // Se há 3+ partes e a terceira não é um ID ou ação comum
            $thirdPart = $pathParts[2];
            $secondPart = $pathParts[1];

            // Debug temporário para solicitar-pecas
            if ($thirdPart === 'solicitar-pecas') {
                Log::info('AutoPermissionMiddleware: DEBUG solicitar-pecas', [
                    'uri' => $uri,
                    'pathParts' => $pathParts,
                    'thirdPart' => $thirdPart,
                    'secondPart' => $secondPart,
                    'in_knownActions' => in_array($thirdPart, $knownActions),
                ]);
            }

            // Log::info('AutoPermissionMiddleware: Verificando terceira parte', [
            //     'thirdPart' => $thirdPart,
            //     'is_numeric' => is_numeric($thirdPart),
            //     'in_actions' => in_array($thirdPart, $knownActions)
            // ]);

            // Caso especial para relatórios: admin/relatorios/{module}/{action}
            if (count($pathParts) >= 4 && $pathParts[1] === 'relatorios') {
                $module = $pathParts[2]; // admin/relatorios/sinistro/onGenerateTotalizador -> 'sinistro'
            }
            // CORREÇÃO: Se a segunda parte (pathParts[2]) é uma ação conhecida, o módulo é pathParts[1]
            // Exemplo: admin/recebimentocombustiveis/store -> módulo = 'recebimentocombustiveis'
            elseif (in_array($thirdPart, $knownActions) || Str::startsWith($thirdPart, 'export-')) {
                $module = $pathParts[1]; // admin/recebimentocombustiveis/store -> 'recebimentocombustiveis'

                // Debug temporário para solicitar-pecas
                if ($thirdPart === 'solicitar-pecas') {
                    Log::info('AutoPermissionMiddleware: BRANCH 1 - Ação conhecida detectada', [
                        'module_setado' => $module,
                        'pathParts[1]' => $pathParts[1],
                    ]);
                }

                // Log::info('AutoPermissionMiddleware: Detectada ação conhecida na terceira parte, usando segunda parte como módulo', [
                //     'module' => $module,
                //     'action' => $thirdPart
                // ]);
            } elseif (! is_numeric($thirdPart) && ! in_array($thirdPart, $knownActions)) {
                $module = $thirdPart; // admin/compras/solicitacoes/create -> 'solicitacoes'

                // Debug temporário para solicitar-pecas
                if ($thirdPart === 'solicitar-pecas') {
                    Log::info('AutoPermissionMiddleware: BRANCH 2 - Usando terceira parte como módulo', [
                        'module_setado' => $module,
                        'thirdPart' => $thirdPart,
                    ]);
                }

                // Log::info('AutoPermissionMiddleware: Usando terceira parte como módulo', [
                //     'module' => $module
                // ]);
            } else {
                $module = $pathParts[1]; // admin/usuarios/17/edit -> 'usuarios'

                // Debug temporário para solicitar-pecas
                if ($thirdPart === 'solicitar-pecas') {
                    Log::info('AutoPermissionMiddleware: BRANCH 3 - Fallback para segunda parte', [
                        'module_setado' => $module,
                        'pathParts[1]' => $pathParts[1],
                    ]);
                }

                // Log::info('AutoPermissionMiddleware: Usando segunda parte como módulo (fallback)', [
                //     'module' => $module
                // ]);
            }
        } else {
            $module = $pathParts[1]; // admin/dashboard -> 'dashboard'
            // Log::info('AutoPermissionMiddleware: Usando segunda parte como módulo (simples)', [
            //     'module' => $module
            // ]);
        }

        if (! $module || $module === 'admin') {
            return null;
        }

        // Determinar ação baseada no método HTTP e ação do controller
        $method = $request->method();
        $controllerAction = $this->getControllerAction($action);



        $action = $this->determineAction($method, $controllerAction, $pathParts);

        if (! $action) {
            return null;
        }

        return [
            'module' => $module,
            'action' => $action,
            'method' => $method,
            'controller_action' => $controllerAction,
            'uri' => $uri,
        ];
    }

    /**
     * Verifica se o usuário tem a permissão necessária
     */
    private function checkPermission($user, array $routeInfo): bool
    {
        $module = $routeInfo['module'];
        $action = $routeInfo['action'];

        // Debug temporário para getDadosVeiculo e carregarKm
        if (in_array($routeInfo['controller_action'] ?? '', ['getDadosVeiculo', 'carregarKm'])) {
            Log::info("DEBUG ORDEMSERVICOS - Verificando acesso", [
                'user_id' => $user->id,
                'controller_action' => $routeInfo['controller_action'] ?? 'N/A',
                'module_detectado' => $module,
                'action_detectada' => $action,
                'uri' => $routeInfo['uri'] ?? 'N/A',
                'method' => $routeInfo['method'] ?? 'N/A',
            ]);
        }

        // Debug temporário para multas
        if ($module === 'multas') {
            Log::info("DEBUG MULTAS - Verificando acesso", [
                'user_id' => $user->id,
                'module_original' => $module,
                'action_original' => $action,
                'permission_module_mapped' => self::MODULE_PERMISSION_MAPPING[$module] ?? $module,
                'permission_action_mapped' => $this->mapActionToPermission($action),
            ]);
        }

        // Debug temporário para relatorioextratoipva
        if ($module === 'relatorioextratoipva') {
            Log::info("DEBUG RELATORIO EXTRATO IPVA", [
                'user_id' => $user->id,
                'module_original' => $module,
                'action_original' => $action,
                'permission_module_mapped' => self::MODULE_PERMISSION_MAPPING[$module] ?? $module,
                'permission_action_mapped' => $this->mapActionToPermission($action),
                'uri' => request()->getPathInfo(),
                'method' => request()->getMethod(),
            ]);
        }

        // Debug temporário para relatorioipvalicenciamento
        if ($module === 'relatorioipvalicenciamento') {
            Log::info("DEBUG RELATORIO IPVA LICENCIAMENTO", [
                'user_id' => $user->id,
                'module_original' => $module,
                'action_original' => $action,
                'permission_module_mapped' => self::MODULE_PERMISSION_MAPPING[$module] ?? $module,
                'permission_action_mapped' => $this->mapActionToPermission($action),
            ]);
        }

        // Debug temporário para relatoriohistoricokm
        if ($module === 'relatoriohistoricokm') {
            Log::info("DEBUG RELATORIO HISTORICO KM", [
                'user_id' => $user->id,
                'module_original' => $module,
                'action_original' => $action,
                'permission_module_mapped' => self::MODULE_PERMISSION_MAPPING[$module] ?? $module,
                'permission_action_mapped' => $this->mapActionToPermission($action),
            ]);
        }

        // 1. Tentar com mapeamento moderno (ex: visualizar_solicitacao_compra)
        $permissionModule = self::MODULE_PERMISSION_MAPPING[$module] ?? $module;
        $permissionAction = $this->mapActionToPermission($action);
        $permission = "{$permissionAction}_{$permissionModule}";

        // Debug temporário para multas
        if ($module === 'multas') {
            Log::info("DEBUG MULTAS - Testando permissão", [
                'user_id' => $user->id,
                'permission_final' => $permission,
                'user_has_permission' => $user->can($permission),
            ]);
        }

        // Debug temporário para recebimentocombustiveis
        if ($module === 'recebimentocombustiveis') {
            Log::info("DEBUG RECEBIMENTOCOMBUSTIVEIS - Testando permissão", [
                'user_id' => $user->id,
                'module_original' => $module,
                'action_original' => $action,
                'permission_module_mapped' => $permissionModule,
                'permission_action_mapped' => $permissionAction,
                'permission_final' => $permission,
                'user_has_permission' => $user->can($permission),
                'route_name' => request()->route() ? request()->route()->getName() : 'N/A',
                'url' => request()->fullUrl(),
            ]);
        }
        // Log::info('AutoPermissionMiddleware::checkPermission - Teste 1 (mapeamento moderno)', [
        //     'user_id' => $user->id,
        //     'permission' => $permission,
        //     'module' => $module,
        //     'module_mapped' => $permissionModule,
        //     'action' => $action,
        //     'action_mapped' => $permissionAction,
        // ]);

        // Debug temporário para relatorioextratoipva
        if ($module === 'relatorioextratoipva') {
            Log::info("DEBUG RELATORIO EXTRATO IPVA - Testando permissão", [
                'user_id' => $user->id,
                'permission_final' => $permission,
                'user_has_permission' => $user->can($permission),
            ]);
        }

        // Debug temporário para relatorioipvalicenciamento
        if ($module === 'relatorioipvalicenciamento') {
            Log::info("DEBUG RELATORIO IPVA LICENCIAMENTO - Testando permissão", [
                'user_id' => $user->id,
                'permission_final' => $permission,
                'user_has_permission' => $user->can($permission),
            ]);
        }

        // Debug temporário para relatoriohistoricokm
        if ($module === 'relatoriohistoricokm') {
            Log::info("DEBUG RELATORIO HISTORICO KM - Testando permissão", [
                'user_id' => $user->id,
                'permission_final' => $permission,
                'user_has_permission' => $user->can($permission),
            ]);
        }

        // Verificar permissão usando PermissionHelper
        if (PermissionHelper::hasAnyPermission([$permission])) {
            return true;
        }

        // 2. Fallback: tentar com mapeamento legacy (ex: ver_solicitacaocompras)
        // IMPORTANTE: Usar o módulo já mapeado pelo MODULE_PERMISSION_MAPPING primeiro
        $legacyModule = self::LEGACY_MODULE_MAPPING[$permissionModule] ?? $permissionModule;
        $legacyPermission = "{$action}_{$legacyModule}";

        // Log::info('AutoPermissionMiddleware::checkPermission - Teste 2 (mapeamento legacy)', [
        //     'user_id' => $user->id,
        //     'permission' => $legacyPermission,
        //     'permission_module_usado' => $permissionModule,
        //     'legacy_module' => $legacyModule
        // ]);

        if (PermissionHelper::hasAnyPermission([$legacyPermission])) {
            return true;
        }

        // 3. Fallback: tentar permissão original (ex: ver_solicitacoes)
        $originalPermission = "{$action}_{$module}";



        if (PermissionHelper::hasAnyPermission([$originalPermission])) {
            return true;
        }

        // 4. Fallback: verificar acesso ao módulo
        if ($action === 'ver') {
            // Log::info('AutoPermissionMiddleware::checkPermission - Teste 4 (acesso ao módulo)', [
            //     'user_id' => $user->id,
            //     'module' => $module,
            //     'action' => $action
            // ]);

            if (PermissionHelper::hasModuleAccess($module)) {
                // Log::info('AutoPermissionMiddleware::checkPermission - SUCESSO com acesso ao módulo', [
                //     'user_id' => $user->id,
                //     'module' => $module
                // ]);
                return true;
            }
        }

        // Fallback: verificar por prefixo
        // Log::info('AutoPermissionMiddleware::checkPermission - Teste 5 (prefixo)', [
        //     'user_id' => $user->id,
        //     'prefix' => $module
        // ]);

        if (PermissionHelper::hasAnyPermissionStartingWith($module)) {
            // Log::info('AutoPermissionMiddleware::checkPermission - SUCESSO com prefixo', [
            //     'user_id' => $user->id,
            //     'prefix' => $module
            // ]);
            return true;
        }



        return false;
    }

    /**
     * Determina a ação baseada no método HTTP e ação do controller
     */
    private function determineAction(string $method, ?string $controllerAction, array $pathParts): ?string
    {
        // Debug temporário para onActionSolicitarPecas
        if ($controllerAction === 'onActionSolicitarPecas') {
            Log::info('AutoPermissionMiddleware: DEBUG determineAction', [
                'method' => $method,
                'controllerAction' => $controllerAction,
                'isset_mapping' => isset(self::ACTION_MAPPING[$method][$controllerAction]),
                'mapping_exists' => self::ACTION_MAPPING[$method][$controllerAction] ?? 'NÃO ENCONTRADO',
            ]);
        }

        // Primeiro, tentar mapear por ação do controller
        if ($controllerAction && isset(self::ACTION_MAPPING[$method][$controllerAction])) {
            return self::ACTION_MAPPING[$method][$controllerAction];
        }

        // Mapear por padrões na URL
        if (count($pathParts) >= 3) {
            $lastSegment = $pathParts[count($pathParts) - 1];

            switch ($lastSegment) {
                case 'create':
                    return 'criar';
                case 'edit':
                    return 'editar';
            }
        }

        // Mapear por método HTTP
        switch ($method) {
            case 'GET':
                return 'ver';
            case 'POST':
                return 'criar';
            case 'PUT':
            case 'PATCH':
                return 'editar';
            case 'DELETE':
                return 'excluir';
            default:
                return null;
        }
    }

    /**
     * Obtém o nome do controller da rota
     */
    private function getControllerName(Request $request): ?string
    {
        $action = $request->route()->getAction();

        if (isset($action['controller'])) {
            $controller = $action['controller'];
            if (is_string($controller) && str_contains($controller, '@')) {
                $controller = explode('@', $controller)[0];
            }

            return class_basename($controller);
        }

        return null;
    }

    /**
     * Obtém a ação do controller da rota
     */
    private function getControllerAction($action): ?string
    {
        if (isset($action['controller'])) {
            $controller = $action['controller'];

            // Laravel 11: Controllers são arrays [ControllerClass, 'method']
            if (is_array($controller) && count($controller) === 2) {
                return $controller[1];
            }

            // Laravel 10 e anterior: Controllers são strings 'ControllerClass@method'
            if (is_string($controller) && str_contains($controller, '@')) {
                return explode('@', $controller)[1];
            }
        }

        return null;
    }

    /**
     * Mapeia ação do middleware para formato da permissão
     */
    private function mapActionToPermission(string $action): string
    {
        // Mapeamento de ações do middleware para ações das permissões
        // CORREÇÃO: A maioria das permissões usa 'ver' em vez de 'visualizar'
        $actionMapping = [
            'ver' => 'ver', // Manter 'ver' como 'ver'
            'criar' => 'criar',
            'editar' => 'editar',
            'excluir' => 'excluir',
        ];

        return $actionMapping[$action] ?? $action;
    }

    /**
     * Log de acesso negado para auditoria
     */
    private function logAccessDenied($user, Request $request, array $routeInfo): void
    {
        Log::warning('Acesso negado por permissão', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'route_info' => $routeInfo,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
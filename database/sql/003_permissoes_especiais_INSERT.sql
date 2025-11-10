-- ============================================================================
-- SCRIPT: Permissões Especiais e Relatórios - INSERT DIRETO
-- DESCRIÇÃO: Cria permissões especiais que não seguem o padrão CRUD
-- IMPORTANTE: Rodar no DBeaver após sincronizar permissões básicas
-- DATA: 2025-10-07
-- ============================================================================
-- INSTRUÇÃO: Execute todo o script de uma vez no DBeaver
-- Em caso de erro, execute ROLLBACK; manualmente
-- ============================================================================

BEGIN;

-- ============================================================================
-- PERMISSÕES ESPECIAIS - ESTOQUE
-- ============================================================================

-- Baixa de Estoque
INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'baixar_estoque', 'Permite dar baixa em itens do estoque', 'Estoque', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_estoque');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'baixar_estoque_materiais', 'Permite dar baixa em materiais do estoque', 'Estoque', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_estoque_materiais');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'baixar_estoque_pecas', 'Permite dar baixa em peças do estoque', 'Estoque', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_estoque_pecas');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'baixar_estoque_unificado', 'Permite dar baixa unificada no estoque', 'Estoque', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_estoque_unificado');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'transferir_estoque', 'Permite transferir itens entre estoques', 'Estoque', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'transferir_estoque');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'transferir_estoque_direto', 'Permite transferência direta de estoque', 'Estoque', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'transferir_estoque_direto');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'ajustar_estoque', 'Permite ajustar quantidades do estoque', 'Estoque', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'ajustar_estoque');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'aprovar_transferencia_estoque', 'Permite aprovar transferências de estoque', 'Estoque', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'aprovar_transferencia_estoque');

-- ============================================================================
-- PERMISSÕES ESPECIAIS - PNEUS
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'baixar_pneu', 'Permite dar baixa em pneus', 'Pneus', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_pneu');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'baixar_pneu_transferencia', 'Permite baixar pneus em transferência', 'Pneus', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_pneu_transferencia');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'movimentar_pneu', 'Permite movimentar pneus entre posições', 'Pneus', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'movimentar_pneu');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'calibrar_pneu', 'Permite registrar calibragem de pneus', 'Pneus', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'calibrar_pneu');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'inventariar_pneu', 'Permite realizar inventário de pneus', 'Pneus', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'inventariar_pneu');

-- ============================================================================
-- PERMISSÕES ESPECIAIS - VEÍCULOS
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'ativar_inativar_veiculo', 'Permite ativar/inativar veículos', 'Veículos', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'ativar_inativar_veiculo');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'alterar_km_manual', 'Permite alterar KM manualmente', 'Veículos', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'alterar_km_manual');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'aprovar_alteracao_km', 'Permite aprovar alterações de KM', 'Veículos', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'aprovar_alteracao_km');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'atrelar_veiculo', 'Permite atrelar/desatrelar veículos', 'Veículos', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'atrelar_veiculo');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'transferir_veiculo_base', 'Permite transferir veículo entre bases', 'Veículos', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'transferir_veiculo_base');

-- ============================================================================
-- PERMISSÕES ESPECIAIS - ABASTECIMENTO
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'ajustar_km_abastecimento', 'Permite ajustar KM de abastecimento', 'Abastecimento', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'ajustar_km_abastecimento');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'validar_abastecimento', 'Permite validar abastecimentos', 'Abastecimento', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'validar_abastecimento');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'lancar_abastecimento_manual', 'Permite lançar abastecimento manual', 'Abastecimento', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'lancar_abastecimento_manual');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'aferir_bomba', 'Permite registrar aferição de bombas', 'Abastecimento', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'aferir_bomba');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'faturar_abastecimento', 'Permite faturar abastecimentos', 'Abastecimento', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'faturar_abastecimento');

-- ============================================================================
-- PERMISSÕES ESPECIAIS - MANUTENÇÃO
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'aprovar_os', 'Permite aprovar ordens de serviço', 'Manutenção', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'aprovar_os');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'finalizar_os', 'Permite finalizar ordens de serviço', 'Manutenção', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'finalizar_os');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'cancelar_os', 'Permite cancelar ordens de serviço', 'Manutenção', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'cancelar_os');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'reabrir_os', 'Permite reabrir ordens de serviço', 'Manutenção', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'reabrir_os');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'lancar_nota_servico', 'Permite lançar notas de serviço', 'Manutenção', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'lancar_nota_servico');

-- ============================================================================
-- PERMISSÕES ESPECIAIS - COMPRAS
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'aprovar_solicitacao', 'Permite aprovar solicitações de compra', 'Compras', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'aprovar_solicitacao');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'reprovar_solicitacao', 'Permite reprovar solicitações de compra', 'Compras', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'reprovar_solicitacao');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'aprovar_orcamento', 'Permite aprovar orçamentos', 'Compras', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'aprovar_orcamento');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'aprovar_pedido', 'Permite aprovar pedidos de compra', 'Compras', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'aprovar_pedido');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'cancelar_pedido', 'Permite cancelar pedidos de compra', 'Compras', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'cancelar_pedido');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'receber_pedido', 'Permite dar entrada/receber pedidos', 'Compras', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'receber_pedido');

-- ============================================================================
-- PERMISSÕES ESPECIAIS - LICENCIAMENTO E DOCUMENTOS
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'baixar_lote_licenciamento', 'Permite baixar lote de licenciamentos', 'Veículos', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_lote_licenciamento');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'baixar_lote_ipva', 'Permite baixar lote de IPVA', 'Veículos', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_lote_ipva');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'baixar_lote_multas', 'Permite baixar lote de multas', 'Veículos', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_lote_multas');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'baixar_lote_notificacoes', 'Permite baixar lote de notificações', 'Veículos', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_lote_notificacoes');

-- ============================================================================
-- PERMISSÕES ESPECIAIS - PORTARIA
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'liberar_saida_veiculo', 'Permite liberar saída de veículos', 'Portaria', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'liberar_saida_veiculo');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'registrar_entrada_veiculo', 'Permite registrar entrada de veículos', 'Portaria', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'registrar_entrada_veiculo');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'autorizar_saida_emergencia', 'Permite autorizar saídas de emergência', 'Portaria', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'autorizar_saida_emergencia');

-- ============================================================================
-- PERMISSÕES ESPECIAIS - IMOBILIZADOS
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'aprovar_imobilizado_gestor', 'Permite aprovar imobilizados (gestor)', 'Imobilizados', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'aprovar_imobilizado_gestor');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'receber_imobilizado', 'Permite receber imobilizados', 'Imobilizados', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'receber_imobilizado');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'transferir_imobilizado', 'Permite transferir imobilizados', 'Imobilizados', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'transferir_imobilizado');

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - ABASTECIMENTO
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_abastecimento', 'Permite visualizar relatórios de abastecimento', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_abastecimento');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_fechamento_abastecimento', 'Permite visualizar relatório de fechamento de abastecimento', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_fechamento_abastecimento');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_faturamento_abastecimento', 'Permite visualizar relatório de faturamento de abastecimento', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_faturamento_abastecimento');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_extrato_abastecimento_terceiros', 'Permite visualizar extrato de abastecimento terceiros', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_extrato_abastecimento_terceiros');

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - VEÍCULOS
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_veiculo', 'Permite visualizar relatórios de veículos', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_veiculo');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_certificado_veiculo', 'Permite visualizar certificado de veículos', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_certificado_veiculo');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_compra_venda_veiculo', 'Permite visualizar relatório de compra/venda de veículos', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_compra_venda_veiculo');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_km_historico', 'Permite visualizar histórico de KM', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_km_historico');

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - MANUTENÇÃO
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_manutencao', 'Permite visualizar relatórios de manutenção', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_manutencao');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_historico_manutencao_veiculo', 'Permite visualizar histórico de manutenção por veículo', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_historico_manutencao_veiculo');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_os_aberta', 'Permite visualizar relatório de OS abertas', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_os_aberta');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_custo_manutencao', 'Permite visualizar relatório de custos de manutenção', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_custo_manutencao');

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - PNEUS
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_pneus', 'Permite visualizar relatórios de pneus', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_pneus');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_calibracao', 'Permite visualizar relatório de calibração', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_calibracao');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_conferencia_rotativo', 'Permite visualizar relatório de conferência rotativo', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_conferencia_rotativo');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_inventario_pneus', 'Permite visualizar relatório de inventário de pneus', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_inventario_pneus');

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - ESTOQUE
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_estoque', 'Permite visualizar relatórios de estoque', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_estoque');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_baixa_estoque', 'Permite visualizar relatório de baixa de estoque', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_baixa_estoque');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_movimentacao_estoque', 'Permite visualizar relatório de movimentação de estoque', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_movimentacao_estoque');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_estoque_minimo', 'Permite visualizar relatório de estoque mínimo', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_estoque_minimo');

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - COMPRAS
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_compras', 'Permite visualizar relatórios de compras', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_compras');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_atendimento_compra', 'Permite visualizar relatório de atendimento de compras', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_atendimento_compra');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_fornecedor_comissionados', 'Permite visualizar relatório de fornecedores comissionados', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_fornecedor_comissionados');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_conta_corrente_fornecedor', 'Permite visualizar conta corrente de fornecedor', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_conta_corrente_fornecedor');

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - FINANCEIRO
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_financeiro', 'Permite visualizar relatórios financeiros', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_financeiro');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_ipva_licenciamento', 'Permite visualizar relatório de IPVA e licenciamento', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_ipva_licenciamento');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_multas', 'Permite visualizar relatório de multas', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_multas');

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - CHECKLIST
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_checklist', 'Permite visualizar relatórios de checklist', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_checklist');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_checklist_fornecedor', 'Permite visualizar relatório de checklist por fornecedor', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_checklist_fornecedor');

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - SUPORTE/QUALIDADE
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_qualidade', 'Permite visualizar relatórios de qualidade', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_qualidade');

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
SELECT 'relatorio_tickets_suporte', 'Permite visualizar relatórios de tickets de suporte', 'Relatórios', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_tickets_suporte');

-- ============================================================================
-- COMMIT DA TRANSAÇÃO
-- ============================================================================

COMMIT;

-- ============================================================================
-- VERIFICAÇÃO FINAL
-- ============================================================================

SELECT
    '✅ Script executado com sucesso!' as status,
    COUNT(*) as total_permissoes_especiais
FROM permissions
WHERE name IN (
    'baixar_estoque', 'baixar_pneu', 'aprovar_os',
    'relatorio_abastecimento', 'relatorio_estoque',
    'relatorio_compras', 'relatorio_financeiro',
    'transferir_estoque', 'movimentar_pneu',
    'alterar_km_manual', 'validar_abastecimento'
);

-- ============================================================================
-- ROLLBACK MANUAL (usar apenas em caso de erro)
-- ============================================================================
-- Se algo der errado, execute: ROLLBACK;
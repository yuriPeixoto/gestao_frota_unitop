-- ============================================================================
-- SCRIPT: Permissões Especiais e Relatórios (CORRIGIDO - SEM ON CONFLICT)
-- DESCRIÇÃO: Cria permissões especiais que não seguem o padrão CRUD
-- IMPORTANTE: Rodar no DBeaver após sincronizar permissões básicas
-- DATA: 2025-10-07
-- ============================================================================

-- ATENÇÃO: Este arquivo foi corrigido para usar WHERE NOT EXISTS
-- ao invés de ON CONFLICT, pois a tabela permissions não tem constraint única

-- Para rodar mais rápido, você pode executar tudo de uma vez.
-- O WHERE NOT EXISTS garante que não haverá duplicatas.

-- ============================================================================
-- PERMISSÕES ESPECIAIS - ESTOQUE
-- ============================================================================

DO $$
BEGIN
    -- Baixa de Estoque
    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_estoque') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('baixar_estoque', 'Permite dar baixa em itens do estoque', 'Estoque', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_estoque_materiais') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('baixar_estoque_materiais', 'Permite dar baixa em materiais do estoque', 'Estoque', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_estoque_pecas') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('baixar_estoque_pecas', 'Permite dar baixa em peças do estoque', 'Estoque', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_estoque_unificado') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('baixar_estoque_unificado', 'Permite dar baixa unificada no estoque', 'Estoque', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'transferir_estoque') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('transferir_estoque', 'Permite transferir itens entre estoques', 'Estoque', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'transferir_estoque_direto') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('transferir_estoque_direto', 'Permite transferência direta de estoque', 'Estoque', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'ajustar_estoque') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('ajustar_estoque', 'Permite ajustar quantidades do estoque', 'Estoque', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'aprovar_transferencia_estoque') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('aprovar_transferencia_estoque', 'Permite aprovar transferências de estoque', 'Estoque', 'web', NOW(), NOW());
    END IF;

-- ============================================================================
-- PERMISSÕES ESPECIAIS - PNEUS
-- ============================================================================

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_pneu') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('baixar_pneu', 'Permite dar baixa em pneus', 'Pneus', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_pneu_transferencia') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('baixar_pneu_transferencia', 'Permite baixar pneus em transferência', 'Pneus', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'movimentar_pneu') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('movimentar_pneu', 'Permite movimentar pneus entre posições', 'Pneus', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'calibrar_pneu') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('calibrar_pneu', 'Permite registrar calibragem de pneus', 'Pneus', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'inventariar_pneu') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('inventariar_pneu', 'Permite realizar inventário de pneus', 'Pneus', 'web', NOW(), NOW());
    END IF;

-- ============================================================================
-- PERMISSÕES ESPECIAIS - VEÍCULOS
-- ============================================================================

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'ativar_inativar_veiculo') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('ativar_inativar_veiculo', 'Permite ativar/inativar veículos', 'Veículos', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'alterar_km_manual') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('alterar_km_manual', 'Permite alterar KM manualmente', 'Veículos', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'aprovar_alteracao_km') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('aprovar_alteracao_km', 'Permite aprovar alterações de KM', 'Veículos', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'atrelar_veiculo') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('atrelar_veiculo', 'Permite atrelar/desatrelar veículos', 'Veículos', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'transferir_veiculo_base') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('transferir_veiculo_base', 'Permite transferir veículo entre bases', 'Veículos', 'web', NOW(), NOW());
    END IF;

-- ============================================================================
-- PERMISSÕES ESPECIAIS - ABASTECIMENTO
-- ============================================================================

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'ajustar_km_abastecimento') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('ajustar_km_abastecimento', 'Permite ajustar KM de abastecimento', 'Abastecimento', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'validar_abastecimento') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('validar_abastecimento', 'Permite validar abastecimentos', 'Abastecimento', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'lancar_abastecimento_manual') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('lancar_abastecimento_manual', 'Permite lançar abastecimento manual', 'Abastecimento', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'aferir_bomba') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('aferir_bomba', 'Permite registrar aferição de bombas', 'Abastecimento', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'faturar_abastecimento') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('faturar_abastecimento', 'Permite faturar abastecimentos', 'Abastecimento', 'web', NOW(), NOW());
    END IF;

-- ============================================================================
-- PERMISSÕES ESPECIAIS - MANUTENÇÃO
-- ============================================================================

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'aprovar_os') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('aprovar_os', 'Permite aprovar ordens de serviço', 'Manutenção', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'finalizar_os') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('finalizar_os', 'Permite finalizar ordens de serviço', 'Manutenção', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'cancelar_os') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('cancelar_os', 'Permite cancelar ordens de serviço', 'Manutenção', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'reabrir_os') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('reabrir_os', 'Permite reabrir ordens de serviço', 'Manutenção', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'lancar_nota_servico') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('lancar_nota_servico', 'Permite lançar notas de serviço', 'Manutenção', 'web', NOW(), NOW());
    END IF;

-- ============================================================================
-- PERMISSÕES ESPECIAIS - COMPRAS
-- ============================================================================

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'aprovar_solicitacao') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('aprovar_solicitacao', 'Permite aprovar solicitações de compra', 'Compras', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'reprovar_solicitacao') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('reprovar_solicitacao', 'Permite reprovar solicitações de compra', 'Compras', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'aprovar_orcamento') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('aprovar_orcamento', 'Permite aprovar orçamentos', 'Compras', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'aprovar_pedido') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('aprovar_pedido', 'Permite aprovar pedidos de compra', 'Compras', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'cancelar_pedido') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('cancelar_pedido', 'Permite cancelar pedidos de compra', 'Compras', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'receber_pedido') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('receber_pedido', 'Permite dar entrada/receber pedidos', 'Compras', 'web', NOW(), NOW());
    END IF;

-- ============================================================================
-- PERMISSÕES ESPECIAIS - LICENCIAMENTO E DOCUMENTOS
-- ============================================================================

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_lote_licenciamento') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('baixar_lote_licenciamento', 'Permite baixar lote de licenciamentos', 'Veículos', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_lote_ipva') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('baixar_lote_ipva', 'Permite baixar lote de IPVA', 'Veículos', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_lote_multas') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('baixar_lote_multas', 'Permite baixar lote de multas', 'Veículos', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'baixar_lote_notificacoes') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('baixar_lote_notificacoes', 'Permite baixar lote de notificações', 'Veículos', 'web', NOW(), NOW());
    END IF;

-- ============================================================================
-- PERMISSÕES ESPECIAIS - PORTARIA
-- ============================================================================

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'liberar_saida_veiculo') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('liberar_saida_veiculo', 'Permite liberar saída de veículos', 'Portaria', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'registrar_entrada_veiculo') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('registrar_entrada_veiculo', 'Permite registrar entrada de veículos', 'Portaria', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'autorizar_saida_emergencia') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('autorizar_saida_emergencia', 'Permite autorizar saídas de emergência', 'Portaria', 'web', NOW(), NOW());
    END IF;

-- ============================================================================
-- PERMISSÕES ESPECIAIS - IMOBILIZADOS
-- ============================================================================

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'aprovar_imobilizado_gestor') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('aprovar_imobilizado_gestor', 'Permite aprovar imobilizados (gestor)', 'Imobilizados', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'receber_imobilizado') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('receber_imobilizado', 'Permite receber imobilizados', 'Imobilizados', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'transferir_imobilizado') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('transferir_imobilizado', 'Permite transferir imobilizados', 'Imobilizados', 'web', NOW(), NOW());
    END IF;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - ABASTECIMENTO
-- ============================================================================

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_abastecimento') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_abastecimento', 'Permite visualizar relatórios de abastecimento', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_fechamento_abastecimento') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_fechamento_abastecimento', 'Permite visualizar relatório de fechamento de abastecimento', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_faturamento_abastecimento') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_faturamento_abastecimento', 'Permite visualizar relatório de faturamento de abastecimento', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_extrato_abastecimento_terceiros') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_extrato_abastecimento_terceiros', 'Permite visualizar extrato de abastecimento terceiros', 'Relatórios', 'web', NOW(), NOW());
    END IF;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - VEÍCULOS
-- ============================================================================

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_veiculo') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_veiculo', 'Permite visualizar relatórios de veículos', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_certificado_veiculo') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_certificado_veiculo', 'Permite visualizar certificado de veículos', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_compra_venda_veiculo') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_compra_venda_veiculo', 'Permite visualizar relatório de compra/venda de veículos', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_km_historico') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_km_historico', 'Permite visualizar histórico de KM', 'Relatórios', 'web', NOW(), NOW());
    END IF;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - MANUTENÇÃO
-- ============================================================================

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_manutencao') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_manutencao', 'Permite visualizar relatórios de manutenção', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_historico_manutencao_veiculo') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_historico_manutencao_veiculo', 'Permite visualizar histórico de manutenção por veículo', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_os_aberta') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_os_aberta', 'Permite visualizar relatório de OS abertas', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_custo_manutencao') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_custo_manutencao', 'Permite visualizar relatório de custos de manutenção', 'Relatórios', 'web', NOW(), NOW());
    END IF;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - PNEUS
-- ============================================================================

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_pneus') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_pneus', 'Permite visualizar relatórios de pneus', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_calibracao') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_calibracao', 'Permite visualizar relatório de calibração', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_conferencia_rotativo') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_conferencia_rotativo', 'Permite visualizar relatório de conferência rotativo', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_inventario_pneus') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_inventario_pneus', 'Permite visualizar relatório de inventário de pneus', 'Relatórios', 'web', NOW(), NOW());
    END IF;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - ESTOQUE
-- ============================================================================

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_estoque') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_estoque', 'Permite visualizar relatórios de estoque', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_baixa_estoque') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_baixa_estoque', 'Permite visualizar relatório de baixa de estoque', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_movimentacao_estoque') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_movimentacao_estoque', 'Permite visualizar relatório de movimentação de estoque', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_estoque_minimo') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_estoque_minimo', 'Permite visualizar relatório de estoque mínimo', 'Relatórios', 'web', NOW(), NOW());
    END IF;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - COMPRAS
-- ============================================================================

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_compras') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_compras', 'Permite visualizar relatórios de compras', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_atendimento_compra') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_atendimento_compra', 'Permite visualizar relatório de atendimento de compras', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_fornecedor_comissionados') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_fornecedor_comissionados', 'Permite visualizar relatório de fornecedores comissionados', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_conta_corrente_fornecedor') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_conta_corrente_fornecedor', 'Permite visualizar conta corrente de fornecedor', 'Relatórios', 'web', NOW(), NOW());
    END IF;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - FINANCEIRO
-- ============================================================================

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_financeiro') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_financeiro', 'Permite visualizar relatórios financeiros', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_ipva_licenciamento') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_ipva_licenciamento', 'Permite visualizar relatório de IPVA e licenciamento', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_multas') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_multas', 'Permite visualizar relatório de multas', 'Relatórios', 'web', NOW(), NOW());
    END IF;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - CHECKLIST
-- ============================================================================

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_checklist') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_checklist', 'Permite visualizar relatórios de checklist', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_checklist_fornecedor') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_checklist_fornecedor', 'Permite visualizar relatório de checklist por fornecedor', 'Relatórios', 'web', NOW(), NOW());
    END IF;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - SUPORTE/QUALIDADE
-- ============================================================================

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_qualidade') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_qualidade', 'Permite visualizar relatórios de qualidade', 'Relatórios', 'web', NOW(), NOW());
    END IF;

    IF NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'relatorio_tickets_suporte') THEN
        INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
        VALUES ('relatorio_tickets_suporte', 'Permite visualizar relatórios de tickets de suporte', 'Relatórios', 'web', NOW(), NOW());
    END IF;

END $$;

-- ============================================================================
-- VERIFICAÇÃO
-- ============================================================================
-- SELECT name, description, premission_group
-- FROM permissions
-- WHERE name LIKE '%baixar%' OR name LIKE '%relatorio%'
-- ORDER BY premission_group, name;

-- ============================================================================
-- MENSAGEM FINAL
-- ============================================================================
SELECT '✅ Script executado com sucesso! ' || COUNT(*) || ' permissões especiais verificadas/criadas.' as resultado
FROM permissions
WHERE name IN (
    'baixar_estoque', 'baixar_pneu', 'aprovar_os', 'relatorio_abastecimento',
    'relatorio_estoque', 'relatorio_compras', 'relatorio_financeiro'
);
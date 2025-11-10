-- ============================================================================
-- SCRIPT: Permissões Especiais e Relatórios
-- DESCRIÇÃO: Cria permissões especiais que não seguem o padrão CRUD
-- IMPORTANTE: Rodar no DBeaver após sincronizar permissões básicas
-- DATA: 2025-10-07
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
VALUES
    ('baixar_pneu', 'Permite dar baixa em pneus', 'Pneus', 'web', NOW(), NOW()),
    ('baixar_pneu_transferencia', 'Permite baixar pneus em transferência', 'Pneus', 'web', NOW(), NOW()),
    ('movimentar_pneu', 'Permite movimentar pneus entre posições', 'Pneus', 'web', NOW(), NOW()),
    ('calibrar_pneu', 'Permite registrar calibragem de pneus', 'Pneus', 'web', NOW(), NOW()),
    ('inventariar_pneu', 'Permite realizar inventário de pneus', 'Pneus', 'web', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ============================================================================
-- PERMISSÕES ESPECIAIS - VEÍCULOS
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
VALUES
    ('ativar_inativar_veiculo', 'Permite ativar/inativar veículos', 'Veículos', 'web', NOW(), NOW()),
    ('alterar_km_manual', 'Permite alterar KM manualmente', 'Veículos', 'web', NOW(), NOW()),
    ('aprovar_alteracao_km', 'Permite aprovar alterações de KM', 'Veículos', 'web', NOW(), NOW()),
    ('atrelar_veiculo', 'Permite atrelar/desatrelar veículos', 'Veículos', 'web', NOW(), NOW()),
    ('transferir_veiculo_base', 'Permite transferir veículo entre bases', 'Veículos', 'web', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ============================================================================
-- PERMISSÕES ESPECIAIS - ABASTECIMENTO
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
VALUES
    ('ajustar_km_abastecimento', 'Permite ajustar KM de abastecimento', 'Abastecimento', 'web', NOW(), NOW()),
    ('validar_abastecimento', 'Permite validar abastecimentos', 'Abastecimento', 'web', NOW(), NOW()),
    ('lancar_abastecimento_manual', 'Permite lançar abastecimento manual', 'Abastecimento', 'web', NOW(), NOW()),
    ('aferir_bomba', 'Permite registrar aferição de bombas', 'Abastecimento', 'web', NOW(), NOW()),
    ('faturar_abastecimento', 'Permite faturar abastecimentos', 'Abastecimento', 'web', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ============================================================================
-- PERMISSÕES ESPECIAIS - MANUTENÇÃO
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
VALUES
    ('aprovar_os', 'Permite aprovar ordens de serviço', 'Manutenção', 'web', NOW(), NOW()),
    ('finalizar_os', 'Permite finalizar ordens de serviço', 'Manutenção', 'web', NOW(), NOW()),
    ('cancelar_os', 'Permite cancelar ordens de serviço', 'Manutenção', 'web', NOW(), NOW()),
    ('reabrir_os', 'Permite reabrir ordens de serviço', 'Manutenção', 'web', NOW(), NOW()),
    ('lancar_nota_servico', 'Permite lançar notas de serviço', 'Manutenção', 'web', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ============================================================================
-- PERMISSÕES ESPECIAIS - COMPRAS
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
VALUES
    ('aprovar_solicitacao', 'Permite aprovar solicitações de compra', 'Compras', 'web', NOW(), NOW()),
    ('reprovar_solicitacao', 'Permite reprovar solicitações de compra', 'Compras', 'web', NOW(), NOW()),
    ('aprovar_orcamento', 'Permite aprovar orçamentos', 'Compras', 'web', NOW(), NOW()),
    ('aprovar_pedido', 'Permite aprovar pedidos de compra', 'Compras', 'web', NOW(), NOW()),
    ('cancelar_pedido', 'Permite cancelar pedidos de compra', 'Compras', 'web', NOW(), NOW()),
    ('receber_pedido', 'Permite dar entrada/receber pedidos', 'Compras', 'web', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ============================================================================
-- PERMISSÕES ESPECIAIS - LICENCIAMENTO E DOCUMENTOS
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
VALUES
    ('baixar_lote_licenciamento', 'Permite baixar lote de licenciamentos', 'Veículos', 'web', NOW(), NOW()),
    ('baixar_lote_ipva', 'Permite baixar lote de IPVA', 'Veículos', 'web', NOW(), NOW()),
    ('baixar_lote_multas', 'Permite baixar lote de multas', 'Veículos', 'web', NOW(), NOW()),
    ('baixar_lote_notificacoes', 'Permite baixar lote de notificações', 'Veículos', 'web', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ============================================================================
-- PERMISSÕES ESPECIAIS - PORTARIA
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
VALUES
    ('liberar_saida_veiculo', 'Permite liberar saída de veículos', 'Portaria', 'web', NOW(), NOW()),
    ('registrar_entrada_veiculo', 'Permite registrar entrada de veículos', 'Portaria', 'web', NOW(), NOW()),
    ('autorizar_saida_emergencia', 'Permite autorizar saídas de emergência', 'Portaria', 'web', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ============================================================================
-- PERMISSÕES ESPECIAIS - IMOBILIZADOS
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
VALUES
    ('aprovar_imobilizado_gestor', 'Permite aprovar imobilizados (gestor)', 'Imobilizados', 'web', NOW(), NOW()),
    ('receber_imobilizado', 'Permite receber imobilizados', 'Imobilizados', 'web', NOW(), NOW()),
    ('transferir_imobilizado', 'Permite transferir imobilizados', 'Imobilizados', 'web', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - ABASTECIMENTO
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
VALUES
    ('relatorio_abastecimento', 'Permite visualizar relatórios de abastecimento', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_fechamento_abastecimento', 'Permite visualizar relatório de fechamento de abastecimento', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_faturamento_abastecimento', 'Permite visualizar relatório de faturamento de abastecimento', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_extrato_abastecimento_terceiros', 'Permite visualizar extrato de abastecimento terceiros', 'Relatórios', 'web', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - VEÍCULOS
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
VALUES
    ('relatorio_veiculo', 'Permite visualizar relatórios de veículos', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_certificado_veiculo', 'Permite visualizar certificado de veículos', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_compra_venda_veiculo', 'Permite visualizar relatório de compra/venda de veículos', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_km_historico', 'Permite visualizar histórico de KM', 'Relatórios', 'web', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - MANUTENÇÃO
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
VALUES
    ('relatorio_manutencao', 'Permite visualizar relatórios de manutenção', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_historico_manutencao_veiculo', 'Permite visualizar histórico de manutenção por veículo', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_os_aberta', 'Permite visualizar relatório de OS abertas', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_custo_manutencao', 'Permite visualizar relatório de custos de manutenção', 'Relatórios', 'web', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - PNEUS
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
VALUES
    ('relatorio_pneus', 'Permite visualizar relatórios de pneus', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_calibracao', 'Permite visualizar relatório de calibração', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_conferencia_rotativo', 'Permite visualizar relatório de conferência rotativo', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_inventario_pneus', 'Permite visualizar relatório de inventário de pneus', 'Relatórios', 'web', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - ESTOQUE
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
VALUES
    ('relatorio_estoque', 'Permite visualizar relatórios de estoque', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_baixa_estoque', 'Permite visualizar relatório de baixa de estoque', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_movimentacao_estoque', 'Permite visualizar relatório de movimentação de estoque', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_estoque_minimo', 'Permite visualizar relatório de estoque mínimo', 'Relatórios', 'web', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - COMPRAS
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
VALUES
    ('relatorio_compras', 'Permite visualizar relatórios de compras', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_atendimento_compra', 'Permite visualizar relatório de atendimento de compras', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_fornecedor_comissionados', 'Permite visualizar relatório de fornecedores comissionados', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_conta_corrente_fornecedor', 'Permite visualizar conta corrente de fornecedor', 'Relatórios', 'web', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - FINANCEIRO
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
VALUES
    ('relatorio_financeiro', 'Permite visualizar relatórios financeiros', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_ipva_licenciamento', 'Permite visualizar relatório de IPVA e licenciamento', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_multas', 'Permite visualizar relatório de multas', 'Relatórios', 'web', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - CHECKLIST
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
VALUES
    ('relatorio_checklist', 'Permite visualizar relatórios de checklist', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_checklist_fornecedor', 'Permite visualizar relatório de checklist por fornecedor', 'Relatórios', 'web', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

-- ============================================================================
-- PERMISSÕES DE RELATÓRIOS JASPER - SUPORTE/QUALIDADE
-- ============================================================================

INSERT INTO permissions (name, description, premission_group, guard_name, created_at, updated_at)
VALUES
    ('relatorio_qualidade', 'Permite visualizar relatórios de qualidade', 'Relatórios', 'web', NOW(), NOW()),
    ('relatorio_tickets_suporte', 'Permite visualizar relatórios de tickets de suporte', 'Relatórios', 'web', NOW(), NOW())
ON CONFLICT (name) DO NOTHING;

COMMIT;

-- ============================================================================
-- VERIFICAÇÃO
-- ============================================================================
-- SELECT name, description, premission_group
-- FROM permissions
-- WHERE name LIKE '%baixar%' OR name LIKE '%relatorio%'
-- ORDER BY premission_group, name;

-- ============================================================================
-- NOTAS IMPORTANTES
-- ============================================================================
-- 1. Estas permissões complementam as básicas (CRUD)
-- 2. Permissões de relatórios podem ser agrupadas ou individualizadas conforme necessidade
-- 3. Após criar, atribuir às roles apropriadas
-- 4. O middleware AutoPermissionMiddleware deve ser atualizado para reconhecer estas permissões
-- ============================================================================
-- SCRIPT: Importação de Roles (Grupos) do Sistema Antigo
-- DESCRIÇÃO: Importa grupos do sistema antigo que ainda não foram migrados
-- IMPORTANTE: Rodar no DBeaver com cautela
-- DATA: 2025-10-07
-- ============================================================================

-- ROLES JÁ EXISTENTES NO LARAVEL (NÃO IMPORTAR):
-- 1  - Solicitante
-- 2  - Aprovador de Solicitação
-- 3  - Comprador
-- 4  - Aprovador de Compra
-- 5  - Aprovador de Compra Nível 1
-- 6  - Aprovador de Compra Nível 2
-- 7  - Aprovador de Compra Nível 3
-- 8  - Aprovador de Compra Nível 4
-- 9  - Almoxarife
-- 10 - Gestor de Frota
-- 11 - Administrador do Módulo Compras
-- 33 - Aprovador de Requisição
-- 34 - Equipe Qualidade
-- 35 - Equipe Unitop

-- GRUPOS DO SISTEMA ANTIGO A SEREM IMPORTADOS:
-- Os IDs serão sequenciais a partir do próximo disponível

-- Verificar próximo ID disponível
-- SELECT MAX(id) + 1 FROM roles;

BEGIN;

-- ============================================================================
-- ROLES PRINCIPAIS (Módulos Funcionais)
-- ============================================================================

-- Abastecimento (ID antigo: 3)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (36, 'Equipe Abastecimento', 'web', NOW(), NOW());

-- Estoque (ID antigo: 5)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (37, 'Equipe Estoque', 'web', NOW(), NOW());

-- Gestão de Jornada (ID antigo: 6)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (38, 'Equipe Gestão de Jornada', 'web', NOW(), NOW());

-- Gestão de Telemetria (ID antigo: 7)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (39, 'Equipe Gestão de Telemetria', 'web', NOW(), NOW());

-- Manutenção (ID antigo: 8)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (40, 'Equipe Manutenção', 'web', NOW(), NOW());

-- Pessoas & Fornecedores (ID antigo: 9)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (41, 'Equipe Pessoas e Fornecedores', 'web', NOW(), NOW());

-- Pneus (ID antigo: 10)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (42, 'Equipe Pneus', 'web', NOW(), NOW());

-- Sinistro (ID antigo: 11)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (43, 'Equipe Sinistros', 'web', NOW(), NOW());

-- Veículo (ID antigo: 12)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (44, 'Equipe Veículos', 'web', NOW(), NOW());

-- Configurações (ID antigo: 13) - Talvez seja Admin
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (45, 'Equipe Configurações', 'web', NOW(), NOW());

-- Gestão de Viagem (ID antigo: 15)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (46, 'Equipe Gestão de Viagem', 'web', NOW(), NOW());

-- Relatórios Gerenciais (ID antigo: 16)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (47, 'Equipe Relatórios Gerenciais', 'web', NOW(), NOW());

-- Motoristas (ID antigo: 17)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (48, 'Equipe Motoristas', 'web', NOW(), NOW());

-- Pedágio (ID antigo: 18)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (49, 'Equipe Pedágio', 'web', NOW(), NOW());

-- Imobilizados (ID antigo: 19)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (50, 'Equipe Imobilizados', 'web', NOW(), NOW());

-- Financeiro (ID antigo: 21)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (51, 'Equipe Financeiro', 'web', NOW(), NOW());

-- Portaria (ID antigo: 22)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (52, 'Equipe Portaria', 'web', NOW(), NOW());

-- ============================================================================
-- ROLES OPERACIONAIS/ESPECÍFICAS
-- ============================================================================

-- Grupo Porteiros (ID antigo: 23)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (53, 'Porteiros', 'web', NOW(), NOW());

-- Pessoal Noite (ID antigo: 25)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (54, 'Pessoal Noite', 'web', NOW(), NOW());

-- Saída de Veículos (ID antigo: 28)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (55, 'Saída de Veículos', 'web', NOW(), NOW());

-- Estoque TI (ID antigo: 29)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (56, 'Estoque TI', 'web', NOW(), NOW());

-- Prêmio Superação (ID antigo: 30)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (57, 'Prêmio Superação', 'web', NOW(), NOW());

-- Prêmio Carvalima (ID antigo: 32)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (58, 'Prêmio Carvalima', 'web', NOW(), NOW());

-- Inventário Pneus (ID antigo: 33)
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (59, 'Inventário Pneus', 'web', NOW(), NOW());

-- ============================================================================
-- ROLES ADMINISTRATIVAS
-- ============================================================================

-- Admin (ID antigo: 1) - Provavelmente será superuser
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (60, 'Administrador Geral', 'web', NOW(), NOW());

-- Standard (ID antigo: 2) - Usuário padrão com acesso básico
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES (61, 'Usuário Padrão', 'web', NOW(), NOW());

-- ============================================================================
-- ATUALIZAR SEQUENCE
-- ============================================================================
-- Atualizar a sequence para o próximo ID disponível
SELECT setval('roles_id_seq', 61, true);

COMMIT;

-- ============================================================================
-- VERIFICAÇÃO
-- ============================================================================
-- Verificar roles criadas
-- SELECT id, name FROM roles WHERE id >= 36 ORDER BY id;

-- ============================================================================
-- NOTAS IMPORTANTES
-- ============================================================================
-- 1. Grupos de "Testes" e "Testes Unitop" não foram importados (temporários)
-- 2. "Vencimentário" foi removido do sistema (mencionado pelo usuário)
-- 3. Compras e Solicitações já foram migradas com roles elaboradas
-- 4. Após importar, será necessário atribuir permissões a cada role
-- 5. Verificar com o cliente quais roles realmente são necessárias
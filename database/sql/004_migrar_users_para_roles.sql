-- ============================================================================
-- SCRIPT: Migração de Users para Roles (Sistema Antigo → Laravel)
-- DESCRIÇÃO: Vincula usuários às roles corretas baseado nos groups do sistema antigo
-- IMPORTANTE: Rodar no DBeaver após executar o script 001 (criação de roles)
-- DATA: 2025-10-07
-- ============================================================================
-- INSTRUÇÃO: Execute todo o script de uma vez no DBeaver
-- Em caso de erro, execute ROLLBACK; manualmente
-- ============================================================================

BEGIN;

-- ============================================================================
-- MAPEAMENTO DE GROUPS ANTIGOS → ROLES NOVOS
-- ============================================================================
-- Sistema Antigo (permissions_mad_builder.sql) → Sistema Novo (roles)
--
-- GROUPS ANTIGOS:
--  1 - Admin                       → 60 - Administrador Geral
--  2 - Standard                    → 61 - Usuário Padrão
--  3 - Abastecimento               → 36 - Equipe Abastecimento
--  5 - Estoque                     → 37 - Equipe Estoque
--  6 - Gestão de Jornada           → 38 - Equipe Gestão de Jornada
--  7 - Gestão de Telemetria        → 39 - Equipe Gestão de Telemetria
--  8 - Manutenção                  → 40 - Equipe Manutenção
--  9 - Pessoas & Fornecedores      → 41 - Equipe Pessoas e Fornecedores
-- 10 - Pneus                       → 42 - Equipe Pneus
-- 11 - Sinistro                    → 43 - Equipe Sinistros
-- 12 - Veículo                     → 44 - Equipe Veículos
-- 13 - Configurações               → 45 - Equipe Configurações
-- 15 - Gestão de Viagem            → 46 - Equipe Gestão de Viagem
-- 16 - Relatórios Gerenciais       → 47 - Equipe Relatórios Gerenciais
-- 17 - Motoristas                  → 48 - Equipe Motoristas
-- 18 - Pedágio                     → 49 - Equipe Pedágio
-- 19 - Imobilizados                → 50 - Equipe Imobilizados
-- 21 - Financeiro                  → 51 - Equipe Financeiro
-- 22 - Portaria                    → 52 - Equipe Portaria
-- 23 - Grupo Porteiros             → 53 - Porteiros
-- 25 - Pessoal Noite               → 54 - Pessoal Noite
-- 28 - Saída de Veículos           → 55 - Saída de Veículos
-- 29 - Estoque TI                  → 56 - Estoque TI
-- 30 - Prêmio Superação            → 57 - Prêmio Superação
-- 32 - Prêmio Carvalima            → 58 - Prêmio Carvalima
-- 33 - Inventário Pneus            → 59 - Inventário Pneus
--
-- GROUPS ANTIGOS NÃO MIGRADOS:
--  4 - Compras                     → JÁ EXISTE (roles 1-11, 33)
-- 14 - Solicitações                → JÁ EXISTE (roles 1-11, 33)
-- 20 - Vencimentário               → REMOVIDO DO SISTEMA
-- 26 - Testes                      → TEMPORÁRIO (NÃO MIGRAR)
-- 27 - Testes Unitop               → TEMPORÁRIO (NÃO MIGRAR)
-- 31 - Compras Aprov/Valid         → JÁ EXISTE (roles de aprovação)
-- ============================================================================

-- ============================================================================
-- TABELA TEMPORÁRIA: Mapeamento Group → Role
-- ============================================================================
CREATE TEMP TABLE temp_group_role_mapping (
    old_group_id INT,
    old_group_name VARCHAR(100),
    new_role_id INT,
    new_role_name VARCHAR(100)
);

-- Inserir mapeamentos
INSERT INTO temp_group_role_mapping (old_group_id, old_group_name, new_role_id, new_role_name) VALUES
(1, 'Admin', 60, 'Administrador Geral'),
(2, 'Standard', 61, 'Usuário Padrão'),
(3, 'Abastecimento', 36, 'Equipe Abastecimento'),
(5, 'Estoque', 37, 'Equipe Estoque'),
(6, 'Gestão de Jornada', 38, 'Equipe Gestão de Jornada'),
(7, 'Gestão de Telemetria', 39, 'Equipe Gestão de Telemetria'),
(8, 'Manutenção', 40, 'Equipe Manutenção'),
(9, 'Pessoas & Fornecedores', 41, 'Equipe Pessoas e Fornecedores'),
(10, 'Pneus', 42, 'Equipe Pneus'),
(11, 'Sinistro', 43, 'Equipe Sinistros'),
(12, 'Veículo', 44, 'Equipe Veículos'),
(13, 'Configurações', 45, 'Equipe Configurações'),
(15, 'Gestão de Viagem', 46, 'Equipe Gestão de Viagem'),
(16, 'Relatórios Gerenciais', 47, 'Equipe Relatórios Gerenciais'),
(17, 'Motoristas', 48, 'Equipe Motoristas'),
(18, 'Pedágio', 49, 'Equipe Pedágio'),
(19, 'Imobilizados', 50, 'Equipe Imobilizados'),
(21, 'Financeiro', 51, 'Equipe Financeiro'),
(22, 'Portaria', 52, 'Equipe Portaria'),
(23, 'Grupo Porteiros', 53, 'Porteiros'),
(25, 'Pessoal Noite', 54, 'Pessoal Noite'),
(28, 'Saída de Veículos', 55, 'Saída de Veículos'),
(29, 'Estoque TI', 56, 'Estoque TI'),
(30, 'Prêmio Superação', 57, 'Prêmio Superação'),
(32, 'Prêmio Carvalima', 58, 'Prêmio Carvalima'),
(33, 'Inventário Pneus', 59, 'Inventário Pneus');

-- ============================================================================
-- TABELA TEMPORÁRIA: Relacionamento System_User → User (via matrícula)
-- ============================================================================
CREATE TEMP TABLE temp_system_user_to_user AS
SELECT
    su.id AS old_user_id,
    su.name AS old_user_name,
    su.login AS old_matricula,
    u.id AS new_user_id,
    u.name AS new_user_name,
    u.matricula AS new_matricula
FROM (
    -- Dados do sistema antigo (simulando a tabela system_users)
    -- Vou usar os dados que vimos no permissions_mad_builder.sql
    SELECT
        202 AS id, 'Bot CVL' AS name, '1234' AS login
    UNION ALL SELECT 213, 'Carlos Eduardo Silva', '7087'
    UNION ALL SELECT 101, 'Juliano Marzani', '6083'
    UNION ALL SELECT 214, 'Rebeca Pierre de Souza', '5957'
    UNION ALL SELECT 216, 'Olga Mustafá Marques (clone)', '6235'
    UNION ALL SELECT 224, 'David dos Reis Pereira', '7271'
    UNION ALL SELECT 240, 'Leonardo Ivan Bastos Souza', '7424'
    UNION ALL SELECT 234, 'Giovanna Cristina Bueno Inocêncio', '6262'
    UNION ALL SELECT 54, 'Frederico Bathazar Petsch', '4704'
    UNION ALL SELECT 7, 'Usuario Padrao Estoque', 'Estoque'
    UNION ALL SELECT 238, 'Ivan Araujo Paiva', '6928'
    UNION ALL SELECT 241, 'Jonathan da Silva Oliveira', '6905'
    UNION ALL SELECT 275, 'KIOSHI SERVICOS DE MANUTENCAO E REPARACAO MECANICA', '2132'
    UNION ALL SELECT 154, 'Danilo Augusto de Souza Gomes', '6903'
    UNION ALL SELECT 269, 'Antonio Lucas Freitas Araujo', '7563'
    UNION ALL SELECT 230, 'Marcelo Bof Matheus', '7425'
    UNION ALL SELECT 228, 'Wender Renato Antonio Martins', '2964'
    UNION ALL SELECT 3, 'Olga Mustafa Marques', '6235'
    -- Adicione mais conforme necessário (lista limitada para exemplo)
) su
LEFT JOIN users u ON u.matricula::TEXT = su.login;

-- ============================================================================
-- TABELA TEMPORÁRIA: Relacionamentos User → Group → Role
-- ============================================================================
CREATE TEMP TABLE temp_user_group_role AS
SELECT DISTINCT
    sug.system_user_id AS old_user_id,
    sug.system_group_id AS old_group_id,
    tmap.new_role_id,
    tsuu.new_user_id,
    tsuu.new_user_name,
    tmap.new_role_name
FROM (
    -- Dados do sistema antigo (system_user_group) extraídos do permissions_mad_builder.sql
    SELECT 405 AS system_user_id, 2 AS system_group_id
    UNION ALL SELECT 405, 29
    UNION ALL SELECT 31, 2
    UNION ALL SELECT 202, 5
    UNION ALL SELECT 108, 2
    UNION ALL SELECT 31, 6
    UNION ALL SELECT 472, 2
    UNION ALL SELECT 91, 2
    UNION ALL SELECT 149, 2
    UNION ALL SELECT 472, 3
    UNION ALL SELECT 472, 15
    UNION ALL SELECT 91, 3
    UNION ALL SELECT 7, 2
    UNION ALL SELECT 31, 7
    UNION ALL SELECT 151, 2
    UNION ALL SELECT 216, 5
    UNION ALL SELECT 204, 5
    UNION ALL SELECT 54, 5
    UNION ALL SELECT 187, 2
    UNION ALL SELECT 187, 3
    UNION ALL SELECT 362, 2
    UNION ALL SELECT 31, 15
    UNION ALL SELECT 275, 2
    UNION ALL SELECT 74, 2
    UNION ALL SELECT 368, 2
    UNION ALL SELECT 46, 5
    UNION ALL SELECT 368, 10
    UNION ALL SELECT 31, 29
    UNION ALL SELECT 154, 5
    UNION ALL SELECT 203, 2
    UNION ALL SELECT 203, 3
    UNION ALL SELECT 182, 2
    UNION ALL SELECT 269, 5
    -- Adicione mais conforme necessário
) sug
INNER JOIN temp_group_role_mapping tmap ON tmap.old_group_id = sug.system_group_id
INNER JOIN temp_system_user_to_user tsuu ON tsuu.old_user_id = sug.system_user_id
WHERE tsuu.new_user_id IS NOT NULL;  -- Apenas users que foram migrados

-- ============================================================================
-- INSERÇÃO NA TABELA model_has_roles (Spatie Permission)
-- ============================================================================

-- Inserir relacionamentos User → Role
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT DISTINCT
    tugr.new_role_id,
    'App\Models\User' AS model_type,
    tugr.new_user_id
FROM temp_user_group_role tugr
WHERE NOT EXISTS (
    -- Evitar duplicatas
    SELECT 1
    FROM model_has_roles mhr
    WHERE mhr.role_id = tugr.new_role_id
      AND mhr.model_type = 'App\Models\User'
      AND mhr.model_id = tugr.new_user_id
);

-- ============================================================================
-- COMMIT DA TRANSAÇÃO
-- ============================================================================

COMMIT;

-- ============================================================================
-- RELATÓRIO DE MIGRAÇÃO
-- ============================================================================

-- Verificar quantos usuários foram migrados
SELECT
    'Total de usuários migrados' AS descricao,
    COUNT(DISTINCT new_user_id) AS quantidade
FROM temp_user_group_role;

-- Verificar quantas roles foram atribuídas
SELECT
    'Total de relacionamentos user→role criados' AS descricao,
    COUNT(*) AS quantidade
FROM model_has_roles
WHERE model_type = 'App\Models\User';

-- Distribuição de usuários por role
SELECT
    r.name AS role_name,
    COUNT(DISTINCT mhr.model_id) AS total_usuarios
FROM model_has_roles mhr
INNER JOIN roles r ON r.id = mhr.role_id
WHERE mhr.model_type = 'App\Models\User'
  AND r.id >= 36  -- Apenas roles novas
GROUP BY r.id, r.name
ORDER BY r.id;

-- ============================================================================
-- ROLLBACK MANUAL (usar apenas em caso de erro)
-- ============================================================================
-- Se algo der errado, execute: ROLLBACK;

-- ============================================================================
-- NOTAS IMPORTANTES
-- ============================================================================
-- 1. Este script usa dados de EXEMPLO extraídos do permissions_mad_builder.sql
-- 2. Para migração COMPLETA, você precisa extrair TODOS os dados de:
--    - system_users (tabela completa)
--    - system_user_group (tabela completa)
-- 3. Após executar, limpar cache: php artisan permission:cache-reset
-- 4. Usuários com group_id 1 (Admin) devem ter is_superuser = true
-- 5. Verificar manualmente se os mapeamentos estão corretos
-- ============================================================================
-- =========================================
-- ANÁLISE COMPLETA DE PERMISSÕES
-- Mad Builder → Laravel (Sistema Individual)
-- =========================================
-- Este script analisa:
-- 1. Usuários migrados vs não migrados
-- 2. Permissões diretas vs por grupo
-- 3. Permissões órfãs (sem correspondência no Laravel)
-- 4. Mapeamento de controllers para permissões Laravel
-- =========================================

BEGIN;

-- Habilitar dblink
CREATE EXTENSION IF NOT EXISTS dblink;

-- =========================================
-- TABELAS TEMPORÁRIAS
-- =========================================

-- Mapeamento de usuários
DROP TABLE IF EXISTS temp_user_mapping;
CREATE TEMP TABLE temp_user_mapping (
    system_user_id INTEGER,
    system_user_name TEXT,
    system_user_email TEXT,
    system_user_login TEXT,
    laravel_user_id INTEGER,
    encontrado BOOLEAN DEFAULT FALSE,
    ativo_sistema_antigo CHAR(1)
);

-- Programas/Permissões por usuário (diretas + grupos)
DROP TABLE IF EXISTS temp_user_all_programs;
CREATE TEMP TABLE temp_user_all_programs (
    system_user_id INTEGER,
    system_program_id INTEGER,
    program_name TEXT,
    program_controller TEXT,
    origem TEXT, -- 'direto' ou 'grupo:{group_id}:{group_name}'
    group_name TEXT
);

-- Mapeamento controller → permissão Laravel
DROP TABLE IF EXISTS temp_controller_permission_map;
CREATE TEMP TABLE temp_controller_permission_map (
    system_program_id INTEGER,
    program_name TEXT,
    program_controller TEXT,
    controller_slug TEXT,
    permission_ver_name TEXT,
    permission_criar_name TEXT,
    permission_ver_exists BOOLEAN DEFAULT FALSE,
    permission_criar_exists BOOLEAN DEFAULT FALSE,
    permission_ver_id INTEGER,
    permission_criar_id INTEGER
);

-- =========================================
-- ETAPA 1: MAPEAR TODOS OS USUÁRIOS
-- =========================================
INSERT INTO temp_user_mapping (system_user_id, system_user_name, system_user_email, system_user_login, ativo_sistema_antigo)
SELECT
    id,
    name,
    email,
    login,
    active
FROM dblink(
    'hostaddr=10.10.1.14 port=5432 dbname=base_unitop_permission_carvalima user=postgres password=SisDBA2@2l',
    'SELECT id, name, email, login, active FROM system_users ORDER BY id'
) AS t(id INTEGER, name TEXT, email TEXT, login TEXT, active CHAR(1));

-- Mapear por email
UPDATE temp_user_mapping tum
SET
    laravel_user_id = u.id,
    encontrado = TRUE
FROM users u
WHERE LOWER(TRIM(tum.system_user_email)) = LOWER(TRIM(u.email))
AND tum.system_user_email IS NOT NULL
AND tum.system_user_email != ''
AND u.deleted_at IS NULL;

-- Mapear por nome (somente se não encontrou por email)
UPDATE temp_user_mapping tum
SET
    laravel_user_id = u.id,
    encontrado = TRUE
FROM users u
WHERE LOWER(TRIM(tum.system_user_name)) = LOWER(TRIM(u.name))
AND tum.encontrado = FALSE
AND u.deleted_at IS NULL;

-- =========================================
-- ETAPA 2: COLETAR PERMISSÕES DIRETAS
-- =========================================
INSERT INTO temp_user_all_programs (system_user_id, system_program_id, program_name, program_controller, origem, group_name)
SELECT DISTINCT
    t.system_user_id,
    t.system_program_id,
    t.program_name,
    t.program_controller,
    'direto',
    NULL
FROM dblink(
    'hostaddr=10.10.1.14 port=5432 dbname=base_unitop_permission_carvalima user=postgres password=SisDBA2@2l',
    'SELECT
        sup.system_user_id,
        sup.system_program_id,
        sp.name as program_name,
        sp.controller as program_controller
    FROM system_user_program sup
    INNER JOIN system_program sp ON sp.id = sup.system_program_id'
) AS t(system_user_id INTEGER, system_program_id INTEGER, program_name TEXT, program_controller TEXT)
WHERE EXISTS (SELECT 1 FROM temp_user_mapping tum WHERE tum.system_user_id = t.system_user_id);

-- =========================================
-- ETAPA 3: COLETAR PERMISSÕES VIA GRUPOS
-- =========================================
INSERT INTO temp_user_all_programs (system_user_id, system_program_id, program_name, program_controller, origem, group_name)
SELECT DISTINCT
    t.system_user_id,
    t.system_program_id,
    t.program_name,
    t.program_controller,
    'grupo:' || t.system_group_id || ':' || t.group_name,
    t.group_name
FROM dblink(
    'hostaddr=10.10.1.14 port=5432 dbname=base_unitop_permission_carvalima user=postgres password=SisDBA2@2l',
    'SELECT
        sug.system_user_id,
        sgp.system_program_id,
        sp.name as program_name,
        sp.controller as program_controller,
        sg.id as system_group_id,
        sg.name as group_name
    FROM system_user_group sug
    INNER JOIN system_group_program sgp ON sgp.system_group_id = sug.system_group_id
    INNER JOIN system_program sp ON sp.id = sgp.system_program_id
    INNER JOIN system_group sg ON sg.id = sug.system_group_id'
) AS t(system_user_id INTEGER, system_program_id INTEGER, program_name TEXT, program_controller TEXT, system_group_id INTEGER, group_name TEXT)
WHERE EXISTS (SELECT 1 FROM temp_user_mapping tum WHERE tum.system_user_id = t.system_user_id)
AND NOT EXISTS (
    SELECT 1 FROM temp_user_all_programs tup2
    WHERE tup2.system_user_id = t.system_user_id
    AND tup2.system_program_id = t.system_program_id
    AND tup2.origem = 'direto'
);

-- =========================================
-- ETAPA 4: MAPEAR CONTROLLERS → PERMISSÕES
-- =========================================
INSERT INTO temp_controller_permission_map (system_program_id, program_name, program_controller, controller_slug, permission_ver_name, permission_criar_name)
SELECT DISTINCT
    tup.system_program_id,
    tup.program_name,
    tup.program_controller,
    LOWER(
        REGEXP_REPLACE(
            REGEXP_REPLACE(tup.program_controller, '(Form|List|Header|Report|Document|Dashboard|View|Card).*$', ''),
            '([A-Z])', '_\1', 'g'
        )
    ) AS controller_slug,
    'ver_' || LOWER(
        REGEXP_REPLACE(
            REGEXP_REPLACE(tup.program_controller, '(Form|List|Header|Report|Document|Dashboard|View|Card).*$', ''),
            '([A-Z])', '_\1', 'g'
        )
    ) AS permission_ver_name,
    'criar_' || LOWER(
        REGEXP_REPLACE(
            REGEXP_REPLACE(tup.program_controller, '(Form|List|Header|Report|Document|Dashboard|View|Card).*$', ''),
            '([A-Z])', '_\1', 'g'
        )
    ) AS permission_criar_name
FROM temp_user_all_programs tup
WHERE tup.program_controller IS NOT NULL
AND tup.program_controller != '';

-- Limpar underscores duplicados
UPDATE temp_controller_permission_map
SET
    controller_slug = TRIM(BOTH '_' FROM REGEXP_REPLACE(controller_slug, '_+', '_', 'g')),
    permission_ver_name = TRIM(BOTH '_' FROM REGEXP_REPLACE(permission_ver_name, '_+', '_', 'g')),
    permission_criar_name = TRIM(BOTH '_' FROM REGEXP_REPLACE(permission_criar_name, '_+', '_', 'g'));

-- Verificar se permissões existem no Laravel
UPDATE temp_controller_permission_map tcm
SET
    permission_ver_exists = TRUE,
    permission_ver_id = p.id
FROM permissions p
WHERE p.name = tcm.permission_ver_name;

UPDATE temp_controller_permission_map tcm
SET
    permission_criar_exists = TRUE,
    permission_criar_id = p.id
FROM permissions p
WHERE p.name = tcm.permission_criar_name;

-- =========================================
-- RELATÓRIOS DE ANÁLISE
-- =========================================

SELECT '=========================================' AS relatorio;
SELECT 'RELATÓRIO 1: RESUMO GERAL' AS relatorio;
SELECT '=========================================' AS relatorio;

SELECT
    COUNT(*) as total_usuarios_sistema_antigo,
    SUM(CASE WHEN ativo_sistema_antigo = 'Y' THEN 1 ELSE 0 END) as usuarios_ativos,
    SUM(CASE WHEN ativo_sistema_antigo = 'N' THEN 1 ELSE 0 END) as usuarios_inativos,
    SUM(CASE WHEN encontrado = TRUE THEN 1 ELSE 0 END) as usuarios_migrados,
    SUM(CASE WHEN encontrado = FALSE AND ativo_sistema_antigo = 'Y' THEN 1 ELSE 0 END) as usuarios_ativos_nao_migrados,
    SUM(CASE WHEN encontrado = FALSE AND ativo_sistema_antigo = 'N' THEN 1 ELSE 0 END) as usuarios_inativos_nao_migrados
FROM temp_user_mapping;

SELECT '=========================================' AS relatorio;
SELECT 'RELATÓRIO 2: USUÁRIOS ATIVOS NÃO MIGRADOS' AS relatorio;
SELECT '=========================================' AS relatorio;

SELECT
    system_user_id,
    system_user_name,
    system_user_email,
    system_user_login
FROM temp_user_mapping
WHERE encontrado = FALSE
AND ativo_sistema_antigo = 'Y'
ORDER BY system_user_name;

SELECT '=========================================' AS relatorio;
SELECT 'RELATÓRIO 3: ESTATÍSTICAS DE PERMISSÕES' AS relatorio;
SELECT '=========================================' AS relatorio;

SELECT
    COUNT(DISTINCT system_program_id) as total_programas_unicos,
    COUNT(*) as total_atribuicoes_permissoes,
    SUM(CASE WHEN origem = 'direto' THEN 1 ELSE 0 END) as permissoes_diretas,
    SUM(CASE WHEN origem LIKE 'grupo:%' THEN 1 ELSE 0 END) as permissoes_via_grupo
FROM temp_user_all_programs tup
INNER JOIN temp_user_mapping tum ON tum.system_user_id = tup.system_user_id
WHERE tum.encontrado = TRUE
AND tum.ativo_sistema_antigo = 'Y';

SELECT '=========================================' AS relatorio;
SELECT 'RELATÓRIO 4: PERMISSÕES ÓRFÃS (SEM CORRESPONDÊNCIA NO LARAVEL)' AS relatorio;
SELECT '=========================================' AS relatorio;

SELECT
    program_controller,
    program_name,
    permission_ver_name,
    permission_criar_name,
    COUNT(DISTINCT tup.system_user_id) as usuarios_afetados
FROM temp_controller_permission_map tcm
INNER JOIN temp_user_all_programs tup ON tup.system_program_id = tcm.system_program_id
INNER JOIN temp_user_mapping tum ON tum.system_user_id = tup.system_user_id
WHERE tum.encontrado = TRUE
AND tum.ativo_sistema_antigo = 'Y'
AND (tcm.permission_ver_exists = FALSE OR tcm.permission_criar_exists = FALSE)
GROUP BY program_controller, program_name, permission_ver_name, permission_criar_name, permission_ver_exists, permission_criar_exists
ORDER BY usuarios_afetados DESC, program_controller;

SELECT '=========================================' AS relatorio;
SELECT 'RELATÓRIO 5: TOP 20 USUÁRIOS COM MAIS PERMISSÕES' AS relatorio;
SELECT '=========================================' AS relatorio;

SELECT
    tum.system_user_name,
    tum.system_user_email,
    COUNT(DISTINCT tup.system_program_id) as total_permissoes,
    SUM(CASE WHEN tup.origem = 'direto' THEN 1 ELSE 0 END) as diretas,
    SUM(CASE WHEN tup.origem LIKE 'grupo:%' THEN 1 ELSE 0 END) as via_grupo
FROM temp_user_mapping tum
INNER JOIN temp_user_all_programs tup ON tup.system_user_id = tum.system_user_id
WHERE tum.encontrado = TRUE
AND tum.ativo_sistema_antigo = 'Y'
GROUP BY tum.system_user_id, tum.system_user_name, tum.system_user_email
ORDER BY total_permissoes DESC
LIMIT 20;

SELECT '=========================================' AS relatorio;
SELECT 'RELATÓRIO 6: DISTRIBUIÇÃO POR GRUPO' AS relatorio;
SELECT '=========================================' AS relatorio;

SELECT
    tup.group_name,
    COUNT(DISTINCT tup.system_user_id) as usuarios_no_grupo,
    COUNT(DISTINCT tup.system_program_id) as permissoes_unicas
FROM temp_user_all_programs tup
INNER JOIN temp_user_mapping tum ON tum.system_user_id = tup.system_user_id
WHERE tup.origem LIKE 'grupo:%'
AND tum.encontrado = TRUE
AND tum.ativo_sistema_antigo = 'Y'
GROUP BY tup.group_name
ORDER BY usuarios_no_grupo DESC;

SELECT '=========================================' AS relatorio;
SELECT 'RELATÓRIO 7: VALIDAÇÃO DE MAPEAMENTO' AS relatorio;
SELECT '=========================================' AS relatorio;

SELECT
    COUNT(DISTINCT tcm.system_program_id) as total_controllers,
    SUM(CASE WHEN tcm.permission_ver_exists THEN 1 ELSE 0 END) as permissoes_ver_existem,
    SUM(CASE WHEN tcm.permission_criar_exists THEN 1 ELSE 0 END) as permissoes_criar_existem,
    SUM(CASE WHEN tcm.permission_ver_exists AND tcm.permission_criar_exists THEN 1 ELSE 0 END) as ambas_existem,
    SUM(CASE WHEN NOT tcm.permission_ver_exists AND NOT tcm.permission_criar_exists THEN 1 ELSE 0 END) as nenhuma_existe
FROM temp_controller_permission_map tcm;

ROLLBACK;
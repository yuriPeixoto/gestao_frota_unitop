-- =========================================
-- MIGRAÇÃO DE PERMISSÕES INDIVIDUAIS
-- Mad Builder → Laravel (Sistema Individual)
-- =========================================
-- ATENÇÃO: Este script migra TODAS as permissões
-- (diretas + via grupo) para permissões INDIVIDUAIS
-- no Laravel. NÃO haverá mais permissões por grupo.
-- =========================================
-- IMPORTANTE:
-- 1. Execute primeiro o script de ANÁLISE
-- 2. Revise todos os relatórios
-- 3. Troque ROLLBACK por COMMIT ao final
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
    origem TEXT,
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

-- Log de migração
DROP TABLE IF EXISTS temp_migration_log;
CREATE TEMP TABLE temp_migration_log (
    user_id INTEGER,
    user_name TEXT,
    user_email TEXT,
    permission_id INTEGER,
    permission_name TEXT,
    permission_type TEXT, -- 'ver' ou 'criar'
    origem TEXT, -- 'direto' ou 'grupo:...'
    action TEXT, -- 'INSERT' ou 'SKIP_EXISTS' ou 'SKIP_NO_PERMISSION'
    timestamp TIMESTAMP DEFAULT NOW()
);

-- =========================================
-- ETAPA 1: MAPEAR USUÁRIOS ATIVOS
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
    'SELECT id, name, email, login, active FROM system_users WHERE active = ''Y'''
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

-- Mapear por nome
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
INNER JOIN temp_user_mapping tum ON tum.system_user_id = t.system_user_id
WHERE tum.encontrado = TRUE;

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
INNER JOIN temp_user_mapping tum ON tum.system_user_id = t.system_user_id
WHERE tum.encontrado = TRUE
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
-- ETAPA 5: MIGRAR PERMISSÕES VER
-- =========================================
INSERT INTO model_has_permissions (permission_id, model_type, model_id)
SELECT DISTINCT
    tcm.permission_ver_id,
    'App\Models\User',
    tum.laravel_user_id
FROM temp_user_mapping tum
INNER JOIN temp_user_all_programs tup ON tup.system_user_id = tum.system_user_id
INNER JOIN temp_controller_permission_map tcm ON tcm.system_program_id = tup.system_program_id
WHERE tum.encontrado = TRUE
AND tcm.permission_ver_exists = TRUE
AND NOT EXISTS (
    SELECT 1 FROM model_has_permissions mhp
    WHERE mhp.permission_id = tcm.permission_ver_id
    AND mhp.model_id = tum.laravel_user_id
    AND mhp.model_type = 'App\Models\User'
)
ON CONFLICT (permission_id, model_id, model_type) DO NOTHING;

-- Log inserções VER
INSERT INTO temp_migration_log (user_id, user_name, user_email, permission_id, permission_name, permission_type, origem, action)
SELECT DISTINCT
    tum.laravel_user_id,
    tum.system_user_name,
    tum.system_user_email,
    tcm.permission_ver_id,
    tcm.permission_ver_name,
    'ver',
    tup.origem,
    CASE
        WHEN EXISTS (
            SELECT 1 FROM model_has_permissions mhp
            WHERE mhp.permission_id = tcm.permission_ver_id
            AND mhp.model_id = tum.laravel_user_id
            AND mhp.model_type = 'App\Models\User'
        ) THEN 'SKIP_EXISTS'
        ELSE 'INSERT'
    END
FROM temp_user_mapping tum
INNER JOIN temp_user_all_programs tup ON tup.system_user_id = tum.system_user_id
INNER JOIN temp_controller_permission_map tcm ON tcm.system_program_id = tup.system_program_id
WHERE tum.encontrado = TRUE
AND tcm.permission_ver_exists = TRUE;

-- Log permissões VER que não existem
INSERT INTO temp_migration_log (user_id, user_name, user_email, permission_id, permission_name, permission_type, origem, action)
SELECT DISTINCT
    tum.laravel_user_id,
    tum.system_user_name,
    tum.system_user_email,
    NULL::INTEGER,
    tcm.permission_ver_name,
    'ver',
    tup.origem,
    'SKIP_NO_PERMISSION'
FROM temp_user_mapping tum
INNER JOIN temp_user_all_programs tup ON tup.system_user_id = tum.system_user_id
INNER JOIN temp_controller_permission_map tcm ON tcm.system_program_id = tup.system_program_id
WHERE tum.encontrado = TRUE
AND tcm.permission_ver_exists = FALSE;

-- =========================================
-- ETAPA 6: MIGRAR PERMISSÕES CRIAR
-- =========================================
INSERT INTO model_has_permissions (permission_id, model_type, model_id)
SELECT DISTINCT
    tcm.permission_criar_id,
    'App\Models\User',
    tum.laravel_user_id
FROM temp_user_mapping tum
INNER JOIN temp_user_all_programs tup ON tup.system_user_id = tum.system_user_id
INNER JOIN temp_controller_permission_map tcm ON tcm.system_program_id = tup.system_program_id
WHERE tum.encontrado = TRUE
AND tcm.permission_criar_exists = TRUE
AND NOT EXISTS (
    SELECT 1 FROM model_has_permissions mhp
    WHERE mhp.permission_id = tcm.permission_criar_id
    AND mhp.model_id = tum.laravel_user_id
    AND mhp.model_type = 'App\Models\User'
)
ON CONFLICT (permission_id, model_id, model_type) DO NOTHING;

-- Log inserções CRIAR
INSERT INTO temp_migration_log (user_id, user_name, user_email, permission_id, permission_name, permission_type, origem, action)
SELECT DISTINCT
    tum.laravel_user_id,
    tum.system_user_name,
    tum.system_user_email,
    tcm.permission_criar_id,
    tcm.permission_criar_name,
    'criar',
    tup.origem,
    CASE
        WHEN EXISTS (
            SELECT 1 FROM model_has_permissions mhp
            WHERE mhp.permission_id = tcm.permission_criar_id
            AND mhp.model_id = tum.laravel_user_id
            AND mhp.model_type = 'App\Models\User'
        ) THEN 'SKIP_EXISTS'
        ELSE 'INSERT'
    END
FROM temp_user_mapping tum
INNER JOIN temp_user_all_programs tup ON tup.system_user_id = tum.system_user_id
INNER JOIN temp_controller_permission_map tcm ON tcm.system_program_id = tup.system_program_id
WHERE tum.encontrado = TRUE
AND tcm.permission_criar_exists = TRUE;

-- Log permissões CRIAR que não existem
INSERT INTO temp_migration_log (user_id, user_name, user_email, permission_id, permission_name, permission_type, origem, action)
SELECT DISTINCT
    tum.laravel_user_id,
    tum.system_user_name,
    tum.system_user_email,
    NULL::INTEGER,
    tcm.permission_criar_name,
    'criar',
    tup.origem,
    'SKIP_NO_PERMISSION'
FROM temp_user_mapping tum
INNER JOIN temp_user_all_programs tup ON tup.system_user_id = tum.system_user_id
INNER JOIN temp_controller_permission_map tcm ON tcm.system_program_id = tup.system_program_id
WHERE tum.encontrado = TRUE
AND tcm.permission_criar_exists = FALSE;

-- =========================================
-- RELATÓRIOS FINAIS
-- =========================================
SELECT '=========================================' AS relatorio;
SELECT 'MIGRAÇÃO CONCLUÍDA - RELATÓRIO FINAL' AS relatorio;
SELECT '=========================================' AS relatorio;

SELECT '--- RESUMO GERAL ---' AS secao;
SELECT
    COUNT(DISTINCT user_id) as usuarios_migrados,
    COUNT(*) as total_registros_log,
    SUM(CASE WHEN action = 'INSERT' THEN 1 ELSE 0 END) as permissoes_inseridas,
    SUM(CASE WHEN action = 'SKIP_EXISTS' THEN 1 ELSE 0 END) as ja_existiam,
    SUM(CASE WHEN action = 'SKIP_NO_PERMISSION' THEN 1 ELSE 0 END) as permissoes_orfas
FROM temp_migration_log;

SELECT '--- POR TIPO DE PERMISSÃO ---' AS secao;
SELECT
    permission_type,
    COUNT(*) as total,
    SUM(CASE WHEN action = 'INSERT' THEN 1 ELSE 0 END) as inseridas,
    SUM(CASE WHEN action = 'SKIP_NO_PERMISSION' THEN 1 ELSE 0 END) as orfas
FROM temp_migration_log
GROUP BY permission_type;

SELECT '--- POR ORIGEM ---' AS secao;
SELECT
    CASE
        WHEN origem = 'direto' THEN 'Permissão Direta'
        WHEN origem LIKE 'grupo:%' THEN 'Via Grupo'
        ELSE origem
    END as tipo_origem,
    COUNT(*) as total,
    SUM(CASE WHEN action = 'INSERT' THEN 1 ELSE 0 END) as inseridas
FROM temp_migration_log
WHERE action IN ('INSERT', 'SKIP_EXISTS')
GROUP BY tipo_origem;

SELECT '--- TOP 20 USUÁRIOS COM MAIS PERMISSÕES MIGRADAS ---' AS secao;
SELECT
    user_name,
    user_email,
    COUNT(*) as permissoes_migradas,
    SUM(CASE WHEN permission_type = 'ver' THEN 1 ELSE 0 END) as ver,
    SUM(CASE WHEN permission_type = 'criar' THEN 1 ELSE 0 END) as criar
FROM temp_migration_log
WHERE action = 'INSERT'
GROUP BY user_id, user_name, user_email
ORDER BY permissoes_migradas DESC
LIMIT 20;

SELECT '--- PERMISSÕES ÓRFÃS (TOP 50) ---' AS secao;
SELECT
    permission_name,
    permission_type,
    COUNT(DISTINCT user_id) as usuarios_afetados
FROM temp_migration_log
WHERE action = 'SKIP_NO_PERMISSION'
GROUP BY permission_name, permission_type
ORDER BY usuarios_afetados DESC
LIMIT 50;

SELECT '--- USUÁRIOS NÃO ENCONTRADOS NO LARAVEL ---' AS secao;
SELECT
    system_user_name,
    system_user_email,
    system_user_login
FROM temp_user_mapping
WHERE encontrado = FALSE
ORDER BY system_user_name;

-- =========================================
-- COMMIT OU ROLLBACK
-- =========================================
-- IMPORTANTE: Troque por COMMIT após revisar os relatórios

ROLLBACK; -- Para cancelar (padrão para segurança)
-- COMMIT; -- Para confirmar a migração
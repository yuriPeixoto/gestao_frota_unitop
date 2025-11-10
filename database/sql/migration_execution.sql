-- =========================================
-- SCRIPT 2: EXECUÇÃO DA MIGRAÇÃO DE PERMISSÕES
-- Sistema Legado (Adianti) → Laravel
-- Postgres + DBeaver + DBLINK
-- =========================================
-- BANCO ATUAL (conectado): cli_carvalima_old (Laravel)
-- BANCO REMOTO (dblink): base_unitop_permission_carvalima (Adianti)
-- =========================================
-- ATENÇÃO: Este script fará inserções no banco!
-- Execute o script de análise primeiro!
-- =========================================

BEGIN;

-- Habilitar extensão dblink se necessário
CREATE EXTENSION IF NOT EXISTS dblink;

-- Tabela temporária para armazenar o mapeamento de usuários
CREATE TEMP TABLE temp_user_mapping (
    system_user_id INTEGER,
    system_user_name TEXT,
    system_user_email TEXT,
    system_user_login TEXT,
    laravel_user_id INTEGER,
    encontrado BOOLEAN DEFAULT FALSE
);

-- Tabela temporária para programas por usuário
CREATE TEMP TABLE temp_user_programs (
    system_user_id INTEGER,
    system_program_id INTEGER,
    program_name TEXT,
    program_controller TEXT,
    origem TEXT
);

-- Tabela temporária para mapeamento de controllers
CREATE TEMP TABLE temp_controller_mapping (
    system_program_id INTEGER,
    program_name TEXT,
    program_controller TEXT,
    controller_slug TEXT,
    permission_ver TEXT,
    permission_criar TEXT,
    permission_ver_exists BOOLEAN DEFAULT FALSE,
    permission_criar_exists BOOLEAN DEFAULT FALSE,
    permission_ver_id INTEGER,
    permission_criar_id INTEGER
);

-- Tabela para log de inserções
CREATE TEMP TABLE temp_migration_log (
    user_id INTEGER,
    user_name TEXT,
    permission_id INTEGER,
    permission_name TEXT,
    action TEXT,
    status TEXT,
    timestamp TIMESTAMP DEFAULT NOW()
);

-- =========================================
-- ETAPA 1: MAPEAR USUÁRIOS ATIVOS
-- =========================================
INSERT INTO temp_user_mapping (system_user_id, system_user_name, system_user_email, system_user_login)
SELECT
    id,
    name,
    email,
    login
FROM dblink(
    'hostaddr=10.10.1.14 port=5432 dbname=base_unitop_permission_carvalima user=postgres password=SisDBA2@2l',
    'SELECT id, name, email, login FROM system_users WHERE active = ''Y'''
) AS t(id INTEGER, name TEXT, email TEXT, login TEXT);

-- Mapear por email
UPDATE temp_user_mapping tum
SET
    laravel_user_id = u.id,
    encontrado = TRUE
FROM users u
WHERE LOWER(TRIM(tum.system_user_email)) = LOWER(TRIM(u.email))
AND tum.system_user_email IS NOT NULL
AND tum.system_user_email != '';

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
-- ETAPA 2: COLETAR PROGRAMAS POR USUÁRIO
-- =========================================
INSERT INTO temp_user_programs (system_user_id, system_program_id, program_name, program_controller, origem)
SELECT DISTINCT
    t.system_user_id,
    t.system_program_id,
    t.program_name,
    t.program_controller,
    'direto'
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

INSERT INTO temp_user_programs (system_user_id, system_program_id, program_name, program_controller, origem)
SELECT DISTINCT
    t.system_user_id,
    t.system_program_id,
    t.program_name,
    t.program_controller,
    'grupo'
FROM dblink(
    'hostaddr=10.10.1.14 port=5432 dbname=base_unitop_permission_carvalima user=postgres password=SisDBA2@2l',
    'SELECT
        sug.system_user_id,
        sgp.system_program_id,
        sp.name as program_name,
        sp.controller as program_controller
    FROM system_user_group sug
    INNER JOIN system_group_program sgp ON sgp.system_group_id = sug.system_group_id
    INNER JOIN system_program sp ON sp.id = sgp.system_program_id'
) AS t(system_user_id INTEGER, system_program_id INTEGER, program_name TEXT, program_controller TEXT)
INNER JOIN temp_user_mapping tum ON tum.system_user_id = t.system_user_id
WHERE tum.encontrado = TRUE
AND NOT EXISTS (
    SELECT 1 FROM temp_user_programs tup2
    WHERE tup2.system_user_id = t.system_user_id
    AND tup2.system_program_id = t.system_program_id
);

-- =========================================
-- ETAPA 3: MAPEAR CONTROLLERS
-- =========================================
INSERT INTO temp_controller_mapping (system_program_id, program_name, program_controller, controller_slug, permission_ver, permission_criar)
SELECT DISTINCT
    tup.system_program_id,
    tup.program_name,
    tup.program_controller,
    LOWER(
        REGEXP_REPLACE(
            REGEXP_REPLACE(tup.program_controller, '(Form|List|Header|Report|Document|Dashboard|View|Card).*$', ''),
            '([A-Z])', '_\1', 'g'
        )
    ),
    'ver_' || LOWER(
        REGEXP_REPLACE(
            REGEXP_REPLACE(tup.program_controller, '(Form|List|Header|Report|Document|Dashboard|View|Card).*$', ''),
            '([A-Z])', '_\1', 'g'
        )
    ),
    'criar_' || LOWER(
        REGEXP_REPLACE(
            REGEXP_REPLACE(tup.program_controller, '(Form|List|Header|Report|Document|Dashboard|View|Card).*$', ''),
            '([A-Z])', '_\1', 'g'
        )
    )
FROM temp_user_programs tup
WHERE tup.program_controller IS NOT NULL
AND tup.program_controller != '';

UPDATE temp_controller_mapping
SET 
    controller_slug = TRIM(BOTH '_' FROM REGEXP_REPLACE(controller_slug, '_+', '_', 'g')),
    permission_ver = TRIM(BOTH '_' FROM REGEXP_REPLACE(permission_ver, '_+', '_', 'g')),
    permission_criar = TRIM(BOTH '_' FROM REGEXP_REPLACE(permission_criar, '_+', '_', 'g'));

-- =========================================
-- ETAPA 4: VERIFICAR PERMISSÕES EXISTENTES
-- =========================================
UPDATE temp_controller_mapping tcm
SET
    permission_ver_exists = TRUE,
    permission_ver_id = p.id
FROM permissions p
WHERE p.name = tcm.permission_ver;

UPDATE temp_controller_mapping tcm
SET
    permission_criar_exists = TRUE,
    permission_criar_id = p.id
FROM permissions p
WHERE p.name = tcm.permission_criar;

-- =========================================
-- ETAPA 5: INSERIR PERMISSÕES - VER
-- =========================================
INSERT INTO model_has_permissions (permission_id, model_type, model_id)
SELECT DISTINCT
    tcm.permission_ver_id,
    'App\Models\User',
    tum.laravel_user_id
FROM temp_user_mapping tum
INNER JOIN temp_user_programs tup ON tup.system_user_id = tum.system_user_id
INNER JOIN temp_controller_mapping tcm ON tcm.system_program_id = tup.system_program_id
WHERE tum.encontrado = TRUE
AND tcm.permission_ver_exists = TRUE
AND NOT EXISTS (
    SELECT 1 FROM model_has_permissions mhp
    WHERE mhp.permission_id = tcm.permission_ver_id
    AND mhp.model_id = tum.laravel_user_id
    AND mhp.model_type = 'App\Models\User'
)
ON CONFLICT (permission_id, model_id, model_type) DO NOTHING;

-- Log das inserções VER
INSERT INTO temp_migration_log (user_id, user_name, permission_id, permission_name, action, status)
SELECT DISTINCT
    tum.laravel_user_id,
    tum.system_user_name,
    tcm.permission_ver_id,
    tcm.permission_ver,
    'INSERT_VER',
    'SUCCESS'
FROM temp_user_mapping tum
INNER JOIN temp_user_programs tup ON tup.system_user_id = tum.system_user_id
INNER JOIN temp_controller_mapping tcm ON tcm.system_program_id = tup.system_program_id
WHERE tum.encontrado = TRUE
AND tcm.permission_ver_exists = TRUE;

-- =========================================
-- ETAPA 6: INSERIR PERMISSÕES - CRIAR
-- =========================================
INSERT INTO model_has_permissions (permission_id, model_type, model_id)
SELECT DISTINCT
    tcm.permission_criar_id,
    'App\Models\User',
    tum.laravel_user_id
FROM temp_user_mapping tum
INNER JOIN temp_user_programs tup ON tup.system_user_id = tum.system_user_id
INNER JOIN temp_controller_mapping tcm ON tcm.system_program_id = tup.system_program_id
WHERE tum.encontrado = TRUE
AND tcm.permission_criar_exists = TRUE
AND NOT EXISTS (
    SELECT 1 FROM model_has_permissions mhp
    WHERE mhp.permission_id = tcm.permission_criar_id
    AND mhp.model_id = tum.laravel_user_id
    AND mhp.model_type = 'App\Models\User'
)
ON CONFLICT (permission_id, model_id, model_type) DO NOTHING;

-- Log das inserções CRIAR
INSERT INTO temp_migration_log (user_id, user_name, permission_id, permission_name, action, status)
SELECT DISTINCT
    tum.laravel_user_id,
    tum.system_user_name,
    tcm.permission_criar_id,
    tcm.permission_criar,
    'INSERT_CRIAR',
    'SUCCESS'
FROM temp_user_mapping tum
INNER JOIN temp_user_programs tup ON tup.system_user_id = tum.system_user_id
INNER JOIN temp_controller_mapping tcm ON tcm.system_program_id = tup.system_program_id
WHERE tum.encontrado = TRUE
AND tcm.permission_criar_exists = TRUE;

-- =========================================
-- RELATÓRIO FINAL
-- =========================================
SELECT '=== MIGRAÇÃO CONCLUÍDA ===' AS relatorio;

SELECT 
    COUNT(DISTINCT user_id) as total_usuarios_migrados,
    COUNT(*) as total_permissoes_inseridas,
    SUM(CASE WHEN action = 'INSERT_VER' THEN 1 ELSE 0 END) as permissoes_ver,
    SUM(CASE WHEN action = 'INSERT_CRIAR' THEN 1 ELSE 0 END) as permissoes_criar
FROM temp_migration_log;

SELECT '=== TOP 10 USUÁRIOS COM MAIS PERMISSÕES ===' AS relatorio;

SELECT 
    user_name,
    COUNT(*) as total_permissoes
FROM temp_migration_log
GROUP BY user_id, user_name
ORDER BY total_permissoes DESC
LIMIT 10;

SELECT '=== PERMISSÕES NÃO MIGRADAS (FALTAM NO SISTEMA) ===' AS relatorio;

SELECT DISTINCT
    permission_ver as permissao_faltante,
    COUNT(*) as usuarios_afetados
FROM temp_controller_mapping tcm
INNER JOIN temp_user_programs tup ON tup.system_program_id = tcm.system_program_id
WHERE NOT tcm.permission_ver_exists
GROUP BY permission_ver
UNION
SELECT DISTINCT
    permission_criar as permissao_faltante,
    COUNT(*) as usuarios_afetados
FROM temp_controller_mapping tcm
INNER JOIN temp_user_programs tup ON tup.system_program_id = tcm.system_program_id
WHERE NOT tcm.permission_criar_exists
GROUP BY permission_criar
ORDER BY usuarios_afetados DESC
LIMIT 20;

-- =========================================
-- COMMIT OU ROLLBACK
-- =========================================
-- Descomente a linha desejada:

-- COMMIT; -- Para confirmar a migração
ROLLBACK; -- Para cancelar (padrão para segurança)

-- =========================================
-- INSTRUÇÕES FINAIS
-- =========================================
-- 1. Execute primeiro o script de ANÁLISE
-- 2. Revise todos os relatórios
-- 3. Certifique-se de que os mapeamentos estão corretos
-- 4. Substitua ROLLBACK por COMMIT acima
-- 5. Execute este script para efetivar a migração
-- 6. Após o COMMIT, teste as permissões no sistema
-- =========================================
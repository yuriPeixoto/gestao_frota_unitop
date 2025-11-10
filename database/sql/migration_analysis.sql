-- =========================================
-- SCRIPT 1: ANÁLISE DE MIGRAÇÃO DE PERMISSÕES
-- Sistema Legado (Adianti) → Laravel
-- Postgres + DBeaver + DBLINK
-- =========================================
-- BANCO ATUAL (conectado): cli_carvalima_old (Laravel)
-- BANCO REMOTO (dblink): base_unitop_permission_carvalima (Adianti)
-- =========================================
-- IMPORTANTE: Ajuste os parâmetros de conexão abaixo se necessário
-- host, port, dbname, user, password
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
    origem TEXT -- 'direto' ou 'grupo'
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

-- Tentar encontrar usuários no Laravel por email
UPDATE temp_user_mapping tum
SET 
    laravel_user_id = u.id,
    encontrado = TRUE
FROM users u
WHERE LOWER(TRIM(tum.system_user_email)) = LOWER(TRIM(u.email))
AND tum.system_user_email IS NOT NULL
AND tum.system_user_email != '';

-- Tentar encontrar por nome (para emails vazios)
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

-- Programas diretos (system_user_program)
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

-- Programas por grupos (system_user_group + system_group_program)
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

-- Limpar underscores duplicados e iniciais
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
-- RELATÓRIOS DE ANÁLISE
-- =========================================

-- Relatório 1: Resumo de Usuários
SELECT 
    '=== RESUMO DE USUÁRIOS ===' AS relatorio;
    
SELECT 
    COUNT(*) as total_usuarios_ativos,
    SUM(CASE WHEN encontrado THEN 1 ELSE 0 END) as usuarios_migrados,
    SUM(CASE WHEN NOT encontrado THEN 1 ELSE 0 END) as usuarios_nao_encontrados
FROM temp_user_mapping;

-- Relatório 2: Usuários Não Encontrados
SELECT 
    '=== USUÁRIOS NÃO ENCONTRADOS NO LARAVEL ===' AS relatorio;
    
SELECT 
    system_user_id,
    system_user_name,
    system_user_email,
    system_user_login
FROM temp_user_mapping
WHERE NOT encontrado
ORDER BY system_user_name;

-- Relatório 3: Total de Programas por Usuário
SELECT 
    '=== PROGRAMAS POR USUÁRIO (TOP 20) ===' AS relatorio;
    
SELECT 
    tum.system_user_name,
    tum.system_user_email,
    COUNT(DISTINCT tup.system_program_id) as total_programas,
    SUM(CASE WHEN tup.origem = 'direto' THEN 1 ELSE 0 END) as programas_diretos,
    SUM(CASE WHEN tup.origem = 'grupo' THEN 1 ELSE 0 END) as programas_grupo
FROM temp_user_mapping tum
LEFT JOIN temp_user_programs tup ON tup.system_user_id = tum.system_user_id
WHERE tum.encontrado = TRUE
GROUP BY tum.system_user_id, tum.system_user_name, tum.system_user_email
ORDER BY total_programas DESC
LIMIT 20;

-- Relatório 4: Controllers Mapeados
SELECT 
    '=== MAPEAMENTO DE CONTROLLERS (SAMPLE) ===' AS relatorio;
    
SELECT 
    program_name,
    program_controller,
    controller_slug,
    permission_ver,
    permission_criar,
    permission_ver_exists,
    permission_criar_exists
FROM temp_controller_mapping
ORDER BY program_name
LIMIT 30;

-- Relatório 5: Permissões Não Encontradas
SELECT 
    '=== PERMISSÕES QUE PRECISAM SER CRIADAS ===' AS relatorio;
    
SELECT DISTINCT
    permission_ver as permissao_faltante,
    'ver' as tipo
FROM temp_controller_mapping
WHERE NOT permission_ver_exists
UNION
SELECT DISTINCT
    permission_criar as permissao_faltante,
    'criar' as tipo
FROM temp_controller_mapping
WHERE NOT permission_criar_exists
ORDER BY permissao_faltante;

-- Relatório 6: Estatísticas Gerais
SELECT 
    '=== ESTATÍSTICAS GERAIS ===' AS relatorio;
    
SELECT 
    (SELECT COUNT(DISTINCT system_program_id) FROM temp_user_programs) as total_programas_unicos,
    (SELECT COUNT(*) FROM temp_controller_mapping WHERE permission_ver_exists) as permissoes_ver_encontradas,
    (SELECT COUNT(*) FROM temp_controller_mapping WHERE permission_criar_exists) as permissoes_criar_encontradas,
    (SELECT COUNT(*) FROM temp_controller_mapping WHERE NOT permission_ver_exists) as permissoes_ver_faltantes,
    (SELECT COUNT(*) FROM temp_controller_mapping WHERE NOT permission_criar_exists) as permissoes_criar_faltantes;

-- Relatório 7: Preview de Inserções
SELECT 
    '=== PREVIEW DE INSERÇÕES (PRIMEIROS 50 REGISTROS) ===' AS relatorio;

SELECT 
    tum.laravel_user_id,
    tum.system_user_name,
    tcm.permission_ver as permission_name,
    tcm.permission_ver_id as permission_id,
    'App\\Models\\User' as model_type
FROM temp_user_mapping tum
INNER JOIN temp_user_programs tup ON tup.system_user_id = tum.system_user_id
INNER JOIN temp_controller_mapping tcm ON tcm.system_program_id = tup.system_program_id
WHERE tum.encontrado = TRUE
AND tcm.permission_ver_exists = TRUE
AND NOT EXISTS (
    SELECT 1 FROM model_has_permissions mhp
    WHERE mhp.permission_id = tcm.permission_ver_id
    AND mhp.model_id = tum.laravel_user_id
    AND mhp.model_type = 'App\\Models\\User'
)
LIMIT 50;

-- =========================================
-- ROLLBACK PARA NÃO COMMITAR ANÁLISE
-- =========================================
ROLLBACK;

-- Nota: Execute este script para análise.
-- As tabelas temporárias serão descartadas após o ROLLBACK.
-- Revise os relatórios antes de executar o script de migração.
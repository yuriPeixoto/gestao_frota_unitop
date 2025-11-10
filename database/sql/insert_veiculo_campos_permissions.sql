-- ============================================================================
-- Script de Inserção de Permissões para Campos de Comodato de Veículos
-- ============================================================================
-- Descrição: Adiciona permissões granulares para os campos id_fornecedor_comodato 
--            e data_comodato nos formulários de veículos
-- Data: 2025-10-17
-- Desenvolvedor: Sistema
-- ============================================================================

-- Verificar permissões existentes (para evitar duplicatas)
SELECT name, description, guard_name 
FROM permissions 
WHERE name IN (
    'ver_fornecedor_comodato',
    'editar_fornecedor_comodato',
    'ver_data_comodato',
    'editar_data_comodato'
);

-- ============================================================================
-- INSERÇÃO DAS PERMISSÕES
-- ============================================================================

-- 1. Permissão: Visualizar Fornecedor Comodato
INSERT INTO permissions (name, guard_name, description, `group`, created_at, updated_at)
SELECT 
    'ver_fornecedor_comodato',
    'web',
    'Permite visualizar o fornecedor de comodato dos veículos',
    'Veículos - Comodato',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE name = 'ver_fornecedor_comodato'
);

-- 2. Permissão: Editar Fornecedor Comodato
INSERT INTO permissions (name, guard_name, description, `group`, created_at, updated_at)
SELECT 
    'editar_fornecedor_comodato',
    'web',
    'Permite editar o fornecedor de comodato dos veículos',
    'Veículos - Comodato',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE name = 'editar_fornecedor_comodato'
);

-- 3. Permissão: Visualizar Data Fim Comodato
INSERT INTO permissions (name, guard_name, description, `group`, created_at, updated_at)
SELECT 
    'ver_data_comodato',
    'web',
    'Permite visualizar a data de fim do comodato dos veículos',
    'Veículos - Comodato',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE name = 'ver_data_comodato'
);

-- 4. Permissão: Editar Data Fim Comodato
INSERT INTO permissions (name, guard_name, description, `group`, created_at, updated_at)
SELECT 
    'editar_data_comodato',
    'web',
    'Permite editar a data de fim do comodato dos veículos',
    'Veículos - Comodato',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM permissions WHERE name = 'editar_data_comodato'
);

-- ============================================================================
-- VERIFICAÇÃO PÓS-INSERÇÃO
-- ============================================================================

-- Listar as permissões recém-criadas
SELECT 
    id,
    name,
    description,
    `group`,
    guard_name,
    created_at
FROM permissions 
WHERE name IN (
    'ver_fornecedor_comodato',
    'editar_fornecedor_comodato',
    'ver_data_comodato',
    'editar_data_comodato'
)
ORDER BY name;

-- ============================================================================
-- EXEMPLO DE CONCESSÃO DE PERMISSÕES A UM USUÁRIO
-- ============================================================================

-- Para conceder TODAS as permissões ao usuário ID 59 (Leonardo), execute:
/*
INSERT INTO model_has_permissions (permission_id, model_type, model_id)
SELECT p.id, 'App\\Models\\User', 59
FROM permissions p
WHERE p.name IN (
    'ver_fornecedor_comodato',
    'editar_fornecedor_comodato',
    'ver_data_comodato',
    'editar_data_comodato'
)
AND NOT EXISTS (
    SELECT 1 FROM model_has_permissions 
    WHERE permission_id = p.id 
    AND model_type = 'App\\Models\\User' 
    AND model_id = 59
);
*/

-- Para conceder apenas permissões de VISUALIZAÇÃO:
/*
INSERT INTO model_has_permissions (permission_id, model_type, model_id)
SELECT p.id, 'App\\Models\\User', {USER_ID}
FROM permissions p
WHERE p.name IN (
    'ver_fornecedor_comodato',
    'ver_data_comodato'
)
AND NOT EXISTS (
    SELECT 1 FROM model_has_permissions 
    WHERE permission_id = p.id 
    AND model_type = 'App\\Models\\User' 
    AND model_id = {USER_ID}
);
*/

-- ============================================================================
-- NOTAS IMPORTANTES
-- ============================================================================
-- 1. Após executar este script, limpe o cache de permissões:
--    php artisan permission:cache-reset
--    OU
--    php artisan cache:clear
--
-- 2. As permissões estarão disponíveis na interface de gerenciamento de
--    permissões em: Admin > Usuários > Permissões
--
-- 3. Os campos só aparecerão no formulário se as colunas id_fornecedor_comodato 
--    e data_comodato existem na tabela veiculos
-- ============================================================================

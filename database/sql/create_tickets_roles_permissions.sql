-- ============================================================================
-- ROLES E PERMISSÕES - SISTEMA DE CHAMADOS
-- ============================================================================

-- ============================================================================
-- Inserir Roles Específicas (se não existirem)
-- ============================================================================

-- Role: Equipe Qualidade
INSERT INTO roles (name, guard_name, created_at, updated_at)
SELECT 'Equipe Qualidade', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE name = 'Equipe Qualidade');

-- Role: Equipe Unitop
INSERT INTO roles (name, guard_name, created_at, updated_at)
SELECT 'Equipe Unitop', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE name = 'Equipe Unitop');

-- ============================================================================
-- Permissões do Sistema de Chamados
-- ============================================================================

-- Permissões de visualização
INSERT INTO permissions (name, guard_name, created_at, updated_at)
SELECT 'tickets.view', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'tickets.view');

INSERT INTO permissions (name, guard_name, created_at, updated_at)
SELECT 'tickets.view_all', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'tickets.view_all');

INSERT INTO permissions (name, guard_name, created_at, updated_at)
SELECT 'tickets.view_internal', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'tickets.view_internal');

-- Permissões de criação e edição
INSERT INTO permissions (name, guard_name, created_at, updated_at)
SELECT 'tickets.create', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'tickets.create');

INSERT INTO permissions (name, guard_name, created_at, updated_at)
SELECT 'tickets.update', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'tickets.update');

INSERT INTO permissions (name, guard_name, created_at, updated_at)
SELECT 'tickets.delete', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'tickets.delete');

-- Permissões de gerenciamento
INSERT INTO permissions (name, guard_name, created_at, updated_at)
SELECT 'tickets.assign', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'tickets.assign');

INSERT INTO permissions (name, guard_name, created_at, updated_at)
SELECT 'tickets.change_status', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'tickets.change_status');

INSERT INTO permissions (name, guard_name, created_at, updated_at)
SELECT 'tickets.set_estimate', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'tickets.set_estimate');

INSERT INTO permissions (name, guard_name, created_at, updated_at)
SELECT 'tickets.add_internal_note', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'tickets.add_internal_note');

-- Permissões específicas da Qualidade
INSERT INTO permissions (name, guard_name, created_at, updated_at)
SELECT 'tickets.quality_review', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'tickets.quality_review');

-- Permissões de relatórios
INSERT INTO permissions (name, guard_name, created_at, updated_at)
SELECT 'tickets.reports', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'tickets.reports');

-- Permissões de configuração
INSERT INTO permissions (name, guard_name, created_at, updated_at)
SELECT 'tickets.manage_categories', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'tickets.manage_categories');

INSERT INTO permissions (name, guard_name, created_at, updated_at)
SELECT 'tickets.manage_tags', 'web', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = 'tickets.manage_tags');

-- ============================================================================
-- Atribuir Permissões às Roles
-- ============================================================================

-- Equipe Qualidade: pode revisar melhorias
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id
FROM permissions p
CROSS JOIN roles r
WHERE r.name = 'Equipe Qualidade'
  AND p.name IN (
    'tickets.view',
    'tickets.view_all',
    'tickets.create',
    'tickets.quality_review'
  )
  AND NOT EXISTS (
    SELECT 1 FROM role_has_permissions rhp
    WHERE rhp.permission_id = p.id AND rhp.role_id = r.id
  );

-- Equipe Unitop: pode fazer tudo
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id
FROM permissions p
CROSS JOIN roles r
WHERE r.name = 'Equipe Unitop'
  AND p.name LIKE 'tickets.%'
  AND NOT EXISTS (
    SELECT 1 FROM role_has_permissions rhp
    WHERE rhp.permission_id = p.id AND rhp.role_id = r.id
  );

-- ============================================================================
-- FIM DO SCRIPT
-- ============================================================================

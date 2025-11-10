-- ============================================================================
-- SISTEMA DE CHAMADOS DE SUPORTE - GESTÃO DE FROTA
-- ============================================================================
-- Sistema completo para gerenciar chamados de suporte, bugs e melhorias
-- Com workflow: Cliente -> Qualidade -> Unitop
-- ============================================================================

-- ============================================================================
-- Tabela de Categorias de Chamados
-- ============================================================================
CREATE TABLE IF NOT EXISTS ticket_categories (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'folder',
    color VARCHAR(50) DEFAULT 'blue',
    is_active BOOLEAN NOT NULL DEFAULT true,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- Índices
CREATE INDEX IF NOT EXISTS ticket_categories_slug_index ON ticket_categories(slug);
CREATE INDEX IF NOT EXISTS ticket_categories_is_active_index ON ticket_categories(is_active);

-- ============================================================================
-- Tabela Principal de Chamados
-- ============================================================================
CREATE TABLE IF NOT EXISTS support_tickets (
    id BIGSERIAL PRIMARY KEY,
    ticket_number VARCHAR(20) NOT NULL UNIQUE, -- Ex: SUP-2025-0001

    -- Relacionamentos
    user_id BIGINT NOT NULL, -- Usuário que criou o chamado
    category_id BIGINT NOT NULL,
    assigned_to BIGINT NULL, -- Atendente atual
    filial_id BIGINT NULL, -- Filial do solicitante

    -- Informações do Chamado
    type VARCHAR(50) NOT NULL CHECK (type IN ('bug', 'melhoria', 'duvida', 'suporte')),
    priority VARCHAR(50) NOT NULL DEFAULT 'media' CHECK (priority IN ('baixa', 'media', 'alta', 'urgente')),
    status VARCHAR(50) NOT NULL DEFAULT 'novo' CHECK (status IN (
        'novo',
        'aguardando_qualidade',
        'aprovado_qualidade',
        'rejeitado_qualidade',
        'em_atendimento',
        'aguardando_cliente',
        'resolvido',
        'fechado',
        'cancelado'
    )),

    subject VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,

    -- Dados adicionais
    browser VARCHAR(100) NULL,
    device VARCHAR(100) NULL,
    ip_address VARCHAR(45) NULL,
    url VARCHAR(500) NULL,

    -- Workflow de Qualidade (para tipo 'melhoria')
    quality_reviewed_by BIGINT NULL, -- Usuário da qualidade que revisou
    quality_reviewed_at TIMESTAMP NULL,
    quality_comments TEXT NULL,

    -- SLA e Prazos
    estimated_hours DECIMAL(8, 2) NULL, -- Estimativa de horas
    estimated_completion_date TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    resolved_at TIMESTAMP NULL,
    closed_at TIMESTAMP NULL,

    -- Satisfação
    satisfaction_rating INT NULL CHECK (satisfaction_rating BETWEEN 1 AND 5),
    satisfaction_comment TEXT NULL,

    -- Controle
    is_internal BOOLEAN DEFAULT false, -- Chamado interno da equipe
    is_public BOOLEAN DEFAULT true, -- Visível para o cliente

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES ticket_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (quality_reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE SET NULL
);

-- Índices
CREATE INDEX IF NOT EXISTS support_tickets_ticket_number_index ON support_tickets(ticket_number);
CREATE INDEX IF NOT EXISTS support_tickets_user_id_index ON support_tickets(user_id);
CREATE INDEX IF NOT EXISTS support_tickets_assigned_to_index ON support_tickets(assigned_to);
CREATE INDEX IF NOT EXISTS support_tickets_category_id_index ON support_tickets(category_id);
CREATE INDEX IF NOT EXISTS support_tickets_type_index ON support_tickets(type);
CREATE INDEX IF NOT EXISTS support_tickets_priority_index ON support_tickets(priority);
CREATE INDEX IF NOT EXISTS support_tickets_status_index ON support_tickets(status);
CREATE INDEX IF NOT EXISTS support_tickets_created_at_index ON support_tickets(created_at);
CREATE INDEX IF NOT EXISTS support_tickets_filial_id_index ON support_tickets(filial_id);

-- ============================================================================
-- Tabela de Respostas/Comentários dos Chamados
-- ============================================================================
CREATE TABLE IF NOT EXISTS ticket_responses (
    id BIGSERIAL PRIMARY KEY,
    ticket_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,

    message TEXT NOT NULL,
    is_internal BOOLEAN DEFAULT false, -- Nota interna (não visível para cliente)
    is_solution BOOLEAN DEFAULT false, -- Marcada como solução

    -- Tempo gasto nesta resposta (para relatórios)
    time_spent_minutes INT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Índices
CREATE INDEX IF NOT EXISTS ticket_responses_ticket_id_index ON ticket_responses(ticket_id);
CREATE INDEX IF NOT EXISTS ticket_responses_user_id_index ON ticket_responses(user_id);
CREATE INDEX IF NOT EXISTS ticket_responses_is_internal_index ON ticket_responses(is_internal);
CREATE INDEX IF NOT EXISTS ticket_responses_created_at_index ON ticket_responses(created_at);

-- ============================================================================
-- Tabela de Anexos dos Chamados
-- ============================================================================
CREATE TABLE IF NOT EXISTS ticket_attachments (
    id BIGSERIAL PRIMARY KEY,
    ticket_id BIGINT NOT NULL,
    response_id BIGINT NULL, -- Se for anexo de uma resposta específica
    user_id BIGINT NOT NULL,

    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size BIGINT NOT NULL, -- em bytes

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (response_id) REFERENCES ticket_responses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Índices
CREATE INDEX IF NOT EXISTS ticket_attachments_ticket_id_index ON ticket_attachments(ticket_id);
CREATE INDEX IF NOT EXISTS ticket_attachments_response_id_index ON ticket_attachments(response_id);

-- ============================================================================
-- Tabela de Histórico de Mudanças de Status
-- ============================================================================
CREATE TABLE IF NOT EXISTS ticket_status_history (
    id BIGSERIAL PRIMARY KEY,
    ticket_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,

    from_status VARCHAR(50) NULL,
    to_status VARCHAR(50) NOT NULL,
    comment TEXT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Índices
CREATE INDEX IF NOT EXISTS ticket_status_history_ticket_id_index ON ticket_status_history(ticket_id);
CREATE INDEX IF NOT EXISTS ticket_status_history_created_at_index ON ticket_status_history(created_at);

-- ============================================================================
-- Tabela de Atribuições de Chamados (histórico)
-- ============================================================================
CREATE TABLE IF NOT EXISTS ticket_assignments (
    id BIGSERIAL PRIMARY KEY,
    ticket_id BIGINT NOT NULL,
    assigned_by BIGINT NOT NULL,
    assigned_to BIGINT NOT NULL,
    comment TEXT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE
);

-- Índices
CREATE INDEX IF NOT EXISTS ticket_assignments_ticket_id_index ON ticket_assignments(ticket_id);
CREATE INDEX IF NOT EXISTS ticket_assignments_assigned_to_index ON ticket_assignments(assigned_to);

-- ============================================================================
-- Tabela de Tags/Etiquetas
-- ============================================================================
CREATE TABLE IF NOT EXISTS ticket_tags (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE,
    color VARCHAR(50) DEFAULT 'gray',

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- Índice
CREATE INDEX IF NOT EXISTS ticket_tags_slug_index ON ticket_tags(slug);

-- ============================================================================
-- Tabela Pivot: Chamados <-> Tags
-- ============================================================================
CREATE TABLE IF NOT EXISTS ticket_tag_pivot (
    ticket_id BIGINT NOT NULL,
    tag_id BIGINT NOT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (ticket_id, tag_id),
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES ticket_tags(id) ON DELETE CASCADE
);

-- ============================================================================
-- Tabela de Observadores (Watchers)
-- ============================================================================
CREATE TABLE IF NOT EXISTS ticket_watchers (
    id BIGSERIAL PRIMARY KEY,
    ticket_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE(ticket_id, user_id),
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Índices
CREATE INDEX IF NOT EXISTS ticket_watchers_ticket_id_index ON ticket_watchers(ticket_id);
CREATE INDEX IF NOT EXISTS ticket_watchers_user_id_index ON ticket_watchers(user_id);

-- ============================================================================
-- Triggers para updated_at
-- ============================================================================
CREATE TRIGGER update_ticket_categories_updated_at BEFORE UPDATE ON ticket_categories
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_support_tickets_updated_at BEFORE UPDATE ON support_tickets
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_ticket_responses_updated_at BEFORE UPDATE ON ticket_responses
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_ticket_tags_updated_at BEFORE UPDATE ON ticket_tags
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ============================================================================
-- Views Úteis
-- ============================================================================

-- View de chamados abertos por prioridade
CREATE OR REPLACE VIEW v_open_tickets_by_priority AS
SELECT
    priority,
    COUNT(*) as total,
    COUNT(CASE WHEN status = 'novo' THEN 1 END) as novos,
    COUNT(CASE WHEN status = 'em_atendimento' THEN 1 END) as em_atendimento,
    COUNT(CASE WHEN status = 'aguardando_cliente' THEN 1 END) as aguardando_cliente
FROM support_tickets
WHERE status NOT IN ('resolvido', 'fechado', 'cancelado')
GROUP BY priority;

-- View de chamados por categoria
CREATE OR REPLACE VIEW v_tickets_by_category AS
SELECT
    c.name as categoria,
    c.slug,
    COUNT(t.id) as total_chamados,
    COUNT(CASE WHEN t.status NOT IN ('resolvido', 'fechado', 'cancelado') THEN 1 END) as abertos,
    COUNT(CASE WHEN t.status IN ('resolvido', 'fechado') THEN 1 END) as fechados
FROM ticket_categories c
LEFT JOIN support_tickets t ON c.id = t.category_id
GROUP BY c.id, c.name, c.slug
ORDER BY total_chamados DESC;

-- View de performance de atendentes
CREATE OR REPLACE VIEW v_agent_performance AS
SELECT
    u.id as user_id,
    u.name as atendente,
    COUNT(t.id) as total_atribuidos,
    COUNT(CASE WHEN t.status = 'em_atendimento' THEN 1 END) as em_atendimento,
    COUNT(CASE WHEN t.status = 'resolvido' THEN 1 END) as resolvidos,
    AVG(EXTRACT(EPOCH FROM (t.resolved_at - t.started_at)) / 3600) as media_horas_resolucao
FROM users u
LEFT JOIN support_tickets t ON u.id = t.assigned_to
WHERE u.id IS NOT NULL
GROUP BY u.id, u.name;

-- ============================================================================
-- Função para gerar número de ticket
-- ============================================================================
CREATE OR REPLACE FUNCTION generate_ticket_number()
RETURNS TEXT AS $$
DECLARE
    current_year INT;
    ticket_count INT;
    new_number TEXT;
BEGIN
    current_year := EXTRACT(YEAR FROM CURRENT_DATE);

    -- Contar tickets do ano atual
    SELECT COUNT(*) INTO ticket_count
    FROM support_tickets
    WHERE EXTRACT(YEAR FROM created_at) = current_year;

    -- Gerar número: SUP-2025-0001
    new_number := 'SUP-' || current_year || '-' || LPAD((ticket_count + 1)::TEXT, 4, '0');

    RETURN new_number;
END;
$$ LANGUAGE plpgsql;

-- ============================================================================
-- Função para calcular SLA (pode ser expandida)
-- ============================================================================
CREATE OR REPLACE FUNCTION calculate_sla_deadline(
    p_priority VARCHAR,
    p_created_at TIMESTAMP
)
RETURNS TIMESTAMP AS $$
DECLARE
    hours_to_add INT;
BEGIN
    -- Definir prazos baseados em prioridade
    hours_to_add := CASE p_priority
        WHEN 'urgente' THEN 4    -- 4 horas
        WHEN 'alta' THEN 24       -- 1 dia
        WHEN 'media' THEN 72      -- 3 dias
        WHEN 'baixa' THEN 168     -- 7 dias
        ELSE 72                   -- padrão: 3 dias
    END;

    RETURN p_created_at + (hours_to_add || ' hours')::INTERVAL;
END;
$$ LANGUAGE plpgsql;

-- ============================================================================
-- Dados Iniciais - Categorias Padrão
-- ============================================================================
INSERT INTO ticket_categories (name, slug, description, icon, color, display_order) VALUES
('Bug/Erro', 'bug', 'Erros e comportamentos inesperados do sistema', 'bug', 'red', 1),
('Melhoria/Feature', 'melhoria', 'Solicitações de melhorias e novas funcionalidades', 'lightbulb', 'yellow', 2),
('Dúvida', 'duvida', 'Dúvidas sobre uso do sistema', 'question-circle', 'blue', 3),
('Suporte Técnico', 'suporte-tecnico', 'Suporte técnico geral', 'headset', 'green', 4),
('Relatórios', 'relatorios', 'Problemas ou melhorias em relatórios', 'chart-bar', 'purple', 5),
('Performance', 'performance', 'Lentidão ou problemas de performance', 'tachometer-alt', 'orange', 6),
('Integrações', 'integracoes', 'Problemas com integrações externas', 'plug', 'indigo', 7),
('Outros', 'outros', 'Outros assuntos', 'ellipsis-h', 'gray', 99)
ON CONFLICT (slug) DO NOTHING;

-- ============================================================================
-- Dados Iniciais - Tags Comuns
-- ============================================================================
INSERT INTO ticket_tags (name, slug, color) VALUES
('Frontend', 'frontend', 'blue'),
('Backend', 'backend', 'green'),
('Database', 'database', 'purple'),
('Mobile', 'mobile', 'indigo'),
('Crítico', 'critico', 'red'),
('Fácil', 'facil', 'green'),
('Médio', 'medio', 'yellow'),
('Complexo', 'complexo', 'orange'),
('Documentação', 'documentacao', 'gray')
ON CONFLICT (slug) DO NOTHING;

-- ============================================================================
-- Comentários nas Tabelas
-- ============================================================================
COMMENT ON TABLE ticket_categories IS 'Categorias de chamados de suporte';
COMMENT ON TABLE support_tickets IS 'Tabela principal de chamados de suporte';
COMMENT ON TABLE ticket_responses IS 'Respostas e comentários dos chamados';
COMMENT ON TABLE ticket_attachments IS 'Anexos dos chamados';
COMMENT ON TABLE ticket_status_history IS 'Histórico de mudanças de status';
COMMENT ON TABLE ticket_assignments IS 'Histórico de atribuições de chamados';
COMMENT ON TABLE ticket_tags IS 'Tags/etiquetas para organização';
COMMENT ON TABLE ticket_watchers IS 'Usuários que observam um chamado';

COMMENT ON COLUMN support_tickets.type IS 'Tipo: bug, melhoria, duvida, suporte';
COMMENT ON COLUMN support_tickets.priority IS 'Prioridade: baixa, media, alta, urgente';
COMMENT ON COLUMN support_tickets.status IS 'Status atual do chamado';
COMMENT ON COLUMN support_tickets.quality_reviewed_by IS 'Usuário da qualidade que revisou (para melhorias)';

-- ============================================================================
-- FIM DO SCRIPT
-- ============================================================================

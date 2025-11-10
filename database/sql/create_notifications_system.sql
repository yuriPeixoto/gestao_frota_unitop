-- ============================================================================
-- SISTEMA DE NOTIFICAÇÕES - GESTÃO DE FROTA
-- ============================================================================
-- Este arquivo cria as tabelas necessárias para o sistema de notificações
-- com suporte a múltiplos níveis (pessoal, departamento, cargo, role)
-- ============================================================================

-- Tabela principal de notificações
CREATE TABLE IF NOT EXISTS notifications (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    type VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id BIGINT NOT NULL,
    data JSONB NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- Índices para otimização
CREATE INDEX IF NOT EXISTS notifications_notifiable_type_notifiable_id_index
    ON notifications(notifiable_type, notifiable_id);
CREATE INDEX IF NOT EXISTS notifications_read_at_index
    ON notifications(read_at);
CREATE INDEX IF NOT EXISTS notifications_created_at_index
    ON notifications(created_at);

-- ============================================================================
-- Tabela de notificações por nível organizacional
-- ============================================================================
CREATE TABLE IF NOT EXISTS notification_targets (
    id BIGSERIAL PRIMARY KEY,
    notification_type VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSONB NULL,
    icon VARCHAR(100) NULL DEFAULT 'bell',
    color VARCHAR(50) NULL DEFAULT 'blue',
    priority VARCHAR(20) NOT NULL DEFAULT 'normal' CHECK (priority IN ('low', 'normal', 'high', 'urgent')),

    -- Níveis de segmentação
    target_type VARCHAR(50) NOT NULL CHECK (target_type IN ('all', 'user', 'department', 'role', 'cargo', 'filial', 'custom')),

    -- IDs específicos (JSON para flexibilidade)
    target_user_ids JSONB NULL DEFAULT '[]'::jsonb,
    target_department_ids JSONB NULL DEFAULT '[]'::jsonb,
    target_role_ids JSONB NULL DEFAULT '[]'::jsonb,
    target_cargo_ids JSONB NULL DEFAULT '[]'::jsonb,
    target_filial_ids JSONB NULL DEFAULT '[]'::jsonb,

    -- Agendamento e expiração
    scheduled_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,

    -- Controle
    is_active BOOLEAN NOT NULL DEFAULT true,
    is_broadcasted BOOLEAN NOT NULL DEFAULT false,
    broadcasted_at TIMESTAMP NULL,
    created_by BIGINT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Índices para notification_targets
CREATE INDEX IF NOT EXISTS notification_targets_type_index ON notification_targets(notification_type);
CREATE INDEX IF NOT EXISTS notification_targets_target_type_index ON notification_targets(target_type);
CREATE INDEX IF NOT EXISTS notification_targets_priority_index ON notification_targets(priority);
CREATE INDEX IF NOT EXISTS notification_targets_scheduled_at_index ON notification_targets(scheduled_at);
CREATE INDEX IF NOT EXISTS notification_targets_is_active_index ON notification_targets(is_active);
CREATE INDEX IF NOT EXISTS notification_targets_created_at_index ON notification_targets(created_at);

-- Índices GIN para buscas em JSONB
CREATE INDEX IF NOT EXISTS notification_targets_user_ids_gin ON notification_targets USING GIN (target_user_ids);
CREATE INDEX IF NOT EXISTS notification_targets_department_ids_gin ON notification_targets USING GIN (target_department_ids);
CREATE INDEX IF NOT EXISTS notification_targets_role_ids_gin ON notification_targets USING GIN (target_role_ids);

-- ============================================================================
-- Tabela de leitura de notificações segmentadas
-- ============================================================================
CREATE TABLE IF NOT EXISTS notification_reads (
    id BIGSERIAL PRIMARY KEY,
    notification_target_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    read_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (notification_target_id) REFERENCES notification_targets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE(notification_target_id, user_id)
);

-- Índices
CREATE INDEX IF NOT EXISTS notification_reads_user_id_index ON notification_reads(user_id);
CREATE INDEX IF NOT EXISTS notification_reads_notification_target_id_index ON notification_reads(notification_target_id);
CREATE INDEX IF NOT EXISTS notification_reads_read_at_index ON notification_reads(read_at);

-- ============================================================================
-- Tabela de configurações de notificação por usuário
-- ============================================================================
CREATE TABLE IF NOT EXISTS user_notification_settings (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,

    -- Preferências de canal
    enable_database BOOLEAN NOT NULL DEFAULT true,
    enable_email BOOLEAN NOT NULL DEFAULT true,
    enable_broadcast BOOLEAN NOT NULL DEFAULT true,
    enable_push BOOLEAN NOT NULL DEFAULT false,

    -- Preferências por tipo de notificação
    notification_preferences JSONB NOT NULL DEFAULT '{
        "sistema": {"enabled": true, "channels": ["database", "broadcast"]},
        "veiculos": {"enabled": true, "channels": ["database", "broadcast"]},
        "manutencao": {"enabled": true, "channels": ["database", "broadcast", "email"]},
        "sinistros": {"enabled": true, "channels": ["database", "broadcast", "email"]},
        "estoque": {"enabled": true, "channels": ["database", "broadcast"]},
        "vencimentarios": {"enabled": true, "channels": ["database", "broadcast", "email"]},
        "compras": {"enabled": true, "channels": ["database", "broadcast", "email"]}
    }'::jsonb,

    -- Horários de silêncio (não enviar notificações)
    quiet_hours_start TIME NULL DEFAULT '22:00:00',
    quiet_hours_end TIME NULL DEFAULT '08:00:00',
    quiet_hours_enabled BOOLEAN NOT NULL DEFAULT false,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(user_id)
);

-- Índice
CREATE INDEX IF NOT EXISTS user_notification_settings_user_id_index ON user_notification_settings(user_id);

-- ============================================================================
-- Comentários nas tabelas
-- ============================================================================
COMMENT ON TABLE notifications IS 'Tabela padrão Laravel para notificações diretas aos usuários';
COMMENT ON TABLE notification_targets IS 'Notificações segmentadas por nível organizacional (departamento, cargo, role, etc)';
COMMENT ON TABLE notification_reads IS 'Controle de leitura de notificações segmentadas';
COMMENT ON TABLE user_notification_settings IS 'Configurações de preferências de notificação por usuário';

-- ============================================================================
-- Função para atualizar updated_at automaticamente
-- ============================================================================
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Triggers para atualizar updated_at
CREATE TRIGGER update_notifications_updated_at BEFORE UPDATE ON notifications
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_notification_targets_updated_at BEFORE UPDATE ON notification_targets
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_user_notification_settings_updated_at BEFORE UPDATE ON user_notification_settings
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ============================================================================
-- Views úteis para consultas
-- ============================================================================

-- View de notificações não lidas por usuário
CREATE OR REPLACE VIEW v_unread_notifications AS
SELECT
    n.id,
    n.type,
    n.notifiable_id as user_id,
    n.data,
    n.created_at,
    (n.data->>'title') as title,
    (n.data->>'message') as message,
    (n.data->>'icon') as icon,
    (n.data->>'color') as color,
    (n.data->>'priority') as priority
FROM notifications n
WHERE n.read_at IS NULL
  AND n.notifiable_type = 'App\Models\User';

COMMENT ON VIEW v_unread_notifications IS 'View de notificações não lidas por usuário';

-- ============================================================================
-- Função para limpar notificações antigas
-- ============================================================================
CREATE OR REPLACE FUNCTION cleanup_old_notifications(days_to_keep INTEGER DEFAULT 90)
RETURNS INTEGER AS $$
DECLARE
    deleted_count INTEGER;
BEGIN
    -- Deletar notificações lidas há mais de X dias
    DELETE FROM notifications
    WHERE read_at IS NOT NULL
      AND read_at < CURRENT_TIMESTAMP - (days_to_keep || ' days')::INTERVAL;

    GET DIAGNOSTICS deleted_count = ROW_COUNT;

    -- Deletar notification_targets expiradas
    DELETE FROM notification_targets
    WHERE expires_at IS NOT NULL
      AND expires_at < CURRENT_TIMESTAMP;

    RETURN deleted_count;
END;
$$ LANGUAGE plpgsql;

COMMENT ON FUNCTION cleanup_old_notifications IS 'Limpa notificações lidas antigas e notification_targets expiradas';

-- ============================================================================
-- Dados iniciais (opcional)
-- ============================================================================

-- Exemplo de notificação de boas-vindas ao sistema
-- INSERT INTO notification_targets (
--     notification_type,
--     title,
--     message,
--     icon,
--     color,
--     priority,
--     target_type,
--     is_active
-- ) VALUES (
--     'sistema.boas_vindas',
--     'Bem-vindo ao Sistema de Gestão de Frota',
--     'Seja bem-vindo! Explore as funcionalidades do sistema e configure suas preferências de notificação.',
--     'info-circle',
--     'blue',
--     'normal',
--     'all',
--     true
-- );

-- ============================================================================
-- FIM DO SCRIPT
-- ============================================================================

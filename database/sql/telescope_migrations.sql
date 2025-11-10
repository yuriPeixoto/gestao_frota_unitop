-- =================================================================
-- LARAVEL TELESCOPE - MIGRAÇÕES MANUAIS
-- =================================================================
-- Arquivo: database/sql/telescope_migrations.sql
-- Executar via DBeaver após backup da base
-- Data: 02/09/2025
-- =================================================================

-- Tabela principal para entradas do Telescope
CREATE TABLE telescope_entries (
    sequence bigserial NOT NULL,
    uuid varchar(36) NOT NULL,
    batch_id varchar(36) NOT NULL,
    family_hash varchar(255) DEFAULT NULL,
    should_display_on_index boolean NOT NULL DEFAULT true,
    type varchar(20) NOT NULL,
    content jsonb NOT NULL,
    created_at timestamp(0) without time zone DEFAULT NULL,
    CONSTRAINT telescope_entries_pkey PRIMARY KEY (sequence),
    CONSTRAINT telescope_entries_uuid_unique UNIQUE (uuid)
);

-- Índices para performance
CREATE INDEX telescope_entries_batch_id_index ON telescope_entries USING btree (batch_id);
CREATE INDEX telescope_entries_family_hash_index ON telescope_entries USING btree (family_hash);
CREATE INDEX telescope_entries_created_at_index ON telescope_entries USING btree (created_at);
CREATE INDEX telescope_entries_type_should_display_on_index_index ON telescope_entries USING btree (type, should_display_on_index);

-- Tabela para monitoramento de tags
CREATE TABLE telescope_monitoring (
    tag varchar(255) NOT NULL,
    CONSTRAINT telescope_monitoring_pkey PRIMARY KEY (tag)
);

-- Comentários para documentação
COMMENT ON TABLE telescope_entries IS 'Entradas do Laravel Telescope - logs, queries, requests, etc.';
COMMENT ON COLUMN telescope_entries.sequence IS 'ID sequencial auto-incrementado';
COMMENT ON COLUMN telescope_entries.uuid IS 'UUID único da entrada';
COMMENT ON COLUMN telescope_entries.batch_id IS 'ID do batch para agrupamento';
COMMENT ON COLUMN telescope_entries.family_hash IS 'Hash para agrupamento de entradas relacionadas';
COMMENT ON COLUMN telescope_entries.should_display_on_index IS 'Se deve ser exibida na listagem principal';
COMMENT ON COLUMN telescope_entries.type IS 'Tipo de entrada (query, request, exception, etc.)';
COMMENT ON COLUMN telescope_entries.content IS 'Dados da entrada em JSON';

COMMENT ON TABLE telescope_monitoring IS 'Tags monitoradas pelo Telescope';

-- Grant permissions (ajustar conforme necessário)
-- GRANT ALL PRIVILEGES ON TABLE telescope_entries TO laravel_user;
-- GRANT ALL PRIVILEGES ON SEQUENCE telescope_entries_sequence_seq TO laravel_user;
-- GRANT ALL PRIVILEGES ON TABLE telescope_monitoring TO laravel_user;

-- =================================================================
-- FIM DAS MIGRAÇÕES DO TELESCOPE
-- =================================================================
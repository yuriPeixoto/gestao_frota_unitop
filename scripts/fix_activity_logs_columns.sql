-- ==========================================
-- SCRIPT SQL: ADICIONAR COLUNAS ACTIVITY_LOGS  
-- ==========================================
-- Descrição: Adicionar colunas faltantes na tabela activity_logs 
--           para compatibilizar com o sistema de logging atualizado
-- Data: 2025-01-29
-- Autor: Sistema Gestão Frota - Correção SQLSTATE[25P02]
-- ==========================================

-- 🔍 VERIFICAR ESTRUTURA ATUAL
SELECT column_name, data_type, is_nullable, column_default
FROM information_schema.columns 
WHERE table_name = 'activity_logs' 
ORDER BY ordinal_position;

-- ✅ ADICIONAR COLUNAS FALTANTES
BEGIN;

-- Coluna para criticidade do log
ALTER TABLE activity_logs 
ADD COLUMN IF NOT EXISTS criticality VARCHAR(20) DEFAULT 'medium' CHECK (criticality IN ('low', 'medium', 'high', 'critical'));

-- Coluna para categoria do log
ALTER TABLE activity_logs 
ADD COLUMN IF NOT EXISTS category VARCHAR(30) DEFAULT 'operational' CHECK (category IN ('security', 'financial', 'operational', 'administrative'));

-- Coluna para resumo do log
ALTER TABLE activity_logs 
ADD COLUMN IF NOT EXISTS summary TEXT;

-- Coluna para tags (JSON)
ALTER TABLE activity_logs 
ADD COLUMN IF NOT EXISTS tags JSONB;

-- Coluna para retenção em dias
ALTER TABLE activity_logs 
ADD COLUMN IF NOT EXISTS retention_days INTEGER DEFAULT 365;

-- Coluna para usuários afetados (JSON)
ALTER TABLE activity_logs 
ADD COLUMN IF NOT EXISTS affected_users JSONB;

-- 🔍 VERIFICAR RESULTADO
SELECT column_name, data_type, is_nullable, column_default
FROM information_schema.columns 
WHERE table_name = 'activity_logs' 
ORDER BY ordinal_position;

-- ✅ CONFIRMAR TRANSAÇÃO
COMMIT;

-- 📝 COMENTÁRIOS SOBRE A ALTERAÇÃO:
-- Esta alteração resolve o erro SQLSTATE[25P02] que estava ocorrendo
-- quando o sistema tentava inserir dados em colunas que não existiam.
-- Após executar este script, as transações de auto-save devem funcionar normalmente.

-- 🧪 TESTE RÁPIDO (opcional):
-- INSERT INTO activity_logs (user_id, action, model, model_id, criticality, category) 
-- VALUES (1, 'test', 'TestModel', 1, 'low', 'operational');
-- DELETE FROM activity_logs WHERE action = 'test' AND model = 'TestModel';
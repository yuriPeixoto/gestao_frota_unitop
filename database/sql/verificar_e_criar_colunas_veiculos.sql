-- ============================================================================
-- Script de Verificação e Criação das Colunas dos Campos de Veículos
-- ============================================================================
-- Descrição: Verifica e cria as colunas valor_seguro e proxima_revisao
-- Data: 2025-10-17
-- Desenvolvedor: Sistema
-- ============================================================================

-- ============================================================================
-- ETAPA 1: VERIFICAÇÃO DAS COLUNAS EXISTENTES
-- ============================================================================

-- Verificar estrutura completa da tabela veiculos
DESCRIBE veiculos;

-- Verificar especificamente se as colunas existem
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'veiculos'
  AND COLUMN_NAME IN ('valor_seguro', 'proxima_revisao');

-- ============================================================================
-- ETAPA 2: CRIAÇÃO DAS COLUNAS (SE NECESSÁRIO)
-- ============================================================================

-- Verificar se a coluna 'valor_seguro' existe antes de criar
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'veiculos' 
      AND COLUMN_NAME = 'valor_seguro'
);

-- Criar coluna 'valor_seguro' se não existir
SET @sql_valor_seguro = IF(
    @column_exists = 0,
    'ALTER TABLE veiculos ADD COLUMN valor_seguro DECIMAL(10,2) NULL COMMENT "Valor do seguro do veículo" AFTER valor_venal',
    'SELECT "Coluna valor_seguro já existe" AS status'
);

PREPARE stmt FROM @sql_valor_seguro;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar se a coluna 'proxima_revisao' existe antes de criar
SET @column_exists_revisao = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'veiculos' 
      AND COLUMN_NAME = 'proxima_revisao'
);

-- Criar coluna 'proxima_revisao' se não existir
SET @sql_proxima_revisao = IF(
    @column_exists_revisao = 0,
    'ALTER TABLE veiculos ADD COLUMN proxima_revisao DATE NULL COMMENT "Data da próxima revisão" AFTER valor_seguro',
    'SELECT "Coluna proxima_revisao já existe" AS status'
);

PREPARE stmt FROM @sql_proxima_revisao;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- ETAPA 3: VERIFICAÇÃO PÓS-CRIAÇÃO
-- ============================================================================

-- Verificar se as colunas foram criadas com sucesso
SELECT 
    COLUMN_NAME AS 'Campo',
    DATA_TYPE AS 'Tipo',
    IS_NULLABLE AS 'Permite NULL',
    COLUMN_DEFAULT AS 'Valor Padrão',
    COLUMN_COMMENT AS 'Comentário',
    ORDINAL_POSITION AS 'Posição'
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'veiculos'
  AND COLUMN_NAME IN ('valor_venal', 'valor_seguro', 'proxima_revisao')
ORDER BY ORDINAL_POSITION;

-- Verificar a estrutura geral
SELECT 
    COUNT(*) AS 'Total de Colunas',
    SUM(CASE WHEN COLUMN_NAME = 'valor_seguro' THEN 1 ELSE 0 END) AS 'valor_seguro existe?',
    SUM(CASE WHEN COLUMN_NAME = 'proxima_revisao' THEN 1 ELSE 0 END) AS 'proxima_revisao existe?'
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'veiculos';

-- ============================================================================
-- ETAPA 4: TESTES BÁSICOS
-- ============================================================================

-- Testar inserção de valores (rollback automático)
START TRANSACTION;

-- Criar um veículo de teste
INSERT INTO veiculos (
    placa, 
    valor_venal, 
    valor_seguro, 
    proxima_revisao,
    situacao_veiculo
) VALUES (
    'TEST-1234', 
    50000.00, 
    5000.00, 
    '2025-12-31',
    1
);

-- Verificar se foi inserido
SELECT 
    id_veiculo,
    placa,
    valor_venal,
    valor_seguro,
    proxima_revisao
FROM veiculos 
WHERE placa = 'TEST-1234';

-- Desfazer a inserção de teste
ROLLBACK;

-- ============================================================================
-- EXEMPLO DE ATUALIZAÇÃO DE DADOS EXISTENTES
-- ============================================================================

/*
-- Caso queira definir valores padrão para veículos existentes
UPDATE veiculos 
SET valor_seguro = 0.00
WHERE valor_seguro IS NULL;

UPDATE veiculos 
SET proxima_revisao = DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
WHERE proxima_revisao IS NULL 
  AND situacao_veiculo = 1;
*/

-- ============================================================================
-- RESULTADO ESPERADO
-- ============================================================================
-- ✅ Ambas as colunas devem existir
-- ✅ valor_seguro: DECIMAL(10,2), NULL permitido
-- ✅ proxima_revisao: DATE, NULL permitido
-- ✅ Posicionadas logo após 'valor_venal'
-- ============================================================================

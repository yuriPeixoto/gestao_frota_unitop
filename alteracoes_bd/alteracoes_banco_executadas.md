# Alterações do Banco de Dados - Executadas

**Data de Execução:** 16/09/2025  
**Banco:** PostgreSQL  
**Projeto:** Laravel 11 + Eloquent + Tailwind  

## 1. EXTENSÕES

```sql
-- Habilita extensão para remoção de acentos em buscas
CREATE EXTENSION IF NOT EXISTS unaccent;
```

---

## 2. TABELA: telefone

```sql
-- Adição de campos para contato comercial
ALTER TABLE telefone ADD COLUMN contato_comercial text;
ALTER TABLE telefone ADD COLUMN telefone_contato text;
```

---

## 3. TABELA: motivo_multa

```sql
-- Correção ortográfica de nomes de colunas
ALTER TABLE motivo_multa RENAME COLUMN id_filial_responsaval TO id_filial_responsavel;
ALTER TABLE motivo_multa RENAME COLUMN id_departamento_responsaval TO id_departamento_responsavel;

-- Definição de valores padrão para campos booleanos
ALTER TABLE motivo_multa ALTER COLUMN debitar_condutor SET DEFAULT false;
ALTER TABLE motivo_multa ALTER COLUMN is_assinado SET DEFAULT false;
```

---

## 4. TABELA: detalhe_multa

```sql
-- Adição de campo para notificação de detalhe
ALTER TABLE detalhe_multa ADD COLUMN notificacao_detalhe text;
```

---

## 5. TABELA: parcelasipva

```sql
-- Definição de valor padrão para juros
ALTER TABLE parcelasipva ALTER COLUMN valor_juros SET DEFAULT 0;
```

---

## 6. TABELA: tipoequipamento

```sql
-- Adição de colunas para quantidade de pneus por eixo
ALTER TABLE tipoequipamento
ADD COLUMN numero_pneus_eixo_1 INTEGER,
ADD COLUMN numero_pneus_eixo_2 INTEGER,
ADD COLUMN numero_pneus_eixo_3 INTEGER,
ADD COLUMN numero_pneus_eixo_4 INTEGER;
```

---

## 7. TABELA: grupo_servico

### **DECISÃO TÉCNICA - SEQUÊNCIA CORRIGIDA:**
- **Problema encontrado:** Sequência estava em 28, mas máximo ID na tabela era 999
- **Solução aplicada:** Ajuste da sequência para o valor máximo atual
- **Verificação prévia realizada:** `SELECT last_value FROM grupo_servico_id_grupo_seq;`

```sql
-- Correção da sequência para próximos IDs serem gerados corretamente
SELECT setval('public.grupo_servico_id_grupo_seq', (SELECT MAX(id_grupo) FROM grupo_servico));
```

---

## 8. TABELA: pessoal

### **DECISÃO TÉCNICA - CPF NOT NULL PULADA:**
- **Problema encontrado:** 753 registros com CPF nulo que não podem ser excluídos nem receber valores temporários
- **Solução adotada:** **Não alterar o banco** - implementar validação no Controller Laravel e Frontend
- **Implementar:** Validação obrigatória de CPF no Controller + Frontend com formato válido
- **Query pulada:** `ALTER TABLE pessoal ALTER COLUMN cpf SET NOT NULL;`

```sql
-- Adição de campo para órgão emissor do RG
ALTER TABLE pessoal ADD COLUMN orgao_emissor varchar(25);

-- Alteração do tamanho do campo CPF para formato 000.000.000-00
ALTER TABLE pessoal ALTER COLUMN cpf TYPE varchar(14);
ALTER TABLE pessoal ALTER COLUMN cpf DROP DEFAULT;
-- ALTER TABLE pessoal ALTER COLUMN cpf SET NOT NULL; -- PULADA - Ver decisão técnica acima

-- Alteração do tamanho do campo RG
ALTER TABLE pessoal ALTER COLUMN rg TYPE varchar(25);

-- Adição de campo para foto da pessoa
ALTER TABLE pessoal ADD COLUMN imagem_pessoal varchar(150);

-- Adição de campos de auditoria
ALTER TABLE pessoal ADD COLUMN data_inclusao TIMESTAMP;
ALTER TABLE pessoal ADD COLUMN data_alteracao TIMESTAMP;
```

---

## 9. TABELA: fornecedor

```sql
-- Adição de campo para identificar pessoa jurídica/física
ALTER TABLE fornecedor ADD COLUMN is_juridico BOOLEAN;

-- Definição de valores padrão para campos booleanos
ALTER TABLE fornecedor ALTER COLUMN is_ativo SET DEFAULT false;
ALTER TABLE fornecedor ALTER COLUMN is_juridico SET DEFAULT false;

-- Adição de campo para website do fornecedor
ALTER TABLE fornecedor ADD COLUMN site text;

-- Atualização de registros legados
UPDATE fornecedor SET is_ativo = false WHERE is_ativo IS NULL;
UPDATE fornecedor SET is_juridico = true WHERE cnpj_fornecedor IS NOT NULL;
```

---

## 10. TABELA: ipvaveiculo

```sql
-- Adição de campos para controle de parcelas do IPVA
ALTER TABLE ipvaveiculo ADD COLUMN intervalo_parcelas INTEGER;
ALTER TABLE ipvaveiculo ADD COLUMN data_primeira_parcela date;
```

---

## 11. TABELA: estado

```sql
-- Atualização completa dos estados brasileiros
TRUNCATE TABLE estado;

INSERT INTO estado (id_uf, uf, nome, codigo_ibge) VALUES
(1, 'AC', 'Acre', 12),
(2, 'AL', 'Alagoas', 27),
(3, 'AP', 'Amapá', 16),
(4, 'AM', 'Amazonas', 13),
(5, 'BA', 'Bahia', 29),
(6, 'CE', 'Ceará', 23),
(7, 'DF', 'Distrito Federal', 53),
(8, 'ES', 'Espírito Santo', 32),
(9, 'GO', 'Goiás', 52),
(10, 'MA', 'Maranhão', 21),
(11, 'MT', 'Mato Grosso', 51),
(12, 'MS', 'Mato Grosso do Sul', 50),
(13, 'MG', 'Minas Gerais', 31),
(14, 'PA', 'Pará', 15),
(15, 'PB', 'Paraíba', 25),
(16, 'PR', 'Paraná', 41),
(17, 'PE', 'Pernambuco', 26),
(18, 'PI', 'Piauí', 22),
(19, 'RJ', 'Rio de Janeiro', 33),
(20, 'RN', 'Rio Grande do Norte', 24),
(21, 'RS', 'Rio Grande do Sul', 43),
(22, 'RO', 'Rondônia', 11),
(23, 'RR', 'Roraima', 14),
(24, 'SC', 'Santa Catarina', 42),
(25, 'SP', 'São Paulo', 35),
(26, 'SE', 'Sergipe', 28),
(27, 'TO', 'Tocantins', 17);
```

---

## 12. TABELA: teste_fumaca

```sql
-- Adição de campos de auditoria
ALTER TABLE teste_fumaca ADD COLUMN data_inclusao TIMESTAMP;
UPDATE teste_fumaca SET data_inclusao = now();
ALTER TABLE teste_fumaca ALTER COLUMN data_inclusao SET NOT NULL;
ALTER TABLE teste_fumaca ADD COLUMN data_alteracao TIMESTAMP;
```

---

## 13. TABELA: transferencia_pneus

```sql
-- Alteração de tipo de coluna e valor padrão
ALTER TABLE transferencia_pneus ALTER COLUMN observacao_baixa TYPE TEXT;
ALTER TABLE transferencia_pneus ALTER COLUMN recebido SET DEFAULT false;
```

---

## 14. TABELA: entrada_afericao_abastecimento

```sql
-- Alteração de tipos para compatibilidade com abastecimento_integracao
ALTER TABLE entrada_afericao_abastecimento ALTER COLUMN volume_abastecimento TYPE double precision;
ALTER TABLE entrada_afericao_abastecimento ALTER COLUMN volume_entrada TYPE double precision;
```

---

## 15. TABELA: tipo_ordem_servico

```sql
-- Inserção de novo tipo para módulo borracharia
INSERT INTO tipo_ordem_servico (data_inclusao, descricao_tipo_ordem) 
VALUES (now(), 'Ordem de Serviço Borracharia');
```

---

## 16. TABELA: pneu

```sql
-- Renomeação de coluna e remoção de constraint
ALTER TABLE pneu RENAME COLUMN id_controle_vida_pneu TO controle_vida_pneu;
ALTER TABLE pneu DROP CONSTRAINT fk_controle_vida;
```

---

## 17. TABELA: requisicao_pneu

```sql
-- Adição de referência para ordem de serviço borracharia
ALTER TABLE requisicao_pneu ADD COLUMN id_ordem_servico INTEGER;
```

---

## 18. TABELA: veiculo

```sql
-- Adição de campo para imagem do veículo
ALTER TABLE veiculo ADD COLUMN imagem_veiculo varchar(150);
```

---

## 19. FUNÇÕES ATUALIZADAS/CRIADAS

As seguintes funções foram completamente atualizadas ou criadas:

1. **`fc_verifica_quantidades_pneus(integer)`**
   - Verifica quantidades de pneus entre nota fiscal e marcações

2. **`fc_atualiza_status_pneu(integer)`** 
   - Atualiza status de pneus de 'BORRACHARIA' para 'ESTOQUE'
   - Corrigida para incluir data_alteracao

3. **`fc_gerar_num_fogo(integer, integer)`**
   - Gera números de fogo para pneus automaticamente
   - Adaptada para novas colunas em pneu e historicopneu

4. **`fc_insere_entrada_afericao()`** (TRIGGER)
   - Corrigida para funcionar apenas com tanques ativos (data_encerramento IS NULL)

**Nota:** O código completo de todas as funções está disponível no arquivo `Alteracoes Banco de Dados.txt` original.

---

## RESUMO DA EXECUÇÃO

- ✅ **47 queries planejadas**
- ✅ **46 queries executadas com sucesso**
- ⚠️ **1 query pulada** (CPF NOT NULL) - com solução alternativa definida
- ✅ **1 sequência corrigida** (grupo_servico)
- ✅ **4 funções atualizadas/criadas**
- ✅ **15+ tabelas alteradas**

**Status:** Todas as alterações foram aplicadas com sucesso. O banco está pronto para o desenvolvimento Laravel 11.
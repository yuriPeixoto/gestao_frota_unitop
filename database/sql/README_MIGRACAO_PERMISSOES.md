# Migração de Permissões - Mad Builder para Laravel

## 📋 Visão Geral

Este conjunto de scripts realiza a migração completa de permissões do sistema antigo (Mad Builder/Adianti) para o novo sistema Laravel, **convertendo todas as permissões (diretas e por grupo) em permissões individuais por usuário**.

### ⚠️ Pontos Importantes

1. **Não haverá mais permissões por grupo** - Todas as permissões serão individuais
2. **Herança de grupos** - Usuários que tinham permissões via grupo receberão essas permissões individualmente
3. **Usuários não migrados** - Apenas usuários ativos que existem no Laravel serão migrados
4. **Permissões órfãs** - Permissões sem correspondência no Laravel serão listadas para análise

## 📁 Arquivos Disponíveis

### 1. `permissions_mad_builder.sql`
- **Descrição**: Dump completo do banco Mad Builder com todas as tabelas de permissões
- **Uso**: Referência para consulta (não executar)

### 2. `analise_completa_permissoes.sql` ⭐
- **Descrição**: Script de análise que **NÃO faz alterações** no banco
- **Executa**: Apenas consultas (ROLLBACK no final)
- **Objetivo**: Gerar relatórios detalhados antes da migração

#### Relatórios Gerados:
1. **Resumo Geral**
   - Total de usuários no sistema antigo
   - Usuários ativos vs inativos
   - Usuários migrados vs não migrados

2. **Usuários Ativos Não Migrados**
   - Lista de usuários que estão ativos no Mad Builder mas não existem no Laravel

3. **Estatísticas de Permissões**
   - Total de programas/controllers únicos
   - Permissões diretas vs via grupo

4. **Permissões Órfãs**
   - Permissões do Mad Builder sem correspondência no Laravel
   - Quantidade de usuários afetados por cada permissão órfã

5. **Top 20 Usuários com Mais Permissões**
   - Ranking de usuários por quantidade de permissões

6. **Distribuição por Grupo**
   - Análise de permissões herdadas de cada grupo

7. **Validação de Mapeamento**
   - Estatísticas de controllers mapeados corretamente

### 3. `executar_migracao_permissoes_individual.sql` ⭐
- **Descrição**: Script de execução que **MIGRA AS PERMISSÕES**
- **Executa**: INSERT na tabela `model_has_permissions`
- **Objetivo**: Realizar a migração efetiva

#### O que faz:
1. Mapeia usuários ativos do Mad Builder para o Laravel (por email e nome)
2. Coleta TODAS as permissões (diretas + via grupo)
3. Mapeia controllers para permissões Laravel (padrão `ver_*` e `criar_*`)
4. Insere as permissões na tabela `model_has_permissions`
5. Gera log completo da migração

#### Relatórios Finais:
1. **Resumo Geral**
   - Usuários migrados
   - Permissões inseridas
   - Permissões que já existiam
   - Permissões órfãs

2. **Por Tipo de Permissão**
   - Estatísticas de permissões `ver` vs `criar`

3. **Por Origem**
   - Quantas permissões vieram diretas vs via grupo

4. **Top 20 Usuários com Mais Permissões Migradas**

5. **Permissões Órfãs (Top 50)**
   - Lista detalhada para análise posterior

6. **Usuários Não Encontrados**
   - Usuários ativos no Mad Builder que não existem no Laravel

## 🚀 Como Executar

### Passo 1: Executar Análise

```bash
# Conecte-se ao banco Laravel via DBeaver ou psql
# Execute o arquivo: analise_completa_permissoes.sql
```

**Importante:**
- ✅ Este script é SEGURO (não faz alterações)
- ✅ Revise TODOS os relatórios gerados
- ✅ Salve as "Permissões Órfãs" para análise posterior
- ✅ Verifique os "Usuários Não Encontrados"

### Passo 2: Revisar Resultados

Analise cuidadosamente:
- [ ] Quantidade de usuários que serão migrados
- [ ] Quantidade de permissões órfãs
- [ ] Usuários ativos não migrados (criar manualmente se necessário)
- [ ] Validação de mapeamento de controllers

### Passo 3: Executar Migração

```bash
# 1. Abra o arquivo: executar_migracao_permissoes_individual.sql
# 2. Revise os parâmetros de conexão DBLINK (linha 72)
# 3. Execute o script
# 4. Revise os relatórios finais
# 5. Se tudo estiver OK, troque ROLLBACK por COMMIT no final
```

**ATENÇÃO:**
```sql
-- Linha final do script (318):

ROLLBACK; -- Para cancelar (padrão para segurança)
-- COMMIT; -- Para confirmar a migração

-- Troque por:

-- ROLLBACK; -- Para cancelar (padrão para segurança)
COMMIT; -- Para confirmar a migração
```

### Passo 4: Validação Pós-Migração

Execute as queries de validação:

```sql
-- Verificar total de permissões inseridas
SELECT
    COUNT(*) as total_permissoes,
    COUNT(DISTINCT model_id) as total_usuarios
FROM model_has_permissions
WHERE model_type = 'App\Models\User';

-- Ver permissões de um usuário específico
SELECT
    u.name,
    u.email,
    p.name as permissao
FROM model_has_permissions mhp
INNER JOIN users u ON u.id = mhp.model_id
INNER JOIN permissions p ON p.id = mhp.permission_id
WHERE u.email = 'seu.email@carvalima.com.br'
AND mhp.model_type = 'App\Models\User'
ORDER BY p.name;
```

## 📊 Mapeamento de Controllers

O sistema mapeia automaticamente controllers do Mad Builder para permissões Laravel:

### Regra de Conversão:

```
Mad Builder Controller → Laravel Permission

Exemplos:
- VeiculoForm          → ver_veiculo, criar_veiculo
- AbastecimentoList    → ver_abastecimento, criar_abastecimento
- MotoristaFormView    → ver_motorista, criar_motorista
- VRelatorioReport     → ver_v_relatorio, criar_v_relatorio
```

### Processo:
1. Remove sufixos: Form, List, Header, Report, Document, Dashboard, View, Card
2. Converte CamelCase para snake_case
3. Adiciona prefixo `ver_` ou `criar_`

## 🔍 Permissões Órfãs

Permissões órfãs são controllers do Mad Builder que **não têm correspondência** no Laravel.

### Causas Comuns:
- Controllers antigos não migrados
- Funcionalidades descontinuadas
- Nomenclatura diferente no novo sistema

### O que fazer:
1. Revisar a lista de permissões órfãs gerada pelo script de análise
2. Para cada permissão órfã:
   - ✅ Se for funcionalidade descontinuada: ignorar
   - ✅ Se for funcionalidade renomeada: criar alias no Laravel
   - ✅ Se for funcionalidade nova no Laravel: criar as permissões manualmente

## ⚙️ Configuração DBLINK

Os scripts usam DBLINK para conectar ao banco antigo. Ajuste os parâmetros se necessário:

```sql
-- Linha de conexão padrão:
'hostaddr=10.10.1.14 port=5432 dbname=base_unitop_permission_carvalima user=postgres password=SisDBA2@2l'

-- Ajuste conforme necessário:
'hostaddr=SEU_IP port=PORTA dbname=NOME_BANCO user=USUARIO password=SENHA'
```

## 📌 Notas Importantes

### Sobre Grupos
- ❌ O Laravel **NÃO** terá mais grupos de permissões
- ✅ Todas as permissões que usuários tinham via grupo serão **individualizadas**
- ✅ O campo `origem` no log mostra se veio de grupo ou direta

### Sobre Usuários
- ✅ Apenas usuários **ativos** (`active = 'Y'`) no Mad Builder serão migrados
- ✅ Apenas usuários que **existem no Laravel** receberão permissões
- ✅ Mapeamento por email (prioridade) ou nome

### Sobre Conflitos
- ✅ O script usa `ON CONFLICT DO NOTHING` - não duplica permissões
- ✅ Se um usuário já tem a permissão, será logado como `SKIP_EXISTS`

## 🐛 Troubleshooting

### Erro: "extension dblink does not exist"
```sql
-- Execute como superuser:
CREATE EXTENSION IF NOT EXISTS dblink;
```

### Erro: "connection to server failed"
- Verifique os parâmetros de conexão DBLINK
- Verifique se o servidor remoto está acessível
- Verifique credenciais

### Permissões não aparecem no sistema
- Execute o comando para limpar cache:
```bash
php artisan cache:clear
php artisan permission:cache-reset
```

## ✅ Checklist de Execução

- [ ] Backup do banco Laravel
- [ ] Executar script de análise
- [ ] Revisar todos os relatórios
- [ ] Salvar lista de permissões órfãs
- [ ] Verificar usuários não migrados
- [ ] Executar script de migração (com ROLLBACK)
- [ ] Revisar relatórios finais
- [ ] Trocar ROLLBACK por COMMIT
- [ ] Executar novamente para efetivar
- [ ] Validar permissões no sistema
- [ ] Limpar cache do Laravel
- [ ] Testar acessos de alguns usuários

## 📞 Suporte

Em caso de dúvidas ou problemas, revisar:
1. Logs da tabela temporária `temp_migration_log`
2. Relatórios gerados pelos scripts
3. Arquivo `permissions_mad_builder.sql` para referência
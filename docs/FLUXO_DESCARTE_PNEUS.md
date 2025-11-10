# Fluxo de Descarte de Pneus - Sistema de Gestão de Frota

## Visão Geral

Este documento detalha o fluxo completo para descarte de pneus no sistema de gestão de frota, desde a identificação da necessidade até o registro final do descarte.

## Status Possíveis dos Pneus

O sistema trabalha com diferentes status de pneus ao longo do ciclo de vida:

- **ESTOQUE** - Pneu disponível no estoque
- **APLICADO** - Pneu aplicado em um veículo
- **DEPÓSITO** - Pneu em depósito
- **DIAGNÓSTICO** - Pneu aguardando diagnóstico 
- **EM MANUTENÇÃO** - Pneu em processo de manutenção/reforma
- **AGUARDANDO LAUDO** - Aguardando laudo técnico
- **TERCEIRO** - Pneu em posse de terceiros
- **TRANSFERÊNCIA** - Em processo de transferência
- **VENDIDO** - Pneu vendido
- **DESCARTE** - Pneu descartado (status final)

## Fluxo Principal para Descarte

### 1. Identificação da Necessidade de Descarte

Os pneus podem ser encaminhados para descarte através de diferentes fluxos:

#### 1.1 Através da Movimentação de Pneus
- **Controller:** `MovimentacaoPneusController` (`app/Http/Controllers/Admin/MovimentacaoPneusController.php`)
- **Função:** Quando um pneu é removido de um veículo, pode ser direcionado para descarte
- **Localização:** Linha 1102-1110 do `MovimentacaoPneusController`
- **Status:** Pneu status alterado para o destino escolhido (incluindo 'DESCARTE')

#### 1.2 Através do Envio e Recebimento
- **Controller:** `EnvioeRecebimento` (`app/Http/Controllers/Admin/EnvioeRecebimento.php`)
- **Função:** Gerencia envio de pneus para manutenção/diagnóstico
- **Status Inicial:** Pneus em status 'DIAGNÓSTICO' são candidatos ao processo

#### 1.3 Diretamente pelo Controle de Pneus
- **Controller:** `PneuController` (`app/Http/Controllers/Admin/PneuController.php`)
- **Função:** Gerenciamento geral dos pneus
- **Status:** Pneus podem ter status alterado manualmente

### 2. Processo de Descarte

#### 2.1 Tela Principal de Descarte
- **Rota:** `/admin/descartepneus` 
- **Controller:** `DescartePneuController` (`app/Http/Controllers/Admin/DescartePneuController.php`)
- **View:** `resources/views/admin/descartepneus/index.blade.php`

**Funcionalidades:**
- Listagem de descartes realizados
- Filtros por ID do pneu, tipo de descarte, data
- Paginação dos resultados
- Busca avançada

#### 2.2 Criação de Novo Descarte
- **Rota:** `/admin/descartepneus/criar`
- **Método:** `create()` do `DescartePneuController`
- **View:** `resources/views/admin/descartepneus/create.blade.php`

**Campos Obrigatórios:**
- **ID do Pneu:** Seleção de pneus disponíveis (status != 'APLICADO' e != 'DESCARTE')
- **Tipo de Descarte:** Seleção do tipo de descarte configurado
- **Valor de Venda:** Valor monetário (obrigatório)
- **Observação:** Descrição detalhada (máx 700 caracteres)
- **Arquivo/Laudo:** Upload opcional de documento (máx 2MB)

#### 2.3 Processamento do Descarte
- **Método:** `store()` do `DescartePneuController` (linha 92-157)
- **Processo:**
  1. Validação dos dados de entrada
  2. Upload de arquivo (se fornecido) para storage/public/laudos
  3. Início de transação no banco
  4. Criação do registro na tabela `descartepneu`
  5. Busca do tipo de descarte para histórico
  6. Atualização do status do pneu para 'DESCARTE'
  7. Criação de registro no `historicopneu`
  8. Commit da transação

## Estrutura de Dados

### 2.4 Tabela `descartepneu`
**Model:** `DescartePneu` (`app/Models/DescartePneu.php`)

**Campos principais:**
- `id_descarte_pneu` (PK)
- `id_pneu` (FK para tabela pneu)
- `id_tipo_descarte` (FK para tabela tipodescarte)
- `data_inclusao`
- `data_alteracao`
- `valor_venda_pneu`
- `observacao`
- `nome_arquivo` (path do arquivo upado)

### 2.5 Tabela `tipodescarte`
**Model:** `TipoDescarte` (`app/Models/TipoDescarte.php`)

**Campos:**
- `id_tipo_descarte` (PK)
- `descricao_tipo_descarte`
- `data_inclusao`
- `data_alteracao`

### 2.6 Tabela `historicopneu`
**Model:** `HistoricoPneu` (`app/Models/HistoricoPneu.php`)

**Registra a movimentação:**
- `id_pneu` (FK)
- `status_movimentacao` (recebe a descrição do tipo de descarte)
- `data_inclusao`
- `data_retirada`
- `origem_operacao` (AUTO_SAVE ou MANUAL)

## Funcionalidades Adicionais

### 3.1 Edição de Descarte
- **Rota:** `/admin/descartepneus/{id}/editar`
- **Método:** `edit()` e `update()` 
- **Função:** Permite alterar dados do descarte (exceto o pneu)

### 3.2 Exclusão de Descarte
- **Rota:** `DELETE /admin/descartepneus/{id}`
- **Método:** `destroy()`
- **Função:** Remove registro de descarte (não reverte status do pneu)

### 3.3 Controle de Permissões
- Utiliza traits `HasPermissions` para controle de acesso
- Logs de atividade através do trait `LogsActivity`

## Validações e Regras de Negócio

### 4.1 Validações no Create/Store
```php
'id_pneu' => 'required|integer',
'id_tipo_descarte' => 'required|integer', 
'valor_venda_pneu' => 'required',
'observacao' => 'required|string|max:700',
'nome_arquivo' => 'nullable|file|max:2048'
```

### 4.2 Pneus Elegíveis para Descarte
- Status diferente de 'APLICADO'
- Status diferente de 'DESCARTE' 
- Status diferente de 'EM MANUTENÇÃO' (apenas na edição)

### 4.3 Processamento de Arquivos
- Armazenamento em `storage/public/laudos`
- Extensões permitidas: conforme configuração Laravel
- Tamanho máximo: 2MB

## Relatórios e Consultas

### 5.1 Listagem Principal
- Filtros por ID do descarte, ID do pneu, tipo de descarte, data
- Ordenação por ID descrescente
- Paginação configurável (padrão: 10 por página)

### 5.2 Relacionamentos
- `DescartePneu` → `TipoDescarte` (belongsTo)
- `DescartePneu` → `Pneu` (belongsTo)
- `TipoDescarte` → `DescartePneu` (hasMany)

## Considerações Técnicas

### 6.1 Transações
- Todo processo de descarte utiliza transações de banco
- Rollback automático em caso de erro
- Logs de erro detalhados

### 6.2 Cache
- Models utilizam cache para otimização
- Limpeza automática em modificações

### 6.3 Sanitização
- Valores monetários sanitizados automaticamente
- Trait `SanitizesMonetaryValues` no controller

## Fluxo Resumido

```
1. Pneu Aplicado/Estoque/Outros Status
   ↓
2. Identificação da Necessidade (via MovimentacaoPneus ou manual)
   ↓  
3. Acesso à Tela de Descarte (/admin/descartepneus/criar)
   ↓
4. Preenchimento dos Dados:
   - Seleção do Pneu
   - Tipo de Descarte  
   - Valor de Venda
   - Observações
   - Upload de Laudo (opcional)
   ↓
5. Processamento (DescartePneuController@store):
   - Validação
   - Upload de arquivo
   - Criação registro descartepneu
   - Atualização status pneu → 'DESCARTE'
   - Criação histórico
   ↓
6. Pneu com Status 'DESCARTE' (status final)
```

## Arquivos Envolvidos

**Controllers:**
- `app/Http/Controllers/Admin/DescartePneuController.php` (principal)
- `app/Http/Controllers/Admin/PneuController.php` (gestão geral)
- `app/Http/Controllers/Admin/MovimentacaoPneusController.php` (movimentação)
- `app/Http/Controllers/Admin/EnvioeRecebimento.php` (envio/diagnóstico)

**Models:**
- `app/Models/DescartePneu.php`
- `app/Models/Pneu.php` 
- `app/Models/TipoDescarte.php`
- `app/Models/HistoricoPneu.php`

**Views:**
- `resources/views/admin/descartepneus/` (todas as views de descarte)

**Routes:**
- `routes/pneus.php` (linha 171-181 - rotas de descarte)
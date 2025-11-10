# Análise do Sistema de Permissões - Gestão de Frota

## 1. SITUAÇÃO ATUAL

### 1.1. Estrutura Existente
- **ORM**: Spatie Permission
- **Middleware**: `AutoPermissionMiddleware` - Funciona baseado em convenções
- **Controllers**: `PermissionController` (sync permissões)
- **Commands disponíveis**:
  - `permissions:sync-basic` - Sincroniza permissões básicas CRUD
  - `permissions:discover` - Descobre permissões (COM ERRO - coluna 'slug' não existe)
  - `permissions:fix-groups` - Corrige grupos
  - `permissions:update-names` - Atualiza nomes
  - `permissions:audit-controllers` - Audita controllers
  - `permissions:audit-views` - Audita views

### 1.2. Roles Atuais no Laravel
```
1  - Solicitante
2  - Aprovador de Solicitação
3  - Comprador
4  - Aprovador de Compra
5  - Aprovador de Compra Nível 1
6  - Aprovador de Compra Nível 2
7  - Aprovador de Compra Nível 3
8  - Aprovador de Compra Nível 4
9  - Almoxarife
10 - Gestor de Frota
11 - Administrador do Módulo Compras
33 - Aprovador de Requisição
34 - Equipe Qualidade
35 - Equipe Unitop
```

### 1.3. Grupos do Sistema Antigo (permissions_mad_builder.sql)
```
 1 - Admin
 2 - Standard
 3 - Abastecimento
 4 - Compras (JÁ MIGRADO)
 5 - Estoque
 6 - Gestão de Jornada
 7 - Gestão de Telemetria
 8 - Manutenção
 9 - Pessoas & Fornecedores
10 - Pneus
11 - Sinistro
12 - Veículo
13 - Configurações
14 - Solicitações (JÁ MIGRADO)
15 - Gestão de Viagem
16 - Relatórios Gerenciais
17 - Motoristas
18 - Pedágio
19 - Imobilizados
20 - Vencimentário (REMOVIDO)
21 - Financeiro
22 - Portaria
23 - Grupo Porteiros
25 - Pessoal Noite
26 - Testes
27 - Testes Unitop
28 - Saída de Veículos
29 - Estoque TI
30 - Prêmio Superação
31 - Compras Aprov/Valid (JÁ MIGRADO)
32 - Prêmio Carvalima
33 - Inventário Pneus
```

## 2. PROBLEMAS IDENTIFICADOS

### 2.1. Permissões Faltantes
- ❌ Comando `permissions:discover` tem erro de coluna 'slug'
- ❌ Algumas telas/módulos não têm permissões criadas
- ❌ Permissões especiais (ex: 'baixa_estoque') não foram criadas automaticamente
- ❌ Relatórios Jasper não têm permissões dedicadas

### 2.2. Middleware de Permissões
- ⚠️ Usuários relatam que só funciona com `is_superuser = true`
- ⚠️ Precisa validar se o `AutoPermissionMiddleware` está aplicado corretamente
- ⚠️ Verificar se PermissionHelper está funcionando corretamente

### 2.3. Roles do Sistema Antigo
- ❌ Faltam importar grupos do sistema antigo como roles do Laravel
- ✅ Grupos de Compras e Solicitações já foram migrados (elaborados)
- ❌ Outros grupos precisam ser importados seletivamente

## 3. PLANO DE AÇÃO

### 3.1. Sincronização de Permissões Básicas
- [X] Rodar `permissions:sync-basic` para criar permissões CRUD básicas
- [ ] Identificar módulos sem permissões
- [ ] Criar permissões especiais manualmente (baixa_estoque, etc)

### 3.2. Permissões de Relatórios Jasper
- [ ] Mapear todas as rotas de relatórios Jasper
- [ ] Criar permissões dedicadas por módulo (ex: relatorio_abastecimento)
- [ ] Atualizar middleware para reconhecer essas permissões

### 3.3. Importação de Roles
- [ ] Analisar quais grupos do sistema antigo devem ser importados
- [ ] Criar script SQL para inserir roles faltantes
- [ ] Mapear permissões adequadas para cada role

### 3.4. Correção do Middleware
- [ ] Debugar `AutoPermissionMiddleware`
- [ ] Validar funcionamento do `PermissionHelper`
- [ ] Testar com usuários não-superuser
- [ ] Adicionar suporte a permissões de relatórios

### 3.5. Correção do Command Discover
- [ ] Corrigir erro da coluna 'slug'
- [ ] Atualizar PermissionDiscoveryService

## 4. PERMISSÕES ESPECIAIS IDENTIFICADAS

### Estoque
- `baixa_estoque` - Dar baixa em itens do estoque
- `transferir_estoque` - Transferir entre estoques
- `ajustar_estoque` - Ajustar quantidades

### Manutenção
- `aprovar_os` - Aprovar ordens de serviço
- `finalizar_os` - Finalizar ordens de serviço

### Abastecimento
- `ajustar_km` - Ajustar KM de abastecimento
- `validar_abastecimento` - Validar abastecimentos

### Veículos
- `ativar_inativar_veiculo` - Ativar/Inativar veículos
- `alterar_km_manual` - Alterar KM manualmente

## 5. ESTRUTURA DE TABELAS SPATIE

```sql
- permissions (id, name, description, premission_group, guard_name)
- roles (id, name, guard_name)
- model_has_permissions (permission_id, model_type, model_id)
- model_has_roles (role_id, model_type, model_id)
- role_has_permissions (permission_id, role_id)
```

## 6. CONVENÇÕES DO MIDDLEWARE

### Padrão de Permissões
- `ver_{module}` - Visualizar/Listar
- `criar_{module}` - Criar novos
- `editar_{module}` - Editar existentes
- `excluir_{module}` - Excluir/Deletar

### Padrão Moderno (também suportado)
- `visualizar_{module}` - Visualizar/Listar
- `criar_{module}` - Criar novos
- `editar_{module}` - Editar existentes
- `excluir_{module}` - Excluir/Deletar

## 7. PRÓXIMOS PASSOS IMEDIATOS

1. ✅ Mapear estrutura atual
2. ⏳ Identificar permissões especiais necessárias
3. ⏳ Criar script SQL para roles do sistema antigo
4. ⏳ Mapear relatórios Jasper
5. ⏳ Corrigir middleware
6. ⏳ Gerar scripts SQL finais
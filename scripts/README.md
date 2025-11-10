# Scripts de Gerenciamento de Permissões

Esta pasta contém scripts utilitários para gerenciar o sistema de permissões do Laravel com Spatie Permission.

## Scripts Disponíveis

### 1. `criar_permissoes_recurso.php`

**Funcionalidade:** Cria permissões completas para um novo recurso do sistema.

**Uso:**

```bash
php scripts/criar_permissoes_recurso.php
```

**O que faz:**

-   Solicita nome do recurso (singular e plural)
-   Cria permissões para Policy (formato singular)
-   Cria permissões para Middleware (formato plural)
-   Opção para criar permissões extras (aprovar/rejeitar)
-   Valida se permissões já existem

**Exemplo:**

-   Recurso: `solicitacao_compra`
-   Plural: `solicitacoes`
-   Cria: `criar_solicitacao_compra`, `criar_solicitacoes`, etc.

### 2. `dar_permissoes_usuario.php`

**Funcionalidade:** Adiciona permissões a um usuário específico.

**Uso:**

```bash
php scripts/dar_permissoes_usuario.php
```

**O que faz:**

-   Lista usuários disponíveis
-   Mostra permissões atuais do usuário
-   Permite filtrar permissões disponíveis
-   Seleção múltipla de permissões
-   Confirmação antes de aplicar

**Recursos:**

-   Filtro por texto
-   Seleção por números ou 'all'
-   Mostra origem das permissões (direta/role)

### 3. `debug_permissoes_usuario.php`

**Funcionalidade:** Debug completo das permissões de um usuário.

**Uso:**

```bash
php scripts/debug_permissoes_usuario.php
```

**O que faz:**

-   Análise completa de permissões
-   Verifica superusuário
-   Lista roles e permissões diretas
-   Teste de permissão específica
-   Comparação entre métodos de verificação

**Ideal para:**

-   Troubleshooting de permissões
-   Entender por que acesso está sendo negado/permitido
-   Validar configurações

### 4. `remover_permissoes_usuario.php`

**Funcionalidade:** Remove permissões diretas de um usuário.

**Uso:**

```bash
php scripts/remover_permissoes_usuario.php
```

**O que faz:**

-   Lista permissões diretas do usuário
-   Permite seleção múltipla ou filtrada
-   Remove apenas permissões diretas (não via roles)
-   Confirmação de segurança
-   Mostra resultado final

**Recursos:**

-   Filtro por texto: `filter:criar`
-   Remoção seletiva ou total
-   Preserva permissões via roles

## Como Usar

### Pré-requisitos

-   Laravel configurado
-   Spatie Permission instalado
-   Scripts devem ser executados da raiz do projeto

### Fluxo Típico

1. **Criar novo recurso:**

    ```bash
    php scripts/criar_permissoes_recurso.php
    ```

2. **Dar permissões a usuários:**

    ```bash
    php scripts/dar_permissoes_usuario.php
    ```

3. **Verificar se está funcionando:**

    ```bash
    php scripts/debug_permissoes_usuario.php
    ```

4. **Remover permissões se necessário:**
    ```bash
    php scripts/remover_permissoes_usuario.php
    ```

## Convenções de Nomenclatura

### Permissões para Policy (Singular)

-   `criar_{recurso_singular}`
-   `editar_{recurso_singular}`
-   `visualizar_{recurso_singular}`
-   `excluir_{recurso_singular}`

### Permissões para Middleware (Plural)

-   `criar_{recurso_plural}`
-   `editar_{recurso_plural}`
-   `visualizar_{recurso_plural}`
-   `excluir_{recurso_plural}`

### Exemplos

| Recurso               | Policy                     | Middleware           |
| --------------------- | -------------------------- | -------------------- |
| Solicitação de Compra | `criar_solicitacao_compra` | `criar_solicitacoes` |
| Usuário               | `criar_usuario`            | `criar_usuarios`     |
| Produto               | `criar_produto`            | `criar_produtos`     |

## Troubleshooting

### Problema: "Permission does not exist"

-   Usar `criar_permissoes_recurso.php` para criar permissões
-   Verificar nome exato da permissão

### Problema: "Usuário tem acesso mas não deveria"

-   Verificar se é superusuário
-   Usar `debug_permissoes_usuario.php` para análise
-   Verificar permissões via roles

### Problema: "Cache não atualiza"

-   Scripts limpam cache automaticamente
-   Executar manualmente: `php artisan cache:clear`

### Problema: "Erro ao dar permissão"

-   Verificar se permissão existe no sistema
-   Verificar se usuário existe
-   Ver logs de erro detalhados

## Segurança

-   ⚠️ Scripts modificam banco de dados diretamente
-   ✅ Sempre fazem confirmação antes de alterações
-   ✅ Mostram preview das alterações
-   ✅ Limpam cache automaticamente
-   ⚠️ Usar apenas em ambiente de desenvolvimento/teste

## Logs e Monitoramento

Os scripts fornecem output detalhado:

-   ✓ Sucesso
-   ○ Já existia/sem alteração
-   ✗ Erro com detalhes

Sempre revise o output antes de prosseguir em produção.

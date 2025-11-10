# Documentação - Sistema de Permissões

## Visão Geral

O sistema utiliza o pacote **Spatie Permission** combinado com um sistema customizado de permissões que inclui:

-   **Políticas (Policies)** do Laravel
-   **Middleware customizado** (`AutoPermissionMiddleware`)
-   **Sistema de superusuário** customizado
-   **Cache de permissões**

## Estrutura do Sistema

### 1. Camadas de Autorização

O sistema possui múltiplas camadas que verificam permissões em ordem:

1. **Superusuário** - Bypassa todas as verificações
2. **Middleware** (`AutoPermissionMiddleware`) - Verifica permissões baseadas na rota
3. **Policies** - Verificações específicas no controller
4. **Permissões diretas** - Spatie Permission padrão

### 2. Componentes Principais

#### User Model

-   Método `hasPermission()` customizado que verifica superusuário primeiro
-   Integração com Spatie Permission
-   Cache de permissões

#### AutoPermissionMiddleware

-   Aplica-se automaticamente em rotas admin
-   Constrói nomes de permissões baseados na URL
-   Formato: `{ação}_{recurso}` (ex: `criar_solicitacoes`)

#### Policies

-   `SolicitacaoCompraPolicy` - Controla acesso a solicitações de compra
-   Usa `hasPermission()` para consistência com superusuário

## Como Adicionar Permissões

### Passo 1: Criar a Permissão

```php
// Via Tinker ou script
use Spatie\Permission\Models\Permission;

Permission::create(['name' => 'criar_solicitacao_compra']);
Permission::create(['name' => 'editar_solicitacao_compra']);
Permission::create(['name' => 'visualizar_solicitacao_compra']);
Permission::create(['name' => 'excluir_solicitacao_compra']);
```

### Passo 2: Identificar as Permissões Necessárias

Para cada funcionalidade, você precisa criar permissões para:

#### Middleware (AutoPermissionMiddleware)

-   **Padrão**: `{ação}_{recurso_plural}`
-   **Exemplos**:
    -   `admin/solicitacoes/create` → `criar_solicitacoes`
    -   `admin/compras/create` → `criar_compras`
    -   `admin/usuarios/edit` → `editar_usuarios`

#### Policy

-   **Padrão**: `{ação}_{recurso_singular}`
-   **Exemplos**:
    -   `criar_solicitacao_compra`
    -   `editar_solicitacao_compra`
    -   `visualizar_solicitacao_compra`

### Passo 3: Script para Criar Permissões Completas

```php
<?php
// create_permissions.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;

function criarPermissoesCompletas($recurso, $recursoPlural = null) {
    if (!$recursoPlural) {
        $recursoPlural = $recurso . 's';
    }

    $acoes = ['criar', 'editar', 'visualizar', 'excluir'];
    $acoesExtras = ['aprovar', 'rejeitar']; // Opcionais

    $permissoes = [];

    // Permissões para Policy (singular)
    foreach ($acoes as $acao) {
        $nome = "{$acao}_{$recurso}";
        $permissoes[] = $nome;

        try {
            Permission::firstOrCreate(['name' => $nome]);
            echo "✓ Criada: $nome\n";
        } catch (Exception $e) {
            echo "✗ Erro: $nome - " . $e->getMessage() . "\n";
        }
    }

    // Permissões para Middleware (plural)
    foreach ($acoes as $acao) {
        $nome = "{$acao}_{$recursoPlural}";
        $permissoes[] = $nome;

        try {
            Permission::firstOrCreate(['name' => $nome]);
            echo "✓ Criada: $nome\n";
        } catch (Exception $e) {
            echo "✗ Erro: $nome - " . $e->getMessage() . "\n";
        }
    }

    return $permissoes;
}

// Exemplo de uso
echo "=== CRIANDO PERMISSÕES PARA SOLICITAÇÃO DE COMPRA ===\n";
$permissoes = criarPermissoesCompletas('solicitacao_compra', 'solicitacoes');

echo "\n=== PERMISSÕES CRIADAS ===\n";
foreach ($permissoes as $perm) {
    echo "- $perm\n";
}
```

### Passo 4: Atribuir Permissões ao Usuário

```php
<?php
// give_permissions.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$userId = 17; // ID do usuário
$user = User::find($userId);

// Permissões para dar
$permissoes = [
    'criar_solicitacao_compra',
    'criar_solicitacoes',
    'editar_solicitacao_compra',
    'editar_solicitacoes',
    'visualizar_solicitacao_compra',
    'visualizar_solicitacoes',
];

foreach ($permissoes as $permissao) {
    try {
        $user->givePermissionTo($permissao);
        echo "✓ Permissão dada: $permissao\n";
    } catch (Exception $e) {
        echo "✗ Erro: $permissao - " . $e->getMessage() . "\n";
    }
}

// Limpar cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
echo "\n✓ Cache limpo\n";
```

## Verificação de Permissões

### Script de Verificação

```php
<?php
// check_permissions.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$userId = 17;
$user = User::find($userId);

echo "=== VERIFICAÇÃO DE PERMISSÕES ===\n";
echo "Usuário: {$user->name}\n";
echo "Superusuário: " . ($user->is_superuser ? 'SIM' : 'NÃO') . "\n\n";

$permissoesParaTestar = [
    'criar_solicitacao_compra',
    'criar_solicitacoes',
    'editar_solicitacao_compra',
    'visualizar_solicitacao_compra',
];

foreach ($permissoesParaTestar as $permissao) {
    $tem = $user->hasPermissionTo($permissao);
    echo "Permissão '$permissao': " . ($tem ? '✓ TEM' : '✗ NÃO TEM') . "\n";
}

echo "\n=== PERMISSÕES DIRETAS ===\n";
$permissoesUser = $user->getDirectPermissions();
foreach ($permissoesUser as $perm) {
    echo "- {$perm->name}\n";
}

echo "\n=== PERMISSÕES VIA ROLES ===\n";
$permissoesRoles = $user->getPermissionsViaRoles();
foreach ($permissoesRoles as $perm) {
    echo "- {$perm->name}\n";
}
```

## Estrutura de Permissões Recomendada

### Convenção de Nomenclatura

| Tipo       | Formato                     | Exemplo                    | Uso                  |
| ---------- | --------------------------- | -------------------------- | -------------------- |
| Policy     | `{ação}_{recurso_singular}` | `criar_solicitacao_compra` | Controllers/Policies |
| Middleware | `{ação}_{recurso_plural}`   | `criar_solicitacoes`       | Rotas automáticas    |

### Ações Padrão

-   **criar** - Criar novos registros
-   **editar** - Modificar registros existentes
-   **visualizar** - Ver/listar registros
-   **excluir** - Deletar registros
-   **aprovar** - Aprovar solicitações
-   **rejeitar** - Rejeitar solicitações

## Debugging de Permissões

### Script de Debug Completo

```php
<?php
// debug_permissions.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

function debugPermissions($userId, $permissao) {
    $user = User::find($userId);

    echo "=== DEBUG PERMISSÃO: $permissao ===\n";
    echo "Usuário: {$user->name} (ID: $userId)\n";

    // 1. Verificar superusuário
    echo "\n1. SUPERUSUÁRIO:\n";
    echo "   is_superuser: " . ($user->is_superuser ? 'SIM' : 'NÃO') . "\n";

    // 2. Verificar método customizado
    echo "\n2. MÉTODO CUSTOMIZADO (hasPermission):\n";
    $hasCustom = $user->hasPermission($permissao);
    echo "   hasPermission('$permissao'): " . ($hasCustom ? 'SIM' : 'NÃO') . "\n";

    // 3. Verificar Spatie padrão
    echo "\n3. SPATIE PADRÃO:\n";
    $hasSpatie = $user->hasPermissionTo($permissao);
    echo "   hasPermissionTo('$permissao'): " . ($hasSpatie ? 'SIM' : 'NÃO') . "\n";

    // 4. Verificar can() do Laravel
    echo "\n4. LARAVEL CAN:\n";
    $canLaravel = $user->can($permissao);
    echo "   can('$permissao'): " . ($canLaravel ? 'SIM' : 'NÃO') . "\n";

    // 5. Verificar permissões diretas
    echo "\n5. PERMISSÕES DIRETAS:\n";
    $diretas = $user->getDirectPermissions()->pluck('name')->toArray();
    $temDireta = in_array($permissao, $diretas);
    echo "   Tem direta: " . ($temDireta ? 'SIM' : 'NÃO') . "\n";

    // 6. Verificar via roles
    echo "\n6. PERMISSÕES VIA ROLES:\n";
    $viaRoles = $user->getPermissionsViaRoles()->pluck('name')->toArray();
    $temViaRole = in_array($permissao, $viaRoles);
    echo "   Tem via role: " . ($temViaRole ? 'SIM' : 'NÃO') . "\n";

    echo "\n" . str_repeat('=', 50) . "\n";
}

// Exemplo de uso
debugPermissions(17, 'criar_solicitacao_compra');
debugPermissions(17, 'criar_solicitacoes');
```

## Troubleshooting

### Problemas Comuns

#### 1. "Acesso negado mesmo com permissão"

-   Verificar se a permissão existe no banco
-   Verificar se o usuário tem a permissão (direta ou via role)
-   Limpar cache de permissões
-   Verificar se há permissões conflitantes (middleware vs policy)

#### 2. "Cache não atualiza"

```php
// Limpar cache manualmente
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Ou via Artisan
php artisan cache:clear
php artisan config:clear
```

#### 3. "Múltiplas camadas bloqueando"

-   Verificar middleware `AutoPermissionMiddleware`
-   Verificar policy correspondente
-   Verificar se permissões têm nomes corretos (singular vs plural)

### Scripts de Manutenção

#### Remover Todas as Permissões de um Usuário

```php
$user = User::find($userId);
$user->syncPermissions([]); // Remove todas as permissões diretas
```

#### Listar Todas as Permissões Disponíveis

```php
use Spatie\Permission\Models\Permission;

$permissions = Permission::all()->pluck('name')->sort();
foreach ($permissions as $perm) {
    echo "- $perm\n";
}
```

## Checklist para Nova Funcionalidade

-   [ ] Identificar o recurso (ex: `solicitacao_compra`)
-   [ ] Criar permissões para policy (singular)
-   [ ] Criar permissões para middleware (plural)
-   [ ] Atualizar policy se necessário
-   [ ] Testar com usuário sem permissão (deve bloquear)
-   [ ] Testar com usuário com permissão (deve permitir)
-   [ ] Verificar se superusuário funciona
-   [ ] Limpar cache de permissões
-   [ ] Documentar as novas permissões

## Notas Importantes

1. **Superusuários** sempre têm acesso, independente das permissões
2. **Cache** pode causar problemas - sempre limpe após mudanças
3. **Nomenclatura** é crítica - middleware usa plural, policy usa singular
4. **Múltiplas camadas** podem conflitar - teste ambas separadamente
5. **Verificações** devem usar `hasPermission()` para consistência

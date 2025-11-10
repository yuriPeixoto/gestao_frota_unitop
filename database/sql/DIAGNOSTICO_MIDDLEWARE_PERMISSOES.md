# Diagnóstico do Middleware de Permissões

## PROBLEMA RELATADO
"O middleware de permissões parece não estar funcionando a não ser se eu atribua is_superuser pros users"

## ANÁLISE DO CÓDIGO ATUAL

### 1. Middleware Registrado e Aplicado
✅ O middleware `AutoPermissionMiddleware` está:
- Registrado em `bootstrap/app.php` como `'auto.permission'`
- Aplicado em `routes/web.php` nas rotas admin: `middleware' => ['auth', '2fa', 'auto.permission']`

### 2. Verificação de Superuser COMENTADA
❌ **PROBLEMA CRÍTICO IDENTIFICADO**: No arquivo `AutoPermissionMiddleware.php` (linhas 122-128), o código que libera acesso para superusers está **COMENTADO**:

```php
// Superuser sempre tem acesso
// if ($user->isSuperuser()) {
//     Log::info('AutoPermissionMiddleware: Usuário é superuser, acesso liberado', [
//         'user_id' => $user->id
//     ]);
//     return $next($request);
// }
```

**ISSO SIGNIFICA QUE ATUALMENTE O MIDDLEWARE NÃO RECONHECE SUPERUSERS!**

### 3. PermissionHelper Funcional
✅ O `PermissionHelper` está funcionando corretamente:
- Todos os métodos verificam `isSuperuser()` antes de checar permissões específicas
- Métodos:
  - `hasModuleAccess()` - Verifica acesso a módulos
  - `hasAnyPermission()` - Verifica lista de permissões
  - `hasAnyPermissionStartingWith()` - Verifica por prefixo
  - `hasAnyViewPermission()` - Verifica permissões de visualização

### 4. Fluxo de Verificação do Middleware

O middleware segue esta sequência:
1. ~~Verifica se é superuser (COMENTADO - NÃO FUNCIONA)~~
2. Verifica se rota deve ser ignorada
3. Extrai informações da rota (módulo + ação)
4. Verifica permissões em 5 níveis:
   - Mapeamento moderno (ex: `visualizar_solicitacao_compra`)
   - Mapeamento legacy (ex: `ver_solicitacaocompras`)
   - Permissão original (ex: `ver_solicitacoes`)
   - Acesso ao módulo (via `hasModuleAccess`)
   - Prefixo do módulo (via `hasAnyPermissionStartingWith`)

## DIAGNÓSTICO FINAL

### Problemas Identificados:

1. **CRÍTICO**: Verificação de superuser comentada no middleware (linha 122-128)
   - **Impacto**: Superusers não conseguem acessar sem permissões explícitas
   - **Solução**: Descomentar o código

2. **MÉDIO**: Logs de debug comentados
   - **Impacto**: Dificulta troubleshooting
   - **Solução**: Criar variável de ambiente para ativar logs quando necessário

3. **BAIXO**: Permissões especiais não mapeadas
   - **Impacto**: Ações como "baixar_estoque" não são reconhecidas automaticamente
   - **Solução**: Adicionar mapeamento de ações especiais no middleware

## SOLUÇÕES PROPOSTAS

### Solução Imediata: Descomentar Verificação de Superuser

```php
// Superuser sempre tem acesso
if ($user->isSuperuser()) {
    Log::info('AutoPermissionMiddleware: Usuário é superuser, acesso liberado', [
        'user_id' => $user->id
    ]);
    return $next($request);
}
```

### Solução Adicional: Adicionar Logs Condicionais

Adicionar no início da classe:
```php
private function shouldLog(): bool
{
    return config('app.debug_permissions', false);
}
```

E substituir logs comentados por:
```php
if ($this->shouldLog()) {
    Log::info('AutoPermissionMiddleware: mensagem...', []);
}
```

### Solução Complementar: Mapear Ações Especiais

Adicionar ao array `ACTION_MAPPING`:
```php
private const ACTION_MAPPING = [
    'GET' => [
        'index' => 'ver',
        'show' => 'ver',
        'create' => 'criar',
        'edit' => 'editar',
    ],
    'POST' => [
        'store' => 'criar',
        'baixar' => 'baixar',          // NOVO
        'baixarLote' => 'baixar',      // NOVO
        'aprovar' => 'aprovar',        // NOVO
        'reprovar' => 'reprovar',      // NOVO
        'finalizar' => 'finalizar',    // NOVO
    ],
    // ... resto do código
];
```

## PASSOS PARA CORREÇÃO

1. ✅ Descomentar verificação de superuser
2. ✅ Adicionar sistema de logs condicionais
3. ✅ Adicionar mapeamento de ações especiais
4. ✅ Testar com usuário superuser
5. ✅ Testar com usuário comum com permissões
6. ✅ Testar com usuário comum sem permissões

## TESTE RECOMENDADO

Após aplicar correções, testar com 3 tipos de usuários:

**Usuário 1: Superuser** (is_superuser = true)
- Deve ter acesso a TUDO sem necessidade de permissões específicas

**Usuário 2: Com Permissões** (is_superuser = false, com permissões atribuídas)
- Deve ter acesso apenas aos módulos com permissões

**Usuário 3: Sem Permissões** (is_superuser = false, sem permissões)
- Deve receber erro 403 ao tentar acessar qualquer módulo admin

## COMANDOS ÚTEIS PARA DEBUG

```bash
# Ver permissões de um usuário específico
php artisan tinker
>>> $user = User::find(ID);
>>> $user->getAllPermissions()->pluck('name');
>>> PermissionHelper::debugUserPermissions();

# Limpar cache de permissões
php artisan permission:cache-reset

# Sincronizar permissões básicas
php artisan permissions:sync-basic

# Auditar controllers
php artisan permissions:audit-controllers
```
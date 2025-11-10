# 🔐 SISTEMA DE PERMISSÕES - GUIA COMPLETO DE APLICAÇÃO

## 📋 ÍNDICE
1. [Resumo Executivo](#resumo-executivo)
2. [Problemas Identificados e Solucionados](#problemas-identificados-e-solucionados)
3. [Arquivos Gerados](#arquivos-gerados)
4. [Ordem de Execução](#ordem-de-execução)
5. [Validação e Testes](#validação-e-testes)
6. [Troubleshooting](#troubleshooting)

---

## 📊 RESUMO EXECUTIVO

### Trabalho Realizado
✅ Mapeamento completo do sistema de permissões atual
✅ Identificação do problema crítico no middleware (superuser comentado)
✅ Correção do `AutoPermissionMiddleware`
✅ Adição de suporte a ações especiais (baixar, aprovar, relatorio, etc)
✅ Criação de script SQL para importar roles do sistema antigo
✅ Criação de script SQL para permissões especiais e relatórios
✅ Documentação completa do sistema

### Próximos Passos
1. Aplicar correções do middleware (já feitas no código)
2. Rodar command de sync de permissões básicas
3. Executar scripts SQL no DBeaver (roles e permissões especiais)
4. Testar com usuários diferentes
5. Atribuir permissões às roles conforme necessário

---

## 🔍 PROBLEMAS IDENTIFICADOS E SOLUCIONADOS

### 1. **CRÍTICO: Verificação de Superuser Comentada**
**Problema**: O código que libera acesso para superusers estava comentado no middleware
**Impacto**: Usuários com `is_superuser = true` não conseguiam acessar sem permissões explícitas
**Solução**: Descomentada a verificação (linha 138-144 do `AutoPermissionMiddleware.php`)

```php
// ANTES (comentado - NÃO FUNCIONAVA):
// if ($user->isSuperuser()) {
//     return $next($request);
// }

// DEPOIS (corrigido - FUNCIONA):
if ($user->isSuperuser()) {
    return $next($request);
}
```

### 2. **Ações Especiais Não Mapeadas**
**Problema**: Ações como "baixar", "aprovar", "relatorio" não eram reconhecidas
**Impacto**: Permissões especiais não funcionavam automaticamente
**Solução**: Adicionadas 11 novas ações ao `ACTION_MAPPING`:
- baixar, baixarLote, baixarItens, baixarItensUnificado, etc
- aprovar, reprovar, finalizar, cancelar, reabrir
- validar, transferir, ajustar
- report (para relatórios)

### 3. **Roles do Sistema Antigo Não Importadas**
**Problema**: Grupos do sistema antigo não existiam como roles no Laravel
**Impacto**: Impossível migrar permissões do sistema antigo
**Solução**: Script SQL `001_importar_roles_sistema_antigo.sql` criado

### 4. **Permissões Especiais Inexistentes**
**Problema**: Permissões como "baixar_estoque" não existiam no banco
**Impacto**: Não era possível atribuir permissões granulares
**Solução**: Script SQL `002_permissoes_especiais.sql` com 70+ permissões

---

## 📁 ARQUIVOS GERADOS

### Documentação
1. **ANALISE_PERMISSOES.md** - Análise completa do sistema
2. **DIAGNOSTICO_MIDDLEWARE_PERMISSOES.md** - Diagnóstico detalhado do problema
3. **README_PERMISSOES.md** (este arquivo) - Guia de aplicação

### Scripts SQL (Rodar no DBeaver)
1. **001_importar_roles_sistema_antigo.sql** - Importa 26 roles
2. **002_permissoes_especiais.sql** - Cria 70+ permissões especiais

### Código Atualizado (Laravel)
1. **app/Http/Middleware/AutoPermissionMiddleware.php** - Corrigido e melhorado

---

## ⚙️ ORDEM DE EXECUÇÃO

### PASSO 1: Aplicar Correções no Código (JÁ FEITO ✅)
As correções no `AutoPermissionMiddleware.php` já foram aplicadas:
- ✅ Verificação de superuser descomentada
- ✅ Ações especiais adicionadas ao mapeamento
- ✅ Suporte a relatórios ('report') adicionado

### PASSO 2: Sincronizar Permissões Básicas

```bash
# Este comando vai criar permissões CRUD para todos os controllers
# Pode demorar alguns minutos (timeout é esperado, mas continua funcionando)
php artisan permissions:sync-basic

# OU rodar em background se preferir
php artisan permissions:sync-basic > sync_log.txt 2>&1 &
```

**IMPORTANTE**: O comando pode dar timeout, mas isso é normal. Ele continua criando permissões no banco.

### PASSO 3: Executar Scripts SQL no DBeaver

#### 3.1. Importar Roles
```sql
-- Abrir arquivo: database/sql/001_importar_roles_sistema_antigo.sql
-- Revisar roles que serão criadas
-- Executar o script completo (BEGIN ... COMMIT)
```

**Roles que serão criadas:**
- 36 - Equipe Abastecimento
- 37 - Equipe Estoque
- 38 - Equipe Gestão de Jornada
- ... (26 roles no total)

#### 3.2. Criar Permissões Especiais
```sql
-- Abrir arquivo: database/sql/002_permissoes_especiais.sql
-- Revisar permissões que serão criadas
-- Executar o script completo (BEGIN ... COMMIT)
```

**Permissões principais:**
- Estoque: baixar_estoque, transferir_estoque, ajustar_estoque
- Pneus: baixar_pneu, movimentar_pneu, calibrar_pneu
- Veículos: ativar_inativar_veiculo, alterar_km_manual
- Abastecimento: ajustar_km_abastecimento, validar_abastecimento
- Manutenção: aprovar_os, finalizar_os, cancelar_os
- Relatórios: 30+ permissões de relatórios por módulo

### PASSO 4: Limpar Cache

```bash
# Limpar cache de permissões do Spatie
php artisan permission:cache-reset

# Limpar cache geral
php artisan cache:clear
php artisan config:clear
```

---

## ✅ VALIDAÇÃO E TESTES

### Teste 1: Usuário Superuser
```bash
# Criar usuário de teste superuser
php artisan tinker
>>> $user = User::find(SEU_USER_ID);
>>> $user->is_superuser = true;
>>> $user->save();
>>> exit

# Testar: Deve ter acesso a TUDO sem permissões específicas
```

### Teste 2: Usuário com Permissões
```bash
php artisan tinker
>>> $user = User::find(USER_ID);
>>> $user->is_superuser = false;
>>> $user->givePermissionTo('ver_estoque');
>>> $user->givePermissionTo('baixar_estoque');
>>> exit

# Testar: Deve acessar /admin/estoque e conseguir baixar itens
#         NÃO deve acessar outros módulos
```

### Teste 3: Usuário sem Permissões
```bash
php artisan tinker
>>> $user = User::find(USER_ID);
>>> $user->is_superuser = false;
>>> $user->syncPermissions([]); // Remove todas
>>> exit

# Testar: Deve receber 403 Forbidden em qualquer módulo admin
```

### Teste 4: Verificar Permissões de um Usuário
```bash
php artisan tinker
>>> $user = User::find(USER_ID);
>>> $user->getAllPermissions()->pluck('name')->toArray();
>>> PermissionHelper::debugUserPermissions();
```

---

## 🔧 TROUBLESHOOTING

### Problema: "403 Forbidden" para Superusers
**Solução**: Verificar se a correção foi aplicada:
```bash
grep -n "if (\$user->isSuperuser())" app/Http/Middleware/AutoPermissionMiddleware.php
# Deve mostrar linha 139 SEM comentário
```

### Problema: Permissões não funcionam após atribuir
**Solução**: Limpar cache
```bash
php artisan permission:cache-reset
```

### Problema: "Permission does not exist"
**Solução**: Sincronizar permissões básicas
```bash
php artisan permissions:sync-basic
```

### Problema: Ação especial não é reconhecida (ex: baixar)
**Solução**: Verificar se ACTION_MAPPING foi atualizado
```bash
grep -A 20 "ACTION_MAPPING" app/Http/Middleware/AutoPermissionMiddleware.php
# Deve mostrar "baixar", "aprovar", etc
```

### Problema: Command discover dá erro "coluna slug"
**Solução**: Usar `permissions:sync-basic` no lugar:
```bash
php artisan permissions:sync-basic
```

### Debug de Permissões
Para ativar logs detalhados temporariamente, descomentar linhas de Log::info no middleware:
```php
// Exemplo de log para debug (linhas 129-136, 150-168, etc)
Log::info('AutoPermissionMiddleware::handle - INÍCIO', [
    'user_id' => $user->id,
    'url' => $request->url(),
    // ...
]);
```

---

## 📝 COMANDOS ÚTEIS

### Permissões
```bash
# Listar todas as permissões
php artisan permission:show

# Sincronizar permissões básicas
php artisan permissions:sync-basic

# Corrigir grupos de permissões
php artisan permissions:fix-groups

# Atualizar nomes de permissões
php artisan permissions:update-names

# Auditar controllers
php artisan permissions:audit-controllers

# Auditar views
php artisan permissions:audit-views
```

### Cache
```bash
# Limpar cache de permissões
php artisan permission:cache-reset

# Limpar todos os caches
php artisan optimize:clear
```

### Database
```bash
# Ver roles atuais
php artisan tinker
>>> Spatie\Permission\Models\Role::all()->pluck('name', 'id');

# Ver permissões de uma role
>>> $role = Spatie\Permission\Models\Role::find(ID);
>>> $role->permissions->pluck('name');

# Atribuir permissão a role
>>> $role->givePermissionTo('nome_da_permissao');
```

---

## 🎯 PRÓXIMAS AÇÕES RECOMENDADAS

### Curto Prazo (Fazer Agora)
1. ✅ Rodar `permissions:sync-basic`
2. ✅ Executar `001_importar_roles_sistema_antigo.sql` no DBeaver
3. ✅ Executar `002_permissoes_especiais.sql` no DBeaver
4. ✅ Limpar cache com `permission:cache-reset`
5. ✅ Testar com um usuário superuser
6. ✅ Testar com um usuário comum com permissões

### Médio Prazo (Próxima Semana)
1. Mapear quais roles precisam de quais permissões
2. Criar scripts de atribuição em massa (role_has_permissions)
3. Criar interface administrativa para gerenciar permissões
4. Documentar permissões customizadas por setor

### Longo Prazo (Próximo Mês)
1. Revisar e consolidar roles duplicadas
2. Criar grupos de permissões (permission groups)
3. Implementar auditoria de permissões
4. Criar relatório de uso de permissões

---

## 📞 SUPORTE

Se encontrar problemas:
1. Verificar logs em `storage/logs/laravel.log`
2. Consultar este README
3. Consultar `DIAGNOSTICO_MIDDLEWARE_PERMISSOES.md`
4. Usar `PermissionHelper::debugUserPermissions()` no tinker

---

**Data de Criação**: 2025-10-07
**Versão**: 1.0
**Autor**: Claude Code (Anthropic)
**Sistema**: Gestão de Frota Carvalima
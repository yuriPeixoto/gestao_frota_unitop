# Passos para Migração do Sistema de Permissões

## ✅ O que está PRONTO:

1. **✅ ModulePermissionService** - Estrutura completa de módulos e permissões
2. **✅ ModulePermissionsSeeder** - Migração automática com backup
3. **✅ PermissionHelper** - Atualizado para novo sistema
4. **✅ SyncModulePermissions** - Comando para sincronizar
5. **✅ Documentação completa** - SISTEMA-PERMISSOES-POR-MODULO.md
6. **✅ Menu de Abastecimentos** - Já atualizado como exemplo

---

## ⚠️ O que FALTA FAZER:

### 1. Executar a Migração

```bash
# IMPORTANTE: Fazer em ambiente de desenvolvimento PRIMEIRO!
php artisan db:seed --class=ModulePermissionsSeeder
```

**O que vai acontecer:**
- ✅ Backup em `database/backups/permissions_backup_[data].json`
- ✅ Limpar permissões antigas
- ✅ Criar ~200+ novas permissões organizadas
- ✅ Migrar permissões dos usuários automaticamente
- ✅ Criar cargo "Administrador" com todas as permissões

---

### 2. Atualizar os Menus (VIEWS)

**Já atualizado:** `components/menus/abastecimentos.blade.php` ✅

**Faltam atualizar:** (use abastecimentos como modelo)

#### Padrão de atualização:

**ANTES:**
```php
@can('ver_veiculo')
```

**DEPOIS:**
```php
@can('veiculos.cadastro.visualizar')
```

#### Lista de arquivos:

- `components/menus/compras.blade.php`
- `components/menus/configuracoes.blade.php`
- `components/menus/checklist.blade.php`
- `components/menus/estoque.blade.php`
- `components/menus/imobilizados.blade.php`
- `components/menus/manutencao.blade.php`
- `components/menus/pessoal.blade.php`
- `components/menus/pneus.blade.php`
- `components/menus/sinistros.blade.php`
- `components/menus/veiculos.blade.php`
- `components/menus/multas.blade.php`
- `components/menus/certificados.blade.php`

#### Mapeamento rápido (exemplos):

| Módulo | Permissão Antiga | Permissão Nova |
|--------|-----------------|----------------|
| Veículos | `ver_veiculo` | `veiculos.cadastro.visualizar` |
| Veículos | `criar_veiculo` | `veiculos.cadastro.criar` |
| Veículos | `editar_veiculo` | `veiculos.cadastro.editar` |
| Veículos | `ver_multa` | `veiculos.multas.visualizar` |
| Veículos | `ver_licenciamentoveiculo` | `veiculos.licencas.visualizar` |
| Pneus | `ver_pneu` | `pneus.cadastro.visualizar` |
| Pneus | `criar_pneu` | `pneus.cadastro.criar` |
| Pneus | `ver_descartepneus` | `pneus.baixa.visualizar` |
| Pneus | `ver_transferenciapneus` | `pneus.transferencia.visualizar` |
| Pneus | `ver_requisicaopneu` | `pneus.venda.visualizar` |
| Manutenção | `ver_ordemservico` | `manutencao.ordem_servico.visualizar` |
| Pessoal | `ver_motorista` | `pessoal.motoristas.visualizar` |
| Pessoal | `ver_funcionario` | `pessoal.funcionarios.visualizar` |
| Estoque | `ver_produto` | `estoque.produtos.visualizar` |
| Estoque | `ver_movimentacao` | `estoque.movimentacao.visualizar` |
| Compras | `ver_solicitacaocompras` | `compras.dashboard.visualizar` |
| Configurações | `ver_user` | `configuracoes.usuarios.visualizar` |
| Configurações | `ver_fornecedor` | `configuracoes.fornecedores.visualizar` |
| Sinistros | `ver_sinistro` | `sinistro.gerenciar.visualizar` |

**⚡ DICA:** Veja todas as permissões disponíveis em: `app/Services/ModulePermissionService.php`

---

### 3. Remover Fallbacks do PermissionHelper (DEPOIS de atualizar todas as views)

Atualmente o `PermissionHelper` tem fallbacks para compatibilidade. Após atualizar TODAS as views, remova:

**Arquivo:** `app/Helpers/PermissionHelper.php`

**Remover estas linhas** (linhas 47-56):
```php
// 3. Fallback para permissões antigas (compatibilidade temporária)
if ($user->can("acessar_{$module}")) {
    return true;
}

foreach ($userPermissions as $permission) {
    if (str_contains($permission, $module)) {
        return true;
    }
}
```

---

### 4. Atualizar Middleware (OPCIONAL - se necessário)

O `AutoPermissionMiddleware` já tem fallbacks, mas você pode otimizá-lo depois.

**Arquivo:** `app/Http/Middleware/AutoPermissionMiddleware.php`

Nas linhas 300-402, remover os fallbacks 2-5 e deixar apenas o teste com o novo formato.

---

## 📝 Resumo do que VOCÊ precisa fazer:

### Passo 1: Testar em DEV
```bash
php artisan db:seed --class=ModulePermissionsSeeder
```

### Passo 2: Atualizar Views
- Use `abastecimentos.blade.php` como modelo
- Substitua `@can('ver_X')` por `@can('modulo.funcionalidade.acao')`
- Liste: ~13 arquivos de menu

### Passo 3: Testar Permissões
- Criar usuário teste
- Dar permissões específicas
- Verificar se menus aparecem corretamente

### Passo 4: Limpar Código
- Remover fallbacks do `PermissionHelper`
- (Opcional) Otimizar `AutoPermissionMiddleware`

---

## 🔍 Como Descobrir a Permissão Correta?

### Opção 1: Ver no ModulePermissionService
Abra: `app/Services/ModulePermissionService.php`

Procure pelo módulo (ex: `'veiculos'`) e veja todas as permissões disponíveis.

### Opção 2: Listar Permissões no Terminal
```bash
php artisan tinker
```

```php
use App\Services\ModulePermissionService;

// Ver módulos
ModulePermissionService::getModules();

// Ver permissões de um módulo
ModulePermissionService::getModulePermissions('veiculos');

// Ver TODAS as permissões geradas
$perms = ModulePermissionService::generateAllPermissions();
foreach($perms as $p) {
    echo $p['nome'] . " - " . $p['nome_amigavel'] . "\n";
}
```

### Opção 3: Verificar no Banco (após migração)
```sql
SELECT name FROM permissions WHERE name LIKE 'veiculos.%' ORDER BY name;
```

---

## 🚨 IMPORTANTE:

1. **SEMPRE faça backup antes** (o seeder já faz automaticamente)
2. **Teste em DEV primeiro**
3. **Não delete permissões manualmente** - use o seeder
4. **Mantenha os fallbacks** até atualizar todas as views
5. **Documente permissões customizadas** se adicionar novas

---

## 🎯 Exemplo Completo - Menu de Veículos

```php
<!-- ANTES -->
@can('ver_veiculo')
    <a href="{{ route('admin.veiculos.index') }}">
        Cadastro de Veículos
    </a>
@endcan

<!-- DEPOIS -->
@can('veiculos.cadastro.visualizar')
    <a href="{{ route('admin.veiculos.index') }}">
        Cadastro de Veículos
    </a>
@endcan
```

---

## ✅ Checklist Final

- [ ] Executar seeder em DEV
- [ ] Verificar backup criado
- [ ] Atualizar menu compras
- [ ] Atualizar menu configurações
- [ ] Atualizar menu checklist
- [ ] Atualizar menu estoque
- [ ] Atualizar menu imobilizados
- [ ] Atualizar menu manutenção
- [ ] Atualizar menu pessoal
- [ ] Atualizar menu pneus
- [ ] Atualizar menu sinistros
- [ ] Atualizar menu veículos
- [ ] Atualizar menu multas
- [ ] Atualizar menu certificados
- [ ] Testar com usuário não-admin
- [ ] Remover fallbacks do PermissionHelper
- [ ] Executar em PRODUÇÃO

---

**DÚVIDAS?** Consulte: `docs/SISTEMA-PERMISSOES-POR-MODULO.md`

**Criado em:** 2025-06-01
**Status:** Pronto para execução
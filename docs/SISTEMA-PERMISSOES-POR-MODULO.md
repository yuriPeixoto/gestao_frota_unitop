# Sistema de Permissões por Módulo

## 📋 Visão Geral

Este documento descreve o novo sistema de permissões baseado em módulos, que substitui o sistema antigo de permissões por controller.

### Benefícios do Novo Sistema

✅ **Organização por Módulos** - Permissões agrupadas por módulo funcional (Abastecimentos, Veículos, Pneus, etc)
✅ **Nomenclatura Amigável** - Nomes intuitivos para usuários finais
✅ **Hierarquia Clara** - Permissão de acesso ao módulo + permissões granulares
✅ **Fácil Gerenciamento** - Interface organizada e intuitiva
✅ **Escalável** - Fácil adicionar novos módulos e permissões

---

## 🏗️ Estrutura

### Formato das Permissões

As permissões seguem o padrão hierárquico:

```
{modulo}.{funcionalidade}.{acao}
```

**Exemplos:**
- `abastecimentos.acessar_modulo` - Acesso básico ao módulo
- `abastecimentos.abastecimento_manual.visualizar` - Ver abastecimentos manuais
- `abastecimentos.abastecimento_manual.criar` - Criar abastecimentos manuais
- `veiculos.cadastro.editar` - Editar veículos
- `pneus.venda.aprovar` - Aprovar vendas de pneus

### Módulos Disponíveis

1. **Abastecimentos** (`abastecimentos`)
2. **Compras** (`compras`)
3. **Configurações** (`configuracoes`)
4. **Checklist** (`checklist`)
5. **Estoque** (`estoque`)
6. **Imobilizados** (`imobilizados`)
7. **Manutenção** (`manutencao`)
8. **Pessoal** (`pessoal`)
9. **Pneus** (`pneus`)
10. **Sinistros** (`sinistro`)
11. **Veículos** (`veiculos`)

---

## 🚀 Instalação e Migração

### Passo 1: Backup Automático

O sistema faz backup automático das permissões antes da migração:
- Arquivo salvo em: `database/backups/permissions_backup_[data].json`
- Contém todas as permissões de usuários e cargos

### Passo 2: Executar a Migração

```bash
# Migrar para o novo sistema (com backup automático)
php artisan db:seed --class=ModulePermissionsSeeder
```

**O que o seeder faz:**
1. ✅ Cria backup das permissões atuais
2. ✅ Limpa permissões antigas
3. ✅ Cria novas permissões baseadas em módulos
4. ✅ Migra permissões dos usuários (mapeamento automático)
5. ✅ Cria cargo "Administrador" com todas as permissões

### Passo 3: Verificar Resultado

Após a migração, o seeder mostra:
- Quantos usuários foram migrados
- Quais permissões não puderam ser mapeadas automaticamente
- Total de permissões criadas

---

## 💻 Como Usar no Código

### 1. Verificar Acesso ao Módulo

```php
use App\Helpers\PermissionHelper;

// Verificar se usuário pode acessar o módulo
if (PermissionHelper::hasModuleAccess('abastecimentos')) {
    // Usuário tem acesso ao módulo de abastecimentos
}
```

### 2. Verificar Permissão Específica

```php
// Método helper customizado
if (PermissionHelper::can('abastecimentos', 'abastecimento_manual', 'editar')) {
    // Usuário pode editar abastecimentos manuais
}

// Ou usar o método nativo do Spatie
if (auth()->user()->can('abastecimentos.abastecimento_manual.editar')) {
    // Mesma verificação
}
```

### 3. Nas Views (Blade)

```php
{{-- Verificar acesso ao módulo --}}
@if(PermissionHelper::hasModuleAccess('abastecimentos'))
    {{-- Exibir menu de abastecimentos --}}
@endif

{{-- Verificar permissão específica --}}
@can('abastecimentos.abastecimento_manual.criar')
    <a href="{{ route('admin.abastecimentomanual.create') }}">Novo Abastecimento</a>
@endcan
```

### 4. Obter Módulos Acessíveis

```php
// Lista simples de módulos
$modules = PermissionHelper::getUserAccessibleModules();

// Lista com nomes amigáveis e descrições
$modules = PermissionHelper::getUserAccessibleModulesWithNames();
// Retorna: [
//   ['nome' => 'abastecimentos', 'nome_amigavel' => 'Abastecimentos', 'descricao' => '...'],
//   ['nome' => 'veiculos', 'nome_amigavel' => 'Veículos', 'descricao' => '...'],
// ]
```

---

## 🔧 Comandos Artisan

### Sincronizar Permissões

```bash
# Criar/atualizar permissões sem limpar as existentes
php artisan permissions:sync-modules

# Forçar sem confirmação
php artisan permissions:sync-modules --force
```

**Quando usar:**
- Após adicionar novos módulos no `ModulePermissionService`
- Para garantir que todas as permissões estão criadas
- Não remove permissões existentes nem afeta usuários

---

## ➕ Adicionar Novo Módulo

### 1. Editar `ModulePermissionService`

Abra o arquivo: `app/Services/ModulePermissionService.php`

Adicione o módulo no array retornado por `getModulesStructure()`:

```php
'novo_modulo' => [
    'nome' => 'novo_modulo',
    'nome_amigavel' => 'Novo Módulo',
    'descricao' => 'Descrição do módulo',
    'icone' => 'icon-name',
    'ordem' => 12,
    'permissoes' => [
        'acessar_modulo' => [
            'nome' => 'novo_modulo.acessar_modulo',
            'nome_amigavel' => 'Acessar Novo Módulo',
            'descricao' => 'Permite acessar o módulo',
            'obrigatoria' => true,
        ],
        'funcionalidade_1' => [
            'nome' => 'novo_modulo.funcionalidade_1',
            'nome_amigavel' => 'Funcionalidade 1',
            'descricao' => 'Descrição da funcionalidade',
            'acoes' => ['visualizar', 'criar', 'editar', 'excluir'],
        ],
    ],
],
```

### 2. Sincronizar Permissões

```bash
php artisan permissions:sync-modules
```

### 3. Atualizar Menus e Views

Adicione a verificação de permissão nos menus:

```php
@if(PermissionHelper::hasModuleAccess('novo_modulo'))
    <div class="menu-item">
        <!-- Conteúdo do menu -->
    </div>
@endif
```

---

## 🎯 Estrutura Completa de um Módulo

### Exemplo: Módulo de Abastecimentos

```
abastecimentos                                    (Módulo)
├── acessar_modulo                                (Permissão obrigatória)
├── abastecimento_manual                          (Funcionalidade)
│   ├── visualizar
│   ├── criar
│   ├── editar
│   └── excluir
├── listar                                        (Funcionalidade)
│   ├── visualizar
│   └── exportar
├── ajuste_km                                     (Funcionalidade)
│   ├── visualizar
│   ├── criar
│   └── editar
└── relatorios                                    (Funcionalidade)
    ├── visualizar
    └── exportar
```

**Como fica no banco:**
```
abastecimentos.acessar_modulo
abastecimentos.abastecimento_manual.visualizar
abastecimentos.abastecimento_manual.criar
abastecimentos.abastecimento_manual.editar
abastecimentos.abastecimento_manual.excluir
abastecimentos.listar.visualizar
abastecimentos.listar.exportar
abastecimentos.ajuste_km.visualizar
...
```

---

## 📊 Gerenciamento de Permissões

### Criar Cargo com Permissões

```php
use Spatie\Permission\Models\Role;

// Criar cargo
$cargo = Role::create(['name' => 'Gerente de Frota']);

// Dar acesso a módulos específicos
$cargo->givePermissionTo([
    'veiculos.acessar_modulo',
    'veiculos.cadastro.visualizar',
    'veiculos.cadastro.editar',

    'abastecimentos.acessar_modulo',
    'abastecimentos.abastecimento_manual.visualizar',
    'abastecimentos.relatorios.visualizar',
    'abastecimentos.relatorios.exportar',
]);
```

### Dar Permissões Direto ao Usuário

```php
$user = User::find(1);

$user->givePermissionTo([
    'pneus.acessar_modulo',
    'pneus.cadastro.visualizar',
    'pneus.cadastro.criar',
]);
```

---

## 🐛 Troubleshooting

### Usuário não tem acesso após migração

1. Verificar se a migração foi concluída com sucesso
2. Limpar cache de permissões:
```bash
php artisan cache:clear
php artisan permission:cache-reset
```

3. Ver permissões do usuário:
```php
dd(PermissionHelper::debugUserPermissions());
```

### Permissão não encontrada

1. Sincronizar permissões:
```bash
php artisan permissions:sync-modules
```

2. Verificar se o módulo está definido no `ModulePermissionService`

### Erro ao executar seeder

Se o seeder falhar, as permissões são revertidas (rollback automático).
O backup em `database/backups/` permanece intacto.

Para restaurar manualmente:
1. Localizar arquivo de backup
2. Criar script de restauração baseado no JSON

---

## 📝 Notas Importantes

### Compatibilidade Retroativa

O `PermissionHelper` mantém compatibilidade com permissões antigas:
- Verifica primeiro o novo formato (`modulo.funcionalidade.acao`)
- Faz fallback para formato antigo (`ver_recurso`, `criar_recurso`)

### Cache de Permissões

O Spatie Permission usa cache. Sempre limpe após alterações:
```php
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
```

### Middleware

O `AutoPermissionMiddleware` continua funcionando, mas precisa ser atualizado para trabalhar melhor com o novo formato se necessário.

---

## 🎉 Próximos Passos

1. ✅ Execute a migração
2. ✅ Verifique se todos os usuários têm as permissões corretas
3. ✅ Atualize a interface de gerenciamento de permissões (se necessário)
4. ✅ Treine a equipe no novo sistema
5. ✅ Documente permissões customizadas adicionais

---

## 📞 Suporte

Para dúvidas ou problemas:
- Verifique os logs em `storage/logs/laravel.log`
- Use `PermissionHelper::debugUserPermissions()` para debug
- Consulte o backup em `database/backups/`

---

**Criado em:** 2025-06-01
**Versão:** 1.0
**Status:** ✅ Pronto para produção
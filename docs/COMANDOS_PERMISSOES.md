# Documentação - Comandos de Permissões

Esta documentação descreve os comandos Artisan criados para gerenciar permissões no sistema de gestão de frota.

## Comandos Disponíveis

### 1. GiveUserPermission - `user:give-permission`

**Arquivo:** `app/Console/Commands/GiveUserPermission.php`

**Propósito:** Conceder uma permissão específica a um usuário.

**Sintaxe:**
```bash
php artisan user:give-permission {user_id} {permission}
```

**Parâmetros:**
- `user_id`: ID numérico do usuário
- `permission`: Nome exato da permissão

**Exemplos de Uso:**
```bash
# Conceder permissão de visualização de veículos
php artisan user:give-permission 59 ver_veiculo

# Conceder permissão de criação de multas
php artisan user:give-permission 59 criar_multa

# Conceder permissão de relatório
php artisan user:give-permission 59 ver_relatorio_extrato_ipva
```

**Funcionalidades:**
- ✅ Verifica se o usuário existe
- ✅ Verifica se a permissão existe
- ✅ Evita duplicação (informa se o usuário já possui a permissão)
- ✅ Feedback claro sobre sucesso ou erro
- ✅ Códigos de retorno adequados (0 = sucesso, 1 = erro)

**Mensagens de Retorno:**
- **Sucesso:** `"Permissão '{permission}' concedida ao usuário '{user_name}' (ID: {user_id})"`
- **Já possui:** `"Usuário '{user_name}' já possui a permissão '{permission}'"`
- **Usuário não encontrado:** `"Usuário com ID {user_id} não encontrado"`
- **Permissão não encontrada:** `"Permissão '{permission}' não encontrada"`

---

### 2. ListPermissions - `permission:list`

**Arquivo:** `app/Console/Commands/ListPermissions.php`

**Propósito:** Listar todas as permissões disponíveis no sistema.

**Sintaxe:**
```bash
php artisan permission:list
```

**Funcionalidades:**
- ✅ Lista todas as permissões em ordem alfabética
- ✅ Mostra contador total de permissões
- ✅ Formato limpo e legível
- ✅ Útil para descobrir nomes exatos de permissões

**Exemplo de Saída:**
```
Permissões disponíveis:
ver_veiculo
criar_veiculo
editar_veiculo
excluir_veiculo
ver_multa
criar_multa
...

Total: 150 permissões
```

**Casos de Uso:**
- Descobrir o nome exato de uma permissão
- Auditoria de permissões do sistema
- Debug de problemas de acesso
- Planejamento de estrutura de permissões

---

## Middleware AutoPermissionMiddleware - Melhorias

**Arquivo:** `app/Http/Middleware/AutoPermissionMiddleware.php`

Durante a sessão, foram implementadas várias melhorias no middleware de permissões automáticas:

### Novos Mapeamentos Adicionados

```php
// Mapeamento de URLs sem underscores para permissões com underscores
'testefrios' => 'teste_frio',
'testefumacas' => 'teste_fumaca',
'licenciamentoveiculos' => 'licenciamento_veiculo',
'ipvaveiculos' => 'ipva_veiculo',
'seguroobrigatorio' => 'seguro_obrigatorio',
'classificacaomultas' => 'classificacao_multa',
'multas' => 'multa',
'relatoriocontacorrentefornecedor' => 'relatorio_cont_corrente_fornecedor',
'relatorioextratoipva' => 'relatorio_extrato_ipva',
'relatoriohistoricokm' => 'relatorio_historico_km',
'relatorioipvalicenciamento' => 'relatorio_ipva_licenciamento_veiculo',
'relatoriotransferenciaveiculo' => 'relatorio_transferencia_veiculo',
'cronotacografos' => 'cronotacografo',
```

### Novas Ações de Exportação Suportadas

```php
// Ações GET para exportação
'exportPdf' => 'ver',
'exportCsv' => 'ver',
'exportXls' => 'ver',
'exportXml' => 'ver',

// Ações POST para geração de relatórios
'gerarPdf' => 'ver',
'gerarExcel' => 'ver',
```

### Lista Expandida de Ações Conhecidas

```php
$knownActions = [
    'create', 'edit', 'show', 
    'export-pdf', 'export-csv', 'export-xls', 'export-xml', 'export',
    'gerarpdf', 'gerarexcel',
    'exportPdf', 'exportCsv', 'exportXls', 'exportXml'
];
```

---

## Helper PermissionHelper - Funcionalidades

**Arquivo:** `app/Helpers/PermissionHelper.php`

### Métodos Principais

#### `hasModuleAccess(string $module): bool`

Verifica se o usuário tem acesso a um módulo específico.

**Lógica de Verificação:**
1. Superuser sempre tem acesso
2. Verifica permissão `{modulo}.acessar_modulo`
3. Fallback: verifica se tem qualquer permissão que comece com `{modulo}.`
4. Compatibilidade: verifica permissão antiga `acessar_{modulo}`

---

## Padrões de Nomenclatura de Permissões

### Estrutura Padrão
- **Visualização:** `ver_{modulo}`
- **Criação:** `criar_{modulo}`
- **Edição:** `editar_{modulo}`
- **Exclusão:** `excluir_{modulo}`

### Relatórios
- **Formato:** `ver_relatorio_{nome_relatorio}`
- **Exemplo:** `ver_relatorio_extrato_ipva`

### Módulos com Underscores
- **URL:** `admin/testefrios`
- **Permissão:** `ver_teste_frio`
- **Mapeamento:** Feito automaticamente pelo middleware

---

## Exemplos Práticos de Uso

### Cenário 1: Usuário sem Acesso a Cronotacógrafos

```bash
# Verificar se o usuário tem a permissão
php artisan permission:list | findstr cronotacografo

# Resultado esperado:
ver_cronotacografo
criar_cronotacografo
editar_cronotacografo
excluir_cronotacografo

# Conceder permissões
php artisan user:give-permission 59 ver_cronotacografo
php artisan user:give-permission 59 criar_cronotacografo
php artisan user:give-permission 59 editar_cronotacografo
php artisan user:give-permission 59 excluir_cronotacografo
```

### Cenário 2: Usuário sem Acesso a Relatórios

```bash
# Listar permissões de relatório
php artisan permission:list | findstr relatorio

# Conceder acesso a um relatório específico
php artisan user:give-permission 59 ver_relatorio_historico_km
php artisan user:give-permission 59 criar_relatorio_historico_km  # Para geração de PDF/Excel
```

---

## Troubleshooting

### Problema: Erro 403 na Geração de PDF

**Causa:** Falta permissão para ação POST `gerarPdf`

**Solução:**
```bash
# Conceder permissão de criação (para ações POST)
php artisan user:give-permission {user_id} criar_{modulo}
```

### Problema: Menu Não Aparece

**Causa:** Verificação `@can()` no Blade com nome incorreto

**Solução:**
1. Verificar o nome exato da permissão: `php artisan permission:list | findstr {termo}`
2. Corrigir a diretiva `@can()` no arquivo Blade
3. Verificar se o mapeamento está correto no middleware

### Problema: Acesso Negado Mesmo com Permissão

**Causa:** Mapeamento incorreto de módulo no middleware

**Solução:**
1. Adicionar mapeamento em `MODULE_PERMISSION_MAPPING`
2. Verificar se a ação está na lista `knownActions` se for o caso

---

## Logs de Debug

Para debugar problemas de permissão, o middleware inclui logs detalhados:

```php
Log::info("DEBUG MODULO", [
    'user_id' => $user->id,
    'module_original' => $module,
    'action_original' => $action,
    'permission_module_mapped' => self::MODULE_PERMISSION_MAPPING[$module] ?? $module,
    'permission_action_mapped' => $this->mapActionToPermission($action),
    'permission_final' => $permission,
    'user_has_permission' => $user->can($permission),
]);
```

**Localização dos Logs:** `storage/logs/laravel-{data}.log`

---

## Manutenção e Expansão

### Adicionando Novos Módulos

1. **Criar as permissões** no seeder ou via comando
2. **Adicionar mapeamento** se a URL diferir da permissão
3. **Testar acesso** e geração de relatórios
4. **Atualizar documentação**

### Padrão para Novos Comandos

Seguir a estrutura existente:
- Validação de entrada
- Mensagens claras
- Códigos de retorno adequados
- Logging quando necessário

---

**Data de Criação:** 13 de outubro de 2025  
**Autor:** Sistema automatizado durante sessão de correção de permissões  
**Versão:** 1.0  
**Última Atualização:** 13 de outubro de 2025
# Documentação - Conversão DB para Eloquent no CotacoesController

## Visão Geral

Este documento descreve a conversão do código de manipulação de banco de dados de queries SQL brutas (DB facade) para Eloquent ORM no módulo de Cotações.

## Alterações Implementadas

### 1. Novos Models Criados

#### ServicoSolicitacaoCompra

**Localização:** `app/Models/ServicoSolicitacaoCompra.php`

```php
class ServicoSolicitacaoCompra extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'servicossolicitacoescompras';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
```

**Relacionamentos:**

-   `solicitacaoCompra()` - BelongsTo com SolicitacaoCompra
-   `servico()` - BelongsTo com Servico

### 2. Imports Adicionados no Controller

```php
use App\Models\OrdemServicoServicos;
use App\Models\ServicoSolicitacaoCompra;
```

### 3. Métodos Convertidos para Eloquent

#### vincularComprador()

**Antes (DB Raw):**

```php
$ordemServicos = DB::connection('base_unitop')
    ->table('ordem_servico_servicos as oss')
    ->join('solicitacoescompras as s', 's.id_ordem_servico', '=', 'oss.id_ordem_servico')
    ->join('servicossolicitacoescompras as sc', function ($join) {
        $join->on('sc.id_solicitacao_compra', '=', 's.id_solicitacoes_compras')
             ->on('oss.id_servicos', '=', 'sc.id_servico');
    })
    ->where('s.id_solicitacoes_compras', $idSolicitacaoCompras)
    ->pluck('oss.id_ordem_servico_serv')
    ->toArray();
```

**Depois (Eloquent):**

```php
$servicosSolicitacao = ServicoSolicitacaoCompra::where('id_solicitacao_compra', $idSolicitacaoCompras)
    ->pluck('id_servico')
    ->toArray();

OrdemServicoServicos::where('id_ordem_servico', $idOrdem)
    ->whereIn('id_servicos', $servicosSolicitacao)
    ->update(['status_servico' => 'INICIADO COTAÇÃO DE SERVIÇO']);
```

#### verificarStatusCompras()

**Melhorias:**

-   Uso de `select()` para otimizar query
-   Logs detalhados para debugging
-   Comparação estrita (`===`) em vez de loose (`==`)

**Código:**

```php
$solicitacao = SolicitacaoCompra::select('situacao_compra')
    ->where('id_solicitacoes_compras', $id)
    ->first();

$jaIniciada = $solicitacao->situacao_compra === 'INICIADA';
```

#### verificarUsuario()

**Melhorias:**

-   Lógica mais clara e documentada
-   Uma única query em vez de duas
-   Logs detalhados para auditoria

**Código:**

```php
$solicitacao = SolicitacaoCompra::select('id_comprador')
    ->where('id_solicitacoes_compras', $id)
    ->first();

// Se já tem um comprador e não é o usuário atual
if ($solicitacao->id_comprador && $solicitacao->id_comprador != $userId) {
    return true; // Bloqueia
}

return false; // Permite assumir
```

## Características Implementadas

### 🛡️ **Robustez e Fallbacks**

1. **Verificação de Classe:**

    ```php
    if (class_exists(ServicoSolicitacaoCompra::class)) {
        // Usar relacionamento específico
    } else {
        // Fallback para todos os serviços da ordem
    }
    ```

2. **Tratamento de Erros:**
    - Try/catch específicos para atualizações de serviços
    - Logs de warning em caso de falha não-crítica
    - Transação não falha se a atualização de serviços der erro

### 📊 **Otimizações de Performance**

1. **Queries Seletivas:**

    ```php
    // Em vez de SELECT *
    SolicitacaoCompra::select('situacao_compra', 'id_comprador')
    ```

2. **Update em Massa:**

    ```php
    // Em vez de loop com updates individuais
    OrdemServicoServicos::where()->whereIn()->update()
    ```

3. **Uso de firstOrFail():**
    ```php
    // Melhor tratamento de erros
    ->firstOrFail() // em vez de ->first() + verificação manual
    ```

### 📝 **Logging Detalhado**

Todos os métodos agora incluem logs estruturados:

```php
Log::info('Comprador vinculado com sucesso', [
    'id_comprador' => $idComprador,
    'id_solicitacao_compras' => $idSolicitacaoCompras,
    'id_ordem_servico' => $idOrdem
]);
```

## Benefícios da Conversão

### ✅ **Vantagens Técnicas**

1. **Legibilidade:** Código mais limpo e expressivo
2. **Manutenibilidade:** Mais fácil de modificar e debug
3. **Segurança:** Proteção automática contra SQL injection
4. **Relacionamentos:** Uso de relacionamentos Eloquent
5. **Caching:** Eloquent oferece caching automático

### ✅ **Vantagens Operacionais**

1. **Debug:** Logs mais detalhados e estruturados
2. **Monitoramento:** Melhor rastreabilidade de operações
3. **Flexibilidade:** Fallbacks para cenários de erro
4. **Performance:** Queries otimizadas com select específicos

## Compatibilidade

### 🔄 **Backward Compatibility**

O código mantém compatibilidade com:

-   Estruturas de tabela existentes
-   Conexões de banco de dados múltiplas
-   Lógica de negócio existente

### 🆕 **Forward Compatibility**

Preparado para:

-   Novos relacionamentos Eloquent
-   Expansão de funcionalidades
-   Melhorias futuras de performance

## Testes Recomendados

1. **Teste de Vinculação:**

    - Assumir solicitação sem ordem de serviço
    - Assumir solicitação com ordem de serviço
    - Assumir solicitação já iniciada

2. **Teste de Concorrência:**

    - Múltiplos usuários tentando assumir a mesma solicitação
    - Verificação de logs de auditoria

3. **Teste de Fallback:**
    - Simular ausência da tabela servicossolicitacoescompras
    - Verificar se fallback funciona corretamente

## Próximos Passos Sugeridos

1. **Relacionamentos:** Adicionar mais relacionamentos Eloquent nos models
2. **Observers:** Implementar observers para auditoria automática
3. **Events:** Criar events para ações importantes
4. **Jobs:** Mover operações pesadas para jobs assíncronos
5. **Cache:** Implementar cache para queries frequentes

## Exemplo de Uso

```php
// Assumir solicitação (via AJAX)
POST /admin/compras/cotacoes/assumir
{
    "id": 123
}

// Resposta de sucesso
{
    "success": true,
    "title": "Sucesso",
    "message": "Solicitação iniciada e vinculada ao comprador."
}
```

A conversão para Eloquent torna o código mais moderno, maintível e robusto! 🚀

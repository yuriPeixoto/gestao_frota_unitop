# Refatoração do AprovarPedidoController

## Resumo das Alterações

Foi realizada uma refatoração completa do `AprovarPedidoController` para melhorar a organização do código, seguindo o princípio da Responsabilidade Única e facilitando a manutenção e testabilidade.

## Estrutura Anterior

O controller original tinha **971 linhas** e concentrava toda a lógica de negócio, incluindo:

-   Consultas diretas ao banco de dados
-   Validações complexas
-   Processamento de cotações
-   Geração de pedidos
-   Manipulação de fornecedores

## Estrutura Atual

### Controller (191 linhas)

O controller agora é focado apenas em:

-   Receber requisições HTTP
-   Chamar os métodos do service
-   Retornar respostas adequadas
-   Tratamento de erros de alto nível

### Service (785 linhas)

Foi criado o `AprovarPedidoService` que centraliza toda a lógica de negócio:

-   Operações de banco de dados
-   Validações de negócio
-   Processamento de cotações
-   Geração de pedidos
-   Manipulação de fornecedores

## Principais Benefícios

### 1. **Responsabilidade Única**

-   Controller: apenas gerenciar requisições/respostas HTTP
-   Service: toda a lógica de negócio relacionada à aprovação de pedidos

### 2. **Testabilidade**

-   Service pode ser testado independentemente
-   Fácil mock do service nos testes do controller

### 3. **Reutilização**

-   Lógica do service pode ser reutilizada em outros controllers ou contextos
-   Métodos bem definidos e documentados

### 4. **Manutenibilidade**

-   Código mais organizado e fácil de entender
-   Separação clara de responsabilidades
-   Métodos menores e mais focados

## Métodos Migrados para o Service

### Métodos de Consulta

-   `buscarSolicitacoesPendentes()`
-   `buscarSolicitacaoCompleta()`
-   `buscarCotacoesCompletas()`
-   `buscarDadosEdicao()`
-   `getFilterData()`

### Métodos de Processamento

-   `aprovarCotacao()`
-   `gerarCotacao()`
-   `cancelarSolicitacao()`
-   `validarCotacoes()`

### Métodos Auxiliares

-   `buscarFornecedorItens()`
-   `buscarFornecedorItem()`
-   `getCotacoes()`
-   `getCotacoesCompletas()`

### Métodos Privados de Negócio

-   `mudarStatusSolicitacao()`
-   `atualizarIdFilial()`
-   `gerarRequisicaoPeca()`
-   `gerarPedidoMenorCotacao()`
-   `gerarPedidoMenorValorItem()`
-   `gerarPedidoLivre()`

## Injeção de Dependência

O controller agora recebe o service via injeção de dependência no construtor:

```php
public function __construct(AprovarPedidoService $aprovarPedidoService)
{
    $this->aprovarPedidoService = $aprovarPedidoService;
}
```

## Exemplo de Uso

### Antes:

```php
public function index(Request $request)
{
    $query = SolicitacaoCompra::with(['solicitante', 'departamento', 'filial', 'aprovador'])
        ->whereIn('situacao_compra', ['AGUARDANDO APROVAÇÃO'])
        ->orderBy('id_solicitacoes_compras', 'desc');

    // ... muita lógica de filtros ...

    $solicitacoes = $query->paginate(30)->appends($request->query());
    // ... mais lógica ...
}
```

### Depois:

```php
public function index(Request $request)
{
    try {
        $solicitacoes = $this->aprovarPedidoService->buscarSolicitacoesPendentes($request);
        $filterData = $this->aprovarPedidoService->getFilterData();

        return view('admin.aprovarpedido.index', array_merge(
            compact('solicitacoes'),
            $filterData,
        ));
    } catch (\Exception $e) {
        // ... tratamento de erro ...
    }
}
```

## Estrutura de Arquivos

```
app/
├── Http/
│   └── Controllers/
│       └── Admin/
│           └── AprovarPedidoController.php (191 linhas)
└── Services/
    └── AprovarPedidoService.php (785 linhas)
```

## Próximos Passos Recomendados

1. **Testes Unitários**: Criar testes para o service
2. **Form Requests**: Extrair validações para classes específicas
3. **Resources**: Usar Resources para formatação de respostas API
4. **Repository Pattern**: Considerar extrair consultas para repositórios
5. **Events/Listeners**: Implementar eventos para ações críticas

## Considerações

-   Todas as funcionalidades existentes foram mantidas
-   A interface pública do controller permanece a mesma
-   O código agora é mais limpo e organizado
-   Facilita futuras manutenções e evoluções

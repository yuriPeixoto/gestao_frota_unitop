# Documentação - Checklist Service

## Visão Geral

O Checklist Service é um serviço Laravel que permite integração com uma API externa de checklists. Ele fornece uma interface completa para gerenciar tipos de checklist, seções, itens, checklists, templates, workflows e estatísticas.

## Estrutura dos Arquivos

-   **ChecklistService.php** - Serviço principal com todos os métodos de integração
-   **ChecklistController.php** - Controller com exemplos de uso
-   **Configuração** - Variáveis de ambiente e service provider

## Configuração

### 1. Variáveis de Ambiente (.env)

```env
CHECKLIST_API_BASE_URL=http://localhost:8002/api/v2
CHECKLIST_API_PREFIX=checklist
CHECKLIST_API_TIMEOUT=30
CHECKLIST_API_RETRY_TIMES=3
CHECKLIST_API_RETRY_SLEEP=1000
```

### 2. Configuração do Serviço (config/services.php)

```php
'checklist' => [
    'base_url' => env('CHECKLIST_API_BASE_URL', 'http://localhost:8002/api/v2'),
    'api_prefix' => env('CHECKLIST_API_PREFIX', 'checklist'),
    'timeout' => env('CHECKLIST_API_TIMEOUT', 30),
    'retry_times' => env('CHECKLIST_API_RETRY_TIMES', 3),
    'retry_sleep' => env('CHECKLIST_API_RETRY_SLEEP', 1000),
],
```

### 3. Registro no Service Provider

```php
// No AppServiceProvider
public function register(): void
{
    $this->app->singleton(ChecklistService::class, function ($app) {
        return new ChecklistService();
    });
}
```

## Principais Funcionalidades

### Health Check

-   `checkHealth()` - Verifica se a API está funcionando

### Tipos de Checklist

-   `listTypes()` - Lista todos os tipos
-   `getType($id)` - Obtém um tipo específico
-   `createType($data)` - Cria novo tipo
-   `updateType($id, $data)` - Atualiza tipo existente
-   `deleteType($id)` - Remove tipo
-   `toggleTypeActive($id, $active)` - Ativa/desativa tipo

### Seções

-   `listSectionsByType($typeId)` - Lista seções de um tipo
-   `getSection($id)` - Obtém seção específica
-   `createSection($typeId, $data)` - Cria nova seção
-   `updateSection($id, $data)` - Atualiza seção
-   `deleteSection($id)` - Remove seção
-   `updateSectionsOrder($typeId, $sections)` - Reordena seções

### Itens

-   `getItem($id)` - Obtém item específico
-   `createItem($sectionId, $data)` - Cria novo item
-   `updateItem($id, $data)` - Atualiza item
-   `deleteItem($id)` - Remove item
-   `duplicateItem($id)` - Duplica item
-   `moveItemToSection($id, $sectionId)` - Move item para outra seção

### Checklists

-   `listChecklists()` - Lista checklists
-   `getChecklist($id)` - Obtém checklist específico
-   `createChecklist($data)` - Cria novo checklist
-   `updateChecklist($id, $data)` - Atualiza checklist
-   `deleteChecklist($id)` - Remove checklist
-   `saveResponse($checklistId, $data)` - Salva resposta individual
-   `saveBulkResponses($checklistId, $responses, $respondedBy)` - Salva múltiplas respostas

### Templates

-   `listTemplates()` - Lista templates
-   `createTemplate($data)` - Cria template
-   `addItemsToTemplate($id, $items)` - Adiciona itens ao template
-   `duplicateTemplate($id, $data)` - Duplica template

### Workflow

-   `getWorkflowByType($typeId)` - Obtém workflow de um tipo
-   `saveWorkflow($typeId, $workflow)` - Salva workflow

### Estatísticas

-   `getStatisticsOverview($startDate, $endDate)` - Visão geral das estatísticas
-   `getStatisticsByType($typeId)` - Estatísticas por tipo
-   `getCompletionRate()` - Taxa de conclusão
-   `getIssueRate()` - Taxa de problemas

## Métodos Auxiliares (Builders)

O serviço inclui métodos auxiliares para construir arrays de dados:

### `buildChecklistTypeData()`

```php
$data = $service->buildChecklistTypeData(
    name: 'Tipo de Checklist',
    description: 'Descrição do tipo',
    isMultiStage: false,
    isActive: true,
    icon: 'icon-name',
    color: '#FF0000',
    departmentId: 1
);
```

### `buildSectionData()`

```php
$data = $service->buildSectionData(
    name: 'Nome da Seção',
    description: 'Descrição',
    icon: 'icon-name',
    orderIndex: 0,
    stage: 1,
    isActive: true
);
```

### `buildItemData()`

```php
$data = $service->buildItemData(
    title: 'Título do Item',
    description: 'Descrição',
    type: 'checkbox',
    options: ['Opção 1', 'Opção 2'],
    validationRules: ['required'],
    isRequired: true,
    orderIndex: 0,
    isActive: true
);
```

### `buildChecklistData()`

```php
$data = $service->buildChecklistData(
    checklistTypeId: 1,
    title: 'Título do Checklist',
    description: 'Descrição',
    entityType: 'project',
    entityId: 123,
    status: 'pending',
    createdBy: 1,
    assignedTo: 2,
    dueDate: '2024-12-31'
);
```

## Exemplos de Uso

### Exemplo 1: Criar um Tipo de Checklist

```php
use App\Services\ChecklistService;

$service = app(ChecklistService::class);

$data = $service->buildChecklistTypeData(
    name: 'Checklist de Qualidade',
    description: 'Verificações de qualidade do produto',
    isMultiStage: true,
    departmentId: 1
);

$result = $service->createType($data);
```

### Exemplo 2: Salvar Respostas em Lote

```php
$responses = [
    ['item_id' => 1, 'value' => 'Sim'],
    ['item_id' => 2, 'value' => 'Não'],
    ['item_id' => 3, 'value' => 'Observações aqui']
];

$result = $service->saveBulkResponses(
    checklistId: 1,
    responses: $responses,
    respondedBy: auth()->id()
);
```

### Exemplo 3: Obter Estatísticas

```php
$overview = $service->getStatisticsOverview('2024-01-01', '2024-12-31');
$completionRate = $service->getCompletionRate(['start_date' => '2024-01-01']);
```

## Facade (Opcional)

Para facilitar o uso, você pode criar uma facade:

```php
// app/Facades/Checklist.php
use Illuminate\Support\Facades\Facade;

class Checklist extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ChecklistService::class;
    }
}

// Uso:
use App\Facades\Checklist;

$types = Checklist::listTypes();
$checklist = Checklist::createChecklist($data);
```

## Controller de Exemplo

O arquivo inclui um controller com exemplos práticos de como usar o serviço em endpoints da sua aplicação:

-   `listTypes()` - Lista tipos com filtros
-   `createType()` - Cria novo tipo usando dados da requisição
-   `createChecklist()` - Cria checklist
-   `saveBulkResponses()` - Salva múltiplas respostas
-   `getStatistics()` - Obtém estatísticas combinadas

## Tratamento de Erros

O serviço inclui tratamento automático de erros:

-   Log de erros para debugging
-   Conversão de respostas de erro da API
-   Lançamento de exceptions apropriadas

## Notas Importantes

-   Todas as requisições usam JSON como formato padrão
-   O serviço suporta métodos HTTP: GET, POST, PUT, PATCH, DELETE
-   Responses são automaticamente convertidos para arrays PHP
-   Logs de erro são registrados automaticamente para facilitar debugging

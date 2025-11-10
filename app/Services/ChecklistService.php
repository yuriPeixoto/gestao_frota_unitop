<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ChecklistService
{
    private string $baseUrl;
    private string $apiPrefix;
    private array $defaultHeaders;
    private int $timeout;
    private bool $verifySSL;

    public function __construct()
    {
        $this->baseUrl = config('services.checklist.base_url', 'https://carvalima.unitopconsultoria.com.br/api/v2');
        $this->apiPrefix = config('services.checklist.api_prefix', 'checklist');
        $this->timeout = config('services.checklist.timeout', 30);
        $this->verifySSL = config('services.checklist.verify_ssl', false);

        $this->defaultHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'GestaoFrotaLaravel/1.0',
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => 'Bearer ' . config('services.checklist.api_token', 'eaZw1DbmXso3FHxx+pkHrbw+jRlcGjmeZbU9+Tb1oRA=')
        ];
    }

    /**
     * Make HTTP request to the API with proper isolation
     */
    private function makeRequest(string $method, string $endpoint, array $data = [], array $params = []): array
    {
        try {
            $url = "{$this->baseUrl}/{$this->apiPrefix}/{$endpoint}";

            // ✅ ISOLAMENTO CRÍTICO: HTTP client sem contexto de sessão Laravel
            $request = Http::withHeaders($this->defaultHeaders)
                ->timeout($this->timeout)
                ->withOptions([
                    'verify' => $this->verifySSL,
                    'allow_redirects' => true,
                    // ✅ CRÍTICO: Não enviar cookies do Laravel
                    'cookies' => false,
                    // ✅ CRÍTICO: Isolar contexto de sessão
                    'http_errors' => false
                ]);

            if (!empty($params)) {
                $request = $request->withUrlParameters($params);
            }

            Log::info('ChecklistService: Fazendo requisição para API externa', [
                'method' => $method,
                'url' => $url,
                'has_data' => !empty($data),
                'headers' => array_keys($this->defaultHeaders)
            ]);

            $response = match (strtoupper($method)) {
                'GET' => $request->get($url, $data),
                'POST' => $request->post($url, $data),
                'PUT' => $request->put($url, $data),
                'PATCH' => $request->patch($url, $data),
                'DELETE' => $request->delete($url, $data),
                default => throw new Exception("Unsupported HTTP method: {$method}")
            };

            return $this->handleResponse($response, $method, $endpoint);
        } catch (Exception $e) {
            Log::error('ChecklistService: Falha na requisição para API externa', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'base_url' => $this->baseUrl,
                'data' => $data,
                'params' => $params,
                'headers' => array_keys($this->defaultHeaders)
            ]);

            throw $e;
        }
    }

    /**
     * Handle API response with improved error handling
     */
    private function handleResponse(Response $response, string $method, string $endpoint): array
    {
        $statusCode = $response->status();

        Log::info('ChecklistService: Resposta da API externa recebida', [
            'method' => $method,
            'endpoint' => $endpoint,
            'status_code' => $statusCode,
            'response_size' => strlen($response->body())
        ]);

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        // ✅ TRATAMENTO ESPECÍFICO PARA DIFERENTES ERROS
        $errorBody = $response->json() ?? [];
        $errorMessage = $errorBody['message'] ?? 'Request failed';

        switch ($statusCode) {
            case 401:
                $errorMessage = "API Authentication failed: " . $errorMessage;
                break;
            case 403:
                $errorMessage = "API Access forbidden: " . $errorMessage;
                break;
            case 404:
                $errorMessage = "API Endpoint not found: " . $errorMessage;
                break;
            case 419:
                $errorMessage = "API CSRF Error (should not happen): " . $errorMessage;
                break;
            case 422:
                $errorMessage = "API Validation error: " . $errorMessage;
                break;
            case 500:
                $errorMessage = "API Internal server error: " . $errorMessage;
                break;
            default:
                $errorMessage = "API Error (HTTP {$statusCode}): " . $errorMessage;
        }

        Log::error('ChecklistService: Erro na resposta da API externa', [
            'method' => $method,
            'endpoint' => $endpoint,
            'status' => $statusCode,
            'response_body' => $response->body(),
            'headers' => $response->headers()
        ]);

        throw new Exception($errorMessage, $statusCode);
    }

    // ===============================
    // HEALTH CHECK
    // ===============================

    /**
     * Check API health
     */
    public function checkHealth(): array
    {
        return $this->makeRequest('GET', 'health');
    }

    // ===============================
    // CHECKLIST TYPES
    // ===============================

    /**
     * List checklist types
     */
    public function listTypes(array $filters = []): array
    {
        return $this->makeRequest('GET', 'types', $filters);
    }

    /**
     * Get checklist type by ID
     */
    public function getType(int $id): array
    {
        return $this->makeRequest('GET', "types/{$id}");
    }

    /**
     * Create new checklist type
     */
    public function createType(array $data): array
    {
        return $this->makeRequest('POST', 'types', $data);
    }

    /**
     * Update checklist type
     */
    public function updateType(int $id, array $data): array
    {
        return $this->makeRequest('PUT', "types/{$id}", $data);
    }

    /**
     * Delete checklist type
     */
    public function deleteType(int $id): array
    {
        return $this->makeRequest('DELETE', "types/{$id}");
    }

    /**
     * Toggle type active status
     */
    public function toggleTypeActive(int $id, bool $active): array
    {
        return $this->makeRequest('PATCH', "types/{$id}/toggle-active", ['active' => $active]);
    }

    /**
     * List checklists by type
     */
    public function listChecklistsByType(int $typeId, array $filters = []): array
    {
        return $this->makeRequest('GET', "types/{$typeId}/checklists", $filters);
    }

    // ===============================
    // SECTIONS
    // ===============================

    /**
     * List sections by type
     */
    public function listSectionsByType(int $typeId, array $filters = []): array
    {
        return $this->makeRequest('GET', "types/{$typeId}/sections", $filters);
    }

    /**
     * Get section by ID
     */
    public function getSection(int $id): array
    {
        return $this->makeRequest('GET', "sections/{$id}");
    }

    /**
     * Create new section
     */
    public function createSection(int $typeId, array $data): array
    {
        return $this->makeRequest('POST', "types/{$typeId}/sections", $data);
    }

    /**
     * Update section
     */
    public function updateSection(int $id, array $data): array
    {
        return $this->makeRequest('PUT', "sections/{$id}", $data);
    }

    /**
     * Delete section
     */
    public function deleteSection(int $id): array
    {
        return $this->makeRequest('DELETE', "sections/{$id}");
    }

    /**
     * Update sections order
     */
    public function updateSectionsOrder(int $typeId, array $sections): array
    {
        return $this->makeRequest('POST', "types/{$typeId}/sections/update-order", ['sections' => $sections]);
    }

    /**
     * List items by section
     */
    public function listItemsBySection(int $sectionId, array $filters = []): array
    {
        return $this->makeRequest('GET', "sections/{$sectionId}/items", $filters);
    }

    // ===============================
    // ITEMS
    // ===============================

    /**
     * Get item by ID
     */
    public function getItem(int $id): array
    {
        return $this->makeRequest('GET', "items/{$id}");
    }

    /**
     * Create new item
     */
    public function createItem(int $sectionId, array $data): array
    {
        return $this->makeRequest('POST', "sections/{$sectionId}/items", $data);
    }

    /**
     * Update item
     */
    public function updateItem(int $id, array $data): array
    {
        return $this->makeRequest('PUT', "items/{$id}", $data);
    }

    /**
     * Delete item
     */
    public function deleteItem(int $id): array
    {
        return $this->makeRequest('DELETE', "items/{$id}");
    }

    /**
     * Update items order
     */
    public function updateItemsOrder(int $sectionId, array $items): array
    {
        return $this->makeRequest('POST', "sections/{$sectionId}/items/update-order", ['items' => $items]);
    }

    /**
     * Duplicate item
     */
    public function duplicateItem(int $id, array $data = []): array
    {
        return $this->makeRequest('POST', "items/{$id}/duplicate", $data);
    }

    /**
     * Move item to another section
     */
    public function moveItemToSection(int $id, int $sectionId, int $orderIndex = 0): array
    {
        return $this->makeRequest('PATCH', "items/{$id}/move-to-section", [
            'section_id' => $sectionId,
            'order_index' => $orderIndex
        ]);
    }

    // ===============================
    // CHECKLISTS
    // ===============================

    /**
     * List checklists
     */
    public function listChecklists(array $filters = []): array
    {
        return $this->makeRequest('GET', 'checklists', $filters);
    }

    /**
     * Get checklist by ID
     */
    public function getChecklist(int $id): array
    {
        return $this->makeRequest('GET', "checklists/{$id}");
    }

    /**
     * Create new checklist
     */
    public function createChecklist(array $data): array
    {
        return $this->makeRequest('POST', 'checklists', $data);
    }

    /**
     * Update checklist
     */
    public function updateChecklist(int $id, array $data): array
    {
        return $this->makeRequest('PUT', "checklists/{$id}", $data);
    }

    /**
     * Delete checklist
     */
    public function deleteChecklist(int $id): array
    {
        return $this->makeRequest('DELETE', "checklists/{$id}");
    }

    /**
     * Update checklist stage
     */
    public function updateChecklistStage(int $id, array $data): array
    {
        return $this->makeRequest('PATCH', "checklists/{$id}/update-stage", $data);
    }

    /**
     * Save response to checklist item
     */
    public function saveResponse(int $checklistId, array $data): array
    {
        return $this->makeRequest('POST', "checklists/{$checklistId}/responses", $data);
    }

    /**
     * Save multiple responses to checklist items
     */
    public function saveBulkResponses(int $checklistId, array $responses, int $respondedBy): array
    {
        return $this->makeRequest('POST', "checklists/{$checklistId}/responses/bulk", [
            'responses' => $responses,
            'responded_by' => $respondedBy
        ]);
    }

    /**
     * Create checklist from template
     */
    public function createChecklistFromTemplate(array $data): array
    {
        return $this->makeRequest('POST', 'checklists/create-from-template', $data);
    }

    // ===============================
    // TEMPLATES
    // ===============================

    /**
     * List templates
     */
    public function listTemplates(array $filters = []): array
    {
        return $this->makeRequest('GET', 'templates', $filters);
    }

    /**
     * Get template by ID
     */
    public function getTemplate(int $id): array
    {
        return $this->makeRequest('GET', "templates/{$id}");
    }

    /**
     * Create new template
     */
    public function createTemplate(array $data): array
    {
        return $this->makeRequest('POST', 'templates', $data);
    }

    /**
     * Update template
     */
    public function updateTemplate(int $id, array $data): array
    {
        return $this->makeRequest('PUT', "templates/{$id}", $data);
    }

    /**
     * Delete template
     */
    public function deleteTemplate(int $id): array
    {
        return $this->makeRequest('DELETE', "templates/{$id}");
    }

    /**
     * Add items to template
     */
    public function addItemsToTemplate(int $id, array $items): array
    {
        return $this->makeRequest('POST', "templates/{$id}/items", ['items' => $items]);
    }

    /**
     * Remove item from template
     */
    public function removeItemFromTemplate(int $templateId, int $itemId): array
    {
        return $this->makeRequest('DELETE', "templates/{$templateId}/items/{$itemId}");
    }

    /**
     * Duplicate template
     */
    public function duplicateTemplate(int $id, array $data): array
    {
        return $this->makeRequest('POST', "templates/{$id}/duplicate", $data);
    }

    // ===============================
    // WORKFLOW
    // ===============================

    /**
     * Get workflow by type
     */
    public function getWorkflowByType(int $typeId): array
    {
        return $this->makeRequest('GET', "workflow/types/{$typeId}");
    }

    /**
     * Save workflow
     */
    public function saveWorkflow(int $typeId, array $workflow): array
    {
        return $this->makeRequest('POST', "workflow/types/{$typeId}", ['workflow' => $workflow]);
    }

    /**
     * Get workflow stage by ID
     */
    public function getWorkflowStage(int $id): array
    {
        return $this->makeRequest('GET', "workflow/{$id}");
    }

    /**
     * Update workflow stage
     */
    public function updateWorkflowStage(int $id, array $data): array
    {
        return $this->makeRequest('PUT', "workflow/{$id}", $data);
    }

    /**
     * Delete workflow stage
     */
    public function deleteWorkflowStage(int $id): array
    {
        return $this->makeRequest('DELETE', "workflow/{$id}");
    }

    // ===============================
    // STATISTICS
    // ===============================

    /**
     * Get statistics overview
     */
    public function getStatisticsOverview(string $startDate = null, string $endDate = null): array
    {
        $params = array_filter([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        return $this->makeRequest('GET', 'statistics/overview', $params);
    }

    /**
     * Get statistics by type
     */
    public function getStatisticsByType(int $typeId, string $startDate = null, string $endDate = null): array
    {
        $params = array_filter([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        return $this->makeRequest('GET', "statistics/by-type/{$typeId}", $params);
    }

    /**
     * Get statistics by entity
     */
    public function getStatisticsByEntity(string $entityType, int $entityId, string $startDate = null, string $endDate = null): array
    {
        $params = array_filter([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        return $this->makeRequest('GET', "statistics/by-entity/{$entityType}/{$entityId}", $params);
    }

    /**
     * Get completion rate statistics
     */
    public function getCompletionRate(array $filters = []): array
    {
        return $this->makeRequest('GET', 'statistics/completion-rate', $filters);
    }

    /**
     * Get issue rate statistics
     */
    public function getIssueRate(string $startDate = null, string $endDate = null): array
    {
        $params = array_filter([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        return $this->makeRequest('GET', 'statistics/issue-rate', $params);
    }

    // ===============================
    // HELPER METHODS
    // ===============================

    /**
     * Build checklist type data array
     */
    public function buildChecklistTypeData(string $name, string $description, bool $isMultiStage = false, bool $isActive = true, string $icon = null, string $color = null, int $departmentId = null): array
    {
        return array_filter([
            'name' => $name,
            'description' => $description,
            'is_multi_stage' => $isMultiStage,
            'is_active' => $isActive,
            'icon' => $icon,
            'color' => $color,
            'department_id' => $departmentId
        ], fn($value) => $value !== null);
    }

    /**
     * Build section data array
     */
    public function buildSectionData(string $name, string $description = null, string $icon = null, int $orderIndex = 0, int $stage = 1, bool $isActive = true): array
    {
        return array_filter([
            'name' => $name,
            'description' => $description,
            'icon' => $icon,
            'order_index' => $orderIndex,
            'stage' => $stage,
            'is_active' => $isActive
        ], fn($value) => $value !== null);
    }

    /**
     * Build item data array
     */
    public function buildItemData(string $title, string $description = null, string $type = 'checkbox', array $options = null, array $validationRules = null, bool $isRequired = false, int $orderIndex = 0, bool $isActive = true): array
    {
        return array_filter([
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'options' => $options,
            'validation_rules' => $validationRules,
            'is_required' => $isRequired,
            'order_index' => $orderIndex,
            'is_active' => $isActive
        ], fn($value) => $value !== null);
    }

    /**
     * Build checklist data array
     */
    public function buildChecklistData(int $checklistTypeId, string $title, string $description = null, string $entityType = null, int $entityId = null, string $status = 'pending', int $createdBy = null, int $department_id = null, string $dueDate = null, int $id_filial = null): array
    {
        return array_filter([
            'checklist_type_id' => $checklistTypeId,
            'title' => $title,
            'description' => $description,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'status' => $status,
            'id_filial' => $id_filial,
            'created_by' => $createdBy,
            'department_id' => $department_id,
            'due_date' => $dueDate
        ], fn($value) => $value !== null);
    }
}

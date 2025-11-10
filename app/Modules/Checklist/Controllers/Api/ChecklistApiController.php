<?php

namespace App\Modules\Checklist\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Controller que faz bridge/proxy para a API Lumen do sistema checklist
 * 
 * FUNCIONALIDADE:
 * - Recebe calls do JavaScript React
 * - Faz proxy interno para API Lumen (10.10.1.5:8443)
 * - Retorna responses JSON para o frontend
 * - Elimina problemas de CORS/SSL
 */
class ChecklistApiController extends Controller
{
    /**
     * Base URL da API Lumen (IP interno)
     */
    private const API_BASE_URL = 'https://carvalima.unitopconsultoria.com.br/api/v2/checklist';

    /**
     * Timeout para requisições HTTP
     */
    private const HTTP_TIMEOUT = 30;

    /**
     * Fazer requisição para API Lumen com tratamento de erros
     */
    private function makeApiRequest(string $method, string $endpoint, array $data = []): JsonResponse
    {
        try {
            $url = self::API_BASE_URL . $endpoint;

            Log::info('ChecklistApi: Fazendo requisição', [
                'method' => $method,
                'url' => $url,
                'data_size' => count($data)
            ]);

            // Configurar cliente HTTP
            $http = Http::timeout(self::HTTP_TIMEOUT)
                ->withOptions([
                    'verify' => false, // Ignorar SSL para IP interno
                    'allow_redirects' => false
                ]);

            // Fazer requisição baseada no método
            $response = match (strtoupper($method)) {
                'GET' => $http->get($url, $data),
                'POST' => $http->post($url, $data),
                'PUT' => $http->put($url, $data),
                'PATCH' => $http->patch($url, $data),
                'DELETE' => $http->delete($url, $data),
                default => throw new \InvalidArgumentException("Método HTTP inválido: {$method}")
            };

            // Log da resposta
            Log::info('ChecklistApi: Resposta recebida', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);

            // Retornar resposta com mesmo status code
            return response()->json(
                $response->json(),
                $response->status()
            );
        } catch (\Exception $e) {
            Log::error('ChecklistApi: Erro na requisição', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro de conectividade com a API',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // HEALTH CHECK
    // ========================================

    /**
     * Health check da API
     */
    public function health(): JsonResponse
    {
        return $this->makeApiRequest('GET', '/health');
    }

    // ========================================
    // ESTATÍSTICAS E OVERVIEW
    // ========================================

    /**
     * Estatísticas gerais do dashboard
     */
    public function statisticsOverview(): JsonResponse
    {
        return $this->makeApiRequest('GET', '/statistics/overview');
    }

    /**
     * Estatísticas por tipo
     */
    public function statisticsByType(int $typeId): JsonResponse
    {
        return $this->makeApiRequest('GET', "/statistics/by-type/{$typeId}");
    }

    /**
     * Taxa de conclusão
     */
    public function completionRate(): JsonResponse
    {
        return $this->makeApiRequest('GET', '/statistics/completion-rate');
    }

    /**
     * Análise de qualidade
     */
    public function qualityAnalysis(): JsonResponse
    {
        return $this->makeApiRequest('GET', '/statistics/quality-analysis');
    }

    // ========================================
    // TIPOS DE CHECKLIST
    // ========================================

    /**
     * Listar tipos de checklist
     */
    public function types(Request $request): JsonResponse
    {
        return $this->makeApiRequest('GET', '/types', $request->query());
    }

    /**
     * Obter tipo específico
     */
    public function typeShow(int $id): JsonResponse
    {
        return $this->makeApiRequest('GET', "/types/{$id}");
    }

    /**
     * Criar novo tipo
     */
    public function typeStore(Request $request): JsonResponse
    {
        return $this->makeApiRequest('POST', '/types', $request->all());
    }

    /**
     * Atualizar tipo
     */
    public function typeUpdate(int $id, Request $request): JsonResponse
    {
        return $this->makeApiRequest('PUT', "/types/{$id}", $request->all());
    }

    /**
     * Deletar tipo
     */
    public function typeDestroy(int $id): JsonResponse
    {
        return $this->makeApiRequest('DELETE', "/types/{$id}");
    }

    // ========================================
    // CHECKLISTS
    // ========================================

    /**
     * Listar checklists
     */
    public function checklists(Request $request): JsonResponse
    {
        return $this->makeApiRequest('GET', '/checklists', $request->query());
    }

    /**
     * Obter checklist específico
     */
    public function checklistShow(int $id): JsonResponse
    {
        return $this->makeApiRequest('GET', "/checklists/{$id}");
    }

    /**
     * Criar novo checklist
     */
    public function checklistStore(Request $request): JsonResponse
    {
        return $this->makeApiRequest('POST', '/checklists', $request->all());
    }

    /**
     * Atualizar checklist
     */
    public function checklistUpdate(int $id, Request $request): JsonResponse
    {
        return $this->makeApiRequest('PUT', "/checklists/{$id}", $request->all());
    }

    /**
     * Deletar checklist
     */
    public function checklistDestroy(int $id): JsonResponse
    {
        return $this->makeApiRequest('DELETE', "/checklists/{$id}");
    }

    // ========================================
    // USUÁRIOS (INTEGRAÇÃO EMPRESA)
    // ========================================

    /**
     * Listar usuários
     */
    public function users(Request $request): JsonResponse
    {
        return $this->makeApiRequest('GET', '/users', $request->query());
    }

    /**
     * Usuários para seleção
     */
    public function usersForSelection(Request $request): JsonResponse
    {
        return $this->makeApiRequest('GET', '/users/for-selection', $request->query());
    }

    /**
     * Buscar usuários
     */
    public function usersSearch(Request $request): JsonResponse
    {
        return $this->makeApiRequest('GET', '/users/search', $request->query());
    }

    // ========================================
    // VEÍCULOS (INTEGRAÇÃO EMPRESA)
    // ========================================

    /**
     * Listar veículos
     */
    public function vehicles(Request $request): JsonResponse
    {
        return $this->makeApiRequest('GET', '/vehicles', $request->query());
    }

    /**
     * Veículos para seleção
     */
    public function vehiclesForSelection(Request $request): JsonResponse
    {
        return $this->makeApiRequest('GET', '/vehicles/for-selection', $request->query());
    }

    /**
     * Buscar veículos
     */
    public function vehiclesSearch(Request $request): JsonResponse
    {
        return $this->makeApiRequest('GET', '/vehicles/search', $request->query());
    }

    // ========================================
    // DEPARTAMENTOS (INTEGRAÇÃO EMPRESA)
    // ========================================

    /**
     * Listar departamentos
     */
    public function departments(Request $request): JsonResponse
    {
        return $this->makeApiRequest('GET', '/departments', $request->query());
    }

    /**
     * Departamentos para seleção
     */
    public function departmentsForSelection(Request $request): JsonResponse
    {
        return $this->makeApiRequest('GET', '/departments/for-selection', $request->query());
    }

    /**
     * Hierarquia departamental
     */
    public function departmentsHierarchy(): JsonResponse
    {
        return $this->makeApiRequest('GET', '/departments/hierarchy');
    }

    // ========================================
    // FILIAIS (INTEGRAÇÃO EMPRESA)
    // ========================================

    /**
     * Listar filiais
     */
    public function branches(Request $request): JsonResponse
    {
        return $this->makeApiRequest('GET', '/branches', $request->query());
    }

    /**
     * Filiais para seleção
     */
    public function branchesForSelection(): JsonResponse
    {
        return $this->makeApiRequest('GET', '/branches/for-selection');
    }

    /**
     * Estrutura organizacional
     */
    public function organizationalStructure(): JsonResponse
    {
        return $this->makeApiRequest('GET', '/branches/organizational-structure');
    }

    // ========================================
    // TEMPLATES
    // ========================================

    /**
     * Listar templates
     */
    public function templates(Request $request): JsonResponse
    {
        return $this->makeApiRequest('GET', '/templates', $request->query());
    }

    /**
     * Criar template
     */
    public function templateStore(Request $request): JsonResponse
    {
        return $this->makeApiRequest('POST', '/templates', $request->all());
    }

    // ========================================
    // SEÇÕES E ITENS
    // ========================================

    /**
     * Seções de um tipo
     */
    public function typeSections(int $typeId): JsonResponse
    {
        return $this->makeApiRequest('GET', "/types/{$typeId}/sections");
    }

    /**
     * Criar seção
     */
    public function sectionStore(int $typeId, Request $request): JsonResponse
    {
        return $this->makeApiRequest('POST', "/types/{$typeId}/sections", $request->all());
    }

    /**
     * Itens de uma seção
     */
    public function sectionItems(int $sectionId): JsonResponse
    {
        return $this->makeApiRequest('GET', "/sections/{$sectionId}/items");
    }

    /**
     * Criar item
     */
    public function itemStore(int $sectionId, Request $request): JsonResponse
    {
        return $this->makeApiRequest('POST', "/sections/{$sectionId}/items", $request->all());
    }

    // ========================================
    // STAGE ASSIGNMENTS (MULTI-STAGE)
    // ========================================

    /**
     * Responsáveis disponíveis
     */
    public function availableAssignees(): JsonResponse
    {
        return $this->makeApiRequest('GET', '/stage-assignments/available-assignees');
    }

    /**
     * Atribuições múltiplas
     */
    public function assignMultiple(Request $request): JsonResponse
    {
        return $this->makeApiRequest('POST', '/stage-assignments/assign-multiple', $request->all());
    }

    // ========================================
    // MÉTODO GENÉRICO PARA ENDPOINTS NÃO MAPEADOS
    // ========================================

    /**
     * Método catch-all para endpoints não explicitamente mapeados
     */
    public function proxy(Request $request, string $path = ''): JsonResponse
    {
        $method = $request->method();
        $endpoint = '/' . ltrim($path, '/');

        $data = $method === 'GET' ? $request->query() : $request->all();

        return $this->makeApiRequest($method, $endpoint, $data);
    }
}

<?php

namespace App\Console\Commands;

use App\Helpers\PermissionHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class AuditControllersPermissions extends Command
{
    protected $signature = 'permissions:audit-controllers {--fix : Aplicar correções automaticamente} {--verbose : Mostrar detalhes verbosos}';
    
    protected $description = 'Audita controllers administrativos e verifica se possuem proteção de permissões adequada';

    protected array $stats = [
        'total_controllers' => 0,
        'protected_controllers' => 0,
        'unprotected_controllers' => 0,
        'partially_protected' => 0,
        'missing_permissions' => 0,
        'total_methods' => 0,
        'protected_methods' => 0,
    ];

    protected array $findings = [];
    protected array $recommendations = [];

    public function handle(): int
    {
        $this->info('🔍 Iniciando auditoria de permissões em controllers...');
        $this->newLine();

        $controllers = $this->getAdminControllers();
        
        if (empty($controllers)) {
            $this->error('❌ Nenhum controller administrativo encontrado!');
            return 1;
        }

        $this->stats['total_controllers'] = count($controllers);
        
        foreach ($controllers as $controller) {
            $this->auditController($controller);
        }

        $this->displayResults();
        
        if ($this->option('fix')) {
            $this->applyFixes();
        }

        return 0;
    }

    /**
     * Obtém todos os controllers administrativos
     */
    private function getAdminControllers(): array
    {
        $controllers = [];
        $controllerPath = app_path('Http/Controllers/Admin');
        
        if (!File::exists($controllerPath)) {
            return [];
        }

        $files = File::allFiles($controllerPath);
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $className = $this->getClassNameFromFile($file);
                if ($className && class_exists($className)) {
                    $controllers[] = $className;
                }
            }
        }

        return $controllers;
    }

    /**
     * Extrai o nome da classe de um arquivo
     */
    private function getClassNameFromFile($file): ?string
    {
        $relativePath = str_replace(app_path(), '', $file->getPathname());
        $relativePath = str_replace(['/', '.php'], ['\\', ''], $relativePath);
        
        return 'App' . $relativePath;
    }

    /**
     * Audita um controller específico
     */
    private function auditController(string $controllerClass): void
    {
        $reflection = new ReflectionClass($controllerClass);
        $controllerName = $reflection->getShortName();
        
        if ($this->shouldSkipController($controllerName)) {
            return;
        }

        $methods = $this->getPublicMethods($reflection);
        $this->stats['total_methods'] += count($methods);

        $controllerAnalysis = [
            'name' => $controllerName,
            'class' => $controllerClass,
            'methods' => [],
            'has_permission_check' => false,
            'has_middleware' => false,
            'protection_level' => 'none'
        ];

        // Verificar se o controller tem alguma proteção
        $controllerAnalysis['has_permission_check'] = $this->hasPermissionChecks($reflection);
        $controllerAnalysis['has_middleware'] = $this->hasMiddleware($reflection);

        // Analisar cada método
        foreach ($methods as $method) {
            $methodAnalysis = $this->analyzeMethod($method, $controllerName);
            $controllerAnalysis['methods'][] = $methodAnalysis;
            
            if ($methodAnalysis['is_protected']) {
                $this->stats['protected_methods']++;
            }
        }

        // Determinar nível de proteção
        $controllerAnalysis['protection_level'] = $this->determineProtectionLevel($controllerAnalysis);
        
        // Atualizar estatísticas
        switch ($controllerAnalysis['protection_level']) {
            case 'full':
                $this->stats['protected_controllers']++;
                break;
            case 'partial':
                $this->stats['partially_protected']++;
                break;
            case 'none':
                $this->stats['unprotected_controllers']++;
                break;
        }

        $this->findings[] = $controllerAnalysis;
        
        // Gerar recomendações
        $this->generateRecommendations($controllerAnalysis);
    }

    /**
     * Verifica se deve pular o controller na auditoria
     */
    private function shouldSkipController(string $controllerName): bool
    {
        $skipList = [
            'DashboardController',
            'ProfileController',
            'ApiController',
            'SearchController',
        ];

        return in_array($controllerName, $skipList);
    }

    /**
     * Obtém métodos públicos do controller
     */
    private function getPublicMethods(ReflectionClass $reflection): array
    {
        return array_filter(
            $reflection->getMethods(ReflectionMethod::IS_PUBLIC),
            fn($method) => $method->class === $reflection->name && !$method->isConstructor()
        );
    }

    /**
     * Verifica se o controller tem verificações de permissão
     */
    private function hasPermissionChecks(ReflectionClass $reflection): bool
    {
        $source = file_get_contents($reflection->getFileName());
        
        $permissionPatterns = [
            'PermissionHelper::',
            'hasPermissionTo',
            'hasAnyPermission', 
            'hasModuleAccess',
            'can\(',
            'cannot\(',
            'authorize\(',
        ];

        foreach ($permissionPatterns as $pattern) {
            if (str_contains($source, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se o controller tem middleware de permissões
     */
    private function hasMiddleware(ReflectionClass $reflection): bool
    {
        $source = file_get_contents($reflection->getFileName());
        
        $middlewarePatterns = [
            'middleware',
            'permission:',
            'role:',
            'can:',
        ];

        foreach ($middlewarePatterns as $pattern) {
            if (str_contains($source, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Analisa um método específico
     */
    private function analyzeMethod(ReflectionMethod $method, string $controllerName): array
    {
        $methodName = $method->name;
        $expectedPermission = $this->getExpectedPermission($methodName, $controllerName);
        
        return [
            'name' => $methodName,
            'expected_permission' => $expectedPermission,
            'is_protected' => false, // Seria necessário análise mais profunda
            'has_permission_check' => $this->methodHasPermissionCheck($method),
            'needs_protection' => $this->methodNeedsProtection($methodName),
        ];
    }

    /**
     * Verifica se um método específico tem verificação de permissão
     */
    private function methodHasPermissionCheck(ReflectionMethod $method): bool
    {
        // Para uma análise completa, seria necessário parsear o código AST
        // Por ora, retornamos false para simplificar
        return false;
    }

    /**
     * Determina se um método precisa de proteção
     */
    private function methodNeedsProtection(string $methodName): bool
    {
        $protectedMethods = ['index', 'show', 'create', 'store', 'edit', 'update', 'destroy'];
        return in_array($methodName, $protectedMethods);
    }

    /**
     * Obtém a permissão esperada para um método
     */
    private function getExpectedPermission(string $methodName, string $controllerName): ?string
    {
        $module = strtolower(str_replace('Controller', '', $controllerName));
        
        $actionMap = [
            'index' => 'ver',
            'show' => 'ver',
            'create' => 'criar',
            'store' => 'criar',
            'edit' => 'editar',
            'update' => 'editar',
            'destroy' => 'excluir',
        ];

        if (isset($actionMap[$methodName])) {
            return $actionMap[$methodName] . '_' . Str::snake(Str::plural($module));
        }

        return null;
    }

    /**
     * Determina o nível de proteção do controller
     */
    private function determineProtectionLevel(array $analysis): string
    {
        if ($analysis['has_permission_check'] && $analysis['has_middleware']) {
            return 'full';
        }
        
        if ($analysis['has_permission_check'] || $analysis['has_middleware']) {
            return 'partial';
        }
        
        return 'none';
    }

    /**
     * Gera recomendações para um controller
     */
    private function generateRecommendations(array $analysis): void
    {
        if ($analysis['protection_level'] === 'none') {
            $this->recommendations[] = [
                'type' => 'critical',
                'controller' => $analysis['name'],
                'message' => 'Controller completamente desprotegido - implementar middleware de permissões',
                'action' => 'add_middleware'
            ];
        } elseif ($analysis['protection_level'] === 'partial') {
            $this->recommendations[] = [
                'type' => 'warning',
                'controller' => $analysis['name'],
                'message' => 'Controller parcialmente protegido - revisar implementação',
                'action' => 'review_protection'
            ];
        }
    }

    /**
     * Exibe os resultados da auditoria
     */
    private function displayResults(): void
    {
        $this->newLine(2);
        $this->info('📊 RESULTADOS DA AUDITORIA');
        $this->info('=' . str_repeat('=', 50));
        
        // Estatísticas gerais
        $this->table(
            ['Métrica', 'Quantidade', 'Porcentagem'],
            [
                ['Controllers Total', $this->stats['total_controllers'], '100%'],
                ['Controllers Protegidos', $this->stats['protected_controllers'], $this->percentage('protected_controllers', 'total_controllers')],
                ['Controllers Desprotegidos', $this->stats['unprotected_controllers'], $this->percentage('unprotected_controllers', 'total_controllers')],
                ['Parcialmente Protegidos', $this->stats['partially_protected'], $this->percentage('partially_protected', 'total_controllers')],
                ['Métodos Total', $this->stats['total_methods'], ''],
                ['Métodos Protegidos', $this->stats['protected_methods'], $this->percentage('protected_methods', 'total_methods')],
            ]
        );

        // Controllers críticos
        $this->newLine();
        $this->error('🚨 CONTROLLERS CRÍTICOS (SEM PROTEÇÃO):');
        
        foreach ($this->findings as $finding) {
            if ($finding['protection_level'] === 'none') {
                $this->line("❌ {$finding['name']} - {$finding['class']}");
            }
        }

        // Recomendações críticas
        $this->newLine();
        $criticalRecommendations = array_filter($this->recommendations, fn($r) => $r['type'] === 'critical');
        
        if (!empty($criticalRecommendations)) {
            $this->error('🔥 AÇÕES CRÍTICAS NECESSÁRIAS:');
            foreach ($criticalRecommendations as $rec) {
                $this->line("• {$rec['controller']}: {$rec['message']}");
            }
        }

        // Resumo de risco
        $this->newLine();
        $riskLevel = $this->calculateRiskLevel();
        $this->line("🎯 NÍVEL DE RISCO: <fg={$riskLevel['color']}>{$riskLevel['level']}</>");
        $this->line("📋 RECOMENDAÇÃO: {$riskLevel['recommendation']}");
    }

    /**
     * Calcula porcentagem
     */
    private function percentage(string $part, string $total): string
    {
        if ($this->stats[$total] == 0) return '0%';
        return round(($this->stats[$part] / $this->stats[$total]) * 100, 1) . '%';
    }

    /**
     * Calcula o nível de risco baseado nas estatísticas
     */
    private function calculateRiskLevel(): array
    {
        $unprotectedPercentage = ($this->stats['unprotected_controllers'] / $this->stats['total_controllers']) * 100;
        
        if ($unprotectedPercentage > 70) {
            return [
                'level' => 'CRÍTICO',
                'color' => 'red',
                'recommendation' => 'Implementação imediata de middleware automático é OBRIGATÓRIA!'
            ];
        } elseif ($unprotectedPercentage > 40) {
            return [
                'level' => 'ALTO',
                'color' => 'yellow',
                'recommendation' => 'Implementar proteções dentro de 48 horas.'
            ];
        } elseif ($unprotectedPercentage > 20) {
            return [
                'level' => 'MÉDIO',
                'color' => 'blue',
                'recommendation' => 'Revisar e corrigir controllers desprotegidos.'
            ];
        } else {
            return [
                'level' => 'BAIXO',
                'color' => 'green',
                'recommendation' => 'Manter monitoramento regular.'
            ];
        }
    }

    /**
     * Aplica correções automáticas
     */
    private function applyFixes(): void
    {
        $this->newLine();
        $this->info('🔧 Aplicando correções automáticas...');
        
        // Implementar correções aqui
        $this->warn('⚠️  Correções automáticas ainda não implementadas.');
        $this->info('💡 Execute os comandos de aplicação de middleware manualmente.');
    }
}
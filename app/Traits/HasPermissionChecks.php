<?php

namespace App\Traits;

use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Trait para adicionar verificações automáticas de permissão em controllers
 * 
 * Uso:
 * 1. Adicionar `use HasPermissionChecks;` no controller
 * 2. Chamar `$this->checkPermission('ver')` nos métodos
 * 3. Ou usar `$this->authorize($action)` para verificação automática
 */
trait HasPermissionChecks
{
    /**
     * Módulo base do controller (extraído automaticamente do nome da classe)
     */
    protected ?string $moduleFromController = null;

    /**
     * Verifica se o usuário tem permissão para uma ação específica
     * 
     * @param string $action Ação (ver, criar, editar, excluir)
     * @param string|null $customModule Módulo customizado (opcional)
     * @throws AccessDeniedHttpException
     */
    protected function checkPermission(string $action, ?string $customModule = null): void
    {
        $user = Auth::user();
        
        // Superuser sempre tem acesso
        if ($user && $user->isSuperuser()) {
            return;
        }

        $module = $customModule ?? $this->getControllerModule();
        $permission = "{$action}_{$module}";

        // Verificar permissão específica
        if (PermissionHelper::hasAnyPermission([$permission])) {
            return;
        }

        // Fallback: verificar acesso ao módulo
        if ($action === 'ver' && PermissionHelper::hasModuleAccess($module)) {
            return;
        }

        // Fallback: verificar por prefixo
        if (PermissionHelper::hasAnyPermissionStartingWith($module)) {
            return;
        }

        $this->throwAccessDenied($permission, $user);
    }

    /**
     * Verifica múltiplas permissões (OR - qualquer uma serve)
     * 
     * @param array $actions Array de ações
     * @param string|null $customModule Módulo customizado (opcional)
     * @throws AccessDeniedHttpException
     */
    protected function checkAnyPermission(array $actions, ?string $customModule = null): void
    {
        $user = Auth::user();
        
        if ($user && $user->isSuperuser()) {
            return;
        }

        $module = $customModule ?? $this->getControllerModule();
        $permissions = array_map(fn($action) => "{$action}_{$module}", $actions);

        if (PermissionHelper::hasAnyPermission($permissions)) {
            return;
        }

        $this->throwAccessDenied(implode(' OU ', $permissions), $user);
    }

    /**
     * Verifica se o usuário tem acesso ao módulo do controller
     * 
     * @param string|null $customModule Módulo customizado (opcional)
     * @throws AccessDeniedHttpException
     */
    protected function checkModuleAccess(?string $customModule = null): void
    {
        $user = Auth::user();
        
        if ($user && $user->isSuperuser()) {
            return;
        }

        $module = $customModule ?? $this->getControllerModule();

        if (PermissionHelper::hasModuleAccess($module)) {
            return;
        }

        $this->throwAccessDenied("acesso ao módulo {$module}", $user);
    }

    /**
     * Autorização automática baseada no método do controller
     * 
     * @param string|null $action Ação específica (opcional, auto-detecta se não fornecida)
     * @param string|null $customModule Módulo customizado (opcional)
     * @throws AccessDeniedHttpException
     */
    protected function authorize(?string $action = null, ?string $customModule = null): void
    {
        if (!$action) {
            $action = $this->detectActionFromMethod();
        }

        if ($action) {
            $this->checkPermission($action, $customModule);
        } else {
            // Fallback: verificar acesso ao módulo
            $this->checkModuleAccess($customModule);
        }
    }

    /**
     * Obtém o módulo do controller baseado no nome da classe
     */
    protected function getControllerModule(): string
    {
        if ($this->moduleFromController === null) {
            $className = class_basename(static::class);
            $module = str_replace('Controller', '', $className);
            $this->moduleFromController = Str::snake(Str::plural(strtolower($module)));
        }

        return $this->moduleFromController;
    }

    /**
     * Define manualmente o módulo do controller
     */
    protected function setControllerModule(string $module): void
    {
        $this->moduleFromController = $module;
    }

    /**
     * Detecta a ação baseada no método chamado
     */
    protected function detectActionFromMethod(): ?string
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        
        foreach ($backtrace as $trace) {
            if (isset($trace['function']) && $trace['class'] === static::class) {
                $method = $trace['function'];
                
                $actionMap = [
                    'index' => 'ver',
                    'show' => 'ver',
                    'create' => 'criar',
                    'store' => 'criar',
                    'edit' => 'editar',
                    'update' => 'editar',
                    'destroy' => 'excluir',
                ];

                return $actionMap[$method] ?? null;
            }
        }

        return null;
    }

    /**
     * Lança exceção de acesso negado
     */
    protected function throwAccessDenied(string $permission, $user): void
    {
        // Log do acesso negado
        \Log::warning('Acesso negado em controller', [
            'user_id' => $user->id ?? null,
            'user_email' => $user->email ?? null,
            'controller' => static::class,
            'permission_required' => $permission,
            'ip' => request()->ip(),
            'route' => request()->path(),
            'timestamp' => now()->toISOString(),
        ]);

        throw new AccessDeniedHttpException(
            "Acesso negado. Permissão necessária: {$permission}"
        );
    }

    /**
     * Verifica se o usuário atual é superuser
     */
    protected function isSuperuser(): bool
    {
        $user = Auth::user();
        return $user && $user->isSuperuser();
    }

    /**
     * Verifica se o usuário tem uma permissão específica (helper rápido)
     */
    protected function hasPermission(string $permission): bool
    {
        return PermissionHelper::hasAnyPermission([$permission]);
    }

    /**
     * Verifica se o usuário tem qualquer permissão de um array (helper rápido)
     */
    protected function hasAnyOf(array $permissions): bool
    {
        return PermissionHelper::hasAnyPermission($permissions);
    }

    /**
     * Verifica se o usuário tem permissões de um módulo (helper rápido)
     */
    protected function hasModuleAccess(string $module): bool
    {
        return PermissionHelper::hasModuleAccess($module);
    }
}
<?php

namespace App\Helpers;

use App\Services\ModulePermissionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Helper para permissões baseadas em módulos
 * Integrado com ModulePermissionService
 */
class PermissionHelper
{
    /**
     * Verifica se o usuário tem acesso a um módulo específico
     * Novo sistema: verifica permissão {modulo}.acessar_modulo
     */
    public static function hasModuleAccess(string $module): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Superuser sempre tem acesso
        if ($user->is_superuser) {
            return true;
        }

        // 1. Verificar permissão de acesso ao módulo (novo formato)
        $moduleAccessPermission = "{$module}.acessar_modulo";
        if ($user->can($moduleAccessPermission)) {
            return true;
        }

        // 2. Fallback: verificar se tem qualquer permissão que comece com o módulo
        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();

        foreach ($userPermissions as $permission) {
            if (str_starts_with($permission, $module . '.')) {
                return true;
            }
        }

        // 3. Fallback para permissões antigas (compatibilidade temporária)
        if ($user->can("acessar_{$module}")) {
            return true;
        }

        // 4. Verificar se tem permissões específicas do módulo (padrão: {verbo}_{modulo})
        // IMPORTANTE: Não considerar permissões compostas (ex: ver_estoque_combustivel não dá acesso ao módulo 'estoque')
        foreach ($userPermissions as $permission) {
            // Extrair o padrão: {verbo}_{modulo}
            if (preg_match('/^(ver|criar|editar|excluir|acessar|relatorio|baixar|transferir|aprovar|validar|faturar|lancar|ajustar)_' . preg_quote($module, '/') . '$/', $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se o usuário tem qualquer permissão que comece com o prefixo especificado
     */
    public static function hasAnyPermissionStartingWith(string $prefix): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Superuser sempre tem acesso
        if ($user->is_superuser) {
            return true;
        }

        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();

        foreach ($userPermissions as $permission) {
            if (str_starts_with($permission, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se o usuário tem qualquer uma das permissões especificadas
     */
    public static function hasAnyPermission(array $permissions): bool
    {
        $user = Auth::user();

        if (! $user || empty($permissions)) {
            return false;
        }

        // Superuser sempre tem acesso
        if ($user->is_superuser) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obter módulos acessíveis pelo usuário
     * Baseado nas permissões reais que o usuário possui
     */
    public static function getUserAccessibleModules(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        // Superuser tem acesso a tudo
        if ($user->is_superuser) {
            return ['all_modules']; // Indica acesso total
        }

        // Extrair módulos das permissões do usuário
        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
        $modules = [];

        foreach ($userPermissions as $permission) {
            // Extrair módulo da permissão (ex: ver_descartepneus -> descartepneus)
            if (str_contains($permission, '_')) {
                $parts = explode('_', $permission, 2);
                if (count($parts) >= 2) {
                    $module = $parts[1];
                    if (! in_array($module, $modules)) {
                        $modules[] = $module;
                    }
                }
            }
        }

        return $modules;
    }

    /**
     * Limpar cache de permissões do usuário
     * Wrapper para o método do User model
     */
    public static function clearUserPermissionsCache(?int $userId = null): bool
    {
        try {
            if ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    $user->clearPermissionCache();
                }
            } else {
                $user = Auth::user();
                if ($user) {
                    $user->clearPermissionCache();
                }
            }

            // Limpar cache do Spatie Permission
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao limpar cache de permissões', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Verificar se usuário tem pelo menos uma permissão de visualização
     * Útil para verificar acesso básico ao admin
     */
    public static function hasAnyViewPermission(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Superuser sempre tem acesso
        if ($user->is_superuser) {
            return true;
        }

        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();

        foreach ($userPermissions as $permission) {
            if (str_starts_with($permission, 'ver_')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar se usuário pode executar uma ação específica em um módulo
     * Ex: can('abastecimentos', 'abastecimento_manual', 'editar')
     */
    public static function can(string $module, string $funcionalidade, string $acao): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->is_superuser) {
            return true;
        }

        // Formato: {modulo}.{funcionalidade}.{acao}
        $permission = "{$module}.{$funcionalidade}.{$acao}";

        return $user->can($permission);
    }

    /**
     * Obter todas as permissões do usuário agrupadas por módulo
     */
    public static function getUserPermissionsGroupedByModule(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        if ($user->is_superuser) {
            return ['superuser' => true];
        }

        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
        $grouped = [];

        foreach ($userPermissions as $perm) {
            // Formato: modulo.funcionalidade.acao ou modulo.acessar_modulo
            $parts = explode('.', $perm);

            if (count($parts) >= 2) {
                $module = $parts[0];
                $resto = implode('.', array_slice($parts, 1));

                if (! isset($grouped[$module])) {
                    $grouped[$module] = [];
                }

                $grouped[$module][] = $resto;
            }
        }

        return $grouped;
    }

    /**
     * Obter nome amigável de um módulo
     */
    public static function getModuleFriendlyName(string $module): string
    {
        $modules = ModulePermissionService::getModules();

        foreach ($modules as $mod) {
            if ($mod['nome'] === $module) {
                return $mod['nome_amigavel'];
            }
        }

        return ucfirst($module);
    }

    /**
     * Obter lista de módulos que o usuário pode acessar (com nomes amigáveis)
     */
    public static function getUserAccessibleModulesWithNames(): array
    {
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        $allModules = ModulePermissionService::getModules();
        $accessibleModules = [];

        foreach ($allModules as $module) {
            if (self::hasModuleAccess($module['nome'])) {
                $accessibleModules[] = [
                    'nome' => $module['nome'],
                    'nome_amigavel' => $module['nome_amigavel'],
                    'descricao' => $module['descricao'],
                    'icone' => $module['icone'] ?? null,
                ];
            }
        }

        return $accessibleModules;
    }

    /**
     * Método para debug - informações do usuário atual
     */
    public static function debugUserPermissions(): array
    {
        $user = Auth::user();

        if (! $user) {
            return ['error' => 'Usuário não autenticado'];
        }

        return [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'is_superuser' => $user->is_superuser,
            'total_permissions' => $user->getAllPermissions()->count(),
            'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            'permissions_grouped' => self::getUserPermissionsGroupedByModule(),
            'accessible_modules' => self::getUserAccessibleModulesWithNames(),
        ];
    }
}

<?php

namespace App\Observers;

use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class PermissionObserver
{
    /**
     * Handle the Permission "created" event.
     */
    public function created(Permission $permission): void
    {
        $this->clearAllPermissionsCache('created', $permission->name);
    }

    /**
     * Handle the Permission "updated" event.
     */
    public function updated(Permission $permission): void
    {
        $this->clearAllPermissionsCache('updated', $permission->name);
    }

    /**
     * Handle the Permission "deleted" event.
     */
    public function deleted(Permission $permission): void
    {
        $this->clearAllPermissionsCache('deleted', $permission->name);
    }

    /**
     * Handle the Permission "restored" event.
     */
    public function restored(Permission $permission): void
    {
        $this->clearAllPermissionsCache('restored', $permission->name);
    }

    /**
     * Handle the Permission "force deleted" event.
     */
    public function forceDeleted(Permission $permission): void
    {
        $this->clearAllPermissionsCache('force_deleted', $permission->name);
    }

    /**
     * Limpa cache de todos os usuários quando uma permissão é modificada
     */
    private function clearAllPermissionsCache(string $action, string $permissionName): void
    {
        try {
            // Limpar cache do Spatie Permission
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // Limpar cache geral do Laravel (chaves relacionadas a permissões)
            \Illuminate\Support\Facades\Cache::flush();
        } catch (\Exception $e) {
            Log::error('Erro ao limpar cache de permissões automaticamente', [
                'action' => $action,
                'permission' => $permissionName,
                'error' => $e->getMessage(),
                'observer' => 'PermissionObserver',
            ]);
        }
    }
}

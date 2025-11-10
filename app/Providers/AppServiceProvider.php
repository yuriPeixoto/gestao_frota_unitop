<?php

namespace App\Providers;

use App\Helpers\PermissionHelper;
use App\Models\CertificadoVeiculos;
use App\Models\HistoricoEventosSinistro;
use App\Models\User;
use App\Observers\ExpirationDateObserver;
use App\Observers\HistoricoEventosSinistroObserver;
use App\Observers\PermissionObserver;
use App\Services\ChecklistService;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar ChecklistService como singleton
        $this->app->singleton(ChecklistService::class, function ($app) {
            return new ChecklistService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // =====================================================
        // OBSERVERS
        // =====================================================
        $this->registerObservers();

        // =====================================================
        // BLADE DIRECTIVES
        // =====================================================
        $this->registerBladeDirectives();

        // =====================================================
        // RATE LIMITING
        // =====================================================
        $this->configureRateLimiting();

        // =====================================================
        // GATES E POLICIES - BYPASS SUPERUSER
        // =====================================================
        $this->registerGates();

        // =====================================================
        // EVENT LISTENERS
        // =====================================================
        $this->registerEventListeners();

        // =====================================================
        // PAGINATION
        // =====================================================
        $this->configurePagination();

        // =====================================================
        // SISTEMA DE PERMISSÕES - INVALIDAÇÃO AUTOMÁTICA
        // =====================================================
        $this->registerPermissionEventListeners();
    }

    /**
     * Registrar todos os observers do sistema
     */
    private function registerObservers(): void
    {
        // Observer existente para certificados
        CertificadoVeiculos::observe(ExpirationDateObserver::class);

        // Observer para sistema de permissões
        Permission::observe(PermissionObserver::class);

        // Observer para histórico de eventos de sinistro
        HistoricoEventosSinistro::observe(HistoricoEventosSinistroObserver::class);
    }

    /**
     * Registrar diretivas personalizadas do Blade
     */
    private function registerBladeDirectives(): void
    {
        Blade::directive('statusBadge', function ($status) {
            return "<?php
                \$statusLower = strtolower($status);
                \$classes = match (\$statusLower) {
                    'quitado' => 'bg-green-100 text-green-800',
                    'parcial' => 'bg-yellow-100 text-yellow-800',
                    'cancelados' => 'bg-red-100 text-red-800',
                    'a vencer' => 'bg-blue-100 text-blue-800',
                    'vencido' => 'bg-purple-100 text-purple-800',
                    'Não informado' => 'bg-gray-100 text-gray-800',
                    default => 'bg-black-100 text-black-800',
                };
            ?>
<span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \$classes }}\">
    {{ ucfirst($status) }}
</span>";
        });
    }

    /**
     * Configurar rate limiting para API
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(1000)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Registrar Gates e Policies do sistema
     * 🚀 HOTFIX: Bypass superuser global
     */
    private function registerGates(): void
    {
        Gate::before(function (User $user, $ability) {
            if ($user->is_superuser) {
                return true;
            }

            return null;
        });
    }

    /**
     * Registrar event listeners gerais do sistema
     */
    private function registerEventListeners(): void
    {
        // Event listener para login do usuário
        Event::listen(Login::class, function ($event) {
            $event->user->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);

            // Pré-carregar cache de permissões no login para melhor performance
            /*try {
                PermissionHelper::preloadPermissionsCache($event->user->id);
            } catch (\Exception $e) {
                // Log silencioso - não deve quebrar o login se falhar
                Log::warning('Falha ao pré-carregar cache de permissões no login', [
                    'user_id' => $event->user->id,
                    'error' => $e->getMessage(),
                ]);
            }*/
        });
    }

    /**
     * Configurar paginação personalizada
     */
    private function configurePagination(): void
    {
        Paginator::useTailwind();
        Paginator::defaultView('pagination.custom-tailwind');
        // Paginator::defaultSimpleView('pagination.simple-tailwind');
    }

    /**
     * Registra event listeners para invalidação automática de cache de permissões
     */
    private function registerPermissionEventListeners(): void
    {
        // Event Listener para quando uma permissão é atribuída a um model
        Event::listen('permission.assigned', function ($event) {
            $this->handlePermissionEvent('assigned', $event);
        });

        // Event Listener para quando uma permissão é removida de um model
        Event::listen('permission.revoked', function ($event) {
            $this->handlePermissionEvent('revoked', $event);
        });

        // Event Listener para quando permissões são sincronizadas
        Event::listen('permission.synced', function ($event) {
            $this->handlePermissionEvent('synced', $event);
        });

        // Event Listener para mudanças em roles (se usado)
        Event::listen('role.assigned', function ($event) {
            $this->handleRoleEvent('assigned', $event);
        });

        Event::listen('role.revoked', function ($event) {
            $this->handleRoleEvent('revoked', $event);
        });

        Event::listen('role.synced', function ($event) {
            $this->handleRoleEvent('synced', $event);
        });

        // Event Listener genérico para mudanças na tabela model_has_permissions
        Event::listen('eloquent.created: *model_has_permissions*', function ($model) {
            $this->handleModelHasPermissionsChange('created', $model);
        });

        Event::listen('eloquent.updated: *model_has_permissions*', function ($model) {
            $this->handleModelHasPermissionsChange('updated', $model);
        });

        Event::listen('eloquent.deleted: *model_has_permissions*', function ($model) {
            $this->handleModelHasPermissionsChange('deleted', $model);
        });
    }

    /**
     * Handle permission events
     */
    private function handlePermissionEvent(string $action, $event): void
    {
        try {
            $modelType = $event->model ?? null;
            $modelId = $event->model_id ?? null;
            $permissionName = $event->permission->name ?? 'unknown';

            // Limpar cache específico se for um usuário
            if ($modelType === 'App\\Models\\User' && $modelId) {
                PermissionHelper::clearUserPermissionsCache($modelId);
            } else {
                // Para outros tipos de model, limpar cache geral
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

                Log::info('Cache geral de permissões limpo automaticamente via event', [
                    'action' => $action,
                    'event_type' => 'permission',
                    'model_type' => $modelType,
                    'model_id' => $modelId,
                    'permission' => $permissionName,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao processar event de permissão', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle role events
     */
    private function handleRoleEvent(string $action, $event): void
    {
        try {
            $modelType = $event->model ?? null;
            $modelId = $event->model_id ?? null;
            $roleName = $event->role->name ?? 'unknown';

            // Limpar cache específico se for um usuário
            if ($modelType === 'App\\Models\\User' && $modelId) {
                PermissionHelper::clearUserPermissionsCache($modelId);
            } else {
                // Para outros tipos de model, limpar cache geral
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

                Log::info('Cache geral de permissões limpo automaticamente via role event', [
                    'action' => $action,
                    'event_type' => 'role',
                    'model_type' => $modelType,
                    'model_id' => $modelId,
                    'role' => $roleName,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao processar event de role', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle model_has_permissions table changes
     */
    private function handleModelHasPermissionsChange(string $action, $model): void
    {
        try {
            $modelType = $model->model_type ?? null;
            $modelId = $model->model_id ?? null;
            $permissionId = $model->permission_id ?? null;

            // Limpar cache específico se for um usuário
            if ($modelType === 'App\\Models\\User' && $modelId) {
                PermissionHelper::clearUserPermissionsCache($modelId);
            } else {
                // Para outros tipos de model, limpar cache geral
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

                Log::info('Cache geral limpo via model_has_permissions change', [
                    'action' => $action,
                    'model_type' => $modelType,
                    'model_id' => $modelId,
                    'permission_id' => $permissionId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao processar mudança em model_has_permissions', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

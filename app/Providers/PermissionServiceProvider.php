<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Modules\Configuracoes\Models\User;
use App\Modules\Configuracoes\Models\Branch;
use App\Policies\BranchPolicy;
use App\Policies\UserPolicy;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Array de mapeamento de policies
     *
     */
    protected array $policies = [
        User::class   => UserPolicy::class,
        Branch::class => BranchPolicy::class
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        $this->registerGates();
    }

    protected function registerGates(): void
    {
        // Gate para superuser
        Gate::before(function (User $user) {
            if ($user->is_superuser) {
                return true;
            }
        });

        // Gates para verificações de permissões
        $this->registerPermissionGates();

        // Gates para gerenciamento de filiais
        $this->registerBranchGates();
    }

    protected function registerPermissionGates(): void
    {
        Gate::define('check-permission', function (User $user, $permission, $branchId = null) {
            return $user->hasPermission($permission, $branchId);
        });

        Gate::define('manage-permissions', function (User $user) {
            return $user->is_superuser || $user->hasPermissionTo('manage_permissions');
        });
    }

    protected function registerBranchGates(): void
    {
        Gate::define('manage-branch', function (User $user, Branch $branch) {
            return $user->is_superuser || $user->hasPermissionTo('manage_branch', $branch->id);
        });

        Gate::define('view-branch', function (User $user, Branch $branch) {
            return $user->is_superuser ||
                $user->hasPermissionTo('view_branch', $branch->id) ||
                $user->branch_id === $branch->id;
        });
    }
}

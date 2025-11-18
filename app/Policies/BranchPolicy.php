<?php

namespace App\Policies;

use App\Modules\Configuracoes\Models\Branch;
use App\Modules\Configuracoes\Models\User;
use Illuminate\Auth\Access\Response;

class BranchPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-branches');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Branch $branch): bool
    {
        // Usuário pode ver sua própria filial
        if ($user->branch_id === $branch->id) {
            return true;
        }

        return $user->hasPermissionTo('view-branch', $branch->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-branches');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Branch $branch): bool
    {
        return $user->hasPermissionTo('update-branch', $branch->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Branch $branch): bool
    {
        // Impede que a filial do usuário seja excluída
        if ($user->branch_id === $branch->id) {
            return false;
        }

        return $user->hasPermissionTo('delete-branch', $branch->id);
    }

    public function manageUsers(User $user, Branch $branch): bool
    {
        return $user->hasPermissionTo('manage-branch-users', $branch->id);
    }
}

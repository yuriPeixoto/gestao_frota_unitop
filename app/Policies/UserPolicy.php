<?php

namespace App\Policies;

use App\Modules\Configuracoes\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-users');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        if ($user->hasPermissionTo('view-users', $model->branch_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-users');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return $user->hasPermissionTo('update-users', $model->branch_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasPermissionTo('delete-users', $model->branch_id);
    }

    public function managePermissions(User $user, User $model): bool
    {
        // Superuser pode gerenciar permissões de qualquer usuário
        if ($user->is_superuser) {
            return true;
        }

        // Usuários com permissão específica podem gerenciar permissões dentro de sua filial
        return $user->hasPermissionTo('manage-permissions', $model->branch_id);
    }

    public function manageRoles(User $user, User $model): bool
    {
        if ($user->is_superuser) {
            return true;
        }

        return $user->hasPermissionTo('manage-roles', $model->branch_id);
    }
}

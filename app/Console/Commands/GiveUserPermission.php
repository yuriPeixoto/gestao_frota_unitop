<?php

namespace App\Console\Commands;

use App\Modules\Configuracoes\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class GiveUserPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:give-permission {user_id} {permission}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give permission to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $permissionName = $this->argument('permission');

        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuário com ID {$userId} não encontrado");
            return 1;
        }

        $permission = Permission::where('name', $permissionName)->first();
        if (!$permission) {
            $this->error("Permissão '{$permissionName}' não encontrada");
            return 1;
        }

        if ($user->hasPermissionTo($permission)) {
            $this->info("Usuário '{$user->name}' já possui a permissão '{$permissionName}'");
            return 0;
        }

        $user->givePermissionTo($permission);
        $this->info("Permissão '{$permissionName}' concedida ao usuário '{$user->name}' (ID: {$userId})");

        return 0;
    }
}

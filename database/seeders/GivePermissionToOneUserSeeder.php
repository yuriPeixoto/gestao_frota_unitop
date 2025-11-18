<?php


namespace Database\Seeders;

use App\Modules\Configuracoes\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class GivePermissionToOneUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obter o usuário com ID 52
        $user = User::find(52);

        if (!$user) {
            $this->command->error('Usuário com ID 52 não encontrado.');
            return;
        }

        // Obter todas as permissões
        $permissions = Permission::all();

        // Atribuir todas as permissões ao usuário
        foreach ($permissions as $permission) {
            $user->givePermissionTo($permission);
            $this->command->line("Permissão {$permission->name} atribuída ao usuário {$user->name}");
        }

        $this->command->info('Todas as permissões foram atribuídas ao usuário com ID 52.');
    }
}

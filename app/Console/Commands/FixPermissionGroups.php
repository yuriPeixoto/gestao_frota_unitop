<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Configuracoes\Models\PermissionGroup;
use App\Modules\Configuracoes\Models\Permission;

class FixPermissionGroups extends Command
{
    protected $signature = 'permissions:fix-groups';
    protected $description = 'Corrige os grupos de permissões existentes';

    public function handle()
    {
        $this->info('Corrigindo grupos de permissões...');

        // Encontra ou cria o grupo Sistema
        $systemGroup = PermissionGroup::firstOrCreate(
            ['name' => 'Sistema'],
            ['description' => 'Permissões do sistema']
        );

        // Move todas as permissões do grupo "Permissões" para "Sistema"
        $permissionsGroup = PermissionGroup::where('name', 'Permissões')->first();
        if ($permissionsGroup) {
            Permission::where('permission_group_id', $permissionsGroup->id)
                     ->update(['permission_group_id' => $systemGroup->id]);

            // Remove o grupo antigo
            $permissionsGroup->delete();
            $this->info('Permissões movidas para o grupo Sistema');
        }

        $this->info('Grupos de permissões corrigidos com sucesso!');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\PermissionGroup;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Criar grupo de permissões do sistema
        $systemGroup = PermissionGroup::create([
            'name' => 'Sistema',
            'description' => 'Permissões de sistema'
        ]);

        // Criar permissão de gerenciamento de permissões
        Permission::create([
            'name' => 'Gerenciar Permissões',
            'slug' => 'gerenciar_permissoes',
            'description' => 'Permite gerenciar todas as permissões do sistema',
            'permission_group_id' => $systemGroup->id
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Configuracoes\Models\Permission;
use App\Modules\Configuracoes\Models\PermissionGroup;
use App\Modules\Configuracoes\Models\User;
use Carbon\Carbon;

class BasicPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Garantir que os grupos existam
        $this->call(PermissionGroupSeeder::class);

        $systemGroup = PermissionGroup::where('name', 'Sistema')->first();
        $userGroup = PermissionGroup::where('name', 'Usuários')->first();
        $branchGroup = PermissionGroup::where('name', 'Filiais')->first();
        $roleGroup = PermissionGroup::where('name', 'Cargos')->first();

        $now = Carbon::now();

        $permissions = [
            // Sistema
            [
                'name' => 'Ver Permissões',
                'slug' => 'ver_permissoes',
                'description' => 'Permite visualizar permissões',
                'permission_group_id' => $systemGroup->id,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Editar Permissões',
                'slug' => 'editar_permissoes',
                'description' => 'Permite editar permissões',
                'permission_group_id' => $systemGroup->id,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Excluir Permissões',
                'slug' => 'excluir_permissoes',
                'description' => 'Permite excluir permissões',
                'permission_group_id' => $systemGroup->id,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // Usuários
            [
                'name' => 'Ver Usuários',
                'slug' => 'ver_usuarios',
                'description' => 'Permite visualizar usuários',
                'permission_group_id' => $userGroup->id,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Criar Usuários',
                'slug' => 'criar_usuarios',
                'description' => 'Permite criar usuários',
                'permission_group_id' => $userGroup->id,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Editar Usuários',
                'slug' => 'editar_usuarios',
                'description' => 'Permite editar usuários',
                'permission_group_id' => $userGroup->id,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Excluir Usuários',
                'slug' => 'excluir_usuarios',
                'description' => 'Permite excluir usuários',
                'permission_group_id' => $userGroup->id,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // Filiais
            [
                'name' => 'Ver Filiais',
                'slug' => 'ver_filiais',
                'description' => 'Permite visualizar filiais',
                'permission_group_id' => $branchGroup->id,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Criar Filiais',
                'slug' => 'criar_filiais',
                'description' => 'Permite criar filiais',
                'permission_group_id' => $branchGroup->id,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Editar Filiais',
                'slug' => 'editar_filiais',
                'description' => 'Permite editar filiais',
                'permission_group_id' => $branchGroup->id,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Excluir Filiais',
                'slug' => 'excluir_filiais',
                'description' => 'Permite excluir filiais',
                'permission_group_id' => $branchGroup->id,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // Cargos
            [
                'name' => 'Ver Cargos',
                'slug' => 'ver_cargos',
                'description' => 'Permite visualizar cargos',
                'permission_group_id' => $roleGroup->id,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Criar Cargos',
                'slug' => 'criar_cargos',
                'description' => 'Permite criar cargos',
                'permission_group_id' => $roleGroup->id,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Editar Cargos',
                'slug' => 'editar_cargos',
                'description' => 'Permite editar cargos',
                'permission_group_id' => $roleGroup->id,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Excluir Cargos',
                'slug' => 'excluir_cargos',
                'description' => 'Permite excluir cargos',
                'permission_group_id' => $roleGroup->id,
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['slug' => $permissionData['slug']],
                $permissionData
            );
        }

        // Atribuir todas as permissões ao superuser
        $superuser = User::where('is_superuser', true)->first();
        if ($superuser) {
            $allPermissions = Permission::all();
            foreach ($allPermissions as $permission) {
                $superuser->permissions()->syncWithoutDetaching([
                    $permission->id => [
                        'created_at' => $now,
                        'updated_at' => $now
                    ]
                ]);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Modules\Configuracoes\Models\Permission;
use App\Modules\Configuracoes\Models\Role;
use App\Modules\Configuracoes\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissões de Usuários
        Permission::create(['name' => 'ver_usuarios']);
        Permission::create(['name' => 'criar_usuarios']);
        Permission::create(['name' => 'editar_usuarios']);
        Permission::create(['name' => 'excluir_usuarios']);

        // Permissões de Filiais
        Permission::create(['name' => 'ver_filiais']);
        Permission::create(['name' => 'criar_filiais']);
        Permission::create(['name' => 'editar_filiais']);
        Permission::create(['name' => 'excluir_filiais']);

        // Permissões de Cargos
        Permission::create(['name' => 'ver_cargos']);
        Permission::create(['name' => 'criar_cargos']);
        Permission::create(['name' => 'editar_cargos']);
        Permission::create(['name' => 'excluir_cargos']);

        // Permissões de Departamentos
        Permission::create(['name' => 'ver_departamentos']);
        Permission::create(['name' => 'criar_departamentos']);
        Permission::create(['name' => 'editar_departamentos']);
        Permission::create(['name' => 'excluir_departamentos']);

        // Criar o papel Super Admin
        $role = Role::create(['name' => 'Super Admin']);
        $role->givePermissionTo(Permission::all());

        // Atribuir papel Super Admin aos usuários superuser existentes
        User::where('is_superuser', true)->get()->each->assignRole('Super Admin');
    }
}

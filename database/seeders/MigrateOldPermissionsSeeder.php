<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class MigrateOldPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'ver_filiais',
            'ver_pessoas',
            'editar_pessoas',
            'excluir_pessoas',
            'ver_veiculos',
            'editar_veiculos',
            'excluir_veiculos',
            'ver_multas',
            'editar_multas',
            'ver_licenciamentoveiculos',
            'editar_licenciamentoveiculos',
            'excluir_licenciamentoveiculos',
            'ver_permissoes',
            'editar_permissoes'
            // Add all your existing permissions here
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}

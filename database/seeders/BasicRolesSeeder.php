<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Configuracoes\Models\Role;
use Carbon\Carbon;

class BasicRolesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $roles = [
            [
                'name' => 'Administrador',
                'description' => 'Administrador geral do sistema',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Gerente',
                'description' => 'Gerente de filial',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Supervisor',
                'description' => 'Supervisor de setor',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Operador',
                'description' => 'Operador do sistema',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}

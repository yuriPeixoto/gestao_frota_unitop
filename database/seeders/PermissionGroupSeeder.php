<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Configuracoes\Models\PermissionGroup;
use Carbon\Carbon;

class PermissionGroupSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $groups = [
            [
                'name' => 'Sistema',
                'description' => 'Permissões do sistema',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Usuários',
                'description' => 'Permissões relacionadas a usuários',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Filiais',
                'description' => 'Permissões relacionadas a filiais',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Cargos',
                'description' => 'Permissões relacionadas a cargos',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ];

        foreach ($groups as $group) {
            PermissionGroup::firstOrCreate(
                ['name' => $group['name']],
                $group
            );
        }
    }
}

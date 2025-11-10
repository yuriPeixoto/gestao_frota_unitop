<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SpatieRolesSeeder extends Seeder
{
    public function run()
    {
        Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::create(['name' => 'Operador', 'guard_name' => 'web']);
    }
}

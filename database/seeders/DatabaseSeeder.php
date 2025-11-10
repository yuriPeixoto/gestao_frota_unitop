<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // PermissionGroupSeeder::class,
            // BasicPermissionsSeeder::class,
            // BasicRolesSeeder::class,
            // ChecklistSeed::class,
            // DepartamentoSeed::class,
            UserSeed::class,
            VeiculoCamposPermissionsSeeder::class
            // AjusteEstoqueSeeder::class,
            // grupoServicoSeed::class
            // DevolucaoTransferenciaEstoqueRequisicaoSeed::class
            // v_FilialSeeder::class,
            // ProdutoSeed::class
        ]);
    }
}

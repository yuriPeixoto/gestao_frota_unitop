<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class VeiculoCamposPermissionsSeeder extends Seeder
{
    /**
     * Seed das permissões de campos específicos de veículos (Comodato)
     * 
     * Este seeder adiciona permissões granulares para visualização e edição
     * dos campos de comodato nos formulários de veículos.
     */
    public function run(): void
    {
        // Limpar cache de permissões
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Grupo: Fornecedor Comodato
        $permissions[] = [
            'name' => 'ver_fornecedor_comodato',
            'guard_name' => 'web',
            'description' => 'Permite visualizar o fornecedor de comodato dos veículos',
            'group' => 'Veículos - Comodato'
        ];

        $permissions[] = [
            'name' => 'editar_fornecedor_comodato',
            'guard_name' => 'web',
            'description' => 'Permite editar o fornecedor de comodato dos veículos',
            'group' => 'Veículos - Comodato'
        ];

        // Grupo: Data Fim Comodato
        $permissions[] = [
            'name' => 'ver_data_comodato',
            'guard_name' => 'web',
            'description' => 'Permite visualizar a data de fim do comodato dos veículos',
            'group' => 'Veículos - Comodato'
        ];

        $permissions[] = [
            'name' => 'editar_data_comodato',
            'guard_name' => 'web',
            'description' => 'Permite editar a data de fim do comodato dos veículos',
            'group' => 'Veículos - Comodato'
        ];

        // Criar todas as permissões
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => $permission['guard_name']],
                [
                    'description' => $permission['description'],
                    'group' => $permission['group']
                ]
            );
        }

        $this->command->info('✅ Permissões de campos de comodato criadas com sucesso!');
        $this->command->info('📋 Total de permissões criadas: ' . count($permissions));

        // Listar as permissões criadas
        $this->command->newLine();
        $this->command->info('🔐 Permissões criadas:');
        foreach ($permissions as $permission) {
            $this->command->line("   • {$permission['name']} ({$permission['group']})");
        }
    }
}

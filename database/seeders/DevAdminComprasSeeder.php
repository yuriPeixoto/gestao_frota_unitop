<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DevAdminComprasSeeder extends Seeder
{
    /**
     * Seed para conceder a um usuário todas as permissões do módulo de compras para testes de desenvolvimento.
     */
    public function run(): void
    {
        // Certifique-se de que o seeder CompraPermissionsSeeder já foi executado
        if (Permission::where('name', 'criar_solicitacao_compra')->doesntExist()) {
            $this->command->info('Executando CompraPermissionsSeeder primeiro...');
            $this->call(CompraPermissionsSeeder::class);
        }

        // 1. Obter o usuário atual (supondo que é você)
        $user = User::where('email', 'mariootaviosilva@gmail.com')->first();

        // Se o usuário não for encontrado, pode criar um usuário de desenvolvimento
        if (!$user) {
            $this->command->info('Usuário não encontrado. Criando usuário administrador para desenvolvimento...');
            $user = User::create([
                'name' => 'Admin Compras',
                'email' => 'admin.compras@exemplo.com',
                'password' => Hash::make('senha123'),
                // Adicione outros campos conforme necessário
            ]);
        }

        // 2. Verificar se o papel de Administrador do Módulo Compras existe
        $role = Role::where('name', 'Administrador do Módulo Compras')->first();

        if (!$role) {
            $this->command->error('O papel de Administrador do Módulo Compras não foi encontrado.');
            return;
        }

        // 3. Atribuir o papel ao usuário
        $user->assignRole($role);

        // 4. Para garantir, atribuir também todas as permissões de compras diretamente
        $permissoes = Permission::where(function ($query) {
            $query->where('name', 'like', '%compra%')
                ->orWhere('name', 'like', '%fornecedor%')
                ->orWhere('name', 'like', '%contrato%')
                ->orWhere('name', 'like', '%orcamento%')
                ->orWhere('name', 'like', '%nota_fiscal%');
        })->get();

        $user->syncPermissions($permissoes);

        $this->command->info('Usuário ' . $user->name . ' (' . $user->email . ') configurado como Administrador de Compras com todas as permissões!');
        $this->command->info('Total de permissões atribuídas: ' . $permissoes->count());
    }
}

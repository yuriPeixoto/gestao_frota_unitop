<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompraPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Início da transação para garantir integridade dos dados
        DB::beginTransaction();

        try {
            // Desabilitar restrições de chave estrangeira
            Schema::disableForeignKeyConstraints();

            // Criar permissões do módulo de compras
            $permissions = $this->createPermissions();

            // Criar roles
            $roles = $this->createRoles();

            // Atribuir permissões às roles
            $this->assignPermissionsToRoles($roles, $permissions);

            // Habilitar restrições de chave estrangeira
            Schema::enableForeignKeyConstraints();

            // Commit da transação
            DB::commit();

            $this->command->info('Permissões e papéis do módulo de compras criados com sucesso!');
        } catch (\Exception $e) {
            // Reverter em caso de erro
            DB::rollBack();
            Schema::enableForeignKeyConstraints();

            $this->command->error('Erro ao criar permissões e papéis: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Criar permissões para o módulo de compras
     */
    private function createPermissions(): array
    {
        $permissionsByGroup = [
            // Solicitações de Compra
            'solicitacao_compra' => [
                'criar_solicitacao_compra' => 'Permite criar novas solicitações de compra',
                'editar_solicitacao_compra' => 'Permite editar solicitações existentes',
                'visualizar_solicitacao_compra' => 'Permite visualizar solicitações',
                'excluir_solicitacao_compra' => 'Permite excluir solicitações',
                'aprovar_solicitacao_compra' => 'Permite aprovar solicitações',
                'rejeitar_solicitacao_compra' => 'Permite rejeitar solicitações',
            ],

            // Pedidos de Compra
            'pedido_compra' => [
                'criar_pedido_compra' => 'Permite criar novos pedidos de compra',
                'editar_pedido_compra' => 'Permite editar pedidos existentes',
                'visualizar_pedido_compra' => 'Permite visualizar pedidos',
                'excluir_pedido_compra' => 'Permite excluir pedidos',
                'aprovar_pedido_compra' => 'Permite aprovar pedidos',
                'aprovar_pedido_compra_nivel_1' => 'Permite aprovar pedidos até R$ 5.000,00',
                'aprovar_pedido_compra_nivel_2' => 'Permite aprovar pedidos até R$ 25.000,00',
                'aprovar_pedido_compra_nivel_3' => 'Permite aprovar pedidos até R$ 100.000,00',
                'aprovar_pedido_compra_nivel_4' => 'Permite aprovar pedidos acima de R$ 100.000,00',
                'rejeitar_pedido_compra' => 'Permite rejeitar pedidos',
                'enviar_pedido_compra' => 'Permite enviar pedidos para fornecedores',
                'cancelar_pedido_compra' => 'Permite cancelar pedidos enviados',
            ],

            // Orçamentos
            'orcamento' => [
                'criar_orcamento' => 'Permite criar novos orçamentos',
                'editar_orcamento' => 'Permite editar orçamentos existentes',
                'visualizar_orcamento' => 'Permite visualizar orçamentos',
                'excluir_orcamento' => 'Permite excluir orçamentos',
                'aprovar_orcamento' => 'Permite aprovar/selecionar orçamentos',
                'rejeitar_orcamento' => 'Permite rejeitar orçamentos',
            ],

            // Fornecedores
            'fornecedor' => [
                'criar_fornecedor' => 'Permite cadastrar novos fornecedores',
                'editar_fornecedor' => 'Permite editar fornecedores existentes',
                'visualizar_fornecedor' => 'Permite visualizar fornecedores',
                'excluir_fornecedor' => 'Permite excluir fornecedores',
            ],

            // Contratos
            'contrato' => [
                'criar_contrato' => 'Permite criar novos contratos',
                'editar_contrato' => 'Permite editar contratos existentes',
                'visualizar_contrato' => 'Permite visualizar contratos',
                'excluir_contrato' => 'Permite excluir contratos',
            ],

            // Notas Fiscais
            'nota_fiscal' => [
                'criar_nota_fiscal' => 'Permite registrar novas notas fiscais',
                'editar_nota_fiscal' => 'Permite editar notas fiscais existentes',
                'visualizar_nota_fiscal' => 'Permite visualizar notas fiscais',
                'excluir_nota_fiscal' => 'Permite excluir notas fiscais',
                'aprovar_nota_fiscal' => 'Permite aprovar notas fiscais',
            ],

            // Relatórios
            'relatorio' => [
                'visualizar_relatorios_compras' => 'Permite visualizar relatórios do módulo',
                'exportar_relatorios_compras' => 'Permite exportar relatórios',
            ]
        ];

        $permissions = [];

        foreach ($permissionsByGroup as $group => $groupPermissions) {
            foreach ($groupPermissions as $name => $description) {
                $permission = Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => 'web',
                ], [
                    'description' => $description,
                    'group' => $group,
                ]);

                $this->command->line("Permissão criada: {$name}");
                $permissions[$name] = $permission;
            }
        }

        return $permissions;
    }

    /**
     * Criar roles para o módulo de compras
     */
    private function createRoles(): array
    {
        $rolesToCreate = [
            'Solicitante' => 'Usuários que podem criar solicitações de compra',
            'Aprovador de Solicitação' => 'Aprovadores de solicitações de compra',
            'Comprador' => 'Responsáveis por processar solicitações e realizar cotações',
            'Aprovador de Compra' => 'Aprovadores de pedidos de compra',
            'Aprovador de Compra Nível 1' => 'Aprovadores de pedidos de compra até R$ 5.000,00',
            'Aprovador de Compra Nível 2' => 'Aprovadores de pedidos de compra até R$ 25.000,00',
            'Aprovador de Compra Nível 3' => 'Aprovadores de pedidos de compra até R$ 100.000,00',
            'Aprovador de Compra Nível 4' => 'Aprovadores de pedidos de compra acima de R$ 100.000,00',
            'Almoxarife' => 'Responsável pelo recebimento de materiais',
            'Gestor de Frota' => 'Perfil especial para aprovações relacionadas à frota',
            'Administrador do Módulo Compras' => 'Acesso total ao módulo de compras',
        ];

        $roles = [];

        foreach ($rolesToCreate as $name => $description) {
            $role = Role::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ], [
                'description' => $description,
            ]);

            $this->command->line("Role criada: {$name}");
            $roles[$name] = $role;
        }

        return $roles;
    }

    /**
     * Atribuir permissões às roles
     */
    private function assignPermissionsToRoles(array $roles, array $permissions): void
    {
        // Permissões para Solicitante
        $solicitantePermissions = [
            'criar_solicitacao_compra',
            'editar_solicitacao_compra',
            'visualizar_solicitacao_compra',
            'visualizar_fornecedor',
            'visualizar_contrato',
            'visualizar_pedido_compra',
        ];
        $this->assignPermissionsToRole($roles['Solicitante'], $solicitantePermissions, $permissions);

        // Permissões para Aprovador de Solicitação
        $aprovadorSolicitacaoPermissions = [
            'visualizar_solicitacao_compra',
            'aprovar_solicitacao_compra',
            'rejeitar_solicitacao_compra',
            'visualizar_fornecedor',
            'visualizar_contrato',
            'visualizar_pedido_compra',
            'visualizar_relatorios_compras',
        ];
        $this->assignPermissionsToRole($roles['Aprovador de Solicitação'], $aprovadorSolicitacaoPermissions, $permissions);

        // Permissões para Comprador
        $compradorPermissions = [
            'visualizar_solicitacao_compra',
            'editar_solicitacao_compra',
            'criar_pedido_compra',
            'editar_pedido_compra',
            'visualizar_pedido_compra',
            'enviar_pedido_compra',
            'cancelar_pedido_compra',
            'criar_orcamento',
            'editar_orcamento',
            'visualizar_orcamento',
            'excluir_orcamento',
            'criar_fornecedor',
            'editar_fornecedor',
            'visualizar_fornecedor',
            'visualizar_contrato',
            'criar_nota_fiscal',
            'editar_nota_fiscal',
            'visualizar_nota_fiscal',
            'visualizar_relatorios_compras',
        ];
        $this->assignPermissionsToRole($roles['Comprador'], $compradorPermissions, $permissions);

        // Permissões para Aprovador de Compra
        $aprovadorCompraPermissions = [
            'visualizar_pedido_compra',
            'aprovar_pedido_compra',
            'rejeitar_pedido_compra',
            'visualizar_orcamento',
            'visualizar_fornecedor',
            'visualizar_contrato',
            'visualizar_solicitacao_compra',
            'visualizar_relatorios_compras',
            'exportar_relatorios_compras',
        ];
        $this->assignPermissionsToRole($roles['Aprovador de Compra'], $aprovadorCompraPermissions, $permissions);

        // Permissões para Aprovador de Compra Nível 1
        $aprovadorNivel1Permissions = array_merge(
            $aprovadorCompraPermissions,
            ['aprovar_pedido_compra_nivel_1']
        );
        $this->assignPermissionsToRole($roles['Aprovador de Compra Nível 1'], $aprovadorNivel1Permissions, $permissions);

        // Permissões para Aprovador de Compra Nível 2
        $aprovadorNivel2Permissions = array_merge(
            $aprovadorCompraPermissions,
            ['aprovar_pedido_compra_nivel_1', 'aprovar_pedido_compra_nivel_2']
        );
        $this->assignPermissionsToRole($roles['Aprovador de Compra Nível 2'], $aprovadorNivel2Permissions, $permissions);

        // Permissões para Aprovador de Compra Nível 3
        $aprovadorNivel3Permissions = array_merge(
            $aprovadorCompraPermissions,
            ['aprovar_pedido_compra_nivel_1', 'aprovar_pedido_compra_nivel_2', 'aprovar_pedido_compra_nivel_3']
        );
        $this->assignPermissionsToRole($roles['Aprovador de Compra Nível 3'], $aprovadorNivel3Permissions, $permissions);

        // Permissões para Aprovador de Compra Nível 4
        $aprovadorNivel4Permissions = array_merge(
            $aprovadorCompraPermissions,
            [
                'aprovar_pedido_compra_nivel_1',
                'aprovar_pedido_compra_nivel_2',
                'aprovar_pedido_compra_nivel_3',
                'aprovar_pedido_compra_nivel_4'
            ]
        );
        $this->assignPermissionsToRole($roles['Aprovador de Compra Nível 4'], $aprovadorNivel4Permissions, $permissions);

        // Permissões para Almoxarife
        $almoxarifePermissions = [
            'visualizar_pedido_compra',
            'visualizar_solicitacao_compra',
            'visualizar_fornecedor',
            'criar_nota_fiscal',
            'editar_nota_fiscal',
            'visualizar_nota_fiscal',
            'aprovar_nota_fiscal',
            'visualizar_relatorios_compras',
        ];
        $this->assignPermissionsToRole($roles['Almoxarife'], $almoxarifePermissions, $permissions);

        // Permissões para Gestor de Frota
        $gestorFrotaPermissions = [
            'visualizar_solicitacao_compra',
            'aprovar_solicitacao_compra',
            'rejeitar_solicitacao_compra',
            'visualizar_pedido_compra',
            'aprovar_pedido_compra',
            'rejeitar_pedido_compra',
            'visualizar_fornecedor',
            'visualizar_contrato',
            'visualizar_relatorios_compras',
        ];
        $this->assignPermissionsToRole($roles['Gestor de Frota'], $gestorFrotaPermissions, $permissions);

        // Permissões para Administrador do Módulo Compras (todas as permissões)
        $adminPermissions = array_keys($permissions);
        $this->assignPermissionsToRole($roles['Administrador do Módulo Compras'], $adminPermissions, $permissions);
    }

    /**
     * Atribuir permissões específicas a uma role
     */
    private function assignPermissionsToRole($role, array $permissionNames, array $permissions): void
    {
        foreach ($permissionNames as $permissionName) {
            if (isset($permissions[$permissionName])) {
                $role->givePermissionTo($permissions[$permissionName]);
                $this->command->line("Permissão {$permissionName} atribuída à role {$role->name}");
            } else {
                $this->command->warn("Permissão {$permissionName} não encontrada");
            }
        }
    }
}

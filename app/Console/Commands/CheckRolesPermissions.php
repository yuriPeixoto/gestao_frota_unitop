<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CheckRolesPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compras:check-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica as roles e permissões existentes no sistema relacionadas ao módulo de compras';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando roles e permissões existentes...');

        // Verificar todas as roles
        $this->info('=== Roles Existentes ===');
        $roles = Role::all();

        if ($roles->isEmpty()) {
            $this->warn('Nenhuma role encontrada no sistema.');
        } else {
            $relevantRoles = ['aprovador', 'comprador', 'solicitante', 'almoxarife', 'gestor'];
            $foundRelevantRoles = [];

            $this->table(
                ['ID', 'Nome', 'Guard', 'Permissões'],
                $roles->map(function ($role) use ($relevantRoles, &$foundRelevantRoles) {
                    $name = $role->name;
                    $isRelevant = false;

                    // Verificar se a role é relevante para compras
                    foreach ($relevantRoles as $relevant) {
                        if (stripos($name, $relevant) !== false) {
                            $isRelevant = true;
                            $foundRelevantRoles[] = $name;
                            break;
                        }
                    }

                    $permissions = $role->permissions->pluck('name')->implode(', ');
                    $permissions = $permissions ?: 'Nenhuma permissão associada';

                    return [
                        'id' => $role->id,
                        'name' => $isRelevant ? "<fg=green>{$name}</>" : $name,
                        'guard' => $role->guard_name,
                        'permissions' => $permissions
                    ];
                })
            );

            // Listar roles relevantes encontradas
            $this->info('');
            $this->info('=== Roles Relevantes para Módulo de Compras ===');
            if (empty($foundRelevantRoles)) {
                $this->warn('Nenhuma role relacionada a Compras encontrada.');
            } else {
                $this->info('Foram encontradas as seguintes roles que podem ser relevantes:');
                foreach ($foundRelevantRoles as $role) {
                    $this->line("- $role");
                }
            }
        }

        // Verificar permissões existentes relacionadas a compras
        $this->info('');
        $this->info('=== Permissões Existentes Relacionadas a Compras ===');

        $permissionKeywords = [
            'compra',
            'pedido',
            'solicitacao',
            'requisicao',
            'fornecedor',
            'estoque',
            'almoxarifado',
            'contrato',
            'orcamento',
            'aprovacao'
        ];

        $permissions = Permission::all();
        $relevantPermissions = [];

        foreach ($permissions as $permission) {
            foreach ($permissionKeywords as $keyword) {
                if (stripos($permission->name, $keyword) !== false) {
                    $relevantPermissions[] = $permission;
                    break;
                }
            }
        }

        if (empty($relevantPermissions)) {
            $this->warn('Nenhuma permissão relacionada a compras encontrada.');
        } else {
            $this->table(
                ['ID', 'Nome', 'Guard', 'Roles Associadas'],
                collect($relevantPermissions)->map(function ($permission) {
                    $roles = $permission->roles->pluck('name')->implode(', ');
                    $roles = $roles ?: 'Nenhuma role associada';

                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'guard' => $permission->guard_name,
                        'roles' => $roles
                    ];
                })
            );
        }

        // Sugerir permissões necessárias para o módulo de compras
        $this->info('');
        $this->info('=== Permissões Sugeridas para o Módulo de Compras ===');

        $suggestedPermissions = [
            // Solicitações de Compra
            'criar_solicitacao_compra',
            'editar_solicitacao_compra',
            'visualizar_solicitacao_compra',
            'excluir_solicitacao_compra',
            'aprovar_solicitacao_compra',
            'rejeitar_solicitacao_compra',

            // Pedidos de Compra
            'criar_pedido_compra',
            'editar_pedido_compra',
            'visualizar_pedido_compra',
            'excluir_pedido_compra',
            'aprovar_pedido_compra',
            'rejeitar_pedido_compra',
            'enviar_pedido_compra',
            'cancelar_pedido_compra',

            // Orçamentos
            'criar_orcamento',
            'editar_orcamento',
            'visualizar_orcamento',
            'excluir_orcamento',
            'aprovar_orcamento',
            'rejeitar_orcamento',

            // Fornecedores
            'criar_fornecedor',
            'editar_fornecedor',
            'visualizar_fornecedor',
            'excluir_fornecedor',

            // Contratos
            'criar_contrato',
            'editar_contrato',
            'visualizar_contrato',
            'excluir_contrato',

            // Notas Fiscais
            'criar_nota_fiscal',
            'editar_nota_fiscal',
            'visualizar_nota_fiscal',
            'excluir_nota_fiscal',
            'aprovar_nota_fiscal',

            // Relatórios
            'visualizar_relatorios_compras',
            'exportar_relatorios_compras'
        ];

        // Identificar quais permissões sugeridas já existem
        $existingNames = $permissions->pluck('name')->toArray();
        $missing = [];
        $existing = [];

        foreach ($suggestedPermissions as $suggestion) {
            if (in_array($suggestion, $existingNames)) {
                $existing[] = $suggestion;
            } else {
                $missing[] = $suggestion;
            }
        }

        if (!empty($existing)) {
            $this->info('Permissões sugeridas que JÁ EXISTEM no sistema:');
            foreach ($existing as $perm) {
                $this->line("- <fg=green>{$perm}</>");
            }
        }

        $this->info('');
        if (!empty($missing)) {
            $this->info('Permissões sugeridas que PRECISAM SER CRIADAS:');
            foreach ($missing as $perm) {
                $this->line("- <fg=yellow>{$perm}</>");
            }
        }

        // Sugerir roles necessárias
        $this->info('');
        $this->info('=== Roles Sugeridas para o Módulo de Compras ===');

        $suggestedRoles = [
            'Solicitante' => 'Usuários que podem criar solicitações de compra',
            'Aprovador de Solicitação' => 'Aprovadores de solicitações de compra (geralmente gestores de departamento)',
            'Comprador' => 'Responsáveis por processar solicitações e realizar cotações',
            'Aprovador de Compra' => 'Aprovadores de pedidos de compra (baseado em alçadas)',
            'Almoxarife' => 'Responsável pelo recebimento de materiais',
            'Gestor de Frota' => 'Perfil especial para aprovações relacionadas à frota',
            'Administrador do Módulo' => 'Acesso total ao módulo de compras'
        ];

        // Identificar quais roles sugeridas já existem (de forma aproximada)
        $existingRoleNames = $roles->pluck('name')->toArray();

        foreach ($suggestedRoles as $roleName => $description) {
            $exists = false;
            foreach ($existingRoleNames as $existing) {
                // Verificar se existe uma role similar
                $similarityKeywords = explode(' ', strtolower($roleName));
                foreach ($similarityKeywords as $keyword) {
                    if (strlen($keyword) > 3 && stripos($existing, $keyword) !== false) {
                        $exists = true;
                        $this->line("- <fg=green>{$roleName}</> ({$description}) - Similar à existente: {$existing}");
                        break 2;
                    }
                }
            }

            if (!$exists) {
                $this->line("- <fg=yellow>{$roleName}</> ({$description}) - Precisa ser criada");
            }
        }

        $this->info('');
        $this->info('A verificação foi concluída. Use estas informações para planejar as permissões e roles do módulo de compras.');
    }
}

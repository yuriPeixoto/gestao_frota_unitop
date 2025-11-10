<?php

namespace Database\Seeders;

use App\Services\ModulePermissionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ModulePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * ATENÇÃO: Este seeder vai:
     * 1. Fazer backup das permissões atuais dos usuários
     * 2. Limpar todas as permissões antigas
     * 3. Criar as novas permissões baseadas em módulos
     * 4. Tentar mapear permissões antigas para as novas (quando possível)
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando migração para o novo sistema de permissões por módulo...');

        DB::beginTransaction();

        try {
            // 1. Fazer backup das permissões atuais
            $this->command->info('📦 Fazendo backup das permissões atuais...');
            $backup = $this->backupCurrentPermissions();
            $this->command->info("   ✓ Backup criado: {$backup['users_count']} usuários, {$backup['roles_count']} cargos");

            // 2. Limpar permissões antigas
            $this->command->info('🧹 Limpando permissões antigas...');
            $this->cleanOldPermissions();
            $this->command->info('   ✓ Permissões antigas removidas');

            // 3. Criar novas permissões
            $this->command->info('✨ Criando novas permissões baseadas em módulos...');
            $result = ModulePermissionService::syncPermissions();
            $this->command->info("   ✓ {$result['total']} permissões criadas");

            // 4. Migrar permissões dos usuários
            $this->command->info('🔄 Migrando permissões dos usuários...');
            $migrated = $this->migrateUserPermissions($backup);
            $this->command->info("   ✓ {$migrated['success']} usuários migrados com sucesso");

            // 5. Criar cargo Admin com todas as permissões
            $this->command->info('👑 Criando cargo Administrador...');
            $this->createAdminRole();
            $this->command->info('   ✓ Cargo Administrador criado com todas as permissões');

            DB::commit();

            $this->command->newLine();
            $this->command->info('✅ Migração concluída com sucesso!');
            $this->command->newLine();
            $this->showSummary($result, $backup, $migrated);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Erro durante a migração: ' . $e->getMessage());
            Log::error('Erro no ModulePermissionsSeeder', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Fazer backup das permissões atuais
     */
    private function backupCurrentPermissions(): array
    {
        $backup = [
            'users' => [],
            'roles' => [],
            'users_count' => 0,
            'roles_count' => 0,
        ];

        // Backup permissões de usuários
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $permissions = DB::table('model_has_permissions')
                ->where('model_type', 'App\\Models\\User')
                ->where('model_id', $user->id)
                ->pluck('permission_id')
                ->toArray();

            $permissionNames = DB::table('permissions')
                ->whereIn('id', $permissions)
                ->pluck('name')
                ->toArray();

            if (!empty($permissionNames)) {
                $backup['users'][$user->id] = $permissionNames;
                $backup['users_count']++;
            }
        }

        // Backup permissões de cargos
        $roles = Role::with('permissions')->get();
        foreach ($roles as $role) {
            $backup['roles'][$role->id] = [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ];
            $backup['roles_count']++;
        }

        // Salvar backup em arquivo JSON
        $backupPath = database_path('backups');
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $backupFile = $backupPath . '/permissions_backup_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($backupFile, json_encode($backup, JSON_PRETTY_PRINT));

        $this->command->comment("   📄 Backup salvo em: {$backupFile}");

        return $backup;
    }

    /**
     * Limpar permissões antigas
     */
    private function cleanOldPermissions(): void
    {
        // Limpar relacionamentos
        DB::table('model_has_permissions')->truncate();
        DB::table('role_has_permissions')->truncate();

        // Limpar permissões
        DB::table('permissions')->truncate();

        // Limpar cache do Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Migrar permissões dos usuários para o novo sistema
     */
    private function migrateUserPermissions(array $backup): array
    {
        $result = [
            'success' => 0,
            'partial' => 0,
            'failed' => 0,
            'details' => [],
        ];

        // Mapeamento de permissões antigas para novas
        $mapping = $this->getPermissionMapping();

        foreach ($backup['users'] as $userId => $oldPermissions) {
            $newPermissions = [];
            $unmapped = [];

            foreach ($oldPermissions as $oldPerm) {
                if (isset($mapping[$oldPerm])) {
                    // Permissão tem mapeamento direto
                    $mapped = $mapping[$oldPerm];
                    if (is_array($mapped)) {
                        $newPermissions = array_merge($newPermissions, $mapped);
                    } else {
                        $newPermissions[] = $mapped;
                    }
                } else {
                    // Tentar mapear automaticamente baseado no nome
                    $auto = $this->autoMapPermission($oldPerm);
                    if ($auto) {
                        $newPermissions = array_merge($newPermissions, $auto);
                    } else {
                        $unmapped[] = $oldPerm;
                    }
                }
            }

            // Remover duplicatas
            $newPermissions = array_unique($newPermissions);

            // Aplicar permissões
            $user = \App\Models\User::find($userId);
            if ($user) {
                try {
                    // Dar permissão de acesso aos módulos baseado nas permissões antigas
                    $modules = $this->detectModulesFromPermissions($oldPermissions);
                    foreach ($modules as $module) {
                        $moduleAccessPerm = "{$module}.acessar_modulo";
                        if (Permission::where('name', $moduleAccessPerm)->exists()) {
                            $newPermissions[] = $moduleAccessPerm;
                        }
                    }

                    $user->syncPermissions(array_unique($newPermissions));
                    $result['success']++;

                    if (!empty($unmapped)) {
                        $result['partial']++;
                        $result['details'][$userId] = [
                            'user' => $user->name,
                            'unmapped' => $unmapped,
                        ];
                    }
                } catch (\Exception $e) {
                    $result['failed']++;
                    Log::warning("Erro ao migrar permissões do usuário {$userId}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $result;
    }

    /**
     * Mapeamento manual de permissões antigas para novas
     */
    private function getPermissionMapping(): array
    {
        return [
            // Abastecimentos
            'ver_abastecimentomanual' => ['abastecimentos.acessar_modulo', 'abastecimentos.abastecimento_manual.visualizar'],
            'criar_abastecimentomanual' => 'abastecimentos.abastecimento_manual.criar',
            'editar_abastecimentomanual' => 'abastecimentos.abastecimento_manual.editar',
            'excluir_abastecimentomanual' => 'abastecimentos.abastecimento_manual.excluir',

            'ver_abastecimentoatstruckpagmanual' => ['abastecimentos.acessar_modulo', 'abastecimentos.listar.visualizar'],
            'ver_encerrante' => ['abastecimentos.acessar_modulo', 'abastecimentos.encerrantes.visualizar'],
            'criar_encerrante' => 'abastecimentos.encerrantes.criar',
            'editar_encerrante' => 'abastecimentos.encerrantes.editar',
            'excluir_encerrante' => 'abastecimentos.encerrantes.excluir',

            'ver_estoquecombustivel' => ['abastecimentos.acessar_modulo', 'abastecimentos.estoque_combustivel.visualizar'],
            'ver_ajustekmabastecimento' => ['abastecimentos.acessar_modulo', 'abastecimentos.ajuste_km.visualizar'],

            // Compras
            'ver_solicitacaocompras' => ['compras.acessar_modulo', 'compras.dashboard.visualizar'],

            // Veículos
            'ver_veiculo' => ['veiculos.acessar_modulo', 'veiculos.cadastro.visualizar'],
            'criar_veiculo' => 'veiculos.cadastro.criar',
            'editar_veiculo' => 'veiculos.cadastro.editar',
            'excluir_veiculo' => 'veiculos.cadastro.excluir',

            'ver_licenciamentoveiculo' => ['veiculos.acessar_modulo', 'veiculos.licencas.visualizar'],
            'ver_ipvaveiculo' => ['veiculos.acessar_modulo', 'veiculos.licencas.visualizar'],
            'ver_seguroobrigatorio' => ['veiculos.acessar_modulo', 'veiculos.licencas.visualizar'],
            'ver_multa' => ['veiculos.acessar_modulo', 'veiculos.multas.visualizar'],
            'ver_certificadoveiculos' => ['veiculos.acessar_modulo', 'veiculos.certificados.visualizar'],

            // Pneus
            'ver_pneu' => ['pneus.acessar_modulo', 'pneus.cadastro.visualizar'],
            'criar_pneu' => 'pneus.cadastro.criar',
            'editar_pneu' => 'pneus.cadastro.editar',

            'ver_descartepneus' => ['pneus.acessar_modulo', 'pneus.baixa.visualizar'],
            'criar_descartepneus' => 'pneus.baixa.criar',

            'ver_transferenciapneus' => ['pneus.acessar_modulo', 'pneus.transferencia.visualizar'],
            'ver_manutencaopneus' => ['pneus.acessar_modulo', 'pneus.movimentacao.visualizar'],
            'ver_contagempneu' => ['pneus.acessar_modulo', 'pneus.movimentacao.visualizar'],
            'ver_requisicaopneu' => ['pneus.acessar_modulo', 'pneus.venda.visualizar'],

            // Manutenção
            'ver_ordemservico' => ['manutencao.acessar_modulo', 'manutencao.ordem_servico.visualizar'],
            'criar_ordemservico' => 'manutencao.ordem_servico.criar',
            'editar_ordemservico' => 'manutencao.ordem_servico.editar',

            // Pessoal
            'ver_funcionario' => ['pessoal.acessar_modulo', 'pessoal.funcionarios.visualizar'],
            'ver_motorista' => ['pessoal.acessar_modulo', 'pessoal.motoristas.visualizar'],
            'criar_motorista' => 'pessoal.motoristas.criar',
            'editar_motorista' => 'pessoal.motoristas.editar',

            // Estoque
            'ver_produto' => ['estoque.acessar_modulo', 'estoque.produtos.visualizar'],
            'criar_produto' => 'estoque.produtos.criar',
            'editar_produto' => 'estoque.produtos.editar',
            'ver_movimentacao' => ['estoque.acessar_modulo', 'estoque.movimentacao.visualizar'],

            // Configurações
            'ver_user' => ['configuracoes.acessar_modulo', 'configuracoes.usuarios.visualizar'],
            'criar_user' => 'configuracoes.usuarios.criar',
            'editar_user' => 'configuracoes.usuarios.editar',
            'excluir_user' => 'configuracoes.usuarios.excluir',

            'ver_permission' => ['configuracoes.acessar_modulo', 'configuracoes.permissoes.visualizar'],
            'ver_role' => ['configuracoes.acessar_modulo', 'configuracoes.permissoes.visualizar'],

            'ver_fornecedor' => ['configuracoes.acessar_modulo', 'configuracoes.fornecedores.visualizar'],
            'criar_fornecedor' => 'configuracoes.fornecedores.criar',
            'editar_fornecedor' => 'configuracoes.fornecedores.editar',
            'excluir_fornecedor' => 'configuracoes.fornecedores.excluir',

            // Sinistros
            'ver_sinistro' => ['sinistro.acessar_modulo', 'sinistro.gerenciar.visualizar'],
            'criar_sinistro' => 'sinistro.gerenciar.criar',
            'editar_sinistro' => 'sinistro.gerenciar.editar',
        ];
    }

    /**
     * Tentar mapear automaticamente uma permissão antiga
     */
    private function autoMapPermission(string $oldPermission): ?array
    {
        // Extrair ação e recurso
        if (preg_match('/^(ver|criar|editar|excluir)_(.+)$/', $oldPermission, $matches)) {
            $acao = $matches[1];
            $recurso = $matches[2];

            // Mapear ação para o novo formato
            $acaoMap = [
                'ver' => 'visualizar',
                'criar' => 'criar',
                'editar' => 'editar',
                'excluir' => 'excluir',
            ];

            $novaAcao = $acaoMap[$acao] ?? $acao;

            // Detectar módulo baseado no recurso
            $module = $this->detectModuleFromResource($recurso);
            if ($module) {
                return [
                    "{$module}.acessar_modulo",
                ];
            }
        }

        return null;
    }

    /**
     * Detectar módulo baseado no nome do recurso
     */
    private function detectModuleFromResource(string $resource): ?string
    {
        $patterns = [
            '/abastecimento|bomba|tanque|encerrante|combustivel/' => 'abastecimentos',
            '/veiculo|placa|licenciamento|ipva|multa|certificado/' => 'veiculos',
            '/pneu|calibr|descarte/' => 'pneus',
            '/manutencao|ordemservico|preventiva|corretiva/' => 'manutencao',
            '/funcionario|motorista|habilitacao/' => 'pessoal',
            '/produto|estoque|movimentacao/' => 'estoque',
            '/compra|solicitacao|pedido|orcamento/' => 'compras',
            '/fornecedor|empresa|filial|user|permission|role/' => 'configuracoes',
            '/sinistro/' => 'sinistro',
            '/imobilizado/' => 'imobilizados',
            '/checklist/' => 'checklist',
        ];

        foreach ($patterns as $pattern => $module) {
            if (preg_match($pattern, $resource)) {
                return $module;
            }
        }

        return null;
    }

    /**
     * Detectar módulos a partir de lista de permissões
     */
    private function detectModulesFromPermissions(array $permissions): array
    {
        $modules = [];

        foreach ($permissions as $perm) {
            if (preg_match('/^(ver|criar|editar|excluir)_(.+)$/', $perm, $matches)) {
                $recurso = $matches[2];
                $module = $this->detectModuleFromResource($recurso);
                if ($module && !in_array($module, $modules)) {
                    $modules[] = $module;
                }
            }
        }

        return $modules;
    }

    /**
     * Criar cargo Administrador com todas as permissões
     */
    private function createAdminRole(): void
    {
        // Verificar se já existe
        $admin = Role::where('name', 'Administrador')->first();
        if (!$admin) {
            $admin = Role::create([
                'name' => 'Administrador',
                'guard_name' => 'web',
            ]);
        }

        // Dar todas as permissões
        $allPermissions = Permission::all();
        $admin->syncPermissions($allPermissions);
    }

    /**
     * Mostrar resumo da migração
     */
    private function showSummary(array $result, array $backup, array $migrated): void
    {
        $this->command->table(
            ['Métrica', 'Valor'],
            [
                ['Permissões antigas (backup)', count($backup['users']) . ' usuários'],
                ['Novas permissões criadas', $result['total']],
                ['Usuários migrados com sucesso', $migrated['success']],
                ['Usuários com migração parcial', $migrated['partial']],
                ['Usuários com falha', $migrated['failed']],
            ]
        );

        if (!empty($migrated['details'])) {
            $this->command->warn('⚠️  Alguns usuários têm permissões que não puderam ser mapeadas:');
            foreach ($migrated['details'] as $userId => $detail) {
                $this->command->comment("   - {$detail['user']}: " . implode(', ', $detail['unmapped']));
            }
        }
    }
}
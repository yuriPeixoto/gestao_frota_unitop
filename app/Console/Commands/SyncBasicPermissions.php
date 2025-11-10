<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncBasicPermissions extends Command
{
    protected $signature = 'permissions:sync-basic';

    protected $description = 'Sincroniza permissões básicas para todos os controllers administrativos';

    public function handle()
    {
        // Aumentar timeout para 20 minutos (1200 segundos)
        set_time_limit(1200);
        $this->info('🔄 Sincronizando permissões básicas...');
        $this->info('🔗 Usando conexão: pgsql');
        $this->info('⏱️ Timeout aumentado para 20 minutos');

        $controllers = $this->getAdminControllers();
        $createdCount = 0;
        $existingCount = 0;

        // Usar conexão pgsql
        $connection = DB::connection('pgsql');

        foreach ($controllers as $controller) {
            $permissions = $this->generatePermissionsForController($controller);

            foreach ($permissions as $permission) {
                $existing = $connection->table('permissions')
                    ->where('name', $permission['name'])
                    ->first();

                if (! $existing) {
                    $connection->table('permissions')->insert([
                        'name' => $permission['name'],
                        'description' => $permission['description'],
                        'premission_group' => $permission['group'],
                        'guard_name' => 'web',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $createdCount++;
                    $this->line("✅ Criada: {$permission['name']}");
                } else {
                    $existingCount++;
                }
            }
        }

        $this->newLine();
        $this->info('📊 RESULTADO:');
        $this->info("✅ Permissões criadas: {$createdCount}");
        $this->info("ℹ️  Permissões existentes: {$existingCount}");
        $this->info('🎯 Total de controllers processados: '.count($controllers));
    }

    private function getAdminControllers(): array
    {
        $controllers = [];
        $controllerPath = app_path('Http/Controllers/Admin');

        if (! is_dir($controllerPath)) {
            return [];
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($controllerPath)
        );

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = str_replace($controllerPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
                $className = str_replace(['/', '\\', '.php'], ['\\', '\\', ''], $relativePath);
                $fullClassName = "App\\Http\\Controllers\\Admin\\{$className}";

                if (class_exists($fullClassName)) {
                    $controllers[] = $fullClassName;
                }
            }
        }

        return $controllers;
    }

    private function generatePermissionsForController(string $controllerClass): array
    {
        $permissions = [];
        $controllerName = class_basename($controllerClass);
        $module = $this->extractModuleName($controllerName);

        $actions = [
            'ver' => 'Visualizar',
            'criar' => 'Criar',
            'editar' => 'Editar',
            'excluir' => 'Excluir',
        ];

        foreach ($actions as $action => $actionName) {
            $permissionName = $action.'_'.strtolower($module);

            $permissions[] = [
                'name' => $permissionName,
                'description' => "Permite {$actionName} {$this->getFriendlyName($module)}",
                'group' => $this->getGroupName($module),
            ];
        }

        return $permissions;
    }

    private function extractModuleName(string $controllerName): string
    {
        $name = str_replace('Controller', '', $controllerName);

        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
    }

    private function getFriendlyName(string $module): string
    {
        return ucwords(str_replace('_', ' ', $module));
    }

    private function getGroupName(string $module): string
    {
        // Mapeamento de grupos baseado nos módulos do menu principal (app.blade.php)
        $groups = [
            // Módulos principais do menu
            'abastecimento' => 'Abastecimentos',
            'compra' => 'Compras',
            'solicitacao' => 'Compras', // Solicitações fazem parte de Compras
            'pedido' => 'Compras',
            'orcamento' => 'Compras',
            'configurac' => 'Configurações',
            'config' => 'Configurações',
            'checklist' => 'Checklist',
            'estoque' => 'Estoque',
            'produto' => 'Estoque',
            'material' => 'Estoque',
            'requisicao' => 'Estoque',
            'imobilizado' => 'Imobilizados',
            'manutencao' => 'Manutenção',
            'ordem' => 'Manutenção',
            'servico' => 'Manutenção',
            'pessoa' => 'Pessoas',
            'pessoal' => 'Pessoas',
            'funcionario' => 'Pessoas',
            'motorista' => 'Pessoas',
            'fornecedor' => 'Pessoas',
            'pneu' => 'Pneus',
            'sinistro' => 'Sinistros',
            'veiculo' => 'Veículos',
            'ticket' => 'Tickets',
            'chamado' => 'Tickets',
            'quality' => 'Tickets',
            'qualidade' => 'Tickets',

            // Outros
            'user' => 'Usuários',
            'usuario' => 'Usuários',
            'role' => 'Cargos',
            'cargo' => 'Cargos',
            'permission' => 'Sistema',
            'permissao' => 'Sistema',
            'relatorio' => 'Relatórios',
            'dashboard' => 'Sistema',
        ];

        // Verificar se o módulo contém alguma palavra-chave
        foreach ($groups as $key => $group) {
            if (str_contains($module, $key)) {
                return $group;
            }
        }

        return 'Sistema';
    }
}

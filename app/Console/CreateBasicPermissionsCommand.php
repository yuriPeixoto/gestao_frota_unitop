<?php

namespace App\Console;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CreateBasicPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-basic-permissions {--model= : Nome específico do modelo para criar permissões}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria permissões CRUD básicas para todos os modelos ou para um específico';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $specificModel = $this->option('model');

        if ($specificModel) {
            $this->createPermissionsForModel($specificModel);
            $this->info("Permissões criadas para o modelo {$specificModel}!");
            return self::SUCCESS;
        }

        // Lista todos os modelos e cria permissões
        $modelFiles = File::files(app_path('Models'));
        $totalCreated = 0;

        foreach ($modelFiles as $file) {
            $modelName = str_replace('.php', '', $file->getFilename());

            // Pula modelos do sistema, se necessário
            if (in_array($modelName, ['Permission', 'Role'])) {
                continue;
            }

            $permissionsCreated = $this->createPermissionsForModel($modelName);
            $totalCreated += $permissionsCreated;

            $this->line("Processado modelo: {$modelName} ({$permissionsCreated} permissões)");
        }

        $this->info("Total de {$totalCreated} permissões verificadas/criadas com sucesso!");

        // Registra no log para fins de auditoria
        Log::info("Comando app:create-basic-permissions executado. {$totalCreated} permissões verificadas/criadas.");

        return self::SUCCESS;
    }

    /**
     * Cria permissões para um modelo específico
     *
     * @param string $modelName
     * @return int Número de permissões criadas
     */
    private function createPermissionsForModel($modelName): int
    {
        $created = 0;
        $actions = ['ver', 'listar', 'criar', 'editar', 'excluir'];

        foreach ($actions as $action) {
            $permissionName = strtolower($action . '_' . $modelName);

            $permission = Permission::firstOrCreate(['name' => $permissionName]);

            if ($permission->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }
}

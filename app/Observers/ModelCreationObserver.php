<?php

namespace App\Observers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class ModelCreationObserver
{
    /**
     * Método para monitorar a criação de novos arquivos de modelo
     */
    public function handle($event)
    {
        try {
            // Verifica se o arquivo criado é um modelo PHP
            if ($this->isModelFile($event->path)) {
                $modelName = $this->extractModelName($event->path);
                $this->createPermissionsForModel($modelName);
                Log::info("Permissões criadas automaticamente para o modelo: {$modelName}");
            }
        } catch (\Exception $e) {
            Log::error("Erro ao criar permissões para novo modelo: " . $e->getMessage());
        }
    }

    /**
     * Verifica se o arquivo é um modelo
     */
    private function isModelFile($path)
    {
        // Verifica se o caminho contém a pasta Models e é um arquivo PHP
        return strpos($path, app_path('Models')) !== false &&
            pathinfo($path, PATHINFO_EXTENSION) === 'php';
    }

    /**
     * Extrai o nome do modelo do caminho do arquivo
     */
    private function extractModelName($path)
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);
        return $filename;
    }

    /**
     * Cria permissões CRUD para o modelo
     */
    private function createPermissionsForModel($modelName)
    {
        $actions = ['ver', 'listar', 'criar', 'editar', 'excluir'];

        foreach ($actions as $action) {
            Permission::firstOrCreate([
                'name' => strtolower($action . '_' . $modelName)
            ]);
        }
    }
}

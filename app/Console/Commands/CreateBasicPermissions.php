<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\File;

class CreateBasicPermissions extends Command
{
    protected $signature = 'permissions:create';
    protected $description = 'Create basic CRUD permissions for all models';

    public function handle()
    {
        $modelFiles = File::files(app_path('Models'));

        foreach ($modelFiles as $file) {
            $modelName = str_replace('.php', '', $file->getFilename());

            $actions = ['ver', 'criar', 'editar', 'excluir'];
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => strtolower($action . '_' . $modelName)
                ]);
            }
        }

        $this->info('Permissions created successfully!');
    }
}

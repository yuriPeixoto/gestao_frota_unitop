<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class ListPermissions extends Command
{
    protected $signature = 'permission:list';
    protected $description = 'List all permissions';

    public function handle()
    {
        $permissions = Permission::all()->pluck('name')->toArray();

        $this->info('Permissões disponíveis:');
        foreach ($permissions as $permission) {
            $this->line($permission);
        }

        $this->info("\nTotal: " . count($permissions) . " permissões");

        return 0;
    }
}

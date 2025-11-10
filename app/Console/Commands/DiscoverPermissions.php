<?php

namespace App\Console\Commands;

use App\Services\PermissionDiscoveryService;
use Illuminate\Console\Command;

class DiscoverPermissions extends Command
{
    protected $signature = 'permissions:discover {--sync : Sincroniza as permissões descobertas com o banco de dados}';
    protected $description = 'Descobre permissões baseadas nos controllers e models do sistema';

    public function handle(PermissionDiscoveryService $service)
    {
        $this->info('Descobrindo permissões...');

        if ($this->option('sync')) {
            $results = $service->syncPermissions();

            if (!empty($results['created'])) {
                $this->info('Novas permissões criadas:');
                foreach ($results['created'] as $slug) {
                    $this->line("- $slug");
                }
            }

            if (!empty($results['existing'])) {
                $this->info('Permissões existentes:');
                foreach ($results['existing'] as $slug) {
                    $this->line("- $slug");
                }
            }

            if (!empty($results['errors'])) {
                $this->error('Erros encontrados:');
                foreach ($results['errors'] as $error) {
                    $this->error("- {$error['slug']}: {$error['error']}");
                }
            }
        } else {
            $permissions = $service->discoverPermissions();

            $this->info('Permissões descobertas:');
            foreach ($permissions as $slug => $permission) {
                $this->line("- $slug: {$permission['name']}");
                $this->line("  Descrição: {$permission['description']}");
                $this->line('');
            }
        }

        $this->info('Concluído!');
    }
}

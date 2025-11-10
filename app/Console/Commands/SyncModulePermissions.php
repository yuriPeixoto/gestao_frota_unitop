<?php

namespace App\Console\Commands;

use App\Services\ModulePermissionService;
use Illuminate\Console\Command;

class SyncModulePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync-modules {--force : Forçar sincronização sem confirmação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar permissões baseadas em módulos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Sincronizando permissões baseadas em módulos...');
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Isso vai criar todas as permissões dos módulos. Continuar?', true)) {
                $this->info('Operação cancelada.');
                return 0;
            }
        }

        try {
            // Sincronizar permissões
            $result = ModulePermissionService::syncPermissions();

            $this->newLine();
            $this->info('✅ Sincronização concluída!');
            $this->newLine();

            // Mostrar resumo
            $this->table(
                ['Status', 'Quantidade'],
                [
                    ['Permissões criadas', count($result['created'])],
                    ['Permissões já existentes', count($result['existing'])],
                    ['Total de permissões', $result['total']],
                ]
            );

            if (!empty($result['created'])) {
                $this->newLine();
                $this->info('📝 Permissões criadas:');
                foreach ($result['created'] as $perm) {
                    $this->line('  - ' . $perm);
                }
            }

            // Limpar cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            $this->info('🧹 Cache de permissões limpo.');

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Erro ao sincronizar permissões: ' . $e->getMessage());
            return 1;
        }
    }
}
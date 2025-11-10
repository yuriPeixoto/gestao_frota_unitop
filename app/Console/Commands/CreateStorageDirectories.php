<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CreateStorageDirectories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:create-directories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria os diretórios necessários no storage público';

    /**
     * Lista dos diretórios que devem existir
     */
    protected $directories = [
        'solicitacoes',
        'solicitacoes/produtos',
        'solicitacoes/servicos',
        'files',
        'produtos',
        'contratos',
        'avatars',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Criando diretórios do storage...');

        foreach ($this->directories as $directory) {
            $fullPath = storage_path('app/public/' . $directory);

            if (!is_dir($fullPath)) {
                if (mkdir($fullPath, 0755, true)) {
                    $this->info("✅ Diretório criado: {$directory}");
                } else {
                    $this->error("❌ Falha ao criar diretório: {$directory}");
                }
            } else {
                $this->info("ℹ️  Diretório já existe: {$directory}");
            }
        }

        $this->info('');
        $this->info('Processo concluído!');

        // Verificar se o link simbólico existe
        $publicStoragePath = public_path('storage');
        if (!is_link($publicStoragePath) && !is_dir($publicStoragePath)) {
            $this->warn('⚠️  Link simbólico do storage não encontrado.');
            $this->info('Execute: php artisan storage:link');
        }

        return 0;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DebugbarControl extends Command
{
    protected $signature = 'debugbar:control {action : enable|disable|remove|status}';
    protected $description = 'Controla o Laravel Debugbar (ativar, desativar, remover ou verificar status)';

    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'enable':
                return $this->enableDebugbar();
            case 'disable':
                return $this->disableDebugbar();
            case 'remove':
                return $this->removeDebugbar();
            case 'status':
                return $this->checkStatus();
            default:
                $this->error('Ação inválida. Use: enable|disable|remove|status');
                return Command::FAILURE;
        }
    }

    protected function enableDebugbar()
    {
        $this->updateEnv('DEBUGBAR_ENABLED', 'true');
        $this->info('✅ Laravel Debugbar habilitado');
        $this->warn('⚠️  Isso pode impactar a performance. Use apenas em desenvolvimento.');
        return Command::SUCCESS;
    }

    protected function disableDebugbar()
    {
        $this->updateEnv('DEBUGBAR_ENABLED', 'false');
        $this->info('✅ Laravel Debugbar desabilitado');
        $this->info('💡 Para melhor performance, considere usar: php artisan debugbar:control remove');
        return Command::SUCCESS;
    }

    protected function removeDebugbar()
    {
        $this->info('🔍 Analisando impacto da remoção do Debugbar...');

        // Verificar se está sendo usado em código
        $usages = $this->findDebugbarUsages();
        
        if (!empty($usages)) {
            $this->warn('⚠️  Encontradas referências ao Debugbar no código:');
            foreach ($usages as $usage) {
                $this->line("   - $usage");
            }
            
            if (!$this->confirm('Deseja continuar mesmo assim?')) {
                $this->info('Operação cancelada.');
                return Command::FAILURE;
            }
        }

        if ($this->confirm('⚠️  ATENÇÃO: Isso removerá completamente o Laravel Debugbar do projeto. Continuar?')) {
            
            // Remover do composer.json
            $this->info('📝 Removendo do composer.json...');
            $this->line('Execute manualmente: composer remove barryvdh/laravel-debugbar');
            
            // Desabilitar no .env
            $this->updateEnv('DEBUGBAR_ENABLED', 'false');
            
            // Remover config se existir
            $configPath = config_path('debugbar.php');
            if (File::exists($configPath)) {
                File::delete($configPath);
                $this->info('🗑️  Arquivo config/debugbar.php removido');
            }

            $this->info('✅ Debugbar removido com sucesso!');
            $this->info('💡 Execute "composer remove barryvdh/laravel-debugbar" para remover completamente');
            $this->info('🔄 Use o Telescope para debugging: /telescope');
            
            return Command::SUCCESS;
        }

        $this->info('Operação cancelada.');
        return Command::FAILURE;
    }

    protected function checkStatus()
    {
        $this->info('📊 Status do Laravel Debugbar:');
        
        // Verificar no composer.json
        $composerJson = json_decode(File::get(base_path('composer.json')), true);
        $isInstalled = isset($composerJson['require-dev']['barryvdh/laravel-debugbar']);
        
        $this->line("   Instalado: " . ($isInstalled ? '✅ Sim' : '❌ Não'));
        
        if ($isInstalled) {
            $envValue = env('DEBUGBAR_ENABLED', 'true');
            $this->line("   Habilitado: " . ($envValue === 'true' ? '✅ Sim' : '❌ Não'));
            $this->line("   Valor .env: DEBUGBAR_ENABLED=$envValue");
            
            // Verificar config
            $configExists = File::exists(config_path('debugbar.php'));
            $this->line("   Config publicada: " . ($configExists ? '✅ Sim' : '❌ Não'));
        }

        // Status do Telescope
        $this->newLine();
        $this->info('📊 Status do Laravel Telescope:');
        $telescopeInstalled = isset($composerJson['require-dev']['laravel/telescope']);
        $this->line("   Instalado: " . ($telescopeInstalled ? '✅ Sim' : '❌ Não'));
        
        if ($telescopeInstalled) {
            $telescopeEnabled = env('TELESCOPE_ENABLED', 'true');
            $this->line("   Habilitado: " . ($telescopeEnabled === 'true' ? '✅ Sim' : '❌ Não'));
        }

        // Recomendação
        $this->newLine();
        $this->info('💡 Recomendação:');
        if ($isInstalled && $telescopeInstalled) {
            $this->line('   Use o Telescope em vez do Debugbar para melhor performance');
            $this->line('   Execute: php artisan debugbar:control disable');
        } elseif ($isInstalled && !$telescopeInstalled) {
            $this->line('   Considere instalar o Telescope: composer require laravel/telescope --dev');
        }

        return Command::SUCCESS;
    }

    protected function findDebugbarUsages()
    {
        $usages = [];
        $files = [
            'app/**/*.php',
            'resources/**/*.php',
            'config/**/*.php',
            'routes/**/*.php',
        ];

        foreach ($files as $pattern) {
            $matches = glob(base_path($pattern), GLOB_BRACE);
            foreach ($matches as $file) {
                if (is_file($file)) {
                    $content = File::get($file);
                    if (preg_match('/debugbar|Debugbar|DEBUGBAR/', $content)) {
                        $usages[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);
                    }
                }
            }
        }

        return array_unique($usages);
    }

    protected function updateEnv($key, $value)
    {
        $envPath = base_path('.env');
        $content = File::get($envPath);

        // Verificar se a chave já existe
        if (preg_match("/^{$key}=.*$/m", $content)) {
            // Atualizar valor existente
            $content = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $content);
        } else {
            // Adicionar nova chave
            $content .= "\n{$key}={$value}";
        }

        File::put($envPath, $content);
    }
}
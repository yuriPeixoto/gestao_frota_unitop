<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ConfigureEmailFallback extends Command
{
   protected $signature = 'email:configure-fallback {--enable} {--disable} {--gmail-user=} {--gmail-password=}';
   protected $description = 'Configura servidor SMTP de fallback para teste imediato';

   public function handle()
   {
      $envPath = base_path('.env');

      if ($this->option('disable')) {
         $this->updateEnvFile($envPath, 'COTACAO_FALLBACK_ENABLED', 'false');
         $this->info('✓ Fallback SMTP desabilitado');
         return;
      }

      if ($this->option('enable')) {
         $gmailUser = $this->option('gmail-user');
         $gmailPassword = $this->option('gmail-password');

         if (!$gmailUser || !$gmailPassword) {
            $this->error('❌ É necessário fornecer --gmail-user e --gmail-password');
            $this->info('Exemplo:');
            $this->info('php artisan email:configure-fallback --enable --gmail-user=seu-email@gmail.com --gmail-password=sua-senha-app');
            return;
         }

         // Atualizar variáveis do .env
         $this->updateEnvFile($envPath, 'COTACAO_FALLBACK_ENABLED', 'true');
         $this->updateEnvFile($envPath, 'COTACAO_FALLBACK_HOST', 'smtp.gmail.com');
         $this->updateEnvFile($envPath, 'COTACAO_FALLBACK_PORT', '587');
         $this->updateEnvFile($envPath, 'COTACAO_FALLBACK_USERNAME', $gmailUser);
         $this->updateEnvFile($envPath, 'COTACAO_FALLBACK_PASSWORD', $gmailPassword);
         $this->updateEnvFile($envPath, 'COTACAO_FALLBACK_ENCRYPTION', 'tls');

         $this->info('✅ Fallback SMTP configurado com Gmail!');
         $this->info("📧 Email: {$gmailUser}");
         $this->info('🔧 Host: smtp.gmail.com:587');
         $this->warn('⚠️  IMPORTANTE: Use uma senha de app do Gmail, não sua senha normal!');
         $this->warn('   👉 Configure em: https://myaccount.google.com/apppasswords');

         // Testar configuração
         if ($this->confirm('Deseja testar a conectividade agora?', true)) {
            $this->call('email:test-system');
         }

         return;
      }

      // Mostrar status atual
      $this->showCurrentStatus();
   }

   private function updateEnvFile($path, $key, $value)
   {
      if (!File::exists($path)) {
         $this->error("Arquivo .env não encontrado: {$path}");
         return;
      }

      $content = File::get($path);

      // Se a chave já existe, substitui
      if (preg_match("/^{$key}=.*$/m", $content)) {
         $content = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $content);
      } else {
         // Se não existe, adiciona no final
         $content .= "\n{$key}={$value}";
      }

      File::put($path, $content);
   }

   private function showCurrentStatus()
   {
      $this->info('=== Status do Sistema de Email ===');

      $config = config('cotacao-email');

      $this->info('📧 Servidor Principal:');
      $this->line("   Host: {$config['smtp']['host']}:{$config['smtp']['port']}");
      $this->line("   From: {$config['from']['email']}");

      $this->info('🔄 Servidor Fallback:');
      if ($config['smtp_fallback']['enabled']) {
         $this->line("   ✅ Habilitado");
         $this->line("   Host: {$config['smtp_fallback']['host']}:{$config['smtp_fallback']['port']}");
         $this->line("   User: {$config['smtp_fallback']['username']}");
      } else {
         $this->line("   ❌ Desabilitado");
      }

      $this->info('');
      $this->info('Comandos disponíveis:');
      $this->line('  php artisan email:configure-fallback --enable --gmail-user=email@gmail.com --gmail-password=senha-app');
      $this->line('  php artisan email:configure-fallback --disable');
      $this->line('  php artisan email:test-system');
   }
}

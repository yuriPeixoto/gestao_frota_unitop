<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailSenderService;
use App\Services\PHPMailerService;
use App\Services\HTMLBodyService;
use Illuminate\Support\Facades\Log;

class TestEmailFallback extends Command
{
   protected $signature = 'email:test-fallback {--email=teste@example.com}';
   protected $description = 'Testa o sistema de email com fallback automático';

   public function handle()
   {
      $this->info('=== Teste do Sistema de Email com Fallback ===');

      // Carregar configuração
      $config = config('cotacao-email');

      $this->info("📧 Servidor principal: {$config['smtp']['host']}:{$config['smtp']['port']}");
      $this->info("🔄 Fallback habilitado: " . ($config['smtp_fallback']['enabled'] ? 'Sim' : 'Não'));
      $this->info("📧 Servidor fallback: {$config['smtp_fallback']['host']}:{$config['smtp_fallback']['port']}");
      $this->info("📧 Email fallback: {$config['smtp_fallback']['username']}");
      $this->line('');

      // Criar instância do serviço
      $emailService = new EmailSenderService(new PHPMailerService(), new HTMLBodyService());

      // Dados de teste
      $emailDestino = $this->option('email');
      $numeroCotacao = 'TESTE-' . time();

      $this->info("🚀 Iniciando teste de envio...");
      $this->info("📬 Email destino: {$emailDestino}");
      $this->info("🔢 Número cotação: {$numeroCotacao}");
      $this->line('');

      try {
         $this->info("⏳ Tentando enviar email...");

         $resultado = $emailService->sendEmail(
            $config['smtp']['host'],
            $config['smtp']['port'],
            $config['smtp']['username'],
            $config['smtp']['password'],
            $config['from']['email'],
            $emailDestino,
            'Teste de Cotação - Sistema de Fallback',
            $config['empresa']['nome'],
            $config['empresa']['endereco'],
            $numeroCotacao,
            'Fornecedor de Teste'
         );

         if ($resultado) {
            $this->success('✅ EMAIL ENVIADO COM SUCESSO!');
            $this->warn('   👀 Verifique os logs para ver qual servidor foi usado');
         } else {
            $this->error('❌ FALHA NO ENVIO DO EMAIL');
            $this->warn('   👀 Verifique os logs para detalhes do erro');
         }
      } catch (\Exception $e) {
         $this->error('💥 ERRO: ' . $e->getMessage());
      }

      $this->line('');
      $this->info('📄 Logs detalhados em: storage/logs/laravel-' . date('Y-m-d') . '.log');

      // Mostrar informações finais
      $this->line('');
      $this->info('🔍 Para ver logs detalhados, execute:');
      $this->line('   tail -f storage/logs/laravel-' . date('Y-m-d') . '.log');
   }
}

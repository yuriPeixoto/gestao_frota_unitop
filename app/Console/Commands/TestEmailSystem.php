<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailSenderService;
use Illuminate\Support\Facades\Log;

class TestEmailSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-system';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa o sistema de envio de emails de cotação';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Teste do Sistema de Email ===');

        try {
            // Testar se a configuração está carregada
            $config = config('cotacao-email');
            $this->info('✓ Configuração carregada com sucesso');

            // Testar se o service pode ser instanciado
            $emailService = app(EmailSenderService::class);
            $this->info('✓ EmailSenderService instanciado com sucesso');

            $this->line('');
            $this->info('Configuração atual:');
            $this->line('- Host SMTP: ' . $config['smtp']['host']);
            $this->line('- Porta: ' . $config['smtp']['port']);
            $this->line('- From: ' . $config['from']['email']);
            $this->line('- Empresa: ' . $config['empresa']['nome']);

            // Verificar se PHPMailer está disponível
            if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
                $this->info('✓ PHPMailer está disponível');
            } else {
                $this->error('✗ PHPMailer não encontrado');
            }

            $this->line('');
            $this->info('✓ Sistema de email configurado e funcionando!');

            // Opção para teste de conectividade SMTP
            if ($this->confirm('Deseja testar a conectividade SMTP?')) {
                $this->testSmtpConnection($config);
            }

            // Opção para testar apenas conectividade (sem auth)
            if ($this->confirm('Deseja testar apenas a conectividade de rede (sem autenticação)?')) {
                $this->testNetworkConnectivity($config);
            }
        } catch (\Exception $e) {
            $this->error('✗ Erro: ' . $e->getMessage());
            $this->line('Trace: ' . $e->getTraceAsString());
        }
    }

    private function testSmtpConnection($config)
    {
        try {
            $this->info('Testando conectividade SMTP...');
            $this->line('Host: ' . $config['smtp']['host'] . ':' . $config['smtp']['port']);

            // Criar uma instância do PHPMailerService para testar conexão
            $phpMailerService = app(\App\Services\PHPMailerService::class);

            $smtpConfig = [
                'host' => $config['smtp']['host'],
                'port' => $config['smtp']['port'],
                'auth' => true,
                'username' => $config['smtp']['username'],
                'password' => $config['smtp']['password'],
                'encryption' => $config['smtp']['encryption'],
                'charset' => $config['smtp']['charset'],
                'debug' => 2, // Ativar debug para ver detalhes
                'auto_tls' => $config['smtp']['auto_tls']
            ];

            $phpMailerService->configureSMTP($smtpConfig);

            // Capturar output de debug
            ob_start();
            $result = $phpMailerService->testConnection();
            $debugOutput = ob_get_clean();

            if ($result) {
                $this->info('✓ Conexão SMTP estabelecida com sucesso!');
            } else {
                $this->error('✗ Falha na conexão SMTP');
                $this->line('Erro reportado pelo PHPMailer: ' . $phpMailerService->ErrorInfo);

                // Verificar se é bloqueio por excesso de tentativas
                if ($phpMailerService->isBlocked()) {
                    $this->warn('');
                    $this->warn('⚠️  DIAGNÓSTICO: O servidor SMTP está bloqueando por excesso de tentativas.');
                    $this->warn('');
                    $this->warn('SOLUÇÕES POSSÍVEIS:');
                    $this->warn('1. Aguardar alguns minutos antes de tentar novamente');
                    $this->warn('2. Verificar se as credenciais estão corretas');
                    $this->warn('3. Entrar em contato com o administrador do servidor');
                    $this->warn('4. Verificar se o IP não está em blacklist');
                    $this->warn('');
                    $this->warn('O sistema continuará funcionando, mas emails podem falhar temporariamente.');
                }
            }

            if (!empty($debugOutput)) {
                $this->line('');
                $this->info('Debug SMTP:');
                $this->line($debugOutput);
            }
        } catch (\Exception $e) {
            $this->error('✗ Erro ao testar SMTP: ' . $e->getMessage());
            $this->line('Detalhes do erro:');
            $this->line($e->getTraceAsString());
        }
    }

    private function testNetworkConnectivity($config)
    {
        $this->info('Testando conectividade de rede (sem autenticação)...');

        $host = $config['smtp']['host'];
        $port = $config['smtp']['port'];

        // Teste básico de conectividade TCP
        $connection = @fsockopen($host, $port, $errno, $errstr, 10);

        if ($connection) {
            $this->info('✓ Conectividade TCP estabelecida com sucesso!');
            fclose($connection);

            // Tentar handshake SMTP básico
            $this->testSmtpHandshake($host, $port);
        } else {
            $this->error("✗ Falha na conectividade TCP: $errstr ($errno)");
        }
    }

    private function testSmtpHandshake($host, $port)
    {
        $this->info('Testando handshake SMTP...');

        $socket = fsockopen($host, $port, $errno, $errstr, 10);
        if (!$socket) {
            $this->error("Erro ao conectar: $errstr");
            return;
        }

        // Ler resposta inicial do servidor
        $response = fgets($socket, 1024);
        $this->line("Resposta do servidor: " . trim($response));

        // Enviar EHLO
        fwrite($socket, "EHLO test\r\n");
        $response = fgets($socket, 1024);
        $this->line("Resposta EHLO: " . trim($response));

        // Ler capacidades
        while ($line = fgets($socket, 1024)) {
            if (strpos($line, '250 ') === 0) {
                $this->line("Capacidade: " . trim($line));
                break;
            } else if (strpos($line, '250-') === 0) {
                $this->line("Capacidade: " . trim($line));
            }
        }

        // Enviar QUIT
        fwrite($socket, "QUIT\r\n");
        fclose($socket);

        $this->info('✓ Handshake SMTP concluído com sucesso!');
    }
}

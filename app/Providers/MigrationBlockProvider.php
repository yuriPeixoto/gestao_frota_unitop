<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;

class MigrationBlockProvider extends ServiceProvider
{
    /**
     * Lista de comandos bloqueados relacionados a migrations e banco de dados.
     */
    private array $blockedCommands = [
        'migrate',
        'migrate:fresh',
        'migrate:refresh',
        'migrate:reset',
        'migrate:rollback',
        'migrate:status',
        'migrate:install',
        'db:seed',
        'db:wipe',
        'schema:dump',
        'migrate:make',
        'make:migration',
    ];

    /**
     * Emails para notificação de tentativas bloqueadas.
     */
    private array $notificationEmails = [
        'yuripeixoto@gmail.com',
        'marcos_jrc@hotmail.com',
    ];

    /**
     * Bootstrap dos serviços.
     */
    public function boot(): void
    {
        // Só aplicar bloqueio se estiver rodando no console (artisan)
        if ($this->app->runningInConsole()) {
            $this->blockMigrationCommands();
        }
    }

    /**
     * Bloqueia comandos de migration e registra tentativas.
     */
    private function blockMigrationCommands(): void
    {
        foreach ($this->blockedCommands as $command) {
            Artisan::command($command, function () use ($command) {
                // Função auxiliar para obter usuário
                $getCurrentUser = function () {
                    $user = $_SERVER['USER'] ?? $_SERVER['USERNAME'] ?? $_SERVER['LOGNAME'] ?? 'unknown';

                    if ($user === 'unknown' && PHP_OS_FAMILY === 'Windows') {
                        $whoami = shell_exec('whoami 2>nul');
                        if ($whoami) {
                            $user = trim($whoami);
                        }
                    }

                    return $user;
                };

                // Função auxiliar para obter IP
                $getClientIpAddress = function () {
                    $localIp = shell_exec('hostname -I 2>/dev/null') ??
                        shell_exec('ipconfig 2>nul | findstr IPv4') ??
                        '127.0.0.1';
                    return trim(explode(' ', trim($localIp))[0] ?? '127.0.0.1');
                };

                // Coleta dados para auditoria
                $auditData = [
                    'command' => $command,
                    'full_command' => implode(' ', $_SERVER['argv'] ?? [$command]),
                    'timestamp' => now()->format('Y-m-d H:i:s'),
                    'user' => $getCurrentUser(),
                    'ip_address' => $getClientIpAddress(),
                    'server_name' => gethostname(),
                    'working_directory' => getcwd(),
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'environment' => config('app.env'),
                    'database' => config('database.connections.pgsql.database'),
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Console',
                    'session_id' => session()->getId() ?? 'N/A',
                ];

                // Log da tentativa bloqueada
                Log::channel('single')->critical('🚫 TENTATIVA DE MIGRATION BLOQUEADA', [
                    'security_alert' => true,
                    'blocked_command' => $auditData['command'],
                    'full_command' => $auditData['full_command'],
                    'user' => $auditData['user'],
                    'ip_address' => $auditData['ip_address'],
                    'timestamp' => $auditData['timestamp'],
                    'server' => $auditData['server_name'],
                    'environment' => $auditData['environment'],
                    'database' => $auditData['database'],
                    'working_directory' => $auditData['working_directory'],
                    'audit_data' => $auditData,
                ]);

                // Tentar enviar email (só se não for mailer=log)
                try {
                    if (config('mail.default') !== 'log') {
                        $notificationEmails = ['yuripeixoto@gmail.com', 'marcos_jrc@hotmail.com'];
                        $subject = "🚨 ALERTA: Tentativa de Migration Bloqueada - {$auditData['command']}";

                        foreach ($notificationEmails as $email) {
                            Mail::raw("Tentativa de migration bloqueada!\n\nComando: {$auditData['full_command']}\nUsuário: {$auditData['user']}\nTimestamp: {$auditData['timestamp']}", function ($message) use ($email, $subject) {
                                $message->to($email)->subject($subject);
                            });
                        }

                        Log::info('📧 Notificação de migration bloqueada enviada', [
                            'emails_sent' => $notificationEmails,
                            'command' => $auditData['command']
                        ]);
                    } else {
                        Log::info('📧 Email de notificação seria enviado (mailer=log)', [
                            'emails' => ['yuripeixoto@gmail.com', 'marcos_jrc@hotmail.com'],
                            'command' => $auditData['command']
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('❌ Falha ao enviar email de notificação', [
                        'error' => $e->getMessage(),
                        'command' => $auditData['command']
                    ]);
                }

                // Exibir mensagem de erro no console
                echo "\n";
                echo "\033[41m\033[97m🚫 ================================\033[0m\n";
                echo "\033[41m\033[97m   COMANDO BLOQUEADO!\033[0m\n";
                echo "\033[41m\033[97m🚫 ================================\033[0m\n";
                echo "\n";
                echo "\033[33mComando tentado: {$auditData['full_command']}\033[0m\n";
                echo "\033[33mUsuário: {$auditData['user']}\033[0m\n";
                echo "\033[33mTimestamp: {$auditData['timestamp']}\033[0m\n";
                echo "\n";
                echo "\033[32m📋 PROCESSO CORRETO:\033[0m\n";
                echo "\033[32m1. Criar script SQL com as mudanças necessárias\033[0m\n";
                echo "\033[32m2. Solicitar code review do script\033[0m\n";
                echo "\033[32m3. DBA executa backup manual antes da mudança\033[0m\n";
                echo "\033[32m4. DBA executa script via DBeaver\033[0m\n";
                echo "\033[32m5. Testar aplicação após mudança\033[0m\n";
                echo "\n";
                echo "\033[41m\033[97m📧 Notificação enviada para administradores.\033[0m\n";
                echo "\033[41m\033[97m📝 Tentativa registrada nos logs do sistema.\033[0m\n";
                echo "\n";

                return 1; // Exit code 1 = erro
            })->describe('🚫 COMANDO BLOQUEADO - Mudanças apenas via DBeaver');
        }
    }

    /**
     * Processa o bloqueio do comando e executa auditoria.
     */
    private function blockCommand(string $command): void
    {
        $auditData = $this->collectAuditData($command);

        // Log da tentativa bloqueada
        $this->logBlockedAttempt($auditData);

        // Enviar notificação por email
        $this->sendEmailNotification($auditData);

        // Exibir mensagem de erro no console
        $this->displayBlockMessage($command, $auditData);
    }

    /**
     * Coleta dados para auditoria da tentativa bloqueada.
     */
    private function collectAuditData(string $command): array
    {
        return [
            'command' => $command,
            'full_command' => implode(' ', $_SERVER['argv'] ?? [$command]),
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'user' => $this->getCurrentUser(),
            'ip_address' => $this->getClientIpAddress(),
            'server_name' => gethostname(),
            'working_directory' => getcwd(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => config('app.env'),
            'database' => config('database.connections.pgsql.database'),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Console',
            'session_id' => session()->getId() ?? 'N/A',
        ];
    }

    /**
     * Obtém o usuário atual do sistema operacional.
     */
    private function getCurrentUser(): string
    {
        // Tenta várias formas de obter o usuário
        $user = $_SERVER['USER'] ?? $_SERVER['USERNAME'] ?? $_SERVER['LOGNAME'] ?? 'unknown';

        // Se estiver no Windows, tenta whoami
        if ($user === 'unknown' && PHP_OS_FAMILY === 'Windows') {
            $whoami = shell_exec('whoami 2>nul');
            if ($whoami) {
                $user = trim($whoami);
            }
        }

        return $user;
    }

    /**
     * Obtém o endereço IP do cliente.
     */
    private function getClientIpAddress(): string
    {
        // Para console, usar IP local/servidor
        if ($this->app->runningInConsole()) {
            // Tenta obter IP local do servidor
            $localIp = shell_exec('hostname -I 2>/dev/null') ??
                shell_exec('ipconfig 2>nul | findstr IPv4') ??
                '127.0.0.1';
            return trim(explode(' ', trim($localIp))[0] ?? '127.0.0.1');
        }

        // Para web requests
        return Request::ip() ?? '127.0.0.1';
    }

    /**
     * Registra a tentativa bloqueada nos logs.
     */
    private function logBlockedAttempt(array $auditData): void
    {
        Log::channel('single')->critical('🚫 TENTATIVA DE MIGRATION BLOQUEADA', [
            'security_alert' => true,
            'blocked_command' => $auditData['command'],
            'full_command' => $auditData['full_command'],
            'user' => $auditData['user'],
            'ip_address' => $auditData['ip_address'],
            'timestamp' => $auditData['timestamp'],
            'server' => $auditData['server_name'],
            'environment' => $auditData['environment'],
            'database' => $auditData['database'],
            'working_directory' => $auditData['working_directory'],
            'audit_data' => $auditData,
        ]);

        // Log adicional em arquivo específico se configurado
        if (config('logging.channels.migrations')) {
            Log::channel('migrations')->critical('Migration bloqueada', $auditData);
        }
    }

    /**
     * Envia notificação por email sobre tentativa bloqueada.
     */
    private function sendEmailNotification(array $auditData): void
    {
        try {
            // Só enviar email se o mailer não for 'log'
            if (config('mail.default') === 'log') {
                Log::info('📧 Email de notificação seria enviado (mailer=log)', [
                    'emails' => $this->notificationEmails,
                    'command' => $auditData['command']
                ]);
                return;
            }

            $subject = "🚨 ALERTA: Tentativa de Migration Bloqueada - {$auditData['command']}";

            $emailData = [
                'subject' => $subject,
                'audit_data' => $auditData,
                'app_name' => config('app.name'),
                'app_url' => config('app.url'),
            ];

            foreach ($this->notificationEmails as $email) {
                Mail::send([], [], function ($message) use ($email, $subject, $emailData, $auditData) {
                    $message->to($email)
                        ->subject($subject)
                        ->html($this->buildEmailContent($emailData));
                });
            }

            Log::info('📧 Notificação de migration bloqueada enviada', [
                'emails_sent' => $this->notificationEmails,
                'command' => $auditData['command']
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Falha ao enviar email de notificação', [
                'error' => $e->getMessage(),
                'command' => $auditData['command']
            ]);
        }
    }

    /**
     * Constrói o conteúdo HTML do email de notificação.
     */
    private function buildEmailContent(array $emailData): string
    {
        $auditData = $emailData['audit_data'];

        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .alert { background-color: #fee; border: 1px solid #fcc; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
                .info-table { border-collapse: collapse; width: 100%; margin-top: 15px; }
                .info-table th, .info-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .info-table th { background-color: #f2f2f2; }
                .command { background-color: #f8f8f8; padding: 10px; font-family: monospace; border-radius: 3px; }
            </style>
        </head>
        <body>
            <div class='alert'>
                <h2>🚨 ALERTA DE SEGURANÇA - Sistema Gestão de Frota</h2>
                <p><strong>Uma tentativa de execução de migration foi bloqueada!</strong></p>
            </div>
            
            <h3>📝 Detalhes da Tentativa:</h3>
            <div class='command'>
                <strong>Comando:</strong> {$auditData['full_command']}
            </div>
            
            <table class='info-table'>
                <tr><th>Timestamp</th><td>{$auditData['timestamp']}</td></tr>
                <tr><th>Usuário SO</th><td>{$auditData['user']}</td></tr>
                <tr><th>Servidor</th><td>{$auditData['server_name']}</td></tr>
                <tr><th>IP</th><td>{$auditData['ip_address']}</td></tr>
                <tr><th>Ambiente</th><td>{$auditData['environment']}</td></tr>
                <tr><th>Banco de Dados</th><td>{$auditData['database']}</td></tr>
                <tr><th>Diretório</th><td>{$auditData['working_directory']}</td></tr>
                <tr><th>PHP Version</th><td>{$auditData['php_version']}</td></tr>
                <tr><th>Laravel Version</th><td>{$auditData['laravel_version']}</td></tr>
            </table>
            
            <h3>⚠️ Ação Requerida:</h3>
            <ul>
                <li>Verificar se a tentativa foi legítima</li>
                <li>Se necessário, investigar o usuário que tentou executar o comando</li>
                <li>Lembrar a equipe que mudanças no banco devem ser via DBeaver</li>
                <li>Conferir os logs do sistema para mais detalhes</li>
            </ul>
            
            <hr>
            <p><small>Sistema: {$emailData['app_name']} | URL: {$emailData['app_url']}</small></p>
            <p><small>Esta é uma notificação automática do sistema de proteção contra migrations.</small></p>
        </body>
        </html>
        ";
    }

    /**
     * Exibe mensagem de bloqueio no console.
     */
    private function displayBlockMessage(string $command, array $auditData): void
    {
        // Usar output direto já que estamos dentro do closure do Artisan::command
        echo "\n";
        echo "\033[41m\033[97m🚫 ================================\033[0m\n";
        echo "\033[41m\033[97m   COMANDO BLOQUEADO!\033[0m\n";
        echo "\033[41m\033[97m🚫 ================================\033[0m\n";
        echo "\n";
        echo "\033[33mComando tentado: {$auditData['full_command']}\033[0m\n";
        echo "\033[33mUsuário: {$auditData['user']}\033[0m\n";
        echo "\033[33mTimestamp: {$auditData['timestamp']}\033[0m\n";
        echo "\n";
        echo "\033[32m📋 PROCESSO CORRETO:\033[0m\n";
        echo "\033[32m1. Criar script SQL com as mudanças necessárias\033[0m\n";
        echo "\033[32m2. Solicitar code review do script\033[0m\n";
        echo "\033[32m3. DBA executa backup manual antes da mudança\033[0m\n";
        echo "\033[32m4. DBA executa script via DBeaver\033[0m\n";
        echo "\033[32m5. Testar aplicação após mudança\033[0m\n";
        echo "\n";
        echo "\033[41m\033[97m📧 Notificação enviada para administradores.\033[0m\n";
        echo "\033[41m\033[97m📝 Tentativa registrada nos logs do sistema.\033[0m\n";
        echo "\n";
    }

    /**
     * Registra os serviços do provider.
     */
    public function register(): void
    {
        // Registrar configurações se necessário
    }
}

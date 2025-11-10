<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\IntegracaoWhatssappCarvalimaService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class LogMaintenanceReminderCommand extends Command
{
    protected $signature = 'logs:send-maintenance-reminder 
                          {--force : Força o envio mesmo que não seja dia de lembrete}
                          {--phone= : Número específico para envio (opcional)}';

    protected $description = 'Envia lembrete mensal via WhatsApp para manutenção de logs com relatório anexo';

    public function handle()
    {
        $this->info('📱 Iniciando processo de lembrete de manutenção de logs...');

        // Verificar se hoje é dia de enviar lembrete (primeira segunda-feira do mês)
        if (!$this->shouldSendReminder() && !$this->option('force')) {
            $this->info('⏭️  Hoje não é dia de enviar lembrete mensal.');
            $this->info('💡 Use --force para forçar o envio.');
            return;
        }

        // Gerar relatório de análise
        $reportFile = $this->generateLogAnalysisReport();

        if (!$reportFile) {
            $this->error('❌ Erro ao gerar relatório de logs.');
            return;
        }

        // Preparar mensagem
        $message = $this->prepareWhatsAppMessage();

        // Enviar mensagem
        $phone = $this->option('phone') ?? env('ADMIN_PHONE', '5565981521185');
        $adminName = env('ADMIN_NAME', 'Admin');

        $this->sendWhatsAppMessage($message, $adminName, $phone, $reportFile);

        $this->info('✅ Lembrete enviado com sucesso!');
        $this->info("📄 Relatório salvo em: {$reportFile}");
    }

    private function shouldSendReminder(): bool
    {
        $today = now();

        // Primeira segunda-feira do mês
        $firstMondayOfMonth = $today->copy()->startOfMonth();

        // Encontrar a primeira segunda-feira
        while ($firstMondayOfMonth->dayOfWeek !== 1) { // 1 = Monday
            $firstMondayOfMonth->addDay();
        }

        return $today->isSameDay($firstMondayOfMonth);
    }

    private function generateLogAnalysisReport(): ?string
    {
        $this->info('📊 Gerando relatório de análise de logs...');

        $reportContent = [];
        $reportContent[] = "===========================================";
        $reportContent[] = "📋 RELATÓRIO MENSAL DE MANUTENÇÃO DE LOGS";
        $reportContent[] = "===========================================";
        $reportContent[] = "Data: " . now()->format('d/m/Y H:i');
        $reportContent[] = "Ambiente: " . env('APP_ENV', 'unknown');
        $reportContent[] = "";

        // Análise de arquivos de log
        $logPath = storage_path('logs');
        $logFiles = File::glob($logPath . '/*.log');

        $reportContent[] = "📁 ARQUIVOS DE LOG ENCONTRADOS:";
        $reportContent[] = str_repeat('-', 50);

        $totalSize = 0;
        $totalLines = 0;

        foreach ($logFiles as $file) {
            $size = File::size($file);
            $lines = count(file($file));
            $modified = date('d/m/Y H:i', File::lastModified($file));

            $sizeFormatted = $this->formatBytes($size);
            $reportContent[] = sprintf(
                "• %s - %s (%s linhas) - %s",
                basename($file),
                $sizeFormatted,
                number_format($lines),
                $modified
            );

            $totalSize += $size;
            $totalLines += $lines;
        }

        $reportContent[] = "";
        $reportContent[] = sprintf(
            "📊 TOTAL: %s em %d arquivos (%s linhas)",
            $this->formatBytes($totalSize),
            count($logFiles),
            number_format($totalLines)
        );

        // Análise de tipos de log
        $this->analyzeLogTypes($logFiles, $reportContent);

        // Encontrar logs verbosos no código
        $this->scanCodeForVerboseLogs($reportContent);

        // Recomendações
        $this->addRecommendations($reportContent, $totalSize);

        // Salvar relatório
        $fileName = 'log-maintenance-report-' . now()->format('Y-m-d') . '.txt';
        $filePath = storage_path('logs/reports/' . $fileName);

        // Criar diretório se não existir
        if (!File::exists(dirname($filePath))) {
            File::makeDirectory(dirname($filePath), 0755, true);
        }

        File::put($filePath, implode("\n", $reportContent));

        return $filePath;
    }

    private function analyzeLogTypes($logFiles, &$reportContent): void
    {
        $reportContent[] = "";
        $reportContent[] = "🔍 ANÁLISE POR TIPO DE LOG:";
        $reportContent[] = str_repeat('-', 50);

        $logTypes = [];

        foreach ($logFiles as $file) {
            $content = File::get($file);

            // Conta diferentes tipos
            $logTypes['SQL Queries'] = ($logTypes['SQL Queries'] ?? 0) + substr_count($content, 'local.INFO: SQL:');
            $logTypes['Debug'] = ($logTypes['Debug'] ?? 0) + substr_count($content, 'local.DEBUG:');
            $logTypes['Info'] = ($logTypes['Info'] ?? 0) + substr_count($content, 'local.INFO:') - substr_count($content, 'local.INFO: SQL:');
            $logTypes['Activity Logs'] = ($logTypes['Activity Logs'] ?? 0) + substr_count($content, 'activity_logs');
            $logTypes['Cache Info'] = ($logTypes['Cache Info'] ?? 0) + substr_count($content, 'Cache de permissões');
            $logTypes['Session Logs'] = ($logTypes['Session Logs'] ?? 0) + substr_count($content, 'sessions" where "id"');
        }

        arsort($logTypes);

        foreach ($logTypes as $type => $count) {
            if ($count > 0) {
                $status = $this->getLogTypeStatus($type, $count);
                $reportContent[] = sprintf("• %s: %s %s", $type, number_format($count), $status);
            }
        }
    }

    private function getLogTypeStatus($type, $count): string
    {
        $thresholds = [
            'SQL Queries' => 100,
            'Debug' => 50,
            'Activity Logs' => 20,
            'Session Logs' => 200,
            'Cache Info' => 10
        ];

        $threshold = $thresholds[$type] ?? 50;

        if ($count > $threshold) {
            return "⚠️  (ALTO - Revisar)";
        } elseif ($count > $threshold / 2) {
            return "🟡 (Moderado)";
        } else {
            return "✅ (OK)";
        }
    }

    private function scanCodeForVerboseLogs(&$reportContent): void
    {
        $reportContent[] = "";
        $reportContent[] = "🔎 LOGS VERBOSOS NO CÓDIGO:";
        $reportContent[] = str_repeat('-', 50);

        $appPath = app_path();
        $files = File::allFiles($appPath);
        $verboseLogs = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = File::get($file->getPathname());
            $lines = explode("\n", $content);

            foreach ($lines as $lineNumber => $line) {
                if (preg_match('/Log::(info|debug)\s*\(\s*["\']/', $line, $matches)) {
                    if (
                        stripos($line, 'SQL:') !== false ||
                        stripos($line, 'Buscando') !== false ||
                        stripos($line, 'bypass') !== false ||
                        stripos($line, 'Cache de') !== false
                    ) {
                        $relativePath = str_replace(app_path(), 'app', $file->getPathname());
                        $verboseLogs[] = sprintf("• %s:%d - %s", $relativePath, $lineNumber + 1, trim($line));
                    }
                }
            }
        }

        if (empty($verboseLogs)) {
            $reportContent[] = "✅ Nenhum log verboso encontrado no código!";
        } else {
            $reportContent[] = sprintf("⚠️  %d logs verbosos encontrados:", count($verboseLogs));
            foreach (array_slice($verboseLogs, 0, 10) as $log) { // Limita a 10
                $reportContent[] = $log;
            }
            if (count($verboseLogs) > 10) {
                $reportContent[] = sprintf("... e mais %d logs.", count($verboseLogs) - 10);
            }
        }
    }

    private function addRecommendations(&$reportContent, $totalSize): void
    {
        $reportContent[] = "";
        $reportContent[] = "💡 RECOMENDAÇÕES DE MANUTENÇÃO:";
        $reportContent[] = str_repeat('-', 50);

        if ($totalSize > 10 * 1024 * 1024) { // 10MB
            $reportContent[] = "🔄 Executar limpeza de logs antigos";
            $reportContent[] = "   Comando: php artisan logs:cleanup --clean --days=30";
        }

        $reportContent[] = "🧹 Comandos sugeridos para manutenção:";
        $reportContent[] = "   • php artisan logs:cleanup --analyze --scan-code";
        $reportContent[] = "   • php artisan logs:cleanup --clean --days=7";
        $reportContent[] = "   • Revisar logs verbosos listados acima";

        $reportContent[] = "";
        $reportContent[] = "📅 Próximo lembrete: " . now()->addMonth()->startOfMonth()->addWeeks(0)->next('Monday')->format('d/m/Y');

        $reportContent[] = "";
        $reportContent[] = "===========================================";
        $reportContent[] = "Relatório gerado automaticamente pelo sistema";
        $reportContent[] = "===========================================";
    }

    private function prepareWhatsAppMessage(): string
    {
        $env = strtoupper(env('APP_ENV', 'UNKNOWN'));

        return "🧹 *LEMBRETE MENSAL - MANUTENÇÃO DE LOGS*\n\n" .
            "📅 Data: " . now()->format('d/m/Y') . "\n" .
            "🌐 Ambiente: {$env}\n\n" .
            "É hora de fazer a manutenção mensal dos logs do sistema!\n\n" .
            "📋 *Relatório completo foi gerado* com:\n" .
            "• Análise de arquivos de log\n" .
            "• Identificação de logs verbosos\n" .
            "• Recomendações de limpeza\n\n" .
            "🔧 *Comandos sugeridos:*\n" .
            "`php artisan logs:cleanup --analyze`\n" .
            "`php artisan logs:cleanup --clean --days=7`\n\n" .
            "📄 Confira o relatório detalhado no servidor!\n\n" .
            "_Mensagem automática do sistema de gestão de logs_";
    }

    private function sendWhatsAppMessage($message, $name, $phone, $reportFile): void
    {
        $this->info("📱 Enviando mensagem para {$name} ({$phone})...");

        try {
            $response = IntegracaoWhatssappCarvalimaService::enviarMensagem($message, $name, $phone);

            // Log do envio
            $this->info("📤 Resposta da API: " . $response);

            // Salvar log do envio
            $logEntry = [
                'timestamp' => now()->toISOString(),
                'phone' => $phone,
                'name' => $name,
                'report_file' => $reportFile,
                'response' => $response
            ];

            File::append(
                storage_path('logs/whatsapp-reminders.log'),
                json_encode($logEntry) . "\n"
            );
        } catch (\Exception $e) {
            $this->error("❌ Erro ao enviar mensagem: " . $e->getMessage());
        }
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class LogCleanupCommand extends Command
{
    protected $signature = 'logs:cleanup 
                          {--analyze : Apenas analisa os logs sem limpar}
                          {--clean : Limpa logs desnecessários}
                          {--scan-code : Escaneia código em busca de logs verbosos}
                          {--days=30 : Manter logs dos últimos X dias}';

    protected $description = 'Analisa e limpa logs desnecessários do sistema';

    public function handle()
    {
        $this->info('🧹 Iniciando análise de logs...');

        if ($this->option('scan-code')) {
            $this->scanCodeForLogs();
        }

        if ($this->option('analyze')) {
            $this->analyzeLogs();
        }

        if ($this->option('clean')) {
            $this->cleanupLogs();
        }

        if (!$this->option('analyze') && !$this->option('clean') && !$this->option('scan-code')) {
            $this->info('Use uma das opções: --analyze, --clean ou --scan-code');
            $this->info('Exemplo: php artisan logs:cleanup --analyze --scan-code');
        }
    }

    private function analyzeLogs()
    {
        $logPath = storage_path('logs');
        $logFiles = File::glob($logPath . '/*.log');

        $this->info("\n📊 Análise de Arquivos de Log:");
        $this->table(
            ['Arquivo', 'Tamanho', 'Linhas', 'Última Modificação'],
            collect($logFiles)->map(function ($file) {
                $size = $this->formatBytes(File::size($file));
                $lines = count(file($file));
                $modified = date('d/m/Y H:i', File::lastModified($file));
                return [basename($file), $size, number_format($lines), $modified];
            })
        );

        // Analisa tipos de log mais comuns
        $this->analyzeLogTypes($logFiles);
    }

    private function analyzeLogTypes($logFiles)
    {
        $this->info("\n🔍 Tipos de Log Mais Frequentes:");

        $logTypes = [];

        foreach ($logFiles as $file) {
            $content = File::get($file);

            // Conta SQL logs
            $sqlCount = substr_count($content, 'local.INFO: SQL:');
            if ($sqlCount > 0) {
                $logTypes['SQL Queries'] = ($logTypes['SQL Queries'] ?? 0) + $sqlCount;
            }

            // Conta debug logs
            $debugCount = substr_count($content, 'local.DEBUG:');
            if ($debugCount > 0) {
                $logTypes['Debug'] = ($logTypes['Debug'] ?? 0) + $debugCount;
            }

            // Conta info logs
            $infoCount = substr_count($content, 'local.INFO:') - $sqlCount; // Remove SQL já contados
            if ($infoCount > 0) {
                $logTypes['Info'] = ($logTypes['Info'] ?? 0) + $infoCount;
            }

            // Conta activity logs
            $activityCount = substr_count($content, 'activity_logs');
            if ($activityCount > 0) {
                $logTypes['Activity Logs'] = ($logTypes['Activity Logs'] ?? 0) + $activityCount;
            }

            // Conta cache logs
            $cacheCount = substr_count($content, 'Cache de permissões');
            if ($cacheCount > 0) {
                $logTypes['Cache Info'] = ($logTypes['Cache Info'] ?? 0) + $cacheCount;
            }
        }

        arsort($logTypes);

        $this->table(
            ['Tipo', 'Quantidade'],
            collect($logTypes)->map(function ($count, $type) {
                return [$type, number_format($count)];
            })
        );

        $this->suggestCleanup($logTypes);
    }

    private function suggestCleanup($logTypes)
    {
        $this->info("\n💡 Sugestões de Limpeza:");

        if (($logTypes['SQL Queries'] ?? 0) > 100) {
            $this->warn("• SQL Queries ({$logTypes['SQL Queries']}): Desabilitar DB::listen() em produção");
        }

        if (($logTypes['Debug'] ?? 0) > 50) {
            $this->warn("• Debug Logs ({$logTypes['Debug']}): Remover Log::debug() desnecessários");
        }

        if (($logTypes['Activity Logs'] ?? 0) > 20) {
            $this->warn("• Activity Logs ({$logTypes['Activity Logs']}): Otimizar payload dos activity logs");
        }

        if (($logTypes['Cache Info'] ?? 0) > 10) {
            $this->warn("• Cache Info ({$logTypes['Cache Info']}): Converter para Log::debug() ou remover");
        }
    }

    private function scanCodeForLogs()
    {
        $this->info("\n🔎 Escaneando código em busca de logs verbosos...");

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
                // Procura por logs problemáticos
                if (preg_match('/Log::(info|debug)\s*\(\s*["\']/', $line, $matches)) {
                    $logLevel = $matches[1];
                    $relativePath = str_replace(app_path(), 'app', $file->getPathname());

                    // Identifica logs suspeitos
                    if (
                        stripos($line, 'SQL:') !== false ||
                        stripos($line, 'Buscando') !== false ||
                        stripos($line, 'bypass') !== false ||
                        stripos($line, 'Cache de') !== false
                    ) {
                        $verboseLogs[] = [
                            'arquivo' => $relativePath,
                            'linha' => $lineNumber + 1,
                            'nivel' => strtoupper($logLevel),
                            'conteudo' => trim($line)
                        ];
                    }
                }
            }
        }

        if (empty($verboseLogs)) {
            $this->info("✅ Nenhum log verboso encontrado no código!");
            return;
        }

        $this->warn("⚠️  Logs verbosos encontrados:");
        $this->table(['Arquivo', 'Linha', 'Nível', 'Conteúdo'], $verboseLogs);

        $this->info("\n💡 Para remover:");
        $this->info("• Procure pelos arquivos listados");
        $this->info("• Comente ou remova as linhas indicadas");
        $this->info("• Ou mude Log::info() para Log::debug() quando apropriado");
    }

    private function cleanupLogs()
    {
        $days = (int) $this->option('days');
        $this->info("\n🧹 Limpando logs mais antigos que {$days} dias...");

        $logPath = storage_path('logs');
        $cutoffDate = now()->subDays($days);

        $cleaned = 0;
        $savedSpace = 0;

        $logFiles = File::glob($logPath . '/*.log');

        foreach ($logFiles as $file) {
            $fileDate = \Carbon\Carbon::createFromTimestamp(File::lastModified($file));

            if ($fileDate->lt($cutoffDate)) {
                $size = File::size($file);
                File::delete($file);
                $cleaned++;
                $savedSpace += $size;

                $this->line("🗑️  Removido: " . basename($file) . " ({$this->formatBytes($size)})");
            }
        }

        // Limpa logs atuais de conteúdo desnecessário
        $this->cleanCurrentLogs();

        $this->info("\n✅ Limpeza concluída:");
        $this->info("• {$cleaned} arquivos removidos");
        $this->info("• {$this->formatBytes($savedSpace)} de espaço liberado");
    }

    private function cleanCurrentLogs()
    {
        $this->info("🧽 Limpando conteúdo desnecessário dos logs atuais...");

        $logPath = storage_path('logs');
        $currentLogs = File::glob($logPath . '/laravel-' . date('Y-m-d') . '.log');

        foreach ($currentLogs as $logFile) {
            $content = File::get($logFile);
            $originalSize = strlen($content);

            // Remove SQL logs excessivos
            $content = preg_replace('/^\[.*?\] local\.INFO: SQL:.*$/m', '', $content);

            // Remove activity logs com payload gigante
            $content = preg_replace('/^\[.*?\] local\.INFO: SQL: insert into "activity_logs".*$/m', '', $content);

            // Remove logs de debug específicos
            $content = preg_replace('/^\[.*?\] local\.DEBUG: FilialController\.getFilialsByEmail:.*$/m', '', $content);
            $content = preg_replace('/^\[.*?\] local\.INFO: Superuser bypass ativado.*$/m', '', $content);
            $content = preg_replace('/^\[.*?\] local\.INFO: Cache de permissões.*$/m', '', $content);

            // Remove linhas vazias extras
            $content = preg_replace('/\n{3,}/', "\n\n", $content);

            $newSize = strlen($content);
            $saved = $originalSize - $newSize;

            if ($saved > 0) {
                File::put($logFile, $content);
                $this->line("🧽 Limpo: " . basename($logFile) . " (economizou {$this->formatBytes($saved)})");
            }
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

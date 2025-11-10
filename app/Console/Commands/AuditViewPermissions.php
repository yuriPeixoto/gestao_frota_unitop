<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AuditViewPermissions extends Command
{
    protected $signature = 'permissions:audit-views {--report : Gerar relatório completo}';
    
    protected $description = 'Audita views para identificar links/botões sem proteção de permissão';

    public function handle(): int
    {
        $this->info('🔍 Auditando views para gaps de permissão...');
        $this->newLine();

        $viewsPath = resource_path('views');
        $issues = [];
        $stats = [
            'total_files' => 0,
            'protected_links' => 0,
            'unprotected_links' => 0,
            'files_with_issues' => 0,
        ];

        $files = File::allFiles($viewsPath);
        
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') continue;
            
            $stats['total_files']++;
            $content = File::get($file->getPathname());
            $relativePath = str_replace(resource_path('views/'), '', $file->getPathname());
            
            // Buscar links suspeitos (admin routes sem proteção)
            $suspiciousPatterns = [
                '/href=".*route\(.*admin\..*\).*"/' => 'Link para rota admin',
                '/href=".*\/admin\/.*"/' => 'Link direto para URL admin',
                '/<button.*onclick=".*admin.*"/' => 'Botão com ação admin',
                '/<a.*\/admin\/.*create/' => 'Link de criação',
                '/<a.*\/admin\/.*edit/' => 'Link de edição',
            ];

            $fileIssues = [];
            
            foreach ($suspiciousPatterns as $pattern => $description) {
                if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                    foreach ($matches[0] as $match) {
                        $lineNumber = substr_count(substr($content, 0, $match[1]), "\n") + 1;
                        
                        // Verificar se está protegido (@can, @if, PermissionHelper)
                        $contextStart = max(0, $match[1] - 500);
                        $contextEnd = min(strlen($content), $match[1] + 500);
                        $context = substr($content, $contextStart, $contextEnd - $contextStart);
                        
                        $isProtected = preg_match('/@can\(|@if\(.*Permission|PermissionHelper::/', $context);
                        
                        if ($isProtected) {
                            $stats['protected_links']++;
                        } else {
                            $stats['unprotected_links']++;
                            $fileIssues[] = [
                                'line' => $lineNumber,
                                'type' => $description,
                                'code' => trim($match[0])
                            ];
                        }
                    }
                }
            }
            
            if (!empty($fileIssues)) {
                $issues[$relativePath] = $fileIssues;
                $stats['files_with_issues']++;
            }
        }

        $this->displayResults($stats, $issues);
        
        if ($this->option('report')) {
            $this->generateReport($stats, $issues);
        }

        return 0;
    }

    private function displayResults(array $stats, array $issues): void
    {
        $this->newLine();
        $this->info('📊 RESULTADOS DA AUDITORIA DE VIEWS');
        $this->info('=' . str_repeat('=', 50));
        
        $this->table(
            ['Métrica', 'Quantidade', 'Status'],
            [
                ['Arquivos analisados', $stats['total_files'], ''],
                ['Links protegidos', $stats['protected_links'], '✅'],
                ['Links sem proteção', $stats['unprotected_links'], $stats['unprotected_links'] > 0 ? '⚠️' : '✅'],
                ['Arquivos com issues', $stats['files_with_issues'], $stats['files_with_issues'] > 0 ? '⚠️' : '✅'],
            ]
        );

        if ($stats['unprotected_links'] > 0) {
            $this->newLine();
            $this->warn('⚠️  ARQUIVOS COM POSSÍVEIS GAPS:');
            
            foreach (array_slice($issues, 0, 5, true) as $file => $fileIssues) {
                $this->line("📁 {$file}");
                foreach (array_slice($fileIssues, 0, 3) as $issue) {
                    $this->line("   Linha {$issue['line']}: {$issue['type']}");
                    $this->line("   Code: " . substr($issue['code'], 0, 80) . "...");
                }
                if (count($fileIssues) > 3) {
                    $this->line("   ... e " . (count($fileIssues) - 3) . " outros");
                }
                $this->newLine();
            }
            
            if (count($issues) > 5) {
                $this->line("... e " . (count($issues) - 5) . " outros arquivos com issues");
            }
        }

        $this->newLine();
        
        if ($stats['unprotected_links'] == 0) {
            $this->info('🎉 EXCELENTE! Nenhum gap crítico encontrado.');
        } else {
            $coverage = round(($stats['protected_links'] / ($stats['protected_links'] + $stats['unprotected_links'])) * 100, 1);
            $this->info("📈 Cobertura atual: {$coverage}%");
            
            if ($coverage > 80) {
                $this->info('✅ Boa cobertura! Ajustes pontuais recomendados.');
            } else {
                $this->warn('⚠️  Cobertura baixa. Considere implementar proteções.');
            }
        }
    }

    private function generateReport(array $stats, array $issues): void
    {
        $report = [
            'timestamp' => now()->toISOString(),
            'stats' => $stats,
            'issues' => $issues,
        ];

        $reportPath = storage_path('app/audit-views-permissions.json');
        File::put($reportPath, json_encode($report, JSON_PRETTY_PRINT));
        
        $this->info("📄 Relatório salvo em: {$reportPath}");
    }
}
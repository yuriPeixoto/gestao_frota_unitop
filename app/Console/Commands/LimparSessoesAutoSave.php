<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class LimparSessoesAutoSave extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autosave:limpar-sessoes
                            {--force : Forçar limpeza de todas as sessões}
                            {--older-than=24 : Limpar sessões mais antigas que X horas}
                            {--stats : Mostrar apenas estatísticas sem limpar}
                            {--user= : Limpar sessões de um usuário específico}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpa sessões antigas do sistema de auto-save de movimentação de pneus';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Sistema de Limpeza de Sessões Auto-Save');
        $this->info('================================================');

        $force = $this->option('force');
        $olderThanHours = (int) $this->option('older-than');
        $statsOnly = $this->option('stats');
        $specificUser = $this->option('user');

        try {
            if ($statsOnly) {
                $this->showStats();
                return 0;
            }

            if ($force) {
                $this->limparTodasSessoes($specificUser);
            } else {
                $this->limparSessoesAntigas($olderThanHours, $specificUser);
            }

            $this->info('✅ Limpeza concluída com sucesso!');
            $this->showStats();
        } catch (\Exception $e) {
            Log::error('Erro na limpeza de sessões: ' . $e->getMessage());
            $this->error('❌ Erro durante a limpeza: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Limpar todas as sessões (usado com --force)
     */
    protected function limparTodasSessoes($specificUser = null)
    {
        $this->warn('⚠️ ATENÇÃO: Limpando TODAS as sessões auto-save!');

        if ($specificUser) {
            $this->info("📍 Foco: Usuário ID {$specificUser}");
        }

        if (!$this->confirm('Tem certeza que deseja continuar?')) {
            $this->info('Operação cancelada.');
            return;
        }

        $pattern = $specificUser
            ? "movimentacao_pneus_*_{$specificUser}"
            : 'movimentacao_pneus_*';

        $keys = $this->getKeysWithPattern($pattern);

        $count = 0;
        $progressBar = $this->output->createProgressBar(count($keys));
        $progressBar->start();

        foreach ($keys as $key) {
            Cache::forget($key);
            $count++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->info("🗑️ {$count} sessões removidas (TODAS)");

        Log::info('Limpeza forçada de sessões auto-save', [
            'total_removidas' => $count,
            'usuario_especifico' => $specificUser,
            'executado_por' => 'artisan_command',
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Limpar apenas sessões antigas
     */
    protected function limparSessoesAntigas($olderThanHours, $specificUser = null)
    {
        $this->info("🕒 Limpando sessões mais antigas que {$olderThanHours} horas...");

        if ($specificUser) {
            $this->info("📍 Foco: Usuário ID {$specificUser}");
        }

        $pattern = $specificUser
            ? "movimentacao_pneus_*_{$specificUser}"
            : 'movimentacao_pneus_*';

        $keys = $this->getKeysWithPattern($pattern);

        $count = 0;
        $skipped = 0;
        $cutoffTime = now()->subHours($olderThanHours);

        $progressBar = $this->output->createProgressBar(count($keys));
        $progressBar->start();

        foreach ($keys as $key) {
            $sessionData = Cache::get($key);

            if ($sessionData && isset($sessionData['last_update'])) {
                try {
                    $lastUpdate = \Carbon\Carbon::parse($sessionData['last_update']);

                    if ($lastUpdate->lt($cutoffTime)) {
                        Cache::forget($key);
                        $count++;

                        if ($this->getOutput()->isVerbose()) {
                            $this->line("🗑️ Removida: {$key} (última atualização: {$lastUpdate->diffForHumans()})");
                        }
                    } else {
                        $skipped++;
                    }
                } catch (\Exception $e) {
                    // Data inválida, remover sessão
                    Cache::forget($key);
                    $count++;

                    if ($this->getOutput()->isVerbose()) {
                        $this->line("🗑️ Removida: {$key} (data inválida)");
                    }
                }
            } else {
                // Sessão sem dados válidos, remover
                Cache::forget($key);
                $count++;

                if ($this->getOutput()->isVerbose()) {
                    $this->line("🗑️ Removida: {$key} (dados inválidos)");
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->info("✨ {$count} sessões antigas removidas");
        $this->info("📄 {$skipped} sessões ativas mantidas");

        Log::info('Limpeza programada de sessões auto-save', [
            'total_removidas' => $count,
            'total_mantidas' => $skipped,
            'cutoff_hours' => $olderThanHours,
            'usuario_especifico' => $specificUser,
            'executado_por' => 'artisan_command',
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Buscar chaves do cache que correspondem ao padrão
     */
    protected function getKeysWithPattern($pattern)
    {
        $keys = [];

        try {
            $cacheDriver = config('cache.default');

            switch ($cacheDriver) {
                case 'redis':
                    $keys = $this->getRedisKeys($pattern);
                    break;

                case 'file':
                    $keys = $this->getFileKeys($pattern);
                    break;

                case 'database':
                    $keys = $this->getDatabaseKeys($pattern);
                    break;

                default:
                    $this->warn("⚠️ Driver de cache '{$cacheDriver}' não suportado para listagem automática.");
                    $this->info("💡 Usando método alternativo com índice de sessões...");
                    $keys = $this->getIndexKeys($pattern);
                    break;
            }
        } catch (\Exception $e) {
            $this->warn("⚠️ Erro ao listar chaves: " . $e->getMessage());
            $this->info("💡 Tentando método alternativo...");
            $keys = $this->getIndexKeys($pattern);
        }

        return $keys;
    }

    /**
     * Buscar chaves no Redis
     */
    protected function getRedisKeys($pattern)
    {
        try {
            if (class_exists('\Illuminate\Support\Facades\Redis')) {
                $redis = Redis::connection();
                return $redis->keys($pattern);
            } else {
                // Fallback para conexão direta
                $redis = Cache::getRedis();
                return $redis->keys($pattern);
            }
        } catch (\Exception $e) {
            throw new \Exception("Erro no Redis: " . $e->getMessage());
        }
    }

    /**
     * Buscar chaves no cache de arquivo
     */
    protected function getFileKeys($pattern)
    {
        $keys = [];
        $cacheDir = storage_path('framework/cache/data');

        if (!is_dir($cacheDir)) {
            return $keys;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($cacheDir)
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $content = file_get_contents($file->getPathname());

                // Decodificar arquivo de cache do Laravel
                if (preg_match('/s:\d+:"([^"]*movimentacao_pneus[^"]*)";/', $content, $matches)) {
                    $key = $matches[1];
                    if (fnmatch($pattern, $key)) {
                        $keys[] = $key;
                    }
                }
            }
        }

        return $keys;
    }

    /**
     * Buscar chaves no cache de database
     */
    protected function getDatabaseKeys($pattern)
    {
        $table = config('cache.stores.database.table', 'cache');

        return DB::connection('pgsql')->table($table)
            ->where('key', 'LIKE', str_replace('*', '%', $pattern))
            ->pluck('key')
            ->toArray();
    }

    /**
     * Buscar chaves usando índice de sessões
     */
    protected function getIndexKeys($pattern)
    {
        $indexKey = 'autosave_sessions_index';
        $sessionIndex = Cache::get($indexKey, []);

        $keys = [];
        foreach ($sessionIndex as $key) {
            if (fnmatch($pattern, $key)) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * Mostrar estatísticas das sessões
     */
    public function showStats()
    {
        $this->info('📊 Estatísticas das Sessões Auto-Save');
        $this->info('=====================================');

        $pattern = 'movimentacao_pneus_*';
        $keys = $this->getKeysWithPattern($pattern);

        $totalSessions = count($keys);
        $activeSessions = 0;
        $oldSessions = 0;
        $invalidSessions = 0;
        $totalOperations = 0;
        $userStats = [];
        $vehicleStats = [];

        $cutoffTime = now()->subHours(2); // Sessões ativas = últimas 2 horas

        foreach ($keys as $key) {
            $sessionData = Cache::get($key);

            if (!$sessionData || !isset($sessionData['last_update'])) {
                $invalidSessions++;
                continue;
            }

            try {
                $lastUpdate = \Carbon\Carbon::parse($sessionData['last_update']);
                $operationsCount = count($sessionData['operacoes'] ?? []);
                $totalOperations += $operationsCount;

                // Extrair user_id e vehicle_id da chave
                if (preg_match('/movimentacao_pneus_(\d+)_(\d+)/', $key, $matches)) {
                    $vehicleId = $matches[1];
                    $userId = $matches[2];

                    $userStats[$userId] = ($userStats[$userId] ?? 0) + 1;
                    $vehicleStats[$vehicleId] = ($vehicleStats[$vehicleId] ?? 0) + 1;
                }

                if ($lastUpdate->gt($cutoffTime)) {
                    $activeSessions++;
                } else {
                    $oldSessions++;
                }
            } catch (\Exception $e) {
                $invalidSessions++;
            }
        }

        // Tabela principal
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total de sessões', $totalSessions],
                ['Sessões ativas (< 2h)', $activeSessions],
                ['Sessões antigas (> 2h)', $oldSessions],
                ['Sessões inválidas', $invalidSessions],
                ['Total de operações', $totalOperations],
                ['Média operações/sessão', $totalSessions > 0 ? round($totalOperations / $totalSessions, 2) : 0],
            ]
        );

        // Top 5 usuários com mais sessões
        if (!empty($userStats)) {
            $this->info('👥 Top 5 Usuários (por número de sessões):');
            arsort($userStats);
            $topUsers = array_slice($userStats, 0, 5, true);

            $userData = [];
            foreach ($topUsers as $userId => $count) {
                $userData[] = ["Usuário {$userId}", $count];
            }

            $this->table(['Usuário', 'Sessões'], $userData);
        }

        // Top 5 veículos com mais sessões
        if (!empty($vehicleStats)) {
            $this->info('🚗 Top 5 Veículos (por número de sessões):');
            arsort($vehicleStats);
            $topVehicles = array_slice($vehicleStats, 0, 5, true);

            $vehicleData = [];
            foreach ($topVehicles as $vehicleId => $count) {
                $vehicleData[] = ["Veículo {$vehicleId}", $count];
            }

            $this->table(['Veículo', 'Sessões'], $vehicleData);
        }

        // Recomendações
        $this->info('💡 Recomendações:');

        if ($oldSessions > 100) {
            $this->warn("⚠️ Muitas sessões antigas ({$oldSessions}). Execute limpeza: php artisan autosave:limpar-sessoes");
        }

        if ($invalidSessions > 10) {
            $this->warn("⚠️ Muitas sessões inválidas ({$invalidSessions}). Execute: php artisan autosave:limpar-sessoes --force");
        }

        if ($totalSessions == 0) {
            $this->info("✨ Nenhuma sessão encontrada. Sistema limpo!");
        } elseif ($activeSessions > 0) {
            $this->info("✅ {$activeSessions} sessões ativas encontradas.");
        }

        // Uso de memória aproximado
        $avgSessionSize = 2; // KB aproximado por sessão
        $totalMemoryUsage = $totalSessions * $avgSessionSize;

        if ($totalMemoryUsage > 100) { // Mais de 100KB
            $this->info("💾 Uso aproximado de memória: {$totalMemoryUsage}KB");
        }

        return [
            'total_sessions' => $totalSessions,
            'active_sessions' => $activeSessions,
            'old_sessions' => $oldSessions,
            'invalid_sessions' => $invalidSessions,
            'total_operations' => $totalOperations,
            'user_stats' => $userStats,
            'vehicle_stats' => $vehicleStats
        ];
    }

    /**
     * Limpar sessões de um veículo específico
     */
    public function clearVehicleSessions($vehicleId)
    {
        $pattern = "movimentacao_pneus_{$vehicleId}_*";
        $keys = $this->getKeysWithPattern($pattern);

        $count = 0;
        foreach ($keys as $key) {
            Cache::forget($key);
            $count++;
        }

        $this->info("🗑️ {$count} sessões do veículo {$vehicleId} removidas");

        Log::info('Limpeza de sessões por veículo', [
            'veiculo_id' => $vehicleId,
            'total_removidas' => $count,
            'executado_por' => 'artisan_command'
        ]);

        return $count;
    }

    /**
     * Verificar integridade das sessões
     */
    public function checkIntegrity()
    {
        $this->info('🔍 Verificando integridade das sessões...');

        $pattern = 'movimentacao_pneus_*';
        $keys = $this->getKeysWithPattern($pattern);

        $corruptedSessions = 0;
        $validSessions = 0;

        foreach ($keys as $key) {
            $sessionData = Cache::get($key);

            if (!$this->isValidSessionData($sessionData)) {
                $corruptedSessions++;
                $this->line("⚠️ Sessão corrompida: {$key}");

                if ($this->confirm("Remover sessão corrompida {$key}?")) {
                    Cache::forget($key);
                    $this->info("🗑️ Sessão removida");
                }
            } else {
                $validSessions++;
            }
        }

        $this->info("✅ {$validSessions} sessões válidas");
        $this->info("❌ {$corruptedSessions} sessões corrompidas");

        return $corruptedSessions === 0;
    }

    /**
     * Validar estrutura dos dados da sessão
     */
    protected function isValidSessionData($sessionData)
    {
        if (!is_array($sessionData)) {
            return false;
        }

        $requiredFields = ['last_update', 'operacoes', 'dados_veiculo'];

        foreach ($requiredFields as $field) {
            if (!isset($sessionData[$field])) {
                return false;
            }
        }

        // Validar formato da data
        try {
            \Carbon\Carbon::parse($sessionData['last_update']);
        } catch (\Exception $e) {
            return false;
        }

        // Validar se operações é array
        if (!is_array($sessionData['operacoes'])) {
            return false;
        }

        return true;
    }
}

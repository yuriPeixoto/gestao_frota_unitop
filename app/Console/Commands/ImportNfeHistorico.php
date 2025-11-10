<?php

namespace App\Console\Commands;

use App\Jobs\ProcessNfeFile;
use App\Services\Nfe\NfeImportService;
use App\Services\Nfe\NfeProcessor;
use App\Services\Nfe\NfePersistence;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ImportNfeHistorico extends Command
{
    /**
     * Nome e assinatura do comando console.
     *
     * @var string
     */
    protected $signature = 'nfe:import-historico
                            {--queue : Enviar processamento para a fila}
                            {--dry-run : Executa sem realmente importar}
                            {--limit= : Limitar número de arquivos processados}';

    /**
     * Descrição do comando console.
     *
     * @var string
     */
    protected $description = 'Importa arquivos XML de NFe da pasta HISTÓRICO (execução diária)';

    /**
     * Executa o comando console.
     *
     * @return int
     */
    public function handle()
    {
        $queue = $this->option('queue');
        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit');
        $verbose = $this->option('verbose');

        Log::channel('nfe')->info('=== INICIANDO IMPORTAÇÃO DE NFe HISTÓRICO ===', [
            'options' => [
                'queue' => $queue,
                'dry-run' => $dryRun,
                'limit' => $limit,
                'verbose' => $verbose
            ]
        ]);

        try {
            // Configuração específica para pasta histórico
            $ftpConfig = [
                'host' => config('nfe-import.ftp.host'),
                'username' => config('nfe-import.ftp.username'),
                'password' => config('nfe-import.ftp.password'),
                'port' => config('nfe-import.ftp.port'),
            ];

            // IMPORTANTE: Usar sempre o banco de produção
            $dbConnection = config('nfe-import.database.connection', 'pgsql');

            // Validação de segurança
            if ($dbConnection !== 'pgsql' && !config('nfe-import.database.allow_staging')) {
                $this->error('❌ ATENÇÃO: NFe deve sempre usar o banco de PRODUÇÃO!');
                $this->error('Conexão atual: ' . $dbConnection);
                $this->error('Configure NFE_DATABASE_CONNECTION=pgsql no .env');
                return 1;
            }

            $this->info('📁 Processando pasta: XMLs-HISTORICO');
            $this->info('🗄️ Banco de dados: ' . $dbConnection);

            // Criar instância do persistence com a conexão correta
            $persistence = new NfePersistence();

            // Criar instância do processor
            $processor = new NfeProcessor($persistence);

            // Criar o serviço de importação
            $importer = new NfeImportService($ftpConfig, $processor);

            if (!$dryRun) {
                // Baixar arquivos da pasta HISTÓRICO
                $downloadResult = $this->downloadFromHistorico($importer, $ftpConfig, $limit);

                if (!$downloadResult['success']) {
                    $this->warn('Aviso durante download: ' . $downloadResult['message']);
                }

                $this->info("📥 Arquivos baixados: " . $downloadResult['downloaded']);
                $this->info("📋 Arquivos na fila: " . $downloadResult['queued']);
            }

            // Processar os arquivos baixados
            $queueDir = config('nfe-import.directories.queue', storage_path('app/nfe/queue'));
            $files = File::glob($queueDir . '/*.xml');

            if (empty($files)) {
                $this->info('✅ Nenhum arquivo para processar.');
                return 0;
            }

            $totalFiles = count($files);
            $processedCount = 0;
            $failedCount = 0;

            $this->info("🔄 Processando {$totalFiles} arquivos...");

            foreach ($files as $index => $file) {
                if ($limit && $index >= $limit) {
                    $this->info("🛑 Limite de {$limit} arquivos atingido.");
                    break;
                }

                if ($verbose) {
                    $this->info(sprintf(
                        '[%d/%d] Processando: %s',
                        $index + 1,
                        $totalFiles,
                        basename($file)
                    ));
                }

                if ($dryRun) {
                    $this->info('🔍 Modo simulação: ' . basename($file));
                    continue;
                }

                if ($queue) {
                    // Enviar para fila com prioridade normal para histórico
                    ProcessNfeFile::dispatch($file)->onQueue('nfe-historico');

                    if ($verbose) {
                        $this->info('📤 Enviado para fila: ' . basename($file));
                    }
                    $processedCount++;
                } else {
                    // Processar imediatamente
                    try {
                        $result = $importer->processFile($file);

                        if ($result['success']) {
                            $processedCount++;
                            if ($verbose) {
                                $this->info('✅ Processado: ' . basename($file));
                            }
                        } else {
                            $failedCount++;
                            $this->error('❌ Falha: ' . ($result['error'] ?? 'Erro desconhecido'));
                        }
                    } catch (\Exception $e) {
                        $failedCount++;
                        $this->error('❌ Erro: ' . $e->getMessage());
                    }
                }
            }

            // Relatório final
            $this->info('');
            $this->info('📊 === RELATÓRIO FINAL ===');
            $this->info("✅ Processados com sucesso: {$processedCount}");
            $this->info("❌ Falhas: {$failedCount}");
            $this->info("📁 Total de arquivos: {$totalFiles}");

            Log::channel('nfe')->info('Comando nfe:import-historico concluído', [
                'processed' => $processedCount,
                'failed' => $failedCount,
                'total' => $totalFiles
            ]);

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Erro crítico: ' . $e->getMessage());
            Log::channel('nfe')->error('Erro crítico no comando nfe:import-historico', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Baixa arquivos da pasta histórico via FTP
     *
     * @param NfeImportService $importer
     * @param array $ftpConfig
     * @param int|null $limit
     * @return array
     */
    private function downloadFromHistorico($importer, $ftpConfig, $limit = null): array
    {
        $result = [
            'success' => true,
            'downloaded' => 0,
            'queued' => 0,
            'message' => ''
        ];

        try {
            // Conectar ao FTP
            $conn = $this->getFTPConnection($ftpConfig);
            if (!$conn) {
                throw new \Exception('Não foi possível conectar ao FTP');
            }

            // Acessar pasta XMLs-HISTORICO
            $ftpDir = 'XMLs-HISTORICO';
            if (!@ftp_chdir($conn, $ftpDir)) {
                throw new \Exception("Erro ao acessar diretório FTP: {$ftpDir}");
            }

            $this->info("📂 Conectado ao diretório: {$ftpDir}");

            // Listar arquivos
            $files = @ftp_nlist($conn, ".");
            if ($files === false) {
                throw new \Exception('Erro ao listar arquivos do FTP');
            }

            // Filtrar apenas XMLs
            $xmlFiles = array_filter($files, function ($file) {
                return strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'xml';
            });

            $totalXmlFiles = count($xmlFiles);
            $this->info("📊 Total de arquivos XML encontrados: {$totalXmlFiles}");

            // Aplicar limite se especificado
            if ($limit) {
                $xmlFiles = array_slice($xmlFiles, 0, $limit);
                $this->info("🔢 Limitando download a {$limit} arquivos");
            }

            // Diretório local para queue
            $queueDir = config('nfe-import.directories.queue');
            if (!File::exists($queueDir)) {
                File::makeDirectory($queueDir, 0755, true);
            }

            // Baixar arquivos
            $progressBar = $this->output->createProgressBar(count($xmlFiles));
            $progressBar->start();

            foreach ($xmlFiles as $file) {
                $localFile = $queueDir . '/' . basename($file);

                // Verificar se já existe localmente
                if (File::exists($localFile)) {
                    $progressBar->advance();
                    continue;
                }

                // Baixar arquivo
                $success = @ftp_get($conn, $localFile, $file, FTP_BINARY);

                if ($success) {
                    $result['downloaded']++;

                    // Criar arquivo de metadados
                    $meta = [
                        'original_file' => $file,
                        'source_directory' => 'XMLs-HISTORICO',
                        'queued_at' => now()->toDateTimeString(),
                        'attempts' => 0
                    ];

                    $metaFile = $localFile . '.meta';
                    File::put($metaFile, json_encode($meta));

                    $result['queued']++;
                } else {
                    Log::channel('nfe')->error('Erro ao baixar arquivo: ' . $file);
                }

                $progressBar->advance();

                // Pequena pausa para não sobrecarregar
                usleep(100000); // 100ms
            }

            $progressBar->finish();
            $this->info(''); // Nova linha após progress bar

            // Fechar conexão FTP
            @ftp_close($conn);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            Log::channel('nfe')->error('Erro no download de histórico: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Estabelece conexão FTP
     *
     * @param array $config
     * @return resource|false
     */
    private function getFTPConnection($config)
    {
        try {
            // Tentar conexão FTPS primeiro
            $conn = @ftp_ssl_connect($config['host'], $config['port'], 60);

            if (!$conn) {
                // Fallback para FTP normal
                $conn = @ftp_connect($config['host'], $config['port'], 60);
            }

            if (!$conn) {
                return false;
            }

            // Login
            if (!@ftp_login($conn, $config['username'], $config['password'])) {
                @ftp_close($conn);
                return false;
            }

            // Modo passivo
            ftp_pasv($conn, true);

            return $conn;
        } catch (\Exception $e) {
            Log::channel('nfe')->error('Erro na conexão FTP: ' . $e->getMessage());
            return false;
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Jobs\ProcessNfeFile;
use App\Services\Nfe\NfeImportService;
use App\Services\Nfe\NfePersistence;
use App\Services\Nfe\NfeProcessor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ImportNfeHoje extends Command
{
    /**
     * Nome e assinatura do comando console.
     *
     * @var string
     */
    protected $signature = 'nfe:import-hoje
                            {--queue : Enviar processamento para a fila}
                            {--dry-run : Executa sem realmente importar}
                            {--limit= : Limitar número de arquivos processados}';

    /**
     * Descrição do comando console.
     *
     * @var string
     */
    protected $description = 'Importa arquivos XML de NFe da pasta HOJE (execução horária)';

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

        Log::channel('nfe')->info('=== INICIANDO IMPORTAÇÃO DE NFe HOJE ===', [
            'options' => [
                'queue' => $queue,
                'dry-run' => $dryRun,
                'limit' => $limit,
                'verbose' => $verbose,
            ],
        ]);

        try {
            // Configuração específica para pasta hoje
            $ftpConfig = [
                'host' => config('nfe-import.ftp.host'),
                'username' => config('nfe-import.ftp.username'),
                'password' => config('nfe-import.ftp.password'),
                'port' => config('nfe-import.ftp.port'),
            ];

            // IMPORTANTE: Usar sempre o banco de produção
            $dbConnection = config('nfe-import.database.connection', 'pgsql');
            $dbConnection = config('nfe-import.database.connection', 'pgsql');

            // Validação de segurança
            if ($dbConnection !== 'pgsql' && ! config('nfe-import.database.allow_staging')) {
                $this->error('❌ ATENÇÃO: NFe deve sempre usar o banco de PRODUÇÃO!');
                $this->error('Conexão atual: '.$dbConnection);
                $this->error('Configure NFE_DATABASE_CONNECTION=pgsql no .env');

                return 1;
            }

            $this->info('📁 Processando pasta: XMLs-HOJE');
            $this->info('🗄️ Banco de dados: '.$dbConnection);
            $this->info('⏰ Execução: '.now()->format('d/m/Y H:i:s'));

            // Criar instância do persistence com a conexão correta
            $persistence = new NfePersistence;

            // Criar instância do processor
            $processor = new NfeProcessor($persistence);

            // Criar o serviço de importação
            $importer = new NfeImportService($ftpConfig, $processor);

            if (! $dryRun) {
                // Baixar arquivos da pasta HOJE
                $downloadResult = $this->downloadFromHoje($importer, $ftpConfig, $limit);

                if (! $downloadResult['success']) {
                    $this->warn('Aviso durante download: '.$downloadResult['message']);
                }

                $this->info('📥 Arquivos baixados: '.$downloadResult['downloaded']);
                $this->info('📋 Arquivos na fila: '.$downloadResult['queued']);
                $this->info('♻️ Arquivos já existentes: '.$downloadResult['already_exists']);
            }

            // Processar os arquivos baixados
            $queueDir = config('nfe-import.directories.queue', storage_path('app/nfe/queue'));
            $files = File::glob($queueDir.'/*.xml');

            if (empty($files)) {
                $this->info('✅ Nenhum arquivo novo para processar.');

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
                    $this->info('🔍 Modo simulação: '.basename($file));

                    continue;
                }

                if ($queue) {
                    // Enviar para fila com prioridade alta para arquivos de hoje
                    try {
                        ProcessNfeFile::dispatch($file)->onQueue('nfe-hoje');

                        if ($verbose) {
                            $this->info('📤 Enviado para fila prioritária: '.basename($file));
                        }
                        $processedCount++;
                    } catch (\Exception $e) {
                        $failedCount++;
                        $this->warn('⚠️ Erro ao enviar para fila: '.basename($file).' - '.$e->getMessage());

                        continue;
                    }
                } else {
                    // Processar imediatamente - CORRIGIDO PARA CONTINUAR APÓS ERROS
                    try {
                        $result = $importer->processFile($file);

                        if (isset($result['success']) && $result['success']) {
                            $processedCount++;
                            if ($verbose) {
                                $this->info('✅ Processado: '.basename($file));
                            }
                        } else {
                            // IMPORTANTE: Incrementa contador mas CONTINUA
                            $failedCount++;
                            $errorMsg = isset($result['error']) ? $result['error'] : 'Erro desconhecido';

                            if ($verbose) {
                                $this->warn('⚠️ Falha: '.basename($file).' - '.$errorMsg);
                            } else {
                                // Se não for verbose, mostra apenas um ponto para indicar progresso
                                $this->output->write('.');
                            }

                            // Log do erro
                            Log::channel('nfe')->warning('Arquivo com erro movido para reprocessamento: '.basename($file), [
                                'error' => $errorMsg,
                            ]);

                            // CONTINUA para o próximo arquivo
                            continue;
                        }
                    } catch (\Exception $e) {
                        // IMPORTANTE: Captura exceção mas CONTINUA processando
                        $failedCount++;

                        if ($verbose) {
                            $this->error('❌ Exceção: '.basename($file).' - '.$e->getMessage());
                        } else {
                            // Se não for verbose, mostra apenas um X para indicar erro
                            $this->output->write('x');
                        }

                        // Log do erro
                        Log::channel('nfe')->error('Exceção ao processar arquivo: '.basename($file), [
                            'error' => $e->getMessage(),
                            'file' => $file,
                            'trace' => $e->getTraceAsString(),
                        ]);

                        // CONTINUA para o próximo arquivo
                        continue;
                    }
                }

                // A cada 100 arquivos, mostra progresso
                if (! $verbose && ($index + 1) % 100 == 0) {
                    $this->info(sprintf(
                        ' [%d/%d] Processados: %d | Falhas: %d',
                        $index + 1,
                        $totalFiles,
                        $processedCount,
                        $failedCount
                    ));
                }
            }

            // Se não estava em modo verbose, adiciona nova linha após os pontos/x
            if (! $verbose && $totalFiles > 0) {
                $this->info(''); // Nova linha
            }

            // Relatório final
            $this->info('');
            $this->info('📊 === RELATÓRIO FINAL ===');
            $this->info("✅ Processados com sucesso: {$processedCount}");
            $this->info("❌ Falhas: {$failedCount}");
            $this->info("📁 Total de arquivos: {$totalFiles}");

            Log::channel('nfe')->info('Comando nfe:import-hoje concluído', [
                'processed' => $processedCount,
                'failed' => $failedCount,
                'total' => $totalFiles,
            ]);

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Erro crítico: '.$e->getMessage());
            Log::channel('nfe')->error('Erro crítico no comando nfe:import-hoje', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }

    /**
     * Baixa arquivos da pasta hoje via FTP
     *
     * @param  NfeImportService  $importer
     * @param  array  $ftpConfig
     * @param  int|null  $limit
     */
    private function downloadFromHoje($importer, $ftpConfig, $limit = null): array
    {
        $result = [
            'success' => true,
            'downloaded' => 0,
            'queued' => 0,
            'already_exists' => 0,
            'message' => '',
        ];

        try {
            // Conectar ao FTP
            $conn = $this->getFTPConnection($ftpConfig);
            if (! $conn) {
                throw new \Exception('Não foi possível conectar ao FTP');
            }

            // Acessar pasta XMLs-HOJE
            $ftpDir = 'XMLs-HOJE';
            if (! @ftp_chdir($conn, $ftpDir)) {
                throw new \Exception("Erro ao acessar diretório FTP: {$ftpDir}");
            }

            $this->info("📂 Conectado ao diretório: {$ftpDir}");

            // Listar arquivos
            $files = @ftp_nlist($conn, '.');
            if ($files === false) {
                throw new \Exception('Erro ao listar arquivos do FTP');
            }

            // Filtrar apenas XMLs
            $xmlFiles = array_filter($files, function ($file) {
                return strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'xml';
            });

            $totalXmlFiles = count($xmlFiles);
            $this->info("📊 Total de arquivos XML encontrados: {$totalXmlFiles}");

            // Verificar quais NFes já existem no banco
            $nfeNumbers = array_map(function ($file) {
                return str_replace(['-nfe.xml', '-NFe.xml'], '', basename($file));
            }, $xmlFiles);

            $existingNFes = $this->checkExistingNFes($nfeNumbers);
            $result['already_exists'] = count($existingNFes);

            // Filtrar apenas arquivos novos
            $newFiles = array_filter($xmlFiles, function ($file) use ($existingNFes) {
                $number = str_replace(['-nfe.xml', '-NFe.xml'], '', basename($file));

                return ! in_array($number, $existingNFes);
            });

            if (empty($newFiles)) {
                $this->info('✅ Todos os arquivos já foram processados anteriormente.');
                @ftp_close($conn);

                return $result;
            }

            $this->info('🆕 NFes novas para processar: '.count($newFiles));

            // Aplicar limite se especificado
            if ($limit) {
                $newFiles = array_slice($newFiles, 0, $limit);
                $this->info("🔢 Limitando download a {$limit} arquivos");
            }

            // Diretório local para queue
            $queueDir = config('nfe-import.directories.queue');
            if (! File::exists($queueDir)) {
                File::makeDirectory($queueDir, 0755, true);
            }

            // Baixar arquivos
            $progressBar = $this->output->createProgressBar(count($newFiles));
            $progressBar->start();

            foreach ($newFiles as $file) {
                $localFile = $queueDir.'/'.basename($file);

                // Baixar arquivo com retry
                $attempts = 0;
                $success = false;

                while (! $success && $attempts < 3) {
                    $attempts++;
                    $success = @ftp_get($conn, $localFile, $file, FTP_BINARY);

                    if (! $success && $attempts < 3) {
                        usleep(500000); // 500ms entre tentativas
                    }
                }

                if ($success) {
                    $result['downloaded']++;

                    // Criar arquivo de metadados
                    $meta = [
                        'original_file' => $file,
                        'source_directory' => 'XMLs-HOJE',
                        'queued_at' => now()->toDateTimeString(),
                        'attempts' => 0,
                    ];

                    $metaFile = $localFile.'.meta';
                    File::put($metaFile, json_encode($meta));

                    $result['queued']++;
                } else {
                    Log::channel('nfe')->error('Erro ao baixar arquivo após 3 tentativas: '.$file);
                }

                $progressBar->advance();

                // Pequena pausa para não sobrecarregar
                usleep(50000); // 50ms (menor pausa pois é execução horária)
            }

            $progressBar->finish();
            $this->info(''); // Nova linha após progress bar

            // Fechar conexão FTP
            @ftp_close($conn);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            Log::channel('nfe')->error('Erro no download de hoje: '.$e->getMessage());
        }

        return $result;
    }

    /**
     * Verifica quais NFes já existem no banco
     */
    private function checkExistingNFes(array $nfeNumbers): array
    {
        if (empty($nfeNumbers)) {
            return [];
        }

        // Usar o Model NfeCore com a conexão de produção
        return \App\Models\NfeCore::on('pgsql')
            ->whereIn('infnfe', $nfeNumbers)
            ->pluck('infnfe')
            ->toArray();
    }

    /**
     * Estabelece conexão FTP
     *
     * @param  array  $config
     * @return resource|false
     */
    private function getFTPConnection($config)
    {
        try {
            // Tentar conexão FTPS primeiro
            $conn = @ftp_ssl_connect($config['host'], $config['port'], 60);

            if (! $conn) {
                // Fallback para FTP normal
                $conn = @ftp_connect($config['host'], $config['port'], 60);
            }

            if (! $conn) {
                return false;
            }

            // Login
            if (! @ftp_login($conn, $config['username'], $config['password'])) {
                @ftp_close($conn);

                return false;
            }

            // Modo passivo
            ftp_pasv($conn, true);

            return $conn;
        } catch (\Exception $e) {
            Log::channel('nfe')->error('Erro na conexão FTP: '.$e->getMessage());

            return false;
        }
    }
}

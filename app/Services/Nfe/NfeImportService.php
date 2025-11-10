<?php

namespace App\Services\Nfe;

use App\Services\Nfe\Contracts\NfeImporterInterface;
use App\Services\Nfe\Contracts\NfeProcessorInterface;
use App\Jobs\ProcessNfeFile;
use App\Models\NfeCore;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class NfeImportService implements NfeImporterInterface
{
    private array $ftpConfig;
    private NfeProcessorInterface $processor;
    private array $statistics = [
        'downloaded' => 0,
        'queued' => 0,
        'processed' => 0,
        'failed' => 0,
        'already_exists' => 0,
    ];

    /**
     * Cria uma nova instância do serviço de importação.
     *
     * @param array $ftpConfig
     * @param NfeProcessorInterface $processor
     * @throws InvalidArgumentException Se a configuração FTP estiver incompleta
     */
    public function __construct(array $ftpConfig, NfeProcessorInterface $processor)
    {
        $this->ftpConfig = $this->validateFtpConfig($ftpConfig);
        $this->processor = $processor;

        // Garante que os diretórios necessários existam
        $this->createRequiredDirectories();
    }

    /**
     * Inicializa e executa o serviço de importação
     *
     * @return array Estatísticas do processo de importação
     */
    public function execute(): array
    {
        try {
            Log::channel('nfe')->info('Iniciando o serviço de importação de NFe');

            // Separamos os passos do processo para evitar que um erro em um passo afete os outros
            $downloadedFiles = [];

            try {
                // Primeiro passo: download dos arquivos
                $downloadedFiles = $this->downloadFromFTP();
            } catch (\Exception $e) {
                // Registra erro mas continua o processo com os arquivos que já temos
                Log::channel('nfe')->error('Erro durante o download dos arquivos: ' . $e->getMessage());
                Log::channel('nfe')->error('Continuando com os arquivos já baixados (' . count($downloadedFiles) . ')');
            }

            // Se temos arquivos baixados, podemos prosseguir mesmo se houve erro no download
            if (!empty($downloadedFiles)) {
                try {
                    // Segundo passo: adicionar arquivos à fila
                    $this->queueFiles($downloadedFiles);
                } catch (\Exception $e) {
                    Log::channel('nfe')->error('Erro ao adicionar arquivos à fila: ' . $e->getMessage());
                }
            }

            try {
                // Terceiro passo: processar a fila
                $this->processQueue();
            } catch (\Exception $e) {
                Log::channel('nfe')->error('Erro ao processar a fila: ' . $e->getMessage());
            }

            Log::channel('nfe')->info('Processamento finalizado', $this->statistics);

            return [
                'success' => true,
                'statistics' => $this->statistics,
                'message' => 'Processamento finalizado com sucesso',
            ];
        } catch (\Exception $e) {
            Log::channel('nfe')->error('Erro crítico durante a execução: ' . $e->getMessage());
            Log::channel('nfe')->debug('Stack Trace: ' . $e->getTraceAsString());

            return [
                'success' => false,
                'message' => 'Erro durante a execução: ' . $e->getMessage(),
                'statistics' => $this->statistics,
            ];
        }
    }

    /**
     * Valida a configuração do FTP
     *
     * @param array $config
     * @return array
     * @throws InvalidArgumentException Se a configuração estiver incompleta
     */
    private function validateFtpConfig(array $config): array
    {
        $requiredFields = ['host', 'username', 'password', 'port'];

        foreach ($requiredFields as $field) {
            if (!isset($config[$field])) {
                throw new InvalidArgumentException("Campo obrigatório ausente na configuração FTP: $field");
            }
        }

        return $config;
    }

    /**
     * Cria os diretórios necessários para o funcionamento do serviço
     */
    private function createRequiredDirectories(): void
    {
        $directories = [
            config('nfe-import.directories.queue'),
            config('nfe-import.directories.processing'),
            config('nfe-import.directories.failed'),
        ];

        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                Log::channel('nfe')->debug("Criando diretório: $dir");
                if (!mkdir($dir, 0755, true)) {
                    throw new RuntimeException("Não foi possível criar o diretório: $dir");
                }
            }
        }
    }

    /**
     * Estabelece conexão com o servidor FTP com manejo adequado de erros de SSL
     *
     */
    private function getFTPConnection()
    {
        try {
            // Tentar conexão FTPS primeiro
            $conn = @ftp_ssl_connect($this->ftpConfig['host'], $this->ftpConfig['port'], 60);

            if (!$conn) {
                // Fallback para FTP normal
                $conn = @ftp_connect($this->ftpConfig['host'], $this->ftpConfig['port'], 60);
            }

            if (!$conn) {
                Log::channel('nfe')->error('Não foi possível conectar ao FTP');
                return false;
            }

            // Login
            if (!@ftp_login($conn, $this->ftpConfig['username'], $this->ftpConfig['password'])) {
                @ftp_close($conn);
                Log::channel('nfe')->error('Falha no login FTP');
                return false;
            }

            // Modo passivo
            ftp_pasv($conn, true);

            Log::channel('nfe')->info('Conexão FTP estabelecida com sucesso');
            return $conn;
        } catch (\Exception $e) {
            Log::channel('nfe')->error('Erro na conexão FTP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fecha a conexão FTP com segurança para evitar erros SSL
     *
     */
    private function safelyCloseFTPConnection($conn): void
    {
        if (!$conn) {
            return;
        }

        try {
            @ftp_close($conn);
        } catch (\Exception $e) {
            // Ignora erros ao fechar conexão
            Log::channel('nfe')->debug('Erro ao fechar conexão FTP: ' . $e->getMessage());
        }
    }

    /**
     * Faz download dos arquivos do FTP com tratamento robusto de erros
     * e implementação de timeout mais longo para a listagem
     *
     * @return array Lista de arquivos baixados
     */
    private function downloadFromFTP(): array
    {
        $downloadedFiles = [];
        $batchSize = config('nfe-import.batch_size', 50);
        $conn = null;

        try {
            $conn = $this->getFTPConnection();
            Log::channel('nfe')->debug('Conexão FTP estabelecida');

            // Ajuste para usar o novo diretório "XMLs"
            $ftpDir = config('nfe-import.ftp.directory', 'XMLs');
            if (!@ftp_chdir($conn, $ftpDir)) {
                throw new RuntimeException("Erro ao acessar diretório FTP: $ftpDir");
            }

            // Aumentamos o time limit para 10 minutos, considerando os 29.000+ arquivos
            $originalTimeLimit = ini_get('max_execution_time');
            set_time_limit(600); // 10 minutos para a listagem

            Log::channel('nfe')->debug("Listando arquivos do diretório FTP ($ftpDir) - isso pode levar vários minutos com 29.000+ arquivos...");

            // Implementamos uma abordagem alternativa para lidar com muitos arquivos
            // Primeiro tentamos listar apenas alguns arquivos .xml como teste
            try {
                // Tentativa de listar apenas um subconjunto ou verificar se há como filtrar
                Log::channel('nfe')->debug("Tentando abordagem otimizada para listar arquivos...");

                // Algumas implementações FTP suportam curingas, tentamos primeiro
                $testFiles = @ftp_nlist($conn, "*.xml");

                // Se falhar, voltamos ao método padrão
                if ($testFiles === false) {
                    Log::channel('nfe')->warning("Filtragem de arquivos não suportada, tentando listagem completa");
                    $files = @ftp_nlist($conn, ".");
                } else {
                    $files = $testFiles;
                }
            } catch (\Exception $e) {
                Log::channel('nfe')->warning("Erro na abordagem otimizada: " . $e->getMessage());
                // Voltamos ao método tradicional
                $files = @ftp_nlist($conn, ".");
            }

            // Restaura o time limit original
            set_time_limit($originalTimeLimit);

            if ($files === false) {
                throw new RuntimeException('Erro ao listar arquivos do FTP');
            }

            Log::channel('nfe')->debug("Total de arquivos listados: " . count($files));

            // Filtramos apenas arquivos XML (caso a filtragem direta não tenha funcionado)
            $xmlFiles = array_filter($files, function ($file) {
                return strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'xml';
            });

            Log::channel('nfe')->debug("Total de arquivos XML identificados: " . count($xmlFiles));

            // OTIMIZAÇÃO: Limita o número de arquivos por execução para evitar sobrecarga
            // $maxFilesPerRun = config('nfe-import.max_files_per_run', 100);
            // if (count($xmlFiles) > $maxFilesPerRun) {
            //     Log::channel('nfe')->info("Limitando processamento aos primeiros $maxFilesPerRun arquivos XML para evitar sobrecarga");
            //     $xmlFiles = array_slice($xmlFiles, 0, $maxFilesPerRun);
            // }

            $allNfeNumbers = array_map(function ($file) {
                return str_replace(['-nfe.xml', '-NFe.xml'], '', basename($file));
            }, $xmlFiles);

            $existingNFes = $this->checkExistingNFes($allNfeNumbers);
            $this->statistics['already_exists'] = count($existingNFes);

            $newFiles = array_filter($xmlFiles, function ($file) use ($existingNFes) {
                $number = str_replace(['-nfe.xml', '-NFe.xml'], '', basename($file));
                return !in_array($number, $existingNFes);
            });

            if (empty($newFiles)) {
                Log::channel('nfe')->info('Nenhum arquivo novo encontrado.');
                return [];
            }

            Log::channel('nfe')->info('Total de NFes novas para processar: ' . count($newFiles));

            $batches = array_chunk($newFiles, $batchSize);
            $totalBatches = count($batches);

            // Fechamos a conexão aqui para reconectar em cada lote
            $this->safelyCloseFTPConnection($conn);
            $conn = null;

            foreach ($batches as $batchNumber => $batchFiles) {
                try {
                    // Nova conexão para cada lote
                    $conn = $this->getFTPConnection();
                    if (!$conn || !@ftp_chdir($conn, $ftpDir)) {
                        Log::channel('nfe')->error("Falha ao conectar FTP para lote $batchNumber");
                        // Tenta reconectar na próxima iteração
                        continue;
                    }

                    Log::channel('nfe')->info(sprintf(
                        'Processando lote %d/%d com %d arquivos',
                        $batchNumber + 1,
                        $totalBatches,
                        count($batchFiles)
                    ));

                    foreach ($batchFiles as $file) {
                        $localFile = $this->getQueuePath(basename($file));

                        // Tenta baixar com três tentativas
                        $success = false;
                        $attempts = 0;

                        while (!$success && $attempts < 3) {
                            $attempts++;
                            $success = @ftp_get($conn, $localFile, $file, FTP_BINARY);

                            if (!$success && $attempts < 3) {
                                Log::channel('nfe')->warning("Tentativa $attempts falhou, tentando novamente em 1s");
                                sleep(1);
                            }
                        }

                        if ($success) {
                            $downloadedFiles[$localFile] = $file;
                            Log::channel('nfe')->debug('Baixado: ' . basename($file));
                            $this->statistics['downloaded']++;
                        } else {
                            Log::channel('nfe')->error('Erro ao baixar: ' . basename($file));
                        }

                        // Pequena pausa entre arquivos
                        usleep(100000);
                    }

                    // Fechamos a conexão de cada lote com segurança
                    $this->safelyCloseFTPConnection($conn);
                    $conn = null;

                    // Pausa entre lotes
                    sleep(1);
                } catch (\Exception $e) {
                    Log::channel('nfe')->error("Erro no lote $batchNumber: " . $e->getMessage());
                    // Sempre fechamos a conexão corretamente entre lotes
                    $this->safelyCloseFTPConnection($conn);
                    $conn = null;
                    // Continuamos com o próximo lote
                }
            }
        } catch (\Exception $e) {
            Log::channel('nfe')->error("Erro durante download do FTP: " . $e->getMessage());
            Log::channel('nfe')->debug("Stack trace: " . $e->getTraceAsString());
        } finally {
            // Garantimos que a conexão seja fechada corretamente sempre
            $this->safelyCloseFTPConnection($conn);
        }

        return $downloadedFiles;
    }

    /**
     * Verifica quais NFes já existem no banco de dados
     *
     * @param array $nfeNumbers
     * @return array
     */
    private function checkExistingNFes(array $nfeNumbers): array
    {
        if (empty($nfeNumbers)) {
            return [];
        }

        // Usando o Model NfeCore para verificar existência
        return NfeCore::whereIn('infnfe', $nfeNumbers)
            ->pluck('infnfe')
            ->toArray();
    }

    /**
     * Adiciona os arquivos baixados na fila de processamento
     *
     * @param array $downloadedFiles
     */
    private function queueFiles(array $downloadedFiles): void
    {
        foreach ($downloadedFiles as $localFile => $originalFile) {
            $meta = [
                'original_file' => $originalFile,
                'queued_at'     => now()->toDateTimeString(),
                'attempts'      => 0
            ];

            $metaFile = $localFile . '.meta';
            file_put_contents($metaFile, json_encode($meta));
            Log::channel('nfe')->info('Arquivo adicionado na fila: ' . basename($originalFile));
            $this->statistics['queued']++;
        }
    }

    /**
     * Processa os arquivos na fila
     *
     * @return array Estatísticas do processamento
     */
    public function processQueue(): array
    {
        $files = glob(config('nfe-import.directories.queue') . '/*.xml');

        // Filtra apenas arquivos válidos
        $validFiles = array_filter($files, function ($file) {
            return file_exists($file) && is_readable($file) && filesize($file) > 0;
        });

        Log::channel('nfe')->info('Arquivos na fila para processamento:', ['count' => count($validFiles)]);

        foreach ($validFiles as $file) {
            try {
                // Dispara um job para cada arquivo
                ProcessNfeFile::dispatch($file);
            } catch (\Exception $e) {
                Log::channel('nfe')->error("Erro ao agendar processamento do arquivo {$file}: " . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'processed' => $this->statistics['processed'],
            'failed' => $this->statistics['failed'],
            'queued' => count($validFiles)
        ];
    }

    /**
     * Processa um arquivo individual
     * (Mantido para compatibilidade, usado pelo Job)
     *
     * @param string $file
     * @return array
     */
    public function processFile(string $file): array
    {
        $metaFile = $file . '.meta';
        if (!file_exists($metaFile)) {
            Log::channel('nfe')->warning('Arquivo sem metadados: ' . $file);
            $this->statistics['failed']++;
            return [
                'success' => false,
                'error' => 'Arquivo sem metadados'
            ];
        }

        $meta = json_decode(file_get_contents($metaFile), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::channel('nfe')->error('Erro ao ler metadados do arquivo: ' . json_last_error_msg());
            $this->statistics['failed']++;
            return [
                'success' => false,
                'error' => 'Erro ao ler metadados: ' . json_last_error_msg()
            ];
        }

        $processingFile = $this->getProcessingPath(basename($file));
        $processingMetaFile = $processingFile . '.meta';

        try {
            $this->moveFile($file, $processingFile);
            $this->moveFile($metaFile, $processingMetaFile);

            Log::channel('nfe')->info('Processando arquivo: ' . basename($meta['original_file']));
            $this->processor->setCaminho($processingFile);
            $processorResult = $this->processor->save();

            // CORREÇÃO: Verificar se o retorno do processador possui a chave 'success'
            // Garantir que retornamos sempre um array com a chave 'success'
            $result = [];

            // Se o processador retornou um array com a chave 'success', usamos esse valor
            if (is_array($processorResult) && isset($processorResult['success'])) {
                $result = $processorResult;
            } elseif (is_bool($processorResult)) {
                $result = [
                    'success' => $processorResult,
                    'message' => $processorResult ? 'Processado com sucesso' : 'Falha no processamento'
                ];
            } elseif (is_array($processorResult)) {
                $result = array_merge([
                    'success' => false,
                    'error' => 'Formato de retorno inválido do processador'
                ], $processorResult);

                Log::channel('nfe')->warning("Processador retornou formato inválido: " . json_encode($processorResult));
            } else {
                $result = [
                    'success' => false,
                    'error' => 'Retorno inesperado do processador: ' . gettype($processorResult)
                ];

                Log::channel('nfe')->error("Tipo de retorno inesperado do processador: " . gettype($processorResult));
            }

            if ($result['success']) {
                $this->handleSuccessfulProcessing(
                    $processingFile,
                    $processingMetaFile,
                    basename($meta['original_file'])
                );
                $this->statistics['processed']++;
            } else {
                // Verifica se é o caso de "NFe já existe"
                if (isset($result['warning']) && strpos($result['warning'], 'NFe já existe') !== false) {
                    Log::channel('nfe')->info($result['warning']);
                    $this->handleSuccessfulProcessing(
                        $processingFile,
                        $processingMetaFile,
                        basename($meta['original_file'])
                    );
                    $this->statistics['already_exists']++;
                } else {
                    $errorMsg = $result['error'] ?? 'Erro desconhecido no processamento';
                    Log::channel('nfe')->error("Falha no processamento: " . $errorMsg);
                    throw new RuntimeException($errorMsg);
                }
            }

            return $result;
        } catch (\Exception $e) {
            Log::channel('nfe')->error("Erro detalhado: " . $e->getMessage());
            Log::channel('nfe')->debug("Stack trace: " . $e->getTraceAsString());
            $this->handleFailedProcessing($processingFile, $processingMetaFile, $meta, $e);
            $this->statistics['failed']++;

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lida com o processamento bem-sucedido de um arquivo
     */
    private function handleSuccessfulProcessing(
        string $processingFile,
        string $processingMetaFile,
        string $originalFile
    ): void {
        // Remove os arquivos
        $this->safeDeleteFile($processingFile);
        $this->safeDeleteFile($processingMetaFile);

        Log::channel('nfe')->info("Arquivo processado com sucesso: $originalFile");
    }

    /**
     * Lida com falhas no processamento de um arquivo
     */
    private function handleFailedProcessing(
        string $processingFile,
        string $processingMetaFile,
        array $meta,
        \Exception $error
    ): void {
        $attempts = ($meta['attempts'] ?? 0) + 1;
        $maxAttempts = config('nfe-import.max_attempts', 3);

        if ($attempts >= $maxAttempts) {
            $failedFile = $this->getFailedPath(basename($processingFile));
            $failedMetaFile = $failedFile . '.meta';

            $this->moveFile($processingFile, $failedFile);
            $meta['attempts'] = $attempts;
            $meta['last_error'] = $error->getMessage();
            $meta['failed_at'] = now()->toDateTimeString();
            file_put_contents($failedMetaFile, json_encode($meta));
            @unlink($processingMetaFile);

            Log::channel('nfe')
                ->error("Arquivo movido para falhas após $attempts tentativas: " . basename($meta['original_file']));
        } else {
            $queueFile = $this->getQueuePath(basename($processingFile));
            $queueMetaFile = $queueFile . '.meta';

            $this->moveFile($processingFile, $queueFile);
            $meta['attempts'] = $attempts;
            $meta['last_error'] = $error->getMessage();
            $meta['last_attempt'] = now()->toDateTimeString();
            file_put_contents($queueMetaFile, json_encode($meta));
            @unlink($processingMetaFile);

            Log::channel('nfe')->warning("Arquivo retornado para fila após erro: " . basename($meta['original_file']));
            Log::channel('nfe')->warning("Tentativa $attempts de $maxAttempts falhou. Erro: " . $error->getMessage());

            // Agenda nova tentativa com delay exponencial
            ProcessNfeFile::dispatch($queueFile)
                ->delay(now()->addSeconds(30 * $attempts))
                ->onQueue('nfe-retry');
        }
    }

    /**
     * Move um arquivo de forma segura
     *
     * @throws RuntimeException Se não for possível mover o arquivo
     */
    private function moveFile(string $source, string $destination): void
    {
        if (!rename($source, $destination)) {
            throw new RuntimeException("Não foi possível mover o arquivo: $source para $destination");
        }
    }

    /**
     * Exclui um arquivo de forma segura
     *
     * @param string $file O arquivo a ser excluído
     * @return bool Sucesso da operação
     */
    private function safeDeleteFile(string $file): bool
    {
        if (!file_exists($file)) {
            return true; // Já não existe
        }

        // Tenta excluir o arquivo e ignora erros
        $result = @unlink($file);

        if (!$result) {
            Log::channel('nfe')->warning("Falha ao excluir arquivo: " . basename($file));
        }

        return $result;
    }

    /**
     * Obtém o caminho completo para um arquivo na fila
     */
    private function getQueuePath(string $filename): string
    {
        return config('nfe-import.directories.queue') . '/' . $filename;
    }

    /**
     * Obtém o caminho completo para um arquivo em processamento
     */
    private function getProcessingPath(string $filename): string
    {
        return config('nfe-import.directories.processing') . '/' . $filename;
    }

    /**
     * Obtém o caminho completo para um arquivo com falha
     */
    private function getFailedPath(string $filename): string
    {
        return config('nfe-import.directories.failed') . '/' . $filename;
    }
}

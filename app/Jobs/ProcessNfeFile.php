<?php

namespace App\Jobs;

use App\Services\Nfe\Contracts\NfeImporterInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessNfeFile implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Número máximo de tentativas.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Timeout do job em segundos.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * O caminho do arquivo a ser processado.
     *
     * @var string
     */
    private $filePath;

    /**
     * Cria uma nova instância do job.
     *
     * @return void
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $this->onQueue('nfe-import');
    }

    /**
     * Chave única para o job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return md5($this->filePath);
    }

    /**
     * Move um arquivo para o diretório de falhas com tratamento adequado de erros.
     *
     * @param  string  $filePath  Caminho do arquivo
     * @param  array  $meta  Metadados do arquivo
     * @return bool Sucesso da operação
     */
    private function moveToFailedDirectory(string $filePath, array $meta = []): bool
    {
        try {
            // Definir caminhos
            $failedDir = config('nfe-import.directories.failed', storage_path('app/nfe/failed'));
            $failedFile = $failedDir.'/'.basename($filePath);
            $failedMetaFile = $failedFile.'.meta';
            $metaFilePath = $filePath.'.meta';

            // Garantir que o diretório de falhas existe
            if (! File::exists($failedDir)) {
                File::makeDirectory($failedDir, 0755, true);
            }

            // Atualizar metadados
            $meta['attempts'] = $this->attempts();
            $meta['failed_at'] = now()->toDateTimeString();

            // Mover o arquivo XML
            if (File::exists($filePath)) {
                if (! File::move($filePath, $failedFile)) {
                    Log::channel('nfe')->error("Falha ao mover arquivo para diretório de falhas: {$filePath}");

                    return false;
                }
            } else {
                Log::channel('nfe')->warning("Arquivo não encontrado para mover: {$filePath}");

                return false;
            }

            // Gravar arquivo de metadados atualizado
            File::put($failedMetaFile, json_encode($meta));

            // Remover o arquivo de metadados original se existir
            if (File::exists($metaFilePath)) {
                File::delete($metaFilePath);
            }

            Log::channel('nfe')->info('Arquivo movido com sucesso para o diretório de falhas: '.basename($filePath));

            return true;
        } catch (\Exception $e) {
            Log::channel('nfe')->error('Erro ao mover arquivo para diretório de falhas: '.$e->getMessage(), [
                'file' => $filePath,
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Executa o job.
     *
     * @return void
     */
    public function handle(NfeImporterInterface $importer)
    {
        $metaFilePath = $this->filePath.'.meta';
        $meta = [];

        Log::channel('nfe')->info('Iniciando processamento do arquivo: '.basename($this->filePath));

        // Verificar e carregar metadados
        if (File::exists($metaFilePath)) {
            $metaContent = File::get($metaFilePath);
            $meta = json_decode($metaContent, true) ?? [];

            // Registrar tentativa atual nos metadados
            $meta['attempts'] = ($meta['attempts'] ?? 0) + 1;
            File::put($metaFilePath, json_encode($meta));

            Log::channel('nfe')->info('Tentativa #'.$meta['attempts'].' para o arquivo: '.basename($this->filePath));
        } else {
            Log::channel('nfe')->warning('Arquivo de metadados não encontrado para: '.basename($this->filePath));

            // Criar metadados iniciais
            $meta = [
                'attempts' => 1,
                'original_file' => basename($this->filePath),
                'queued_at' => now()->toDateTimeString(),
            ];
            File::put($metaFilePath, json_encode($meta));
        }

        try {
            // Verificar se já atingiu o número máximo de tentativas nos metadados
            if (isset($meta['attempts']) && $meta['attempts'] > $this->tries) {
                Log::channel('nfe')->warning('Atingiu o máximo de tentativas ('.$meta['attempts'].') para o arquivo: '.basename($this->filePath));

                // Adicionar informação de erro se disponível
                if (! isset($meta['last_error'])) {
                    $meta['last_error'] = 'Atingiu o máximo de tentativas sem erro específico';
                }

                // Mover para a pasta de falhas
                $this->moveToFailedDirectory($this->filePath, $meta);

                return;
            }

            // Processar o arquivo
            $result = $importer->processFile($this->filePath);

            if (! $result['success']) {
                $errorMessage = $result['error'] ?? 'Erro desconhecido no processamento';
                Log::channel('nfe')->error('Falha ao processar arquivo: '.basename($this->filePath), [
                    'error' => $errorMessage,
                ]);

                // Atualizar metadados com o erro
                $meta['last_error'] = $errorMessage;
                File::put($metaFilePath, json_encode($meta));

                // Se o erro for fatal ou já atingiu o número máximo de tentativas, mover para falhas
                if ((isset($result['retry']) && $result['retry'] === false) || $this->attempts() >= $this->tries) {
                    Log::channel('nfe')->warning('Não serão feitas mais tentativas para este arquivo.');
                    $this->moveToFailedDirectory($this->filePath, $meta);

                    return;
                }

                throw new \Exception($errorMessage);
            }

            Log::channel('nfe')->info('Arquivo processado com sucesso: '.basename($this->filePath));

            // Limpar arquivos após processamento bem-sucedido
            if (File::exists($this->filePath)) {
                File::delete($this->filePath);
            }

            if (File::exists($metaFilePath)) {
                File::delete($metaFilePath);
            }
        } catch (\Exception $e) {
            $errorMessage = 'Exceção ao processar arquivo: '.$e->getMessage();
            Log::channel('nfe')->error($errorMessage);

            // Atualizar metadados com o erro
            $meta['last_error'] = $errorMessage;
            File::put($metaFilePath, json_encode($meta));

            // Se atingiu o número máximo de tentativas, mover para falhas
            if ($this->attempts() >= $this->tries) {
                Log::channel('nfe')->error('Número máximo de tentativas atingido para: '.basename($this->filePath));
                $this->moveToFailedDirectory($this->filePath, $meta);
            }

            throw $e;
        }
    }

    /**
     * Manipula falha do job.
     *
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $metaFilePath = $this->filePath.'.meta';
        $meta = [];

        if (File::exists($metaFilePath)) {
            $metaContent = File::get($metaFilePath);
            $meta = json_decode($metaContent, true) ?? [];
        }

        $meta['last_error'] = $exception->getMessage();

        Log::channel('nfe')->error('Job falhou definitivamente: '.basename($this->filePath), [
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Se ainda não foi movido para falhas, fazer isso agora
        if (File::exists($this->filePath)) {
            $this->moveToFailedDirectory($this->filePath, $meta);
        }
    }
}

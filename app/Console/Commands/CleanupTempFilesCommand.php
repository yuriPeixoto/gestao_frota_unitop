<?php

namespace App\Console\Commands;

use App\Services\SinistroDocumentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupTempFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sinistros:cleanup-temp-files {--hours=24 : Idade em horas para considerar um arquivo como antigo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpa arquivos temporários de upload de documentos de sinistros';

    /**
     * @var SinistroDocumentService
     */
    protected $documentService;

    /**
     * Create a new command instance.
     *
     * @param SinistroDocumentService $documentService
     * @return void
     */
    public function __construct(SinistroDocumentService $documentService)
    {
        parent::__construct();
        $this->documentService = $documentService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $hours = $this->option('hours');

        $this->info("Iniciando limpeza de arquivos temporários mais antigos que {$hours} horas...");

        try {
            $count = $this->documentService->cleanupTempFiles($hours);

            $this->info("Limpeza concluída: {$count} arquivo(s) removido(s)");
            Log::info("Tarefa agendada de limpeza de arquivos temporários: {$count} arquivo(s) removido(s)");

            return 0; // Sucesso
        } catch (\Exception $e) {
            $this->error("Erro durante a limpeza de arquivos temporários: " . $e->getMessage());
            Log::error("Erro na tarefa agendada de limpeza de arquivos temporários: " . $e->getMessage());

            return 1; // Erro
        }
    }
}

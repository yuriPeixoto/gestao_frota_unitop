<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ChecklistService;
use Illuminate\Support\Facades\Log;

class TestChecklistService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checklist:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Teste a conectividade com a API do Checklist Service';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testando conectividade com a API do Checklist...');

        // Mostrar configurações
        $this->info('Configurações:');
        $this->info('- Base URL: ' . config('services.checklist.base_url'));
        $this->info('- API Prefix: ' . config('services.checklist.api_prefix'));
        $this->info('- Timeout: ' . config('services.checklist.timeout'));
        $this->info('- Verify SSL: ' . (config('services.checklist.verify_ssl') ? 'true' : 'false'));
        $this->info('');

        try {
            $service = app(ChecklistService::class);

            // Tentar fazer um health check primeiro
            $this->info('1. Fazendo health check...');
            try {
                $result = $service->checkHealth();
                $this->info('✅ Health check bem-sucedido!');
            } catch (\Exception $e) {
                $this->warn('⚠️ Health check falhou, mas continuando...');
                $this->warn('Erro: ' . $e->getMessage());
            }

            // Agora testar a criação de checklist (que é o que estava falhando)
            $this->info('');
            $this->info('2. Testando criação de checklist...');

            $testData = $service->buildChecklistData(
                checklistTypeId: 1,
                title: 'Teste de Checklist - ' . now()->format('Y-m-d H:i:s'),
                description: 'Checklist de teste para verificar conectividade',
                entityType: 'test',
                entityId: 999,
                createdBy: 1,
                assignedTo: 1,
                dueDate: now()->addDays(7)->format('Y-m-d')
            );

            $this->info('Dados para teste: ' . json_encode($testData, JSON_PRETTY_PRINT));
            $this->info('URL que será chamada: ' . config('services.checklist.base_url') . '/' . config('services.checklist.api_prefix') . '/checklists');

            $result = $service->createChecklist($testData);

            $this->info('✅ Criação de checklist bem-sucedida!');
            $this->info('Resposta: ' . json_encode($result, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            $this->error('❌ Erro na conexão:');
            $this->error('Tipo: ' . get_class($e));
            $this->error('Mensagem: ' . $e->getMessage());

            // Se for um RequestException do Guzzle, mostrar mais detalhes
            if ($e instanceof \Illuminate\Http\Client\RequestException) {
                $response = $e->response;
                if ($response) {
                    $this->error('Status Code: ' . $response->status());
                    $this->error('Response Body: ' . $response->body());
                }
            }

            // Log detalhado para debugging
            Log::error('Teste ChecklistService falhou', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}

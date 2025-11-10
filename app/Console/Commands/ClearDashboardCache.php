<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearDashboardCache extends Command
{
    protected $signature = 'dashboard:clear-cache';
    protected $description = 'Limpa o cache do dashboard para forçar recalculo dos dados';

    public function handle()
    {
        $this->info('Limpando cache do dashboard...');
        
        // Limpar todos os caches relacionados ao dashboard
        $cacheKeys = [
            'dashboard_all_modules_*',
            'dashboard_abastecimentos_queries_*',
            'dashboard_vencimentario_card',
            'vencimentario_queries_*',
            'dashboard_compras_card',
            'dashboard_estoque_card',
            'dashboard_imobilizados_card',
            'dashboard_manutencao_card',
            'dashboard_pessoal_card',
            'dashboard_pneus_card',
            'dashboard_sinistros_card',
            'dashboard_veiculos_card',
            'tanques_baixo_estoque'
        ];

        foreach ($cacheKeys as $pattern) {
            // Para chaves com wildcard, precisamos usar cache tags ou flush geral
            if (str_contains($pattern, '*')) {
                // Remove o wildcard e busca chaves que começam com o padrão
                $prefix = str_replace('*', '', $pattern);
                Cache::flush(); // Em produção, use cache tags para ser mais específico
                break;
            } else {
                Cache::forget($pattern);
            }
        }

        $this->info('Cache do dashboard limpo com sucesso!');
        $this->info('O próximo carregamento do dashboard irá recalcular todos os dados.');
        
        return Command::SUCCESS;
    }
}
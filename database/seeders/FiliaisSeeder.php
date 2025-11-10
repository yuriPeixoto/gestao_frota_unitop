<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FiliaisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Iniciando migração de dados da v_filiais para tabela filiais...');

        try {
            // Buscar dados da view v_filiais
            $viewFiliais = DB::connection('pgsql')->table('v_filial')
                ->select('id', 'name')
                ->orderBy('id')
                ->get();

            if ($viewFiliais->isEmpty()) {
                $this->command->warn('Nenhum dado encontrado na view v_filial');
                return;
            }

            $this->command->info("Encontradas {$viewFiliais->count()} filiais na view");

            $insertedCount = 0;
            $skippedCount = 0;

            foreach ($viewFiliais as $viewFilial) {
                // Verificar se já existe na nova tabela
                $exists = DB::connection('pgsql')->table('filiais')
                    ->where('id', $viewFilial->id)
                    ->exists();

                if ($exists) {
                    $this->command->warn("Filial ID {$viewFilial->id} já existe - pulando");
                    $skippedCount++;
                    continue;
                }

                // Inserir na nova tabela mantendo o mesmo ID
                DB::connection('pgsql')->table('filiais')->insert([
                    'id' => $viewFilial->id,
                    'name' => $viewFilial->name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $insertedCount++;
                $this->command->info("Migrada: {$viewFilial->name} (ID: {$viewFilial->id})");
            }

            // Ajustar sequence do PostgreSQL para o próximo ID
            $maxId = DB::connection('pgsql')->table('filiais')->max('id');
            if ($maxId) {
                DB::statement("SELECT setval('filiais_id_seq', {$maxId})");
                $this->command->info("Sequence ajustada para próximo ID: " . ($maxId + 1));
            }

            $this->command->info("Migração concluída!");
            $this->command->info("Total inserido: {$insertedCount}");
            $this->command->info("Total pulado: {$skippedCount}");
        } catch (\Exception $e) {
            $this->command->error('Erro durante a migração: ' . $e->getMessage());
            Log::error('Erro na migração de filiais', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}

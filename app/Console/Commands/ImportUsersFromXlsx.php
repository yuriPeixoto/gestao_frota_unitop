<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class ImportUsersFromXlsx extends Command
{
    protected $signature = 'users:import-xlsx
                           {file=import.xlsx : Caminho para o arquivo XLSX}
                           {--dry-run : Simular importação sem salvar dados}
                           {--update-existing : Atualizar usuários existentes}
                           {--default-password=12345678 : Senha padrão para novos usuários}
                           {--show-mappings : Mostrar mapeamentos antes de importar}';

    protected $description = 'Importa usuários do Excel com dados da planilha e mapeamentos inteligentes';

    private $stats = [
        'processed' => 0,
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => 0,
        'mapping_errors' => 0
    ];

    private $mappingCache = [
        'departamentos' => [],
        'filiais' => [],
        'tipos_pessoal' => []
    ];

    public function handle()
    {
        $filePath = $this->argument('file');
        $isDryRun = $this->option('dry-run');
        $updateExisting = $this->option('update-existing');
        $defaultPassword = $this->option('default-password');
        $showMappings = $this->option('show-mappings');

        // Se apenas o nome do arquivo foi passado, assumir que está na raiz
        if (!str_contains($filePath, '/') && !str_contains($filePath, '\\')) {
            $filePath = base_path($filePath);
        }

        $this->info('🚀 Iniciando importação de usuários Carvalima');
        $this->info("📁 Arquivo: {$filePath}");
        $this->info("🔄 Modo: " . ($isDryRun ? 'SIMULAÇÃO' : 'EXECUÇÃO REAL'));
        $this->newLine();

        if (!file_exists($filePath)) {
            $this->error("❌ Arquivo não encontrado: {$filePath}");
            return 1;
        }

        try {
            // 1. Carregar mapeamentos do banco
            $this->loadMappings();

            if ($showMappings) {
                $this->showMappingsTable();
                if (!$this->confirm('Continuar com a importação?')) {
                    return 0;
                }
            }

            // 2. Carregar e processar Excel
            $this->info('📖 Carregando arquivo Excel...');
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray(null, true, true, true);

            if (count($data) <= 1) {
                $this->error('❌ Arquivo vazio ou apenas com cabeçalho');
                return 1;
            }

            // 3. Validar estrutura do Excel
            $headers = array_map('trim', $data[1]);
            if (!$this->validateExcelStructure($headers)) {
                return 1;
            }

            // 4. Processar registros
            if (!$isDryRun) {
                DB::beginTransaction();
            }

            $this->info("📊 Processando " . (count($data) - 1) . " usuários...");
            $progressBar = $this->output->createProgressBar(count($data) - 1);
            $progressBar->start();

            // Processar dados (pular cabeçalho)
            for ($i = 2; $i <= count($data); $i++) {
                if (isset($data[$i])) {
                    $this->processUser($data[$i], $isDryRun, $updateExisting, $defaultPassword);
                }
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            if (!$isDryRun && $this->stats['errors'] == 0) {
                DB::commit();
                $this->info('✅ Transação confirmada');
            } elseif (!$isDryRun) {
                DB::rollBack();
                $this->error('❌ Transação cancelada devido a erros');
            }

            $this->showFinalStats($isDryRun);
        } catch (\Exception $e) {
            if (!$isDryRun) {
                DB::rollBack();
            }

            $this->error("❌ Erro crítico: {$e->getMessage()}");
            Log::error('Erro na importação de usuários', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }

        return 0;
    }

    private function loadMappings(): void
    {
        $this->info('🗺️  Carregando mapeamentos do banco...');

        // Carregar departamentos
        $departamentos = DB::connection('pgsql')->table('departamento')->where('ativo', true)->get();
        foreach ($departamentos as $dept) {
            $key = mb_strtolower(trim($dept->descricao_departamento ?? ''), 'UTF-8');
            if (!empty($key)) {
                $this->mappingCache['departamentos'][$key] = $dept->id_departamento;
            }
        }

        // Carregar filiais
        $filiais = DB::connection('pgsql')->table('filiais')->get();
        foreach ($filiais as $filial) {
            $key = mb_strtolower(trim($filial->name ?? ''), 'UTF-8');
            if (!empty($key)) {
                $this->mappingCache['filiais'][$key] = $filial->id;
            }
        }

        // Carregar tipos de pessoal
        $tipos = DB::connection('pgsql')->table('tipopessoal')->where('is_ativo', true)->get();
        foreach ($tipos as $tipo) {
            $key = mb_strtolower(trim($tipo->descricao_tipo ?? ''), 'UTF-8');
            if (!empty($key)) {
                $this->mappingCache['tipos_pessoal'][$key] = $tipo->id_tipo_pessoal;
            }
        }

        $this->info("✅ Carregados: {$departamentos->count()} departamentos, {$filiais->count()} filiais, {$tipos->count()} cargos");
    }

    private function validateExcelStructure(array $headers): bool
    {
        $expectedHeaders = ['NOME', 'Matricula', 'EMAIL', 'DEPARTAMENTO', 'CARGO', 'FILIAL'];
        $missing = array_diff($expectedHeaders, $headers);

        if (!empty($missing)) {
            $this->error('❌ Cabeçalhos ausentes: ' . implode(', ', $missing));
            $this->info('Cabeçalhos encontrados: ' . implode(', ', $headers));
            return false;
        }

        return true;
    }

    private function processUser(array $row, bool $isDryRun, bool $updateExisting, string $defaultPassword): void
    {
        $this->stats['processed']++;

        try {
            // Extrair dados da linha
            $nome = trim($row['A'] ?? '');
            $matricula = $this->parseMatricula($row['B'] ?? null);
            $email = $this->parseEmail($row['C'] ?? '');
            $departamentoNome = trim($row['D'] ?? '');
            $cargoNome = trim($row['E'] ?? '');
            $filialNome = trim($row['F'] ?? '');

            // Validações básicas - nome é obrigatório
            if (empty($nome)) {
                $this->stats['errors']++;
                $this->warn("⚠️  Linha {$this->stats['processed']}: Nome vazio");
                return;
            }

            // Email ou matrícula deve existir para login
            if (empty($email) && is_null($matricula)) {
                $this->stats['errors']++;
                $this->warn("⚠️  Linha {$this->stats['processed']}: Usuário '{$nome}' sem email nem matrícula");
                return;
            }

            // Resolver mapeamentos
            $departamentoId = $this->resolveDepartamento($departamentoNome);
            $filialId = $this->resolveFilial($filialNome);
            $tipoId = $this->resolveTipoPessoal($cargoNome);

            // Verificar se usuário já existe
            $existingUser = $this->findExistingUser($email, $matricula);

            if ($existingUser) {
                if ($updateExisting) {
                    $this->updateExistingUser($existingUser, $nome, $matricula, $email, $departamentoId, $filialId, $tipoId, $isDryRun);
                    $this->stats['updated']++;
                } else {
                    $this->stats['skipped']++;
                    if ($this->stats['skipped'] <= 5) {
                        $this->warn("⏭️  Usuário já existe: {$email}");
                    }
                }
            } else {
                $this->createNewUser($nome, $matricula, $email, $departamentoId, $filialId, $tipoId, $defaultPassword, $isDryRun);
                $this->stats['created']++;
            }
        } catch (\Exception $e) {
            $this->stats['errors']++;
            $this->error("❌ Erro na linha {$this->stats['processed']}: {$e->getMessage()}");
        }
    }

    private function parseMatricula($value): ?int
    {
        if (is_null($value) || $value === '' || $value === '\\N') {
            return null;
        }

        // Converter para inteiro, se for numérico
        if (is_numeric($value)) {
            return (int) $value; // Agora suporta BIGINT, pode usar qualquer valor
        }

        return null;
    }

    private function parseEmail(?string $value): ?string
    {
        if (empty($value) || $value === '\\N' || $value === 'N/D') {
            return null;
        }

        $email = strtolower(trim($value));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $email;
    }

    private function findExistingUser(?string $email, ?int $matricula): ?User
    {
        $query = User::query();

        if ($email && $matricula) {
            return $query->where('email', $email)
                ->orWhere('matricula', $matricula)
                ->first();
        } elseif ($email) {
            return $query->where('email', $email)->first();
        } elseif ($matricula) {
            return $query->where('matricula', $matricula)->first();
        }

        return null;
    }

    private function resolveDepartamento(string $nome): ?int
    {
        if (empty($nome) || $nome === '\\N') {
            return null;
        }

        $key = mb_strtolower(trim($nome), 'UTF-8');

        // Busca exata
        if (isset($this->mappingCache['departamentos'][$key])) {
            return $this->mappingCache['departamentos'][$key];
        }

        // Mapeamentos especiais conhecidos
        $specialMappings = [
            'pa' => 13, // PA → Departamento Pessoal
            'frota' => 300, // Frota
            'qualidade' => 9, // Processos e Qualidade
            'administrativo' => 1, // ADMINISTRATIVO
            'comercial' => 2, // COMERCIAL
            'operacional' => 5, // Operacional
            'recursos humanos' => 12, // Recursos Humanos
            'tecnologia da informação' => 22, // TI
            'diretoria' => 19, // Diretoria
            'financeiro' => 17, // Financeiro
            'auditoria e gestão' => 11, // Auditoria e Gestão
            'suprimentos' => 14, // Suprimentos
        ];

        if (isset($specialMappings[$key])) {
            return $specialMappings[$key];
        }

        // Busca aproximada (contains)
        foreach ($this->mappingCache['departamentos'] as $bancoNome => $id) {
            if (str_contains($bancoNome, $key) || str_contains($key, $bancoNome)) {
                return $id;
            }
        }

        $this->stats['mapping_errors']++;
        $this->warn("⚠️  Departamento não encontrado: '{$nome}'");
        return null;
    }

    private function resolveFilial(string $nome): int
    {
        if (empty($nome) || $nome === '\\N') {
            return 1; // Default: Matriz
        }

        $key = mb_strtolower(trim($nome), 'UTF-8');

        // Busca exata
        if (isset($this->mappingCache['filiais'][$key])) {
            return $this->mappingCache['filiais'][$key];
        }

        // Mapeamentos especiais conhecidos
        $specialMappings = [
            'cuiabá' => 4,
            'curitiba' => 7,
            'matriz' => 1,
            'são paulo' => 3,
            'campo grande' => 2,
            'unidades' => 16,
            'sinop' => 11,
            'rondonópolis' => 8,
            'vilhena' => 6,
            'dourados' => 5,
            'joinville' => 10,
            'navegantes' => 9,
            'porto velho' => 17,
            'rio branco' => 21,
            'londrina' => 20,
        ];

        if (isset($specialMappings[$key])) {
            return $specialMappings[$key];
        }

        // Busca aproximada
        foreach ($this->mappingCache['filiais'] as $bancoNome => $id) {
            if (str_contains($bancoNome, $key) || str_contains($key, $bancoNome)) {
                return $id;
            }
        }

        $this->warn("⚠️  Filial não encontrada: '{$nome}' - usando Matriz");
        return 1; // Default: Matriz
    }

    private function resolveTipoPessoal(string $nome): ?int
    {
        if (empty($nome) || $nome === '\\N') {
            return null;
        }

        // Normalizar corretamente com suporte a UTF-8 e acentos
        $key = mb_strtolower(trim($nome), 'UTF-8');

        // Busca exata no cache do banco
        if (isset($this->mappingCache['tipos_pessoal'][$key])) {
            return $this->mappingCache['tipos_pessoal'][$key];
        }

        // Mapeamentos CORRETOS que você me passou
        $specialMappings = [
            'tecnico de segurança  do traba' => 40,  // → Técnico de Segurança, id 40
            'tecnico de segurança do traba' => 40,   // Variação sem espaços duplos
            'líder operacional' => 17,               // → LIDER, id 17
            'líder administrativo' => 17,            // → LIDER, id 17
            'fiscal de patio' => 18,                 // → Fiscal de Pátio, id 18
            'servicos gerais' => 25,                 // → Serviços Gerais, id 25
            'oficial de manutenção ii' => 31,        // → Manutenção, id 31

            // Outros mapeamentos conhecidos
            'auxiliar administrativo' => 23,
            'aprendiz de auxiliar administr' => 19,
            'executivo de contas' => 33,
            'coord. de atendeimento ao clie' => 27,
            'engenheiro de seguranca do tra' => 40,
            'analista administrativo' => 21,
            'assistente administrativo' => 23,
            'supervisor operacional' => 26,
            'supervisor(a) comercial' => 26,
            'gerente de filial' => 30,
            'gerente' => 30,
            'gerente de suprimentos' => 30,
            'eletricista de veiculo' => 102,
            'mecanico' => 100,
            'diretor' => 32,
            'analista de endomarketing plen' => 21,
        ];

        // Verificar se está no mapeamento especial
        if (isset($specialMappings[$key])) {
            return $specialMappings[$key];
        }

        // Busca por palavras-chave (fallback)
        $keywords = [
            'segurança' => 40,
            'líder' => 17,
            'lider' => 17,
            'analista' => 21,
            'assistente' => 23,
            'supervisor' => 26,
            'coordenador' => 27,
            'gerente' => 30,
            'diretor' => 32,
            'motorista' => 1,
            'mecânico' => 100,
            'mecanico' => 100,
            'borracheiro' => 103,
            'eletricista' => 102,
            'aprendiz' => 19,
            'manutenção' => 31,
            'oficial' => 31,
            'fiscal' => 18,
            'servicos' => 25,
        ];

        foreach ($keywords as $keyword => $id) {
            if (str_contains($key, $keyword)) {
                return $id;
            }
        }

        // Busca aproximada no cache do banco
        foreach ($this->mappingCache['tipos_pessoal'] as $bancoNome => $id) {
            if (str_contains($bancoNome, $key) || str_contains($key, $bancoNome)) {
                return $id;
            }
        }

        // Se chegou até aqui, não encontrou
        $this->stats['mapping_errors']++;
        $this->warn("⚠️  Cargo não encontrado: '{$nome}' (normalizado: '{$key}')");
        return null;
    }

    private function createNewUser(string $nome, ?int $matricula, ?string $email, ?int $departamentoId, int $filialId, ?int $tipoId, string $defaultPassword, bool $isDryRun): void
    {
        if ($isDryRun) {
            return;
        }

        $user = User::create([
            'name' => $nome,
            'email' => $email,
            'password' => $defaultPassword,
            'matricula' => $matricula,
            'filial_id' => $filialId,
            'departamento_id' => $departamentoId,
            'pessoal_id' => $tipoId,
            'is_ativo' => true,
            'has_password_updated' => false
        ]);

        // Associar à filial na tabela user_filial
        $user->filiais()->syncWithoutDetaching([$filialId]);
    }

    private function updateExistingUser(User $user, string $nome, ?int $matricula, ?string $email, ?int $departamentoId, int $filialId, ?int $tipoId, bool $isDryRun): void
    {
        if ($isDryRun) {
            return;
        }

        $user->update([
            'name' => $nome,
            'matricula' => $matricula ?? $user->matricula,
            'email' => $email ?? $user->email,
            'filial_id' => $filialId,
            'departamento_id' => $departamentoId ?? $user->departamento_id,
            'pessoal_id' => $tipoId ?? $user->pessoal_id,
        ]);

        // Atualizar associação de filial
        if (!$user->filiais()->where('filiais.id', $filialId)->exists()) {
            $user->filiais()->syncWithoutDetaching([$filialId]);
        }
    }

    private function showMappingsTable(): void
    {
        $this->newLine();
        $this->info('🗺️  MAPEAMENTOS CARREGADOS');
        $this->info('========================');

        $this->info("📊 Departamentos: " . count($this->mappingCache['departamentos']));
        $this->info("📊 Filiais: " . count($this->mappingCache['filiais']));
        $this->info("📊 Cargos: " . count($this->mappingCache['tipos_pessoal']));
        $this->newLine();
    }

    private function showFinalStats(bool $isDryRun): void
    {
        $this->newLine();
        $this->info('📊 RELATÓRIO FINAL DA IMPORTAÇÃO');
        $this->info('================================');

        if ($isDryRun) {
            $this->warn('⚠️  MODO SIMULAÇÃO - Nenhum dado foi alterado');
        }

        $this->table(
            ['Métrica', 'Quantidade'],
            [
                ['Usuários Processados', $this->stats['processed']],
                ['Usuários Criados', $this->stats['created']],
                ['Usuários Atualizados', $this->stats['updated']],
                ['Usuários Ignorados', $this->stats['skipped']],
                ['Erros de Processamento', $this->stats['errors']],
                ['Erros de Mapeamento', $this->stats['mapping_errors']]
            ]
        );

        if ($this->stats['mapping_errors'] > 0) {
            $this->warn("⚠️  {$this->stats['mapping_errors']} erros de mapeamento encontrados. Verifique os logs acima.");
        }

        if (!$isDryRun && $this->stats['created'] > 0) {
            $this->info("✅ {$this->stats['created']} usuários criados com senha padrão: {$this->option('default-password')}");
            $this->warn("🔐 IMPORTANTE: Instrua os usuários a alterarem suas senhas no primeiro login!");
        }

        if ($this->stats['errors'] > 0) {
            $this->error("❌ {$this->stats['errors']} erros críticos encontrados. Verifique os dados e tente novamente.");
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixNfeCode extends Command
{
    /**
     * O nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'nfe:fix-code {--dry-run : Apenas simula as correções sem aplicá-las}';

    /**
     * A descrição do comando.
     *
     * @var string
     */
    protected $description = 'Corrige problemas comuns de namespace e importações nos arquivos de NFe';

    /**
     * Classes comuns que podem estar faltando
     *
     * @var array
     */
    protected $commonImports = [
        // Básicas do PHP
        'RuntimeException' => 'RuntimeException',
        'InvalidArgumentException' => 'InvalidArgumentException',
        'Exception' => 'Exception',
        'SimpleXMLElement' => 'SimpleXMLElement',

        // Modelos
        'NfeCore' => 'App\\Models\\NfeCore',
        'NfeEmissor' => 'App\\Models\\NfeEmissor',
        'NfeDestinatario' => 'App\\Models\\NfeDestinatario',
        'NfeProduto' => 'App\\Models\\NfeProduto',
        'NfeTransportadora' => 'App\\Models\\NfeTransportadora',
        'NfeFatura' => 'App\\Models\\NfeFatura',
        'BaseNfeModel' => 'App\\Models\\BaseNfeModel',

        // Interfaces
        'NfeImporterInterface' => 'App\\Services\\Nfe\\Contracts\\NfeImporterInterface',
        'NfeProcessorInterface' => 'App\\Services\\Nfe\\Contracts\\NfeProcessorInterface',
        'NfePersistenceInterface' => 'App\\Services\\Nfe\\Contracts\\NfePersistenceInterface',

        // Serviços
        'NfeImportService' => 'App\\Services\\Nfe\\NfeImportService',
        'NfeProcessor' => 'App\\Services\\Nfe\\NfeProcessor',
        'NfePersistence' => 'App\\Services\\Nfe\\NfePersistence',
        'EmailSanitizerTrait' => 'App\\Services\\Nfe\\Traits\\EmailSanitizerTrait',

        // Jobs
        'ProcessNfeFile' => 'App\\Jobs\\ProcessNfeFile',

        // Laravel
        'Log' => 'Illuminate\\Support\\Facades\\Log',
        'Storage' => 'Illuminate\\Support\\Facades\\Storage',
        'DB' => 'Illuminate\\Support\\Facades\\DB',
        'Cache' => 'Illuminate\\Support\\Facades\\Cache'
    ];

    /**
     * Caminhos dos arquivos para verificar.
     *
     * @var array
     */
    protected $paths = [
        'app/Models',
        'app/Services/Nfe',
        'app/Jobs',
        'app/Console/Commands',
        'app/Providers'
    ];

    /**
     * Executa o comando.
     *
     * @return mixed
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info("Executando em modo de simulação (dry-run). Nenhuma alteração será feita.");
        }

        $files = $this->collectPhpFiles();
        $this->info("Encontrados " . count($files) . " arquivos PHP para verificar.");

        $changedFiles = 0;

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $originalContent = $content;

            // Aplica as correções
            $content = $this->fixNamespace($file, $content);
            $content = $this->fixMissingImports($file, $content);
            $content = $this->fixDuplicateImports($content);
            $content = $this->organizeImports($content);

            // Verifica se houve alterações
            if ($content !== $originalContent) {
                $changedFiles++;

                if ($dryRun) {
                    $this->info("Alterações necessárias em: " . $file);
                    $this->printDiff($originalContent, $content);
                } else {
                    file_put_contents($file, $content);
                    $this->info("Corrigido: " . $file);
                }
            }
        }

        if ($changedFiles === 0) {
            $this->info("Nenhum arquivo precisou de correções!");
        } else {
            $this->info("Total de arquivos corrigidos: " . $changedFiles);

            if ($dryRun) {
                $this->comment("Execute novamente sem a opção --dry-run para aplicar as correções.");
            }
        }

        return 0;
    }

    /**
     * Coleta todos os arquivos PHP nos caminhos especificados.
     *
     * @return array
     */
    protected function collectPhpFiles()
    {
        $files = [];

        foreach ($this->paths as $path) {
            $fullPath = base_path($path);

            if (File::isDirectory($fullPath)) {
                $dirFiles = File::glob($fullPath . '/*.php');
                $files = array_merge($files, $dirFiles);

                // Também verifica subdiretórios
                $subdirs = File::directories($fullPath);
                foreach ($subdirs as $subdir) {
                    $subdirFiles = File::glob($subdir . '/*.php');
                    $files = array_merge($files, $subdirFiles);
                }
            } elseif (File::isFile($fullPath) && File::extension($fullPath) === 'php') {
                $files[] = $fullPath;
            }
        }

        return $files;
    }

    /**
     * Corrige o namespace do arquivo com base no seu caminho.
     *
     * @param string $filePath
     * @param string $content
     * @return string
     */
    protected function fixNamespace($filePath, $content)
    {
        // Obtém o namespace esperado com base no caminho do arquivo
        $expectedNamespace = $this->getExpectedNamespace($filePath);
        if (!$expectedNamespace) {
            return $content;
        }

        // Verifica se já existe uma declaração de namespace
        if (preg_match('/namespace\s+([^;]+);/i', $content, $matches)) {
            $currentNamespace = trim($matches[1]);

            // Se o namespace estiver incorreto, corrige-o
            if ($currentNamespace !== $expectedNamespace) {
                $content = str_replace(
                    "namespace {$currentNamespace};",
                    "namespace {$expectedNamespace};",
                    $content
                );
            }
        } else {
            // Adiciona o namespace se não existir
            // Procura a posição após o <?php
            $phpPos = strpos($content, '<?php');
            if ($phpPos !== false) {
                $insertPos = $phpPos + 5;
                while (ctype_space($content[$insertPos] ?? '')) {
                    $insertPos++;
                }

                $namespaceDeclaration = "\n\nnamespace {$expectedNamespace};\n";

                $content = substr($content, 0, $insertPos) . $namespaceDeclaration . substr($content, $insertPos);
            }
        }

        return $content;
    }

    /**
     * Adiciona as importações necessárias com base no conteúdo.
     *
     * @param string $filePath
     * @param string $content
     * @return string
     */
    protected function fixMissingImports($filePath, $content)
    {
        // Extrai o namespace atual do arquivo
        $currentNamespace = '';
        if (preg_match('/namespace\s+([^;]+);/i', $content, $matches)) {
            $currentNamespace = trim($matches[1]);
        }

        // Extrai as importações existentes
        $existingImports = [];
        preg_match_all('/use\s+([^;]+);/i', $content, $matches);
        if (isset($matches[1])) {
            foreach ($matches[1] as $match) {
                // Trata imports com alias
                if (strpos($match, ' as ') !== false) {
                    list($import,) = explode(' as ', $match);
                    $existingImports[] = trim($import);
                } else {
                    $existingImports[] = trim($match);
                }
            }
        }

        // Analisa o conteúdo para encontrar referências a classes
        $missingImports = [];
        foreach ($this->commonImports as $shortName => $fqcn) {
            // Evita importar classes do mesmo namespace
            if ($this->isInSameNamespace($fqcn, $currentNamespace)) {
                continue;
            }

            // Verifica se a classe é referenciada mas não está importada
            if ($this->needsImport($content, $shortName, $existingImports, $currentNamespace)) {
                $missingImports[] = $fqcn;
            }
        }

        if (empty($missingImports)) {
            return $content;
        }

        // Adiciona as importações faltantes após as existentes
        $lastImportPos = $this->findLastImportPosition($content);
        if ($lastImportPos !== false) {
            $importBlock = "";

            foreach ($missingImports as $import) {
                $importBlock .= "use {$import};\n";
            }

            $content = substr_replace($content, $importBlock, $lastImportPos, 0);
        } else {
            // Se não existem imports, adiciona após o namespace
            $namespacePos = strpos($content, 'namespace');
            if ($namespacePos !== false) {
                $semicolonPos = strpos($content, ';', $namespacePos);
                if ($semicolonPos !== false) {
                    $importBlock = "\n\n";

                    foreach ($missingImports as $import) {
                        $importBlock .= "use {$import};\n";
                    }

                    $content = substr_replace($content, $importBlock, $semicolonPos + 1, 0);
                }
            }
        }

        return $content;
    }

    /**
     * Encontra a posição após o último import.
     *
     * @param string $content
     * @return int|false
     */
    protected function findLastImportPosition($content)
    {
        if (preg_match_all('/use\s+[^;]+;/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $lastMatch = end($matches[0]);
            return $lastMatch[1] + strlen($lastMatch[0]);
        }

        return false;
    }

    /**
     * Verifica se uma classe está em um namespace.
     *
     * @param string $fqcn
     * @param string $namespace
     * @return bool
     */
    protected function isInSameNamespace($fqcn, $namespace)
    {
        if (empty($namespace)) {
            return false;
        }

        $parts = explode('\\', $fqcn);
        array_pop($parts); // Remove o nome da classe
        $classNamespace = implode('\\', $parts);

        return $classNamespace === $namespace;
    }

    /**
     * Verifica se uma classe precisa ser importada.
     *
     * @param string $content
     * @param string $shortName
     * @param array $existingImports
     * @param string $currentNamespace
     * @return bool
     */
    protected function needsImport($content, $shortName, $existingImports, $currentNamespace)
    {
        // Ignora classes básicas do PHP que não precisam de importação
        $basicClasses = ['self', 'static', 'parent', 'array', 'bool', 'callable', 'float', 'int', 'string', 'void', 'null', 'object'];
        if (in_array($shortName, $basicClasses)) {
            return false;
        }

        // Verifica se a classe já está importada
        $fqcn = $this->commonImports[$shortName];
        foreach ($existingImports as $import) {
            if ($import === $fqcn) {
                return false;
            }
        }

        // Padrões para encontrar referências à classe
        $patterns = [
            "/\\b{$shortName}\\b/", // Uso direto (NfeCore)
            "/new\\s+{$shortName}\\s*[({]/", // new NfeCore(
            "/extends\\s+{$shortName}\\b/", // extends NfeCore
            "/implements\\s+{$shortName}\\b/", // implements NfeInterface
            "/catch\\s*\\(\\s*{$shortName}\\s+/", // catch (RuntimeException
            "/{$shortName}::/", // NfeCore::
            "/instanceof\\s+{$shortName}\\b/" // instanceof NfeCore
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        // Casos específicos para alguns tipos comuns
        if ($shortName === 'Log' && strpos($content, 'Log::') !== false) {
            return true;
        }

        if ($shortName === 'DB' && strpos($content, 'DB::') !== false) {
            return true;
        }

        if ($shortName === 'Storage' && strpos($content, 'Storage::') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Remove importações duplicadas.
     *
     * @param string $content
     * @return string
     */
    protected function fixDuplicateImports($content)
    {
        $imports = [];
        $pattern = '/use\s+([^;]+);/i';

        if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            // Mapeamento de posição inicial e tamanho de cada statement use
            $positions = [];
            $seen = [];

            foreach ($matches[0] as $index => $match) {
                $import = trim($matches[1][$index][0]);
                $fullImport = $match[0];
                $startPos = $match[1];

                if (in_array($import, $seen)) {
                    // Se já vimos este import, marque para remoção
                    $positions[] = [
                        'start' => $startPos,
                        'length' => strlen($fullImport),
                        'remove' => true
                    ];
                } else {
                    $seen[] = $import;
                    $positions[] = [
                        'start' => $startPos,
                        'length' => strlen($fullImport),
                        'remove' => false
                    ];
                }
            }

            // Remove as duplicatas de trás para frente para evitar deslocamentos
            usort($positions, function ($a, $b) {
                return $b['start'] - $a['start'];
            });

            foreach ($positions as $pos) {
                if ($pos['remove']) {
                    $content = substr_replace(
                        $content,
                        '',
                        $pos['start'],
                        $pos['length']
                    );

                    // Remove também a linha vazia resultante
                    $content = preg_replace("/\n\n\n/", "\n\n", $content);
                }
            }
        }

        return $content;
    }

    /**
     * Organiza as importações em ordem alfabética.
     *
     * @param string $content
     * @return string
     */
    protected function organizeImports($content)
    {
        $imports = [];
        $pattern = '/use\s+([^;]+);/i';

        if (preg_match_all($pattern, $content, $matches)) {
            // Extrai todas as importações
            foreach ($matches[1] as $import) {
                $imports[] = trim($import);
            }

            // Se temos pelo menos 2 importações, ordena-as
            if (count($imports) >= 2) {
                // Remove todas as importações do conteúdo
                $content = preg_replace($pattern, '', $content);

                // Ordena as importações
                sort($imports);

                // Localiza onde inserir as importações (após o namespace)
                $namespacePos = strpos($content, 'namespace');
                if ($namespacePos !== false) {
                    $semicolonPos = strpos($content, ';', $namespacePos);
                    if ($semicolonPos !== false) {
                        $importBlock = "\n\n";

                        foreach ($imports as $import) {
                            $importBlock .= "use {$import};\n";
                        }

                        $content = substr_replace($content, $importBlock, $semicolonPos + 1, 0);
                    }
                }

                // Remove espaços em branco extras
                $content = preg_replace('/\n{3,}/', "\n\n", $content);
            }
        }

        return $content;
    }

    /**
     * Determina o namespace esperado com base no caminho do arquivo.
     *
     * @param string $filePath
     * @return string|null
     */
    protected function getExpectedNamespace($filePath)
    {
        $relativePath = $this->getRelativePath($filePath);

        $namespaceMap = [
            'app/Models/' => 'App\\Models',
            'app/Services/Nfe/' => 'App\\Services\\Nfe',
            'app/Services/Nfe/Contracts/' => 'App\\Services\\Nfe\\Contracts',
            'app/Services/Nfe/Traits/' => 'App\\Services\\Nfe\\Traits',
            'app/Jobs/' => 'App\\Jobs',
            'app/Providers/' => 'App\\Providers',
            'app/Console/Commands/' => 'App\\Console\\Commands',
            'app/Http/Controllers/' => 'App\\Http\\Controllers'
        ];

        foreach ($namespaceMap as $path => $namespace) {
            if (strpos($relativePath, $path) === 0) {
                // Adiciona eventuais subdiretórios ao namespace
                $subPath = substr($relativePath, strlen($path));
                $subPath = pathinfo($subPath, PATHINFO_DIRNAME);

                if ($subPath !== '.' && !empty($subPath)) {
                    $subNamespace = str_replace('/', '\\', $subPath);
                    return $namespace . '\\' . $subNamespace;
                }

                return $namespace;
            }
        }

        return null;
    }

    /**
     * Obtém o caminho relativo ao diretório base.
     *
     * @param string $filePath
     * @return string
     */
    protected function getRelativePath($filePath)
    {
        $basePath = base_path();

        if (strpos($filePath, $basePath) === 0) {
            return substr($filePath, strlen($basePath) + 1);
        }

        return $filePath;
    }

    /**
     * Exibe as diferenças entre duas strings.
     *
     * @param string $old
     * @param string $new
     */
    protected function printDiff($old, $new)
    {
        $oldLines = explode("\n", $old);
        $newLines = explode("\n", $new);

        $diff = new \SebastianBergmann\Diff\Differ;
        $diffResult = $diff->diffLines($old, $new);

        $lines = explode("\n", $diffResult);

        foreach ($lines as $line) {
            if (strpos($line, '+') === 0) {
                $this->line("<fg=green>{$line}</>");
            } elseif (strpos($line, '-') === 0) {
                $this->line("<fg=red>{$line}</>");
            } else {
                $this->line($line);
            }
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

class ValidateNamespaces extends Command
{
    /**
     * O nome e assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'namespace:validate {path? : Caminho específico para validar}';

    /**
     * A descrição do comando.
     *
     * @var string
     */
    protected $description = 'Valida namespaces e importações em arquivos PHP';

    /**
     * Mapeamento de diretórios para namespaces.
     *
     * @var array
     */
    protected $namespaceMap = [
        'app/Models' => 'App\\Models',
        'app/Services' => 'App\\Services',
        'app/Services/Nfe' => 'App\\Services\\Nfe',
        'app/Services/Nfe/Contracts' => 'App\\Services\\Nfe\\Contracts',
        'app/Services/Nfe/Traits' => 'App\\Services\\Nfe\\Traits',
        'app/Providers' => 'App\\Providers',
        'app/Jobs' => 'App\\Jobs',
        'app/Console/Commands' => 'App\\Console\\Commands',
        'app/Http/Controllers' => 'App\\Http\\Controllers',
    ];

    /**
     * Classes comuns que podem estar faltando
     *
     * @var array
     */
    protected $commonClasses = [
        'App\\Models\\NfeCore',
        'App\\Models\\NfeEmissor',
        'App\\Models\\NfeDestinatario',
        'App\\Models\\NfeProduto',
        'App\\Models\\NfeTransportadora',
        'App\\Models\\NfeFatura',
        'App\\Services\\Nfe\\Contracts\\NfeImporterInterface',
        'App\\Services\\Nfe\\Contracts\\NfeProcessorInterface',
        'App\\Services\\Nfe\\Contracts\\NfePersistenceInterface',
        'App\\Services\\Nfe\\NfeImportService',
        'App\\Services\\Nfe\\NfeProcessor',
        'App\\Services\\Nfe\\NfePersistence',
        'App\\Jobs\\ProcessNfeFile',
        'RuntimeException',
        'InvalidArgumentException',
        'Exception',
        'SimpleXMLElement',
    ];

    /**
     * Executa o comando.
     *
     * @return int
     */
    public function handle()
    {
        $path = $this->argument('path') ?? app_path();
        $this->info("Validando namespaces e importações em: {$path}");

        $files = $this->getPhpFiles($path);
        $this->info("Encontrados {$files->count()} arquivos PHP para validar.");

        $errors = [];
        $warnings = [];

        foreach ($files as $file) {
            $result = $this->validateFile($file);

            if (!empty($result['errors'])) {
                $errors[$file->getRealPath()] = $result['errors'];
            }

            if (!empty($result['warnings'])) {
                $warnings[$file->getRealPath()] = $result['warnings'];
            }
        }

        // Exibe erros
        if (!empty($errors)) {
            $this->error("Encontrados " . count($errors) . " arquivos com erros:");
            foreach ($errors as $file => $fileErrors) {
                $this->line("\n<fg=red>Arquivo: " . basename($file) . "</>");
                foreach ($fileErrors as $error) {
                    $this->line("  - {$error}");
                }
            }
        } else {
            $this->info("Nenhum erro de namespace encontrado! 🎉");
        }

        // Exibe avisos
        if (!empty($warnings)) {
            $this->warn("\nEncontrados " . count($warnings) . " arquivos com possíveis problemas:");
            foreach ($warnings as $file => $fileWarnings) {
                $this->line("\n<fg=yellow>Arquivo: " . basename($file) . "</>");
                foreach ($fileWarnings as $warning) {
                    $this->line("  - {$warning}");
                }
            }
        }

        return empty($errors) ? 0 : 1;
    }

    /**
     * Obtém todos os arquivos PHP no caminho especificado.
     *
     * @param string $path
     * @return \Illuminate\Support\Collection
     */
    protected function getPhpFiles($path)
    {
        return collect(File::allFiles($path))
            ->filter(function (SplFileInfo $file) {
                return $file->getExtension() === 'php';
            });
    }

    /**
     * Valida o namespace e importações de um arquivo.
     *
     * @param SplFileInfo $file
     * @return array
     */
    protected function validateFile(SplFileInfo $file)
    {
        $result = [
            'errors' => [],
            'warnings' => []
        ];

        $content = $file->getContents();
        $relativePath = $this->getRelativePath($file->getRealPath());

        // Verifica declaração de namespace
        $expectedNamespace = $this->detectExpectedNamespace($relativePath);
        $declaredNamespace = $this->extractNamespace($content);

        if ($expectedNamespace && $declaredNamespace && $expectedNamespace !== $declaredNamespace) {
            $result['errors'][] = "Namespace incorreto: esperado '{$expectedNamespace}', encontrado '{$declaredNamespace}'";
        } elseif ($expectedNamespace && !$declaredNamespace) {
            $result['errors'][] = "Namespace não declarado: esperado '{$expectedNamespace}'";
        }

        // Verifica importações
        $usedClasses = $this->extractUsedClasses($content);
        $declaredClasses = $this->extractDeclaredClasses($content, $declaredNamespace);

        // Verifica referências não importadas
        $referencedClasses = $this->extractReferencedClasses($content);

        foreach ($referencedClasses as $referencedClass) {
            // Ignora classes no mesmo namespace e classes padrão do PHP
            if (
                $this->isInSameNamespace($referencedClass, $declaredNamespace, $declaredClasses) ||
                $this->isBuiltinClass($referencedClass)
            ) {
                continue;
            }

            // Verifica se a classe foi importada
            $foundImport = false;
            foreach ($usedClasses as $usedClass) {
                if ($this->isMatchingImport($usedClass, $referencedClass)) {
                    $foundImport = true;
                    break;
                }
            }

            if (!$foundImport) {
                $result['warnings'][] = "Possível classe não importada: '{$referencedClass}'";
            }
        }

        // Verifica importações não utilizadas
        foreach ($usedClasses as $usedClass) {
            $className = $this->getClassNameFromFQCN($usedClass);
            $found = false;

            // Procura referências à classe no código
            foreach ($referencedClasses as $referencedClass) {
                if (
                    $referencedClass === $className ||
                    $referencedClass === $usedClass
                ) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $result['warnings'][] = "Possível importação não utilizada: '{$usedClass}'";
            }
        }

        // Verifica importações comuns que podem estar faltando
        $this->checkForMissingCommonImports($content, $usedClasses, $result);

        return $result;
    }

    /**
     * Extrai o namespace declarado no arquivo.
     *
     * @param string $content
     * @return string|null
     */
    protected function extractNamespace($content)
    {
        if (preg_match('/namespace\s+([^;]+);/i', $content, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Extrai as classes importadas (use statements).
     *
     * @param string $content
     * @return array
     */
    protected function extractUsedClasses($content)
    {
        $classes = [];

        preg_match_all('/use\s+([^;]+);/i', $content, $matches);

        if (isset($matches[1])) {
            foreach ($matches[1] as $match) {
                // Trata imports com alias (use Namespace\Class as Alias)
                if (strpos($match, ' as ') !== false) {
                    list($class,) = explode(' as ', $match);
                    $classes[] = trim($class);
                } else {
                    $classes[] = trim($match);
                }
            }
        }

        return $classes;
    }

    /**
     * Extrai as classes declaradas no arquivo.
     *
     * @param string $content
     * @param string|null $namespace
     * @return array
     */
    protected function extractDeclaredClasses($content, $namespace)
    {
        $classes = [];

        // Encontra todas as declarações class, interface, trait
        preg_match_all('/(class|interface|trait)\s+([a-zA-Z0-9_]+)/i', $content, $matches);

        if (isset($matches[2])) {
            foreach ($matches[2] as $className) {
                if ($namespace) {
                    $classes[] = $namespace . '\\' . $className;
                } else {
                    $classes[] = $className;
                }
            }
        }

        return $classes;
    }

    /**
     * Extrai as classes referenciadas no código (new Class, extends Class, etc).
     *
     * @param string $content
     * @return array
     */
    protected function extractReferencedClasses($content)
    {
        $classes = [];

        // Removes os comentários para evitar falsos positivos
        $contentWithoutComments = preg_replace('/(\/\/.*?$|\/\*[\s\S]*?\*\/)/m', '', $content);

        // Padrões para encontrar classes referenciadas
        $patterns = [
            '/new\s+([a-zA-Z0-9_\\\\]+)(?!\()/i',                    // new ClassName
            '/extends\s+([a-zA-Z0-9_\\\\]+)/i',                      // extends ClassName
            '/implements\s+([a-zA-Z0-9_\\\\,\s]+)/i',                // implements Interface1, Interface2
            '/instanceof\s+([a-zA-Z0-9_\\\\]+)/i',                   // instanceof ClassName
            '/::\s*class|([a-zA-Z0-9_\\\\]+)::class/i',              // ClassName::class
            '/catch\s*\(\s*([a-zA-Z0-9_\\\\]+)\s+/i',                // catch (ExceptionType
            '/(\$[a-zA-Z0-9_]+)\s+instanceof\s+([a-zA-Z0-9_\\\\]+)/i', // $var instanceof ClassName
            '/function\s+\([^)]*?([a-zA-Z0-9_\\\\]+)\s+\$[a-zA-Z0-9_]+/i', // function (TypeHint $param
            '/:\s*([a-zA-Z0-9_\\\\]+)(?:\s*\{|\s*$|\s*;)/i',         // ): ReturnType {
        ];

        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $contentWithoutComments, $matches);

            if (isset($matches[1])) {
                foreach ($matches[1] as $match) {
                    // Limpa espaços e quebras de linha
                    $match = trim($match);

                    // Para interfaces implementadas, separa a lista
                    if (strpos($match, ',') !== false) {
                        $interfaces = array_map('trim', explode(',', $match));
                        $classes = array_merge($classes, $interfaces);
                    } else {
                        $classes[] = $match;
                    }
                }
            }
        }

        // Filtra classes vazias e duplicadas
        return array_unique(array_filter($classes));
    }

    /**
     * Verifica se uma classe referenciada está no mesmo namespace.
     *
     * @param string $className
     * @param string|null $namespace
     * @param array $declaredClasses
     * @return bool
     */
    protected function isInSameNamespace($className, $namespace, $declaredClasses)
    {
        // Se não tem namespace ou é um nome completamente qualificado
        if (!$namespace || strpos($className, '\\') === 0) {
            return false;
        }

        // Verifica se é uma classe declarada no arquivo
        foreach ($declaredClasses as $declaredClass) {
            if ($this->getClassNameFromFQCN($declaredClass) === $className) {
                return true;
            }
        }

        // Verifica se o namespace já está incluído no nome da classe
        if (strpos($className, $namespace . '\\') === 0) {
            return true;
        }

        return false;
    }

    /**
     * Verifica se é uma classe interna do PHP.
     *
     * @param string $className
     * @return bool
     */
    protected function isBuiltinClass($className)
    {
        $builtinClasses = [
            'self',
            'static',
            'parent',
            'array',
            'bool',
            'callable',
            'float',
            'int',
            'string',
            'iterable',
            'object',
            'resource',
            'mixed',
            'void',
            'null',
            'true',
            'false',
            'stdClass',
            'Exception',
            'ErrorException',
            'DateTime',
            'DateTimeInterface',
            'DateTimeImmutable',
            'Closure',
            'Generator',
            'Error',
            'Throwable',
            'PDO',
            'SimpleXMLElement'
        ];

        return in_array($className, $builtinClasses);
    }

    /**
     * Verifica se uma importação corresponde a uma classe referenciada.
     *
     * @param string $import
     * @param string $reference
     * @return bool
     */
    protected function isMatchingImport($import, $reference)
    {
        // Caso exato
        if ($import === $reference) {
            return true;
        }

        // Caso da classe curta
        $importClassName = $this->getClassNameFromFQCN($import);
        if ($importClassName === $reference) {
            return true;
        }

        return false;
    }

    /**
     * Obtém o nome da classe de um FQCN.
     *
     * @param string $fqcn
     * @return string
     */
    protected function getClassNameFromFQCN($fqcn)
    {
        $parts = explode('\\', $fqcn);
        return end($parts);
    }

    /**
     * Detecta o namespace esperado com base no caminho do arquivo.
     *
     * @param string $relativePath
     * @return string|null
     */
    protected function detectExpectedNamespace($relativePath)
    {
        foreach ($this->namespaceMap as $directory => $namespace) {
            if (strpos($relativePath, $directory) === 0) {
                $subPath = substr($relativePath, strlen($directory));
                $subPath = rtrim(str_replace('/', '\\', $subPath), '.php');

                if (substr($subPath, 0, 1) === '\\') {
                    $subPath = substr($subPath, 1);
                }

                // Remove o nome do arquivo
                $subPathParts = explode('\\', $subPath);
                array_pop($subPathParts);
                $subPath = implode('\\', $subPathParts);

                return $namespace . ($subPath ? "\\{$subPath}" : '');
            }
        }

        return null;
    }

    /**
     * Obtém o caminho relativo do arquivo.
     *
     * @param string $path
     * @return string
     */
    protected function getRelativePath($path)
    {
        $basePath = base_path();

        if (strpos($path, $basePath) === 0) {
            return substr($path, strlen($basePath) + 1);
        }

        return $path;
    }

    /**
     * Verifica importações comuns que podem estar faltando.
     *
     * @param string $content
     * @param array $usedClasses
     * @param array &$result
     * @return void
     */
    protected function checkForMissingCommonImports($content, $usedClasses, &$result)
    {
        // Verifica importações dos modelos de NFe
        if (strpos($content, 'NfeCore') !== false && !$this->hasImport('App\\Models\\NfeCore', $usedClasses)) {
            $result['warnings'][] = "O arquivo usa 'NfeCore' mas não importa 'App\\Models\\NfeCore'";
        }

        // Verifica importações de exceções comuns
        if (strpos($content, 'throw new RuntimeException') !== false && !$this->hasImport('RuntimeException', $usedClasses)) {
            $result['warnings'][] = "O arquivo lança 'RuntimeException' mas não a importa";
        }

        if (strpos($content, 'throw new InvalidArgumentException') !== false && !$this->hasImport('InvalidArgumentException', $usedClasses)) {
            $result['warnings'][] = "O arquivo lança 'InvalidArgumentException' mas não a importa";
        }

        // Verifica importações de interfaces usadas
        if (strpos($content, 'implements NfeProcessorInterface') !== false && !$this->hasImport('App\\Services\\Nfe\\Contracts\\NfeProcessorInterface', $usedClasses)) {
            $result['warnings'][] = "O arquivo implementa 'NfeProcessorInterface' mas não importa a interface";
        }

        if (strpos($content, 'implements NfeImporterInterface') !== false && !$this->hasImport('App\\Services\\Nfe\\Contracts\\NfeImporterInterface', $usedClasses)) {
            $result['warnings'][] = "O arquivo implementa 'NfeImporterInterface' mas não importa a interface";
        }

        if (strpos($content, 'implements NfePersistenceInterface') !== false && !$this->hasImport('App\\Services\\Nfe\\Contracts\\NfePersistenceInterface', $usedClasses)) {
            $result['warnings'][] = "O arquivo implementa 'NfePersistenceInterface' mas não importa a interface";
        }

        // Verifica importações de classes frequentemente usadas
        if (strpos($content, 'Log::') !== false && !$this->hasImport('Illuminate\\Support\\Facades\\Log', $usedClasses)) {
            $result['warnings'][] = "O arquivo usa 'Log::' mas não importa 'Illuminate\\Support\\Facades\\Log'";
        }

        if (strpos($content, 'SimpleXMLElement') !== false && !$this->hasImport('SimpleXMLElement', $usedClasses)) {
            $result['warnings'][] = "O arquivo usa 'SimpleXMLElement' mas não a importa";
        }
    }

    /**
     * Verifica se uma classe está importada.
     *
     * @param string $class
     * @param array $imports
     * @return bool
     */
    protected function hasImport($class, $imports)
    {
        foreach ($imports as $import) {
            if ($import === $class || $this->getClassNameFromFQCN($import) === $class) {
                return true;
            }
        }

        return false;
    }
}

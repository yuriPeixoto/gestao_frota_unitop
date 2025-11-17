<?php

namespace App\Services;

use App\Modules\Configuracoes\Models\Permission;
use App\Modules\Configuracoes\Models\PermissionGroup;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class PermissionDiscoveryService
{
    protected const SYSTEM_GROUP = 'system';

    protected array $translations = [
        'models' => [
            'User' => 'Usuário',
            'Users' => 'Usuários',
            'Branch' => 'Filial',
            'Branches' => 'Filiais',
            'Role' => 'Cargo',
            'Roles' => 'Cargos',
            'Permission' => 'Permissão',
            'Permissions' => 'Sistema',
            'ActivityLog' => 'Log de atividades',
            'ActivityLogs' => 'Logs de atividades'
        ],
        'actions' => [
            'ver' => [
                'name' => 'Visualizar',
                'description' => 'visualizar'
            ],
            'criar' => [
                'name' => 'Criar',
                'description' => 'criar'
            ],
            'editar' => [
                'name' => 'Editar',
                'description' => 'editar'
            ],
            'excluir' => [
                'name' => 'Excluir',
                'description' => 'excluir'
            ],
            'gerenciar' => [
                'name' => 'Gerenciar',
                'description' => 'gerenciar'
            ]
        ],
        'groups' => [
            'others' => 'Sistema'
        ]
    ];

    protected array $groupMappings = [
        'User' => [
            'key'         => 'users',
            'name'        => 'Usuários',
            'description' => 'Permissões relacionadas a usuários'
        ],
        'Branch' => [
            'key'         => 'branches',
            'name'        => 'Filiais',
            'description' => 'Permissões relacionadas a filiais'
        ],
        'Role' => [
            'key'         => 'roles',
            'name'        => 'Cargos',
            'description' => 'Permissões relacionadas a cargos'
        ],
        'Permission' => [
            'key'         => self::SYSTEM_GROUP,
            'name'        => 'Sistema',
            'description' => 'Permissões do sistema'
        ],
        'ActivityLog' => [
            'key'         => 'activity_logs',
            'name'        => 'Logs de Atividades',
            'description' => 'Permissões relacionadas aos logs de atividades'
        ]
    ];

    protected array $excluded = [
        'controllers' => [
            'PermissionDiscoveryController',
            'PermissionGroupController',
        ],
        'models' => [
            'PermissionGroup',
            'PermissionDiscovery',
            'Address'
        ],
        'resources' => [
            'branches',
            'users',
            'roles',
            self::SYSTEM_GROUP,
            'permissions'
        ]
    ];

    protected array $methodMap = [
        'index' => 'ver',
        'show' => 'ver',
        'create' => 'criar',
        'store' => 'criar',
        'edit' => 'editar',
        'update' => 'editar',
        'destroy' => 'excluir'
    ];

    protected ?array $existingPermissions = null;

    public function discoverPermissions(): array
    {
        $discoveredPermissions = [];
        $existingPermissions = $this->getExistingPermissions();

        // Descobre permissões de controllers e models
        foreach (['getControllerPermissions', 'getModelPermissions'] as $method) {
            $sources = $method === 'getControllerPermissions' ? $this->getControllers() : $this->getModels();

            foreach ($sources as $source) {
                $permissions = $this->$method($source);
                $permissions = array_filter(
                    $permissions,
                    fn($permission, $slug) => !$this->permissionExists($slug, $permission['group_key']),
                    ARRAY_FILTER_USE_BOTH
                );

                $discoveredPermissions = array_merge($discoveredPermissions, $permissions);
            }
        }

        return $discoveredPermissions;
    }

    public function syncPermissions(): array
    {
        $discoveredPermissions = $this->discoverPermissions();
        $results = ['created' => [], 'existing' => [], 'errors' => []];
        $this->existingPermissions = null;

        foreach ($this->getAllGroupMappings() as $mapping) {
            try {
                $group = PermissionGroup::firstOrCreate(
                    ['name' => $mapping['name']],
                    [
                        'description' => $mapping['description'],
                        'created_at'  => now(),
                        'updated_at'  => now()
                    ]
                );

                foreach ($discoveredPermissions as $slug => $permission) {
                    if ($permission['group_key'] !== $mapping['key']) {
                        continue;
                    }

                    $created = Permission::firstOrCreate(
                        ['slug' => $slug],
                        [
                            'name' => $permission['name'],
                            'description' => $permission['description'],
                            'permission_group_id' => $group->id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );

                    $results[$created->wasRecentlyCreated ? 'created' : 'existing'][] = $slug;
                }
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'group' => $mapping['name'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    protected function getExistingPermissions(): array
    {
        if ($this->existingPermissions === null) {
            $this->existingPermissions = Permission::select('slug', 'name', 'permission_group_id')
                ->with('group:id,name')
                ->get()
                ->mapWithKeys(fn($permission) => [
                    $permission->slug => [
                        'name'       => $permission->name,
                        'group_id'   => $permission->permission_group_id,
                        'group_name' => $permission->group->name ?? null
                    ]
                ])
                ->toArray();
        }

        return $this->existingPermissions;
    }

    protected function getAllGroupMappings(): array
    {
        $mappings = $this->groupMappings;

        foreach ($this->getModels() as $modelClass) {
            $baseName = class_basename($modelClass);
            if (!isset($mappings[$baseName])) {
                $mappings[$baseName] = $this->generateGroupMapping($modelClass);
            }
        }

        return $mappings;
    }

    protected function getControllers(): array
    {
        return $this->getSourceFiles('Http/Controllers/Admin', function ($className) {
            $baseName = class_basename($className);
            return !in_array($baseName, $this->excluded['controllers']) &&
                   !$this->isExcludedResource($baseName);
        });
    }

    protected function getModels(): array
    {
        return $this->getSourceFiles('Models', function ($className) {
            $baseName = class_basename($className);
            return !in_array($baseName, $this->excluded['models']) &&
                   !$this->isExcludedResource(Str::singular($baseName));
        });
    }

    protected function getSourceFiles(string $path, callable $filter): array
    {
        $sources = [];
        $fullPath = app_path($path);

        if (File::exists($fullPath)) {
            foreach (File::allFiles($fullPath) as $file) {
                $className = str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    "App\\{$path}\\" . $file->getRelativePathname()
                );

                if (class_exists($className) && $filter($className)) {
                    $sources[] = $className;
                }
            }
        }

        return $sources;
    }

    protected function getControllerPermissions(string $controllerClass): array
    {
        $permissions = [];
        $reflection  = new ReflectionClass($controllerClass);
        $baseName    = str_replace('Controller', '', class_basename($controllerClass));

        // Pula se for um recurso excluído
        if ($this->isExcludedResource($baseName)) {
            return [];
        }

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (!isset($this->methodMap[$method->getName()])) {
                continue;
            }

            $action         = $this->methodMap[$method->getName()];
            $translatedName = $this->translateModelName($baseName, true);
            $slug           = "{$action}_{$this->generateSlug($baseName)}";

            $permissions[$slug] = $this->generatePermission(
                $action,
                $translatedName,
                $this->getGroupKeyForModel($baseName)
            );
        }

        return $permissions;
    }

    protected function getModelPermissions(string $modelClass): array
    {
        $permissions = [];
        $baseName    = class_basename($modelClass);

        // Pula se for um recurso excluído
        if ($this->isExcludedResource($baseName)) {
            return [];
        }

        $translatedName = $this->translateModelName($baseName, true);
        $groupKey       = $this->getGroupKeyForModel($baseName);

        foreach ($this->translations['actions'] as $action => $translation) {
            $slug = "{$action}_{$this->generateSlug($baseName)}";
            $permissions[$slug] = $this->generatePermission($action, $translatedName, $groupKey);
        }

        return $permissions;
    }

    protected function generatePermission(string $action, string $name, string $groupKey): array
    {
        $translation    = $this->translations['actions'][$action];
        $translatedName = mb_strtolower($name);

        return [
            'name'        => "{$translation['name']} {$name}",
            'slug'        => "{$action}_{$this->generateSlug($name)}",
            'description' => "Permite {$translation['description']} {$translatedName}",
            'group_key'   => $groupKey
        ];
    }

    protected function translateModelName(string $name, bool $plural = false): string
    {
        $key = $plural ? Str::plural($name) : $name;
        return $this->translations['models'][$key] ?? $this->generateFriendlyName($name);
    }

    protected function generateFriendlyName(string $name): string
    {
        if (isset($this->translations['models'][$name])) {
            return $this->translations['models'][$name];
        }

        $name = preg_replace('/(?<!^)[A-Z]/', ' $0', $name);
        $name = str_replace('_', ' ', $name);
        $name = preg_replace('/\s+(De|Da|Do|Dos|Das)\s+/i', ' de ', $name);

        return trim(ucwords(mb_strtolower($name)));
    }

    protected function generateSlug(string $name): string
    {
        // Remove acentos e caracteres especiais
        $name = preg_replace('/[áàãâä]/ui', 'a', $name);
        $name = preg_replace('/[éèêë]/ui', 'e', $name);
        $name = preg_replace('/[íìîï]/ui', 'i', $name);
        $name = preg_replace('/[óòõôö]/ui', 'o', $name);
        $name = preg_replace('/[úùûü]/ui', 'u', $name);
        $name = preg_replace('/[ç]/ui', 'c', $name);

        return Str::snake(Str::plural($name));
    }

    protected function isExcludedResource(string $name): bool
    {
        $name = strtolower($name);
        // Verifica também os group mappings
        foreach ($this->groupMappings as $key => $mapping) {
            if (Str::contains($name, strtolower($key))) {
                return true;
            }
        }
        // Verifica recursos excluídos
        foreach ($this->excluded['resources'] as $resource) {
            if (Str::contains($name, $resource)) {
                return true;
            }
        }
        return false;
    }

    protected function permissionExists(string $slug, string $groupKey): bool
    {
        $existingPermissions = $this->getExistingPermissions();

        if (!isset($existingPermissions[$slug])) {
            return false;
        }

        $group = PermissionGroup::where('name', $this->groupMappings[$groupKey]['name'] ?? '')
                               ->orWhere('name', ucfirst($groupKey))
                               ->first();

        return !$group || $existingPermissions[$slug]['group_id'] === $group->id;
    }

    protected function getGroupKeyForModel(string $baseName): string
    {
        // Verifica primeiro no mapeamento de grupos
        if (isset($this->groupMappings[$baseName])) {
            return $this->groupMappings[$baseName]['key'];
        }

        // Se não encontrar, gera uma chave baseada no nome
        return $this->normalizeGroupKey($baseName);
    }

    protected function generateGroupMapping(string $modelClass): array
    {
        $baseName = class_basename($modelClass);

        if (isset($this->groupMappings[$baseName])) {
            return $this->groupMappings[$baseName];
        }

        $friendlyName = $this->translateModelName($baseName);
        $pluralName = $this->generateFriendlyPluralName($friendlyName);
        $key = $this->getGroupKeyForModel($baseName);

        return [
            'key' => $key,
            'name' => $pluralName,
            'description' => "Permissões relacionadas a " . mb_strtolower($pluralName)
        ];
    }

    protected function generateFriendlyPluralName(string $name): string
    {
        $specialPlurals = [
            '/ão$/i' => 'ões',
            '/al$/i' => 'ais',
            '/el$/i' => 'éis',
            '/ol$/i' => 'óis',
            '/ul$/i' => 'uis',
            '/il$/i' => 'is',
            '/m$/i' => 'ns',
            '/r$/i' => 'res',
            '/s$/i' => 's',
            '/z$/i' => 'zes',
        ];

        if (Str::endsWith($name, 's')) {
            return $name;
        }

        foreach ($specialPlurals as $pattern => $replacement) {
            if (preg_match($pattern, $name)) {
                return preg_replace($pattern, $replacement, $name);
            }
        }

        return $name . 's';
    }

    protected function getGroupName(string $key): string
    {
        return $this->translations['groups'][$key] ?? ucfirst($key);
    }

    protected function getMethodMap(): array
    {
        static $map = [
            'index' => 'ver',
            'show' => 'ver',
            'create' => 'criar',
            'store' => 'criar',
            'edit' => 'editar',
            'update' => 'editar',
            'destroy' => 'excluir'
        ];

        return $map;
    }

    protected function isPortugueseWord(string $word): bool
    {
        static $indicators = [
            'de', 'da', 'do', 'das', 'dos', 'e',
            'acao', 'ção', 'dade', 'mento', 'agem',
            'ário', 'ária', 'eiro', 'eira', 'ista'
        ];

        $word = mb_strtolower($word);
        foreach ($indicators as $indicator) {
            if (Str::contains($word, $indicator)) {
                return true;
            }
        }

        return false;
    }

    protected function shouldIgnorePermission(string $slug): bool
    {
        foreach ($this->excluded['resources'] as $resource) {
            if (Str::contains($slug, $resource)) {
                return true;
            }
        }

        return false;
    }

    protected function getBaseName(string $className): string
    {
        return str_replace('Controller', '', class_basename($className));
    }

    /**
     * Gera um array de mapeamento entre IDs de grupo e suas chaves
     */
    protected function getGroupIdMap(): array
    {
        static $map = null;

        if ($map === null) {
            $map = [
                'users' => 1,       // ID do grupo de Usuários
                'roles' => 2,       // ID do grupo de Cargos
                'branches' => 3,    // ID do grupo de Filiais
                'system' => 4,     // ID do grupo de Sistema
            ];
        }

        return $map;
    }

    /**
     * Determina o ID do grupo com base no slug da permissão
     */
    protected function determinePermissionGroup(string $slug): int
    {
        $map = $this->getGroupIdMap();

        foreach ($map as $key => $id) {
            if (Str::contains($slug, $key)) {
                return $id;
            }
        }

        return $map['system']; // Grupo padrão é Sistema
    }

    /**
     * Verifica se um grupo específico já existe
     */
    protected function groupExists(string $name): bool
    {
        return PermissionGroup::where('name', $name)->exists();
    }

    /**
     * Obtém ou cria um grupo de permissões
     */
    protected function getOrCreateGroup(array $groupData): PermissionGroup
    {
        return PermissionGroup::firstOrCreate(
            ['name' => $groupData['name']],
            [
                'description' => $groupData['description'],
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    protected function normalizeGroupKey(string $key): string
    {
        return Str::snake(Str::plural($key));
    }
}

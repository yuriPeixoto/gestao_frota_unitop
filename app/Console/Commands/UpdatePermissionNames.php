<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Configuracoes\Models\Permission;

class UpdatePermissionNames extends Command
{
    protected $signature = 'permissions:update-names';
    protected $description = 'Atualiza os nomes e descrições das permissões existentes';

    private $translations = [
        'branches' => 'Filiais',
        'users' => 'Usuários',
        'roles' => 'Cargos',
        'permissions' => 'Permissões'
    ];

    private $actions = [
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
        ]
    ];

    public function handle()
    {
        $permissions = Permission::all();

        foreach ($permissions as $permission) {
            $parts = explode('_', $permission->slug);
            if (count($parts) >= 2) {
                $action = $parts[0];
                $resource = $parts[1];

                if (isset($this->actions[$action]) && isset($this->translations[$resource])) {
                    $permission->update([
                        'name' => "{$this->actions[$action]['name']} {$this->translations[$resource]}",
                        'description' => "Permite {$this->actions[$action]['description']} {$this->translations[$resource]}"
                    ]);

                    $this->info("Atualizada permissão: {$permission->slug}");
                }
            }
        }

        $this->info('Todas as permissões foram atualizadas!');
    }
}

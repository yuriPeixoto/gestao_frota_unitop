<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RequisicaoMaterialPermissionsSeeder extends Seeder
{
   /**
    * Run the database seeds.
    */
   public function run(): void
   {
      // Início da transação para garantir integridade dos dados
      DB::beginTransaction();

      try {
         // Desabilitar restrições de chave estrangeira
         Schema::disableForeignKeyConstraints();

         // Criar permissões do módulo de requisição de materiais
         $permissions = $this->createPermissions();

         // Criar roles
         $roles = $this->createRoles();

         // Atribuir permissões às roles
         $this->assignPermissionsToRoles($roles, $permissions);

         // Habilitar restrições de chave estrangeira
         Schema::enableForeignKeyConstraints();

         // Commit da transação
         DB::commit();

         $this->command->info('Permissões e papéis do módulo de requisição de materiais criados com sucesso!');
      } catch (\Exception $e) {
         // Reverter em caso de erro
         DB::rollBack();
         Schema::enableForeignKeyConstraints();

         $this->command->error('Erro ao criar permissões e papéis: ' . $e->getMessage());
         throw $e;
      }
   }

   /**
    * Criar permissões para o módulo de requisição de materiais
    */
   private function createPermissions(): array
   {
      $permissionsByGroup = [
         // Requisições de Materiais
         'requisicao_material' => [
            'criar_requisicao_material' => 'Permite criar novas requisições de materiais',
            'editar_requisicao_material' => 'Permite editar requisições existentes',
            'visualizar_requisicao_material' => 'Permite visualizar requisições de materiais',
            'excluir_requisicao_material' => 'Permite excluir requisições',
            'aprovar_requisicao_material' => 'Permite aprovar requisições de materiais',
            'rejeitar_requisicao_material' => 'Permite rejeitar requisições de materiais',
            'cancelar_requisicao_material' => 'Permite cancelar requisições de materiais',
            'finalizar_requisicao_material' => 'Permite finalizar requisições de materiais',
            'ver_requisicaomaterial' => 'Permite visualizar menu de requisições de materiais',
         ],
      ];

      $permissions = [];

      foreach ($permissionsByGroup as $group => $groupPermissions) {
         foreach ($groupPermissions as $name => $description) {
            $permission = Permission::firstOrCreate([
               'name' => $name,
               'guard_name' => 'web',
            ], [
               'description' => $description,
               'group' => $group,
            ]);

            $this->command->line("Permissão criada: {$name}");
            $permissions[$name] = $permission;
         }
      }

      return $permissions;
   }

   /**
    * Criar roles para o módulo de requisição de materiais
    */
   private function createRoles(): array
   {
      $rolesToCreate = [
         'Aprovador de Requisição' => 'Usuários que podem aprovar/rejeitar requisições de materiais',
      ];

      $roles = [];

      foreach ($rolesToCreate as $name => $description) {
         $role = Role::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
         ], [
            'description' => $description,
         ]);

         $this->command->line("Role criada: {$name}");
         $roles[$name] = $role;
      }

      return $roles;
   }

   /**
    * Atribuir permissões às roles
    */
   private function assignPermissionsToRoles(array $roles, array $permissions): void
   {
      // Permissões para Aprovador de Requisição
      $aprovadorRequisicaoPermissions = [
         'visualizar_requisicao_material',
         'aprovar_requisicao_material',
         'rejeitar_requisicao_material',
         'ver_requisicaomaterial',
      ];
      $this->assignPermissionsToRole($roles['Aprovador de Requisição'], $aprovadorRequisicaoPermissions, $permissions);
   }

   /**
    * Atribuir permissões específicas a uma role
    */
   private function assignPermissionsToRole($role, array $permissionNames, array $permissions): void
   {
      foreach ($permissionNames as $permissionName) {
         if (isset($permissions[$permissionName])) {
            $role->givePermissionTo($permissions[$permissionName]);
            $this->command->line("Permissão {$permissionName} atribuída à role {$role->name}");
         } else {
            $this->command->warn("Permissão {$permissionName} não encontrada");
         }
      }
   }
}

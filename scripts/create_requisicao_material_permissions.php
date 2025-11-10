<?php

// Script para criar permissões e role de Aprovador de Requisição de Materiais
// Execute: php scripts/create_requisicao_material_permissions.php

require_once __DIR__ . '/../vendor/autoload.php';

// Carrega a aplicação Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

try {
   echo "Iniciando criação de permissões e role para Aprovador de Requisição de Materiais...\n";

   // Limpar cache de permissões
   app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

   // Permissões que serão criadas
   $permissions = [
      'criar_requisicao_material' => 'Permite criar novas requisições de materiais',
      'editar_requisicao_material' => 'Permite editar requisições existentes',
      'visualizar_requisicao_material' => 'Permite visualizar requisições de materiais',
      'excluir_requisicao_material' => 'Permite excluir requisições',
      'aprovar_requisicao_material' => 'Permite aprovar requisições de materiais',
      'rejeitar_requisicao_material' => 'Permite rejeitar requisições de materiais',
      'cancelar_requisicao_material' => 'Permite cancelar requisições de materiais',
      'finalizar_requisicao_material' => 'Permite finalizar requisições de materiais',
      'ver_requisicaomaterial' => 'Permite visualizar menu de requisições de materiais',
   ];

   // Criar as permissões
   foreach ($permissions as $name => $description) {
      $permission = Permission::firstOrCreate([
         'name' => $name,
         'guard_name' => 'web',
      ], [
         'description' => $description,
         'group' => 'requisicao_material',
      ]);

      echo "Permissão criada/encontrada: {$name}\n";
   }

   // Verificar se a role já existe
   $role = Role::where('name', 'Aprovador de Requisição')->where('guard_name', 'web')->first();
   if (!$role) {
      try {
         // Usar SQL direto para resolver o problema de sequência
         $maxId = DB::table('roles')->max('id') ?? 0;
         $nextId = $maxId + 1;

         DB::table('roles')->insert([
            'id' => $nextId,
            'name' => 'Aprovador de Requisição',
            'guard_name' => 'web',
            'description' => 'Usuários que podem aprovar/rejeitar requisições de materiais',
            'created_at' => now(),
            'updated_at' => now(),
         ]);

         // Atualizar a sequência
         DB::statement("SELECT setval('roles_id_seq', (SELECT MAX(id) FROM roles))");

         $role = Role::where('name', 'Aprovador de Requisição')->where('guard_name', 'web')->first();
         echo "Role criada via SQL: Aprovador de Requisição (ID: {$role->id})\n";
      } catch (Exception $e) {
         echo "Erro ao criar role via SQL: " . $e->getMessage() . "\n";
         // Tentar encontrar a role caso já exista
         $role = Role::where('name', 'Aprovador de Requisição')->where('guard_name', 'web')->first();
         if ($role) {
            echo "Role encontrada após erro: Aprovador de Requisição (ID: {$role->id})\n";
         } else {
            // Se ainda não existe, listar roles existentes para debug
            $roles = Role::all();
            echo "Roles existentes:\n";
            foreach ($roles as $r) {
               echo "- {$r->name} (ID: {$r->id})\n";
            }
            throw $e;
         }
      }
   } else {
      echo "Role já existe: Aprovador de Requisição (ID: {$role->id})\n";
   }



   // Atribuir permissões específicas à role
   $rolePermissions = [
      'visualizar_requisicao_material',
      'aprovar_requisicao_material',
      'rejeitar_requisicao_material',
      'ver_requisicaomaterial',
   ];

   foreach ($rolePermissions as $permissionName) {
      $permission = Permission::where('name', $permissionName)->first();
      if ($permission) {
         $role->givePermissionTo($permission);
         echo "Permissão {$permissionName} atribuída à role Aprovador de Requisição\n";
      } else {
         echo "AVISO: Permissão {$permissionName} não encontrada\n";
      }
   }

   echo "\n✅ Criação concluída com sucesso!\n";
   echo "- Permissões criadas: " . count($permissions) . "\n";
   echo "- Role criada: Aprovador de Requisição\n";
   echo "- Permissões atribuídas à role: " . count($rolePermissions) . "\n";
} catch (Exception $e) {
   echo "❌ Erro: " . $e->getMessage() . "\n";
   echo "Stack trace: " . $e->getTraceAsString() . "\n";
   exit(1);
}

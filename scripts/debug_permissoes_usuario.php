<?php

/**
 * Script para debug completo de permissões de um usuário
 * Uso: php scripts/debug_permissoes_usuario.php
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

function debugPermissoesUsuario($userId, $permissaoEspecifica = null)
{
   $user = User::find($userId);

   if (!$user) {
      echo "Usuário com ID $userId não encontrado!\n";
      return;
   }

   echo str_repeat('=', 60) . "\n";
   echo "DEBUG DE PERMISSÕES - USUÁRIO ID: $userId\n";
   echo str_repeat('=', 60) . "\n";

   echo "Nome: {$user->name}\n";
   echo "Email: {$user->email}\n";
   echo "Superusuário: " . ($user->is_superuser ? 'SIM ⚡' : 'NÃO') . "\n";
   echo "Criado em: {$user->created_at}\n";

   // Roles do usuário
   echo "\n" . str_repeat('-', 40) . "\n";
   echo "ROLES DO USUÁRIO\n";
   echo str_repeat('-', 40) . "\n";

   $roles = $user->roles;
   if ($roles->isEmpty()) {
      echo "❌ Nenhuma role atribuída\n";
   } else {
      foreach ($roles as $role) {
         echo "✓ {$role->name}\n";
      }
   }

   // Permissões diretas
   echo "\n" . str_repeat('-', 40) . "\n";
   echo "PERMISSÕES DIRETAS\n";
   echo str_repeat('-', 40) . "\n";

   $permissoesDiretas = $user->getDirectPermissions();
   if ($permissoesDiretas->isEmpty()) {
      echo "❌ Nenhuma permissão direta\n";
   } else {
      foreach ($permissoesDiretas as $perm) {
         echo "✓ {$perm->name}\n";
      }
   }

   // Permissões via roles
   echo "\n" . str_repeat('-', 40) . "\n";
   echo "PERMISSÕES VIA ROLES\n";
   echo str_repeat('-', 40) . "\n";

   $permissoesRoles = $user->getPermissionsViaRoles();
   if ($permissoesRoles->isEmpty()) {
      echo "❌ Nenhuma permissão via roles\n";
   } else {
      foreach ($permissoesRoles as $perm) {
         echo "✓ {$perm->name}\n";
      }
   }

   // Todas as permissões (consolidado)
   echo "\n" . str_repeat('-', 40) . "\n";
   echo "TODAS AS PERMISSÕES (CONSOLIDADO)\n";
   echo str_repeat('-', 40) . "\n";

   $todasPermissoes = $user->getAllPermissions();
   if ($todasPermissoes->isEmpty()) {
      echo "❌ Nenhuma permissão total\n";
   } else {
      foreach ($todasPermissoes as $perm) {
         $origem = $user->getDirectPermissions()->contains($perm) ? '[DIRETA]' : '[VIA ROLE]';
         echo "✓ {$perm->name} $origem\n";
      }
   }

   // Teste de permissão específica se fornecida
   if ($permissaoEspecifica) {
      echo "\n" . str_repeat('-', 40) . "\n";
      echo "TESTE ESPECÍFICO: $permissaoEspecifica\n";
      echo str_repeat('-', 40) . "\n";

      // 1. Verificar superusuário
      echo "1. SUPERUSUÁRIO:\n";
      echo "   is_superuser: " . ($user->is_superuser ? '✓ SIM' : '❌ NÃO') . "\n";

      // 2. Verificar método customizado (se existe)
      echo "\n2. MÉTODO CUSTOMIZADO:\n";
      if (method_exists($user, 'hasPermission')) {
         $hasCustom = $user->hasPermission($permissaoEspecifica);
         echo "   hasPermission('$permissaoEspecifica'): " . ($hasCustom ? '✓ SIM' : '❌ NÃO') . "\n";
      } else {
         echo "   Método hasPermission não encontrado\n";
      }

      // 3. Verificar Spatie padrão
      echo "\n3. SPATIE PERMISSION:\n";
      try {
         $hasSpatie = $user->hasPermissionTo($permissaoEspecifica);
         echo "   hasPermissionTo('$permissaoEspecifica'): " . ($hasSpatie ? '✓ SIM' : '❌ NÃO') . "\n";
      } catch (Exception $e) {
         echo "   Erro: " . $e->getMessage() . "\n";
      }

      // 4. Verificar can() do Laravel
      echo "\n4. LARAVEL GATE:\n";
      $canLaravel = $user->can($permissaoEspecifica);
      echo "   can('$permissaoEspecifica'): " . ($canLaravel ? '✓ SIM' : '❌ NÃO') . "\n";

      // 5. Verificar se permissão existe
      echo "\n5. PERMISSÃO EXISTE:\n";
      $permissionExists = \Spatie\Permission\Models\Permission::where('name', $permissaoEspecifica)->exists();
      echo "   Permissão '$permissaoEspecifica' existe no sistema: " . ($permissionExists ? '✓ SIM' : '❌ NÃO') . "\n";

      // 6. Resultado final
      echo "\n6. RESULTADO FINAL:\n";
      $resultado = $user->is_superuser || $user->hasPermissionTo($permissaoEspecifica);
      echo "   Acesso seria: " . ($resultado ? '✅ PERMITIDO' : '🚫 NEGADO') . "\n";
   }

   echo "\n" . str_repeat('=', 60) . "\n";
}

// Solicitar ID do usuário
echo "Digite o ID do usuário para debug: ";
$handle = fopen("php://stdin", "r");
$userId = (int)trim(fgets($handle));

echo "Digite uma permissão específica para testar (ou pressione Enter para pular): ";
$permissaoEspecifica = trim(fgets($handle));
fclose($handle);

if (empty($permissaoEspecifica)) {
   $permissaoEspecifica = null;
}

// Executar debug
debugPermissoesUsuario($userId, $permissaoEspecifica);

echo "\n✓ Debug concluído!\n";

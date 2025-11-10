<?php

/**
 * Script para remover permissões de um usuário
 * Uso: php scripts/remover_permissoes_usuario.php
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

function mostrarUsuarios()
{
   echo "=== USUÁRIOS DISPONÍVEIS ===\n";
   $users = User::select('id', 'name', 'email')->orderBy('name')->get();

   foreach ($users as $user) {
      echo "ID: {$user->id} - {$user->name} ({$user->email})\n";
   }
   echo "\n";
}

function mostrarPermissoesUsuario($user)
{
   echo "=== PERMISSÕES ATUAIS DO USUÁRIO ===\n";
   echo "Usuário: {$user->name}\n";
   echo "Superusuário: " . ($user->is_superuser ? 'SIM' : 'NÃO') . "\n\n";

   // Permissões diretas
   $permissoesDiretas = $user->getDirectPermissions();
   echo "PERMISSÕES DIRETAS:\n";
   if ($permissoesDiretas->isEmpty()) {
      echo "- Nenhuma permissão direta\n";
   } else {
      foreach ($permissoesDiretas as $index => $perm) {
         echo ($index + 1) . ". {$perm->name}\n";
      }
   }

   echo "\nPERMISSÕES VIA ROLES:\n";
   $permissoesRoles = $user->getPermissionsViaRoles();
   if ($permissoesRoles->isEmpty()) {
      echo "- Nenhuma permissão via roles\n";
   } else {
      foreach ($permissoesRoles as $perm) {
         echo "- {$perm->name} (via role)\n";
      }
   }

   return $permissoesDiretas;
}

function selecionarPermissoes($permissions)
{
   $selecionadas = [];

   echo "\nOpções:\n";
   echo "- Digite os números das permissões (separados por vírgula)\n";
   echo "- Digite 'all' para remover todas as permissões diretas\n";
   echo "- Digite 'filter:texto' para filtrar por texto\n";
   echo "\nSua escolha: ";

   $handle = fopen("php://stdin", "r");
   $entrada = trim(fgets($handle));
   fclose($handle);

   if (strtolower($entrada) === 'all') {
      return $permissions->pluck('name')->toArray();
   }

   if (strpos($entrada, 'filter:') === 0) {
      $filtro = substr($entrada, 7);
      $filtradas = $permissions->filter(function ($perm) use ($filtro) {
         return str_contains($perm->name, $filtro);
      });

      echo "\nPermissões filtradas por '$filtro':\n";
      foreach ($filtradas as $index => $perm) {
         echo ($index + 1) . ". {$perm->name}\n";
      }

      echo "\nDigite os números das permissões filtradas (separados por vírgula) ou 'all': ";
      $handle = fopen("php://stdin", "r");
      $entradaFiltrada = trim(fgets($handle));
      fclose($handle);

      if (strtolower($entradaFiltrada) === 'all') {
         return $filtradas->pluck('name')->toArray();
      }

      $indices = explode(',', $entradaFiltrada);
      $filtradasArray = $filtradas->values();
      foreach ($indices as $indice) {
         $indice = (int)trim($indice) - 1;
         if (isset($filtradasArray[$indice])) {
            $selecionadas[] = $filtradasArray[$indice]->name;
         }
      }

      return $selecionadas;
   }

   $indices = explode(',', $entrada);
   foreach ($indices as $indice) {
      $indice = (int)trim($indice) - 1;
      if (isset($permissions[$indice])) {
         $selecionadas[] = $permissions[$indice]->name;
      }
   }

   return $selecionadas;
}

// Mostrar usuários
mostrarUsuarios();

// Solicitar ID do usuário
echo "Digite o ID do usuário: ";
$handle = fopen("php://stdin", "r");
$userId = (int)trim(fgets($handle));

$user = User::find($userId);
if (!$user) {
   echo "Usuário não encontrado!\n";
   exit(1);
}

// Mostrar permissões atuais
$permissoesDiretas = mostrarPermissoesUsuario($user);

if ($permissoesDiretas->isEmpty()) {
   echo "\nEste usuário não possui permissões diretas para remover.\n";
   echo "Nota: Permissões via roles não podem ser removidas diretamente do usuário.\n";
   exit(0);
}

// Selecionar permissões para remover
$permissoesSelecionadas = selecionarPermissoes($permissoesDiretas);

if (empty($permissoesSelecionadas)) {
   echo "Nenhuma permissão selecionada!\n";
   exit(1);
}

echo "\nPermissões que serão REMOVIDAS:\n";
foreach ($permissoesSelecionadas as $perm) {
   echo "- $perm\n";
}

echo "\n⚠️  ATENÇÃO: Esta ação irá remover as permissões selecionadas!\n";
echo "Confirmar remoção? (s/n): ";
$confirmacao = trim(fgets($handle));
fclose($handle);

if (strtolower($confirmacao) !== 's') {
   echo "Operação cancelada.\n";
   exit(0);
}

// Remover as permissões
echo "\n=== REMOVENDO PERMISSÕES ===\n";
$sucessos = 0;
$erros = 0;

foreach ($permissoesSelecionadas as $permissao) {
   try {
      if (!$user->hasDirectPermission($permissao)) {
         echo "○ NÃO TINHA: $permissao\n";
      } else {
         $user->revokePermissionTo($permissao);
         echo "✓ REMOVIDA: $permissao\n";
         $sucessos++;
      }
   } catch (Exception $e) {
      echo "✗ ERRO: $permissao - " . $e->getMessage() . "\n";
      $erros++;
   }
}

// Limpar cache
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

echo "\n=== RESULTADO ===\n";
echo "Permissões removidas: $sucessos\n";
echo "Erros: $erros\n";
echo "Cache de permissões limpo: ✓\n";

// Mostrar permissões finais
echo "\n=== PERMISSÕES RESTANTES ===\n";
$user = $user->fresh();
$permissoesFinal = $user->getDirectPermissions();

if ($permissoesFinal->isEmpty()) {
   echo "✓ Nenhuma permissão direta restante\n";
} else {
   echo "Permissões diretas restantes:\n";
   foreach ($permissoesFinal as $perm) {
      echo "- {$perm->name}\n";
   }
}

$permissoesViaRoles = $user->getPermissionsViaRoles();
if (!$permissoesViaRoles->isEmpty()) {
   echo "\nPermissões via roles (não alteradas):\n";
   foreach ($permissoesViaRoles as $perm) {
      echo "- {$perm->name}\n";
   }
}

echo "\n✓ Script concluído!\n";

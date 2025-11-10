<?php

/**
 * Script para dar permissões a um usuário
 * Uso: php scripts/dar_permissoes_usuario.php
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Permission;

function mostrarUsuarios()
{
   echo "=== USUÁRIOS DISPONÍVEIS ===\n";
   $users = User::select('id', 'name', 'email')->orderBy('name')->get();

   foreach ($users as $user) {
      echo "ID: {$user->id} - {$user->name} ({$user->email})\n";
   }
   echo "\n";
}

function mostrarPermissoes($filtro = null)
{
   echo "=== PERMISSÕES DISPONÍVEIS ===\n";

   $query = Permission::query();
   if ($filtro) {
      $query->where('name', 'like', "%{$filtro}%");
   }

   $permissions = $query->orderBy('name')->get();

   if ($permissions->isEmpty()) {
      echo "Nenhuma permissão encontrada" . ($filtro ? " com filtro '$filtro'" : "") . "\n";
      return [];
   }

   foreach ($permissions as $index => $permission) {
      echo ($index + 1) . ". {$permission->name}\n";
   }
   echo "\n";

   return $permissions;
}

function selecionarPermissoes($permissions)
{
   $selecionadas = [];

   echo "Digite os números das permissões (separados por vírgula) ou 'all' para todas: ";
   $handle = fopen("php://stdin", "r");
   $entrada = trim(fgets($handle));
   fclose($handle);

   if (strtolower($entrada) === 'all') {
      return $permissions->pluck('name')->toArray();
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

echo "\nUsuário selecionado: {$user->name}\n";
echo "Superusuário: " . ($user->is_superuser ? 'SIM' : 'NÃO') . "\n\n";

// Mostrar permissões atuais
echo "=== PERMISSÕES ATUAIS DO USUÁRIO ===\n";
$permissoesAtuais = $user->getAllPermissions();
if ($permissoesAtuais->isEmpty()) {
   echo "Nenhuma permissão atual\n";
} else {
   foreach ($permissoesAtuais as $perm) {
      $tipo = $user->getDirectPermissions()->contains($perm) ? '[DIRETA]' : '[VIA ROLE]';
      echo "- {$perm->name} $tipo\n";
   }
}
echo "\n";

// Solicitar filtro para permissões
echo "Digite um filtro para as permissões (ou pressione Enter para ver todas): ";
$filtro = trim(fgets($handle));

// Mostrar permissões disponíveis
$permissions = mostrarPermissoes($filtro ?: null);
if ($permissions->isEmpty()) {
   echo "Nenhuma permissão disponível!\n";
   exit(1);
}

// Selecionar permissões
$permissoesSelecionadas = selecionarPermissoes($permissions);

if (empty($permissoesSelecionadas)) {
   echo "Nenhuma permissão selecionada!\n";
   exit(1);
}

echo "\nPermissões que serão adicionadas:\n";
foreach ($permissoesSelecionadas as $perm) {
   echo "- $perm\n";
}

echo "\nConfirmar? (s/n): ";
$confirmacao = trim(fgets($handle));
fclose($handle);

if (strtolower($confirmacao) !== 's') {
   echo "Operação cancelada.\n";
   exit(0);
}

// Dar as permissões
echo "\n=== ADICIONANDO PERMISSÕES ===\n";
$sucessos = 0;
$erros = 0;

foreach ($permissoesSelecionadas as $permissao) {
   try {
      if ($user->hasPermissionTo($permissao)) {
         echo "○ JÁ TEM: $permissao\n";
      } else {
         $user->givePermissionTo($permissao);
         echo "✓ ADICIONADA: $permissao\n";
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
echo "Permissões adicionadas: $sucessos\n";
echo "Erros: $erros\n";
echo "Cache de permissões limpo: ✓\n";

// Mostrar permissões finais
echo "\n=== PERMISSÕES FINAIS DO USUÁRIO ===\n";
$permissoesFinal = $user->fresh()->getAllPermissions();
foreach ($permissoesFinal as $perm) {
   $tipo = $user->getDirectPermissions()->contains($perm) ? '[DIRETA]' : '[VIA ROLE]';
   echo "- {$perm->name} $tipo\n";
}

echo "\n✓ Script concluído!\n";

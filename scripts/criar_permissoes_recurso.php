<?php

/**
 * Script para criar permissões completas para um novo recurso
 * Uso: php scripts/criar_permissoes_recurso.php
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;

function criarPermissoesCompletas($recurso, $recursoPlural = null)
{
   if (!$recursoPlural) {
      $recursoPlural = $recurso . 's';
   }

   echo "=== CRIANDO PERMISSÕES PARA: $recurso ===\n";
   echo "Recurso singular: $recurso\n";
   echo "Recurso plural: $recursoPlural\n\n";

   $acoes = ['criar', 'editar', 'visualizar', 'excluir'];
   $acoesExtras = ['aprovar', 'rejeitar']; // Para recursos que precisam de aprovação

   $permissoesCriadas = [];

   // Permissões para Policy (singular)
   echo "--- Permissões para Policy (singular) ---\n";
   foreach ($acoes as $acao) {
      $nome = "{$acao}_{$recurso}";

      try {
         $permission = Permission::firstOrCreate(['name' => $nome]);
         if ($permission->wasRecentlyCreated) {
            echo "✓ CRIADA: $nome\n";
         } else {
            echo "○ JÁ EXISTE: $nome\n";
         }
         $permissoesCriadas[] = $nome;
      } catch (Exception $e) {
         echo "✗ ERRO: $nome - " . $e->getMessage() . "\n";
      }
   }

   // Permissões para Middleware (plural)
   echo "\n--- Permissões para Middleware (plural) ---\n";
   foreach ($acoes as $acao) {
      $nome = "{$acao}_{$recursoPlural}";

      try {
         $permission = Permission::firstOrCreate(['name' => $nome]);
         if ($permission->wasRecentlyCreated) {
            echo "✓ CRIADA: $nome\n";
         } else {
            echo "○ JÁ EXISTE: $nome\n";
         }
         $permissoesCriadas[] = $nome;
      } catch (Exception $e) {
         echo "✗ ERRO: $nome - " . $e->getMessage() . "\n";
      }
   }

   // Perguntar se quer criar permissões extras
   echo "\nCriar permissões extras (aprovar/rejeitar)? (s/n): ";
   $handle = fopen("php://stdin", "r");
   $resposta = trim(fgets($handle));
   fclose($handle);

   if (strtolower($resposta) === 's') {
      echo "\n--- Permissões Extras ---\n";
      foreach ($acoesExtras as $acao) {
         $nome = "{$acao}_{$recurso}";

         try {
            $permission = Permission::firstOrCreate(['name' => $nome]);
            if ($permission->wasRecentlyCreated) {
               echo "✓ CRIADA: $nome\n";
            } else {
               echo "○ JÁ EXISTE: $nome\n";
            }
            $permissoesCriadas[] = $nome;
         } catch (Exception $e) {
            echo "✗ ERRO: $nome - " . $e->getMessage() . "\n";
         }
      }
   }

   echo "\n=== RESUMO ===\n";
   echo "Total de permissões processadas: " . count($permissoesCriadas) . "\n";
   echo "\nPermissões criadas/verificadas:\n";
   foreach ($permissoesCriadas as $perm) {
      echo "- $perm\n";
   }

   echo "\n=== PRÓXIMOS PASSOS ===\n";
   echo "1. Atribuir permissões aos usuários/roles necessários\n";
   echo "2. Atualizar policies se necessário\n";
   echo "3. Verificar se middleware está funcionando\n";
   echo "4. Testar funcionalidade\n";

   return $permissoesCriadas;
}

// Solicitar nome do recurso
echo "Digite o nome do recurso (singular, ex: 'solicitacao_compra'): ";
$handle = fopen("php://stdin", "r");
$recurso = trim(fgets($handle));

echo "Digite o nome do recurso no plural (ex: 'solicitacoes') ou pressione Enter para auto-gerar: ";
$recursoPlural = trim(fgets($handle));
fclose($handle);

if (empty($recurso)) {
   echo "Erro: Nome do recurso é obrigatório!\n";
   exit(1);
}

if (empty($recursoPlural)) {
   $recursoPlural = null; // Será auto-gerado
}

// Criar as permissões
$permissoes = criarPermissoesCompletas($recurso, $recursoPlural);

echo "\n✓ Script concluído!\n";

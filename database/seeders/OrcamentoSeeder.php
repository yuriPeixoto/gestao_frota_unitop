<?php

namespace Database\Seeders;

use App\Modules\Compras\Models\Fornecedor;
use App\Modules\Compras\Models\ItemOrcamento;
use App\Modules\Compras\Models\Orcamento;
use App\Modules\Compras\Models\PedidoCompra;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrcamentoSeeder extends Seeder
{
   /**
    * Run the database seeds.
    */
   public function run(): void
   {
      // Buscar alguns pedidos de compra existentes
      $pedidos = PedidoCompra::with('itens')->limit(5)->get();

      if ($pedidos->isEmpty()) {
         $this->command->info('Nenhum pedido de compra encontrado. Pulando seed de orçamentos.');
         return;
      }

      // Buscar fornecedores existentes
      $fornecedores = Fornecedor::limit(10)->get();

      if ($fornecedores->isEmpty()) {
         $this->command->info('Nenhum fornecedor encontrado. Pulando seed de orçamentos.');
         return;
      }

      $this->command->info('Criando orçamentos de exemplo...');

      DB::beginTransaction();
      try {
         $contador = 0;
         foreach ($pedidos as $pedido) {
            // Para cada pedido, criar 2-3 orçamentos de fornecedores diferentes
            $numOrcamentos = 2 + ($contador % 2); // Alterna entre 2 e 3
            $fornecedoresUsados = $fornecedores->take($numOrcamentos);

            foreach ($fornecedoresUsados as $index => $fornecedor) {
               $diasAtras = 7 + ($contador * 3);
               $prazoEntrega = 10 + ($index * 5);
               $diasValidade = 45 + ($contador * 10);

               $orcamento = Orcamento::create([
                  'id_pedido' => $pedido->id_pedido_compras,
                  'id_fornecedor' => $fornecedor->id_fornecedor,
                  'data_orcamento' => now()->subDays($diasAtras),
                  'prazo_entrega' => $prazoEntrega,
                  'validade' => now()->addDays($diasValidade),
                  'observacao' => 'Orçamento de exemplo criado pelo seeder',
                  'selecionado' => $index === 0, // Primeiro orçamento será selecionado
                  'data_inclusao' => now(),
               ]);

               $valorTotal = 0;

               // Criar itens do orçamento baseados nos itens do pedido
               if ($pedido->itens->isNotEmpty()) {
                  foreach ($pedido->itens as $itemIndex => $itemPedido) {
                     $valorUnitario = 50.00 + (($index + $itemIndex) * 15.50); // Valores variados
                     $quantidade = $itemPedido->quantidade ?? 1;
                     $valorItemTotal = $valorUnitario * $quantidade;
                     $valorTotal += $valorItemTotal;

                     ItemOrcamento::create([
                        'id_orcamento' => $orcamento->id_orcamento,
                        'id_item_pedido' => $itemPedido->id_item_pedido,
                        'valor_unitario' => $valorUnitario,
                        'quantidade' => $quantidade,
                        'valor_total' => $valorItemTotal,
                        'data_inclusao' => now(),
                     ]);
                  }
               } else {
                  // Se não há itens, criar um item genérico
                  $valorUnitario = 100.00 + ($index * 25.00);
                  $quantidade = 1;
                  $valorTotal = $valorUnitario * $quantidade;
               }

               // Atualizar o valor total do orçamento
               $orcamento->valor_total = $valorTotal;
               $orcamento->save();

               $this->command->info("Orçamento criado: ID {$orcamento->id_orcamento} - Pedido: {$pedido->id_pedido_compras} - Fornecedor: {$fornecedor->nome_fornecedor}");
            }
            $contador++;
         }

         DB::commit();
         $this->command->info('Orçamentos de exemplo criados com sucesso!');
      } catch (\Exception $e) {
         DB::rollBack();
         $this->command->error('Erro ao criar orçamentos: ' . $e->getMessage());
      }
   }
}

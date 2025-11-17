<?php

namespace App\Services;

use App\Modules\Compras\Models\SolicitacaoCompra;
use App\Modules\Compras\Models\PedidoCompra;
use Illuminate\Support\Facades\Log;

class CancelamentoService
{
   public function cancelar(SolicitacaoCompra $solicitacao, $userId, $justificativa)
   {
      // $this->verificarPedidosAtivos($solicitacao);

      $solicitacao->update([
         'situacao_compra' => 'CANCELADA',
         'justificativa_edit_or_delete' => $justificativa,
         'is_cancelada' => true,
      ]);

      // Registrar log do cancelamento
      $solicitacao->registrarLog(
         'CANCELADA',
         $userId,
         $justificativa
      );

      return $solicitacao;
   }

   protected function verificarPedidosAtivos(SolicitacaoCompra $solicitacao)
   {
      $temPedidoAtivo = PedidoCompra::where('id_solicitacoes_compras', $solicitacao->id_solicitacoes_compras)
         ->where('situacao_pedido', '!=', 6)
         ->exists();

      if ($temPedidoAtivo) {
         throw new \Exception('Não é possível cancelar esta solicitação, pois já existem pedidos de compra associados a ela com status diferente de cancelado.');
      }
   }
}

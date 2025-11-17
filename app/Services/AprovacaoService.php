<?php

namespace App\Services;

use App\Modules\Compras\Models\SolicitacaoCompra;

class AprovacaoService
{
   public function aprovar(SolicitacaoCompra $solicitacao, $userId, $observacao = null)
   {
      $solicitacao->aprovado_reprovado = true;
      $solicitacao->data_aprovacao = now();
      $solicitacao->id_aprovador = $userId;
      $solicitacao->observacao_aprovador = $observacao;
      $solicitacao->situacao_compra = 'APROVADA';
      $solicitacao->save();

      $this->atualizarItens($solicitacao, 'APROVADA');

      // Registrar log da aprovação
      $solicitacao->registrarLog(
         'APROVADA',
         $userId,
         $observacao ?? 'Solicitação aprovada'
      );

      return $solicitacao;
   }

   public function reprovar(SolicitacaoCompra $solicitacao, $userId, $observacao)
   {
      $solicitacao->aprovado_reprovado = false;
      $solicitacao->data_aprovacao = now();
      $solicitacao->id_aprovador = $userId;
      $solicitacao->observacao_aprovador = $observacao;
      $solicitacao->situacao_compra = 'REJEITADA';
      $solicitacao->save();

      $this->atualizarItens($solicitacao, 'REJEITADA');

      // Registrar log da reprovação
      $solicitacao->registrarLog(
         'REJEITADA',
         $userId,
         $observacao
      );

      return $solicitacao;
   }

   protected function atualizarItens(SolicitacaoCompra $solicitacao, $status)
   {
      $solicitacao->itens()->update(['status' => $status]);
   }
}

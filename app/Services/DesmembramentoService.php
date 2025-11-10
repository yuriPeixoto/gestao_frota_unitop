<?php

namespace App\Services;

use App\Models\SolicitacaoCompra;
use App\Models\ItemSolicitacaoCompra;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DesmembramentoService
{
   public function desmembrar(SolicitacaoCompra $solicitacao, array $dados)
   {
      DB::beginTransaction();
      try {
         $itensRequest = $dados['itens'];
         $justificativa = $dados['justificativa'];

         $itensPorNovaSolicitacao = $this->agruparItens($itensRequest);
         $this->validarAgrupamento($itensPorNovaSolicitacao);

         $novasSolicitacoes = $this->criarNovasSolicitacoes($solicitacao, $itensPorNovaSolicitacao, $justificativa);
         $this->finalizarSolicitacoesOriginais($solicitacao, $novasSolicitacoes);

         DB::commit();

         return $novasSolicitacoes;
      } catch (\Exception $e) {
         DB::rollBack();
         Log::error('Erro ao desmembrar solicitação: ' . $e->getMessage());
         throw $e;
      }
   }

   protected function agruparItens(array $itensRequest)
   {
      $itensPorNovaSolicitacao = [];

      foreach ($itensRequest as $itemData) {
         $novaSolicitacaoIndex = $itemData['nova_solicitacao'];
         if (!isset($itensPorNovaSolicitacao[$novaSolicitacaoIndex])) {
            $itensPorNovaSolicitacao[$novaSolicitacaoIndex] = [];
         }
         $itensPorNovaSolicitacao[$novaSolicitacaoIndex][] = $itemData['id'];
      }

      return $itensPorNovaSolicitacao;
   }

   protected function validarAgrupamento(array $itensPorNovaSolicitacao)
   {
      foreach ($itensPorNovaSolicitacao as $index => $itensIds) {
         if (empty($itensIds)) {
            throw ValidationException::withMessages([
               'itens' => ["A solicitação desmembrada #{$index} não tem nenhum item."]
            ]);
         }
      }
   }

   protected function criarNovasSolicitacoes(SolicitacaoCompra $solicitacao, array $itensPorNovaSolicitacao, string $justificativa)
   {
      $novasSolicitacoes = [];

      foreach ($itensPorNovaSolicitacao as $novaSolicitacaoIndex => $itensIds) {
         $novaSolicitacao = $this->criarNovaSolicitacao($solicitacao, $justificativa, $itensIds);
         $novasSolicitacoes[] = $novaSolicitacao;
      }

      return $novasSolicitacoes;
   }

   protected function criarNovaSolicitacao(SolicitacaoCompra $solicitacao, string $justificativa, array $itensIds)
   {
      $novaSolicitacao = new SolicitacaoCompra();
      $novaSolicitacao->fill([
         'id_departamento' => $solicitacao->id_departamento,
         'id_filial' => $solicitacao->id_filial,
         'filial_entrega' => $solicitacao->filial_entrega,
         'filial_faturamento' => $solicitacao->filial_faturamento,
         'prioridade' => $solicitacao->prioridade,
         'id_solicitante' => $solicitacao->id_solicitante,
         'id_comprador' => Auth::id(),
         'situacao_compra' => 'APROVADA',
         'observacao' => "Desmembrada da solicitação #{$solicitacao->id_solicitacoes_compras}. " . $justificativa,
         'id_solicitacao_original' => $solicitacao->id_solicitacoes_compras,
         'aprovado_reprovado' => true,
         'data_aprovacao' => $solicitacao->data_aprovacao,
         'id_aprovador' => $solicitacao->id_aprovador,
         'id_fornecedor' => $solicitacao->id_fornecedor,
         'is_contrato' => $solicitacao->is_contrato,
         'is_aplicacao_direta' => $solicitacao->is_aplicacao_direta,
      ]);
      $novaSolicitacao->save();

      $this->copiarItens($novaSolicitacao, $itensIds);

      // Registrar log da criação da nova solicitação
      $novaSolicitacao->registrarLog(
         'APROVADA',
         Auth::id(),
         "Solicitação criada por desmembramento da solicitação #{$solicitacao->id_solicitacoes_compras}. " . $justificativa
      );

      return $novaSolicitacao;
   }

   protected function copiarItens(SolicitacaoCompra $novaSolicitacao, array $itensIds)
   {
      foreach ($itensIds as $itemId) {
         $itemOriginal = ItemSolicitacaoCompra::find($itemId);
         if (!$itemOriginal || $itemOriginal->isTotalmenteAtendido()) continue;

         $novoItem = new ItemSolicitacaoCompra();
         $novoItem->fill([
            'id_solicitacao' => $novaSolicitacao->id_solicitacoes_compras,
            'tipo' => $itemOriginal->tipo,
            'id_produto' => $itemOriginal->id_produto,
            'id_servico' => $itemOriginal->id_servico,
            'descricao' => $itemOriginal->descricao,
            'quantidade' => $itemOriginal->quantidade_pendente,
            'unidade_medida' => $itemOriginal->unidade_medida,
            'status' => 'aprovado',
            'justificativa' => "Desmembrado da solicitação #{$itemOriginal->id_solicitacao}",
         ]);
         $novoItem->save();
      }
   }

   protected function finalizarSolicitacoesOriginais(SolicitacaoCompra $solicitacao, array $novasSolicitacoes)
   {
      $solicitacao->update([
         'observacao' => $solicitacao->observacao . "\nSolicitação desmembrada em " . count($novasSolicitacoes) . " nova(s) solicitação(ões).",
         'situacao_compra' => 'FINALIZADO',
         'data_finalizada' => now(),
      ]);

      // Registrar log da finalização por desmembramento
      $solicitacao->registrarLog(
         'FINALIZADO',
         Auth::id(),
         "Solicitação finalizada por desmembramento em " . count($novasSolicitacoes) . " nova(s) solicitação(ões)"
      );

      $solicitacao->atualizarStatusItens();
   }
}

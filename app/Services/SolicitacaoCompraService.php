<?php

namespace App\Services;

use App\Models\SolicitacaoCompra;
use App\Models\ItemSolicitacaoCompra;
use App\Models\Produto;
use App\Modules\Manutencao\Models\Servico;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SolicitacaoCompraService
{
   public function criarSolicitacao(array $dados, array $produtos, array $servicos)
   {
      DB::beginTransaction();
      try {
         $solicitacao = SolicitacaoCompra::create($dados);

         $this->processarItens($solicitacao->id_solicitacoes_compras, $produtos, $servicos);

         $solicitacao->registrarLog('INCLUIDA', Auth::id(), 'Solicitação de compra criada');

         DB::commit();

         return $solicitacao;
      } catch (\Exception $e) {
         DB::rollBack();
         Log::error('Erro ao criar solicitação de compra: ' . $e->getMessage());
         throw $e;
      }
   }

   protected function processarItens($solicitacaoId, array $produtos, array $servicos)
   {
      $produtosToInsert = $this->prepararItensProdutos($solicitacaoId, $produtos);
      $servicosToInsert = $this->prepararItensServicos($solicitacaoId, $servicos);

      if (!empty($produtosToInsert)) {
         ItemSolicitacaoCompra::insert($produtosToInsert);
      }

      if (!empty($servicosToInsert)) {
         ItemSolicitacaoCompra::insert($servicosToInsert);
      }
   }

   protected function prepararItensProdutos($solicitacaoId, array $produtos)
   {
      $itens = [];

      foreach ($produtos as $produtoData) {
         $produto = Produto::find($produtoData['id_produto']);
         if (!$produto) continue;

         $itens[] = [
            'data_inclusao' => now(),
            'id_solicitacao_compra' => $solicitacaoId,
            'id_produto' => $produtoData['id_produto'],
            'quantidade_solicitada' => $produtoData['quantidade_solicitada'] ?? 1,
            'id_unidade' => $produtoData['id_unidade'] ?? null,
            'observacao_item' => $produtoData['observacao_item'] ?? null,
            'justificativa_iten_solicitacao' => $produtoData['justificativa_iten_solicitacao'] ?? null,
            'imagem_produto' => $produtoData['imagem_produto'] ?? null, // Adicionado: imagem_produto
         ];
      }

      return $itens;
   }

   protected function prepararItensServicos($solicitacaoId, array $servicos)
   {
      $itens = [];

      foreach ($servicos as $servicoData) {
         $servico = Servico::find($servicoData['id_servico']);
         if (!$servico) continue;

         $itens[] = [
            'data_inclusao' => now(),
            'id_solicitacao_compra' => $solicitacaoId,
            'id_produto' => $servicoData['id_servico'],
            'quantidade_solicitada' => $servicoData['quantidade'] ?? 1, // Corrigido: quantidade_solicitada
            'observacao_item' => $servicoData['observacao_item'] ?? null,
            'justificativa_iten_solicitacao' => $servicoData['justificativa_iten_solicitacao'] ?? null,
            'imagem_produto' => $servicoData['imagem_produto'] ?? null, // Adicionado: imagem_produto
         ];
      }

      return $itens;
   }
}

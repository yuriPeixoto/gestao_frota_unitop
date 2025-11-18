<?php

namespace App\Traits;

use App\Models\Produto;
use App\Modules\Manutencao\Models\Servico;
use App\Modules\Configuracoes\Models\UnidadeProduto;

trait ItemTraitSolicitacao
{
   protected function getProdutos()
   {
      return Produto::select('id_produto', 'descricao_produto')
         ->limit(15)
         ->where('is_ativo', true)
         ->orderBy('descricao_produto')
         ->get()
         ->map(function ($produto) {
            return [
               'value' => $produto->id_produto,
               'label' => $produto->id_produto . ' - ' . $produto->descricao_produto,
               'tipo' => 'produto'
            ];
         });
   }

   protected function getServicos()
   {
      return Servico::select('id_servico', 'descricao_servico')
         ->limit(15)
         ->orderBy('descricao_servico')
         ->get()
         ->map(function ($servico) {
            return [
               'value' => $servico->id_servico,
               'label' => $servico->id_servico . ' - ' . $servico->descricao_servico,
            ];
         });
   }

   protected function getProdutoDescricao()
   {
      return Produto::select('id_produto', 'descricao_produto')
         ->where('is_ativo', true)
         ->orderBy('descricao_produto')
         ->get()
         ->mapWithKeys(function ($item) {
            return [
               $item->id_produto => $item->descricao_produto ?? 'Não Informado',
            ];
         })
         ->toArray();
   }

   protected function getUnidadeProduto()
   {
      return UnidadeProduto::select('id_unidade_produto', 'descricao_unidade')
         ->orderBy('descricao_unidade')
         ->get()
         ->mapWithKeys(function ($item) {
            return [
               $item->id_unidade_produto => $item->descricao_unidade ?? 'Não Informado',
            ];
         })
         ->toArray();
   }

   protected function getServicoDescricao()
   {
      return Servico::select('id_servico', 'descricao_servico')
         ->orderBy('descricao_servico')
         ->get()
         ->mapWithKeys(function ($item) {
            return [
               $item->id_servico => $item->descricao_servico ?? 'Não Informado',
            ];
         })
         ->toArray();
   }
}

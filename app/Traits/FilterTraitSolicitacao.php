<?php

namespace App\Traits;

use App\Models\Departamento;
use App\Models\VFilial;
use Illuminate\Http\Request;

trait FilterTraitSolicitacao
{
   protected function applyFilters($query, Request $request)
   {
      if ($request->input('situacao_compra')) {
         $query->where('situacao_compra', $request->input('situacao_compra'));
      }

      if ($request->input('departamento_id')) {
         $query->where('id_departamento', $request->input('departamento_id'));
      }

      if ($request->input('filial_id')) {
         $query->where('id_filial', $request->input('filial_id'));
      }

      if ($request->input('tipo_solicitacao')) {
         $query->where('tipo_solicitacao', $request->input('tipo_solicitacao'));
      }

      if ($request->input('data_inicio')) {
         $query->where('data_inclusao', '>=', $request->input('data_inicio'));
      }

      if ($request->input('data_fim')) {
         $query->where('data_inclusao', '<=', $request->input('data_fim') . ' 23:59:59');
      }

      if ($termo = $request->input('termo')) {
         $query->where(function ($q) use ($termo) {
            $q->where('id_solicitacoes_compras', 'LIKE', "%{$termo}%")
               ->orWhereHas('solicitante', function ($user) use ($termo) {
                  $user->where('name', 'LIKE', "%{$termo}%");
               })
               ->orWhereHas('departamento', function ($dept) use ($termo) {
                  $dept->where('descricao_departamento', 'LIKE', "%{$termo}%");
               });
         });
      }

      return $query;
   }

   protected function getFilterData()
   {
      return [
         'departamentos' => Departamento::where('ativo', true)->orderBy('descricao_departamento')->get(),
         'filiais' => VFilial::orderBy('name')->get(),
         'situacoesCompra' => [
            'INICIADA' => 'Iniciada',
            'AGUARDANDO APROVAÇÃO DO GESTOR DEPARTAMENTO' => 'Aguardando Aprovação do Gestor',
            'AGUARDANDO APROVAÇÃO' => 'Aguardando Aprovação Final',
            'AGUARDANDO INÍCIO DE COMPRAS' => 'Aguardando Início de Compras',
            'AGUARDANDO VALIDAÇÃO DO SOLICITANTE' => 'Aguardando Validação do Solicitante',
            'SOLICITAÇÃO VALIDADA PELO SOLICITANTE' => 'Solicitação Validada',
            'COTAÇÕES RECUSADAS PELO GESTOR' => 'Cotações Recusadas',
            'REPROVADO GESTOR DEPARTAMENTO' => 'Reprovado Gestor Departamento',
            'FINALIZADO' => 'Finalizado',
            'CANCELADA' => 'Cancelada',
            'APROVADO' => 'Aprovado',
         ]
      ];
   }
}

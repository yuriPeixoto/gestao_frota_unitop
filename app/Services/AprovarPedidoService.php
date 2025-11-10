<?php

namespace App\Services;

use App\Models\Cotacoes;
use App\Models\CotacoesItens;
use App\Models\Departamento;
use App\Models\Filial;
use App\Models\ItemSolicitacaoCompra;
use App\Models\PedidoCompra;
use App\Models\SolicitacaoCompra;
use App\Models\User;
use App\Models\VcotacoesMenosValor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AprovarPedidoService
{
   /**
    * Busca solicitações de compra pendentes de aprovação
    */
   public function buscarSolicitacoesPendentes(Request $request)
   {
      $query = SolicitacaoCompra::with(['solicitante', 'departamento', 'filial', 'aprovador'])
         ->whereIn('situacao_compra', [
            'AGUARDANDO APROVAÇÃO',
            'SOLICITAÇÃO VALIDADA PELO GESTOR'
         ])
         ->orderBy('id_solicitacoes_compras', 'desc');

      if ($request->filled('id_solicitacoes_compras')) {
         $query->where('id_solicitacoes_compras', $request->id_solicitacoes_compras);
      }

      if ($request->filled('data_inclusao')) {
         $query->where('data_inclusao', $request->data_inclusao);
      }

      if ($request->filled('id_departamento')) {
         $query->where('id_departamento', $request->id_departamento);
      }

      if ($request->filled('id_filial')) {
         $query->where('id_filial', $request->id_filial);
      }

      if ($request->filled('tipo_solicitacao')) {
         $query->where('tipo_solicitacao', $request->tipo_solicitacao);
      }

      return $query->paginate(30)->appends($request->query());
   }

   /**
    * Busca dados para filtros
    */
   public function getFilterData()
   {
      return [
         'id_solicitacoes_compras' => SolicitacaoCompra::select('id_solicitacoes_compras as label', 'id_solicitacoes_compras as value')
            ->orderBy('id_solicitacoes_compras')
            ->limit(30)
            ->distinct()
            ->get()
            ->toArray(),
         'departamentos' => Departamento::select('descricao_departamento as label', 'id_departamento as value')
            ->orderBy('descricao_departamento')
            ->limit(30)
            ->distinct()
            ->get()
            ->toArray(),
         'filiais' => Filial::select('name as label', 'id as value')
            ->orderBy('name')
            ->limit(30)
            ->distinct()
            ->get()
            ->toArray(),
      ];
   }

   /**
    * Busca solicitação com relacionamentos
    */
   public function buscarSolicitacaoCompleta($id)
   {
      return SolicitacaoCompra::with([
         'solicitante',
         'aprovador',
         'departamento',
         'filial',
         'filialEntrega',
         'filialFaturamento',
         'fornecedor',
         'itens.produto',
         'itens.servico',
         'logs'
      ])->findOrFail($id);
   }

   /**
    * Busca cotações e itens para uma solicitação
    */
   public function buscarCotacoesCompletas($id)
   {
      $cotacoesList = Cotacoes::where('id_solicitacoes_compras', $id)->with('fornecedor')->get();

      $cotacoesItens = collect();
      $cotacoesItensCompletos = collect();

      if ($cotacoesList->isNotEmpty()) {
         $cotacaoIds = $cotacoesList->pluck('id_cotacoes')->toArray();
         $cotacoesItens = CotacoesItens::whereIn('id_cotacao', $cotacaoIds)->get();

         $cotacoesItensCompletos = $cotacoesItens->map(function ($item) use ($cotacoesList) {
            $cotacao = $cotacoesList->firstWhere('id_cotacoes', $item->id_cotacao);

            return [
               'id_cotacao' => $item->id_cotacao,
               'id_produto' => $item->id_produto,
               'descricao_produto' => $item->descricao_produto ?? $item->produto->descricao_produto ?? 'N/A',
               'quantidade_solicitada' => $item->quantidade_solicitada ?? 0,
               'quantidade_fornecedor' => $item->quantidade_fornecedor ?? 0,
               'valorunitario' => $item->valorunitario ?? 0,
               'valor_item' => $item->valor_item ?? 0,
               'valor_desconto' => $item->valor_desconto ?? 0,
               'fornecedor' => $cotacao->fornecedor->nome_fornecedor ?? 'N/A',
               'data_entrega' => $cotacao->data_entrega ?? null,
               'condicao_pag' => $cotacao->condicao_pag ?? 'N/A'
            ];
         });
      }

      return [
         'cotacoesList' => $cotacoesList,
         'cotacoesItens' => $cotacoesItens,
         'cotacoesItensCompletos' => $cotacoesItensCompletos
      ];
   }

   /**
    * Busca dados para edição de cotação
    */
   public function buscarDadosEdicao($id)
   {
      $solicitacao = SolicitacaoCompra::where('id_solicitacoes_compras', $id)->firstOrFail();
      $itemSolicitacaoCompra = ItemSolicitacaoCompra::where('id_solicitacao_compra', $id)->get();
      $cotacoesList = Cotacoes::where('id_solicitacoes_compras', $id)->get();

      $cotacoesItens = collect();
      $cotacoesItensUnicos = collect();

      if ($cotacoesList->isNotEmpty()) {
         $cotacaoIds = $cotacoesList->pluck('id_cotacoes')->toArray();
         $cotacoesItens = CotacoesItens::whereIn('id_cotacao', $cotacaoIds)->get();

         $cotacoesItensUnicos = $cotacoesItens->groupBy('id_produto')->map(function ($group) {
            return [
               'id_produto' => $group->first()->id_produto,
               'descricao' => $group->first()->descricao ?? null,
               'total_cotacoes' => $group->count(),
               'cotacoes_ids' => $group->pluck('id_cotacao')->unique()->values()->toArray(),
            ];
         })->values();
      }

      return [
         'solicitacao' => $solicitacao,
         'itemSolicitacaoCompra' => $itemSolicitacaoCompra,
         'cotacoesList' => $cotacoesList,
         'cotacoesItens' => $cotacoesItens,
         'cotacoesItensUnicos' => $cotacoesItensUnicos
      ];
   }

   /**
    * Cancela uma solicitação de compra
    */
   public function cancelarSolicitacao($idSolicitacao)
   {
      DB::beginTransaction();

      try {
         $usuarioId = Auth::id();

         $solicitacao = SolicitacaoCompra::findOrFail($idSolicitacao);

         if ($solicitacao->situacao_compra === 'AGUARDANDO APROVAÇÃO') {
            $solicitacao->update(['situacao_compra' => 'INICIADA']);

            $solicitacao->registrarLog('INICIADA', $usuarioId, 'Solicitação de compra cancelada');
         }

         DB::commit();

         return ['success' => true, 'message' => 'Cotações recusadas!'];
      } catch (\Exception $e) {
         DB::rollBack();
         Log::error('Erro ao recusar cotação:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
         ]);
         return ['success' => false, 'message' => 'Erro ao recusar cotação: ' . $e->getMessage()];
      }
   }

   /**
    * Busca fornecedores para lista de itens
    */
   public function buscarFornecedorItens(array $itemIds)
   {
      if (empty($itemIds)) {
         return [];
      }

      $fornecedores = DB::table('cotacoesitens as ct')
         ->join('cotacoes as cc', 'cc.id_cotacoes', '=', 'ct.id_cotacao')
         ->whereIn('ct.id_cotacoes_itens', $itemIds)
         ->distinct()
         ->pluck('cc.id_fornecedor')
         ->toArray();

      return array_values(array_filter($fornecedores, function ($v) {
         return $v !== null && $v !== '';
      }));
   }

   /**
    * Busca fornecedor para um item específico
    */
   public function buscarFornecedorItem($itemId)
   {
      if (empty($itemId)) {
         return null;
      }

      return DB::table('cotacoesitens as ct')
         ->join('cotacoes as cc', 'cc.id_cotacoes', '=', 'ct.id_cotacao')
         ->where('ct.id_cotacoes_itens', $itemId)
         ->value('cc.id_fornecedor');
   }

   /**
    * Busca cotações com informações detalhadas
    */
   public function getCotacoes($id)
   {
      $todasCotacoes = Cotacoes::where('id_solicitacoes_compras', $id)
         ->with('fornecedor', 'itens')
         ->orderBy('id_cotacoes')
         ->get();

      if ($todasCotacoes->isEmpty()) {
         return [];
      }

      // Buscar ID da cotação vencedora
      $cotacaoVencedoraId = $this->buscarCotacaoVencedora($id);

      // Se há 3 ou menos cotações, mostrar todas sem duplicação
      // Se há mais de 3, usar a lógica de seleção
      if ($todasCotacoes->count() <= 3) {
         $cotacaoSelecionadas = $todasCotacoes;
      } else {
         $cotacaoSelecionadas = $this->buscarCotacoesSelecionadas($id, $todasCotacoes);
         // Remover duplicatas usando ID único
         $cotacaoSelecionadas = $cotacaoSelecionadas->unique('id_cotacoes');
      }

      $result = $cotacaoSelecionadas->filter()->map(function ($cotacao) use ($cotacaoVencedoraId) {
         $itensDetalhados = $cotacao->itens->map(function ($item) {
            return [
               'descricao' => $item->descricao_produto ?? 'N/A',
               'quantidade' => $item->quantidade_solicitada ?? 0,
               'valor_unitario' => $item->valorunitario ?? 0,
               'valor_desconto' => $item->valor_desconto ?? 0,
               'valor_bruto' => $item->valor_item ?? 0,
            ];
         });

         $valores = $cotacao->itens->sum('valor_item');
         $valoresDesconto = $cotacao->itens->sum('valor_desconto');

         return [
            'numero' => $cotacao->id_cotacoes,
            'itens' => $cotacao->itens->map(function ($item) {
               return $item->descricao_produto ?? 'N/A';
            })->join(', '),
            'itens_detalhados' => $itensDetalhados,
            'valores' => number_format($valores, 2, ',', '.'),
            'valoresDesconto' => number_format($valoresDesconto, 2, ',', '.'),
            'fornecedor' => $cotacao->fornecedor->nome_fornecedor ?? 'N/A',
            'is_vencedora' => $cotacao->id_cotacoes == $cotacaoVencedoraId,
         ];
      });

      return $result->values()->toArray();
   }

   /**
    * Busca cotações completas para modal
    */
   public function getCotacoesCompletas($id)
   {
      $cotacoes = Cotacoes::where('id_solicitacoes_compras', $id)
         ->with(['fornecedor', 'itens'])
         ->orderBy('id_cotacoes')
         ->get();

      if ($cotacoes->isEmpty()) {
         return [];
      }

      $resultado = [];

      foreach ($cotacoes as $cotacao) {
         foreach ($cotacao->itens as $item) {
            $resultado[] = [
               'id' => $item->id_cotacoes_itens ?? uniqid(),
               'id_cotacao' => $cotacao->id_cotacoes,
               'id_cotacao_item' => $item->id_cotacoes_itens ?? null,
               'codigo_cotacao' => $cotacao->id_cotacoes,
               'codigo_item' => $item->id_cotacoes_itens ?? $item->id_produto,
               'fornecedor' => $cotacao->nome_fornecedor ?? ($cotacao->fornecedor->nome_fornecedor ?? 'N/A'),
               'descricao_produto' => $item->descricao_produto ?? 'N/A',
               'unidade' => $item->descricao_unidade ?? 'UN',
               'quantidade_solicitada' => $item->quantidade_solicitada ?? 1,
               'quantidade_fornecedor' => $item->quantidade_fornecedor ?? 1,
               'valor_unitario' => $item->valorunitario ?? 0,
               'valor_item' => $item->valor_item ?? 0,
               'valor_desconto' => $item->valor_desconto ?? 0,
               'percentual_desconto' => $item->per_desconto_item ?? 0,
            ];
         }
      }

      return $resultado;
   }

   /**
    * Aprova cotação
    */
   public function aprovarCotacao(Request $request)
   {
      $tipoAprovacao = trim((string) $request->input('tipoAprovacao', ''));
      $idSolicitacao = $request->input('id_solicitacao_compras');

      if (!$idSolicitacao) {
         return ['success' => false, 'message' => 'id_solicitacao_compras é obrigatório.'];
      }

      $solicitacao = SolicitacaoCompra::findOrFail($idSolicitacao);
      $jaExistePedido = $this->jaExistePedido($idSolicitacao);

      if ($jaExistePedido) {
         return ['success' => false, 'message' => 'Já existe um pedido para esta solicitação.'];
      }

      $aprovador = Auth::id();
      $idUsuario = $solicitacao->id_solicitante;
      $filialEntrega = $request->input('filialEntrega');
      $filialFaturamento = $request->input('filialFaturamento');

      try {
         $resultado = $this->processarTipoAprovacao(
            $tipoAprovacao,
            $idSolicitacao,
            $aprovador,
            $filialEntrega,
            $filialFaturamento,
            $request
         );

         if ($resultado['success']) {
            $this->mudarStatusSolicitacao($idSolicitacao);
            $this->atualizarIdFilial($idSolicitacao);

            if ($idUsuario && empty($solicitacao->id_ordem_servico)) {
               $this->gerarRequisicaoPeca($idSolicitacao);
            }

            return ['success' => true, 'message' => 'Pedido emitido com sucesso.'];
         }

         return $resultado;
      } catch (\Exception $e) {
         Log::error('Erro ao atualizar solicitação de compra:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
         ]);
         return ['success' => false, 'message' => 'Erro ao atualizar solicitação de compra.'];
      }
   }

   /**
    * Valida cotações
    */
   public function validarCotacoes($id_solicitacao)
   {
      $id = (int) $id_solicitacao;

      Log::info('Iniciando validação de cotações', ['id_solicitacao' => $id]);

      try {
         $connection = DB::connection('pgsql');

         // Primeiro, vamos verificar se existem cotações para esta solicitação
         $cotacoesCount = $connection->selectOne(
            'SELECT COUNT(*) as total FROM cotacoes WHERE id_solicitacoes_compras = ?',
            [$id]
         );

         Log::info('Cotações encontradas', [
            'id_solicitacao' => $id,
            'total_cotacoes' => $cotacoesCount->total ?? 0
         ]);

         if (!$cotacoesCount || $cotacoesCount->total == 0) {
            Log::warning('Nenhuma cotação encontrada para validação', ['id_solicitacao' => $id]);
            return false;
         }

         $sql1 = "WITH dados_ AS (
                        SELECT
                            ct.id_cotacao
                        FROM
                            cotacoes cc
                        JOIN cotacoesitens ct ON cc.id_cotacoes = ct.id_cotacao
                        WHERE
                            cc.id_solicitacoes_compras = ?
                        GROUP BY
                            ct.id_cotacao,
                            cc.valor_total_desconto,
                            cc.valor_total
                        HAVING
                            COUNT(*) = COUNT(CASE WHEN ct.quantidade_solicitada  <= ct.quantidade_fornecedor THEN 1 END)
                        ORDER BY
                            CASE
                                WHEN cc.valor_total_desconto = 0 THEN SUM(cc.valor_total + cc.valor_total_desconto)
                                ELSE cc.valor_total_desconto
                            END
                    )
                    SELECT COUNT(*) AS id_cotacao FROM dados_;";

         $rows1 = $connection->select($sql1, [$id]);
         $retorno = 0;
         if (!empty($rows1) && isset($rows1[0]->id_cotacao)) {
            $retorno = (int) $rows1[0]->id_cotacao;
         }

         Log::info('Resultado da validação de cotações', [
            'id_solicitacao' => $id,
            'cotacoes_validas' => $retorno,
            'validacao_passou' => $retorno > 0
         ]);

         return $retorno > 0;
      } catch (\Exception $e) {
         Log::error('validarCotacoes error', [
            'id_solicitacao' => $id,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
         ]);
         return false;
      }
   }

   /**
    * Gera cotação
    */
   public function gerarCotacao(Request $request)
   {
      $logFile = storage_path('logs/debug_gerarCotacao.log');
      $logData = date('Y-m-d H:i:s') . " - INÍCIO gerarCotacao - " . json_encode([
         'method' => $request->method(),
         'input' => $request->all()
      ]) . "\n";
      file_put_contents($logFile, $logData, FILE_APPEND | LOCK_EX);

      $id_solicitacao_compra = $request->input('id_solicitacao_compra') ?: $request->input('id_solicitacao') ?: session('id_solicitacao_compra');
      $observacao_do_pedido = $request->input('observacao_do_pedido', session('observacao_do_pedido'));
      $aprovador = Auth::id();

      file_put_contents($logFile, date('Y-m-d H:i:s') . " - Parâmetros: id_solicitacao_compra=$id_solicitacao_compra, aprovador=$aprovador\n", FILE_APPEND | LOCK_EX);

      if ($observacao_do_pedido === 'NULL' || $observacao_do_pedido === null || $observacao_do_pedido === '') {
         $observacao_param = null;
      } else {
         $observacao_param = $observacao_do_pedido;
      }

      $isConfirmed = (int) $request->input('confirmAction', 0) === 1 || $request->filled('itens_selecionados');

      if (!$isConfirmed) {
         file_put_contents($logFile, date('Y-m-d H:i:s') . " - Retornando confirmação\n", FILE_APPEND | LOCK_EX);
         return [
            'confirm' => true,
            'message' => 'Tem certeza que deseja gerar o pedido de compras?'
         ];
      }

      try {
         $checked = $this->processarItensSelecionados($request);

         if (empty($checked) || !is_array($checked)) {
            return ['success' => false, 'message' => 'Selecione pelo menos 1 item das cotações.'];
         }

         $resultado = $this->processarCotacaoItens($checked, $observacao_param, $aprovador);

         if ($resultado['success']) {
            if ($id_solicitacao_compra) {
               $this->mudarStatusSolicitacao($id_solicitacao_compra);
            }
            return ['success' => true, 'message' => 'Todos os Pedidos foram gerados com sucesso.'];
         }

         return $resultado;
      } catch (\Exception $e) {
         Log::error('=== EXCEPTION em gerarCotacao ===', [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
         ]);
         return [
            'success' => false,
            'message' => 'Erro ao gerar cotações: ' . $e->getMessage(),
            'exception_details' => [
               'message' => $e->getMessage(),
               'file' => $e->getFile(),
               'line' => $e->getLine()
            ]
         ];
      }
   }

   // Métodos privados auxiliares

   private function calcularMenoresPorProduto($id)
   {
      $menoresPorProduto = [];
      $cotacaoMenosValor = VcotacoesMenosValor::where('id_solicitacoes_compras', $id)->first();

      if ($cotacaoMenosValor) {
         $fornecedorNomePadrao = $cotacaoMenosValor->nome_fornecedor ?? 'N/A';
         $valorComparar = $cotacaoMenosValor->valor_total ?? 0;
         $valorDesconto = $cotacaoMenosValor->valor_total_desconto ?? 0;

         $produtoKey = 'default';
         $menoresPorProduto[$produtoKey] = [
            'nome_fornecedor' => $fornecedorNomePadrao,
            'valor_total' => $valorComparar,
            'valor_total_desconto' => $valorDesconto,
         ];
      }

      return $menoresPorProduto;
   }

   private function buscarCotacoesSelecionadas($id, $todasCotacoes)
   {
      $cotacaoSelecionadas = collect();
      $idsAdicionados = []; // Track para evitar duplicatas

      $cotacao01 = $this->buscarIDCotacao01($id);
      if ($cotacao01 && !in_array($cotacao01, $idsAdicionados)) {
         $cotacao = $todasCotacoes->firstWhere('id_cotacoes', $cotacao01);
         if ($cotacao) {
            $cotacaoSelecionadas->push($cotacao);
            $idsAdicionados[] = $cotacao01;
         }
      }

      $cotacao02 = $this->buscarIDCotacao02($id);
      if ($cotacao02 && !in_array($cotacao02, $idsAdicionados)) {
         $cotacao = $todasCotacoes->firstWhere('id_cotacoes', $cotacao02);
         if ($cotacao) {
            $cotacaoSelecionadas->push($cotacao);
            $idsAdicionados[] = $cotacao02;
         }
      }

      $cotacao03 = $this->buscarIDCotacao03($id);
      if ($cotacao03 && !in_array($cotacao03, $idsAdicionados)) {
         $cotacao = $todasCotacoes->firstWhere('id_cotacoes', $cotacao03);
         if ($cotacao) {
            $cotacaoSelecionadas->push($cotacao);
            $idsAdicionados[] = $cotacao03;
         }
      }

      return $cotacaoSelecionadas;
   }

   private function getCotacaoMenorValor($menoresPorProduto)
   {
      $cotacaoMenosValorObj = [
         'cotacaoMenosValor' => [
            'nome_fornecedor' => 'N/A',
            'valor_total' => 0,
            'valor_total_desconto' => 0,
         ]
      ];

      if (!empty($menoresPorProduto)) {
         $menorValor = collect($menoresPorProduto)->sortBy('valor_total')->first();
         $cotacaoMenosValorObj = [
            'cotacaoMenosValor' => [
               'nome_fornecedor' => $menorValor['nome_fornecedor'] ?? 'N/A',
               'valor_total' => $menorValor['valor_total'] ?? 0,
               'valor_total_desconto' => $menorValor['valor_total_desconto'] ?? 0,
            ]
         ];
      }

      return $cotacaoMenosValorObj;
   }

   private function processarTipoAprovacao($tipoAprovacao, $idSolicitacao, $aprovador, $filialEntrega, $filialFaturamento, $request)
   {
      switch ($tipoAprovacao) {
         case 'menorValorFornecedor':
            return $this->processarMenorValorFornecedor($idSolicitacao, $aprovador, $filialEntrega, $filialFaturamento);

         case 'menorValorProdutos':
            return $this->processarMenorValorProdutos($idSolicitacao, $aprovador, $filialEntrega, $filialFaturamento);

         case 'selecionarCotacao':
            return $this->processarSelecionarCotacao($request, $idSolicitacao, $filialEntrega, $filialFaturamento);

         default:
            return ['success' => false, 'message' => 'Tipo de aprovação inválido.'];
      }
   }

   private function processarMenorValorFornecedor($idSolicitacao, $aprovador, $filialEntrega, $filialFaturamento)
   {
      Log::info('Iniciando processarMenorValorFornecedor', [
         'id_solicitacao' => $idSolicitacao,
         'aprovador' => $aprovador,
         'filial_entrega' => $filialEntrega,
         'filial_faturamento' => $filialFaturamento
      ]);

      if (empty($filialEntrega) || empty($filialFaturamento)) {
         Log::warning('Filiais não informadas', [
            'filial_entrega' => $filialEntrega,
            'filial_faturamento' => $filialFaturamento
         ]);
         return ['success' => false, 'message' => 'Filial de entrega e faturamento são obrigatórias.'];
      }

      $validarCotacao = $this->validarCotacoes($idSolicitacao);
      Log::info('Resultado da validação de cotações', ['validar_cotacao' => $validarCotacao]);

      if (!$validarCotacao) {
         return ['success' => false, 'message' => 'Cotações não passaram na validação. Verifique se todas as quantidades solicitadas estão disponíveis.'];
      }

      $filialEntregaId = Filial::where('name', $filialEntrega)->value('id');
      $filialFaturamentoId = Filial::where('name', $filialFaturamento)->value('id');

      if (!$filialEntregaId || !$filialFaturamentoId) {
         Log::error('Filiais não encontradas no banco', [
            'filial_entrega' => $filialEntrega,
            'filial_entrega_id' => $filialEntregaId,
            'filial_faturamento' => $filialFaturamento,
            'filial_faturamento_id' => $filialFaturamentoId
         ]);
         return ['success' => false, 'message' => 'Filiais informadas não foram encontradas no sistema.'];
      }

      $gerarPedidoMenor = $this->gerarPedidoMenorCotacao($idSolicitacao, $aprovador, $filialEntregaId, $filialFaturamentoId);

      if ($gerarPedidoMenor) {
         Log::info('Pedido gerado com sucesso para menor valor por fornecedor');
         return ['success' => true];
      }

      return ['success' => false, 'message' => 'Erro ao gerar pedido no banco de dados. Verifique os logs para mais detalhes.'];
   }

   private function processarMenorValorProdutos($idSolicitacao, $aprovador, $filialEntrega, $filialFaturamento)
   {
      if (!empty($filialEntrega) && !empty($filialFaturamento)) {
         $filialEntregaId = Filial::where('name', $filialEntrega)->value('id');
         $filialFaturamentoId = Filial::where('name', $filialFaturamento)->value('id');

         $gerarPedidoMenor = $this->gerarPedidoMenorValorItem($idSolicitacao, $aprovador, $filialEntregaId, $filialFaturamentoId);
         if ($gerarPedidoMenor) {
            return ['success' => true];
         }
      }

      return ['success' => false, 'message' => 'Erro ao processar menor valor por produtos.'];
   }

   private function processarSelecionarCotacao($request, $idSolicitacao, $filialEntrega, $filialFaturamento)
   {
      $observacao = $request->input('observacao_pedido');
      foreach ($request->input('selecionar_dados_cotacao') as $object) {
         if (!empty($filialEntrega) && !empty($filialFaturamento)) {
            $this->gerarPedidoLivre($object, $observacao, $filialEntrega, $filialFaturamento);
         }
      }

      return ['success' => true];
   }

   private function processarItensSelecionados($request)
   {
      $checked = $request->input('builder_datagrid_check', []);
      if (empty($checked) && $request->filled('itens_selecionados')) {
         $itensSelecionados = $request->input('itens_selecionados');
         if (is_array($itensSelecionados)) {
            $mapped = array_map(function ($it) {
               if (is_array($it) || is_object($it)) {
                  $it = (array) $it;
                  return $it['id_cotacao_item'] ?? $it['id_cotacao_itens'] ?? $it['id_cotacoes_itens'] ?? ($it['id'] ?? null);
               }
               return $it;
            }, $itensSelecionados);
            $checked = array_values(array_filter($mapped, function ($v) {
               return $v !== null && $v !== '';
            }));
         }
      }

      return $checked;
   }

   private function processarCotacaoItens($checked, $observacao_param, $aprovador)
   {
      $id_fornecedores = $this->buscarFornecedorItens($checked);
      $id_fornecedores['id_primeiro_fornecedor'] = $id_fornecedores[0] ?? 0;
      $id_fornecedores['id_segundo_fornecedor'] = $id_fornecedores[1] ?? 0;
      $id_fornecedores['id_terceiro_fornecedor'] = $id_fornecedores[2] ?? 0;

      $itens_primeiro_fornecedor = [];
      $itens_segundo_fornecedor = [];
      $itens_terceiro_fornecedor = [];

      foreach ($checked as $check_id) {
         $fornecedorItem = $this->buscarFornecedorItem($check_id);
         if ($fornecedorItem == $id_fornecedores['id_primeiro_fornecedor']) {
            $itens_primeiro_fornecedor[] = $check_id;
         } elseif ($fornecedorItem == $id_fornecedores['id_segundo_fornecedor']) {
            $itens_segundo_fornecedor[] = $check_id;
         } elseif ($fornecedorItem == $id_fornecedores['id_terceiro_fornecedor']) {
            $itens_terceiro_fornecedor[] = $check_id;
         }
      }

      $conn = DB::connection('pgsql');
      $retorno = [];

      $callFc = function (array $items) use ($conn, $observacao_param, $aprovador) {
         if (empty($items)) {
            return ['value' => null, 'rows' => []];
         }
         $items_string = '{' . implode(',', $items) . '}';

         $rows = $conn->select('SELECT * FROM fc_gerar_pedidos_itens_cotacoes(?, ?, ?)', [$items_string, $observacao_param, $aprovador]);

         if (!empty($rows)) {
            $first = $rows[0];
            $vars = get_object_vars($first);
            $value = reset($vars);
            return ['value' => $value, 'rows' => $rows];
         }
         return ['value' => null, 'rows' => $rows ?? []];
      };

      if (count($itens_primeiro_fornecedor) > 0) {
         $retorno['primeiro_fornecedor'] = $callFc($itens_primeiro_fornecedor);
      }

      if (count($itens_segundo_fornecedor) > 0) {
         $retorno['segundo_fornecedor'] = $callFc($itens_segundo_fornecedor);
      }

      if (count($itens_terceiro_fornecedor) > 0) {
         $retorno['terceiro_fornecedor'] = $callFc($itens_terceiro_fornecedor);
      }

      $containsZero = false;
      foreach ($retorno as $r) {
         $val = null;
         if (is_array($r) && array_key_exists('value', $r)) {
            $val = $r['value'];
         } else {
            $val = $r;
         }
         if ((string) $val === '0' || $val === 0) {
            $containsZero = true;
            break;
         }
      }

      if (!$containsZero && !empty($retorno)) {
         return ['success' => true, 'message' => 'Todos os Pedidos foram gerados com sucesso.'];
      }

      return ['success' => false, 'message' => 'Erro ao gerar pedidos.', 'debug' => $retorno];
   }

   private function buscarIDCotacao01($id_solicitacao_compras)
   {
      $cotacao = Cotacoes::where('id_solicitacoes_compras', $id_solicitacao_compras)
         ->orderByRaw('(valor_total - valor_total_desconto) ASC')
         ->first();

      return $cotacao ? $cotacao->id_cotacoes : null;
   }

   private function buscarIDCotacao02($id_solicitacao_compras)
   {
      $cotacao = Cotacoes::where('id_solicitacoes_compras', $id_solicitacao_compras)
         ->orderByRaw('(valor_total - valor_total_desconto) ASC')
         ->skip(1)
         ->first();

      return $cotacao ? $cotacao->id_cotacoes : null;
   }

   private function buscarIDCotacao03($id_solicitacao_compras)
   {
      $cotacao = Cotacoes::where('id_solicitacoes_compras', $id_solicitacao_compras)
         ->orderByRaw('(valor_total - valor_total_desconto) ASC')
         ->skip(2)
         ->first();

      return $cotacao ? $cotacao->id_cotacoes : 1;
   }

   private function buscarCotacaoVencedora($id_solicitacao_compras)
   {
      $cotacaoMenosValor = VcotacoesMenosValor::where('id_solicitacoes_compras', $id_solicitacao_compras)
         ->orderBy('valor_total_desconto', 'ASC')
         ->first();

      Log::info('buscarCotacaoVencedora', [
         'id_solicitacao_compras' => $id_solicitacao_compras,
         'cotacao_menos_valor' => $cotacaoMenosValor ?? null
      ]);

      return $cotacaoMenosValor ? $cotacaoMenosValor->id_cotacoes : null;
   }

   private function jaExistePedido($id_solicitacoes_compra)
   {
      $pedido = PedidoCompra::where('id_solicitacoes_compras', $id_solicitacoes_compra)->first();
      return $pedido ? true : false; // Corrigido: retorna true se existe pedido, false se não existe
   }

   private function mudarStatusSolicitacao($id_solicitacao)
   {
      try {
         DB::beginTransaction();

         $solicitacao = SolicitacaoCompra::find($id_solicitacao);

         if (!$solicitacao) {
            Log::warning('Solicitação não encontrada para mudar status', ['id_solicitacao' => $id_solicitacao]);
            DB::rollBack();
            return;
         }

         $solicitacao->update([
            'situacao_compra' => 'FINALIZADO',
            'data_finalizada' => now()
         ]);

         // Registrar log da finalização
         $solicitacao->registrarLog(
            'FINALIZADO',
            Auth::id(),
            'Solicitação de compra finalizada - Pedido aprovado e gerado'
         );

         DB::commit();
      } catch (\Exception $e) {
         Log::error('Erro ao mudar status da solicitação de compra:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
         ]);
         DB::rollBack();
      }
   }

   private function atualizarIdFilial($id_solcompras)
   {
      try {
         DB::beginTransaction();

         $filialId = SolicitacaoCompra::where('id_solicitacoes_compras', $id_solcompras)->value('id_filial');

         if ($filialId !== null) {
            PedidoCompra::where('id_solicitacoes_compras', $id_solcompras)
               ->update(['id_filial' => $filialId]);
         } else {
            Log::warning('atualizarIdFilial: id_filial não encontrado para solicitação', ['id_solicitacao' => $id_solcompras]);
         }

         DB::commit();
      } catch (\Throwable $th) {
         Log::error('Erro ao atualizar id_filial do pedido de compra:', [
            'message' => $th->getMessage(),
            'trace' => $th->getTraceAsString()
         ]);
         DB::rollBack();
      }
   }

   private function gerarRequisicaoPeca($id_solcompras)
   {
      try {
         $rows = DB::connection('pgsql')->select(
            'SELECT * FROM fc_gerar_requisicao_pecas_auto(?)',
            [$id_solcompras]
         );

         if (!empty($rows)) {
            $first = $rows[0];
            $vars = get_object_vars($first);
            $value = reset($vars);
            return intval($value) === 1;
         }
      } catch (\Exception $e) {
         Log::error('Erro ao gerar requisição de peça:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
         ]);
      }
   }

   private function gerarPedidoMenorCotacao($id_solicitacao, $aprovador, $filialentrega, $filialfaturamento)
   {
      try {
         $rows = DB::connection('pgsql')->select(
            'SELECT * FROM fc_inserir_cotacao_menor_valor(?, ?, ?, ?)',
            [$id_solicitacao, $aprovador, $filialentrega, $filialfaturamento]
         );

         // Se a função PostgreSQL executou sem erro, consideramos sucesso
         Log::info('gerarPedidoMenorCotacao executado com sucesso', [
            'id_solicitacao' => $id_solicitacao,
            'aprovador' => $aprovador,
            'rows_count' => count($rows)
         ]);

         return true;
      } catch (\Exception $e) {
         Log::error('gerarPedidoMenorCotacao error', [
            'id_solicitacao' => $id_solicitacao,
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
         ]);
         return false;
      }
   }

   private function gerarPedidoMenorValorItem($id_solicitacao, $aprovador, $filialentrega, $filialfaturamento)
   {
      try {
         $rows = DB::connection('pgsql')->select(
            'SELECT * FROM fc_inserir_cotacao_menor_valor_produtos_(?, ?, ?, ?)',
            [$id_solicitacao, $aprovador, $filialentrega, $filialfaturamento]
         );

         if (!empty($rows)) {
            $first = $rows[0];
            $vars = get_object_vars($first);
            $value = reset($vars);
            $clean = str_replace(['{', '}'], '', (string) $value);
            return $clean;
         }

         return null;
      } catch (\Exception $e) {
         Log::error('GerarPedidoMenoValorIten error', ['exception' => $e->getMessage()]);
         return null;
      }
   }

   private function gerarPedidoLivre($id_solicitacao, $observacao, $filialentrega, $filialfaturamento)
   {
      try {
         $rows = DB::connection('pgsql')->select(
            'SELECT * FROM fc_inserir_pedido_cotacao(?, ?, ?, ?)',
            [$id_solicitacao, $observacao, $filialentrega, $filialfaturamento]
         );

         if (!empty($rows)) {
            $first = $rows[0];
            $vars = get_object_vars($first);
            $value = reset($vars);
            return intval($value) === 1;
         }

         return false;
      } catch (\Exception $e) {
         Log::error('GerarPedidoLivre error', ['exception' => $e->getMessage()]);
         return false;
      }
   }
}

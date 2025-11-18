<?php

namespace App\Modules\Compras\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cotacoes;
use App\Models\CotacoesItens;
use App\Modules\Configuracoes\Models\Departamento;
use App\Modules\Configuracoes\Models\Filial;
use App\Modules\Compras\Models\PedidoCompra;
use App\Modules\Compras\Models\SolicitacaoCompra;
use App\Modules\Configuracoes\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ValidarCotacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $query = SolicitacaoCompra::with(['solicitante', 'departamento', 'filial', 'aprovador'])
                ->whereIn('situacao_compra', [
                    'AGUARDANDO VALIDAÇÃO DO SOLICITANTE',
                    'AGUARDANDO APROVAÇÃO',
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

            $solicitacoes = $query->paginate(30)
                ->appends($request->query());

            $filterData = $this->getFilterData();

            $usuarios = User::select('id as value', 'name as label')->orderBy('name')->get();

            return view('admin.compras.validarcotacoes.index', array_merge(
                compact('solicitacoes'),
                $filterData,
            ));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar listagem de validação de cotações:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Retorne uma view de erro ou mensagem amigável
            return back()->with('error', 'Erro ao carregar validação de cotações.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $solicitacao = SolicitacaoCompra::with(['itens.produto', 'itens.servico'])->findOrFail($id);

        $cotacoesList = Cotacoes::where('id_solicitacoes_compras', $id)->with('fornecedor')->get();

        // Buscar itens de cotações para todas as cotações da solicitação
        $cotacoesItens = collect();
        $cotacoesItensCompletos = collect();
        if ($cotacoesList->isNotEmpty()) {
            $cotacaoIds = $cotacoesList->pluck('id_cotacoes')->toArray();
            $cotacoesItens = CotacoesItens::whereIn('id_cotacao', $cotacaoIds)->get();

            // Criar versão completa com dados das cotações e fornecedores
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

        return view('admin.compras.validarcotacoes.edit', compact(
            'solicitacao',
            'cotacoesItensCompletos',
            'cotacoesList'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getFilterData()
    {

        return [
            'id_solicitacoes_compras' => SolicitacaoCompra::select('id_solicitacoes_compras as label', 'id_solicitacoes_compras as value')->orderBy('id_solicitacoes_compras')->limit(30)->distinct()->get()->toArray(),
            'departamentos' => Departamento::select('descricao_departamento as label', 'descricao_departamento as value')->orderBy('descricao_departamento')->limit(30)->distinct()->get()->toArray(),
            'filiais' => Filial::select('name as label', 'id as value')->orderBy('name')->limit(30)->distinct()->get()->toArray(),
        ];
    }

    public function getCotacoes($id)
    {
        // Buscar todas as cotações da solicitação
        $todasCotacoes = Cotacoes::where('id_solicitacoes_compras', $id)
            ->with('fornecedor', 'itens')
            ->orderBy('id_cotacoes')
            ->get();

        if ($todasCotacoes->isEmpty()) {
            return response()->json([]);
        }

        // Aplicar lógica específica para selecionar as 3 cotações
        $cotacaoSelecionadas = collect();

        // Cotação 01: Primeira cotação (menor ID)
        $cotacao01 = $this->buscarIDCotacao01($id);
        if ($cotacao01) {
            $cotacaoSelecionadas->push($todasCotacoes->firstWhere('id_cotacoes', $cotacao01));
        }

        // Cotação 02: Cotação do meio (não é primeira nem última)
        $cotacao02 = $this->buscarIDCotacao02($id);
        if ($cotacao02) {
            $cotacaoEncontrada = $todasCotacoes->firstWhere('id_cotacoes', $cotacao02);
            if ($cotacaoEncontrada) {
                $cotacaoSelecionadas->push($cotacaoEncontrada);
            }
        }

        // Cotação 03: Última cotação (maior ID)
        $cotacao03 = $this->buscarIDCotacao03($id);
        if ($cotacao03) {
            $cotacaoEncontrada = $todasCotacoes->firstWhere('id_cotacoes', $cotacao03);
            if ($cotacaoEncontrada) {
                $cotacaoSelecionadas->push($cotacaoEncontrada);
            }
        }

        // Filtrar cotações nulas e mapear para o formato esperado
        $result = $cotacaoSelecionadas->filter()->map(function ($cotacao) {
            $itensDetalhados = $cotacao->itens->map(function ($item) {
                return [
                    'descricao' => $item->descricao_produto ?? 'N/A',
                    'quantidade' => $item->quantidade_solicitada ?? 0,
                    'valor_unitario' => $item->valorunitario ?? 0,
                    'valor_bruto' => $item->valor_item ?? 0,
                    'valor_desconto' => $item->valor_desconto ?? 0
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
                'fornecedor' => $cotacao->fornecedor->nome_fornecedor ?? 'N/A'
            ];
        });

        return response()->json($result->values());
    }

    /**
     * Buscar ID da primeira cotação (menor ID)
     */
    private function buscarIDCotacao01($id_solicitacao_compras)
    {
        $cotacao = Cotacoes::where('id_solicitacoes_compras', $id_solicitacao_compras)
            ->orderByRaw('(valor_total - valor_total_desconto) ASC')
            ->first();

        return $cotacao ? $cotacao->id_cotacoes : null;
    }

    /**
     * Buscar ID da cotação do meio (não é primeira nem última)
     */
    private function buscarIDCotacao02($id_solicitacao_compras)
    {
        $cotacao = Cotacoes::where('id_solicitacoes_compras', $id_solicitacao_compras)
            ->orderByRaw('(valor_total - valor_total_desconto) ASC')
            ->skip(1)
            ->first();

        return $cotacao ? $cotacao->id_cotacoes : null;
    }

    /**
     * Buscar ID da última cotação (maior ID, mas não a primeira)
     */
    private function buscarIDCotacao03($id_solicitacao_compras)
    {
        $cotacao = Cotacoes::where('id_solicitacoes_compras', $id_solicitacao_compras)
            ->orderByRaw('(valor_total - valor_total_desconto) ASC')
            ->skip(2)
            ->first();

        return $cotacao ? $cotacao->id_cotacoes : 1;
    }

    /**
     * Validar cotação - equivalente ao onAction do Adianti
     */
    public function validarCotacao(Request $request)
    {
        try {
            $id_solicitacao_compras = $request->input('id_solicitacao_compras');
            $observacao = $request->input('observacao');

            if (!empty($id_solicitacao_compras)) {
                $aprovador = Auth::id();
                $id_usuario = $this->buscarIdSolicitante($id_solicitacao_compras);

                $this->mudarStatusSolicitacao($id_solicitacao_compras, $observacao);
                $this->enviarMapaCotacao($id_solicitacao_compras, $aprovador);

                return redirect()
                    ->route('admin.compras.validarcotacoes.index')
                    ->with('success', 'Cotação validada! Mapa de cotação gerado.');
            } else {
                return redirect()
                    ->route('admin.compras.validarcotacoes.edit', $id_solicitacao_compras)
                    ->with('error', 'ID da solicitação é obrigatório.');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao validar cotação:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erro ao validar cotação: ' . $e->getMessage());
        }
    }

    /**
     * Recusar cotação - equivalente ao onRecusarCotacao do Adianti
     */
    public function recusarCotacao(Request $request)
    {
        try {
            $id_solicitacao_compras = $request->input('id_solicitacao_compras');
            $observacao = $request->input('observacao');

            if (!empty($observacao)) {
                $aprovador = Auth::id();
                $id_usuario = $this->buscarIdSolicitante($id_solicitacao_compras);

                $this->mudarStatusSolicitacaoNaovalidado($id_solicitacao_compras, $observacao);

                return redirect()
                    ->route('admin.compras.validarcotacoes.index')
                    ->with('success', 'Cotações recusadas!');
            } else {
                return back()->with('error', 'Por favor, justificar a ação desejada!');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao recusar cotação:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erro ao recusar cotação: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar cotação - equivalente ao onCancelar do Adianti
     */
    public function cancelarCotacao(Request $request)
    {
        try {
            $id_solicitacao_compras = $request->input('id_solicitacao_compras');

            if (!empty($id_solicitacao_compras)) {
                DB::beginTransaction();
                $solicitacao = SolicitacaoCompra::find($id_solicitacao_compras);

                $usuarioId = Auth::user()->id;

                if ($solicitacao && $solicitacao->situacao_compra == 'AGUARDANDO VALIDAÇÃO DO SOLICITANTE') {
                    if (!empty($solicitacao->id_ordem_servico)) {
                        // Executar função de reprovar compra de serviços
                        DB::select("SELECT * FROM fc_reprovar_compra_servicos(?)", [$id_solicitacao_compras]);
                    }

                    // Cancelar a solicitação
                    $solicitacao->update([
                        'situacao_compra' => 'REPROVADO GESTOR DEPARTAMENTO',
                        'aprovado_reprovado' => false,
                        'is_cancelada' => true
                    ]);

                    $solicitacao->registrarLog(
                        'REPROVADO GESTOR DEPARTAMENTO',
                        $usuarioId,
                        'Solicitação cancelada pelo gestor do departamento.'
                    );

                    DB::commit();

                    return redirect()
                        ->route('admin.compras.validarcotacoes.index')
                        ->with('success', 'Cotação cancelada com sucesso!');
                }
            }

            return back()->with('error', 'Não foi possível cancelar a cotação.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao cancelar cotação:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erro ao cancelar cotação: ' . $e->getMessage());
        }
    }

    /**
     * Mudar status da solicitação para validada
     */
    private function mudarStatusSolicitacao($id_solicitacao, $observacao)
    {
        try {
            DB::beginTransaction();

            $usuarioId = Auth::user()->id;

            $solicitacao = SolicitacaoCompra::where('id_solicitacoes_compras', $id_solicitacao)->first();

            $solicitacao->update([
                'situacao_compra' => 'SOLICITAÇÃO VALIDADA PELO GESTOR',
                'aprovado_reprovado' => true,
                'observacao' => $observacao
            ]);

            $solicitacao->registrarLog(
                'SOLICITAÇÃO VALIDADA PELO GESTOR',
                $usuarioId,
                'Solicitação validada pelo gestor do departamento.',
            );


            Cotacoes::where('id_solicitacoes_compras', $id_solicitacao)->update([
                'aprovado_recusado' => true
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            Log::error('Erro ao mudar status da solicitação de compra:', [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            DB::rollBack();
        }
    }

    /**
     * Mudar status da solicitação para não validada
     */
    private function mudarStatusSolicitacaoNaovalidado($id_solicitacao, $observacao)
    {
        try {
            DB::beginTransaction();

            $usuarioId = Auth::user()->id;

            $solicitacao = SolicitacaoCompra::where('id_solicitacoes_compras', $id_solicitacao)->first();

            $solicitacao->update([
                'situacao_compra' => 'COTAÇÕES RECUSADAS PELO GESTOR',
                'aprovado_reprovado' => false,
                'observacao' => $observacao
            ]);

            $solicitacao->registrarLog(
                'COTAÇÕES RECUSADAS PELO GESTOR',
                $usuarioId,
                $observacao
            );

            DB::table('cotacoes')
                ->where('id_solicitacoes_compras', $id_solicitacao)
                ->update([
                    'aprovado_recusado' => false
                ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao mudar status da solicitação para não validada:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-throw a exceção para ser tratada no chamador
        }
    }

    /**
     * Buscar ID do solicitante
     */
    private function buscarIdSolicitante($id_solicitacao_compras)
    {
        $solicitacao = DB::table('solicitacoescompras')
            ->select('id_solicitante')
            ->where('id_solicitacoes_compras', $id_solicitacao_compras)
            ->first();

        return $solicitacao ? $solicitacao->id_solicitante : null;
    }

    /**
     * Enviar mapa de cotação
     */
    private function enviarMapaCotacao($idSolicitacoes, $aprovador)
    {
        // Buscar dados do aprovador
        $usuario = DB::table('v_usuarios_carvalima')
            ->select('name', 'id')
            ->where('id', $aprovador)
            ->first();

        if ($usuario) {
            $telefone = DB::table('usuarios_mensagem')
                ->select('telefone_user')
                ->where('id_user', $usuario->id)
                ->first();

            // Atualizar status da solicitação
            DB::table('solicitacoescompras')
                ->where('id_solicitacoes_compras', $idSolicitacoes)
                ->update([
                    'situacao_compra' => 'AGUARDANDO APROVAÇÃO',
                    'id_validador_cotacao' => $aprovador
                ]);

            // Verificar se há ordem de serviço
            $ordem_servico = DB::table('solicitacoescompras')
                ->select('id_ordem_servico')
                ->where('id_solicitacoes_compras', $idSolicitacoes)
                ->first();

            if (!empty($ordem_servico->id_ordem_servico)) {
                // Atualizar status das peças da ordem de serviço
                $ordem_pecas = DB::select("
                    SELECT oss.id_ordem_servico_pecas
                    FROM solicitacoescompras s
                    JOIN ordem_servico os ON os.id_ordem_servico = s.id_ordem_servico
                    JOIN ordem_servico_pecas oss ON oss.id_ordem_servico = os.id_ordem_servico
                    JOIN itenssolicitacoescompras sc ON sc.id_solicitacao_compra = s.id_solicitacoes_compras AND oss.id_produto = sc.id_produto
                    WHERE s.id_solicitacoes_compras = ?", [$idSolicitacoes]);

                if ($ordem_pecas) {
                    $ids_pecas = collect($ordem_pecas)->pluck('id_ordem_servico_pecas')->toArray();

                    DB::table('ordem_servico_pecas')
                        ->whereIn('id_ordem_servico_pecas', $ids_pecas)
                        ->update(['situacao_pecas' => 'AGUARDANDO APROVAÇÃO DE COMPRA']);
                }
            }

            // Buscar aprovadores baseado no valor
            $query = "
                WITH valor AS
                (
                    SELECT
                        SUM(COALESCE(cts.valor_item, 1)) AS valor,
                        1 AS id
                    FROM solicitacoescompras sc
                    INNER JOIN cotacoes ct ON ct.id_solicitacoes_compras = sc.id_solicitacoes_compras
                    INNER JOIN cotacoesitens cts ON cts.id_cotacao = ct.id_cotacoes
                    WHERE sc.id_solicitacoes_compras = $idSolicitacoes
                ),
                quantidade AS
                (
                    SELECT
                        COUNT(ct.id_cotacoes) AS quantidade,
                        1 AS id
                    FROM solicitacoescompras sc
                    INNER JOIN cotacoes ct ON ct.id_solicitacoes_compras = sc.id_solicitacoes_compras
                    WHERE sc.id_solicitacoes_compras = $idSolicitacoes
                )
                SELECT DISTINCT
                   ap.id_usuario,
                   va.name,
                  (REPLACE((REPLACE((REPLACE((REPLACE(ap.telefone,' ','')),'-','')),'(','')),')','')) AS telefone,
                   ap.valor_aprovacao
                FROM aprovadorespedidos ap
                   INNER JOIN v_usuarios va ON va.id = ap.id_usuario
                WHERE ap.id_usuario IN (10,19,28,44,1)
                AND (ap.tipo_solicitacao_compras IS TRUE
                OR ap.tipo_gerencial IS TRUE)
                AND
                (
                SELECT
                    COALESCE(v.valor, 1) / NULLIF(COALESCE(q.quantidade, 1), 0) AS retorno_
                FROM valor v
                INNER JOIN quantidade q ON q.id = v.id
                ) BETWEEN ap.valor_aprovacao AND ap.valor_aprovacao_final
                ORDER BY
                   ap.valor_aprovacao
                ASC";

            $aprovadores = DB::select($query);

            foreach ($aprovadores as $aprovador_item) {
                $idUser = $aprovador_item->id_usuario;
                $nome = $aprovador_item->name;
                $telefone = $aprovador_item->telefone;

                if (!empty($telefone) && !empty($nome)) {
                    $texto = "*Atenção:* A solicitação de compras n° $idSolicitacoes está esperando sua aprovação.\n"
                        . "[Abrir listagem de pedidos]\n https://carvalima.unitopconsultoria.com.br/index.php?class=AprovarPedidoList&method=onAbriMapa&key=$idSolicitacoes&id_solicitacoes_compras=$idSolicitacoes" . "\n";

                    // Aqui você pode implementar o envio de WhatsApp se necessário
                    // IntegracaoServiceWhatssapCarvalima::enviarMensagem($texto, $nome, $telefone);
                }

                // Registrar notificação no sistema
                // SystemNotification::register($idUser, 'Cotação', 'Orçamento Aguardando Aprovação', ...);
            }
        }
    }
}

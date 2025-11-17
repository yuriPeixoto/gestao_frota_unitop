<?php

namespace App\Modules\Compras\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SolicitacaoCompra;
use App\Models\ItemSolicitacaoCompra;
use App\Models\Departamento;
use App\Models\Fornecedor;
use App\Models\GrupoDespesa;
use App\Models\VFilial;
use App\Models\Produto;
use App\Models\UnidadeProduto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\SolicitacaoCompraService;
use App\Services\AprovacaoService;
use App\Services\CancelamentoService;
use App\Services\DesmembramentoService;
use App\Traits\FilterTraitSolicitacao;
use App\Traits\ItemTraitSolicitacao;

class SolicitacaoCompraController extends Controller
{
    use AuthorizesRequests, FilterTraitSolicitacao, ItemTraitSolicitacao;

    protected $solicitacaoService;
    protected $aprovacaoService;
    protected $cancelamentoService;
    protected $desmembramentoService;

    public function __construct(
        SolicitacaoCompraService $solicitacaoService,
        AprovacaoService $aprovacaoService,
        CancelamentoService $cancelamentoService,
        DesmembramentoService $desmembramentoService
    ) {
        $this->solicitacaoService = $solicitacaoService;
        $this->aprovacaoService = $aprovacaoService;
        $this->cancelamentoService = $cancelamentoService;
        $this->desmembramentoService = $desmembramentoService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // PRIMEIRO: Verificar autorização ANTES de qualquer consulta
        $this->authorize('viewAny', SolicitacaoCompra::class);

        // Obter parâmetros de filtro
        $situacaoCompra = $request->input('situacao_compra');
        $departamentoId = $request->input('departamento_id');
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        $filialId = $request->input('filial_id');
        $termo = $request->input('termo');

        $query = SolicitacaoCompra::with(['solicitante', 'departamento', 'filial', 'aprovador']);

        $query = $this->applyFilters($query, $request);
        $query->orderBy('data_inclusao', 'desc');

        $solicitacoes = $query->paginate(10)->appends(request()->query());

        $filterData = $this->getFilterData();

        return view('admin.compras.solicitacoes.index', array_merge(
            compact('solicitacoes', 'situacaoCompra', 'departamentoId', 'dataInicio', 'dataFim', 'filialId', 'termo'),
            $filterData,
        ));
    }


    public function listarPerUser(Request $request)
    {

        $user = Auth::user()->id_departamento;

        // Obter parâmetros de filtro
        $situacaoCompra = $request->input('situacao_compra');
        $departamentoId = $request->input('departamento_id');
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        $filialId = $request->input('filial_id');
        $termo = $request->input('termo');

        $query = SolicitacaoCompra::with(['solicitante', 'departamento', 'filial', 'aprovador'])
            ->where('id_departamento', $user);

        $query = $this->applyFilters($query, $request);
        $query->orderBy('data_inclusao', 'desc');

        $solicitacoes = $query->paginate(10)->appends(request()->query());
        $filterData = $this->getFilterData();

        return view('admin.compras.solicitacoes.index', array_merge(
            compact('solicitacoes', 'situacaoCompra', 'departamentoId', 'dataInicio', 'dataFim', 'filialId', 'termo'),
            $filterData,
        ));
    }

    public function listarPendentes(Request $request)
    {

        // Obter parâmetros de filtro
        $situacaoCompra = $request->input('situacao_compra');
        $departamentoId = $request->input('departamento_id');
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        $filialId = $request->input('filial_id');
        $termo = $request->input('termo');

        $query = SolicitacaoCompra::with(['solicitante', 'departamento', 'filial', 'aprovador'])
            ->where('situacao_compra', 'AGUARDANDO APROVAÇÃO DO GESTOR DEPARTAMENTO');

        if (Auth::user()->cannot('visualizar_solicitacao_compra') && Auth::user()->can('criar_solicitacao_compra')) {
            $query->where('id_solicitante', Auth::id());
        }

        $query = $this->applyFilters($query, $request);
        $query->orderBy('data_inclusao', 'desc');

        $solicitacoes = $query->paginate(10)->appends(request()->query());
        $filterData = $this->getFilterData();

        return view('admin.compras.solicitacoes.index', array_merge(
            compact('solicitacoes', 'situacaoCompra', 'departamentoId', 'dataInicio', 'dataFim', 'filialId', 'termo'),
            $filterData,
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        try {

            $this->authorize('create', SolicitacaoCompra::class);

            $departamentos = Departamento::where('ativo', true)->orderBy('descricao_departamento')->get();
            $filiais = VFilial::select('name as label', 'id as value')->orderBy('name')->get()->toArray();
            $fornecedores = Fornecedor::where('is_ativo', true)->orderBy('nome_fornecedor')->limit(30)->get();
            $grupoDespesa = GrupoDespesa::select('id_grupo_despesa as value', 'descricao_grupo as label')->orderBy('descricao_grupo')->get();


            return view('admin.compras.solicitacoes.create', [
                'departamentos' => $departamentos,
                'filiais' => $filiais,
                'fornecedores' => $fornecedores,
                'servicos' => $this->getServicos(),
                'servicosDescricao' => $this->getServicoDescricao(),
                'produtos' => $this->getProdutos(),
                'produtosDescricao' => $this->getProdutoDescricao(),
                'unidadesDescricao' => $this->getUnidadeProduto(),
                'tamanhoMaximoBytes' => 10 * 1024 * 1024,
                'tipo' => [
                    ['codigo' => '1', 'descricao' => 'Produto'],
                    ['codigo' => '2', 'descricao' => 'Serviço'],
                ],
                'grupoDespesa' => $grupoDespesa
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::error('SolicitacaoCompraController::create - Autorização NEGADA', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('SolicitacaoCompraController::create - Erro geral', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        try {

            $this->authorize('create', SolicitacaoCompra::class);


            $validated = $request->validate([
                "id_departamento" => "required",
                "id_filial" => "required",
                "prioridade" => "required|in:ALTA,MEDIA,BAIXA,ESCALA",
                "filial_entrega" => "required",
                "filial_faturamento" => "required",
                "grupo_despesa" => "required",
                "tipo_solicitacao" => "required",
                "is_contrato" => "boolean",
                "is_aplicacao_direta" => "required|in:1,2",
                "id_fornecedor" => "nullable",
                "observacao" => "nullable",
            ]);

            $produtos = $this->parseInputItems($request->input('produtos'));
            $servicos = $this->parseInputItems($request->input('servicos'));

            // Processar imagens dos produtos
            $produtos = $this->processarImagensProdutos($request, $produtos);

            // Processar imagens dos serviços
            $servicos = $this->processarImagensServicos($request, $servicos);

            if (empty($produtos) && empty($servicos)) {
                Log::warning('SolicitacaoCompraController::store - Erro: Nenhum item fornecido', [
                    'user_id' => $user->id
                ]);
                return redirect()->back()->withInput()->with('error', 'É necessário informar pelo menos um produto ou serviço.');
            }

            try {
                $dadosSolicitacao = [
                    'id_departamento' => $validated['id_departamento'],
                    'id_filial' => $validated['id_filial'],
                    'filial_entrega' => $validated['filial_entrega'],
                    'filial_faturamento' => $validated['filial_faturamento'],
                    'id_solicitante' => Auth::id(),
                    'prioridade' => $validated['prioridade'],
                    'id_grupo_despesas' => $validated['grupo_despesa'],
                    'situacao_compra' => null, // Status inicial será definido quando enviada para aprovação
                    'observacao' => $validated['observacao'] ?? null,
                    'tipo_solicitacao' => $validated['tipo_solicitacao'] ?? null,
                    'aprovado_reprovado' => null,
                    'is_cancelada' => false,
                    'id_fornecedor' => $validated['id_fornecedor'] ?? null,
                    'is_contrato' => $request->boolean('is_contrato'),
                    'is_aplicacao_direta' => (int) $validated['is_aplicacao_direta'],
                ];

                $solicitacao = $this->solicitacaoService->criarSolicitacao($dadosSolicitacao, $produtos, $servicos);

                return redirect()
                    ->route('admin.compras.solicitacoes.show', $solicitacao->id_solicitacoes_compras)
                    ->with('success', 'Solicitação de compra criada com sucesso! Agora você pode enviá-la para aprovação.');
            } catch (\Exception $e) {
                Log::error('SolicitacaoCompraController::store - ERRO ao criar solicitação', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'dados_solicitacao' => $dadosSolicitacao ?? 'não definido'
                ]);

                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Erro ao criar solicitação de compra: ' . $e->getMessage());
            }
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::error('SolicitacaoCompraController::store - Autorização NEGADA', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('SolicitacaoCompraController::store - Erro de validação', [
                'user_id' => $user->id,
                'errors' => $e->errors(),
                'message' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('SolicitacaoCompraController::store - Erro geral', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $solicitacao = SolicitacaoCompra::with([
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

        return view('admin.compras.solicitacoes.show', compact('solicitacao'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->authorize('update', SolicitacaoCompra::class);


        $solicitacao = SolicitacaoCompra::with(['itens.produto', 'itens.servico'])->findOrFail($id);

        $itemSolicitacao = ItemSolicitacaoCompra::where('id_solicitacao_compra', $id)->get();

        $produtosList = $itemSolicitacao->whereNotNull('id_produto')->values()->map(function ($item) {
            return [
                'id_produto' => $item->id_produto,
                'unidade' => $item->unidade_medida,
                'quantidade' => $item->quantidade,
                'justificativa' => $item->justificativa,
                'tipo' => 'produto',
                'data_inclusao' => $item->data_inclusao,
            ];
        })->toArray();



        $servicosList = $itemSolicitacao->whereNotNull('id_servico')->values()->map(function ($item) {
            return [
                'id_servico' => $item->id_servico,
                'quantidade' => $item->quantidade,
                'justificativa' => $item->justificativa,
                'tipo' => 'servico',
                'data_inclusao' => $item->data_inclusao,
            ];
        })->toArray();

        $tipo_item = $produtosList ? 'produto' : ($servicosList ? 'servico' : '');

        $departamentos = Departamento::where('ativo', true)->orderBy('descricao_departamento')->get();
        $filiais = VFilial::select('name as label', 'id as value')->orderBy('name')->get()->toArray();
        $fornecedores = Fornecedor::where('is_ativo', true)->orderBy('nome_fornecedor')->limit(30)->get();
        $grupoDespesa = GrupoDespesa::select('id_grupo_despesa as value', 'descricao_grupo as label')->orderBy('descricao_grupo')->get();

        return view('admin.compras.solicitacoes.edit', [
            'solicitacao' => $solicitacao,
            'itemSolicitacao' => $itemSolicitacao,
            'tipo_item' => $tipo_item,
            'departamentos' => $departamentos,
            'filiais' => $filiais,
            'fornecedores' => $fornecedores,
            'servicos' => $this->getServicos(),
            'servicosDescricao' => $this->getServicoDescricao(),
            'produtos' => $this->getProdutos(),
            'produtosDescricao' => $this->getProdutoDescricao(),
            'produtosList' => $produtosList,
            'servicosList' => $servicosList,
            'tipo' => [
                ['codigo' => '1', 'descricao' => 'Produto'],
                ['codigo' => '2', 'descricao' => 'Serviço'],
            ],
            'grupoDespesa' => $grupoDespesa
        ]);
    }

    public function update(Request $request, string $id)
    {
        $solicitacao = SolicitacaoCompra::findOrFail($id);

        $validated = $request->validate([
            "id_departamento" => "required",
            "id_filial" => "required",
            "prioridade" => "required",
            "filial_entrega" => "required",
            "filial_faturamento" => "required",
            "grupo_despesa" => "required",
            "tipo_solicitacao" => "nullable",
            "is_contrato" => "boolean",
            "is_aplicacao_direta" => "boolean",
            "id_fornecedor" => "nullable",
            "observacao" => "nullable",
        ]);

        try {
            $solicitacao->update([
                'id_departamento' => $validated['id_departamento'],
                'id_filial' => $validated['id_filial'],
                'filial_entrega' => $validated['filial_entrega'],
                'filial_faturamento' => $validated['filial_faturamento'],
                'id_solicitante' => Auth::id(),
                'prioridade' => $validated['prioridade'],
                'id_grupo_despesas' => $validated['grupo_despesa'],
                'situacao_compra' => null,
                'observacao' => $validated['observacao'] ?? null,
                'tipo_solicitacao' => $validated['tipo_solicitacao'] ?? null,
                'aprovado_reprovado' => null,
                'is_cancelada' => false,
                'id_fornecedor' => $validated['id_fornecedor'] ?? null,
                'is_contrato' => $request->has('is_contrato') ? true : false,
                'is_aplicacao_direta' => $request->has('is_aplicacao_direta') ? true : false,
            ]);

            // Registrar log da atualização
            $solicitacao->registrarLog(
                $solicitacao->situacao_compra ?? 'INCLUIDA',
                Auth::id(),
                'Solicitação de compra atualizada'
            );

            return redirect()
                ->route('admin.compras.solicitacoes.index')
                ->with('success', 'Solicitação de compra atualizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar solicitação de compra: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar solicitação de compra: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $solicitacao = SolicitacaoCompra::findOrFail($id);

        $request->validate([
            'justificativa_edit_or_delete' => 'required|string|min:5',
        ]);

        try {
            $solicitacao->justificativa_edit_or_delete = $request->input('justificativa_edit_or_delete');
            $solicitacao->save();

            // Registrar log antes da exclusão
            $solicitacao->registrarLog(
                $solicitacao->situacao_compra ?? 'EXCLUIDA',
                Auth::id(),
                'Solicitação excluída: ' . $request->input('justificativa_edit_or_delete')
            );

            $solicitacao->delete();

            return redirect()
                ->route('admin.compras.solicitacoes.index')
                ->with('success', 'Solicitação de compra excluída com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir solicitação de compra: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Erro ao excluir solicitação de compra: ' . $e->getMessage());
        }
    }

    /**
     * Aprovar a solicitação de compra.
     */
    public function aprovar(Request $request, string $id)
    {
        $solicitacao = SolicitacaoCompra::findOrFail($id);

        $request->validate([
            'observacao_aprovador' => 'nullable|string',
        ]);

        try {
            $this->aprovacaoService->aprovar(
                $solicitacao,
                Auth::id(),
                $request->input('observacao_aprovador')
            );

            if ($request->expectsJson()) {
                return $this->jsonSuccessResponse($solicitacao, 'Solicitação aprovada com sucesso!', 'aprovada');
            }

            return redirect()
                ->route('admin.compras.solicitacoes.show', $solicitacao->id_solicitacoes_compras)
                ->with('success', 'Solicitação aprovada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao aprovar solicitação de compra: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao aprovar solicitação: ' . $e->getMessage()
                ]);
            }

            return redirect()
                ->back()
                ->with('error', 'Erro ao aprovar solicitação: ' . $e->getMessage());
        }
    }

    public function rejeitar(Request $request, string $id)
    {
        $solicitacao = SolicitacaoCompra::findOrFail($id);

        $request->validate([
            'observacao_aprovador' => 'required',
        ]);

        try {
            $this->aprovacaoService->reprovar(
                $solicitacao,
                Auth::id(),
                $request->input('observacao_aprovador')
            );

            if ($request->expectsJson()) {
                return $this->jsonSuccessResponse($solicitacao, 'Solicitação rejeitada com sucesso!', 'rejeitada');
            }

            return redirect()
                ->route('admin.compras.solicitacoes.show', $solicitacao->id_solicitacoes_compras)
                ->with('success', 'Solicitação rejeitada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao rejeitar solicitação de compra: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao rejeitar solicitação: ' . $e->getMessage()
                ]);
            }

            return redirect()
                ->back()
                ->with('error', 'Erro ao rejeitar solicitação: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar a solicitação de compra.
     */
    public function cancelar(Request $request, string $id)
    {
        $solicitacao = SolicitacaoCompra::findOrFail($id);

        if (Auth::id() != $solicitacao->id_solicitante && !Auth::user()->hasRole('Administrador do Módulo Compras')) {
            abort(403, 'Você não tem permissão para cancelar esta solicitação.');
        }

        $request->validate([
            'justificativa_edit_or_delete' => 'required',
        ]);

        try {

            $this->cancelamentoService->cancelar(
                $solicitacao,
                Auth::id(),
                $request->input('justificativa_edit_or_delete')
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitação cancelada com sucesso!',
                    'status' => 'cancelada'
                ]);
            }

            return redirect()
                ->route('admin.compras.solicitacoes.show', $solicitacao->id_solicitacoes_compras)
                ->with('success', 'Solicitação cancelada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao cancelar solicitação: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao cancelar solicitação: ' . $e->getMessage()
                ]);
            }

            return redirect()
                ->back()
                ->with('error', 'Erro ao cancelar solicitação: ' . $e->getMessage());
        }
    }

    /**
     * Adicionar observação à solicitação.
     */
    public function observacao(Request $request, string $id)
    {
        $solicitacao = SolicitacaoCompra::findOrFail($id);

        $request->validate([
            'observacao' => 'required|string|min:5',
        ]);

        try {
            DB::connection('pgsql')
                ->table('solicitacao_observacoes')
                ->insert([
                    'id_solicitacao' => $solicitacao->id_solicitacoes_compras,
                    'id_usuario' => Auth::id(),
                    'observacao' => $request->input('observacao'),
                    'data_inclusao' => now(),
                ]);

            if ($request->expectsJson()) {
                return $this->jsonObservacaoResponse($request->input('observacao'));
            }

            return redirect()
                ->route('admin.compras.solicitacoes.show', $solicitacao->id_solicitacoes_compras)
                ->with('success', 'Observação adicionada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao adicionar observação: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao adicionar observação: ' . $e->getMessage()
                ]);
            }

            return redirect()
                ->back()
                ->with('error', 'Erro ao adicionar observação: ' . $e->getMessage());
        }
    }

    /**
     * Desmembrar a solicitação em várias outras.
     */
    public function desmembrar(Request $request, string $id)
    {
        $solicitacao = SolicitacaoCompra::with('itens')->findOrFail($id);

        if (!$solicitacao->podeSerDesmembrada()) {
            abort(400, 'Esta solicitação não pode ser desmembrada.');
        }

        $request->validate([
            'itens' => 'required|array',
            'itens.*.id' => 'required|exists:itens_solicitacao_compra,id_item_solicitacao',
            'itens.*.nova_solicitacao' => 'required|integer|min:1',
            'justificativa' => 'required|string|min:5',
        ]);

        try {
            $novasSolicitacoes = $this->desmembramentoService->desmembrar($solicitacao, $request->all());

            $mensagemSucesso = "Solicitações juntadas com sucesso! Nova solicitação: #{$novasSolicitacoes[0]->id_solicitacoes_compras}";

            return redirect()
                ->route('admin.compras.solicitacoes.index')
                ->with('success', $mensagemSucesso);
        } catch (\Exception $e) {
            Log::error('Erro ao desmembrar solicitação: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Erro ao desmembrar solicitações: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Buscar solicitações para a API.
     */
    public function buscar(Request $request)
    {
        if (!Auth::user()->can('visualizar_solicitacao_compra')) {
            return response()->json(['error' => 'Sem permissão'], 403);
        }

        $termo = $request->input('termo');
        $status = $request->input('status');
        $departamentoId = $request->input('departamento_id');

        $query = SolicitacaoCompra::with(['solicitante', 'departamento']);

        if ($termo) {
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

        if ($status) {
            $query->{$this->getScopeByStatus($status)}();
        }

        if ($departamentoId) {
            $query->where('id_departamento', $departamentoId);
        }

        $solicitacoes = $query->orderBy('data_inclusao', 'desc')->limit(10)->get();

        return response()->json([
            'resultados' => $solicitacoes->map(function ($solicitacao) {
                return [
                    'id' => $solicitacao->id_solicitacoes_compras,
                    'solicitante' => $solicitacao->solicitante->name ?? 'N/A',
                    'departamento' => $solicitacao->departamento->descricao_departamento ?? 'N/A',
                    'data' => $solicitacao->data_inclusao ? $solicitacao->data_inclusao->format('d/m/Y') : 'N/A',
                    'status' => $solicitacao->status,
                    'itens_count' => $solicitacao->itens->count()
                ];
            })
        ]);
    }

    /**
     * Exportar dados em CSV.
     */
    public function exportCsv(Request $request)
    {
        if (!Auth::user()->can('visualizar_relatorios_compras')) {
            abort(403, 'Sem permissão para exportar relatórios.');
        }

        $query = SolicitacaoCompra::with(['solicitante', 'departamento', 'filial', 'aprovador']);
        $query = $this->applyFilters($query, $request);
        $query->orderBy('data_inclusao', 'desc');

        $solicitacoes = $query->get();
        $fileName = 'solicitacoes_compra_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return response()->streamDownload(function () use ($solicitacoes) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, [
                'ID',
                'Solicitante',
                'Departamento',
                'Data Solicitação',
                'Status',
                'Prioridade',
                'Aprovador',
                'Data Aprovação',
                'Filial',
                'Observação'
            ]);

            foreach ($solicitacoes as $solicitacao) {
                fputcsv($output, [
                    $solicitacao->id_solicitacoes_compras,
                    $solicitacao->solicitante->name ?? 'N/A',
                    $solicitacao->departamento->descricao_departamento ?? 'N/A',
                    $solicitacao->data_inclusao ? $solicitacao->data_inclusao->format('d/m/Y H:i') : 'N/A',
                    $solicitacao->situacao_compra,
                    $solicitacao->prioridade,
                    $solicitacao->aprovador->name ?? 'N/A',
                    $solicitacao->data_aprovacao ? $solicitacao->data_aprovacao->format('d/m/Y H:i') : 'N/A',
                    $solicitacao->filial->nome_filial ?? 'N/A',
                    $solicitacao->observacao ?? ''
                ]);
            }

            fclose($output);
        }, $fileName, $headers);
    }

    public function pegaUnidade(Request $request)
    {
        try {
            $idProduto = $request->input('produto');
            $produto = Produto::where('id_produto', $idProduto)->first();
            $unidade = UnidadeProduto::where('id_unidade_produto', $produto->id_unidade_produto ?? null)->first();

            return response()->json([
                'value' => $unidade->id_unidade_produto ?? null,
                'label' => $unidade->descricao_unidade ?? 'Não informado',
                'unidade' => $unidade->descricao_unidade ?? 'Não informado', // Mantendo compatibilidade com o código atual
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do unidade: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar dados do unidade'], 500);
        }
    }

    public function getById(Request $request, string $id)
    {
        $itensSolicitacao = ItemSolicitacaoCompra::where('id_solicitacao_compra', $id)->get();
        return response()->json(['itens' => $itensSolicitacao]);
    }

    /**
     * Enviar solicitação para aprovação
     */
    public function enviarParaAprovacao(Request $request, string $id)
    {
        $solicitacao = SolicitacaoCompra::findOrFail($id);

        try {
            $user = Auth::user();

            // Simplificado: não precisa mais do cargo, todas seguem fluxo comum
            $solicitacao->enviarParaAprovacao($user->id);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitação enviada para aprovação com sucesso!',
                    'status' => $solicitacao->status
                ]);
            }

            return redirect()
                ->route('admin.compras.solicitacoes.show', $solicitacao->id_solicitacoes_compras)
                ->with('success', 'Solicitação enviada para aprovação com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao enviar solicitação para aprovação: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao enviar solicitação: ' . $e->getMessage()
                ]);
            }

            return redirect()
                ->back()
                ->with('error', 'Erro ao enviar solicitação: ' . $e->getMessage());
        }
    }

    /**
     * Aprovar solicitação (gestor departamental)
     */
    public function aprovarGestor(Request $request, string $id)
    {
        $solicitacao = SolicitacaoCompra::findOrFail($id);

        $request->validate([
            'observacao' => 'nullable|string|max:1000'
        ]);

        try {
            $user = Auth::user();
            $observacao = $request->input('observacao');

            $solicitacao->aprovarGestor($user->id, $observacao);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitação aprovada com sucesso!',
                    'status' => $solicitacao->status
                ]);
            }

            return redirect()
                ->route('admin.compras.solicitacoes.show', $solicitacao->id_solicitacoes_compras)
                ->with('success', 'Solicitação aprovada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao aprovar solicitação: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao aprovar solicitação: ' . $e->getMessage()
                ]);
            }

            return redirect()
                ->back()
                ->with('error', 'Erro ao aprovar solicitação: ' . $e->getMessage());
        }
    }

    /**
     * Reprovar solicitação (gestor departamental)
     */
    public function reprovarGestor(Request $request, string $id)
    {
        $solicitacao = SolicitacaoCompra::findOrFail($id);

        $request->validate([
            'observacao' => 'required|string|max:1000'
        ]);

        try {
            $user = Auth::user();
            $observacao = $request->input('observacao');

            $solicitacao->reprovarGestor($user->id, $observacao);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitação reprovada.',
                    'status' => $solicitacao->status
                ]);
            }

            return redirect()
                ->route('admin.compras.solicitacoes.show', $solicitacao->id_solicitacoes_compras)
                ->with('success', 'Solicitação reprovada.');
        } catch (\Exception $e) {
            Log::error('Erro ao reprovar solicitação: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao reprovar solicitação: ' . $e->getMessage()
                ]);
            }

            return redirect()
                ->back()
                ->with('error', 'Erro ao reprovar solicitação: ' . $e->getMessage());
        }
    }

    /**
     * Finalizar solicitação
     */
    public function finalizar(Request $request, string $id)
    {
        $solicitacao = SolicitacaoCompra::findOrFail($id);

        $request->validate([
            'observacao' => 'nullable|string|max:1000'
        ]);

        try {
            $user = Auth::user();
            $observacao = $request->input('observacao');

            $solicitacao->finalizar($user->id, $observacao);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitação finalizada com sucesso!',
                    'status' => $solicitacao->status
                ]);
            }

            return redirect()
                ->route('admin.compras.solicitacoes.show', $solicitacao->id_solicitacoes_compras)
                ->with('success', 'Solicitação finalizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao finalizar solicitação: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao finalizar solicitação: ' . $e->getMessage()
                ]);
            }

            return redirect()
                ->back()
                ->with('error', 'Erro ao finalizar solicitação: ' . $e->getMessage());
        }
    }

    /**
     * Enviar solicitação para aprovação - Fluxo simplificado comum
     * Todas as solicitações seguem o mesmo caminho
     */
    public function processarAprovacao(Request $request, string $id)
    {
        $solicitacao = SolicitacaoCompra::findOrFail($id);

        try {
            $user = Auth::user();
            $data = $request->all();

            DB::beginTransaction();

            // Fluxo comum: todas as solicitações vão para aprovação departamental
            $situacao = SolicitacaoCompra::STATUS_AGUARDANDO_APROVACAO_GESTOR;
            $aprovadoGestor = false;

            // Atualizar a solicitação
            $solicitacao->situacao_compra = $situacao;
            $solicitacao->aprovado_reprovado = $aprovadoGestor;

            // Atualizar campos de filiais se fornecidos
            if (isset($data['filial_entrega'])) {
                $solicitacao->filial_entrega = $data['filial_entrega'];
            }
            if (isset($data['filial_faturamento'])) {
                $solicitacao->filial_faturamento = $data['filial_faturamento'];
            }

            $solicitacao->save();

            // Registrar log da mudança
            $solicitacao->registrarLog($situacao, $user->id, 'Solicitação enviada para aprovação departamental');

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitação enviada para aprovação com sucesso!',
                    'status' => $solicitacao->status
                ]);
            }

            return redirect()
                ->route('admin.compras.solicitacoes.show', $solicitacao->id_solicitacoes_compras)
                ->with('success', 'Solicitação enviada para aprovação departamental com sucesso!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro ao processar aprovação: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao processar aprovação: ' . $e->getMessage()
                ]);
            }

            return redirect()
                ->back()
                ->with('error', 'Erro ao processar aprovação: ' . $e->getMessage());
        }
    }

    /**
     * Processar solicitação de contrato (equivalente à função fc_inserir_compras_produtos_direta_)
     */
    private function processarSolicitacaoContrato(SolicitacaoCompra $solicitacao)
    {
        // Esta função seria equivalente à fc_inserir_compras_produtos_direta_ do sistema de referência
        // Implementar lógica específica para contratos conforme necessário
        Log::info("Processando solicitação de contrato: {$solicitacao->id_solicitacoes_compras}");

        // TODO: Implementar lógica específica para processamento de contratos
        // Por exemplo: gerar pedidos automaticamente, notificar stakeholders, etc.
    }

    protected function parseInputItems($input)
    {
        if (!$input) return [];

        if (is_string($input)) {
            return json_decode($input, true) ?? [];
        } elseif (is_array($input)) {
            return $input;
        }

        return [];
    }

    /**
     * Garante que um diretório existe no storage público
     *
     * @param string $directory
     * @return bool
     */
    protected function ensureDirectoryExists($directory)
    {
        $fullPath = storage_path('app/public/' . $directory);

        if (!is_dir($fullPath)) {

            if (!mkdir($fullPath, 0755, true)) {
                Log::error("Falha ao criar diretório: {$directory}", ['path' => $fullPath]);
                return false;
            }
        }

        return true;
    }

    protected function processarImagensProdutos(Request $request, array $produtos)
    {
        foreach ($produtos as $index => &$produto) {
            $inputName = "produto_imagem_{$index}";

            if ($request->hasFile($inputName)) {
                $file = $request->file($inputName);

                if ($file->isValid()) {
                    // Definir o diretório de armazenamento
                    $directory = 'solicitacoes/produtos';

                    // Garantir que o diretório existe
                    if (!$this->ensureDirectoryExists($directory)) {
                        Log::error("Não foi possível criar o diretório para produtos", ['directory' => $directory]);
                        continue;
                    }

                    // Gerar um nome único para o arquivo
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                    // Armazenar o arquivo
                    $path = $file->storeAs($directory, $filename, 'public');
                    // Adicionar o caminho da imagem ao produto
                    $produto['imagem_produto'] = $path;
                }
            } else {
                Log::info("Nenhum arquivo encontrado para produto {$index}", [
                    'nome_input' => $inputName
                ]);
            }
        }

        return $produtos;
    }

    protected function processarImagensServicos(Request $request, array $servicos)
    {
        foreach ($servicos as $index => &$servico) {
            $inputName = "servico_imagem_{$index}";

            if ($request->hasFile($inputName)) {
                $file = $request->file($inputName);

                if ($file->isValid()) {
                    // Definir o diretório de armazenamento
                    $directory = 'solicitacoes/servicos';

                    // Garantir que o diretório existe
                    if (!$this->ensureDirectoryExists($directory)) {
                        Log::error("Não foi possível criar o diretório para serviços", ['directory' => $directory]);
                        continue;
                    }

                    // Gerar um nome único para o arquivo
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                    // Armazenar o arquivo
                    $path = $file->storeAs($directory, $filename, 'public');

                    // Adicionar o caminho da imagem ao serviço
                    $servico['imagem_produto'] = $path;  // Usando imagem_produto para manter compatibilidade
                }
            } else {
                Log::info("Nenhum arquivo encontrado para serviço {$index}", [
                    'nome_input' => $inputName
                ]);
            }
        }

        return $servicos;
    }

    protected function jsonSuccessResponse($solicitacao, $message, $status)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'status' => $status,
            'registro' => [
                'acao' => $status === 'aprovada' ? 'aprovacao' : 'rejeicao',
                'data' => now()->format('Y-m-d H:i:s'),
                'usuario_nome' => Auth::user()->name,
                'observacao' => $solicitacao->observacao_aprovador
            ]
        ]);
    }

    protected function getScopeByStatus($status)
    {
        return match ($status) {
            'pendente' => 'pendentes',
            'aprovada' => 'aprovadas',
            'reprovada' => 'reprovadas',
            'cancelada' => 'canceladas',
            'finalizada' => 'finalizadas',
            default => 'pendentes'
        };
    }

    protected function jsonObservacaoResponse($observacao)
    {
        return response()->json([
            'success' => true,
            'message' => 'Observação adicionada com sucesso!',
            'registro' => [
                'acao' => 'observacao',
                'data' => now()->format('Y-m-d H:i:s'),
                'usuario_nome' => Auth::user()->name,
                'observacao' => $observacao
            ]
        ]);
    }
}

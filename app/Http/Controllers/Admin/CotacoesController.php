<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MovimentacaoSolicitacao;
use App\Models\MovimentacaoSolicitacaoItens;
use App\Models\Cotacoes;
use App\Models\CotacoesItens;
use App\Models\Filial;
use App\Models\Fornecedor;
use App\Models\ItemSolicitacaoCompra;
use App\Modules\Manutencao\Models\OrdemServicoServicos;
use App\Models\Produto;
use App\Modules\Manutencao\Models\ServicoSolicitacaoCompra;
use App\Models\SolicitacaoCompra;
use App\Modules\Manutencao\Models\OrdemServicoPecas;
use App\Models\User;
use App\Models\VSolicitacoesComprasV2;
use App\Services\EmailSenderService;
use App\Services\NotificationService;
use App\Services\PHPMailerService;
use PHPMailer\PHPMailer\PHPMailer;
use App\Traits\JasperServerIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class CotacoesController extends Controller
{
    use AuthorizesRequests;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        try {

            $baseQuery = VSolicitacoesComprasV2::query()
                ->leftJoin('solicitacoescompras as sc', 'sc.id_solicitacoes_compras', '=', 'v_solicitacoes_compras_v2.id_solicitacoes_compras')
                ->select('v_solicitacoes_compras_v2.*', 'sc.is_adiado')
                ->whereIn('v_solicitacoes_compras_v2.situacao_compra', [
                    'COTAÇÕES RECUSADAS PELO GESTOR',
                    'SOLICITAÇÃO VALIDADA PELO GESTOR',
                    'AGUARDANDO INÍCIO DE COMPRAS',
                    'Iniciada',
                    'INICIADA'
                ]);

            // Aplicar filtros base (exceto tipo_solicitacao)
            $filteredQuery = clone $baseQuery;

            if ($request->filled('id_solicitacoes_compras')) {
                $filteredQuery->where('v_solicitacoes_compras_v2.id_solicitacoes_compras', $request->id_solicitacoes_compras);
            }

            if ($request->filled('id_ordem_servico')) {
                $filteredQuery->where('v_solicitacoes_compras_v2.id_ordem_servico', $request->id_ordem_servico);
            }

            if ($request->filled('id_veiculo')) {
                $filteredQuery->where('v_solicitacoes_compras_v2.id_veiculo', $request->id_veiculo);
            }

            if ($request->filled('situacao_compra')) {
                $filteredQuery->where('v_solicitacoes_compras_v2.situacao_compra', $request->situacao_compra);
            }

            if ($request->filled('data_inicio')) {
                $filteredQuery->whereDate('v_solicitacoes_compras_v2.data_inclusao', '>=', $request->data_inicio);
            }

            if ($request->filled('data_final')) {
                $filteredQuery->whereDate('v_solicitacoes_compras_v2.data_inclusao', '<=', $request->data_final);
            }

            if ($request->filled('descricao_grupo')) {
                $filteredQuery->where('v_solicitacoes_compras_v2.descricao_grupo', $request->descricao_grupo);
            }

            if ($request->filled('descricao_departamento')) {
                $filteredQuery->where('v_solicitacoes_compras_v2.descricao_departamento', $request->descricao_departamento);
            }

            if ($request->filled('prioridade')) {
                $filteredQuery->where('v_solicitacoes_compras_v2.prioridade', $request->prioridade);
            }

            if ($request->filled('comprador')) {
                $filteredQuery->where('v_solicitacoes_compras_v2.comprador', $request->comprador);
            }

            if ($request->filled('filial')) {
                $filteredQuery->where('v_solicitacoes_compras_v2.filial', $request->filial);
            }

            if ($request->filled('solicitante')) {
                $filteredQuery->where('v_solicitacoes_compras_v2.solicitante', $request->solicitante);
            }

            // Aplicar filtro de tipo_solicitacao se especificado
            if ($request->filled('tipo_solicitacao')) {
                $filteredQuery->where('v_solicitacoes_compras_v2.tipo_solicitacao', $request->tipo_solicitacao);
            }

            $query = $filteredQuery->orderByDesc('v_solicitacoes_compras_v2.id_solicitacoes_compras');

            $cotacoes = $query->paginate(30)
                ->appends($request->query());

            $filterData = $this->getFilterData();

            $usuarios = User::select('id as value', 'name as label')->orderBy('name')->get();

            return view('admin.compras.cotacoes.index', array_merge(
                compact('cotacoes'),
                $filterData,
            ));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar listagem de cotações:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Retorne uma view de erro ou mensagem amigável
            return back()->with('error', 'Erro ao carregar cotações.');
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
            'logs',
            'userAdiado'
        ])->findOrFail($id);

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

        $this->authorize('view', $solicitacao);

        return view('admin.compras.cotacoes.show', compact(
            'solicitacao',
            'cotacoesList',
            'cotacoesItens',
            'cotacoesItensCompletos'
        ));
    }

    /**
     * Get a specific cotacao by ID (for AJAX requests)
     */
    public function getCotacao(string $id)
    {
        try {
            // Buscar a cotação por ID
            $cotacao = Cotacoes::with(['fornecedor'])->findOrFail($id);

            // Buscar itens da cotação
            $cotacoesItens = CotacoesItens::where('id_cotacao', $id)->get();

            // Retornar dados em formato JSON
            return response()->json([
                'success' => true,
                'cotacao' => $cotacao,
                'itens' => $cotacoesItens
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar cotação', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => true,
                'message' => 'Cotação não encontrada: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $user = Auth::user();
            $cotacoes = VSolicitacoesComprasV2::where('id_solicitacoes_compras', $id)->firstOrFail();
            $comprador = $cotacoes->comprador;

            // Buscar a solicitação de compra real para ter acesso aos relacionamentos
            $solicitacaoCompra = SolicitacaoCompra::with([
                'comprador',
                'filial',
                'filialEntrega',
                'filialFaturamento',
                'grupoDespesa'
            ])->where('id_solicitacoes_compras', $id)->first();

            // Verificar se já tem comprador e se não é o usuário atual
            if ($comprador !== null && $comprador !== $user->name) {
                // Verificar se é uma requisição AJAX
                if (request()->wantsJson() || request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'title' => 'Atenção',
                        'message' => 'Solicitação já iniciada por outro comprador: ' . $comprador
                    ]);
                }

                // Para requisições normais, retornar redirect com flash message
                return redirect()->route('admin.compras.cotacoes.index')
                    ->with('error', 'Solicitação já iniciada por outro comprador: ' . $comprador);
            }

            $idSolicitacao = $cotacoes->id_solicitacoes_compras;
            $idOrdem = $cotacoes->id_ordem_servico;

            $itemSolicitacaoCompra = ItemSolicitacaoCompra::with('produto:id_produto,pre_cadastro')->where('id_solicitacao_compra', $id)->get();

            $cotacoesList = Cotacoes::where('id_solicitacoes_compras', $id)->get();

            // Buscar itens de cotações para todas as cotações da solicitação
            $cotacoesItens = collect();
            $cotacoesItensUnicos = collect();
            if ($cotacoesList->isNotEmpty()) {
                $cotacaoIds = $cotacoesList->pluck('id_cotacoes')->toArray();
                $cotacoesItens = CotacoesItens::whereIn('id_cotacao', $cotacaoIds)->get();

                // Criar versão com itens únicos (agrupados por produto)
                $cotacoesItensUnicos = $cotacoesItens->groupBy('id_produto')->map(function ($group) {
                    return [
                        'id_produto'      => $group->first()->id_produto,
                        'descricao'       => $group->first()->descricao ?? null, // ou outro campo
                        'total_cotacoes'  => $group->count(),
                        'cotacoes_ids'    => $group->pluck('id_cotacao')->unique()->values()->toArray(),
                    ];
                })->values();
            }

            $validarMapaCotacao = $this->validarMapaCotacao($idSolicitacao, $idOrdem);

            $descricaoItem = $this->getProdutoDescricao();

            // Dados para os selects do formulário
            $formData = $this->getFormData();

            // Dados para o gráfico de descontos - filtrado pela solicitação atual
            $dadosGraficoDescontos = $this->getDadosGraficoDescontos($idSolicitacao);

            return view('admin.compras.cotacoes.edit', array_merge(
                compact(
                    'cotacoes',
                    'solicitacaoCompra',
                    'itemSolicitacaoCompra',
                    'cotacoesList',
                    'descricaoItem',
                    'cotacoesItens',
                    'validarMapaCotacao',
                    'dadosGraficoDescontos'
                ),
                $formData,
                [
                    'action' => route('admin.compras.cotacoes.update', $id),
                    'method' => 'PUT'
                ]
            ));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar cotação para edição:', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Verificar se é uma requisição AJAX
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'title' => 'Erro',
                    'message' => 'Cotação não encontrada.'
                ], 404);
            }

            return back()->with('error', 'Cotação não encontrada.');
        }
    }

    /**
     * Get data for form selects
     */
    private function getFormData()
    {
        return [
            'compradores' => VSolicitacoesComprasV2::select('comprador as label', 'comprador as value')
                ->whereNotNull('comprador')
                ->distinct()
                ->orderBy('comprador')
                ->get()
                ->toArray(),

            'solicitantes' => VSolicitacoesComprasV2::select('solicitante as label', 'solicitante as value')
                ->whereNotNull('solicitante')
                ->distinct()
                ->orderBy('solicitante')
                ->get()
                ->toArray(),

            'filiais' => Filial::select('name as label', 'id as value')
                ->whereNotNull('name')
                ->distinct()
                ->orderBy('name')
                ->get()
                ->toArray(),

            'solicitacoesPecas' => VSolicitacoesComprasV2::select('id_solicitacoes_compras as label', 'id_solicitacoes_compras as value')
                ->distinct()
                ->orderBy('id_solicitacoes_compras')
                ->get()
                ->toArray(),

            'gruposDespesas' => VSolicitacoesComprasV2::select('descricao_grupo as label', 'descricao_grupo as value')
                ->whereNotNull('descricao_grupo')
                ->distinct()
                ->orderBy('descricao_grupo')
                ->get()
                ->toArray(),

            'usuarios' => User::select('name as label', 'id as value')
                ->orderBy('name')
                ->distinct()
                ->get()
                ->toArray(),

            'fornecedor' => Fornecedor::select('nome_fornecedor as label', 'id_fornecedor as value')
                ->orderBy('nome_fornecedor')
                ->distinct()
                ->limit(30)
                ->get()
                ->toArray()
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            // Validação dos dados específicos
            $validatedData = $request->validate([
                'filial' => 'nullable|integer|exists:filiais,id',
                'filial_entrega' => 'nullable|integer|exists:filiais,id',
                'filial_faturamento' => 'nullable|integer|exists:filiais,id',
                'observacaocomprador' => 'nullable|string',
            ]);

            // Buscar a solicitação de compra
            $solicitacaoCompra = SolicitacaoCompra::findOrFail($id);

            // Atualizar apenas os campos permitidos
            $updated = false;

            if ($request->filled('filial')) {
                $solicitacaoCompra->id_filial = $validatedData['filial'];
                $updated = true;
            }

            if ($request->filled('filial_entrega')) {
                $solicitacaoCompra->filial_entrega = $validatedData['filial_entrega'];
                $updated = true;
            }

            if ($request->filled('filial_faturamento')) {
                $solicitacaoCompra->filial_faturamento = $validatedData['filial_faturamento'];
                $updated = true;
            }

            if ($request->has('observacaocomprador')) {
                $solicitacaoCompra->observacaocomprador = $validatedData['observacaocomprador'];
                $updated = true;
            }

            if ($updated) {
                $solicitacaoCompra->save();

                // Registrar log da atualização
                $solicitacaoCompra->registrarLog(
                    $solicitacaoCompra->situacao_compra ?? 'INCLUIDA',
                    Auth::id(),
                    'Dados da solicitação atualizados: ' . ($request->filled('observacaocomprador') ? 'Observação do comprador' : 'Filiais')
                );
            }

            return back()->with('success', 'Dados atualizados com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erro de validação ao atualizar cotação:', [
                'id' => $id,
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Dados inválidos. Verifique os campos preenchidos.');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar cotação:', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar dados: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar cotação via AJAX
     */
    public function atualizar(Request $request)
    {
        try {
            // Validação dos dados
            $validatedData = $request->validate([
                'id_cotacao' => 'required|integer|exists:cotacoes,id_cotacoes',
                'data_entrega' => 'nullable|date',
                'arquivo_cotacao' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240', // 10MB max
            ]);

            $cotacao = Cotacoes::findOrFail($validatedData['id_cotacao']);

            // Atualizar data de entrega se fornecida
            if ($request->filled('data_entrega')) {
                $cotacao->data_entrega = $validatedData['data_entrega'];
            }

            // Processar arquivo se enviado
            if ($request->hasFile('arquivo_cotacao')) {
                $arquivo = $request->file('arquivo_cotacao');

                // Criar nome único para o arquivo
                $nomeArquivo = time() . '_' . $arquivo->getClientOriginalName();

                // Salvar o arquivo (ajuste o caminho conforme sua estrutura)
                $caminhoArquivo = $arquivo->storeAs('cotacoes', $nomeArquivo, 'public');

                // Atualizar o campo do arquivo na cotação
                $cotacao->caminhoimagem = $caminhoArquivo;
            }

            $cotacao->save();

            return response()->json([
                'success' => true,
                'message' => 'Cotação atualizada com sucesso!',
                'data' => [
                    'id_cotacao' => $cotacao->id_cotacoes,
                    'data_entrega' => $cotacao->data_entrega,
                    'arquivo' => $cotacao->caminhoimagem
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar cotação:', [
                'request' => $request->all(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar cotação: ' . $e->getMessage()
            ], 500);
        }
    }

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
            'id_solicitacoes_compras' => VSolicitacoesComprasV2::select('id_solicitacoes_compras as label', 'id_solicitacoes_compras as value')->orderBy('id_solicitacoes_compras')->limit(30)->distinct()->get()->toArray(),
            'ordens_servico' => VSolicitacoesComprasV2::select('id_ordem_servico as label', 'id_ordem_servico as value')->orderBy('id_ordem_servico')->limit(30)->distinct()->get()->toArray(),
            'veiculos' => VSolicitacoesComprasV2::with('veiculo')->select('id_veiculo as label', 'id_solicitacoes_compras')->orderBy('id_veiculo')->limit(30)->distinct()->get()->filter(function ($item) {
                return $item->veiculo && !empty($item->veiculo->placa);
            })
                ->map(function ($item) {
                    return [
                        'label' => $item->id_solicitacoes_compras,
                        'value' => $item->veiculo->placa,
                    ];
                })
                ->toArray(),
            'usuarios' => User::select('name as label', 'id as value')->orderBy('name')->distinct()->get()->toArray(),
            'situacoes_compra' => VSolicitacoesComprasV2::select('situacao_compra as label', 'situacao_compra as value')->orderBy('situacao_compra')->limit(30)->distinct()->get()->toArray(),
            'grupos' => VSolicitacoesComprasV2::select('descricao_grupo as label', 'descricao_grupo as value')->orderBy('descricao_grupo')->limit(30)->distinct()->get()->toArray(),
            'departamentos' => VSolicitacoesComprasV2::select('descricao_departamento as label', 'descricao_departamento as value')->orderBy('descricao_departamento')->limit(30)->distinct()->get()->toArray(),
            'prioridades' => VSolicitacoesComprasV2::select('prioridade as label', 'prioridade as value')->orderBy('prioridade')->limit(30)->distinct()->get()->toArray(),
            'compradores' => VSolicitacoesComprasV2::select('comprador as label', 'comprador as value')->orderBy('comprador')->limit(30)->distinct()->get()->toArray(),
            'filiais' => VSolicitacoesComprasV2::select('filial as label', 'filial as value')->orderBy('filial')->limit(30)->distinct()->get()->toArray(),
            'solicitantes' => VSolicitacoesComprasV2::select('solicitante as label', 'solicitante as value')->orderBy('solicitante')->limit(30)->distinct()->get()->toArray(),
            'tipos_solicitacao' => VSolicitacoesComprasV2::select('tipo_solicitacao as label', 'tipo_solicitacao as value')->orderBy('tipo_solicitacao')->limit(30)->distinct()->get()->toArray(),
        ];
    }

    public function incluirCotacao(Request $request)
    {

        try {

            $idsolicitacao = $request->input('solicitacao');
            $idfornecedor = $request->input('fornecedor');
            $contatoFornecedor = $request->input('nome_contato');
            $emailcontato = $request->input('email');
            $idusuario = Auth::user()->id;

            $result = DB::connection('pgsql')->select(
                "SELECT * FROM fc_gerar_cotacao(?, ?, ?, ?, ?)",
                [$idfornecedor, $contatoFornecedor, $emailcontato, $idsolicitacao, $idusuario]
            );

            return response()->json([
                'success' => true,
                'title' => 'Cotação incluída com sucesso',
                'message' => 'Cotação incluída com sucesso',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao incluir cotação', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'title' => 'Erro ao incluir cotação',
                'message' => 'Erro ao incluir cotação: ' . $e->getMessage(),
            ]);
        }
    }

    public function buscarItem(Request $request, $id = null)
    {
        // aceita id por rota ou por query param (nome: solicitacaoCompraConsulta)
        $solicitacaoId = $id ?? $request->query('solicitacaoCompraConsulta');

        if (empty($solicitacaoId)) {
            return response()->json(['message' => 'Informe o id da solicitação'], 400);
        }
        // busca a solicitação (view) para pegar dados complementares
        $solicitacao = SolicitacaoCompra::where('id_solicitacoes_compras', $solicitacaoId)->first();

        $items = ItemSolicitacaoCompra::where('id_solicitacao_compra', $solicitacaoId)->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Itens não encontrados'], 404);
        }

        $result = [];

        foreach ($items as $itemsSolicitacao) {

            $result[] = [
                'id_filial' => $solicitacao->filial->name ?? null,
                'departamento' => optional($solicitacao->departamento)->descricao_departamento ?? null,
                'prioridade' => $solicitacao->prioridade ?? null,
                'situacao' => $solicitacao->situacao_compra ?? null,
                'observacao' => $solicitacao->observacao ?? null,
                'produto' => $itemsSolicitacao->id_produto ?? null,
                'quantidade' => $itemsSolicitacao->quantidade_solicitada ?? null,
                'imagem' => $itemsSolicitacao->anexo ?? '',
                'observacao_item' => $itemsSolicitacao->observacao_item ?? '',
                'item_id' => $itemsSolicitacao->id ?? 'null',
            ];
        }

        return response()->json($result);
    }

    public static function assumirSolicitacao(Request $request)
    {
        try {
            $id = $request->input('id');
            $userId = Auth::user()->id;

            // Verificar se já foi iniciada por outro comprador
            if (self::verificarUsuario($id, $userId)) {
                return response()->json([
                    'success' => false,
                    'title' => 'Atenção',
                    'message' => 'Solicitação já iniciada por outro comprador'
                ]);
            }

            // Verificar status da compra
            if (self::verificarStatusCompras($id)) {
                return response()->json([
                    'success' => false,
                    'title' => 'Atenção',
                    'message' => 'Solicitação já iniciada por outro comprador'
                ]);
            }

            // Vincular comprador
            self::vincularComprador($userId, $id);

            return response()->json([
                'success' => true,
                'title' => 'Sucesso',
                'message' => 'Solicitação iniciada e vinculada ao comprador.'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao assumir solicitação:', [
                'id' => $request->input('id'),
                'user_id' => Auth::user()->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'title' => 'Erro',
                'message' => 'Erro ao assumir solicitação: ' . $e->getMessage()
            ]);
        }
    }

    private static function vincularComprador($idComprador, $idSolicitacaoCompras)
    {
        try {
            DB::beginTransaction();

            // Atualizar solicitação com comprador e status
            $solicitacao = SolicitacaoCompra::where('id_solicitacoes_compras', $idSolicitacaoCompras)
                ->firstOrFail();

            $solicitacao->update([
                'id_comprador' => $idComprador,
                'situacao_compra' => 'INICIADA'
            ]);

            $solicitacao->registrarLog('INICIADA', Auth::id(), 'Solicitação de Iniciada');

            // Se há ordem de serviço vinculada, atualizar status dos serviços
            if (!empty($idOrdem)) {
                try {
                    // Tentar primeiro buscar por servicossolicitacoescompras (se existir)
                    $servicosSolicitacao = [];

                    if (class_exists(ServicoSolicitacaoCompra::class)) {
                        $servicosSolicitacao = ServicoSolicitacaoCompra::where('id_solicitacao_compra', $idSolicitacaoCompras)
                            ->pluck('id_servico')
                            ->toArray();
                    }

                    if (!empty($servicosSolicitacao)) {
                        // Atualizar baseado nos serviços específicos da solicitação
                        $updateCount = OrdemServicoServicos::where('id_ordem_servico', $idOrdem)
                            ->whereIn('id_servicos', $servicosSolicitacao)
                            ->update(['status_servico' => 'INICIADO COTAÇÃO DE SERVIÇO']);
                    } else {
                        // Fallback: atualizar todos os serviços da ordem de serviço
                        $updateCount = OrdemServicoServicos::where('id_ordem_servico', $idOrdem)
                            ->update(['status_servico' => 'INICIADO COTAÇÃO DE SERVIÇO']);
                    }
                } catch (\Exception $serviceUpdateError) {
                    Log::warning('Erro ao atualizar status dos serviços, continuando...', [
                        'id_ordem_servico' => $idOrdem,
                        'error' => $serviceUpdateError->getMessage()
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao vincular comprador:', [
                'id_comprador' => $idComprador,
                'id_solicitacao_compras' => $idSolicitacaoCompras,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    private static function verificarStatusCompras($id)
    {
        try {
            $solicitacao = SolicitacaoCompra::select('situacao_compra')
                ->where('id_solicitacoes_compras', $id)
                ->first();

            if (!$solicitacao) {
                return false;
            }

            $jaIniciada = $solicitacao->situacao_compra === 'INICIADA';

            return $jaIniciada;
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status da compra:', [
                'id' => $id,
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }

    private static function verificarUsuario($id, $userId)
    {
        try {
            // Buscar a solicitação para verificar o comprador atual
            $solicitacao = SolicitacaoCompra::select('id_comprador')
                ->where('id_solicitacoes_compras', $id)
                ->first();

            if (!$solicitacao) {
                return true; // Em caso de não encontrar, bloqueia por segurança
            }

            // Se já tem um comprador e não é o usuário atual
            if ($solicitacao->id_comprador && $solicitacao->id_comprador != $userId) {
                return true; // Bloqueia - já tem outro comprador
            }

            return false; // Permite assumir

        } catch (\Exception $e) {
            Log::error('Erro ao verificar usuário:', [
                'id' => $id,
                'user_id' => $userId,
                'message' => $e->getMessage()
            ]);
            return true; // Em caso de erro, bloqueia por segurança
        }
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


    public static function mudarStatusSolicitante(Request $request)
    {
        try {
            DB::beginTransaction();

            $idUser = Auth::user()->id;
            $idSolicitacoes = $request->input('id_solicitacoes_compras');

            // // Verificar se a situação permite a alteração
            // $situacoesPermitidas = ['Iniciada', 'INICIADA', 'COTAÇÕES RECUSADAS PELO GESTOR'];

            // if (!in_array($request->input('situacao_compra'), $situacoesPermitidas)) {
            //     Log::warning('Tentativa de mudar situação de compra sem permissão', [
            //         'id_solicitacoes_compras' => $idSolicitacoes,
            //         'situacao_compra' => $request->input('situacao_compra')
            //     ]);

            //     return response()->json([
            //         'success' => false,
            //         'title' => 'Info',
            //         'message' => 'Cotação não iniciada ou em andamento',
            //     ]);


            // }

            // Atualizar status da solicitação de compras
            $solicitacao = SolicitacaoCompra::where('id_solicitacoes_compras', $idSolicitacoes)->first();

            $solicitacao->update(['situacao_compra' => 'AGUARDANDO VALIDAÇÃO DO SOLICITANTE']);

            $solicitacao->registrarLog('AGUARDANDO VALIDAÇÃO DO SOLICITANTE', $idUser);

            // Atualizar comprador nas cotações
            Cotacoes::where('id_solicitacoes_compras', $idSolicitacoes)
                ->update(['id_comprador' => $idUser]);

            // Buscar ID da ordem de serviço
            $solicitacao = SolicitacaoCompra::select('id_ordem_servico')
                ->where('id_solicitacoes_compras', $idSolicitacoes)
                ->first();

            $idOrdem = $solicitacao->id_ordem_servico ?? null;

            // Se há ordem de serviço, atualizar peças relacionadas
            if (!empty($idOrdem)) {
                // Buscar IDs das peças da ordem de serviço relacionadas aos itens da solicitação
                $ordemServicoPecasIds = DB::table('solicitacoescompras as s')
                    ->join('ordem_servico as os', 'os.id_ordem_servico', '=', 's.id_ordem_servico')
                    ->join('ordem_servico_pecas as oss', 'oss.id_ordem_servico', '=', 'os.id_ordem_servico')
                    ->join('itenssolicitacoescompras as sc', function ($join) {
                        $join->on('sc.id_solicitacao_compra', '=', 's.id_solicitacoes_compras')
                            ->on('oss.id_produto', '=', 'sc.id_produto');
                    })
                    ->where('s.id_solicitacoes_compras', $idSolicitacoes)
                    ->pluck('oss.id_ordem_servico_pecas')
                    ->toArray();

                // Atualizar situação das peças se houver
                if (!empty($ordemServicoPecasIds)) {
                    DB::table('ordem_servico_pecas')
                        ->whereIn('id_ordem_servico_pecas', $ordemServicoPecasIds)
                        ->update(['situacao_pecas' => 'AGUARDANDO VALIDAÇÃO GESTOR']);
                }
            }

            // // Buscar aprovador para envio de notificação
            // $aprovador = DB::table('solicitacoescompras as sc')
            //     ->join('aprovadorespedidos as ap', 'ap.id_usuario', '=', 'sc.id_aprovador')
            //     ->join('v_usuarios as va', 'va.id', '=', 'ap.id_usuario')
            //     ->select([
            //         'ap.data_inclusao',
            //         'ap.id_usuario',
            //         'va.name',
            //         DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(ap.telefone, ' ', ''), '-', ''), '(', ''), ')', '') as telefone"),
            //         'ap.valor_aprovacao'
            //     ])
            //     ->where('sc.id_solicitacoes_compras', $idSolicitacoes)
            //     ->where(function ($query) {
            //         $query->where('ap.tipo_solicitacao_compras', true)
            //             ->orWhere('ap.tipo_gerencial', true);
            //     })
            //     ->orderByDesc('ap.data_inclusao')
            //     ->first();

            DB::commit();

            // Enviar notificações se aprovador encontrado
            // if ($aprovador) {
            //     $idUserAprovador = $aprovador->id_usuario;
            //     $nome = $aprovador->name;
            //     $telefone = $aprovador->telefone;

            //     if (!empty($telefone) && !empty($nome)) {
            //         $baseUrl = config('app.url');
            //         $texto = "*Atenção:* A solicitação de compras n° {$idSolicitacoes} está esperando sua aprovação.\n"
            //             . "[Abrir listagem de pedidos]\n {$baseUrl}/admin/compras/solicitacoes/{$idSolicitacoes}/aprovar\n";

            //         // Enviar WhatsApp (assumindo que o serviço existe)
            //         if (class_exists('IntegracaoWhatssappCarvalimaService')) {
            //             IntegracaoWhatssappCarvalimaService::enviarMensagem($texto, $nome, $telefone);
            //         }
            //     }

            //     // Registrar notificação no sistema (assumindo que o serviço existe)
            //     if (class_exists('SystemNotification')) {
            //         $notificationParam = ['key' => $idSolicitacoes];
            //         $icon = 'fas fa-clipboard-check';
            //         $action = route('admin.compras.cotacoes.show', $idSolicitacoes);

            //         SystemNotification::register(
            //             $idUserAprovador,
            //             'Cotação',
            //             'Orçamento Aguardando Aprovação',
            //             $action,
            //             'Orçamento Aguardando Aprovação',
            //             $icon
            //         );
            //     }
            // }

            return response()->json([
                'success' => true,
                'message' => 'Cotações enviadas ao solicitante',
                'redirect' => route('admin.compras.cotacoes.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao mudar status do solicitante:', [
                'id_solicitacoes_compras' => $param['id_solicitacoes_compras'] ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'title' => 'Erro',
                'message' => 'Erro ao processar solicitação: ' . $e->getMessage()
            ]);
        }
    }

    public static function mudarStatus(Request $request)
    {
        try {
            $situacaoCompra = $request->input('situacao_compra');
            $idSolicitacoes = $request->input('id_solicitacoes_compras');
            $idgrupo = $request->input('id_grupo_despesa');

            // Verificar se a situação permite a alteração
            $situacoesPermitidas = ['Iniciada', 'INICIADA', 'COTAÇÕES RECUSADAS PELO GESTOR'];

            if (!in_array($situacaoCompra, $situacoesPermitidas)) {
                return response()->json([
                    'success' => false,
                    'title' => 'Info',
                    'message' => 'Cotação já em andamento ou finalizada'
                ]);
            }

            DB::beginTransaction();

            $idUser = Auth::user()->id;

            // Atualizar solicitação de compras
            $solicitacao = SolicitacaoCompra::where('id_solicitacoes_compras', $idSolicitacoes)
                ->first();

            $solicitacao->update(['situacao_compra' => 'AGUARDANDO APROVAÇÃO']);

            // Registrar log da mudança de status
            $solicitacao->registrarLog(
                'AGUARDANDO APROVAÇÃO',
                $idUser,
                'Solicitação enviada para aprovação'
            );

            // Atualizar cotações
            Cotacoes::where('id_solicitacoes_compras', $idSolicitacoes)
                ->update(['id_comprador' => $idUser]);

            // Buscar aprovadores usando query builder para consulta complexa
            $aprovadores = DB::select("
                WITH valor AS (
                    SELECT
                        SUM(COALESCE(cts.valor_item, 1)) AS valor,
                        1 AS id
                    FROM solicitacoescompras sc
                    INNER JOIN cotacoes ct ON ct.id_solicitacoes_compras = sc.id_solicitacoes_compras
                    INNER JOIN cotacoesitens cts ON cts.id_cotacao = ct.id_cotacoes
                    WHERE sc.id_solicitacoes_compras = ?
                ),
                quantidade AS (
                    SELECT
                        COUNT(ct.id_cotacoes) AS quantidade,
                        1 AS id
                    FROM solicitacoescompras sc
                    INNER JOIN cotacoes ct ON ct.id_solicitacoes_compras = sc.id_solicitacoes_compras
                    WHERE sc.id_solicitacoes_compras = ?
                )
                SELECT DISTINCT
                   ap.id_usuario,
                   va.name,
                   REPLACE(REPLACE(REPLACE(REPLACE(ap.telefone, ' ', ''), '-', ''), '(', ''), ')', '') AS telefone,
                   ap.valor_aprovacao
                FROM aprovadorespedidos ap
                   INNER JOIN v_usuarios va ON va.id = ap.id_usuario
                WHERE ap.id_usuario IN (10,19,28,44,1)
                AND ap.id_grupo_despesa = ?
                AND (ap.tipo_solicitacao_compras IS TRUE OR ap.tipo_gerencial IS TRUE)
                AND (
                    SELECT
                        COALESCE(v.valor, 1) / NULLIF(COALESCE(q.quantidade, 1), 0) AS retorno_
                    FROM valor v
                    INNER JOIN quantidade q ON q.id = v.id
                ) BETWEEN ap.valor_aprovacao AND ap.valor_aprovacao_final
                ORDER BY ap.valor_aprovacao ASC
            ", [$idSolicitacoes, $idSolicitacoes, $idgrupo]);

            DB::commit();

            // // Processar aprovadores e enviar notificações
            // if (!empty($aprovadores)) {
            //     foreach ($aprovadores as $aprovador) {
            //         $idUserAprovador = $aprovador->id_usuario;
            //         $nome = $aprovador->name;
            //         $telefone = $aprovador->telefone;

            //         if (!empty($telefone) && !empty($nome)) {
            //             $baseUrl = config('app.url');
            //             $texto = "*Atenção:* A solicitação de compras n° {$idSolicitacoes} está esperando sua aprovação.\n"
            //                 . "[Abrir listagem de pedidos]\n {$baseUrl}/admin/compras/cotacoes/{$idSolicitacoes}/edit\n";

            //             // Enviar WhatsApp se o serviço estiver disponível
            //             if (class_exists('IntegracaoWhatssappCarvalimaService')) {
            //                 IntegracaoWhatssappCarvalimaService::enviarMensagem($texto, $nome, $telefone);
            //             }
            //         }
            //     }

            // Registrar notificação no sistema (se disponível)
            // $notificationParam = ['key' => $idSolicitacoes];
            // $icon = 'fas fa-clipboard-check';
            // SystemNotification::register($idUser, 'Cotação', 'Orçamento Aguardando Aprovação',
            //     route('admin.compras.cotacoes.edit', $idSolicitacoes), 'Orçamento Aguardando Aprovação', $icon);
            // }

            return response()->json([
                'success' => true,
                'title' => 'Sucesso',
                'message' => 'Status da cotação alterado com sucesso!',
                'redirect' => route('admin.compras.cotacoes.edit', $idSolicitacoes)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao mudar status da cotação:', [
                'id_solicitacoes_compras' => $request->input('id_solicitacoes_compras'),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'title' => 'Erro',
                'message' => 'Erro ao alterar status: ' . $e->getMessage()
            ]);
        }
    }

    public function imprimirCotacao(Request $request)
    {
        try {
            // Receber como arrays ou converter para arrays se necessário
            $solicitacao = $request->input('solicitacao', []);

            // Converter strings em arrays se necessário
            if (!is_array($solicitacao) && !empty($solicitacao)) {
                $solicitacao = [$solicitacao];
            }

            // Processar filtros
            // solicitacao
            if (empty($solicitacao) || in_array('', $solicitacao) || in_array('0', $solicitacao)) {
                $P_in_codigo = '=';
                $P_id_codigo = '';
            } else {
                $P_in_codigo = '=';
                $P_id_codigo = implode(",", array_filter($solicitacao));
            }

            $parametros = array(
                'P_in_codigo' => $P_in_codigo,
                'P_id_codigo' => $P_id_codigo,
            );

            // Resto da lógica do relatório...
            $name = 'cotacaoDocument';
            $agora = date('d-m-YH:i');
            $tipo = '.pdf';
            $relatorio = $name . $agora . $tipo;

            $partes = parse_url('http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
            $host = $partes['host'] . PHP_EOL;
            $pathrel = (explode('.', $host));
            $dominio = $pathrel[0];

            if ($dominio == '127' || $dominio == 'localhost' || strpos($host, '127.0.0.1') !== false) {
                $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
                $pastarelatorio = '/reports/homologacao/' . $name;
            } elseif ($dominio == 'lcarvalima') {
                $jasperserver = 'http://10.10.1.8:8080/jasperserver';
                $input = '/reports/carvalima/' . $name;

                // Verificar se o diretório existe antes de tentar chmod
                if (is_dir($input)) {
                    chmod($input, 0777);
                } else {
                    Log::warning('Diretório não encontrado: ' . $input);
                }

                $pastarelatorio = $input;
            } else {
                $jasperserver = 'http://10.10.1.8:8080/jasperserver';
                $input = '/reports/' . $dominio . '/' . $name;

                // Verificar se o diretório existe antes de tentar chmod
                if (is_dir($input)) {
                    chmod($input, 0777);
                } else {
                    Log::warning('Diretório não encontrado: ' . $input);
                }

                $pastarelatorio = $input;
            }

            $jsi = new JasperServerIntegration(
                $jasperserver,
                $pastarelatorio,
                'pdf',
                'unitop',
                'unitop2022',
                $parametros
            );

            try {
                $data = $jsi->execute();
                return response($data, 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="' . $relatorio . '"');
            } catch (\Exception $e) {
                Log::error('Erro ao gerar relatório: ' . $e->getMessage());
                return back()->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Não foi possível gerar o relatório. ' . $e->getMessage()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro geral: ' . $e->getMessage());
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro inesperado: ' . $e->getMessage()
            ]);
        }
    }

    public function salvarItensCotacao(Request $request)
    {
        // cotação (já mostrado antes)
        $cotacaoId = $request->input('cotacao_info');

        $cotacao = Cotacoes::find($cotacaoId);
        $cotacao->update([
            'data_entrega' => $request->input('data_entrega'),
            'condicao_pag' => $request->input('condicao_pag'),
            'data_alteracao' => now()
        ]);

        // pega o array de itens (se não existir, vira array vazio)
        $itens = $request->input('itens_cotacao', []);

        foreach ($itens as $key => $item) {
            $cotacoesitens = CotacoesItens::find($item['id_cotacoes_itens']);
            $cotacoesitens->update([
                'quantidade_fornecedor' => $item['quantidade_fornecedor'],
                'valorunitario' => $item['valorunitario'],
                'condicao_pag' => $cotacao->condicao_pag,
                'valor_item' => $item['valor_item'],
                'valor_desconto' => $item['valor_desconto'],
                'data_alteracao' => now()
            ]);
        }

        DB::connection('pgsql')->select(
            "SELECT * FROM fc_atualizar_valor_cotacao(?)",
            [$cotacaoId]
        );

        return response()->json([
            'success' => true,
        ]);
    }

    public function validarMapaCotacao($idSolicitacao, $idOrdem)
    {
        // Validações básicas
        if (empty($idSolicitacao) || empty($idOrdem)) {
            return false;
        }

        try {
            // Conta cotações da solicitação
            $count = Cotacoes::where('id_solicitacoes_compras', $idSolicitacao)->count();

            if ($count !== 1) {
                return false;
            }

            // IDs das cotações relacionadas à solicitação
            $cotacaoIds = Cotacoes::where('id_solicitacoes_compras', $idSolicitacao)
                ->pluck('id_cotacoes')
                ->toArray();

            if (empty($cotacaoIds)) {
                return false;
            }

            // Soma valor_desconto (com fallback para 2) e identifica o fornecedor (considera apenas cotações com data_entrega)
            $registro = CotacoesItens::selectRaw('SUM(COALESCE(valor_desconto, 2)) as valor_com_desconto, cotacoes.id_fornecedor')
                ->join('cotacoes', 'cotacoes.id_cotacoes', '=', 'cotacoesitens.id_cotacao')
                ->whereIn('id_cotacao', $cotacaoIds)
                ->whereNotNull('cotacoes.data_entrega')
                ->groupBy('cotacoes.id_fornecedor')
                ->first();

            if (!$registro) {
                return false;
            }


            $valorSolicitacao = (float) $registro->valor_com_desconto;
            $fornecedor = $registro->id_fornecedor;


            if (empty($fornecedor)) {
                return false;
            }

            // Soma valores da ordem de serviço para o fornecedor usando Eloquent
            $valorOrdem = (float) OrdemServicoPecas::where('id_ordem_servico', $idOrdem)
                ->where('id_fornecedor', $fornecedor)
                ->sum(DB::raw('COALESCE(valor_total_com_desconto, 0)'));

            // Comparação com tolerância para evitar problemas de ponto flutuante
            $precision = 0.0001;

            return abs($valorSolicitacao - $valorOrdem) < $precision;
        } catch (\Exception $e) {
            Log::error('Erro em validarMapaCotacao', [
                'idSolicitacao' => $idSolicitacao,
                'idOrdem' => $idOrdem,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Buscar dados para gráfico de descontos das cotações finalizadas
     */
    public function getDadosGraficoDescontos($idSolicitacao = null)
    {
        try {
            // Buscar cotações da solicitação
            $queryBase = Cotacoes::select('cotacoes.*');

            // Filtrar por solicitação específica se fornecida
            if ($idSolicitacao) {
                $queryBase->where('cotacoes.id_solicitacoes_compras', $idSolicitacao);
            }

            $cotacoes = $queryBase->with(['fornecedor', 'itens.produto'])->get();


            return $cotacoes->map(function ($cotacao) {
                // Buscar todos os itens da cotação
                $itens = CotacoesItens::where('id_cotacao', $cotacao->id_cotacoes)
                    ->with('produto')
                    ->get();


                // Calcular totais
                $valorTotalSemDesconto = $itens->sum(function ($item) {
                    return ($item->quantidade_fornecedor ?? 0) * ($item->valor_unitario ?? 0);
                });

                $valorTotalComDesconto = $itens->sum(function ($item) {
                    $valorSemDesconto = ($item->quantidade_fornecedor ?? 0) * ($item->valor_unitario ?? 0);
                    return $valorSemDesconto - ($item->valor_desconto ?? 0);
                });

                return [
                    'nome_fornecedor' => $cotacao->fornecedor->nome_fornecedor ?? 'Fornecedor não encontrado',
                    'id_cotacoes' => $cotacao->id_cotacoes,
                    'id_fornecedor' => $cotacao->id_fornecedor,
                    'data_entrega' => $cotacao->data_entrega,
                    'condicao_pag' => $cotacao->condicao_pag,
                    'valor_total_sem_desconto' => (float) $valorTotalSemDesconto,
                    'valor_desconto' => (float) $valorTotalComDesconto,
                    'itens' => $itens->map(function ($item) {
                        return [
                            'id_cotacoes_itens' => $item->id_cotacoes_itens,
                            'id_produto' => $item->id_produto,
                            'descricao_produto' => $item->produto->descricao_produto ?? 'Produto não encontrado',
                            'unidade' => $item->produto->unidade ?? 'UN',
                            'quantidade' => $item->quantidade_fornecedor ?? 0,
                            'valor_unitario' => (float) ($item->valor_unitario ?? 0),
                            'valor_desconto' => (float) ($item->valor_desconto ?? 0),
                            'data_entrega' => $item->data_entrega ?? ($cotacao->data_entrega ?? null)
                        ];
                    })->toArray()
                ];
            });
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do gráfico de descontos:', [
                'idSolicitacao' => $idSolicitacao,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return collect();
        }
    }

    public function onEnviarCotacoes(Request $request)
    {
        try {
            // Buscar configurações do arquivo de config
            $config = config('cotacao-email');

            // Configurações do servidor SMTP
            $host = $config['smtp']['host'];
            $port = $config['smtp']['port'];
            $username = $config['smtp']['username'];
            $password = $config['smtp']['password'];

            // Pegar o ID da solicitação do request
            $idSolicitacao = $request->input('id_solicitacoes_compras');

            // Dados do email vindos da configuração
            $from = $config['from']['email'];
            $subject = $config['subject'];
            $empresa = $config['empresa']['nome'];
            $enderecoEmpresa = $config['empresa']['endereco'];

            // Validar se as filiais estão preenchidas
            $filialEntrega = $request->input('filial_entrega');
            $filialFaturamento = $request->input('filial_faturamento');

            if (empty($filialEntrega) || empty($filialFaturamento)) {
                return response()->json([
                    'success' => false,
                    'title' => 'Atenção',
                    'message' => 'Não é possível enviar a cotação sem Filial de Faturamento e Entrega preenchidos.'
                ]);
            }

            if (!empty($idSolicitacao)) {
                // Buscar cotações da solicitação com informações do fornecedor
                $cotacoes = Cotacoes::where('id_solicitacoes_compras', $idSolicitacao)
                    ->with('fornecedor')
                    ->get();

                if ($cotacoes->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'title' => 'Atenção',
                        'message' => 'Nenhuma cotação encontrada para esta solicitação.'
                    ]);
                }

                $emailsEnviados = 0;
                $emailsComErro = 0;
                $detalhesErros = [];

                // Instanciar o serviço de email usando injeção de dependência
                $emailSenderService = app(EmailSenderService::class);

                foreach ($cotacoes as $cotacao) {
                    try {
                        // Rate limiting - delay entre emails se habilitado
                        if ($config['rate_limiting']['enabled'] && $emailsEnviados > 0) {
                            $delay = $config['rate_limiting']['delay_between_emails'];

                            sleep($delay);
                        }

                        // Obter email do fornecedor - verificar tanto na cotação quanto no fornecedor relacionado
                        $emailFornecedor = $cotacao->email ?? $cotacao->fornecedor->email ?? null;
                        $nomeFornecedor = $cotacao->nome_fornecedor ?? $cotacao->fornecedor->nome_fantasia ?? 'Fornecedor';

                        // Validar se o email do fornecedor existe
                        if (empty($emailFornecedor)) {
                            $emailsComErro++;
                            $detalhesErros[] = "Fornecedor '{$nomeFornecedor}' não possui email cadastrado";
                            if ($config['logging']['log_errors']) {
                                Log::warning("Cotação sem email:", [
                                    'id_cotacao' => $cotacao->id_cotacoes,
                                    'fornecedor' => $nomeFornecedor
                                ]);
                            }
                            continue;
                        }

                        // Validar formato do email se habilitado na config
                        if ($config['validation']['validate_email_format'] && !filter_var($emailFornecedor, FILTER_VALIDATE_EMAIL)) {
                            $emailsComErro++;
                            $detalhesErros[] = "Email inválido para '{$nomeFornecedor}': {$emailFornecedor}";
                            if ($config['logging']['log_errors']) {
                                Log::warning("Email inválido:", [
                                    'id_cotacao' => $cotacao->id_cotacoes,
                                    'email' => $emailFornecedor,
                                    'fornecedor' => $nomeFornecedor
                                ]);
                            }
                            continue;
                        }

                        // Log da tentativa de envio se habilitado
                        if ($config['logging']['enabled']) {
                            Log::info("Tentando enviar email para:", [
                                'id_cotacao' => $cotacao->id_cotacoes,
                                'email' => $emailFornecedor,
                                'fornecedor' => $nomeFornecedor
                            ]);
                        }

                        // Enviar email usando o novo serviço
                        $resultado = $emailSenderService->sendEmail(
                            $host,
                            $port,
                            $username,
                            $password,
                            $from,
                            $emailFornecedor,
                            $subject,
                            $empresa,
                            $enderecoEmpresa,
                            $cotacao->id_cotacoes,
                            $nomeFornecedor
                        );

                        if ($resultado) {
                            $emailsEnviados++;

                            // Rate limiting - pausa entre emails para evitar bloqueios
                            if ($config['rate_limiting']['enabled'] && $config['rate_limiting']['delay_between_emails'] > 0) {
                                sleep($config['rate_limiting']['delay_between_emails']);
                            }
                        } else {
                            $emailsComErro++;

                            // Verificar se é um bloqueio SMTP fazendo um teste específico
                            $phpMailerService = app(PHPMailerService::class);
                            $phpMailerService->configureSMTP([
                                'host' => $host,
                                'port' => $port,
                                'auth' => true,
                                'username' => $username,
                                'password' => $password,
                                'encryption' => PHPMailer::ENCRYPTION_STARTTLS,
                            ]);

                            $isBlocked = $phpMailerService->testForBlocking();

                            if ($isBlocked) {
                                $detalhesErros[] = "SMTP bloqueado temporariamente - '{$nomeFornecedor}' ({$emailFornecedor})";
                            } else {
                                $detalhesErros[] = "Falha ao enviar para '{$nomeFornecedor}' ({$emailFornecedor})";
                            }

                            if ($config['logging']['log_errors']) {
                                Log::error("Falha no envio do email:", [
                                    'id_cotacao' => $cotacao->id_cotacoes,
                                    'email' => $emailFornecedor,
                                    'fornecedor' => $nomeFornecedor
                                ]);
                            }
                        }
                    } catch (\Exception $emailException) {
                        $emailsComErro++;
                        $detalhesErros[] = "Erro ao enviar para '{$nomeFornecedor}': " . $emailException->getMessage();
                        if ($config['logging']['log_errors']) {
                            Log::error("Exceção ao enviar email:", [
                                'id_cotacao' => $cotacao->id_cotacoes ?? 'N/A',
                                'fornecedor' => $nomeFornecedor ?? 'N/A',
                                'error' => $emailException->getMessage(),
                                'trace' => $emailException->getTraceAsString()
                            ]);
                        }
                    }
                }

                // Preparar mensagem de retorno
                $mensagem = "Processo concluído: {$emailsEnviados} email(s) enviado(s)";
                if ($emailsComErro > 0) {
                    $mensagem .= " e {$emailsComErro} erro(s)";

                    // Verificar se há bloqueios SMTP nos erros
                    $temBloqueioSMTP = false;
                    foreach ($detalhesErros as $erro) {
                        if (strpos($erro, 'SMTP bloqueado') !== false) {
                            $temBloqueioSMTP = true;
                            break;
                        }
                    }

                    if ($temBloqueioSMTP) {
                        $mensagem .= ". ATENÇÃO: Servidor SMTP temporariamente bloqueado por excesso de tentativas. Aguarde alguns minutos e tente novamente.";
                    }
                }

                return response()->json([
                    'success' => $emailsEnviados > 0,
                    'title' => $emailsEnviados > 0 ? 'Sucesso' : 'Atenção',
                    'message' => $mensagem,
                    'data' => [
                        'emails_enviados' => $emailsEnviados,
                        'emails_com_erro' => $emailsComErro,
                        'total_cotacoes' => $cotacoes->count(),
                        'detalhes_erros' => $detalhesErros
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'title' => 'Atenção',
                'message' => 'ID da solicitação não informado.'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro geral ao enviar cotações:', [
                'id_solicitacao' => $request->input('id_solicitacoes_compras'),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'title' => 'Erro',
                'message' => 'Erro ao enviar cotações: ' . $e->getMessage()
            ]);
        }
    }

    public function getSolicitacao($id)
    {
        try {
            $solicitacao = VSolicitacoesComprasV2::where('id_solicitacoes_compras', $id)->firstOrFail();
            $usuarios = User::select('id', 'name', 'email')->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'solicitacao' => [
                    'id' => $solicitacao->id_solicitacoes_compras,
                    'id_comprador' => $solicitacao->id_comprador,
                    'comprador' => $solicitacao->comprador,
                    'situacao' => $solicitacao->situacao_compra
                ],
                'usuarios' => $usuarios
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados da solicitação:', [
                'id' => $id,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dados da solicitação.'
            ], 404);
        }
    }

    public function trocarComprador(Request $request, $id)
    {
        try {
            $novoCompradorId = $request->input('id_comprador');

            if (!$novoCompradorId) {
                return response()->json([
                    'success' => false,
                    'title' => 'Erro',
                    'message' => 'Selecione um novo comprador.'
                ]);
            }

            DB::beginTransaction();

            // Buscar solicitação
            $solicitacao = SolicitacaoCompra::where('id_solicitacoes_compras', $id)
                ->firstOrFail();

            // Buscar comprador anterior
            $compradorAnteriorNome = 'Nenhum';
            if ($solicitacao->id_comprador) {
                $compradorAnteriorObj = User::find($solicitacao->id_comprador);
                if ($compradorAnteriorObj) {
                    // Pegar apenas o primeiro nome
                    $compradorAnteriorNome = explode(' ', $compradorAnteriorObj->name)[0];
                }
            }

            // Buscar novo comprador
            $novoComprador = User::findOrFail($novoCompradorId);
            $novoCompradorNome = explode(' ', $novoComprador->name)[0];

            $solicitacao->update([
                'id_comprador' => $novoCompradorId,
                'justificativa_edit_or_delete' => 'COMPRADOR ALTERADO PELO ADMINISTRADOR'
            ]);

            // Registrar log da alteração
            $solicitacao->registrarLog(
                'COMPRADOR ALTERADO',
                Auth::id(),
                "Trocou o comprador {$compradorAnteriorNome} para o comprador {$novoCompradorNome}"
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'title' => 'Sucesso',
                'message' => "Comprador alterado para '{$novoComprador->name}' com sucesso!"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao trocar comprador:', [
                'id_solicitacao_compras' => $id ?? null,
                'novo_comprador_id' => $novoCompradorId ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'title' => 'Erro',
                'message' => 'Erro ao trocar comprador: ' . $e->getMessage()
            ]);
        }
    }

    public function onDevolver(Request $request)
    {
        try {
            $idSolicitacaoCompras = $request->input('id');

            DB::beginTransaction();

            // Atualizar solicitação com comprador e status
            $solicitacao = SolicitacaoCompra::where('id_solicitacoes_compras', $idSolicitacaoCompras)
                ->firstOrFail();

            $solicitacao->update([
                'justificativa_edit_or_delete' => 'DEVOLVIDA PELO COMPRADOR',
                'situacao_compra' => 'AGUARDANDO APROVAÇÃO DO GESTOR DEPARTAMENTO',
                'aprovado_reprovado' => false
            ]);

            // Registrar log da devolução
            $solicitacao->registrarLog(
                'AGUARDANDO APROVAÇÃO DO GESTOR DEPARTAMENTO',
                Auth::id(),
                'Solicitação devolvida pelo comprador'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'title' => 'Sucesso',
                'message' => 'Solicitação devolvida ao solicitante com sucesso!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao vincular comprador:', [
                'id_solicitacao_compras' => $idSolicitacaoCompras,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'title' => 'Erro',
                'message' => 'Erro ao devolver solicitação: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Adiar uma solicitação de compra
     */
    public function adiar(Request $request, $id)
    {
        try {
            // Validação dos dados
            $validatedData = $request->validate([
                'data_adiado' => 'required|date',
                'justificativa_adiado' => 'required|string',
                'id_user_adiado' => 'required|integer|exists:users,id'
            ]);

            DB::beginTransaction();

            // Buscar a solicitação
            $solicitacao = SolicitacaoCompra::where('id_solicitacoes_compras', $id)
                ->firstOrFail();

            // Atualizar os campos de adiamento
            $solicitacao->update([
                'is_adiado' => true,
                'data_adiado' => $validatedData['data_adiado'],
                'justificativa_adiado' => $validatedData['justificativa_adiado'],
                'id_user_adiado' => $validatedData['id_user_adiado']
            ]);

            // Registrar log da alteração
            $usuario = User::find($validatedData['id_user_adiado']);
            $solicitacao->registrarLog(
                'SOLICITAÇÃO ADIADA',
                $validatedData['id_user_adiado'],
                "Solicitação adiada por '{$usuario->name}' em {$validatedData['data_adiado']}. Justificativa: {$validatedData['justificativa_adiado']}"
            );

            DB::commit();

            $solicitacaoDepartamento = $solicitacao->id_departamento;
            $numeroDaSolicitacao = $solicitacao->id_solicitacoes_compras;
            $data = $solicitacao->data_adiado ? \Carbon\Carbon::parse($solicitacao->data_adiado)->format('d/m/Y') : now()->format('d/m/Y');
            $usuario = $solicitacao->userAdiado->name;

            $this->notificationService->sendToDepartments(
                departmentIds: [$solicitacaoDepartamento],
                type: 'solicitacao.adiada',
                title: 'Solicitação de Compra Adiada',
                message: "A solicitação: $numeroDaSolicitacao foi adiada na data: $data pelo usuario: $usuario",
                priority: 'normal',
                icon: 'file-invoice',
                color: 'blue'
            );

            return response()->json([
                'success' => true,
                'title' => 'Sucesso',
                'message' => 'Solicitação adiada com sucesso!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'title' => 'Erro de Validação',
                'message' => 'Dados inválidos: ' . implode(', ', array_map(function ($errors) {
                    return implode(', ', $errors);
                }, $e->errors()))
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao adiar solicitação:', [
                'id_solicitacao_compras' => $id,
                'dados' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'title' => 'Erro',
                'message' => 'Erro ao adiar solicitação: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remover adiamento de uma solicitação de compra
     */
    public function removerAdiamento(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Buscar a solicitação
            $solicitacao = SolicitacaoCompra::where('id_solicitacoes_compras', $id)
                ->firstOrFail();

            // Verificar se está realmente adiada
            if (!$solicitacao->is_adiado) {
                return response()->json([
                    'success' => false,
                    'title' => 'Atenção',
                    'message' => 'Esta solicitação não está adiada.'
                ]);
            }

            // Guardar dados do adiamento para o log
            $usuarioAdiado = $solicitacao->userAdiado;
            $dataAdiado = $solicitacao->data_adiado;
            $justificativaAdiado = $solicitacao->justificativa_adiado;

            // Remover o adiamento - zerar todos os campos
            $solicitacao->update([
                'is_adiado' => false,
                'data_adiado' => null,
                'justificativa_adiado' => null,
                'id_user_adiado' => null
            ]);

            // Registrar log da remoção
            $usuarioAtual = Auth::user();
            $logMessage = "Adiamento removido por '{$usuarioAtual->name}'.";

            if ($usuarioAdiado) {
                $logMessage .= " Adiamento anterior: '{$usuarioAdiado->name}' em {$dataAdiado}";
                if ($justificativaAdiado) {
                    $logMessage .= " - Justificativa: {$justificativaAdiado}";
                }
            }

            $solicitacao->registrarLog(
                'ADIAMENTO REMOVIDO',
                Auth::id(),
                $logMessage
            );

            DB::commit();

            $solicitacaoDepartamento = $solicitacao->id_departamento;
            $numeroDaSolicitacao = $solicitacao->id_solicitacoes_compras;
            $data = now()->format('d/m/Y');

            $this->notificationService->sendToDepartments(
                departmentIds: [$solicitacaoDepartamento],
                type: 'solicitacao.remover_adiamento',
                title: 'Solicitação de Compra Tirada de Adiamento',
                message: "A solicitação: $numeroDaSolicitacao foi tirada de adiamento na data: $data",
                priority: 'normal',
                icon: 'file-invoice',
                color: 'blue'
            );

            return response()->json([
                'success' => true,
                'title' => 'Sucesso',
                'message' => 'Adiamento removido com sucesso!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao remover adiamento:', [
                'id_solicitacao_compras' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'title' => 'Erro',
                'message' => 'Erro ao remover adiamento: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Exibir formulário para unificar cotações
     */
    public function exibirFormularioUnificacao(Request $request)
    {
        $this->authorize('juntar', SolicitacaoCompra::class);

        try {
            // Buscar apenas cotações com situação INICIADA e com comprador preenchido
            $cotacoes = SolicitacaoCompra::query()
                ->with(['comprador', 'filial', 'solicitante', 'departamento', 'grupoDespesa'])
                ->whereIn('situacao_compra', ['INICIADA', 'Iniciada'])
                ->whereNotNull('id_comprador')
                ->orderBy('data_inclusao', 'desc')
                ->paginate(15);

            // Filtros para busca
            $compradores = SolicitacaoCompra::with('comprador')
                ->whereHas('comprador')
                ->get()
                ->map(function ($item) {
                    return [
                        'label' => $item->comprador->name,
                        'value' => $item->id_comprador
                    ];
                })
                ->unique('value')
                ->values()
                ->toArray();

            return view('admin.compras.cotacoes.unificacao.unificar', compact('cotacoes', 'compradores'));
        } catch (\Exception $e) {
            Log::error('Erro ao exibir formulário de unificação de cotações: ' . $e->getMessage());

            return redirect()->route('admin.compras.cotacoes.index')
                ->with('error', 'Erro ao carregar formulário de unificação: ' . $e->getMessage());
        }
    }

    /**
     * Unificar cotações selecionadas
     */
    public function unificarCotacoes(Request $request)
    {
        $this->authorize('juntar', SolicitacaoCompra::class);

        // Validação
        $request->validate([
            'cotacoes_ids' => 'required|array|min:2',
            'cotacoes_ids.*' => 'required|integer|exists:solicitacoescompras,id_solicitacoes_compras',
            'observacao' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();
        try {
            $cotacoesIds = $request->input('cotacoes_ids');
            $observacao = $request->input('observacao');

            // Buscar as cotações selecionadas
            $cotacoes = SolicitacaoCompra::with(['itens.produto', 'itens.servico', 'comprador', 'filial', 'solicitante', 'departamento', 'grupoDespesa'])
                ->whereIn('id_solicitacoes_compras', $cotacoesIds)
                ->get();

            // Verificar se todas as cotações podem ser juntadas
            $compradoresUnicos = [];
            $tiposUnicos = [];

            foreach ($cotacoes as $cotacao) {
                // Verificar se está no status INICIADA
                if (!in_array($cotacao->situacao_compra, ['INICIADA', 'Iniciada'])) {
                    throw new \Exception("A cotação #{$cotacao->id_solicitacoes_compras} não está com situação INICIADA.");
                }

                // Verificar se tem comprador preenchido
                if (empty($cotacao->id_comprador)) {
                    throw new \Exception("A cotação #{$cotacao->id_solicitacoes_compras} não possui comprador preenchido.");
                }

                // Coletar compradores únicos
                if (!in_array($cotacao->id_comprador, $compradoresUnicos)) {
                    $compradoresUnicos[] = $cotacao->id_comprador;
                }

                // Coletar tipos únicos
                if (!in_array($cotacao->tipo_solicitacao, $tiposUnicos)) {
                    $tiposUnicos[] = $cotacao->tipo_solicitacao;
                }
            }

            // Verificar se todos os compradores são iguais
            if (count($compradoresUnicos) > 1) {
                $nomesCompradores = $cotacoes->whereIn('id_comprador', $compradoresUnicos)->pluck('comprador.name')->unique()->implode(', ');
                throw new \Exception("Não é possível juntar cotações com compradores diferentes. Compradores encontrados: {$nomesCompradores}");
            }

            // Verificar se todas as cotações são do mesmo tipo
            if (count($tiposUnicos) > 1) {
                throw new \Exception("Não é possível juntar cotações de tipos diferentes. Só é possível juntar peças com peças ou serviços com serviços.");
            }

            $tipoSolicitacao = $tiposUnicos[0];
            $idComprador = $compradoresUnicos[0];

            // Determinar dados da nova cotação (baseado na primeira cotação)
            $cotacaoPrincipal = $cotacoes->first();

            // Criar a nova solicitação juntada
            $novaSolicitacao = $this->criarSolicitacaoJuntada($cotacoes, $cotacaoPrincipal, $tipoSolicitacao, $observacao, $idComprador);

            // Registrar movimentação e finalizar as solicitações originais
            foreach ($cotacoes as $cotacao) {
                $solicitacao = SolicitacaoCompra::find($cotacao->id_solicitacoes_compras);
                if ($solicitacao) {
                    // Registrar movimentação
                    MovimentacaoSolicitacao::create([
                        'data_inclusao' => now(),
                        'id_solicitacao_antigo' => $solicitacao->id_solicitacoes_compras,
                        'id_solicitacao_novo' => $novaSolicitacao->id_solicitacoes_compras,
                        'situacao_antigo' => $solicitacao->situacao_compra,
                        'situacao_novo' => 'FINALIZADO'
                    ]);

                    // Registrar itens da movimentação
                    foreach ($solicitacao->itens as $itemAntigo) {
                        // Encontrar o item correspondente na nova solicitação
                        $itemNovo = $novaSolicitacao->itens->filter(function ($novoItem) use ($itemAntigo) {
                            if ($itemAntigo->id_produto && $novoItem->id_produto) {
                                return $itemAntigo->id_produto == $novoItem->id_produto;
                            }
                            if ($itemAntigo->id_servico && $novoItem->id_servico) {
                                return $itemAntigo->id_servico == $novoItem->id_servico;
                            }
                            return false;
                        })->first();

                        if ($itemNovo) {
                            MovimentacaoSolicitacaoItens::create([
                                'data_inclusao' => now(),
                                'id_itens_solicitacoes_antigo' => $itemAntigo->id_itens_solicitacoes,
                                'id_itens_solicitacoes_novo' => $itemNovo->id_itens_solicitacoes,
                                'quantidade_solicitada' => $itemAntigo->quantidade_solicitada
                            ]);
                        }
                    }

                    // Finalizar solicitação original
                    $solicitacao->update([
                        'situacao_compra' => 'FINALIZADO',
                        'data_finalizada' => now(),
                        'observacao' => ($solicitacao->observacao ? $solicitacao->observacao . "\n\n" : '') .
                            "Cotação finalizada devido à junção com outras cotações. Nova cotação criada: " .
                            "#{$novaSolicitacao->id_solicitacoes_compras}",
                        'data_alteracao' => now()
                    ]);

                    $solicitacao->registrarLog('FINALIZADO', Auth::id(), 'Cotação finalizada por junção');
                }
            }

            DB::commit();

            $mensagemSucesso = "Cotações juntadas com sucesso! Nova cotação: #{$novaSolicitacao->id_solicitacoes_compras}";

            return redirect()->route('admin.compras.cotacoes.index')
                ->with('success', $mensagemSucesso);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro ao juntar cotações: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Erro ao juntar cotações: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Desmembrar cotação unificada
     */
    public function desmembrarCotacao(Request $request, $id)
    {
        try {
            $this->authorize('juntar', SolicitacaoCompra::class);

            DB::beginTransaction();

            // Buscar a solicitação unificada
            $solicitacaoUnificada = SolicitacaoCompra::where('id_solicitacoes_compras', $id)
                ->where('is_unificado', true)
                ->first();

            if (!$solicitacaoUnificada) {
                Log::warning('Solicitação não encontrada ou não é unificada: ' . $id);
                throw new \Exception('Solicitação não encontrada ou não é uma solicitação unificada.');
            }

            // Buscar movimentações relacionadas
            $movimentacoes = MovimentacaoSolicitacao::where('id_solicitacao_novo', $id)->get();

            if ($movimentacoes->isEmpty()) {
                Log::warning('Nenhuma movimentação encontrada para solicitação: ' . $id);
                throw new \Exception('Não foram encontradas movimentações para esta solicitação unificada.');
            }

            // Restaurar cada solicitação original
            foreach ($movimentacoes as $movimentacao) {
                $solicitacaoOriginal = SolicitacaoCompra::find($movimentacao->id_solicitacao_antigo);

                if ($solicitacaoOriginal) {
                    // Restaurar situação anterior
                    $solicitacaoOriginal->update([
                        'situacao_compra' => $movimentacao->situacao_antigo,
                        'data_alteracao' => now()
                    ]);

                    $solicitacaoOriginal->registrarLog($movimentacao->situacao_antigo, Auth::id(), 'Solicitação restaurada por desmembramento');
                }
            }

            // Cancelar a solicitação unificada
            $solicitacaoUnificada->update([
                'situacao_compra' => 'CANCELADO',
                'data_cancelamento' => now(),
                'observacao' => ($solicitacaoUnificada->observacao ? $solicitacaoUnificada->observacao . "\n\n" : '') .
                    'Solicitação cancelada por desmembramento. Solicitações originais foram restauradas.',
                'data_alteracao' => now()
            ]);

            $solicitacaoUnificada->registrarLog('CANCELADO', Auth::id(), 'Solicitação cancelada por desmembramento');

            DB::commit();

            // Verificar se é uma requisição AJAX/JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cotação desmembrada com sucesso! As solicitações originais foram restauradas.'
                ]);
            }

            return redirect()->route('admin.compras.cotacoes.index')
                ->with('success', 'Cotação desmembrada com sucesso! As solicitações originais foram restauradas.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::error('Erro de autorização ao desmembrar cotação: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para realizar esta ação.'
                ], 403);
            }

            return redirect()->back()
                ->with('error', 'Você não tem permissão para realizar esta ação.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro ao desmembrar cotação: ' . $e->getMessage(), [
                'cotacao_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            // Verificar se é uma requisição AJAX/JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao desmembrar cotação: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Erro ao desmembrar cotação: ' . $e->getMessage());
        }
    }

    /**
     * Criar uma única solicitação juntada para cotações
     */
    private function criarSolicitacaoJuntada($cotacoes, $solicitacaoPrincipal, $tipoSolicitacao, $observacao, $idComprador)
    {
        // Coletar todos os itens das cotações
        $todosItens = [];

        foreach ($cotacoes as $cotacao) {
            if (!$cotacao) continue;

            foreach ($cotacao->itens as $item) {
                $todosItens[] = $item;
            }
        }

        return $this->criarSolicitacaoComItens(
            $solicitacaoPrincipal,
            $todosItens,
            $tipoSolicitacao,
            $observacao,
            $cotacoes->pluck('id_solicitacoes_compras')->toArray(),
            $idComprador
        );
    }

    /**
     * Criar uma nova solicitação com os itens especificados para cotações
     */
    private function criarSolicitacaoComItens($solicitacaoPrincipal, $itens, $tipoSolicitacao, $observacao, $cotacoesOriginais, $idComprador)
    {
        // Preparar observação da nova solicitação
        $observacaoCompleta = "Cotação criada pela junção das cotações: " .
            implode(', ', array_map(fn($id) => "#{$id}", $cotacoesOriginais));

        if ($observacao) {
            $observacaoCompleta .= "\n\nObservação da junção: " . $observacao;
        }

        // Criar nova solicitação com status INICIADA, comprador definido e marcada como unificada
        $novaSolicitacao = SolicitacaoCompra::create([
            'id_departamento' => $solicitacaoPrincipal->id_departamento,
            'id_solicitante' => Auth::id(),
            'prioridade' => $solicitacaoPrincipal->prioridade,
            'id_filial' => $solicitacaoPrincipal->id_filial,
            'filial_entrega' => $solicitacaoPrincipal->filial_entrega,
            'filial_faturamento' => $solicitacaoPrincipal->filial_faturamento,
            'tipo_solicitacao' => $tipoSolicitacao,
            'id_grupo_despesas' => $solicitacaoPrincipal->id_grupo_despesas,
            'id_fornecedor' => $solicitacaoPrincipal->id_fornecedor,
            'observacao' => $observacaoCompleta,
            'situacao_compra' => 'INICIADA',
            'id_comprador' => $idComprador,
            'is_unificado' => true,
            'data_inclusao' => now()
        ]);

        // Consolidar itens iguais (mesmo produto/serviço)
        $itensConsolidados = $this->consolidarItens($itens);

        // Criar itens da nova solicitação
        foreach ($itensConsolidados as $itemConsolidado) {
            $dadosItem = [
                'id_solicitacao_compra' => $novaSolicitacao->id_solicitacoes_compras,
                'data_inclusao' => now(),
                'quantidade_solicitada' => $itemConsolidado['quantidade'],
            ];

            // Adicionar produto ou serviço conforme o tipo
            if ($itemConsolidado['tipo'] === 'produto' && !empty($itemConsolidado['id_produto'])) {
                $dadosItem['id_produto'] = $itemConsolidado['id_produto'];
            } elseif ($itemConsolidado['tipo'] === 'servico' && !empty($itemConsolidado['id_servico'])) {
                $dadosItem['id_produto'] = $itemConsolidado['id_servico']; // O model parece usar id_produto para ambos
            }

            // Adicionar campos opcionais se não estiverem vazios
            if (!empty($itemConsolidado['observacao_item'])) {
                $dadosItem['observacao_item'] = $itemConsolidado['observacao_item'];
            }

            if (!empty($itemConsolidado['justificativa'])) {
                $dadosItem['justificativa_iten_solicitacao'] = $itemConsolidado['justificativa'];
            }

            ItemSolicitacaoCompra::create($dadosItem);
        }

        // Registrar log de unificação com as solicitações originais
        $observacaoLog = "Solicitação criada pela unificação das solicitações: " .
            implode(', ', array_map(fn($id) => "#{$id}", $cotacoesOriginais));

        $novaSolicitacao->registrarLog('UNIFICADA', Auth::id(), $observacaoLog);

        return $novaSolicitacao;
    }

    /**
     * Consolidar itens iguais somando as quantidades (para cotações)
     */
    private function consolidarItens($itens)
    {
        $consolidados = [];

        foreach ($itens as $item) {
            // Criar chave única baseada no tipo e ID do produto/serviço
            $id = $item->id_produto ?: ($item->id_servico ?? null);
            $tipo = $item->id_produto ? 'produto' : 'servico';
            $chave = $tipo . '_' . $id;

            if (isset($consolidados[$chave])) {
                // Se já existe, somar quantidade e juntar justificativas
                $consolidados[$chave]['quantidade'] += ($item->quantidade_solicitada ?: $item->quantidade);

                // Juntar justificativas se diferentes
                $justificativaOriginal = $consolidados[$chave]['justificativa'];
                $novaJustificativa = $item->justificativa_iten_solicitacao ?: $item->justificativa;

                if ($novaJustificativa && $novaJustificativa !== $justificativaOriginal) {
                    $consolidados[$chave]['justificativa'] = trim($justificativaOriginal . "\n" . $novaJustificativa);
                }

                // Juntar observações se diferentes
                $observacaoOriginal = $consolidados[$chave]['observacao_item'];
                $novaObservacao = $item->observacao_item;

                if ($novaObservacao && $novaObservacao !== $observacaoOriginal) {
                    $consolidados[$chave]['observacao_item'] = trim($observacaoOriginal . "\n" . $novaObservacao);
                }
            } else {
                // Se não existe, criar novo item consolidado
                $consolidados[$chave] = [
                    'tipo' => $tipo,
                    'id_produto' => $item->id_produto,
                    'id_servico' => $item->id_servico,
                    'quantidade' => $item->quantidade_solicitada ?: $item->quantidade,
                    'justificativa' => $item->justificativa_iten_solicitacao ?: $item->justificativa,
                    'observacao_item' => $item->observacao_item,
                ];
            }
        }

        return array_values($consolidados);
    }

    /**
     * Exibir formulário para unificar itens de solicitações
     */
    public function exibirFormularioUnificacaoItens(Request $request)
    {
        $this->authorize('juntar', SolicitacaoCompra::class);

        try {
            // Buscar itens de solicitações com situação INICIADA e com comprador preenchido
            $query = ItemSolicitacaoCompra::query()
                ->with(['solicitacaoCompra' => function ($query) {
                    $query->select(['id_solicitacoes_compras', 'id_comprador', 'tipo_solicitacao', 'id_departamento', 'id_filial'])
                        ->with('comprador:id,name');
                }])
                ->whereHas('solicitacaoCompra', function ($query) {
                    $query->whereIn('situacao_compra', ['INICIADA', 'Iniciada'])
                        ->whereNotNull('id_comprador');
                });

            // Aplicar filtros se fornecidos
            if ($request->filled('comprador')) {
                $query->whereHas('solicitacaoCompra', function ($q) use ($request) {
                    $q->where('id_comprador', $request->comprador);
                });
            }

            if ($request->filled('tipo')) {
                $query->whereHas('solicitacaoCompra', function ($q) use ($request) {
                    $q->where('tipo_solicitacao', $request->tipo);
                });
            }

            if ($request->filled('solicitacao')) {
                $query->where('id_solicitacao_compra', 'like', '%' . $request->solicitacao . '%');
            }

            $itens = $query->orderBy('id_solicitacao_compra', 'desc')
                ->orderBy('id_itens_solicitacoes')
                ->paginate(20);

            // Filtros para busca - usar a view que já tem o nome do comprador
            $compradores = VSolicitacoesComprasV2::select('comprador as label', 'comprador as value')
                ->whereNotNull('comprador')
                ->where('comprador', '!=', '')
                ->whereIn('situacao_compra', ['INICIADA', 'Iniciada'])
                ->distinct()
                ->orderBy('comprador')
                ->get()
                ->toArray();

            return view('admin.compras.cotacoes.unificacao.unificar-itens', compact('itens', 'compradores'));
        } catch (\Exception $e) {
            Log::error('Erro ao exibir formulário de unificação de itens: ' . $e->getMessage());

            return redirect()->route('admin.compras.cotacoes.index')
                ->with('error', 'Erro ao carregar formulário de unificação de itens: ' . $e->getMessage());
        }
    }

    /**
     * Unificar itens selecionados em uma nova cotação
     */
    public function unificarItens(Request $request)
    {
        $this->authorize('juntar', SolicitacaoCompra::class);

        $request->validate([
            'itens_ids' => 'required|array|min:2',
            'itens_ids.*' => 'integer|exists:itenssolicitacoescompras,id_itens_solicitacoes',
            'observacao' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // Buscar os itens selecionados com informações das solicitações
            $itens = ItemSolicitacaoCompra::with(['solicitacaoCompra.comprador'])
                ->whereIn('id_itens_solicitacoes', $request->itens_ids)
                ->whereHas('solicitacaoCompra', function ($q) {
                    $q->whereIn('situacao_compra', ['INICIADA', 'Iniciada'])
                        ->whereNotNull('id_comprador');
                })
                ->get();

            if ($itens->isEmpty()) {
                throw new \Exception('Nenhum item válido encontrado para unificação.');
            }

            // Verificar se todos os itens têm o mesmo comprador
            $compradores = $itens->pluck('solicitacaoCompra.id_comprador')->unique();
            if ($compradores->count() > 1) {
                throw new \Exception('Todos os itens devem ter o mesmo comprador.');
            }

            // Verificar se todos os itens são do mesmo tipo
            $tipos = $itens->pluck('solicitacaoCompra.tipo_solicitacao')->unique();
            if ($tipos->count() > 1) {
                throw new \Exception('Todos os itens devem ser do mesmo tipo (produtos ou serviços).');
            }

            $primeiroItem = $itens->first();
            $idComprador = $primeiroItem->solicitacaoCompra->id_comprador;
            $tipoSolicitacao = $primeiroItem->solicitacaoCompra->tipo_solicitacao;

            // Consolidar itens iguais
            $itensConsolidados = $this->consolidarItens($itens->toArray());

            // Criar nova solicitação/cotação
            $novaSolicitacao = SolicitacaoCompra::create([
                'tipo_solicitacao' => $tipoSolicitacao,
                'id_departamento' => $primeiroItem->solicitacaoCompra->id_departamento,
                'id_comprador' => $idComprador,
                'id_filial' => $primeiroItem->solicitacaoCompra->id_filial,
                'data_inclusao' => now(),
                'usuario_inclusao' => Auth::user()->name,
                'situacao_compra' => 'INICIADA',
                'unificada' => true,
                'observacao_unificacao' => $request->observacao ?? 'Unificação de itens de múltiplas solicitações',
            ]);

            Log::info('Nova cotação criada para unificação de itens', [
                'cotacao_id' => $novaSolicitacao->id_solicitacoes_compras,
                'id_comprador' => $idComprador,
                'tipo' => $tipoSolicitacao,
                'itens_count' => count($itensConsolidados)
            ]);

            // Registrar log da criação da nova solicitação
            $novaSolicitacao->registrarLog(
                'INICIADA',
                Auth::id(),
                'Solicitação criada por unificação de itens: ' . ($request->observacao ?? 'Unificação de itens de múltiplas solicitações')
            );

            // Registrar movimentação principal
            $movimentacao = MovimentacaoSolicitacao::create([
                'id_solicitacao_origem' => null, // Múltiplas origens
                'id_solicitacao_destino' => $novaSolicitacao->id_solicitacoes_compras,
                'tipo_movimentacao' => 'unificacao_itens',
                'usuario' => Auth::user()->name,
                'observacao' => $request->observacao ?? 'Unificação de itens de múltiplas solicitações',
                'data_movimentacao' => now(),
            ]);

            // Criar os itens consolidados na nova cotação
            foreach ($itensConsolidados as $itemConsolidado) {
                ItemSolicitacaoCompra::create([
                    'id_solicitacoes_compras' => $novaSolicitacao->id_solicitacoes_compras,
                    'codigo_item' => $itemConsolidado['codigo_item'],
                    'descricao_item' => $itemConsolidado['descricao_item'],
                    'quantidade' => $itemConsolidado['quantidade_total'],
                    'unidade' => $itemConsolidado['unidade'],
                    'observacao' => $itemConsolidado['observacao'],
                    'usuario_inclusao' => Auth::user()->name,
                    'data_inclusao' => now(),
                ]);
            }

            // Registrar movimentação detalhada de cada item
            foreach ($itens as $item) {
                MovimentacaoSolicitacaoItens::create([
                    'id_movimentacao_solicitacao' => $movimentacao->id,
                    'id_item_origem' => $item->id_itens_solicitacoes,
                    'id_solicitacao_origem' => $item->id_solicitacao_compra,
                    'codigo_item_origem' => $item->codigo_item,
                    'descricao_item_origem' => $item->descricao_item,
                    'quantidade_origem' => $item->quantidade,
                    'unidade_origem' => $item->unidade,
                    'id_solicitacao_destino' => $novaSolicitacao->id_solicitacoes_compras,
                    'operacao' => 'copia_para_unificacao',
                    'observacao' => 'Item copiado para nova cotação unificada',
                ]);
            }

            DB::commit();

            Log::info('Unificação de itens concluída com sucesso', [
                'nova_cotacao_id' => $novaSolicitacao->id_solicitacoes_compras,
                'itens_unificados' => count($request->itens_ids),
                'itens_consolidados' => count($itensConsolidados),
                'movimentacao_id' => $movimentacao->id
            ]);

            return redirect()->route('admin.compras.cotacoes.show', $novaSolicitacao->id_solicitacoes_compras)
                ->with('success', "Unificação de itens realizada com sucesso! Nova cotação #{$novaSolicitacao->id_solicitacoes_compras} criada com " . count($itensConsolidados) . " itens.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao unificar itens: ' . $e->getMessage(), [
                'itens_ids' => $request->itens_ids,
                'user' => Auth::user()->name ?? 'N/A'
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao unificar itens: ' . $e->getMessage());
        }
    }
}

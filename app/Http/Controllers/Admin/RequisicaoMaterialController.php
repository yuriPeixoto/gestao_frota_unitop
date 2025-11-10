<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RelacaoSolicitacaoPeca;
use App\Models\Departamento;
use App\Models\Filial;
use App\Models\Fornecedor;
use App\Models\RequisicaoMateriaisItens;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Produto;
use App\Models\ProdutosPorFilial;
use App\Models\ProdutosSolicitacoes;
use App\Models\RequisicaoMateriais;
use App\Models\UnidadeProduto;
use App\Models\TransferenciaEstoque;
use App\Models\TransferenciaEstoqueAux;
use App\Models\TransferenciaEstoqueItens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\IntegracaoWhatssappCarvalimaService;

use function PHPUnit\Framework\isString;

class RequisicaoMaterialController extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = RelacaoSolicitacaoPeca::query()
            ->where(function ($q) {
                $q->where('situacao', 'APROVADO')
                    ->orWhereNull('situacao');
            })
            ->with(['departamentoPecas', 'usuario']);

        $this->applyFilters($query, $request);

        $SolicitacaoPecas = $query->latest('id_solicitacao_pecas')
            ->paginate(10);

        $dados = $this->getDados();

        return view(
            'admin.requisicaoMaterial.index',
            compact(
                'SolicitacaoPecas',
                'dados'
            )
        );
    }

    public function create()
    {
        $forms = $this->getDados();
        $fornecedor = $this->FornecedorFrequente();
        $placa = $this->placaFrequente();

        return view('admin.requisicaoMaterial.create', compact('forms', 'fornecedor', 'placa'));
    }

    public function store(Request $request)
    {
        $tabelaReqMats = json_decode($request->tabelaReqMats, true);
        $requisicao_pneu = $request->requisicao_pneu;
        $terceiro = $request->is_terceiro;
        $cargos_aprovadores = array(1, 2, 3);
        $user = Auth::user()->id;
        $cargo = User::where('id', $user)->value('id_departamento');
        $datainicial = now();

        if (empty($tabelaReqMats)) {
            return redirect()->back()->with('error', 'Adicione ao menos um produto na solicitação.');
        }

        if (!empty($user)) {
            if (empty($cargo)) {
                return redirect()->back()
                    ->with('error', "Atenção: Para completar a solicitação é necessário que o usuário esteja vinculado ao departamento.")
                    ->withInput();
            }
        }

        // if (isset($cargo)) {
        //     if (in_array($cargo, $cargos_aprovadores)) {
        //         $situacao        = 'APROVADO';
        //         $aprovadogestor  = true;
        //         $dataaprovacao   = $datainicial;
        //     } else {
        //         $situacao       = 'AGUARDANDO APROVAÇÃO';
        //         $aprovadogestor = false;
        //         $dataaprovacao  = null;
        //     }
        // } else {
        //     return redirect()->back()->with('error', "Atenção: Para completar a solicitação é necessário que o usuário esteja vinculado ao cargo.");
        // }

        // if ($requisicao_pneu == 1 && $terceiro == 1) {
        //     $situacao       = 'AGUARDANDO APROVAÇÃO';
        //     $aprovadogestor = false;
        //     $dataaprovacao  = null;
        // }

        try {
            db::beginTransaction();

            $object = new RelacaoSolicitacaoPeca();

            $object->data_inclusao                = $datainicial;
            $object->situacao                     = null;
            $object->id_departamento              = $cargo;
            $object->id_filial                    = GetterFilial();
            $object->id_veiculo                   = $request->id_veiculo;
            $object->transferencia_entre_filiais  = $request->transferencia_entre_filiais ?? null;
            $object->id_user_aprovador            = $user;
            $object->data_aprovacao               = null;
            $object->observacao                   = $request->observacao;
            $object->justificativa_de_finalizacao = $request->justificativa_de_finalizacao;
            $object->id_terceiro                  = $request->id_terceiro ?? null;
            $object->aprovacao_gestor             = null;
            $object->is_cancelado                 = $request->is_cancelado ?? false;
            $object->requisicao_ti                = $request->requisicao_ti ?? false;
            $object->requisicao_pneu              = $request->requisicao_pneu ?? false;

            // Processar anexo da requisição
            if ($request->hasFile('anexo_requisicao')) {
                $anexoPath = $request->file('anexo_requisicao')->store('requisicoes/anexos', 'public');
                $object->anexo_imagem = $anexoPath;
            }

            $object->save();

            db::commit();

            $idSolicitacoes = $object->id_solicitacao_pecas;

            $this->processProdutosRequisicao($tabelaReqMats, $idSolicitacoes, $request);

            $query =
                "WITH valor AS
                (
                SELECT
                    SUM(COALESCE(cts.valor_medio, 1)) AS valor,
                    1 AS id
                FROM relacaosolicitacoespecas sc
                INNER JOIN produtossolicitacoes ct ON ct.id_relacao_solicitacoes = sc.id_solicitacao_pecas
                INNER JOIN produtos_por_filial cts ON cts.id_produto_unitop = ct.id_protudos AND sc.id_filial = cts.id_filial
                WHERE sc.id_solicitacao_pecas = $idSolicitacoes
                )
                SELECT DISTINCT
                    ap.id_usuario,
                    va.name,
                    ap.valor_aprovacao,
                    (REPLACE((REPLACE((REPLACE((REPLACE(ap.telefone,' ','')),'-','')),'(','')),')','')) AS telefone
                FROM aprovadorespedidos ap
                INNER JOIN v_usuarios va ON va.id = ap.id_usuario
                WHERE (ap.tipo_requisicao_materiais IS TRUE OR ap.tipo_gerencial IS TRUE)
                AND
                (
                SELECT
                    COALESCE(v.valor, 1)
                FROM valor v
                ) BETWEEN ap.valor_aprovacao AND ap.valor_aprovacao_final
                ORDER BY
                    ap.valor_aprovacao
                ASC";

            $result = db::select($query);

            if ($result) {
                foreach ($result as $item) {
                    // $idUser     = $item->id_usuario;
                    $nome       = $item->name;
                    $telefone   = $item->telefone;

                    if (!empty($telefone) && !empty($nome)) {
                        $texto = "*Atenção:* A solicitação de compras n° $idSolicitacoes está esperando sua aprovação.\n"
                            . "[Abrir listagem de pedidos]\n https://carvalima.unitopconsultoria.com.br/index.php?class=RelacaosolicitacoespecasAprovarForm&method=onEdit&key=$idSolicitacoes&id_solicitacao_pecas=$idSolicitacoes" . "\n";
                        IntegracaoWhatssappCarvalimaService::enviarMensagem($texto, "$nome", "$telefone");
                    }
                }
            }

            return redirect()->route('admin.requisicaoMaterial.show', $idSolicitacoes)
                ->with('success', 'Solicitação realizada com sucesso.');
        } catch (\Exception $e) {
            db::rollBack();
            Log::error('Erro ao criar solicitação de peças: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao criar solicitação: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Erro ao criar solicitação: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $forms = $this->getDados();
        $fornecedor = $this->FornecedorFrequente();
        $placa = $this->placaFrequente();

        $requisicaoMaterial = RelacaoSolicitacaoPeca::findOrFail($id);
        $produtosSolicitados = ProdutosSolicitacoes::with(['produto'])->where('id_relacao_solicitacoes', $id)->get();

        return view('admin.requisicaoMaterial.edit', compact('forms', 'fornecedor', 'placa', 'requisicaoMaterial', 'produtosSolicitados'));
    }

    public function update(Request $request, $id)
    {
        $object = RelacaoSolicitacaoPeca::findOrFail($id);
        $tabelaReqMats = json_decode($request->tabelaReqMats, true);
        $requisicao_pneu = $request->requisicao_pneu;
        $terceiro = $request->is_terceiro;
        $cargos_aprovadores = array(1, 2, 3);
        $user = Auth::user()->id;

        // $cargo = User::where('id', $user)->value('id_departamento');
        $cargo = 1; // Temporário até arrumar o campo cargo na tabela users
        $datainicial = now();
        $id_veiculo = null;
        $id_terceiro = null;

        if (isset($request->id_veiculo) && isString($request->id_veiculo)) {
            $id_veiculo = Veiculo::where('placa', $request->id_veiculo)->value('id_veiculo');
        }

        if (isset($request->id_terceiro) && isString($request->id_terceiro)) {
            $id_terceiro = Fornecedor::where('nome_fornecedor', $request->id_terceiro)->value('id_fornecedor');
        }

        if (empty($tabelaReqMats)) {
            return redirect()->back()->with('error', 'Adicione ao menos um produto na solicitação.');
        }

        if (!empty($user)) {
            if (empty($cargo)) {
                return redirect()->back()
                    ->with('error', "Atenção: Para completar a solicitação é necessário que o usuário esteja vinculado ao departamento.")
                    ->withInput();
            }
        }

        // if (isset($cargo)) {
        //     if (in_array($cargo, $cargos_aprovadores)) {
        //         $situacao        = 'APROVADO';
        //         $aprovadogestor  = true;
        //         $dataaprovacao   = $datainicial;
        //     } else {
        //         $situacao       = 'AGUARDANDO APROVAÇÃO';
        //         $aprovadogestor = false;
        //         $dataaprovacao  = null;
        //     }
        // } else {
        //     return redirect()->back()->with('error', "Atenção: Para completar a solicitação é necessário que o usuário esteja vinculado ao cargo.");
        // }

        // if ($requisicao_pneu == 1 && $terceiro == 1) {
        //     $situacao       = 'AGUARDANDO APROVAÇÃO';
        //     $aprovadogestor = false;
        //     $dataaprovacao  = null;
        // }

        try {
            db::beginTransaction();

            $object->data_inclusao                = $datainicial;
            $object->id_departamento              = $cargo;
            $object->id_filial                    = GetterFilial();
            $object->id_veiculo                   = $id_veiculo ?? null;
            $object->transferencia_entre_filiais  = $request->transferencia_entre_filiais ?? null;
            $object->id_user_aprovador            = $user;
            $object->id_usuario_abertura          = $user;
            $object->observacao                   = $request->observacao;
            $object->justificativa_de_finalizacao = $request->justificativa_de_finalizacao;
            $object->id_terceiro                  = $id_terceiro ?? null;
            $object->is_cancelado                 = $request->is_cancelado ?? false;
            $object->requisicao_ti                = $request->requisicao_ti ?? false;
            $object->requisicao_pneu              = $request->requisicao_pneu ?? false;

            // Processar anexo da requisição
            if ($request->hasFile('anexo_requisicao')) {
                $anexoPath = $request->file('anexo_requisicao')->store('requisicoes/anexos', 'public');
                $object->anexo_imagem = $anexoPath;
            }

            $object->save();

            db::commit();

            $idSolicitacoes = $object->id_solicitacao_pecas;

            $this->processProdutosRequisicao($tabelaReqMats, $idSolicitacoes, $request);

            $query =
                "WITH valor AS
                (
                SELECT
                    SUM(COALESCE(cts.valor_medio, 1)) AS valor,
                    1 AS id
                FROM relacaosolicitacoespecas sc
                INNER JOIN produtossolicitacoes ct ON ct.id_relacao_solicitacoes = sc.id_solicitacao_pecas
                INNER JOIN produtos_por_filial cts ON cts.id_produto_unitop = ct.id_protudos AND sc.id_filial = cts.id_filial
                WHERE sc.id_solicitacao_pecas = $idSolicitacoes
                )
                SELECT DISTINCT
                    ap.id_usuario,
                    va.name,
                    ap.valor_aprovacao,
                    (REPLACE((REPLACE((REPLACE((REPLACE(ap.telefone,' ','')),'-','')),'(','')),')','')) AS telefone
                FROM aprovadorespedidos ap
                INNER JOIN v_usuarios va ON va.id = ap.id_usuario
                WHERE (ap.tipo_requisicao_materiais IS TRUE OR ap.tipo_gerencial IS TRUE)
                AND
                (
                SELECT
                    COALESCE(v.valor, 1)
                FROM valor v
                ) BETWEEN ap.valor_aprovacao AND ap.valor_aprovacao_final
                ORDER BY
                    ap.valor_aprovacao
                ASC";

            $result = db::select($query);

            if ($result) {
                foreach ($result as $item) {
                    // $idUser     = $item->id_usuario;
                    $nome       = $item->name;
                    $telefone   = $item->telefone;

                    if (!empty($telefone) && !empty($nome)) {
                        $texto = "*Atenção:* A solicitação de compras n° $idSolicitacoes está esperando sua aprovação.\n"
                            . "[Abrir listagem de pedidos]\n https://carvalima.unitopconsultoria.com.br/index.php?class=RelacaosolicitacoespecasAprovarForm&method=onEdit&key=$idSolicitacoes&id_solicitacao_pecas=$idSolicitacoes" . "\n";
                        IntegracaoWhatssappCarvalimaService::enviarMensagem($texto, "$nome", "$telefone");
                    }
                }
            }

            return redirect()->route('admin.requisicaoMaterial.show', $idSolicitacoes)
                ->with('success', 'Solicitação atualizada com sucesso.');
        } catch (\Exception $e) {
            db::rollBack();
            Log::error('Erro ao criar solicitação de peças: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar solicitação: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Erro ao criar solicitação: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $relacaoSolicitacaoPecas = RelacaoSolicitacaoPeca::with([
            'veiculo',
            'departamentoPecas',
            'filial',
            'filialManutencao',
            'pessoalEstoque',
            'usuario',
            'transferenciaEstoqueAux',
            'devolucoes',
            'fornecedor',
        ])->findOrFail($id);

        return view('admin.requisicaoMaterial.show', compact('relacaoSolicitacaoPecas'));
    }

    public function getDados()
    {
        return [
            'usuarios' => User::select('id as value', 'name as label')->orderBy('name')->get()->toArray(),
            'filial' => Filial::select('id as value', 'name as label')->orderBy('name')->get()->toArray(),
            'departamento' => Departamento::select('id_departamento as value', 'descricao_departamento as label')->orderBy('label')->get()->toArray(),
            'situacao' => [['value' => 'INICIADA', 'label' => 'INICIADA'], ['value' => 'AGUARDANDO APROVAÇÃO', 'label' => 'AGUARDANDO APROVAÇÃO'], ['value' => 'FINALIZADA', 'label' => 'FINALIZADA']],
        ];
    }

    public function FornecedorFrequente()
    {
        return Cache::remember('fornecedores_frequentes', now()->addMinutes(30), function () {
            return Fornecedor::select(
                'id_fornecedor as value',
                DB::raw("CONCAT('Cód: ', id_fornecedor, ' - CNPJ: ', cnpj_fornecedor, ' - ', nome_fornecedor) as label")
            )
                ->orderBy('id_fornecedor')
                ->limit(30)
                ->get()
                ->toArray();
        });
    }

    public function placaFrequente()
    {
        return Cache::remember('placas_frequentes', now()->addMinutes(30), function () {
            return Veiculo::select('id_veiculo as value', 'placa as label')
                ->where('is_terceiro', true)
                ->where('situacao_veiculo', true)
                ->limit(30)
                ->orderBy('placa')
                ->get()
                ->toArray();
        });
    }

    public function applyFilters($query, Request $request)
    {
        if ($request->filled('id_solicitacao_pecas')) {
            $query->where('id_solicitacao_pecas', $request->input('id_solicitacao_pecas'));
        }

        if ($request->filled('id_departamento')) {
            $query->where('id_departamento', $request->input('id_departamento'));
        }

        if ($request->filled('id_usuario')) {
            $query->where('id_usuario_abertura', $request->input('id_usuario'));
        }

        if ($request->filled('id_situacao')) {
            $query->where('situacao', 'ilike', '%' . $request->input('id_situacao') . '%');
        }

        if ($request->filled('data_inicial')) {
            $query->whereDate('data_inclusao', '>=', $request->input('data_inicial'));
        }

        if ($request->filled('data_final')) {
            $query->whereDate('data_inclusao', '<=', $request->input('data_final'));
        }
    }

    public function processProdutosRequisicao($produtos, $idRelacaoSolicitacoes, Request $request)
    {
        if (is_array($produtos) && count($produtos) > 0) {
            try {
                DB::beginTransaction();

                // Primeiro, remover produtos existentes para esta requisição
                ProdutosSolicitacoes::where('id_relacao_solicitacoes', $idRelacaoSolicitacoes)->delete();

                foreach ($produtos as $index => $item) {

                    if (!is_array($item)) {
                        continue;
                    }

                    $dadosServico = [
                        'id_relacao_solicitacoes' => $idRelacaoSolicitacoes,
                        'id_protudos' => $item['idProduto'] ?? $item['id_protudos'],
                        'quantidade' => $item['quantidade'],
                        'observacao' => $item['observacao'] ?? null,
                        'id_user' => $item['id_user'] ?? Auth::user()->id,
                        'situacao_pecas' => $item['situacao_pecas'] ?? null,
                        'quantidade_transferencia' => $item['quantidade_transferencia'] ?? null,
                        'filial_transferencia' => $item['filial_transferencia'] ?? null,
                    ];

                    // Processar anexo do produto se existir
                    $anexoFieldName = "anexo_produto_$index";
                    if ($request && $request->hasFile($anexoFieldName)) {

                        $anexoPath = $request->file($anexoFieldName)->store('produtos/anexos', 'public');
                        $dadosServico['anexo_imagem'] = $anexoPath;
                    }

                    ProdutosSolicitacoes::create($dadosServico);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao processar produtos da solicitação: ' . $e->getMessage());
                throw $e;
            }
        }
    }

    public static function onProdutos($idProduto)
    {
        try {
            if (!empty($idProduto)) {
                $produto = Produto::select([
                    'id_unidade_produto',
                    'id_produto',
                    'imagem_produto',
                    'nome_imagem',
                    'codigo_produto',
                    'descricao_produto'
                ])
                    ->with(['ProdutoPorFilial' => function ($query) {
                        $query->select('id_produto_unitop', 'quantidade_produto')
                            ->where('id_filial', GetterFilial());
                    }])
                    ->where('id_produto', $idProduto)
                    ->where('is_ativo', '!=', false)
                    ->first();

                // Adaptar para o formato esperado pelo front-end
                if ($produto && $produto->ProdutoPorFilial->isNotEmpty()) {
                    $produtoFormatado = (object) [
                        'id_unidade_produto' => $produto->id_unidade_produto,
                        'quantidade_produto' => $produto->ProdutoPorFilial->first()->quantidade_produto,
                        'id_produto' => $produto->id_produto,
                        'imagem_produto' => $produto->imagem_produto,
                        'nome_imagem' => $produto->nome_imagem,
                        'codigo_produto' => $produto->codigo_produto,
                        'descricao_produto' => $produto->descricao_produto,
                    ];
                } else {
                    $produtoFormatado = null;
                }

                return response()->json(['success' => true, 'produto' => $produtoFormatado]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao buscar estoque produto: ' . $e->getMessage());
        }
    }

    public function getProdutosPorTipo(Request $request)
    {
        try {
            $idOperacao = $request->operacao;
            $produtos = [];

            // Base query builder para todos os tipos
            $query = Produto::select('id_produto as value', 'descricao_produto as label')
                ->whereHas('ProdutoPorFilial')
                ->where('is_imobilizado', '!=', true)
                ->where('is_ativo', '!=', false);

            if ($idOperacao == 1) {
                // Pneus - produtos com modelo de pneu, exceto estoque T.I.
                $produtos = $query->clone()
                    ->whereNotNull('id_modelo_pneu')
                    ->whereHas('ProdutoPorFilial', function ($q) {
                        $q->where('id_estoque', '!=', 22);
                    })
                    ->orderBy('descricao_produto')
                    ->distinct()
                    ->get()
                    ->toArray();
            } elseif ($idOperacao == 2) {
                // Materiais - produtos sem modelo de pneu, exceto estoque T.I.
                $produtos = $query->clone()
                    ->whereNull('id_modelo_pneu')
                    ->whereHas('ProdutoPorFilial', function ($q) {
                        $q->where('id_estoque', '!=', 22);
                    })
                    ->orderBy('descricao_produto')
                    ->distinct()
                    ->limit(20)
                    ->get()
                    ->toArray();
            } elseif ($idOperacao == 3) {
                // T.I. - produtos do estoque T.I. (id_estoque = 22)
                $produtos = $query->clone()
                    ->whereNull('id_modelo_pneu')
                    ->whereHas('ProdutoPorFilial', function ($q) {
                        $q->where('id_estoque', 22);
                    })
                    ->orderBy('descricao_produto')
                    ->distinct()
                    ->get()
                    ->toArray();
            }

            return response()->json([
                'success' => true,
                'produtos' => $produtos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar produtos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchProdutos(Request $request)
    {
        $term = strtolower($request->get('term'));

        // $produtos = Cache::remember('produtos_search_' . $term, now()->addMinutes(30), function () use ($term) {
        return Produto::where('descricao_produto', 'ILIKE', "%{$term}%")
            ->whereHas('produtoPorFilial', function ($query) {
                $query->where('id_estoque', '!=', 22);
            })
            ->where('is_imobilizado', false)
            ->where('is_ativo', true)
            ->orderBy('descricao_produto')
            ->get(['id_produto as value', 'descricao_produto as label'])
            ->toArray();
        // });

        // return response()->json($produtos);
    }

    public function getProdutosPorRequisicao($id)
    {
        try {
            $itens = ProdutosSolicitacoes::with(['produto'])
                ->where('id_relacao_solicitacoes', $id)
                ->get()
                ->map(function ($item) {
                    return [
                        'id_produtos_solicitacoes' => $item->id_produtos_solicitacoes,
                        'descricao_produto' => $item->produto->descricao_produto,
                        'quantidade' => $item->quantidade,
                        'descricao_unidade' => UnidadeProduto::where('id_unidade_produto', $item->produto->id_unidade_produto)->value('descricao_unidade'),
                    ];
                });

            return response()->json([
                'success' => true,
                'nfItens' => $itens
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dados: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDisponibilidadeProduto(Request $request)
    {
        try {
            $produtoId = $request->get('produto_id');

            if (!$produtoId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID do produto é obrigatório'
                ], 400);
            }

            // Buscar o produto
            $produto = Produto::find($produtoId);
            if (!$produto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não encontrado'
                ], 404);
            }

            // Buscar disponibilidade em todas as filiais - query mais simples
            $sql = "
                SELECT
                    COALESCE(f.id, ppf.id_filial) as id_filial,
                    COALESCE(f.name, 'Filial ID: ' || ppf.id_filial) as nome_filial,
                    COALESCE(e.descricao_estoque, 'N/A') as estoque,
                    ppf.quantidade_produto,
                    COALESCE(ppf.valor_medio, 0) as valor_medio,
                    COALESCE(ppf.localizacao, '') as localizacao,
                    ppf.data_inclusao,
                    ppf.data_alteracao
                FROM produtos_por_filial ppf
                LEFT JOIN filiais f ON ppf.id_filial = f.id
                LEFT JOIN estoque e ON ppf.id_estoque = e.id_estoque
                WHERE ppf.id_produto_unitop = ?
                  AND ppf.quantidade_produto > 0
                ORDER BY COALESCE(f.name, 'ZZZ_' || ppf.id_filial), COALESCE(e.descricao_estoque, 'N/A')
            ";

            $disponibilidade = DB::connection('pgsql')->select($sql, [$produtoId]);

            // Convert stdClass objects to array for easier processing
            $disponibilidade = collect($disponibilidade)->map(function ($item) {
                return (array) $item;
            });

            // Calcular totais
            $quantidadeTotal = $disponibilidade->sum('quantidade_produto');
            $valorMedioGeral = $disponibilidade->where('valor_medio', '>', 0)->avg('valor_medio');

            return response()->json([
                'success' => true,
                'produto' => [
                    'id' => $produto->id_produto,
                    'codigo' => $produto->codigo_produto,
                    'descricao' => $produto->descricao_produto
                ],
                'disponibilidade' => $disponibilidade->toArray(),
                'resumo' => [
                    'quantidade_total' => (int) $quantidadeTotal,
                    'valor_medio_geral' => (float) $valorMedioGeral,
                    'filiais_com_estoque' => $disponibilidade->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar disponibilidade do produto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar disponibilidade: ' . $e->getMessage(),
                'debug' => [
                    'produto_id' => $produtoId,
                    'error_details' => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function debugDisponibilidade(Request $request)
    {
        $produtoId = $request->get('produto_id', 1432);

        // Consulta 1: Todos os registros
        $todos = DB::connection('pgsql')->select("
            SELECT ppf.*, f.name as nome_filial, e.descricao_estoque
            FROM produtos_por_filial ppf
            LEFT JOIN filiais f ON ppf.id_filial = f.id
            LEFT JOIN estoque e ON ppf.id_estoque = e.id_estoque
            WHERE ppf.id_produto_unitop = ?
            ORDER BY ppf.quantidade_produto DESC
        ", [$produtoId]);

        // Consulta 2: Apenas com quantidade > 0
        $comEstoque = DB::connection('pgsql')->select("
            SELECT ppf.*, f.name as nome_filial, e.descricao_estoque
            FROM produtos_por_filial ppf
            LEFT JOIN filiais f ON ppf.id_filial = f.id
            LEFT JOIN estoque e ON ppf.id_estoque = e.id_estoque
            WHERE ppf.id_produto_unitop = ? AND ppf.quantidade_produto > 0
            ORDER BY ppf.quantidade_produto DESC
        ", [$produtoId]);

        return response()->json([
            'produto_id' => $produtoId,
            'total_registros' => count($todos),
            'registros_com_estoque' => count($comEstoque),
            'todos_registros' => $todos,
            'registros_com_estoque_detalhado' => $comEstoque
        ]);
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Remover registros do banco
            ProdutosSolicitacoes::where('id_relacao_solicitacoes', $id)->delete();
            RelacaoSolicitacaoPeca::where('id_solicitacao_pecas', $id)->delete();

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir sinistro: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function enviarAprovacao(Request $request)
    {
        $id = $request->input('id');

        // Validar se o ID foi fornecido e é válido
        if (empty($id) || !is_numeric($id)) {
            return response()->json([
                'success' => false,
                'message' => 'ID da requisição é obrigatório e deve ser um número válido.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $situacao = 'AGUARDANDO APROVAÇÃO';
            $aprovadogestor = false;
            $dataaprovacao = null;

            $requisicao = RelacaoSolicitacaoPeca::find($id);

            if (!$requisicao) {
                return response()->json(['success' => false, 'message' => 'Requisição não encontrada.'], 404);
            }

            $idSolicitacoes = $requisicao->id_solicitacao_pecas;
            $requisicao->update([
                'situacao' => $situacao,
                'aprovacao_gestor' => $aprovadogestor,
                'data_aprovacao' => $dataaprovacao,
            ]);

            DB::commit();

            // Verificar se é uma requisição AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitação enviada para aprovação com sucesso.',
                    'redirect_url' => route('admin.requisicaoMaterial.show', $idSolicitacoes)
                ]);
            }

            return redirect()->route('admin.requisicaoMaterial.show', $idSolicitacoes)
                ->with('success', 'Solicitação enviada para aprovação com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao enviar solicitação para aprovação: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function aprovar(Request $request)
    {

        $id = $request->input('id');

        // Validar se o ID foi fornecido e é válido
        if (empty($id) || !is_numeric($id)) {
            Log::error('ID inválido:', ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'ID da requisição é obrigatório e deve ser um número válido.'
            ], 400);
        }

        try {
            $requisicao = RelacaoSolicitacaoPeca::find($id);
            if (!$requisicao) {
                Log::error('Requisição não encontrada:', ['id' => $id]);
                return response()->json(['success' => false, 'message' => 'Requisição não encontrada.'], 404);
            }

            // Verificar se há itens com situacao_pecas "TRANSFERENCIA"
            $itensTransferencia = ProdutosSolicitacoes::with(['produto'])
                ->where('id_relacao_solicitacoes', $id)
                ->where('situacao_pecas', 'TRANSFERENCIA')
                ->get();

            // Se há itens de transferência, retornar dados para o modal
            if ($itensTransferencia->count() > 0) {
                return response()->json([
                    'success' => true,
                    'show_transfer_modal' => true,
                    'transfer_items' => $itensTransferencia->map(function ($item) {
                        return [
                            'id' => $item->id_produtos_solicitacoes,
                            'produto_codigo' => $item->produto->id_produto ?? '',
                            'produto_nome' => $item->produto->descricao_produto ?? '',
                            'quantidade' => $item->quantidade_transferencia ?? $item->quantidade,
                            'filial_origem' => $item->filial_transferencia,
                            'observacao' => "Transferência de " . ($item->observacao ?? '')
                        ];
                    })
                ]);
            }

            return $this->processarAprovacao($requisicao, $request);
        } catch (\Exception $e) {
            Log::error('Erro ao aprovar solicitação: ' . $e->getMessage());
            Log::error('Stack trace:', ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function aprovarSemTransferencia(Request $request)
    {
        $id = $request->input('id');

        // Validar se o ID foi fornecido e é válido
        if (empty($id) || !is_numeric($id)) {
            Log::error('ID inválido:', ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'ID da requisição é obrigatório e deve ser um número válido.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $requisicao = RelacaoSolicitacaoPeca::find($id);
            if (!$requisicao) {
                Log::error('Requisição não encontrada:', ['id' => $id]);
                return response()->json(['success' => false, 'message' => 'Requisição não encontrada.'], 404);
            }

            // Inativar itens com situacao_pecas "TRANSFERENCIA"
            ProdutosSolicitacoes::where('id_relacao_solicitacoes', $id)
                ->where('situacao_pecas', 'TRANSFERENCIA')
                ->update(['situacao_pecas' => 'INATIVO']);

            // Processar aprovação diretamente
            $result = $this->processarAprovacao($requisicao, $request, false);

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao aprovar solicitação sem transferência: ' . $e->getMessage());
            Log::error('Stack trace:', ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function processarAprovacaoComTransferencia(Request $request)
    {
        $id = $request->input('id');
        $itensTransferencia = $request->input('itens_transferencia', []);

        // Validar se o ID foi fornecido e é válido
        if (empty($id) || !is_numeric($id)) {
            Log::error('ID inválido:', ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'ID da requisição é obrigatório e deve ser um número válido.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $requisicao = RelacaoSolicitacaoPeca::find($id);
            if (!$requisicao) {
                Log::error('Requisição não encontrada:', ['id' => $id]);
                return response()->json(['success' => false, 'message' => 'Requisição não encontrada.'], 404);
            }

            // Verificar se há itens de transferência para processar
            $itensComTransferencia = ProdutosSolicitacoes::where('id_relacao_solicitacoes', $id)
                ->where('situacao_pecas', 'TRANSFERENCIA')
                ->get();

            if ($itensComTransferencia->isNotEmpty()) {
                // Criar transferência se houver itens selecionados
                if (!empty($itensTransferencia)) {
                    $transferencia = $this->criarTransferencias($itensTransferencia, $requisicao);

                    // Criar requisição clone para itens de transferência
                    $resultadoClones = $this->criarRequisicaoClone($requisicao, $itensComTransferencia, $transferencia ? $transferencia->id_tranferencia : null);

                    // Log das requisições criadas
                    if (is_array($resultadoClones) && isset($resultadoClones['requisicoes_criadas']) && count($resultadoClones['requisicoes_criadas']) > 0) {
                        Log::info("Requisições clones criadas com sucesso", [
                            'total_requisicoes' => $resultadoClones['total_requisicoes'],
                            'filiais_processadas' => $resultadoClones['filiais_processadas'],
                            'ids_requisicoes' => collect($resultadoClones['requisicoes_criadas'])->pluck('id_solicitacao_pecas')->toArray()
                        ]);
                    }
                } else {
                    Log::warning('Nenhum item de transferência foi enviado para criação');
                }
            }

            // Processar aprovação normal da requisição original
            $result = $this->processarAprovacao($requisicao, $request, false);

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar aprovação com transferência: ' . $e->getMessage());
            Log::error('Stack trace:', ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    private function processarAprovacao($requisicao, $request, $useTransaction = true)
    {
        try {
            if ($useTransaction) {
                DB::beginTransaction();
            }

            $situacao        = 'APROVADO';
            $aprovadogestor  = true;
            $dataaprovacao   = now();
            $requisicao_pneu = $requisicao->requisicao_pneu;
            $idSolicitacoes = $requisicao->id_solicitacao_pecas;

            $requisicao->update([
                'situacao' => $situacao,
                'aprovacao_gestor' => $aprovadogestor,
                'data_aprovacao' => $dataaprovacao,
            ]);

            if ($useTransaction) {
                DB::commit();
            }

            if (!$requisicao_pneu) {
                $this->inserirRequisicaoMateriais($idSolicitacoes);
            }

            // Verificar se é uma requisição AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitação aprovada com sucesso.',
                    'redirect_url' => route('admin.requisicaoMaterial.show', $idSolicitacoes)
                ]);
            }

            return redirect()->route('admin.requisicaoMaterial.show', $idSolicitacoes)
                ->with('success', 'Solicitação aprovada com sucesso.');
        } catch (\Exception $e) {
            if ($useTransaction) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    private function criarTransferencias($itensTransferencia, $requisicao)
    {
        // Se os itens chegaram como string JSON, decodificar
        if (is_string($itensTransferencia)) {
            $itensTransferencia = json_decode($itensTransferencia, true);
        }

        // Verificar se os dados são válidos
        if (!is_array($itensTransferencia) || empty($itensTransferencia)) {
            Log::error('Dados de transferência inválidos:', ['tipo' => gettype($itensTransferencia), 'valor' => $itensTransferencia]);
            return null;
        }

        $user = Auth::user();
        if (!$user) {
            Log::error('Usuário não autenticado');
            return response()->json(['success' => false, 'message' => 'Usuário não autenticado'], 401);
        }

        $dataAtual = now();
        $ultimaTransferencia = null;

        // Agrupar itens por filial de origem
        $itensPorFilial = collect($itensTransferencia)->groupBy('filial_origem');

        foreach ($itensPorFilial as $filialOrigem => $itens) {
            // Validar filial origem
            if (empty($filialOrigem) || $filialOrigem === '' || $filialOrigem === null) {
                Log::error('Filial origem inválida, pulando criação da transferência', ['filial_origem' => $filialOrigem]);
                continue;
            }

            // Converter para inteiro se for string numérica
            $filialOrigem = is_numeric($filialOrigem) ? (int)$filialOrigem : $filialOrigem;

            // Verificar se GetterFilial() funciona
            $filialGetter = GetterFilial();

            // Criar transferência principal
            try {
                $transferencia = TransferenciaEstoque::create([
                    'data_inclusao' => $dataAtual,
                    'id_filial' => $user->id_filial ?? GetterFilial(),
                    'id_usuario' => $user->id,
                    'id_departamento' => $user->id_departamento,
                    'usuario_baixa' => $user->id,
                    'filial_baixa' => $filialOrigem,
                    'observacao_solicitacao' => 'Transferência gerada automaticamente pela aprovação da requisição #' . $requisicao->id_solicitacao_pecas,
                    'situacao' => 'AGUARDANDO',
                    'aprovado' => false,
                    'recebido' => false,
                ]);
            } catch (\Exception $createError) {
                Log::error('Erro ao criar transferência:', [
                    'error' => $createError->getMessage(),
                    'trace' => $createError->getTraceAsString()
                ]);
                throw $createError;
            }

            $ultimaTransferencia = $transferencia;

            // Criar itens da transferência
            foreach ($itens as $item) {
                $produtoSolicitacao = ProdutosSolicitacoes::find($item['id']);
                if ($produtoSolicitacao) {
                    TransferenciaEstoqueItens::create([
                        'data_inclusao' => $dataAtual,
                        'id_produto' => $produtoSolicitacao->id_protudos,
                        'quantidade' => $item['quantidade'],
                        'id_transferencia' => $transferencia->id_tranferencia,
                    ]);
                } else {
                    Log::error('Produto solicitação não encontrado:', ['id' => $item['id']]);
                }
            }
        }

        return $ultimaTransferencia;
    }

    private function criarRequisicaoClone($requisicaoOriginal, $itensTransferencia, $idTransferencia = null)
    {
        $dataAtual = now();
        $user = Auth::user();
        $idSolicitacaoParam = $requisicaoOriginal->id_solicitacao_pecas;
        $requisicoesClones = [];

        // Agrupar itens por filial_transferencia
        $itensPorFilial = collect($itensTransferencia)->groupBy('filial_transferencia');

        foreach ($itensPorFilial as $filialTransferencia => $itensFilial) {
            // Validar se a filial é válida
            if (empty($filialTransferencia) || $filialTransferencia === null) {
                Log::warning('Filial de transferência inválida, pulando criação da requisição', ['filial_transferencia' => $filialTransferencia]);
                continue;
            }

            // Buscar informações da filial de transferência
            $filialTransferenciaInfo = Filial::find($filialTransferencia);
            $nomeFilialTransferencia = $filialTransferenciaInfo ? $filialTransferenciaInfo->name : "Filial ID: $filialTransferencia";

            // Criar nova requisição clone para esta filial
            $requisicaoClone = RelacaoSolicitacaoPeca::create([
                'data_inclusao' => $dataAtual,
                'data_alteracao' => $dataAtual,
                'id_departamento' => $requisicaoOriginal->id_departamento,
                'id_usuario_abertura' => $requisicaoOriginal->id_usuario_abertura,
                'id_filial' => $requisicaoOriginal->id_filial,
                'id_veiculo' => $requisicaoOriginal->id_veiculo,
                'id_orderm_servico' => $requisicaoOriginal->id_orderm_servico,
                'situacao' => 'AGUARDANDO TRANSFERÊNCIA',
                'id_usuario_estoque' => null,
                'aprovacao_gestor' => null,
                'transferencia_entre_filiais' => $requisicaoOriginal->transferencia_entre_filiais,
                'observacao' => "Requisição criada automaticamente para itens de transferência da {$nomeFilialTransferencia} - Requisição origem #{$requisicaoOriginal->id_solicitacao_pecas}",
                'justificativa_de_finalizacao' => null,
                'id_terceiro' => $requisicaoOriginal->id_terceiro,
                'requisicao_pneu' => $requisicaoOriginal->requisicao_pneu,
                'id_filial_manutencao' => $requisicaoOriginal->id_filial_manutencao,
                'observacao_cancelamento' => null,
                'is_separado' => false,
                'id_user_aprovador' => $requisicaoOriginal->id_user_aprovador,
                'data_aprovacao' => null,
                'requisicao_ti' => $requisicaoOriginal->requisicao_ti,
                'is_cancelado' => false,
                'observacao_cancelado' => null,
                'is_requisicao_os_imobilizado' => $requisicaoOriginal->is_requisicao_os_imobilizado,
                'deleted_at' => null,
                'anexo_imagem' => null,
                'id_transferencia' => $idTransferencia,
                'is_transferencia' => true,
            ]);

            $requisicaoCloneId = $requisicaoClone->id_solicitacao_pecas;
            $requisicoesClones[] = $requisicaoClone;

            // Criar os registros auxiliares de transferência para esta requisição
            $this->criarTransferenciasAux($idSolicitacaoParam, $requisicaoCloneId);

            // Clonar os itens de transferência desta filial
            foreach ($itensFilial as $itemOriginal) {
                ProdutosSolicitacoes::create([
                    'data_inclusao' => $dataAtual,
                    'data_alteracao' => $dataAtual,
                    'id_relacao_solicitacoes' => $requisicaoCloneId,
                    'id_protudos' => $itemOriginal->id_protudos,
                    'quantidade' => $itemOriginal->quantidade,
                    'quantidade_baixa' => null,
                    'data_baixa' => null,
                    'id_filial' => $itemOriginal->id_filial,
                    'id_unidade_produto' => $itemOriginal->id_unidade_produto,
                    'id_user' => $user->id,
                    'data_baixa_sistema' => null,
                    'id_ordem_servico_peca' => $itemOriginal->id_ordem_servico_peca,
                    'situacao_pecas' => 'TRANSFERENCIA',
                    'observacao' => "Item clonado da requisição #{$requisicaoOriginal->id_solicitacao_pecas} - Filial: {$nomeFilialTransferencia}",
                    'anexo_imagem' => null,
                    'quantidade_transferencia' => $itemOriginal->quantidade_transferencia,
                    'filial_transferencia' => $itemOriginal->filial_transferencia,
                    'quantidade_compra' => $itemOriginal->quantidade_compra,
                ]);

                // Atualizar estoque da filial de transferência
                $transferencia = ProdutosPorFilial::where('id_produto_unitop', $itemOriginal->id_protudos)
                    ->where('id_filial', $itemOriginal->filial_transferencia)
                    ->first();

                if ($transferencia) {
                    $quantidadeTransferida = $transferencia->quantidade_produto - $itemOriginal->quantidade_transferencia;

                    $transferencia->update([
                        'quantidade_produto' => $quantidadeTransferida,
                        'quantidade_transferencia' => $itemOriginal->quantidade_transferencia
                    ]);
                } else {
                    Log::warning("Produto não encontrado no estoque da filial de transferência", [
                        'produto_id' => $itemOriginal->id_protudos,
                        'filial_transferencia' => $itemOriginal->filial_transferencia
                    ]);
                }
            }

            Log::info("Criada requisição clone para filial $filialTransferencia", [
                'requisicao_clone_id' => $requisicaoCloneId,
                'filial_transferencia' => $filialTransferencia,
                'quantidade_itens' => count($itensFilial)
            ]);
        }

        // Retornar todas as requisições criadas para cada filial
        return [
            'requisicoes_criadas' => $requisicoesClones,
            'total_requisicoes' => count($requisicoesClones),
            'filiais_processadas' => $itensPorFilial->keys()->toArray()
        ];
    }

    public function testarTransferencia(Request $request)
    {
        try {

            $user = Auth::user();

            $filialGetter = GetterFilial();

            $dataAtual = now();

            // Teste simples de criação de transferência
            $transferencia = TransferenciaEstoque::create([
                'data_inclusao' => $dataAtual,
                'id_filial' => $user->id_filial ?? 1,
                'id_usuario' => $user->id,
                'id_departamento' => $user->id_departamento ?? 1,
                'usuario_baixa' => $user->id,
                'filial_baixa' => 1,
                'observacao_solicitacao' => 'Teste de transferência',
                'situacao' => 'AGUARDANDO',
                'aprovado' => false,
                'recebido' => false,
            ]);

            return response()->json(['success' => true, 'message' => 'Teste concluído', 'transferencia_id' => $transferencia->id_tranferencia]);
        } catch (\Exception $e) {
            Log::error('Erro no teste:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function revisar(Request $request)
    {
        $id = $request->input('id');

        // Validar se o ID foi fornecido e é válido
        if (empty($id) || !is_numeric($id)) {
            return response()->json([
                'success' => false,
                'message' => 'ID da requisição é obrigatório e deve ser um número válido.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $situacao = 'REVISAR REQUISIÇÃO';
            $aprovadogestor = false;
            $dataaprovacao = null;

            $requisicao = RelacaoSolicitacaoPeca::find($id);

            if (!$requisicao) {
                return response()->json(['success' => false, 'message' => 'Requisição não encontrada.'], 404);
            }

            $idSolicitacoes = $requisicao->id_solicitacao_pecas;

            $requisicao->update([
                'situacao' => $situacao,
                'aprovacao_gestor' => $aprovadogestor,
                'data_aprovacao' => $dataaprovacao,
            ]);

            DB::commit();

            // Verificar se é uma requisição AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitação aprovada com sucesso.',
                    'redirect_url' => route('admin.requisicaoMaterial.show', $idSolicitacoes)
                ]);
            }

            return redirect()->route('admin.requisicaoMaterial.show', $idSolicitacoes)
                ->with('success', 'Solicitação enviada para aprovação com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao enviar solicitação para aprovação: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reprovar(Request $request)
    {
        $id = $request->input('id');

        // Validar se o ID foi fornecido e é válido
        if (empty($id) || !is_numeric($id)) {
            return response()->json([
                'success' => false,
                'message' => 'ID da requisição é obrigatório e deve ser um número válido.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $situacao = 'REPROVADO';

            $requisicao = RelacaoSolicitacaoPeca::find($id);

            if (!$requisicao) {
                return response()->json(['success' => false, 'message' => 'Requisição não encontrada.'], 404);
            }

            $idSolicitacoes = $requisicao->id_solicitacao_pecas;
            $requisicao->update([
                'situacao' => $situacao,
            ]);

            DB::commit();

            // Verificar se é uma requisição AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitação reprovada com sucesso.',
                    'redirect_url' => route('admin.requisicaoMaterial.show', $idSolicitacoes)
                ]);
            }

            return redirect()->route('admin.requisicaoMaterial.show', $idSolicitacoes)
                ->with('success', 'Solicitação reprovada com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao reprovar solicitação: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function inserirRequisicaoMateriais($id_relacao_solicitacoes,)
    {
        try {
            $relacaoSolicitacao = RelacaoSolicitacaoPeca::where('id_solicitacao_pecas', $id_relacao_solicitacoes)->first();
            $relacaoSolicitacaoItens = ProdutosSolicitacoes::where('id_relacao_solicitacoes', $id_relacao_solicitacoes)->get();

            if (!empty($relacaoSolicitacao->id_terceiro) && $relacaoSolicitacao->requisicao_pneu == false) {
                try {
                    DB::beginTransaction();
                    $requisicaoMateriais = RequisicaoMateriais::create([
                        'data_inclusao' => now(),
                        'id_filial' => $relacaoSolicitacao->id_filial,
                        'situacao' => 'APROVADO',
                        'id_terceiro' => $relacaoSolicitacao->id_terceiro,
                        'id_usuario_solicitante' => $relacaoSolicitacao->id_usuario_abertura,
                        'observacao_solicitante' => $relacaoSolicitacao->observacao,
                        'venda' => true,
                    ]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Erro ao inserir requisição de pneu: ' . $e->getMessage());
                }
            } else {
                try {
                    DB::beginTransaction();
                    $requisicaoMateriais = RequisicaoMateriais::create([
                        'data_inclusao' => now(),
                        'id_filial' => $relacaoSolicitacao->id_filial,
                        'situacao' => 'APROVADO',
                        'id_terceiro' => $relacaoSolicitacao->id_terceiro,
                        'id_usuario_solicitante' => $relacaoSolicitacao->id_usuario_abertura,
                        'observacao_solicitante' => $relacaoSolicitacao->observacao,
                    ]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Erro ao inserir requisição de pneu: ' . $e->getMessage());
                }
            }

            $id_requisicao_materiais = $requisicaoMateriais->id_requisicao_materiais;

            try {
                DB::beginTransaction();
                foreach ($relacaoSolicitacaoItens as $item) {
                    RequisicaoMateriaisItens::create([
                        'data_inclusao' => now(),
                        'id_requisicao_materiais' => $id_requisicao_materiais,
                        'id_produto' => $item->id_protudos,
                        'quantidade' => $item->quantidade
                    ]);
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao inserir modelos de pneu: ' . $e->getMessage());
            }

            return;
        } catch (\Exception $e) {
            Log::error('Erro ao inserir requisição de pneu: ' . $e->getMessage());
        }
    }

    private function criarTransferenciasAux($idSolicitacaoParam, $idSolicitacaoNovo)
    {
        // Buscar a filial de transferência específica da nova requisição
        $novaRequisicao = RelacaoSolicitacaoPeca::where('id_solicitacao_pecas', $idSolicitacaoNovo)->first();
        $produtosNovaRequisicao = ProdutosSolicitacoes::where('id_relacao_solicitacoes', $idSolicitacaoNovo)
            ->where('situacao_pecas', 'TRANSFERENCIA')
            ->get();

        // Obter as filiais de transferência únicas da nova requisição
        $filiaisTransferencia = $produtosNovaRequisicao->pluck('filial_transferencia')->unique()->filter();

        // Busca todos os produtos da solicitação antiga que correspondem às filiais da nova requisição
        $produtos = ProdutosSolicitacoes::where('id_relacao_solicitacoes', $idSolicitacaoParam)
            ->where('situacao_pecas', 'TRANSFERENCIA')
            ->when($filiaisTransferencia->isNotEmpty(), function ($query) use ($filiaisTransferencia) {
                return $query->whereIn('filial_transferencia', $filiaisTransferencia->toArray());
            })
            ->select(
                'id_produtos_solicitacoes',
                'id_protudos',
                'quantidade',
                'quantidade_baixa',
                'filial_transferencia'
            )
            ->distinct()
            ->get();

        Log::info("Criando transferências auxiliares", [
            'id_solicitacao_param' => $idSolicitacaoParam,
            'id_solicitacao_novo' => $idSolicitacaoNovo,
            'filiais_transferencia' => $filiaisTransferencia->toArray(),
            'produtos_encontrados' => $produtos->count()
        ]);

        foreach ($produtos as $p) {
            // Busca grupo do produto usando Eloquent
            $produto = Produto::where('id_produto', $p->id_protudos)->first();
            $grupoServico = $produto->id_grupo_servico ?? null;

            // Busca filial que solicita usando Eloquent
            $solicitacao = RelacaoSolicitacaoPeca::where('id_solicitacao_pecas', $idSolicitacaoParam)
                ->first();
            $idFilialSolicita = $solicitacao->id_filial ?? null;

            // A filial que recebe é a filial_transferencia do produto
            $idFilialRecebe = $p->filial_transferencia ?? 1;

            Log::info("Processando produto ID: {$p->id_protudos}, Filial Solicita: {$idFilialSolicita}, Filial Recebe: {$idFilialRecebe}");

            // Estoque da filial que solicita
            $estoqueSolicita = ProdutosPorFilial::where('id_produto_unitop', $p->id_protudos)
                ->where('id_filial', $idFilialSolicita)
                ->first();
            $quantidadeEstoque = $estoqueSolicita ? $estoqueSolicita->quantidade_produto : 0;

            $estoqueRecebe = ProdutosPorFilial::where('id_produto_unitop', $p->id_protudos)
                ->where('id_filial', $idFilialRecebe)
                ->first();
            $quantidadeFilialRecebe = $estoqueRecebe ? $estoqueRecebe->quantidade_produto : 0;

            // Calcula quantidade_superavit (mesma lógica do SQL)
            if (!in_array($grupoServico, [550, 640, 390])) {
                $quantidadeSuperavit =
                    ($p->quantidade ?? 0)
                    - ($p->quantidade_baixa ?? 0)
                    - $quantidadeEstoque;
            } else {
                $quantidadeSuperavit =
                    ($p->quantidade ?? 0)
                    - ($p->quantidade_baixa ?? 0);
            }

            // Cria o registro
            TransferenciaEstoqueAux::create([
                'data_inclusao' => now(),
                'id_relacao_solicitacoes_novo' => $idSolicitacaoNovo,
                'id_relacao_solicitacoes_antigo' => $idSolicitacaoParam,
                'id_produtos_solicitacoes' => $p->id_produtos_solicitacoes,
                'id_protudos_solicitado' => $p->id_protudos,
                'quantidade_estoq' => $quantidadeFilialRecebe,
                'quantidade_solici' => $p->quantidade,
                'quantidade_superavit' => $quantidadeSuperavit,
                'id_filial_recebe' => $idFilialRecebe,
                'id_filial_solicita' => $idFilialSolicita,
                'solicitacao' => 'PRIMEIRA SOLICITAÇÃO'
            ]);
        }
    }

    /**
     * Método para obter informações sobre requisições de transferência criadas
     *
     * @param int $requisicaoOriginalId
     * @return array
     */
    public function getRequisicoesTransferenciaCriadas($requisicaoOriginalId)
    {
        try {
            $requisicoesTransferencia = RelacaoSolicitacaoPeca::where('is_transferencia', true)
                ->whereHas('produtosSolicitacoes', function ($query) use ($requisicaoOriginalId) {
                    $query->where('observacao', 'like', "%requisição #{$requisicaoOriginalId}%");
                })
                ->with(['filial', 'produtosSolicitacoes.produto'])
                ->get();

            $resultado = [];
            foreach ($requisicoesTransferencia as $requisicao) {
                $filiaisTransferencia = $requisicao->produtosSolicitacoes
                    ->pluck('filial_transferencia')
                    ->unique()
                    ->filter();

                $resultado[] = [
                    'id_requisicao' => $requisicao->id_solicitacao_pecas,
                    'situacao' => $requisicao->situacao,
                    'filial_solicitante' => $requisicao->filial->name ?? 'N/A',
                    'filiais_transferencia' => $filiaisTransferencia->toArray(),
                    'total_itens' => $requisicao->produtosSolicitacoes->count(),
                    'data_criacao' => $requisicao->data_inclusao,
                    'observacao' => $requisicao->observacao
                ];
            }

            return [
                'success' => true,
                'requisicoes_transferencia' => $resultado,
                'total_requisicoes' => count($resultado)
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao buscar requisições de transferência: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao buscar requisições de transferência: ' . $e->getMessage()
            ];
        }
    }
}

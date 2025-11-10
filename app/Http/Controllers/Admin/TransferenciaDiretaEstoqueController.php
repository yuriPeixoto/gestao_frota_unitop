<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\SoftDeletes;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use App\Models\ProdutosPorFilial;
use Illuminate\Http\Request;
use App\Models\TransferenciaDiretaEstoque;
use App\Models\TransferenciaDiretaEstoqueItens;
use App\Models\TransferenciaEstoque;
use App\Models\TransferenciaEstoqueItens;
use App\Models\UserFilial;
use App\Models\VFilial;
use App\Traits\ExportableTrait;
use BaconQrCode\Renderer\RendererStyle\Fill;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\Border;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class TransferenciaDiretaEstoqueController extends Controller
{
    use ExportableTrait;

    protected $transferenciaDiretaEstoque;
    protected $filial;

    public function __construct(TransferenciaDiretaEstoque $transferenciaDiretaEstoque, VFilial $filial)
    {
        $this->transferenciaDiretaEstoque = $transferenciaDiretaEstoque;
        $this->filial = $filial;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $filialUsuario = Auth::user()->filial_id;

        session()->put('filtros_transferencia', $request->only([
            'status',
            'page',
            'data_inclusao',
            'data_final',
            'id_transferencia_direta_estoque'
        ]));

        // $query = $this->buildQueryWithFilters($request);

        $query = TransferenciaDiretaEstoque::query()
            ->with('filial', 'departamento', 'usuario', 'filial_relacao')->orderBy('id_transferencia_direta_estoque', 'desc');

        if ($request->has('search')) {
            $query->when($request->has('search'), function ($query) use ($request) {
                return $query->whereRaw('LOWER(usuario.name) LIKE LOWER(?)', ['%' . $request->search . '%']);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('id_transferencia_direta_estoque')) {
            $query->where('id_transferencia_direta_estoque', $request->id_transferencia_direta_estoque);
        }

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [$request->data_inclusao, $request->data_final]);
        } elseif ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao);
        } elseif ($request->filled('data_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_final);
        }

        // if ($request->filled('data_inclusao') && $request->filled('data_final')) {
        //     $query->whereBetween('data_inclusao', [
        //         $request->input('data_inclusao'),
        //         $request->input('data_final')
        //     ]);
        // }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // // Filtro por ID da transferência direta
        // if ($request->filled('id_transferencia_direta_estoque')) {
        //     $query->where('id_transferencia_direta_estoque', $request->id_transferencia_direta_estoque);
        // }
        $status = TransferenciaDiretaEstoque::select('status')
            ->distinct()
            ->orderBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->status,
                    'name' => $item->status
                ];
            });

        $query->where('filial', $filialUsuario);

        $results = $query->paginate(40);
        return view('admin.transferenciaDiretoEstoque.index', compact('results', 'status'));
    }

    /**
     * Mostra os detalhes de cada transferencia
     */
    public function show(Request $request, $id)
    {
        $query = $this->transferenciaDiretaEstoque
            ->join('transferencia_direta_estoque_itens as tdei', 'tdei.id_transferencia_direta_estoque', '=', 'transferencia_direta_estoque.id_transferencia_direta_estoque')
            ->join('produto as p', 'p.id_produto', '=', 'tdei.id_produto')
            ->leftJoin('produtos_por_filial as ppf', function ($join) {
                $join->on('ppf.id_produto_unitop', '=', 'p.id_produto')
                    ->where('ppf.id_filial', '=', 1); // ou variável da matriz
            })
            ->select(
                'tdei.id_transferencia_direta_estoque_itens as id_transferencia_item',
                'tdei.id_transferencia_direta_estoque as id_transferencia',
                'tdei.qtde_produto',
                'tdei.qtd_baixa',
                'p.id_produto',
                'p.descricao_produto',
                'ppf.quantidade_produto as quantidade_filial',
                DB::raw('(CASE WHEN (ppf.quantidade_produto = tdei.qtd_baixa) THEN TRUE ELSE FALSE END) as verificar')
            )
            ->where('transferencia_direta_estoque.id_transferencia_direta_estoque', $id);


        if ($request->has('search')) {
            $query->when($request->has('search'), function ($query) use ($request) {
                return $query->whereRaw('LOWER(p.descricao_produto) LIKE LOWER(?)', ['%' . $request->search . '%']);
            });
        }


        $transferencia = $query->paginate()->through(function ($item) {
            $item->matriz = DB::connection('pgsql')->table('filiais as f')
                ->join('produtos_por_filial as ppf', 'ppf.id_filial', '=', 'f.id')
                ->join('produto as p', 'p.id_produto', '=', 'ppf.id_produto_unitop')
                ->where('f.id', 1)
                ->where('p.id_produto', $item->id_produto)
                ->select('ppf.*', 'f.*')
                ->first();
            return $item;
        });





        return view('admin.transferenciaDiretoEstoque.show', compact('transferencia', 'id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create()
    {
        // Busca os produtos para o select
        $produtos = DB::table('produto')
            ->select('id_produto as value', 'descricao_produto as label')
            ->orderBy('id_produto')
            ->limit(30)
            ->get();

        return view('admin.transferenciaDiretoEstoque.create', compact('produtos'));
    }

    public function store(Request $request)
    {

        DB::beginTransaction();

        try {

            $userFilial = Auth::user();
            //$filialId = $userFilial?->filial_id;
            $usuario = Auth::user();
            $departamentoId = $usuario->departamento_id;

            $request->validate([
                'observacao' => 'nullable|string',
                'produtos' => 'required|array|min:1',
                'produtos.*.id_produto' => 'required|exists:produto,id_produto',
                'produtos.*.quantidade' => 'required|numeric|min:1',
            ]);

            // Criando transferencia
            $transferencia = TransferenciaDiretaEstoque::create([
                'observacao' => $request->observacao,
                'filial' => Auth::user()->filial_id ?? null,
                'id_usuario'    => Auth::id(),
                'id_departamento' => $departamentoId,
                'data_inclusao' =>  Carbon::now(),
                'data_alteracao' =>  Carbon::now(),
                'status' => 'INICIADA',
            ]);

            // Criando item para transferencia
            foreach ($request->produtos as $produto) {
                TransferenciaDiretaEstoqueItens::create([
                    'id_transferencia_direta_estoque' => $transferencia->id_transferencia_direta_estoque,
                    'id_produto'    =>  $produto['id_produto'],
                    //'qtd_baixa' =>  $produto['quantidade'],
                    'qtde_produto' =>  $produto['quantidade'],
                    'data_inclusao' => Carbon::now(),
                    'data_alteracao' =>  Carbon::now(),

                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Transferência registrada com sucesso!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Erro ao registrar a transferência: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function envio(string $id)
    {
        $transferencia = $this->transferenciaDiretaEstoque
            ->join('filiais as vf', 'vf.id', '=', 'transferencia_direta_estoque.filial')
            ->join('transferencia_direta_estoque_itens as tdei', 'tdei.id_transferencia_direta_estoque', '=', 'transferencia_direta_estoque.id_transferencia_direta_estoque')
            ->join('departamento as d', 'transferencia_direta_estoque.id_departamento', '=', 'd.id_departamento')
            ->join('produtos_por_filial as ppf', 'ppf.id_produto_unitop', '=', 'tdei.id_produto')
            ->join('estoque as et', 'ppf.id_estoque', '=', 'et.id_estoque')
            ->join('produto as p', 'p.id_produto', '=', 'ppf.id_produto_unitop')
            ->select(
                DB::raw('DISTINCT ON (tdei.id_transferencia_direta_estoque_itens) p.*'),
                'ppf.quantidade_produto as quantidade_filial',
                'et.*',
                'tdei.qtd_baixa',
                'tdei.id_transferencia_direta_estoque_itens as id_transferencia_item',
                'transferencia_direta_estoque.id_transferencia_direta_estoque as id_transferencia',
                'vf.name',
                'd.descricao_departamento',
                DB::raw('(CASE WHEN (ppf.quantidade_produto = tdei.qtd_baixa) THEN TRUE ELSE FALSE END) as verificar')
            )
            ->where('transferencia_direta_estoque.id_transferencia_direta_estoque', $id)
            ->groupBy(
                'p.id_produto',
                'ppf.quantidade_produto',
                'et.id_estoque',
                'tdei.qtd_baixa',
                'tdei.id_transferencia_direta_estoque_itens',
                'transferencia_direta_estoque.id_transferencia_direta_estoque',
                'vf.name',
                'd.descricao_departamento'
            )
            ->paginate(10);

        return view('admin.transferenciaDiretoEstoque.envio', compact('transferencia', 'id'));
    }

    public function envioTransferencia(Request $request, string $id)
    {
        try {
            Log::debug("Início do envioTransferencia para ID: {$id}");

            // Executa a procedure
            $result = DB::connection('pgsql')
                ->select('SELECT * FROM fc_inserir_transferencia_direta(?)', [$id]);

            Log::debug("Resultado da procedure:", ['result' => $result]);

            // Atualiza a transferência atual
            DB::connection('pgsql')
                ->table('transferencia_direta_estoque')
                ->where('id_transferencia_direta_estoque', $id)
                ->update([
                    'status' => 'FINALIZADA',
                    'transferencia_feita' => true,
                ]);

            // Atualiza a indireta (transferencia_estoque)
            DB::connection('pgsql')
                ->table('transferencia_estoque')
                ->whereRaw('id_tranferencia = (SELECT MAX(id_tranferencia) FROM transferencia_estoque)')
                ->update(['situacao' => 'EM_TRANSITO']);

            // Atualiza o registro antigo (caso exista)
            $idAntigo = DB::connection('pgsql')->table('transferencia_direta_estoque_aux')
                ->where('id_transferencia_direta_estoque_novo', $id)
                ->value('id_transferencia_direta_estoque_antigo');

            if ($idAntigo) {
                DB::connection('pgsql')
                    ->table('transferencia_direta_estoque')
                    ->where('id_transferencia_direta_estoque', $idAntigo)
                    ->update([
                        'status' => 'FINALIZADA',
                        'transferencia_feita' => true,
                    ]);
            }

            Log::debug("Transferência {$id} finalizada e indireta atualizada para EM_TRANSITO");

            return redirect()->back()->with('success', 'Transferência enviada com sucesso.');
        } catch (\Exception $e) {
            Log::error("Erro ao enviar transferência ID {$id}: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()]);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Busca a transferência com joins
        $transferencia = $this->transferenciaDiretaEstoque
            ->join('filiais as vf', 'vf.id', '=', 'transferencia_direta_estoque.filial')
            //->join('users as vuc', 'vuc.id', '=', 'transferencia_direta_estoque.id_usuario')
            ->join('departamento as d', 'transferencia_direta_estoque.id_departamento', '=', 'd.id_departamento')
            ->select(
                'transferencia_direta_estoque.*',
                'vf.name as filial_name',
                //'vuc.name as user',
                'd.descricao_departamento'
            )
            ->where('transferencia_direta_estoque.id_transferencia_direta_estoque', $id)
            ->first();

        if (!$transferencia) {
            abort(404);
        }

        // Produtos já selecionados na transferência
        $produtosSelecionados = DB::connection('pgsql')
            ->table('transferencia_direta_estoque_itens as tdei')
            ->join('produto as p', 'p.id_produto', '=', 'tdei.id_produto')
            ->where('tdei.id_transferencia_direta_estoque', $id)
            ->select('p.id_produto', 'p.descricao_produto', 'tdei.qtd_baixa', 'tdei.qtde_produto')
            ->get();

        // Todos os produtos para o select
        $produtos = Produto::orderBy('descricao_produto')
            ->limit(30)
            ->get(['id_produto', 'descricao_produto']);

        return view('admin.transferenciaDiretoEstoque.edit', compact(
            'transferencia',
            'produtosSelecionados',
            'produtos'
        ));
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            Log::info("Iniciando processarBaixa para transferência ID: $id");

            $produtos = $request->input('produtos');

            Log::info("Validação dos produtos OK", ['produtos' => $produtos]);

            $filialId = auth()->user()->id_filial;

            foreach ($produtos as $produto) {
                $idTransferenciaItem = $produto['id_transferencia_direta_estoque_itens'];
                $qtdBaixa = floatval($produto['qtd_baixa']);
                $idProduto = $produto['id_produto'];

                Log::info('Processando produto', $produto);

                // Busca o produto na filial
                $produtoEstoque = DB::connection('pgsql')
                    ->table('produtos_por_filial')
                    ->where('id_produto_unitop', $idProduto)
                    ->where('id_filial', $filialId)
                    ->first();

                if (!$produtoEstoque) {
                    Log::warning("❌ Produto ID $idProduto não encontrado na filial $filialId. Pulando...");
                    continue;
                } else {
                    Log::info("🔎 Produto encontrado no estoque: ID $idProduto, saldo atual: " . $produtoEstoque->quantidade_produto);
                }

                // Calcula novo saldo
                $quantidadeProduto = $produtoEstoque->quantidade_produto;
                $saldo = $quantidadeProduto - $qtdBaixa;

                if ($saldo < 0) {
                    Log::warning("⚠️ Estoque negativo detectado para produto $idProduto. Quantidade atual: $quantidadeProduto, tentativa de baixa: $qtdBaixa");
                    $saldo = 0;
                }

                // Atualiza estoque
                DB::connection('pgsql')
                    ->table('produtos_por_filial')
                    ->where('id_produto_unitop', $idProduto)
                    ->where('id_filial', $filialId)
                    ->update(['quantidade_produto' => $saldo]);

                Log::info("🛒 Estoque do produto ID $idProduto atualizado com sucesso. Novo saldo: $saldo");

                // Marca o item como baixado
                DB::table('transferencia_direta_estoque_itens')
                    ->where('id_transferencia_direta_estoque_itens', $idTransferenciaItem)
                    ->update(['baixa_realizada' => 1]);
            }

            // Atualiza status da transferência
            DB::table('transferencia_direta_estoque')
                ->where('id_transferencia_direta_estoque', $id)
                ->update(['status' => 'EM BAIXA']);

            Log::info("✅ Finalizando processo para transferência ID: $id");
            Log::info("📦 Status da transferência atualizado para: EM BAIXA");

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Baixa realizada com sucesso.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao realizar baixa de produtos: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao realizar baixa de produtos.']);
        }
    }

    public function updateTransferencia(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $observacao = $request->input('observacao');
            $produtos = $request->input('produtos', []);

            DB::table('transferencia_direta_estoque')
                ->where('id_transferencia_direta_estoque', $id)
                ->update(['observacao' => $observacao]);

            foreach ($produtos as $produto) {
                DB::table('transferencia_direta_estoque_itens')
                    ->where('id_transferencia_direta_estoque', $id)
                    ->where('id_produto', $produto['id_produto'])
                    ->update([
                        'qtde_produto' => $produto['quantidade'],
                        'data_alteracao' => now()
                    ]);
            }

            DB::commit();
            session()->flash('message', 'Transferência editada com sucesso!');
            return redirect()->route('admin.transferenciaDiretoEstoque.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na edição da transferência: ' . $e->getMessage());
            session()->flash('error', 'Erro ao editar transferência.');
            return redirect()->back();
        }
    }

    public function verificarQtdTodosProdutos($idSolicitacao)
    {
        $result = DB::connection('pgsql')->table('transferencia_direta_estoque_itens')
            ->selectRaw('CASE WHEN (qtde_produto = qtd_baixa) THEN TRUE ELSE FALSE END AS verificar')
            ->where('id_transferencia_direta_estoque', $idSolicitacao)
            ->get();

        if ($result) {
            $retorno = [];
            foreach ($result as $object) {
                $retorno[] = $object->verificar;
            }

            if (in_array(false, $retorno)) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function verificaSaldo($produto, $qtdBaixa, $qtdBaixaAnterior)
    {
        $user = Auth::user();

        $registro = DB::connection('pgsql')->table('produtos_por_filial')
            ->where('id_produto_unitop', $produto)
            ->where('id_filial', $user->filial_id)
            ->value('quantidade_produto');

        if ($registro === null) {
            throw new \Exception("Produto $produto não encontrado na filial {$user->filial_id}");
        }

        // Só verifica saldo se a nova baixa for maior que a anterior
        if ($qtdBaixa > $qtdBaixaAnterior) {
            $diferenca = $qtdBaixa - $qtdBaixaAnterior;
            return $registro >= $diferenca;
        }

        // Se não for aumentar a baixa, saldo está ok
        return true;
    }

    public function verificarBaixaAnterior($idSolicitacao, $idProduto)
    {
        $produto = DB::connection('pgsql')->table('transferencia_direta_estoque_itens')
            ->select('qtd_baixa')
            ->where('id_transferencia_direta_estoque', $idSolicitacao)
            ->where('id_produto', $idProduto)
            ->first();


        return $produto ? (float) $produto->qtd_baixa : 0;
    }


    public function gerarPdf(Request $request, $id)
    {
        try {


            $dominio = $_SERVER['SERVER_NAME'];
            $parametros = ['P_requisicao' => intval($id)];

            $name  = 'Baixa_Produtos_requisicao';
            $agora = date('d-m-Y_H-i');
            $tipo  = '.pdf';
            $relatorioNome = "{$name}_{$agora}{$tipo}";

            $jasperserver = $dominio == '127.0.0.1' ? 'http://www.unitopconsultoria.com.br:9088/jasperserver' : 'http://10.10.1.8:8080/jasperserver';
            $relatorioPath = "/reports/homologacao/{$name}";

            $url = "{$jasperserver}/rest_v2/reports{$relatorioPath}.pdf";

            $queryParams = http_build_query($parametros);
            $url .= "?{$queryParams}";

            $username = env('USER_JASPERREPORTS');
            $password = env('PASSWORD_JASPERREPORTS');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

            $data = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                return response()->json(['error' => 'Erro ao gerar relatório.'], 500);
            }

            return Response::make($data, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename={$relatorioNome}",
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getEstoquePorProduto(Request $request)
    {
        $idProduto = $request->get('id_produto');
        $idFilial = $request->get('id_filial');

        if (!$idProduto || !$idFilial) {
            return response()->json(['quantidade' => 0]);
        }

        $registro = ProdutosPorFilial::withoutGlobalScopes()
            ->where('id_produto_unitop', $idProduto)
            ->where('id_filial', $idFilial)
            ->first();

        return response()->json([
            'quantidade' => $registro?->quantidade_produto ?? 0
        ]);
    }

    public function baixar(Request $request, $id)
    {
        if ($request->isMethod('post')) {

            $produtos = $request->input('produtos', []);
            $action = $request->input('action');

            if (empty($produtos)) {
                return redirect()->back()->with('error', 'Nenhum produto informado.');
            }

            Log::info('Processando produto', $produtos);

            foreach ($produtos as $produto) {
                if (!isset($produto['id_transferencia_direta_estoque_itens']) || !isset($produto['qtd_baixa'])) {
                    continue;
                }

                $item = TransferenciaDiretaEstoqueItens::find($produto['id_transferencia_direta_estoque_itens']);
                if ($item) {
                    $item->qtd_baixa = $produto['qtd_baixa'];
                    $item->save();

                    // --- Ajusta estoque da filial do usuário (descontar) ---
                    $idFilial = Auth::user()->id_filial;

                    $estoque = ProdutosPorFilial::where('id_produto_unitop', $item->id_produto_unitop)
                        ->where('id_filial', $idFilial)
                        ->first();

                    if ($estoque) {
                        // evita quantidade negativa
                        $novaQuantidade = max(0, $estoque->quantidade_produto - $produto['qtd_baixa']);

                        $estoque->update([
                            'quantidade_produto' => $novaQuantidade,
                            'data_alteracao'     => now(),
                        ]);

                        Log::info('Estoque atualizado (baixa)', [
                            'produto' => $item->id_produto_unitop,
                            'filial' => $idFilial,
                            'qtd_baixada' => $produto['qtd_baixa'],
                            'novo_saldo' => $novaQuantidade
                        ]);
                    } else {
                        Log::warning("Produto {$item->id_produto_unitop} não encontrado no estoque da filial {$idFilial}");
                    }
                }
            }


            Log::info('Processando mudança', ['item' => $item->toArray()]);
            // Se ação for "finalizar", validamos se todos foram baixados completamente
            if ($action === 'finalizar') {
                $itens = TransferenciaDiretaEstoqueItens::where('id_transferencia_direta_estoque', $id)->get();
                $todosBaixados = $itens->every(function ($item) {
                    return $item->qtd_baixa >= $item->qtde_produto;
                });

                if (!$todosBaixados) {
                    return redirect()->back()->with('error', 'Existem produtos com baixa incompleta.');
                }

                // Finaliza a transferência
                $transferencia = TransferenciaDiretaEstoque::find($id);
                $transferencia->status = 'FINALIZADA';
                $transferencia->save();
                Log::info('Status atualizado', $transferencia);
                return redirect()->route('admin.transferenciaDiretoEstoque.index')->with('success', 'Transferência finalizada com sucesso!');
            }

            return redirect()->back()->with('success', 'Baixa salva com sucesso.');
        }

        // GET: carregar a view
        $transferencia = TransferenciaDiretaEstoque::with([
            'filial:id,name',
            'departamento:id_departamento,descricao_departamento',
            'itens.produto:id_produto,descricao_produto'
        ])->findOrFail($id);

        $produtos = Produto::orderBy('descricao_produto')
            ->limit(30)
            ->get(['id_produto', 'descricao_produto']);

        return view('admin.transferenciaDiretoEstoque.baixarview', [
            'transferencia' => $transferencia,
            'produtosSelecionados' => $transferencia->itens,
            'produtos' => $produtos,
        ]);
    }

    public function processarBaixa(Request $request, $id)
    {
        Log::info("Iniciando processarBaixa para transferência ID: {$id}");

        // Validação básica dos produtos
        $request->validate([
            'produtos' => 'required|array',
            'produtos.*.id_transferencia_direta_estoque_itens' => 'required|exists:transferencia_direta_estoque_itens,id_transferencia_direta_estoque_itens',
            'produtos.*.qtd_baixa' => 'required|numeric|min:0',
        ]);

        Log::info('Validação dos produtos OK', ['produtos' => $request->input('produtos')]);

        $idFilial = Auth::user()->filial_id ?? null;

        Log::info('Filial capturada', ['Filial' => $idFilial]);

        DB::transaction(function () use ($request, $idFilial) {

            foreach ($request->input('produtos', []) as $produtoData) {
                Log::info('Processando produto', $produtoData);

                $item = TransferenciaDiretaEstoqueItens::findOrFail($produtoData['id_transferencia_direta_estoque_itens']);
                $qtdeBaixa = $produtoData['qtd_baixa'];

                // Calcula a diferença de baixa (para não descontar duas vezes)
                $qtdeAnterior = $item->qtd_baixa ?? 0;
                $diferencaBaixa = $qtdeBaixa - $qtdeAnterior;

                // Atualiza a baixa no item da transferência
                $item->qtd_baixa = $qtdeBaixa;
                $item->save();

                // Atualiza estoque da filial
                $estoque = ProdutosPorFilial::firstOrNew([
                    'id_produto_unitop' => $item->id_produto,
                    'id_filial' => $idFilial,
                ]);

                if (!$estoque->exists) {
                    // Campos obrigatórios ao criar um novo registro
                    $estoque->quantidade_produto = 0;
                    $estoque->valor_medio = 0;
                    $estoque->data_inclusao = now();
                    $estoque->data_alteracao = now();
                    $estoque->is_ativo = true;
                    //$estoque->id_filial = $idFilial;
                    $estoque->save();
                }

                // Atualiza saldo considerando a diferença da baixa
                $novoSaldo = $estoque->quantidade_produto - $diferencaBaixa;
                $estoque->quantidade_produto = $novoSaldo;
                $estoque->data_alteracao = now();
                $estoque->save();

                Log::info('Estoque atualizado (baixa)', [
                    'produto' => $estoque->id_produto_unitop,
                    'filial'  => $idFilial,
                    'qtd_baixada' => $diferencaBaixa,
                    'novo_saldo' => $novoSaldo
                ]);
            }
        });

        // --- Finalização da transferência ---
        if ($request->input('action') === 'finalizar') {
            Log::info("Finalizando processo para transferência ID: {$id}");

            $transferencia = TransferenciaDiretaEstoque::with('itens')->findOrFail($id);

            $todosItensBaixados = $transferencia->itens->every(function ($item) {
                return $item->qtd_baixa >= $item->qtde_produto;
            });

            $transferencia->status = $todosItensBaixados ? 'EM BAIXA' : 'EM BAIXA PARCIAL';
            $transferencia->save();

            Log::info("Status da transferência atualizado para: " . $transferencia->status);

            return redirect()->route('admin.transferenciaDiretoEstoque.index')
                ->with('success', 'Baixas atualizadas e processo finalizado com sucesso.');
        }

        return redirect()->back()->with('success', 'Baixas registradas com sucesso.');
    }

    public function visualizarModal($id)
    {
        $query = DB::table('transferencia_direta_estoque_itens as tdei')
            ->join('produto as p', 'p.id_produto', '=', 'tdei.id_produto')
            ->leftJoin('produtos_por_filial as ppf', function ($join) {
                $join->on('ppf.id_produto_unitop', '=', 'p.id_produto')
                    ->where('ppf.id_filial', '=', 1); // matriz
            })
            ->select(
                'tdei.id_transferencia_direta_estoque_itens as id_transferencia_item',
                'tdei.id_transferencia_direta_estoque as id_transferencia',
                'tdei.qtde_produto',
                'tdei.qtd_baixa',
                'p.id_produto',
                'p.descricao_produto',
                'ppf.quantidade_produto as quantidade_matriz',
                DB::raw('(CASE WHEN (ppf.quantidade_produto = tdei.qtd_baixa) THEN TRUE ELSE FALSE END) as verificar')
            )
            ->where('tdei.id_transferencia_direta_estoque', $id);

        $transferencia = $query->paginate(10)->through(function ($item) {
            $item->matriz = DB::table('produtos_por_filial as ppf')
                ->join('filiais as f', 'ppf.id_filial', '=', 'f.id')
                ->where('f.id', 1)
                ->where('ppf.id_produto_unitop', $item->id_produto)
                ->select('ppf.quantidade_produto', 'f.*')
                ->first();
            return $item;
        });

        return view('components.transferencia.modal-visualizar', compact('transferencia'));
    }


    public function confirmar(Request $request, $id)
    {
        if ($request->isMethod('post')) {

            $produtos = $request->input('produtos', []);
            $action = $request->input('action');

            if (empty($produtos)) {
                return redirect()->back()->with('error', 'Nenhum produto informado.');
            }

            Log::info('Processando produto', $produtos);

            foreach ($produtos as $produto) {
                if (!isset($produto['id_transferencia_direta_estoque_itens']) || !isset($produto['qtd_baixa'])) {
                    continue;
                }

                $item = TransferenciaDiretaEstoqueItens::find($produto['id_transferencia_direto_estoque_itens']);
                if ($item) {
                    $item->qtd_baixa = $produto['qtd_baixa'];
                    $item->save();
                }
            }

            Log::info('Processando mudança', ['item' => $item->toArray()]);
            // Se ação for "finalizar", validamos se todos foram baixados completamente
            if ($action === 'finalizar') {
                $itens = TransferenciaDiretaEstoqueItens::where('id_transferencia_direta_estoque', $id)->get();
                $todosBaixados = $itens->every(function ($item) {
                    return $item->qtd_baixa >= $item->qtde_produto;
                });

                if (!$todosBaixados) {
                    return redirect()->back()->with('error', 'Existem produtos com baixa incompleta.');
                }

                // Finaliza a transferência
                $transferencia = TransferenciaDiretaEstoque::find($id);
                $transferencia->status = 'FINALIZADA';
                $transferencia->save();
                Log::info('Status atualizado', $transferencia);
                return redirect()->route('admin.transferenciaDiretoEstoque.index')->with('success', 'Transferência finalizada com sucesso!');
            }

            return redirect()->back()->with('success', 'Baixa salva com sucesso.');
        }

        // GET: carregar a view
        $transferencia = TransferenciaDiretaEstoque::with([
            'filial:id,name',
            'departamento:id_departamento,descricao_departamento',
            'itens.produto:id_produto,descricao_produto'
        ])->findOrFail($id);

        $produtos = Produto::orderBy('descricao_produto')
            ->limit(30)
            ->get(['id_produto', 'descricao_produto']);

        return view('admin.transferenciaDiretoEstoque.confirmarRecebimento', [
            'transferencia' => $transferencia,
            'produtosSelecionados' => $transferencia->itens,
            'produtos' => $produtos,
        ]);
    }

    public function confirmarRecebimento(Request $request, $id)
    {
        $request->validate([
            'produtos' => 'required|array',
            'produtos.*.id_transferencia_direta_estoque_itens' => 'required|exists:transferencia_direta_estoque_itens,id_transferencia_direta_estoque_itens',
            'produtos.*.qtd_baixa' => 'required|numeric|min:0',
            'observacao' => 'nullable|string',
            'action' => 'required|string|in:salvar,finalizar',
        ]);

        DB::connection('pgsql')->beginTransaction();

        try {
            // Atualiza observação
            if ($request->filled('observacao')) {
                DB::connection('pgsql')->table('transferencia_direta_estoque')
                    ->where('id_transferencia_direta_estoque', $id)
                    ->update(['observacao' => $request->input('observacao')]);
            }

            // Atualiza cada item com a qtd_baixa enviada
            foreach ($request->input('produtos') as $produtoData) {
                DB::connection('pgsql')->table('transferencia_direta_estoque_itens')
                    ->where('id_transferencia_direta_estoque_itens', $produtoData['id_transferencia_direta_estoque_itens'])
                    ->update(['qtd_baixa' => $produtoData['qtd_baixa']]);
            }

            // Se ação for finalizar
            if ($request->input('action') === 'finalizar') {
                $faltando = DB::connection('pgsql')
                    ->table('transferencia_direta_estoque_itens')
                    ->where('id_transferencia_direta_estoque', $id)
                    ->whereColumn('qtd_baixa', '<', 'qtde_produto')
                    ->exists();

                if ($faltando) {
                    DB::connection('pgsql')->rollBack();
                    return redirect()->back()->with('error', 'Existem produtos com baixa incompleta.');
                }

                // Marca a transferência direta como FINALIZADA
                DB::connection('pgsql')->table('transferencia_direta_estoque')
                    ->where('id_transferencia_direta_estoque', $id)
                    ->update([
                        'status' => 'FINALIZADA',
                        'data_alteracao' => now(),
                    ]);
            }

            DB::connection('pgsql')->commit();

            $msg = $request->input('action') === 'finalizar'
                ? 'Transferência finalizada com sucesso!'
                : 'Baixa salva com sucesso.';

            return redirect()->route('admin.transferenciaDiretoEstoque.index')->with('success', $msg);
        } catch (Exception $e) {
            DB::connection('pgsql')->rollBack();
            Log::error("Erro ao confirmar recebimento da transferência {$id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao confirmar recebimento: ' . $e->getMessage());
        }
    }



    public function buildQueryWithFilters(Request $request)
    {
        $query = TransferenciaDiretaEstoque::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('id_transferencia_direta_estoque')) {
            $query->where('id_transferencia_direta_estoque', $request->id_transferencia_direta_estoque);
        }

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [$request->data_inclusao, $request->data_final]);
        } elseif ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao);
        } elseif ($request->filled('data_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_final);
        }

        return $query->with([
            'usuario',
            'filial',
            'filial_solicita_',
            'departamento'
        ])->orderByDesc('id_transferencia_direta_estoque');
    }

    public function exportPdf(Request $request)
    {
        try {
            Log::info('Iniciando exportação de PDF', $request->all());

            // Monta query já com filtros
            $query = $this->buildQueryWithFilters($request);

            // Para exportação, traz TODOS os registros filtrados (sem paginação)
            $resultados = $query->get();

            // Renderiza a view do PDF
            $html = View::make('PDFS.transferencias_pdf', compact('resultados'))->render();

            // Configura Dompdf
            $options = new Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isHtml5ParserEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="transferenciaDiretoEstoque.pdf"');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function exportCsv(Request $request)
    {
        try {
            $query = TransferenciaDiretaEstoque::query();
            $page = $request->input('page', 1);
            $perPage = 40;

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $resultados = $query->with([
                'usuario',
                'filial',
                'filial_solicita_',
                'departamento'
            ])->orderBy('id_transferencia_direta_estoque', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            if ($resultados->isEmpty()) {
                throw new \Exception('Nenhum registro encontrado para exportação.');
            }

            // Define o nome e caminho do arquivo
            $filename = 'transferencias_' . uniqid() . '.csv';
            $filepath = storage_path('app/output/' . $filename);

            // Garante que o diretório existe
            if (!file_exists(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }

            // Abre arquivo CSV para escrita
            $handle = fopen($filepath, 'w');

            // Define as colunas do CSV
            $header = [
                'ID',
                'Filial',
                'Filial Solicitante',
                'Departamento',
                'Usuário',
                'Status',
                'Data de Criação'
            ];
            fputcsv($handle, $header);

            // Escreve os dados
            foreach ($resultados as $item) {
                fputcsv($handle, [
                    $item->id_transferencia_direta_estoque,
                    optional($item->filial)->nome ?? '',
                    optional($item->filial_solicita_)->nome ?? '',
                    optional($item->departamento)->nome ?? '',
                    optional($item->usuario)->name ?? '',
                    $item->status ?? '',
                    optional($item->created_at)->format('d/m/Y H:i') ?? '',
                ]);
            }

            fclose($handle);

            // Retorna para download
            return response()->download($filepath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Erro ao exportar CSV: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    public function exportXls(Request $request)
    {
        // Pega filtros da sessão ou do request
        $request->merge(session('filtros_transferencia', []));

        // Monta a query COM filtros
        $query = TransferenciaDiretaEstoque::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('id_transferencia_direta_estoque')) {
            $query->where('id_transferencia_direta_estoque', $request->id_transferencia_direta_estoque);
        }

        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao);
        }

        if ($request->filled('data_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_final);
        }

        $query->with(['usuario', 'filial', 'filial_solicita_', 'departamento'])
            ->orderBy('id_transferencia_direta_estoque', 'desc');

        $columns = [
            'id_transferencia_direta_estoque' => 'ID',
            'filial.name' => 'Filial',
            'filial_solicita_.name' => 'Filial Solicitante',
            'departamento.descricao_departamento' => 'Departamento',
            'usuario.name' => 'Usuário',
            'status' => 'Status',
            'data_inclusao' => 'Data de Inclusao',
        ];

        return $this->exportToExcel($request, $query, $columns, 'transferencias');
    }

    /*
    protected function exportToExcel(Request $request, $query, array $columns, string $filenamePrefix, array $filters = [])
    {
        $registros = $query->get();

        if ($registros->isEmpty()) {
            return back()->with('error', 'Nenhum registro encontrado para exportação.');
        }

        $filename = $filenamePrefix . '_' . uniqid() . '.xls';

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');

        // Cabeçalho
        fputcsv($output, array_values($columns), "\t");

        // Conteúdo
        foreach ($registros as $item) {
            $linha = [];

            foreach ($columns as $key => $label) {
                $valor = data_get($item, $key);

                if ($valor instanceof \Carbon\Carbon) {
                    $valor = $valor->format('d/m/Y H:i');
                }

                $linha[] = $valor ?? '';
            }

            fputcsv($output, $linha, "\t");
        }

        fclose($output);
        exit;
    }
*/
    public function exportXml(Request $request)
    {
        // Monta query com filtros — você pode chamar um método que retorna o query com filtros aplicados
        $query = TransferenciaDiretaEstoque::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('id_transferencia_direta_estoque')) {
            $query->where('id_transferencia_direta_estoque', $request->id_transferencia_direta_estoque);
        }

        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao);
        }

        if ($request->filled('data_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_final);
        }

        $query->with(['usuario', 'filial', 'filial_solicita_', 'departamento'])
            ->orderBy('id_transferencia_direta_estoque', 'desc');

        $structure = [
            'id' => 'id_transferencia_direta_estoque',
            'filial' => 'filial.name',
            'filial_solicitante' => 'filial_solicita_.name',
            'departamento' => 'departamento.descricao_departamento',
            'usuario' => 'usuario.name',
            'status' => 'status',
            'data_inclusao' => 'data_inclusao',
            'observacao' => 'observacao'
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'transferencias',    // prefixo do arquivo
            'transferencia',     // nome do item XML
            'transferencias',    // nome do elemento raiz
            []                   // filtros, se precisar
        );
    }

    protected function exportToXml(Request $request, $query, array $structure, string $filenamePrefix, string $itemElement, string $rootElement, array $filters = [])
    {
        $registros = $query->get();

        if ($registros->isEmpty()) {
            return back()->with('error', 'Nenhum registro encontrado para exportação.');
        }

        // Cria XML raiz
        $xml = new \SimpleXMLElement("<{$rootElement}/>");

        foreach ($registros as $item) {
            $xmlItem = $xml->addChild($itemElement);

            foreach ($structure as $xmlKey => $field) {
                $value = data_get($item, $field);

                // Formata datas se for Carbon
                if ($value instanceof \Carbon\Carbon) {
                    $value = $value->format('Y-m-d H:i:s');
                }

                // Escapa valores (converte & < > etc)
                $xmlItem->addChild($xmlKey, htmlspecialchars((string)$value));
            }
        }

        $filename = $filenamePrefix . '_' . uniqid() . '.xml';

        // Headers para download XML
        header('Content-Type: text/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        echo $xml->asXML();
        exit;
    }

    public function solicitar($id)
    {
        try {
            // Inicia transação
            DB::connection('pgsql')->beginTransaction();

            // Chama a function do banco que faz todo o processo
            $resultado = DB::connection('pgsql')->select("
            SELECT fc_gerar_requisicao_direta_matriz(?)
        ", [$id]);

            // Verifica retorno (se a função retornar algo)
            $retorno = $resultado[0]->fc_gerar_requisicao_direta_matriz ?? null;

            DB::connection('pgsql')->commit();

            // Mensagem de sucesso

            TransferenciaDiretaEstoque::where('id_transferencia_direta_estoque', $id)
                ->update(['status' => 'AGUARDANDO TRANSFERENCIA']);

            return redirect()->back()->with('success', 'Requisição criada e enviada para o Estoque Matriz com sucesso!');
        } catch (\Exception $e) {
            DB::connection('pgsql')->rollBack();
            Log::error('Erro ao solicitar transferência direta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao solicitar transferência: ' . $e->getMessage());
        }
    }


    public function recebimento($id)
    {
        $query = DB::table('transferencia_direta_estoque_itens as tdei')
            ->join('produto as p', 'p.id_produto', '=', 'tdei.id_produto')
            ->leftJoin('produtos_por_filial as ppf', function ($join) {
                $join->on('ppf.id_produto_unitop', '=', 'p.id_produto')
                    ->where('ppf.id_filial', '=', 1); // matriz
            })
            ->select(
                'tdei.id_transferencia_direta_estoque_itens as id_transferencia_item',
                'tdei.id_transferencia_direta_estoque as id_transferencia',
                'tdei.qtde_produto',
                'tdei.qtd_baixa',
                'p.id_produto',
                'p.descricao_produto',
                'ppf.quantidade_produto as quantidade_matriz',
                DB::raw('(CASE WHEN (ppf.quantidade_produto = tdei.qtd_baixa) THEN TRUE ELSE FALSE END) as verificar')
            )
            ->where('tdei.id_transferencia_direta_estoque', $id);

        $transferencia = $query->get()->map(function ($item) {
            $item->matriz = DB::table('produtos_por_filial as ppf')
                ->join('filiais as f', 'ppf.id_filial', '=', 'f.id')
                ->where('f.id', 1)
                ->where('ppf.id_produto_unitop', $item->id_produto)
                ->select('ppf.quantidade_produto', 'f.*')
                ->first();
            return $item;
        });


        return view('components.transferencia.modal-visualizar-transferencia', [
            'transferencia' => $transferencia,
            'id' => $id,
        ]);
    }
}

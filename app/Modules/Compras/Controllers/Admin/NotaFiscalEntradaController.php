<?php

namespace App\Modules\Compras\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NotaFiscalEntrada;
use App\Models\NotaFiscalProdutos;
use App\Modules\Estoque\Models\HistoricoMovimentacaoEstoque;
use App\Models\Municipio;
use App\Models\Estado;
use App\Models\Filial;
use App\Models\PedidoCompra;
use App\Models\ProdutosPorFilial;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\JasperServerIntegration;

class NotaFiscalEntradaController extends Controller
{
    public function index(Request $request)
    {
        $query = NotaFiscalEntrada::query()
            ->with(['pedidoCompra'])
            ->where('id_filial', GetterFilial());

        // Aplica filtros baseados na requisição
        $this->applyFilters($query, $request);

        // Usa cache para melhorar a performance
        $nfEntrada = $query->latest('id_nota_fiscal_entrada')
            ->paginate(20)
            ->appends($request->query());

        // Para requisições HTMX, retorna apenas a tabela
        if ($request->header('HX-Request')) {
            return view('admin.notafiscalentrada._table', compact('nfEntrada'));
        }

        return view('admin.notafiscalentrada.index', compact(
            'nfEntrada'
        ));

        return view('admin.notafiscalentrada.index');
    }

    public function create()
    {
        $municipios = Municipio::select('id_municipio as value', 'nome_municipio as label')->orderBy('nome_municipio')->get();
        $estados = Estado::select('id_uf as value', 'uf as label')->orderBy('nome')->get();
        $filial = Filial::select('id as value', 'name as label')->orderBy('name')->get();

        return view('admin.notafiscalentrada.create', compact('municipios', 'estados', 'filial'));
    }

    public function store(Request $request)
    {
        $validar = $this->validateNfProdutos($request);

        if (!empty($request->input('chave_nf_entrada'))) {
            $numero_sem_zeros = ltrim($request->input('chave_nf_entrada'), '0'); //Importado do sistema legado
        }

        try {
            db::beginTransaction();

            $nfEntrada = new NotaFiscalEntrada();

            $nfEntrada->data_inclusao      = now();
            $nfEntrada->cnpj               = $validar['cnpj'];
            $nfEntrada->nome_empresa       = $validar['nome_empresa'];
            $nfEntrada->numero             = $request->input('numero') ?? null;
            $nfEntrada->endereco           = $request->input('endereco') ?? null;
            $nfEntrada->bairro             = $request->input('bairro') ?? null;
            $nfEntrada->ibge_municio       = Municipio::where('nome_municipio', $request->input('nome_municipio'))->where('uf', $request->input('uf'))->first()->cod_ibge ?? null;
            $nfEntrada->nome_municipio     = $request->input('nome_municipio') ?? null;
            $nfEntrada->uf                 = $request->input('uf') ?? null;
            $nfEntrada->cep                = $request->input('cep') ?? null;
            $nfEntrada->cod_nota_fiscal    = 1;
            $nfEntrada->natureza_operacao  = $request->input('natureza_operacao') ?? null;
            $nfEntrada->numero_nota_fiscal = $validar['numero_nota_fiscal'];
            $nfEntrada->data_emissao       = $validar['data_emissao'];
            $nfEntrada->data_saida         = $request->input('data_saida') ?? null;
            $nfEntrada->valor_nota_fiscal  = $validar['valor_nota_fiscal'];
            $nfEntrada->valor_frete        = $request->input('valor_frete') ?? 0;
            $nfEntrada->valor_desconto     = $request->input('valor_desconto_nfe') ?? 0;
            $nfEntrada->processada         = true;
            $nfEntrada->id_filial          = $request->input('id_filial');
            $nfEntrada->id_fornecedor      = $request->input('id_fornecedor');
            $nfEntrada->id_uf              = Estado::where('uf', $request->input('uf'))->first()->id_uf ?? null;
            $nfEntrada->apuracao_saldo     = false;
            $nfEntrada->aplica_rateio      = $request->input('aplica_rateio') ?? false;
            $nfEntrada->id_pedido_compras  = $request->input('id_pedido_compras') ?? null;
            $nfEntrada->chave_nf_entrada   = $numero_sem_zeros ?? null;
            $nfEntrada->tipo_nota          = 1;
            $nfEntrada->id_user            = Auth::user()->id;

            $nfEntrada->save();

            db::commit();

            $this->processNfProdutos($validar, $nfEntrada->id_nota_fiscal_entrada);

            return redirect()->route('admin.notafiscalentrada.index')->with('success', 'Nota Fiscal Entrada cadastrada com sucesso!');
        } catch (\Exception $e) {
            db::rollBack();
            Log::error($e->getMessage());
        }
    }

    public function edit($id)
    {
        $nfEntrada = NotaFiscalEntrada::findorFail($id);

        $nfeProdutos = $this->getProdutos($id);

        $municipios = Municipio::select('id_municipio as value', 'nome_municipio as label')->orderBy('nome_municipio')->get();
        $estados = Estado::select('id_uf as value', 'uf as label')->orderBy('nome')->get();
        $filial = Filial::select('id as value', 'name as label')->orderBy('name')->get();

        return view('admin.notafiscalentrada.edit', compact('nfEntrada', 'nfeProdutos', 'municipios', 'estados', 'filial'));
    }

    public function update(Request $request, $id)
    {
        $validar = $this->validateNfProdutos($request);

        //Importado do sistema legado
        if (!empty($request->input('chave_nf_entrada'))) {
            $numero_sem_zeros = ltrim($request->input('chave_nf_entrada'), '0');
        }

        try {
            db::beginTransaction();

            $nfEntrada = NotaFiscalEntrada::findorFail($id);

            $nfEntrada->data_alteracao      = now();
            $nfEntrada->cnpj               = $validar['cnpj'];
            $nfEntrada->nome_empresa       = $validar['nome_empresa'];
            $nfEntrada->numero             = $request->input('numero') ?? null;
            $nfEntrada->endereco           = $request->input('endereco') ?? null;
            $nfEntrada->bairro             = $request->input('bairro') ?? null;
            $nfEntrada->ibge_municio       = Municipio::where('nome_municipio', $request->input('nome_municipio'))->where('uf', $request->input('uf'))->first()->cod_ibge ?? null;
            $nfEntrada->nome_municipio     = $request->input('nome_municipio') ?? null;
            $nfEntrada->uf                 = $request->input('uf') ?? null;
            $nfEntrada->cep                = $request->input('cep') ?? null;
            $nfEntrada->cod_nota_fiscal    = 1;
            $nfEntrada->natureza_operacao  = $request->input('natureza_operacao') ?? null;
            $nfEntrada->numero_nota_fiscal = $validar['numero_nota_fiscal'];
            $nfEntrada->data_emissao       = $validar['data_emissao'];
            $nfEntrada->data_saida         = $request->input('data_saida') ?? null;
            $nfEntrada->valor_nota_fiscal  = $validar['valor_nota_fiscal'];
            $nfEntrada->valor_frete        = $request->input('valor_frete') ?? 0;
            $nfEntrada->valor_desconto     = $request->input('valor_desconto_nfe') ?? 0;
            $nfEntrada->id_filial          = $request->input('id_filial');
            $nfEntrada->id_fornecedor      = $request->input('id_fornecedor');
            $nfEntrada->id_uf              = Estado::where('uf', $request->input('uf'))->first()->id_uf ?? null;
            $nfEntrada->aplica_rateio      = $request->input('aplica_rateio') ?? false;
            $nfEntrada->id_pedido_compras  = $request->input('id_pedido_compras') ?? null;
            $nfEntrada->chave_nf_entrada   = $numero_sem_zeros ?? null;
            $nfEntrada->tipo_nota          = 1;
            $nfEntrada->id_user            = Auth::user()->id;

            $nfEntrada->save();


            db::commit();

            if (!empty($nfEntrada->id_nota_fiscal_entrada)) {
                NotaFiscalProdutos::where('id_nota_fiscal_entrada', $id)->delete();
            }

            $this->processNfProdutos($validar, $nfEntrada->id_nota_fiscal_entrada);

            return redirect()->route('admin.notafiscalentrada.index');
        } catch (\Exception $e) {
            db::rollBack();
            Log::error('Erro ao atualizar nota fiscal: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            db::beginTransaction();

            NotaFiscalProdutos::where('id_nota_fiscal_entrada', $id)->delete();
            NotaFiscalEntrada::findorFail($id)->delete();

            db::commit();

            return response()->json([
                'success' => true,
                'title'   => 'Sucesso',
                'message' => 'Nota Fiscal Entrada excluída com sucesso!'
            ], 200);
        } catch (\Exception $e) {
            db::rollBack();
            Log::error('Erro ao excluir nota fiscal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'title'   => 'Erro',
                'message' => 'Não foi possivel excluir a Nota Fiscal Entrada: ' . $e->getMessage()
            ], 200);
        }
    }

    public function applyFilters($query, Request $request)
    {
        if ($request->filled('id_nota_fiscal_entrada')) {
            $query->where('id_nota_fiscal_entrada', $request->input('id_nota_fiscal_entrada'));
        }

        if ($request->filled('id_pedido_compras')) {
            $query->where('id_pedido_compras', $request->input('id_pedido_compras'));
        }

        if ($request->filled('cnpj')) {
            $query->where('cnpj', $request->input('cnpj'));
        }

        if ($request->filled('nome_empresa')) {
            $query->where('nome_empresa', 'ilike', '%' . $request->input('nome_empresa') . '%');
        }

        if ($request->filled('numero')) {
            $query->where('numero', $request->input('numero'));
        }

        if ($request->filled('cod_nota_fiscal')) {
            $query->where('cod_nota_fiscal', $request->input('cod_nota_fiscal'));
        }

        if ($request->filled('natureza_operacao')) {
            $query->where('natureza_operacao', $request->input('natureza_operacao'));
        }

        if ($request->filled('numero_nota_fiscal')) {
            $query->where('numero_nota_fiscal', $request->input('numero_nota_fiscal'));
        }

        if ($request->filled('data_inclusao_inicial')) {
            $query->whereDate('data_inclusao', '>=', $request->input('data_inclusao_inicial'));
        }

        if ($request->filled('data_inclusao_final')) {
            $query->whereDate('data_inclusao', '<=', $request->input('data_inclusao_final'));
        }

        if ($request->filled('data_emissao_inicial')) {
            $query->whereDate('data_emissao', '>=', $request->input('data_emissao_inicial'));
        }

        if ($request->filled('data_emissao_final')) {
            $query->whereDate('data_emissao', '<=', $request->input('data_emissao_final'));
        }
    }

    public function search(Request $request)
    {
        $term = strtolower($request->get('term', ''));

        $fornecedores = NotaFiscalEntrada::where('numero_nota_fiscal', $term)
            ->orderBy('data_inclusao', 'desc')
            ->limit(30)
            ->get(['numero_nota_fiscal as label', 'id_nota_fiscal_entrada as value']);

        return response()->json($fornecedores);
    }

    public function getById($id)
    {
        $NFEntrada = NotaFiscalEntrada::findOrFail($id);

        return response()->json([
            'value' => $NFEntrada->id_nota_fiscal_entrada,
            'label' => $NFEntrada->numero_nota_fiscal
        ]);
    }

    private function validateNfProdutos(Request $request)
    {
        return $request->validate([
            'nome_empresa' => 'required',
            'cnpj' => 'required',
            'numero_nota_fiscal' => 'required',
            'data_emissao' => 'required|date',
            'valor_nota_fiscal' => 'required',
            'valor_desconto_nfe' => 'required',
            'id_filial' => 'required',
            'nfeProdutos' => [
                'nullable',
                'json',
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        return; // Se não há produtos, não valida
                    }

                    $items = json_decode($value, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail('O campo nfeProdutos deve conter um JSON válido.');
                        return;
                    }
                }
            ]
        ], [
            // MENSAGENS DE ERRO AQUI NO SEGUNDO PARÂMETRO
            'nome_empresa.required' => 'O campo Nome da Empresa é obrigatório.',
            'cnpj.required' => 'O campo CNPJ é obrigatório.',
            'numero_nota_fiscal.required' => 'O campo Número da Nota Fiscal é obrigatório.',
            'data_emissao.required' => 'O campo Data de Emissão é obrigatório.',
            'data_emissao.date' => 'O campo Data de Emissão deve ser uma data válida.',
            'valor_nota_fiscal.required' => 'O campo Valor da Nota Fiscal é obrigatório.',
            'valor_desconto_nfe.required' => 'O campo Valor do Desconto é obrigatório.',
            'id_filial.required' => 'O campo Filial é obrigatório.',
        ]);
    }

    public function processNfProdutos($validar, $id_nota_fiscal_entrada)
    {
        if (!isset($validar['nfeProdutos']) || empty($validar['nfeProdutos'])) {
            LOG::WARNING('nfeProdutos não encontrado ou vazio nos dados validados');
            return;
        }

        $items = json_decode($validar['nfeProdutos'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            LOG::ERROR('Erro ao decodificar JSON dos produtos: ' . json_last_error_msg());
            throw new \Exception('Erro ao processar dados dos produtos da NFe.');
        }

        if (empty($items) || !is_array($items)) {
            LOG::INFO('Nenhum produto encontrado para processar');
            return;
        }

        DB::transaction(function () use ($items, $id_nota_fiscal_entrada) {
            foreach ($items as $index => $item) {
                try {
                    // Valida se o item é um array
                    if (!is_array($item)) {
                        LOG::WARNING("Item #{$index} não é um array válido", $item);
                        continue;
                    }

                    $produtoData = [
                        'data_inclusao' => now(),
                        'id_nota_fiscal_entrada' => $id_nota_fiscal_entrada,
                        'cod_produto' => $item['codProduto'] ?? null,
                        'nome_produto' => $item['nomeProduto'] ?? null,
                        'ncm' => $item['ncm'] == '-' ? null : $item['ncm'],
                        'unidade' => $item['unidade'] == '-' ? null : $item['unidade'],
                        'quantidade_produtos' => sanitizeToDouble($item['quantidadeProdutos'] ?? 0),
                        'valor_unitario' => sanitizeToDouble($item['valorUnitario'] ?? 0),
                        'valor_total' => sanitizeToDouble($item['valorTotal'] ?? 0),
                        'valor_desconto' => sanitizeToDouble($item['valorDesconto'] ?? 0),
                    ];

                    if (empty($produtoData['cod_produto']) || empty($produtoData['nome_produto'])) {
                        LOG::WARNING("Item #{$index} com dados obrigatórios faltando", $item);
                        continue;
                    }

                    if ($produtoData['quantidade_produtos'] <= 0) {
                        LOG::WARNING("Item #{$index} com quantidade inválida: {$produtoData['quantidade_produtos']}");
                        continue;
                    }

                    NotaFiscalProdutos::create($produtoData);
                } catch (\Exception $e) {
                    LOG::ERROR("Erro ao processar item #{$index}: " . $e->getMessage(), $item);
                    throw $e; // Re-lança a exceção para rollback da transação
                }
            }
        });
    }

    public function buscarPedido(Request $request)
    {
        $pedido = PedidoCompra::where('id_pedido_compras', $request->idPedido)
            ->with(['itens.produtos.unidadeProduto', 'fornecedor.endereco.municipio'])
            ->first();

        if (!$pedido) {
            return response()->json(['success' => false, 'message' => 'Pedido não encontrado.'], 404);
        }

        $endereco = collect($pedido->fornecedor->endereco)->first();

        $dados = [
            'cnpj' => $pedido->fornecedor->cnpj ?? $pedido->fornecedor->cnpj_fornecedor,
            'id_fornecedor' => $pedido->fornecedor->id_fornecedor,
            'nome_empresa' => $pedido->fornecedor->nome_fornecedor,
            'endereco' => optional($endereco)->rua,
            'bairro' => optional($endereco)->bairro,
            'numero' => optional($endereco)->numero,
            'municipio' => optional(optional($endereco)->municipio)->nome_municipio,
            'uf' => optional(optional($endereco)->municipio)->uf,
            'cep' => optional($endereco)->cep,
            'itens' => collect($pedido->itens)->map(function ($iten) {
                return [
                    'id_itens_pedido'     => $iten->id_itens_pedidos ?? $iten->id_itens_pedido,
                    'data_inclusao'       => $iten->data_inclusao,
                    'data_alteracao'      => $iten->data_alteracao,
                    'cod_produto'         => $iten->cod_produto,
                    'nome_produto'        => optional($iten->produtos)->descricao_produto,
                    'ncm'                 => optional($iten->produtos)->ncm,
                    'unidade'             => optional($iten->produtos->unidade_produto ?? null)->descricao_unidade,
                    'quantidade_produtos' => $iten->quantidade_produtos,
                    'valor_unitario'      => $iten->valor_produto,
                    'valor_total'         => $iten->valor_total,
                    'valor_desconto'      => $iten->valor_desconto
                ];
            })->values(),
        ];

        return response()->json(['success' => true, 'dados' => $dados], 200);
    }

    public function onAtualizarEstoque(Request $request)
    {
        try {
            $idnotafiscal = $request->idNofiscalEntrada;

            if (!isset($idnotafiscal)) {
                return response()->json(['success' => false, 'message' => 'Número do pedido de compras nao informado'], 400);
            }

            $apuracao = NotaFiscalEntrada::where('id_nota_fiscal_entrada', $idnotafiscal)->first()->apuracao_saldo;

            $existe_pneu = DB::select("SELECT fc_verifica_produto_pneu(?)", [$idnotafiscal]);

            $existe_pneu = $existe_pneu[0]->fc_verifica_produto_pneu;

            $dados = DB::select("SELECT * FROM public.fc_verifica_quantidades_pneus(?)", [$idnotafiscal]);

            foreach ($dados as $item) {
                $comparacao = $item->comparacao;
                $marcado = $item->is_marcado;
            }


            if (!$apuracao && $existe_pneu == 1) {
                if ($comparacao == false) {
                    return response()->json(['success' => false, 'message' => 'Atenção: Existem pneus pendentes de marcação ou o saldo não foi apurado']);
                }

                if ($comparacao == true && $marcado == true) {
                    $this->atualizarValorMedio($idnotafiscal);
                    $this->atualizarQuantidadeProdutos($request);

                    // Aqui é onde fazemos a pergunta ao usuário
                    return response()->json([
                        'needs_confirmation' => true,
                        'message' => 'Pneus ainda não conferidos, deseja marcar como conferidos?',
                        'action' => 'marcar_conferidos',
                        'data' => [
                            'idnotafiscal' => $idnotafiscal,
                            'success_message' => 'Estoque e Valor Médio Atualizado.'
                        ]
                    ]);
                }

                $this->onHandleUserConfirmation($request);
            } elseif ($apuracao != true && $existe_pneu == 0) {
                $this->atualizarValorMedio($idnotafiscal);

                $retorno = $this->atualizarQuantidadeProdutos($request);

                if (!$retorno) {
                    return response()->json(['success' => false, 'message' => "Atenção: Produto não foi encontrado, o mesmo deve ser cadastrado antes de incluir esta nota fiscal"], 400);
                }

                return response()->json(['success' => true, 'message' => "Estoque e Valor Médio Atualizado."], 200);
            } else {
                return response()->json(['success' => true, 'message' => "Atualização já efetuada!", 'idNotaFiscalEntrada' => $idnotafiscal], 200);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar estoque: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno do servidor'], 500);
        }
    }

    // Novo método para lidar com a resposta do usuário
    public function onHandleUserConfirmation(Request $request)
    {
        try {
            $action = $request->input('action');
            $confirmed = $request->input('confirmed');
            $data = $request->input('data');

            if ($action === 'marcar_conferidos') {
                if ($confirmed) {
                    // Usuário confirmou - chamar processamento de pneus
                    return $this->onProcessarPneus($data['idnotafiscal']);
                } else {
                    // Usuário não confirmou - apenas retornar mensagem de sucesso da atualização anterior
                    return response()->json([
                        'success' => true,
                        'message' => $data['success_message']
                    ]);
                }
            } elseif ($action === 'conferir_pneu') {
                // Nova ação para confirmar pneus específicos
                return $this->handlePneuConfirmation($confirmed, $data);
            }

            return response()->json(['success' => false, 'message' => 'Ação não reconhecida'], 400);
        } catch (\Exception $e) {
            Log::error('Erro ao processar confirmação do usuário: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno do servidor'], 500);
        }
    }

    // user custom functions
    public static function ValidarDevolucaoParcial($idNota)
    {
        if (!empty($idNota)) {

            $new = NotaFiscalProdutos::where('id_nota_fiscal_entrada', $idNota)->where('qtde_devolucao', 'IS NOT', null)->first();

            if (!empty($new)) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function getNotaFiscalEntradaItens($id)
    {
        $itens = NotaFiscalProdutos::where('id_nota_fiscal_entrada', $id)
            ->with('notaFiscalEntrada')
            ->get();

        if (!$itens) {
            return response()->json(['success' => false, 'message' => 'Nota Fiscal Entrada não encontrada.'], 404);
        }

        return response()->json(['success' => true, 'nfItens' => $itens]);
    }

    public function checkNFGerada($numero_nf, $id_fornecedor)
    {

        try {
            if (!empty($numero_nf) && !empty($id_fornecedor)) {

                $jaFoiGerado = NotaFiscalEntrada::where('numero_nota_fiscal', $numero_nf)
                    ->where('id_fornecedor', $id_fornecedor)
                    ->exists();
            }

            log::debug(['jaFoiGerado ' => $jaFoiGerado ? 'foi' : 'não foi']);

            return $jaFoiGerado;
        } catch (\Exception $e) {
            LOG::ERROR('Erro ao buscar NF ', $e->getMessage());
        }
    }

    public function buscarFornecedor($idpedido)
    {
        $retorno = PedidoCompra::where('id_pedido_compras', $idpedido)
            ->with('fornecedor')
            ->select('id_fornecedor')
            ->first();

        return $retorno;
    }

    public function buscarItensPedido($idpedido, $nf)
    {
        log::debug(['idpedido ' => $idpedido]);
        log::debug(['nf ' => $nf]);
        $pedido = PedidoCompra::where('id_pedido_compras', $idpedido)
            ->with('solicitacaoCompra')
            ->first();

        if ($pedido) {
            $existPedido = $pedido->id_pedido_compras;
            $tipoPedido = $pedido->solicitacaoCompra->is_aplicacao_direta ?? true;
        }

        log::debug(['existPedido ' => $existPedido]);
        log::debug(['tipoPedido ' => $tipoPedido]);

        if (!empty($existPedido) && $tipoPedido) {

            $retorno = db::select("SELECT * FROM fc_incluir_produtos_nf(?, ?)", [$idpedido, $nf]);
            log::debug(['retorno ' => $retorno]);

            return $retorno;
        } elseif (!empty($existPedido) && !$tipoPedido) {
            return response()->json(['success' => false, 'message' => "Atenção: Um pedido de aplicação direta não pode ser usado para abastecimento de estoque."], 200);
        } else {
            return response()->json(['success' => false, 'message' => "Atenção: Pedido nao encontrado."], 404);
        }
    }

    public function atualizarValorMedio($idnotafiscal)
    {
        $data_final    =  date('Y-m-d');
        $usuario       =  intval(Auth::user()->id);
        $idfilial      =  intval(GetterFilial());

        $data_inicial = NotaFiscalEntrada::selectRaw('data_emissao::date')->where('id_nota_fiscal_entrada', $idnotafiscal)->first()->data_emissao;

        $retorno = db::select("SELECT * FROM fc_apuracao_saldo_estoque(?,?,?,?,?)", [$data_inicial, $data_final, $usuario, $idfilial, $idnotafiscal]);

        return $retorno;
    }

    public function atualizarQuantidadeProdutos(Request $request)
    {
        $produtos = NotaFiscalProdutos::where('id_nota_fiscal_entrada', $request->idNofiscalEntrada)->get();

        foreach ($produtos as $produto) {
            $cod_produtos = $produto->cod_produto;
            $quantidadeprodutos = $produto->quantidade_produtos;
            $nf_entrada = $produto->id_nota_fiscal_entrada;

            $this->incluirProduto($quantidadeprodutos, $cod_produtos, $nf_entrada);
        }
    }

    public function incluirProduto($quantidade, $id_produto, $nf_entrada)
    {
        $idfilial = GetterFilial();

        $quantidade_produto = intval(ProdutosPorFilial::where('id_produto_unitop', $id_produto)
            ->where('id_filial', $idfilial)->first()->quantidade_produto);

        $resultado = db::select("SELECT * FROM fc_atualizar_quantidade_produtos_nf(?,?,?)", [$id_produto, $quantidade, $idfilial]);
        $resultado = $resultado[0]->fc_atualizar_quantidade_produtos_nf;

        if ($resultado == 0) {
            return false;
        } else {
            if ($quantidade_produto == null) {
                $quantidade_produto = 0;
            }

            $saldototal = $quantidade_produto + $quantidade;

            if (isset($id_produto) && isset($idfilial) && isset($quantidade_produto) && isset($quantidade) && isset($nf_entrada) && isset($saldototal)) {
                try {
                    $histMovimentacaoEstoque = new HistoricoMovimentacaoEstoque();

                    $histMovimentacaoEstoque->data_inclusao = now();
                    $histMovimentacaoEstoque->id_produto    = $id_produto;
                    $histMovimentacaoEstoque->id_filial     = $idfilial;
                    $histMovimentacaoEstoque->qtde_estoque  = $quantidade_produto;
                    $histMovimentacaoEstoque->qtde_entrada  = $quantidade;
                    $histMovimentacaoEstoque->numero_nf     = $nf_entrada;
                    $histMovimentacaoEstoque->saldo_total   = $saldototal;
                    $histMovimentacaoEstoque->tipo          = 'ENTRADA DE MERCADORIA';

                    $histMovimentacaoEstoque->save();
                } catch (\Exception $e) {
                    Log::error('Erro ao incluir produto: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro ao incluir produto: ' . $e->getMessage()
                    ], 200);
                }
                return true;
            } else {
                return response()->json(['success' => false, 'Não foi possivel atualizar a quantidade dos produtos pois existe algum campo com valor nulo!']);
            }
        }
    }

    public static function executarProcessoFinal($idnotafiscal)
    {
        try {

            db::select("SELECT fc_atualiza_status_pneu(?)", [intval($idnotafiscal)]);
        } catch (\Exception $e) {
            log::error('error' . $e->getMessage());
        }
    }

    public function onProcessarPneus($idnotafiscal)
    {
        try {
            $objects = DB::select("select
                                        count(p.id_pneu) as qtd_modelo,
                                        m.descricao_modelo,
                                        osm.id_ordem_servico
                                    from
                                        ordem_servico_marcacao osm
                                    join pneu p on
                                        osm.id_pneu = p.id_pneu
                                    join modelopneu m on
                                        p.id_modelo_pneu = m.id_modelo_pneu
                                        and m.ativo is true
                                    where
                                        p.status_pneu = 'BORRACHARIA'
                                        and p.id_pneu in (
                                            select
                                                nfp.id_pneu
                                            from
                                                nota_fiscal_pneu nfp
                                            join nota_fiscal_produtos nfprod on
                                                nfprod.id_nota_fiscal_produtos = nfp.id_nota_fiscal_produtos
                                            where
                                                nfprod.id_nota_fiscal_entrada = ?
                                            )
                                        and osm.is_conferido is false
                                        and osm.is_marcado is true
                                        group by m.descricao_modelo, osm.id_ordem_servico", [$idnotafiscal]);

            if (!$objects || count($objects) === 0) {
                // Não há pneus para processar, finalizar
                $this->executarProcessoFinal($idnotafiscal);
                return response()->json([
                    'success' => true,
                    'message' => 'Processo finalizado com sucesso!'
                ]);
            }

            // Salvar todos os pneus na sessão para processar em sequência
            session(['pneus_pendentes' => $objects]);
            session(['idnotafiscal_atual' => $idnotafiscal]);
            session(['indice_pneu_atual' => 0]);


            // Processar o primeiro pneu
            return $this->processarProximoPneu();
        } catch (\Exception $e) {
            Log::error('Erro ao processar pneus: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao processar pneus'], 500);
        }
    }

    private function processarProximoPneu()
    {
        $pneusPendentes = session('pneus_pendentes', []);
        $indiceAtual = session('indice_pneu_atual', 0);
        $idnotafiscal = session('idnotafiscal_atual');

        if ($indiceAtual >= count($pneusPendentes)) {
            // Todos os pneus foram processados
            session()->forget(['pneus_pendentes', 'idnotafiscal_atual', 'indice_pneu_atual']);
            $this->executarProcessoFinal($idnotafiscal);

            return response()->json([
                'success' => true,
                'message' => 'Todos os pneus foram processados e o processo foi finalizado!',
                'idNotaFiscalEntrada' => $idnotafiscal
            ]);
        }

        $pneuAtual = $pneusPendentes[$indiceAtual];

        return response()->json([
            'needs_confirmation' => true,
            'message' => "O modelo {$pneuAtual->descricao_modelo} que possui {$pneuAtual->qtd_modelo} pneu(s), foi conferido e está marcado?",
            'action' => 'conferir_pneu',
            'data' => [
                'modelo' => $pneuAtual->descricao_modelo,
                'id_os' => $pneuAtual->id_ordem_servico,
                'qtd_modelo' => $pneuAtual->qtd_modelo,
                'idnotafiscal' => $idnotafiscal,
                'indice_atual' => $indiceAtual,
                'total_pneus' => count($pneusPendentes)
            ]
        ]);
    }

    private function handlePneuConfirmation($confirmed, $data)
    {
        try {
            if ($confirmed) {
                // Chamar método onYesConferido
                $this->onYesConferido($data);
            } else {
                // Chamar método onNoConferido
                $this->onNoConferido($data);
            }

            // Avançar para o próximo pneu
            $indiceAtual = session('indice_pneu_atual', 0);
            session(['indice_pneu_atual' => $indiceAtual + 1]);

            return $this->processarProximoPneu();
        } catch (\Exception $e) {
            Log::error('Erro ao confirmar pneu: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao processar confirmação do pneu'], 500);
        }
    }

    private function onYesConferido($data)
    {
        try {
            $idnotafiscal = $data['idnotafiscal'] ?? null;
            $id_modelo = $data['modelo'] ?? null;
            $id_os = $data['id_os'] ?? null;

            if ($id_os) {
                DB::table('ordem_servico_marcacao as osm')
                    ->join('pneu as p', 'p.id_pneu', '=', 'osm.id_pneu')
                    ->join('modelopneu as mp', 'mp.id_modelo_pneu', '=', 'p.id_modelo_pneu')
                    ->where('mp.descricao_modelo', 'ilike', '%' . $id_modelo . '%')
                    ->where('osm.id_nota_fiscal_entrada', $idnotafiscal)
                    ->update([
                        'osm.is_conferido' => true,
                        'osm.data_alteracao' => now(),
                        'osm.conferido_por' => Auth::user()->id
                    ]);
            }
        } catch (\Exception $e) {
            log::error('Erro ao atualizar ordem de serviço' . $e->getMessage());
        }
    }

    private function onNoConferido($data)
    {
        // Implementar lógica quando usuário confirma que o pneu NÃO foi conferido
        Log::info("Pneu NÃO conferido - Modelo: {$data['modelo']}, OS: {$data['id_os']}, Qtd: {$data['qtd_modelo']}");

        // Aqui você colocaria a lógica do método original onNoConferido
        // Por exemplo: manter status atual, registrar pendência, etc.
    }

    public static function onCarregarDados(Request $request)
    {
        try {
            $chave_nf_entrada = $request->chaveNFe;

            if (isset($chave_nf_entrada)) {
                $objects = db::select("SELECT 
                    ff.id_fornecedor,
                    nfe.xnome,
                    nfe.cnpj,
                    nfe.xlgr,
                    nfe.xbairro,
                    nfe.nro,
                    nfe.cmun,
                    nfe.xmun,
                    mc.id_municipio,
                    mc.nome_municipio,
                    st.id_uf,
                    nfe.uf,
                    nfe.cep,
                    nf.nnf,
                    nf.cnf,
                    nf.natop,
                    nf.dhemi,
                    nf.dhsaient,
                    nf.vnf,
                    nf.vdesc,
                    nf.vfrete
                FROM nfe_core nf
                    LEFT JOIN nfe_emissor nfe ON nf.id = nfe.id_nfe
                    LEFT JOIN municipio mc ON mc.cod_ibge = nfe.cmun
                    LEFT JOIN estado st ON st.uf @@ nfe.uf
                    LEFT JOIN 
                    (
                        SELECT 
                            ROW_NUMBER() OVER(PARTITION BY REPLACE('/','',(REPLACE('.','',(REPLACE('-','',(REPLACE(' ','',f.cnpj_fornecedor)))))))) AS number_,
                            REPLACE((REPLACE( (REPLACE((REPLACE(f.cnpj_fornecedor,' ', '')),'-', '')),'.', '')),'/', '') cnpj_,
                            f.*
                        FROM fornecedor f 
                        WHERE f.cnpj_fornecedor IS NOT NULL 
                    ) ff ON ff.cnpj_ = REPLACE((REPLACE( (REPLACE((REPLACE(nfe.cnpj,' ', '')),'-', '')),'.', '')),'/', '')
                WHERE infnfe = ?", [$chave_nf_entrada]);

                if ($objects) {
                    foreach ($objects as $object) {
                        $dados = [
                            'id_fornecedor'  => $object->id_fornecedor,
                            'xnome'          => $object->xnome,
                            'cnpj'           => $object->cnpj,
                            'xlgr'           => $object->xlgr,
                            'xbairro'        => $object->xbairro,
                            'nro'            => $object->nro,
                            'xmun'           => $object->xmun,
                            'cmun'           => $object->cmun,
                            'nome_municipio' => $object->nome_municipio,
                            'id_uf'          => $object->id_uf,
                            'uf'             => $object->uf,
                            'cep'            => $object->cep,
                            'nnf'            => $object->nnf,
                            'cnf'            => $object->cnf,
                            'natop'          => $object->natop,
                            'dhemi'          => $object->dhemi,
                            'dhsaient'       => $object->dhsaient,
                            'vnf'            => $object->vnf,
                            'vdesc'          => $object->vdesc,
                            'vfrete'         => $object->vfrete,
                        ];
                    }
                }

                return response()->json(['success' => true, 'dados' => $dados], 200);
            }
        } catch (\Exception $e) {
            log::error('erro ao carregar dados da NFe' . $e->getMessage());
        }
    }

    public function indexGerarNumFogo($id)
    {
        $nfProdutos = NotaFiscalProdutos::where('id_nota_fiscal_entrada', $id)
            ->with(['produto.modeloPneu', 'nfPneus'])
            ->get()
            ->map(function ($item) {
                if ($item->nfPneus && $item->nfPneus->count() == 0) {
                    return [
                        'id_nota_fiscal_produtos' => $item->id_nota_fiscal_produtos,
                        'nome_produto' => $item->nome_produto,
                        'value' => $item->produto?->modeloPneu?->id_modelo_pneu,
                        'label' => $item->produto?->modeloPneu?->descricao_modelo ?? $item->nome_produto,
                    ];
                }
                return null;
            })
            ->filter();


        return view('admin.notafiscalentrada._gerarNumFogo', compact('nfProdutos', 'id'));
    }

    public static function onLancarNumFogoPneus(Request $request, $id)
    {
        try {
            $cod_nf_produto = $request->id_nota_fiscal_produtos;
            $modelopneu     = $request->modelo_pneu;
            $retorno        = NULL;

            if (isset($cod_nf_produto) && isset($modelopneu)) {

                $objects = db::select("SELECT * FROM fc_gerar_num_fogo(?, ?)", [$cod_nf_produto, $modelopneu]);

                if ($objects) {
                    foreach ($objects as $object) {
                        $retorno = $object->fc_gerar_num_fogo;
                    }
                }

                if ($retorno == 1) {

                    return redirect()->route('admin.notafiscalentrada.edit', ['notafiscalentrada' => $id])->with('success', "Números de fogo gerados com sucesso.");
                } else {
                    return redirect()->back()->with('error', "Não foi possível gerar os números de fogo.");
                }
            } else {

                return redirect()->back()->with('error', "Verifique se algum dos campos ficou vazio.");
            }
        } catch (\Exception $e) {
            log::error('Erro ao gerar número de fogo ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao gerar número de fogo ' . $e->getMessage());
        }
    }

    public static function onImprimirNumFogoGerado(Request $request)
    {
        try {
            $idnotafiscalproduto = $request->produtoID;

            $parametros = array('P_id_nota_fiscal' => $idnotafiscalproduto);

            $name  = 'relatorio_pneus';
            $agora = date('d-m-YH:i');
            $tipo  = '.pdf';
            $relatorio = $name . $agora . $tipo;
            $barra = '/';
            $partes     = parse_url('http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
            $host       = $partes['host'] . PHP_EOL;
            $pathrel    = (explode('.', $host));
            $dominio    = $pathrel[0];

            if ($dominio == '127' || $dominio == 'localhost' || strpos($host, '127.0.0.1') !== false) {
                $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
                $pastarelatorio = '/reports/homologacao/' . $name;
                $imprime = 'homologacao';

                Log::info('Usando servidor de homologação');
            } elseif ($dominio == 'lcarvalima') {
                $jasperserver = 'http://10.10.1.8:8080/jasperserver';
                $input = '/reports/carvalima/' . $name;

                // Verificar se o diretório existe antes de tentar chmod
                if (is_dir($input)) {
                    chmod($input, 0777);
                    Log::info('Permissões do diretório alteradas: ' . $input);
                } else {
                    Log::warning('Diretório não encontrado: ' . $input);
                }

                $pastarelatorio = $input;
                $imprime = $dominio;

                Log::info('Usando servidor de produção');
            } else {
                $jasperserver = 'http://10.10.1.8:8080/jasperserver';
                $input = '/reports/' . $dominio . '/' . $name;

                // Verificar se o diretório existe antes de tentar chmod
                if (is_dir($input)) {
                    chmod($input, 0777);
                    Log::info('Permissões do diretório alteradas: ' . $input);
                } else {
                    Log::warning('Diretório não encontrado: ' . $input);
                }

                $pastarelatorio = $input;
                $imprime = $dominio;

                Log::info('Usando servidor de produção');
            }

            $jsi = new jasperserverintegration(
                $jasperserver,
                $pastarelatorio, // Report Unit Path
                'pdf',           // Tipo da exportação do relatório
                'unitop',        // Usuário com acesso ao relatório
                'unitop2022',    // Senha do usuário
                $parametros      // Conteudo do Array
            );

            $data = $jsi->execute();

            try {
                return response($data, 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="' . $relatorio . '"');
            } catch (\Exception $e) {
                LOG::ERROR('error', $e->getMessage());
            }
        } catch (\Exception $e) {
            log::error('Erro ao gerar relatório de Número de Fogo ' . $e->getMessage());
        }
    }

    public function getProdutos($id, $devolucao = false)
    {
        return NotaFiscalProdutos::where('id_nota_fiscal_entrada', $id)
            ->with(['nfPneus'])
            ->get()
            ->map(function ($item) use ($devolucao) {
                return [
                    'id_nota_fiscal_produtos' => $item->id_nota_fiscal_produtos,
                    'id_nota_fiscal_entrada' => $item->id_nota_fiscal_entrada,
                    'cod_produto' => $item->cod_produto,
                    'nome_produto' => $item->nome_produto,
                    'ncm' => $item->ncm,
                    'unidade' => $item->unidade,
                    'quantidade_produtos' => $item->quantidade_produtos,
                    'valor_unitario_formatado' => $item->valor_unitario_formatado,
                    'valor_total_formatado' => $item->valor_total_formatado,
                    'valor_unitario_desconto_formatado' => $item->valor_unitario_desconto_formatado,
                    'data_inclusao' => $item->data_inclusao,
                    'data_alteracao' => $item->data_alteracao,
                    'has_pneus' => $item->nfPneus->count() > 0,
                    'is_pneu' => strpos(strtolower($item->nome_produto), 'pneu') !== false,
                    'is_devolucao' => $devolucao,
                    'qtde_devolucao' => $item->qtde_devolucao
                ];
            });
    }

    public function devolucao($id)
    {
        $nfEntrada = NotaFiscalEntrada::findOrFail($id);

        $nfeProdutos = $this->getProdutos($id, true);

        $municipios = Municipio::select('id_municipio as value', 'nome_municipio as label')->orderBy('nome_municipio')->get();
        $estados = Estado::select('id_uf as value', 'uf as label')->orderBy('nome')->get();
        $filial = Filial::select('id as value', 'name as label')->orderBy('name')->get();

        return view('admin.notafiscalentrada.edit_dev', compact('nfEntrada', 'nfeProdutos', 'estados', 'filial', 'municipios'));
    }

    public function devolve(Request $request, $id)
    {
        try {
            $idnotafiscal = $request->id_nota_fiscal_entrada;
            if (isset($idnotafiscal)) {
                $this->atualizarQuantidadeProdutosDevolucao($request);
                return redirect()->route('admin.notafiscalentrada.index')->with('success', "Estoque Atualizado.");
            }
        } catch (\Exception $e) {
            LOG::ERROR('Erro ao atualizar estoque ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar estoque ' . $e->getMessage());
        }
    }

    public function atualizarQuantidadeProdutosDevolucao($request)
    {
        $nfEntrada      = $request->id_nota_fiscal_entrada;
        $numeroNf       = $request->numero_nota_fiscal;
        $idFilial       = $request->id_filial;

        $itens = json_decode($request->nfeProdutos, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            LOG::ERROR('Erro ao decodificar JSON dos produtos: ' . json_last_error_msg());
            throw new \Exception('Erro ao processar dados dos produtos da NFe.');
        }

        if (empty($itens) || !is_array($itens)) {
            LOG::INFO('Nenhum produto encontrado para processar');
            return;
        }

        if (isset($itens)) {
            foreach ($itens as $item) {
                log::debug($item);
                $codProdutos    = $item['codProduto'];
                $qtdeProdutos   = $item['qtdeDevolvida'];

                $this->incluirDevolucaoProduto($qtdeProdutos, $codProdutos, $nfEntrada, $numeroNf, $idFilial);
            }
        }
    }

    public function incluirDevolucaoProduto($quantidade, $idProduto, $nfEntrada, $numeroNf, $idFilial)
    {
        if (isset($quantidade) && isset($idProduto) && isset($nfEntrada) && isset($numeroNf)) {
            $quantidadeBaixa = HistoricoMovimentacaoEstoque::where('id_nf_entrada', $nfEntrada)
                ->where('id_produto', $idProduto)
                ->sum('qtde_baixa');

            if ($quantidade > $quantidadeBaixa) {
                $quantidadeTotal = $quantidade - $quantidadeBaixa;

                try {

                    db::beginTransaction();
                    $estoque = ProdutosPorFilial::where('id_produto_unitop', $idProduto)->where('id_filial', $idFilial)->first();

                    $estoque->data_alteracao = now();
                    $estoque->quantidade_produto = $estoque->quantidade_produto ?? 0 - $quantidadeTotal;

                    $estoque->save();

                    $queryMovimentacao =
                        "INSERT INTO historico_movimentacao_estoque 
                        (
                            data_inclusao,
                            id_produto,
                            id_filial,
                            qtde_estoque,
                            qtde_baixa,
                            saldo_total,
                            numero_nf,
                            tipo,
                            id_nf_entrada
                        )
                        SELECT DISTINCT 
                            CURRENT_TIMESTAMP,
                            pr.cod_produto,
                            v.id_filial,
                            ppf.quantidade_produto,
                            $quantidadeTotal,
                            (
                                ppf.quantidade_produto - $quantidadeTotal 
                            ),
                            v.numero_nota_fiscal,
                            'DEVOLUÇÃO PARCIAL DE NOTA FISCAL',
                            v.id_nota_fiscal_entrada    
                        FROM nota_fiscal_entrada v
                        JOIN nota_fiscal_produtos pr ON pr.id_nota_fiscal_entrada = v.id_nota_fiscal_entrada
                        JOIN produtos_por_filial ppf ON ppf.id_produto_unitop = pr.cod_produto AND ppf.id_filial = v.id_filial
                        WHERE pr.id_nota_fiscal_entrada = ?
                        AND pr.cod_produto = ?
                        LIMIT 1";

                    db::select($queryMovimentacao, [$nfEntrada, $idProduto]);
                    db::commit();

                    return true;
                } catch (\Exception $e) {
                    db::rollBack();
                    LOG::ERROR('Erro ao atualizar estoque ' . $e->getMessage());
                }
            }
        }
    }
}

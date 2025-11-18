<?php

namespace App\Modules\Estoque\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Departamento;
use App\Modules\Estoque\Models\HistoricoMovimentacaoEstoque;
use App\Modules\Compras\Models\ItemCompra;
use App\Modules\Manutencao\Models\OrdemServico;
use App\Modules\Manutencao\Models\OrdemServicoPecas;
use App\Modules\Imobilizados\Models\OrdemServicoPecasImobilizados;
use App\Models\Produto;
use App\Models\ProdutosPorFilial;
use App\Modules\Compras\Models\ProdutosSolicitacoes;
use App\Modules\Compras\Models\RelacaoSolicitacaoPeca;
use App\Modules\Estoque\Models\TransferenciaEstoqueAux;
use App\Modules\Configuracoes\Models\User;
use App\Models\Veiculo;
use App\Models\VFilial;
use App\Models\VRequisicaoProdutoOs;
use App\Traits\JasperServerIntegration;
use App\Traits\SanitizesMonetaryValues;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaidaProdutosEstoqueController extends Controller
{
    use SanitizesMonetaryValues;

    public function index(Request $request)
    {
        $relacaoPecas = $this->getRelacaoPecas();
        $relacaoMats = $this->getRelacaoMats();
        $relacaoTransf = $this->getRelacaoTransf();

        $selectOptions = $this->getSelectOptions();

        return view('admin.saidaprodutosestoque.index', array_merge([
            'relacaoPecas' => $relacaoPecas,
            'relacaoMats' => $relacaoMats,
            'relacaoTransf' => $relacaoTransf,
        ], $selectOptions));
    }

    public function edit($id_solicitacao_pecas)
    {
        $requisicao = VRequisicaoProdutoOs::where('id_solicitacao_pecas', $id_solicitacao_pecas)
            ->firstOrFail();

        if ($requisicao->is_transferencia == true) {
            $itens = ProdutosSolicitacoes::with(['produto.unidadeProduto', 'produto.produtoPorFilial'])
                ->where('id_relacao_solicitacoes', $id_solicitacao_pecas)
                ->where(function ($q) {
                    $q->where('situacao_pecas', '=', 'TRANSFERENCIA');
                })
                ->get();
        } else {
            $itens = ProdutosSolicitacoes::with(['produto.unidadeProduto', 'produto.produtoPorFilial'])
                ->where('id_relacao_solicitacoes', $id_solicitacao_pecas)
                ->where(function ($q) {
                    $q->where('situacao_pecas', '!=', 'TRANSFERENCIA')
                        ->orWhereNull('situacao_pecas');
                })
                ->get();
        }

        foreach ($itens as $item) {
            if ($item->produto && $item->produto->id_produto) {
                // Busca estoque na filial do usuário
                $produtoFilial = ProdutosPorFilial::where('id_produto_unitop', $item->produto->id_produto)
                    ->where('id_filial', GetterFilial())
                    ->first();


                $item->quantidade_estoque_filial = $produtoFilial->quantidade_produto ?? 0;
                $item->quantidade_transferencia = $produtoFilial->quantidade_transferencia ?? 0;
                $item->localizacao_filial       = $produtoFilial->localizacao ?? null;
            } else {
                $item->quantidade_estoque_filial = 0;
                $item->quantidade_transferencia  = 0;
                $item->localizacao_filial        = null;
            }
        }

        return view(
            'admin.saidaprodutosestoque.edit',
            compact('requisicao', 'itens', 'id_solicitacao_pecas')
        );
    }

    public function editTransferencia($id_solicitacao_pecas)
    {
        $requisicao = VRequisicaoProdutoOs::where('id_solicitacao_pecas', $id_solicitacao_pecas)
            ->firstOrFail();

        if ($requisicao->is_transferencia == true) {
            $itens = ProdutosSolicitacoes::with(['produto.unidadeProduto', 'produto.produtoPorFilial'])
                ->where('id_relacao_solicitacoes', $id_solicitacao_pecas)
                ->where(function ($q) {
                    $q->where('situacao_pecas', '=', 'TRANSFERENCIA');
                })
                ->get();
        } else {
            $itens = ProdutosSolicitacoes::with(['produto.unidadeProduto', 'produto.produtoPorFilial'])
                ->where('id_relacao_solicitacoes', $id_solicitacao_pecas)
                ->where(function ($q) {
                    $q->where('situacao_pecas', '!=', 'TRANSFERENCIA')
                        ->orWhereNull('situacao_pecas');
                })
                ->get();
        }

        foreach ($itens as $item) {
            if ($item->produto && $item->produto->id_produto) {
                // Busca estoque na filial do usuário
                $produtoFilial = ProdutosPorFilial::where('id_produto_unitop', $item->produto->id_produto)
                    ->where('id_filial', GetterFilial())
                    ->first();


                $item->quantidade_estoque_filial = $produtoFilial->quantidade_produto ?? 0;
                $item->quantidade_transferencia = $produtoFilial->quantidade_transferencia ?? 0;
                $item->localizacao_filial       = $produtoFilial->localizacao ?? null;
            } else {
                $item->quantidade_estoque_filial = 0;
                $item->quantidade_transferencia  = 0;
                $item->localizacao_filial        = null;
            }
        }

        return view(
            'admin.saidaprodutosestoque.edit_transferencia',
            compact('requisicao', 'itens', 'id_solicitacao_pecas')
        );
    }

    private function getRelacaoPecas()
    {
        $query = RelacaoSolicitacaoPeca::query()
            ->where(function ($q) {
                $q->where('requisicao_pneu', false)
                    ->orWhereNull('requisicao_pneu');
            })
            ->whereNotNull('id_orderm_servico')
            ->where('id_filial', '=', GetterFilial())
            ->with(['departamentoPecas', 'ordemServico']);

        return $query->latest('data_inclusao')->paginate(10);
    }

    private function getRelacaoMats()
    {
        $query = RelacaoSolicitacaoPeca::query()
            ->with(['transferenciaEstoqueAux.filialSolicitante', 'veiculo'])
            ->whereNotIn('situacao', [
                'AGUARDANDO APROVAÇÃO',
                'AGUARDANDO TRANSFERÊNCIA',
                'FINALIZADA',
                'ESTORNO DE TRANSFERENCIA',
                'ESTORNO PARCIAL',
                'TRANSFERIDO PARCIALMENTE',
                'RECEBIMENTO CONFIRMADO',
                'TRANSFERIDO'
            ])
            ->where('situacao', 'not like', 'REPROVADO')
            ->whereNull('id_orderm_servico')
            ->where('id_filial', '=', GetterFilial())
            ->where('requisicao_pneu', false);

        return $query->latest('data_inclusao')->paginate(10);
    }

    private function getRelacaoTransf()
    {
        $query = RelacaoSolicitacaoPeca::query()
            ->whereIn('situacao', [
                'AGUARDANDO TRANSFERÊNCIA',
                'RECEBIMENTO CONFIRMADO',
                'ESTORNO DE TRANSFERENCIA',
                'ESTORNO PARCIAL',
                'TRANSFERIDO PARCIALMENTE',
                'TRANSFERIDO'
            ])
            ->where('situacao', '!=', 'REPROVADO')
            ->whereNull('id_orderm_servico')
            ->where('requisicao_pneu', false)
            ->whereHas('produtosSolicitacoes', function ($q) {
                $q->where('filial_transferencia', '=', GetterFilial());
            });

        return $query->latest('data_inclusao')->paginate(10);
    }

    private function getSelectOptions()
    {
        return [
            'veiculos' => $this->getVeiculos(),
            'filiais' => $this->getFiliais(),
            'usuarioSolicitante' => $this->getUsuarioSolicitante(),
            'departamentos' => $this->getDepartamentos(),
        ];
    }

    private function getVeiculos()
    {
        return Cache::remember('veiculos_frequentes', now()->addHours(12), function () {
            return Veiculo::where('situacao_veiculo', true)
                ->limit(20)
                ->get([
                    'id_veiculo as value',
                    'placa as label',
                ]);
        });
    }

    private function getFiliais()
    {
        return VFilial::select('id as value', 'name as label')
            ->orderBy('name')
            ->get();
    }

    private function getUsuarioSolicitante()
    {
        return User::select('id as value', 'name as label')
            ->where('is_superuser', false)
            ->orderBy('name')
            ->get();
    }

    private function getDepartamentos()
    {
        return Departamento::select('id_departamento as value', 'descricao_departamento as label')
            ->where('ativo', true)
            ->orderBy('descricao_departamento')
            ->get();
    }

    public function onVizualizar($idrequisicao)
    {
        try {
            $imobilizado = 'NÃO';

            $imobilizado = $this->getImobilizadoDetails($idrequisicao);

            if ($imobilizado == 'NÃO') {
                $items = ProdutosSolicitacoes::with(['produto.unidadeProduto', 'produto.produtoPorFilial'])
                    ->where('id_relacao_solicitacoes', $idrequisicao)->get();

                // Obter filial do usuário
                $filialUsuario = GetterFilial();

                // Adicionar dados da filial a cada item
                foreach ($items as $item) {
                    // Buscar quantidade na filial do usuário se houver produto relacionado
                    if ($item->produto && $item->produto->id_produto) {
                        $produtoFilial = DB::connection('pgsql')->table('produtos_por_filial')
                            ->where('id_produto_unitop', $item->produto->id_produto)
                            ->where('id_filial', $filialUsuario)
                            ->first();

                        if ($produtoFilial) {
                            $item->quantidade_estoque_filial = $produtoFilial->quantidade_produto;
                            $item->localizacao_filial = $produtoFilial->localizacao;
                        } else {
                            $item->quantidade_estoque_filial = 0;
                            $item->localizacao_filial = null;
                        }
                    } else {
                        $item->quantidade_estoque_filial = 0;
                        $item->localizacao_filial = null;
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'items' => $items,
                    'filial_usuario' => $filialUsuario,
                ]);
            } else {
                return response()->json([
                    'status' => 'redirect',
                    'imobilizado' => $imobilizado,
                    'message' => 'Redirecionamento necessário para imobilizados',
                    'redirect_data' => ['id_relacao' => $idrequisicao],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro no método onVizualizar: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function onImprimir(string $id)
    {
        try {
            $svgPath = public_path('images/logo_carvalima.svg');
            $svgContent = file_get_contents($svgPath);
            $base64Svg = base64_encode($svgContent);

            if (! empty($id) || $id != null) {
                $parametros = ['P_requisicao' => $id];

                $name = 'Baixa_Produtos';
                $agora = date('d-m-YH:i');
                $tipo = '.pdf';
                $relatorio = $name . $agora . $tipo;
                $barra = '/';
                $partes = parse_url('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
                $host = $partes['host'] . PHP_EOL;
                $pathrel = (explode('.', $host));
                $dominio = $pathrel[0];

                if ($dominio == 127) {
                    $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
                    $pastarelatorio = '/reports/homologacao/' . $name;
                    $imprime = 'homologacao';
                } elseif ($dominio != 127) {
                    $jasperserver = 'http://10.10.1.8:8080/jasperserver';
                    $input = '/reports/' . $dominio . '/' . $name;
                    chmod($input, 0777);
                    $pastarelatorio = $input;
                    $imprime = $dominio;
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
                    Log::error('Erro ao gerar PDF (onImprimir):', ['message' => $e->getMessage()]);
                }
            }
        } catch (\Exception $e) {
            Log::info('Erro ao gerar PDF (onImprimir) inicio do catch:', ['message' => $e->getMessage()]);
        }
    }

    public function onImprimirPecas(string $id)
    {
        try {
            $svgPath = public_path('images/logo_carvalima.svg');
            $svgContent = file_get_contents($svgPath);
            $base64Svg = base64_encode($svgContent);
            $valor = Auth::user()->name;

            if (! empty($id) || $id != null) {
                $parametros = [
                    'P_requisicao' => $id,
                    'P_name' => $valor,
                ];

                $name = 'Baixa_Produtos';
                $agora = date('d-m-YH:i');
                $tipo = '.pdf';
                $relatorio = $name . $agora . $tipo;
                $barra = '/';
                $partes = parse_url('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
                $host = $partes['host'] . PHP_EOL;
                $pathrel = (explode('.', $host));
                $dominio = $pathrel[0];

                if ($dominio == '127' || $dominio == 'localhost' || strpos($host, '127.0.0.1') !== false) {
                    $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
                    $pastarelatorio = '/reports/homologacao/' . $name;
                    $imprime = 'homologacao';
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
                    $imprime = $dominio;
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
                    $imprime = $dominio;
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
                    Log::error('Erro ao gerar PDF (onImprimirPecas):', ['message' => $e->getMessage()]);
                }
            }
        } catch (\Exception $e) {
            Log::info('Erro no onImprimirPecas:', ['message' => $e->getMessage()]);
        }
    }

    public function getImobilizadoDetails($idrequisicao)
    {
        try {
            $query = "SELECT va.imobilizado FROM v_requisicao_produto_os AS va
                    WHERE va.id_solicitacao_pecas = ?
                    AND va.data_inclusao::DATE >= '2024-01-01'
                    AND va.situacao != 'FINALIZADA'";

            $objects = DB::connection('pgsql')->select($query, [$idrequisicao]);

            if ($objects) {
                foreach ($objects as $object) {
                    $imobilizado = $object->imobilizado;
                }
            }

            return $imobilizado;
        } catch (\Exception $e) {
            Log::error('Erro ao obter detalhes do imobilizado: ' . $e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Erro ao obter detalhes do imobilizado'], 500);
        }
    }

    public function onBaixarEstoque($idrequisicao)
    {
        try {
            $imobilizado = 'NÃO';

            $imobilizado = $this->getImobilizadoDetails($idrequisicao);

            if ($imobilizado == 'NÃO') {
                $pageParam = ['key' => $idrequisicao];
                // TApplication::loadPage('RelacaosolicitacoespecasBaixarEstoqueFormNova', 'onEdit', $pageParam);
            } else {
                $pageParam = ['key' => $idrequisicao];
                // TApplication::loadPage('BaixaRelacaoImobilizadosNewForm', 'onEdit', $pageParam);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao baixar estoque:', ['message' => $e->getMessage()]);
        }
    }

    public function onEditBatchArray(Request $request)
    {
        // Captura os IDs da query string
        $idsString = $request->get('ids');
        $confirmAction = $request->get('confirmed', false);

        // Converte string em array
        $ids = explode(',', $idsString);

        if (isset($confirmAction) && $confirmAction == true) {
            try {
                foreach ($ids as $check_id) {
                    if (self::validarRequisicaoSolicitada($check_id)) {
                        $id_solicitacoes_solicitadas[] = $check_id;
                    } elseif (! self::validarRequisicaoSolicitada($check_id)) {

                        $id_solicitacoes[] = $check_id;
                    }
                }

                if (! empty($id_solicitacoes_solicitadas)) {
                    $solicitacoes_solicitadas = implode(',', $id_solicitacoes_solicitadas);

                    return response()->json([
                        'warning',
                        "As seguintes requisições já foram solicitadas e foram filtradas da Unificação: $solicitacoes_solicitadas",
                    ]);
                }

                if (! empty($id_solicitacoes)) {
                    $pageParam = ['id_solicitacao_array' => $id_solicitacoes];

                    // necessita desenvolvimento
                    // TApplication::loadPage('TransferenciaProdutossolicitacoesUnificadoList', 'onShow', $pageParam);
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Peças unificadas com Xucesso!',
                        'ids' => $id_solicitacoes,
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json('error', $e->getMessage());
            }
        }
    }

    public static function validarRequisicaoSolicitada($idRequisicao)
    {

        $retorno = null;

        $query = 'SELECT DISTINCT p.id_relacao_solicitacoes_novo
                                FROM transferencia_estoque_aux p
                                WHERE p.id_relacao_solicitacoes_antigo = ?';

        $objects = DB::connection('pgsql')->select($query, [$idRequisicao]);

        if ($objects) {
            foreach ($objects as $object) {
                $retorno = ! empty($object->id_relacao_solicitacoes_novo) ? true : false;
            }
        }

        return $retorno;
    }

    public function onAssumir($id)
    {
        try {
            $iduser = Auth::user()->id;

            $result = RelacaoSolicitacaoPeca::findorFail($id);

            $result->data_alteracao = now();
            $result->id_usuario_estoque = $iduser;
            $result->situacao = 'INICIADA';

            $result->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Requisição assumida com sucesso!',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao assumir requisição:', ['message' => $e->getMessage()]);
        }
    }

    private function isTransferencia(RelacaoSolicitacaoPeca $requisicao): bool
    {
        return $requisicao->tipo === 'TRANSFERENCIA'; // ajuste conforme sua tabela
    }

    private function atualizarOrdemServico($requisicao, $idProduto, $qtdNovaBaixa, $saldo, $idFilial)
    {
        $idOrdemServico = $requisicao->id_orderm_servico;

        HistoricoMovimentacaoEstoque::create([
            'data_inclusao' => now(),
            'id_produto' => $idProduto,
            'id_filial' => $idFilial,
            'qtde_estoque' => $requisicao->quantidade ?? 0,
            'qtde_baixa' => $qtdNovaBaixa,
            'id_ordem_servico' => $idOrdemServico,
            'saldo_total' => $saldo,
            'tipo' => 'BAIXA DE OS',
        ]);

        // Atualizar ordem_servico_pecas e ordem_servico aqui conforme suas regras
        OrdemServicoPecas::where('id_ordem_servico', $idOrdemServico)
            ->where('id_produto', $idProduto)
            ->update([
                'situacao_pecas' => 'BAIXADA',
                'data_recebimento' => now(),
            ]);

        OrdemServico::where('id_ordem_servico', $idOrdemServico)
            ->update(['id_status_ordem_servico' => 2]);

        // Se for imobilizado → atualizar tabela de imobilizados também
        if ($requisicao->is_requisicao_os_imobilizado) {
            OrdemServicoPecasImobilizados::where('id_manutencao_imobilizado', $idOrdemServico)
                ->where('id_produto', $idProduto)
                ->update(['situacao_pecas' => 'BAIXADA']);
        }
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

    public function finalizarBaixa(Request $request)
    {


        $request->validate([
            'id_solicitacao_pecas' => 'required|integer',
            'justificativa_de_finalizacao' => 'nullable|string',
            'id_ordem_servico' => 'nullable|integer',
            'requisicao_ti' => 'nullable|boolean',
            'produtos_solicitacoes' => 'nullable|array',
            'situacao' => 'nullable|string',
        ]);

        $observacao = $request->input('justificativa_de_finalizacao');
        $idSolicitacao = $request->input('id_solicitacao_pecas');
        $idOrdemServico = $request->input('id_ordem_servico');
        $requisicaoTi = $request->boolean('requisicao_ti');
        $idProdutosSolicitacoes = $request->input('produtos_solicitacoes', []);
        $userId = Auth::id();

        $todosBaixados = DB::table('produtossolicitacoes')
            ->where('id_relacao_solicitacoes', $idSolicitacao)
            ->where('situacao_pecas', '!=', 'COMPRAS')
            ->where('situacao_pecas', '!=', 'TRANSFERENCIA')
            ->where('situacao_pecas', '!=', 'EM SOLICITACAO')
            ->where('situacao_pecas', '!=', 'INATIVO')
            ->whereRaw('COALESCE(quantidade_baixa,0) < quantidade')
            ->exists();

        if ($todosBaixados) {
            return redirect()->back()
                ->with('error', "Não é possível finalizar. Ainda existem produtos que não foram totalmente baixados.");
        }

        $statusPermitidos = ['EM BAIXA PARCIAL', 'RECUSADO NA MATRIZ'];
        $cargosLista = [1, 5];
        $produtosNaoTransferidos = [];

        try {
            DB::beginTransaction();

            // Recupera cargo do usuário
            $cargo = DB::table('usuario_deparmanto')
                ->where('id_user', $userId)
                ->value('id_cargo');

            // Verifica se há produtos pendentes
            $pendentes = DB::table('produtossolicitacoes')
                ->where('id_relacao_solicitacoes', $idSolicitacao)
                ->where('situacao_pecas', '!=', 'COMPRAS')
                ->where('situacao_pecas', '!=', 'EM SOLICITACAO')
                ->where('situacao_pecas', '!=', 'TRANSFERENCIA')
                ->where('situacao_pecas', '!=', 'INATIVO')
                ->whereNull('quantidade_baixa')
                ->exists();

            if (!empty($idProdutosSolicitacoes)) {
                // Recupera produtos já transferidos
                $produtosTransferidos = DB::table('transferencia_estoque_aux_envio')
                    ->where('id_relacao_solicitacoes_novo', $idSolicitacao)
                    ->whereIn('id_produtos_solicitacoes', $idProdutosSolicitacoes)
                    ->pluck('id_produtos_solicitacoes')
                    ->toArray();

                // Produtos ainda não transferidos
                foreach ($idProdutosSolicitacoes as $idProduto) {
                    if (!in_array($idProduto, $produtosTransferidos)) {
                        $produtosNaoTransferidos[] = $idProduto;
                    }
                }
            }

            if (!empty($produtosNaoTransferidos)) {
                $idsNaoTransferidos = implode(', ', $produtosNaoTransferidos);
                return redirect()->back()->with('error', "Os seguintes produtos ainda não foram transferidos e impedem a finalização: $idsNaoTransferidos");
            }

            if ($pendentes) {
                return redirect()->back()->with('error', "Requisição $idSolicitacao possui produtos pendentes de destinação, favor verificar!");
            }

            if (empty($observacao)) {
                return redirect()->back()->with('error', "Não é possível finalizar a solicitação. Justificativa de finalização não foi preenchida!");
            }

            // Atualiza a solicitação como FINALIZADA
            RelacaoSolicitacaoPeca::where('id_solicitacao_pecas', $idSolicitacao)
                ->update([
                    'situacao' => 'FINALIZADA',
                    'justificativa_de_finalizacao' => $observacao,
                    'id_usuario_estoque' => $userId
                ]);

            DB::commit();

            // Redireciona para a tela correta
            if (!empty($idOrdemServico)) {
                return redirect()->route('admin.saidaprodutosestoque.index')
                    ->with('success', 'Solicitação finalizada com sucesso.');
            } elseif ($requisicaoTi) {
                return redirect()->route('admin.saidaprodutosestoque.index')
                    ->with('success', 'Solicitação finalizada com sucesso.');
            } else {
                return redirect()->route('admin.saidaprodutosestoque.index')
                    ->with('success', 'Solicitação finalizada com sucesso.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao finalizar baixa: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Erro ao finalizar a baixa.');
        }
    }

    public function finalizarBaixaTransferencia(Request $request)
    {

        $request->validate([
            'id_solicitacao_pecas' => 'required|integer',
            'justificativa_de_finalizacao' => 'nullable|string',
            'id_ordem_servico' => 'nullable|integer',
            'requisicao_ti' => 'nullable|boolean',
            'produtos_solicitacoes' => 'nullable|array',
            'numero_nota' => 'required',
            'chave_nota' => 'nullable',
            'situacao' => 'nullable|string',
        ]);

        $observacao = $request->input('justificativa_de_finalizacao');
        $idSolicitacao = $request->input('id_solicitacao_pecas');
        $idOrdemServico = $request->input('id_ordem_servico');
        $requisicaoTi = $request->boolean('requisicao_ti');
        $idProdutosSolicitacoes = $request->input('produtos_solicitacoes', []);
        $userId = Auth::id();

        $todosBaixados = DB::table('produtossolicitacoes')
            ->where('id_relacao_solicitacoes', $idSolicitacao)
            ->where('situacao_pecas', '!=', 'COMPRAS')
            ->where('situacao_pecas', '!=', 'TRANSFERENCIA')
            ->where('situacao_pecas', '!=', 'EM SOLICITACAO')
            ->where('situacao_pecas', '!=', 'INATIVO')
            ->whereRaw('COALESCE(quantidade_baixa,0) < quantidade')
            ->exists();

        $statusPermitidos = ['EM BAIXA PARCIAL', 'RECUSADO NA MATRIZ'];
        $cargosLista = [1, 5];
        $produtosNaoTransferidos = [];

        try {
            DB::beginTransaction();

            // Recupera cargo do usuário
            $cargo = DB::table('usuario_deparmanto')
                ->where('id_user', $userId)
                ->value('id_cargo');


            if (!empty($idProdutosSolicitacoes)) {
                // Recupera produtos já transferidos
                $produtosTransferidos = DB::table('transferencia_estoque_aux_envio')
                    ->where('id_relacao_solicitacoes_novo', $idSolicitacao)
                    ->whereIn('id_produtos_solicitacoes', $idProdutosSolicitacoes)
                    ->pluck('id_produtos_solicitacoes')
                    ->toArray();

                // Produtos ainda não transferidos
                foreach ($idProdutosSolicitacoes as $idProduto) {
                    if (!in_array($idProduto, $produtosTransferidos)) {
                        $produtosNaoTransferidos[] = $idProduto;
                    }
                }
            }

            if (!empty($produtosNaoTransferidos)) {
                $idsNaoTransferidos = implode(', ', $produtosNaoTransferidos);
                return redirect()->back()->with('error', "Os seguintes produtos ainda não foram transferidos e impedem a finalização: $idsNaoTransferidos");
            }

            // Atualiza a solicitação como FINALIZADA
            RelacaoSolicitacaoPeca::where('id_solicitacao_pecas', $idSolicitacao)
                ->update([
                    'numero_nota' => $request->numero_nota,
                    'chave_nota' => $request->chave_nota,
                    'justificativa_de_finalizacao' => $observacao,
                    'id_usuario_estoque' => $userId
                ]);

            DB::commit();

            // Redireciona para a tela correta
            if (!empty($idOrdemServico)) {
                return redirect()->route('admin.saidaprodutosestoque.index')
                    ->with('success', 'Solicitação finalizada com sucesso.');
            } elseif ($requisicaoTi) {
                return redirect()->route('admin.saidaprodutosestoque.index')
                    ->with('success', 'Solicitação finalizada com sucesso.');
            } else {
                return redirect()->route('admin.saidaprodutosestoque.index')
                    ->with('success', 'Solicitação finalizada com sucesso.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao finalizar baixa: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Erro ao finalizar a baixa.');
        }
    }

    public function processarBaixa(Request $request, $idRequisicao)
    {
        $request->validate([
            'id_produto' => 'required|integer',
            'id_produtos_solicitacoes' => 'required|integer',
            'quantidade_baixa' => 'required|numeric|min:0', // Permitir 0 para estoque zerado
            'data_baixa' => 'required|date',
            'iniciar_processo_compras' => 'boolean',
            'quantidade_original_solicitada' => 'numeric|nullable',
            'tipo_operacao' => 'string|nullable', // Novo campo para identificar tipo de operação
        ]);

        $idProduto = $request->input('id_produto');
        $idProdutoSolic = $request->input('id_produtos_solicitacoes');
        $qtdBaixa = (float) $request->input('quantidade_baixa');
        $qtdOriginalSolicitada = (float) $request->input('quantidade_original_solicitada', $qtdBaixa);
        $dataBaixa = Carbon::parse($request->input('data_baixa'))->format('Y-m-d');
        $iniciarProcessoCompras = $request->boolean('iniciar_processo_compras');
        $tipoOperacao = $request->input('tipo_operacao', null); // Novo campo
        $idFilial = GetterFilial();
        $idUsuario = Auth::id();

        DB::beginTransaction();

        try {

            $produtoSolic = ProdutosSolicitacoes::where('id_relacao_solicitacoes', $idRequisicao)
                ->where('id_produtos_solicitacoes', $idProdutoSolic)
                ->where('id_protudos', $idProduto)
                ->lockForUpdate()
                ->firstOrFail();

            // Verificar se o item está inativo
            if ($produtoSolic->situacao_pecas === 'INATIVO') {
                DB::rollBack();
                return response()->json(['message' => 'Não é possível processar baixa para item inativo.'], 422);
            }

            // Quantidade já baixada antes
            $qtdBaixaAnterior = $produtoSolic->quantidade_baixa ?? 0;

            // Não pode baixar menos que antes
            if ($qtdBaixa < $qtdBaixaAnterior) {
                return response()->json(['message' => 'A nova baixa não pode ser menor que a baixa anterior.'], 422);
            }

            // Nova quantidade a baixar (somente diferença)
            $qtdNovaBaixa = $qtdBaixa - $qtdBaixaAnterior;

            if ($qtdBaixa > $produtoSolic->quantidade) {
                return response()->json(['message' => 'Quantidade de baixa maior que a solicitada.'], 422);
            }

            // Se a quantidade de baixa for 0 (estoque zerado), apenas processar compras
            if ($qtdBaixa == 0 && $iniciarProcessoCompras) {
                // Não atualiza estoque, apenas cria solicitação para compras
                // A solicitação original permanece com quantidade_baixa = 0
                $estoque = null;
                $saldo = 0;
            } else {
                // Verifica estoque apenas se há quantidade para baixar
                $estoque = ProdutosPorFilial::where('id_produto_unitop', $idProduto)
                    ->where('id_filial', $idFilial)
                    ->lockForUpdate()
                    ->firstOrFail();

                // NOVA LÓGICA: COMPRA PARCIAL - quando Nova QTD Baixa é menor que Quantidade Solicitada
                if ($tipoOperacao === 'baixa_parcial_com_compra' && $qtdBaixa < $produtoSolic->quantidade) {

                    // Verificar se há estoque suficiente para a quantidade de baixa
                    if ($qtdNovaBaixa > $estoque->quantidade_produto) {
                        // Se não houver estoque suficiente, marcar como COMPRA automaticamente
                        $produtoSolic->update([
                            'quantidade_baixa' => 0, // Não baixa nada do estoque
                            'data_baixa' => $dataBaixa,
                            'id_user' => $idUsuario,
                            'quantidade_compra' => $qtdBaixa,
                            'situacao_pecas' => 'COMPRAS',
                            'observacao' => "COMPRA - Estoque insuficiente. Solicitado: {$qtdBaixa}, Disponível: {$estoque->quantidade_produto}",
                        ]);

                        // Registrar na tabela ItemCompra
                        $this->registrarItemCompra($produtoSolic, $idUsuario, 'COMPRAS', $qtdBaixa);

                        // Não altera estoque pois não houve baixa
                        $saldo = $estoque->quantidade_produto;
                    } else {
                        $qtdParaCompras = $produtoSolic->quantidade - $qtdBaixa;

                        // Atualiza a solicitação com baixa parcial e quantidade para compras
                        $produtoSolic->update([
                            'quantidade_baixa' => $qtdBaixa,
                            'data_baixa' => $dataBaixa,
                            'id_user' => $idUsuario,
                            'quantidade_compra' => $qtdParaCompras,
                            'situacao_pecas' => 'COMPRA PARCIAL',
                            'observacao' => "COMPRA PARCIAL - Baixado: {$qtdBaixa}, Para compras: {$qtdParaCompras}",
                        ]);

                        // Registrar na tabela ItemCompra
                        $this->registrarItemCompra($produtoSolic, $idUsuario, 'COMPRA PARCIAL', $qtdParaCompras);

                        // Atualiza estoque (deduz apenas a quantidade baixada)
                        $saldo = $estoque->quantidade_produto - $qtdNovaBaixa;
                        $estoque->update(['quantidade_produto' => $saldo]);
                    }
                }
                // NOVA LÓGICA: Se Nova QTD Baixa = Quantidade Solicitada total mas estoque insuficiente
                elseif ($qtdBaixa == $produtoSolic->quantidade && $qtdNovaBaixa > $estoque->quantidade_produto) {

                    // Se o estoque é zero, marcar como COMPRA diretamente
                    if ($estoque->quantidade_produto == 0) {
                        $produtoSolic->update([
                            'quantidade_baixa' => 0, // Não baixa nada do estoque
                            'data_baixa' => $dataBaixa,
                            'id_user' => $idUsuario,
                            'quantidade_compra' => $qtdBaixa,
                            'situacao_pecas' => 'COMPRAS',
                            'observacao' => "COMPRA - Estoque zerado. Solicitado: {$qtdBaixa}, Disponível: {$estoque->quantidade_produto}",
                        ]);

                        // Registrar na tabela ItemCompra
                        $this->registrarItemCompra($produtoSolic, $idUsuario, 'COMPRAS', $qtdBaixa);

                        // Não altera estoque pois não houve baixa
                        $saldo = $estoque->quantidade_produto;
                    } else {
                        // Baixar apenas o que tem disponível no estoque
                        $qtdBaixaReal = $qtdBaixaAnterior + $estoque->quantidade_produto;
                        $qtdNovaBaixaReal = $estoque->quantidade_produto;
                        $qtdParaCompras = $produtoSolic->quantidade - $qtdBaixaReal;

                        // Atualiza a solicitação com baixa parcial
                        $produtoSolic->update([
                            'quantidade_baixa' => $qtdBaixaReal,
                            'data_baixa' => $dataBaixa,
                            'id_user' => $idUsuario,
                            'quantidade_compra' => $qtdParaCompras,
                            'situacao_pecas' => 'COMPRA PARCIAL',
                            'observacao' => "Baixa parcial - Estoque disponível: {$estoque->quantidade_produto}, Baixado: {$qtdBaixaReal}, Para compras: {$qtdParaCompras}",
                        ]);

                        // Registrar na tabela ItemCompra
                        $this->registrarItemCompra($produtoSolic, $idUsuario, 'COMPRA PARCIAL', $qtdParaCompras);

                        // Zerar o estoque (baixou tudo que tinha)
                        $saldo = 0;
                        $estoque->update(['quantidade_produto' => $saldo]);
                    }
                } else {
                    // Lógica normal - verificação de estoque
                    if ($qtdNovaBaixa > $estoque->quantidade_produto) {
                        // Se não houver estoque suficiente, marcar como COMPRA automaticamente
                        $produtoSolic->update([
                            'quantidade_baixa' => 0, // Não baixa nada do estoque
                            'data_baixa' => $dataBaixa,
                            'id_user' => $idUsuario,
                            'quantidade_compra' => $qtdBaixa,
                            'situacao_pecas' => 'COMPRAS',
                            'observacao' => "COMPRA - Estoque insuficiente. Solicitado: {$qtdBaixa}, Disponível: {$estoque->quantidade_produto}",
                        ]);

                        // Registrar na tabela ItemCompra
                        $this->registrarItemCompra($produtoSolic, $idUsuario, 'COMPRAS', $qtdBaixa);

                        // Não altera estoque pois não houve baixa
                        $saldo = $estoque->quantidade_produto;
                    } else {
                        // Atualiza a solicitação
                        $produtoSolic->update([
                            'quantidade_baixa' => $qtdBaixa,
                            'data_baixa' => $dataBaixa,
                            'id_user' => $idUsuario,
                        ]);

                        // Atualiza estoque (somente diferença)
                        $saldo = $estoque->quantidade_produto - $qtdNovaBaixa;
                        $estoque->update(['quantidade_produto' => $saldo]);
                    }
                }
            }

            // Verifica se todos produtos foram baixados
            $todosBaixados = ProdutosSolicitacoes::where('id_relacao_solicitacoes', $idRequisicao)
                ->whereRaw('COALESCE(quantidade_baixa,0) < quantidade')
                ->doesntExist();

            // Busca dados da requisição
            $requisicao = RelacaoSolicitacaoPeca::lockForUpdate()->findOrFail($idRequisicao);
            $status = $todosBaixados ? 'FINALIZADA' : 'EM BAIXA PARCIAL';

            // Se for transferência entre estoques → "AGUARDANDO ENVIO"
            if ($this->isTransferencia($requisicao)) {
                $status = $todosBaixados ? 'AGUARDANDO ENVIO' : 'EM BAIXA PARCIAL';
            }

            // Atualiza status da requisição
            $requisicao->update([
                'id_usuario_estoque' => $idUsuario,
                'situacao' => $status,
            ]);

            // Se for OS vinculada → atualizar também a OS
            if (!empty($requisicao->id_orderm_servico) && $qtdBaixa > 0) {
                $this->atualizarOrdemServico($requisicao, $idProduto, $qtdNovaBaixa, $saldo, $idFilial);
            } elseif ($qtdBaixa > 0 && $estoque) {
                // Registro no histórico (REQUISIÇÃO normal) apenas se houve baixa
                HistoricoMovimentacaoEstoque::create([
                    'data_inclusao' => now(),
                    'id_produto' => $idProduto,
                    'id_filial' => $idFilial,
                    'qtde_estoque' => $estoque->quantidade_produto + $qtdNovaBaixa, // Quantidade antes da baixa
                    'qtde_baixa' => $qtdNovaBaixa,
                    'saldo_total' => $saldo,
                    'id_relacaosolicitacoespecas' => $idRequisicao,
                    'tipo' => 'BAIXA DE REQUISICAO',
                ]);
            }

            if ($iniciarProcessoCompras && $qtdOriginalSolicitada > $qtdBaixa && $tipoOperacao !== 'baixa_parcial_com_compra') {
                $qtdParaCompras = $qtdOriginalSolicitada - $qtdBaixa;

                Log::info('Atualizando solicitação existente com quantidade para compras', [
                    'qtdParaCompras' => $qtdParaCompras,
                    'idRequisicao' => $idRequisicao,
                    'idProduto' => $idProduto
                ]);

                // Atualizar a solicitação existente com quantidade_compra e situacao_pecas
                $produtoSolic->update([
                    'data_alteracao' => now(),
                    'quantidade_compra' => $qtdParaCompras,
                    'situacao_pecas' => 'COMPRAS',
                    'observacao' => "Saida de produtos {$idRequisicao} - Quantidade insuficiente em estoque. Quantidade original: {$qtdOriginalSolicitada}, Baixado: {$qtdBaixa}, Para compras: {$qtdParaCompras}",
                ]);

                // Registrar na tabela ItemCompra
                $this->registrarItemCompra($produtoSolic, $idUsuario, 'COMPRAS', $qtdParaCompras);

                Log::info('Solicitação atualizada para compras - SUCESSO', [
                    'id_solicitacao' => $produtoSolic->id_produtos_solicitacoes,
                    'quantidade_para_compras' => $qtdParaCompras,
                    'situacao_pecas' => $produtoSolic->situacao_pecas,
                    'produto_id' => $idProduto
                ]);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Baixa realizada com sucesso']);
        } catch (\Exception $e) {
            Log::error('Erro ao baixar unificado: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro no servidor'], 500);
        }
    }

    public function transferirProduto(Request $request, $idRequisicao)
    {
        $request->validate([
            'id_produto' => 'required|integer',
            'id_produtos_solicitacoes' => 'required|integer',
            'quantidade_baixa' => 'required|numeric|min:0',
            'data_baixa' => 'required|date',
        ]);

        $idProduto = $request->input('id_produto');
        $idProdutoSolic = $request->input('id_produtos_solicitacoes');
        $qtdBaixa = (float) $request->input('quantidade_baixa');
        $dataBaixa = Carbon::parse($request->input('data_baixa'))->format('Y-m-d');
        $idFilial = GetterFilial();
        $idUsuario = Auth::id();

        DB::beginTransaction();

        try {

            $produtoSolic = ProdutosSolicitacoes::where('id_relacao_solicitacoes', $idRequisicao)
                ->where('id_produtos_solicitacoes', $idProdutoSolic)
                ->where('id_protudos', $idProduto)
                ->lockForUpdate()
                ->firstOrFail();

            // Verificar se o item está inativo
            if ($produtoSolic->situacao_pecas === 'INATIVO') {
                DB::rollBack();
                return response()->json(['message' => 'Não é possível processar baixa para item inativo.'], 422);
            }

            // Validação: Quantidade deve ser exatamente igual à solicitada
            if ($qtdBaixa != $produtoSolic->quantidade) {
                if ($qtdBaixa < $produtoSolic->quantidade) {
                    return response()->json(['message' => "Quantidade informada ({$qtdBaixa}) é menor que a quantidade solicitada ({$produtoSolic->quantidade}). Informe a quantidade exata."], 422);
                } else {
                    return response()->json(['message' => "Quantidade informada ({$qtdBaixa}) é maior que a quantidade solicitada ({$produtoSolic->quantidade}). Informe a quantidade exata."], 422);
                }
            }

            // Verificar se já foi baixado anteriormente
            $qtdBaixaAnterior = $produtoSolic->quantidade_baixa ?? 0;
            if ($qtdBaixaAnterior > 0) {
                return response()->json(['message' => 'Este item já foi baixado anteriormente.'], 422);
            }

            // Buscar estoque usando quantidade_transferencia
            $estoque = ProdutosPorFilial::where('id_produto_unitop', $idProduto)
                ->where('id_filial', $idFilial)
                ->lockForUpdate()
                ->firstOrFail();

            $qtdTransferenciaDisponivel = (float) $estoque->quantidade_transferencia ?? 0;

            // Verificar se há estoque suficiente na quantidade_transferencia
            if ($qtdBaixa > $qtdTransferenciaDisponivel) {
                return response()->json(['message' => "Estoque insuficiente para transferência. Disponível: {$qtdTransferenciaDisponivel}, Solicitado: {$qtdBaixa}"], 422);
            }

            // Atualiza a solicitação
            $produtoSolic->update([
                'quantidade_baixa' => $qtdBaixa,
                'data_baixa' => $dataBaixa,
                'id_user' => $idUsuario,
            ]);

            // Atualiza estoque (deduz da quantidade_transferencia)
            $saldo = $qtdTransferenciaDisponivel - $qtdBaixa;

            $estoque->update(['quantidade_transferencia' => $saldo]);

            // Verifica se todos produtos foram baixados
            $todosBaixados = ProdutosSolicitacoes::where('id_relacao_solicitacoes', $idRequisicao)
                ->whereRaw('COALESCE(quantidade_baixa,0) < quantidade')
                ->doesntExist();

            // Busca dados da requisição
            $requisicao = RelacaoSolicitacaoPeca::lockForUpdate()->findOrFail($idRequisicao);

            // Definir status baseado se todos os produtos foram transferidos
            $status = $todosBaixados ? 'TRANSFERIDO' : 'TRANSFERIDO PARCIALMENTE';

            // Atualiza status da requisição
            $requisicao->update([
                'id_usuario_estoque' => $idUsuario,
                'situacao' => $status,
            ]);

            Log::info("Transferência de produto processada", [
                'id_relacao' => $idRequisicao,
                'id_produto_transferido' => $idProdutoSolic,
                'todos_baixados' => $todosBaixados,
                'nova_situacao' => $status
            ]);

            // Se for OS vinculada → atualizar também a OS
            if (!empty($requisicao->id_orderm_servico)) {
                $this->atualizarOrdemServico($requisicao, $idProduto, $qtdBaixa, $saldo, $idFilial);
            } else {
                // Registro no histórico (REQUISIÇÃO normal)
                HistoricoMovimentacaoEstoque::create([
                    'data_inclusao' => now(),
                    'id_produto' => $idProduto,
                    'id_filial' => $idFilial,
                    'qtde_estoque' => $qtdTransferenciaDisponivel, // Quantidade antes da baixa
                    'qtde_baixa' => $qtdBaixa,
                    'saldo_total' => $saldo,
                    'id_relacaosolicitacoespecas' => $idRequisicao,
                    'tipo' => 'BAIXA DE REQUISICAO',
                ]);
            }

            DB::commit();

            $mensagemSucesso = $todosBaixados
                ? 'Transferência realizada com sucesso. Todos os produtos foram transferidos.'
                : 'Transferência parcial realizada com sucesso. Ainda existem produtos pendentes de transferência nesta solicitação.';

            return response()->json(['success' => true, 'message' => $mensagemSucesso]);
        } catch (\Exception $e) {
            Log::error('Erro ao transferir produto: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro no servidor'], 500);
        }
    }

    public function finalizarbaixaconsultamateriais(Request $request)
    {
        $request->validate([
            'id_solicitacao_pecas' => 'required|integer',
            'justificativa_de_finalizacao' => 'nullable|string',
            'id_ordem_servico' => 'nullable|integer',
            'requisicao_ti' => 'nullable|boolean',
            'produtos_solicitacoes' => 'nullable|array',
            'situacao' => 'nullable|string',
        ]);

        $observacao = $request->input('justificativa_de_finalizacao');
        $idSolicitacao = $request->input('id_solicitacao_pecas');
        $idOrdemServico = $request->input('id_ordem_servico');
        $requisicaoTi = $request->boolean('requisicao_ti');
        $idProdutosSolicitacoes = $request->input('produtos_solicitacoes', []);
        $userId = Auth::id();

        $statusPermitidos = ['EM BAIXA PARCIAL', 'RECUSADO NA MATRIZ'];
        $cargosLista = [1, 5];
        $produtosNaoTransferidos = [];

        try {
            DB::beginTransaction();

            // Recupera cargo do usuário
            $cargo = DB::table('usuario_deparmanto')
                ->where('id_user', $userId)
                ->value('id_cargo');

            // Verifica se há produtos pendentes
            $pendentes = DB::table('produtossolicitacoes')
                ->where('id_relacao_solicitacoes', $idSolicitacao)
                ->whereNull('quantidade_baixa')
                ->exists();

            if (!empty($idProdutosSolicitacoes)) {
                // Recupera produtos já transferidos
                $produtosTransferidos = DB::table('transferencia_estoque_aux_envio')
                    ->where('id_relacao_solicitacoes_novo', $idSolicitacao)
                    ->whereIn('id_produtos_solicitacoes', $idProdutosSolicitacoes)
                    ->pluck('id_produtos_solicitacoes')
                    ->toArray();

                // Produtos ainda não transferidos
                foreach ($idProdutosSolicitacoes as $idProduto) {
                    if (!in_array($idProduto, $produtosTransferidos)) {
                        $produtosNaoTransferidos[] = $idProduto;
                    }
                }
            }

            if (!empty($produtosNaoTransferidos)) {
                $idsNaoTransferidos = implode(', ', $produtosNaoTransferidos);
                return redirect()->back()->with('error', "Os seguintes produtos ainda não foram transferidos e impedem a finalização: $idsNaoTransferidos");
            }

            if ($pendentes) {
                return redirect()->back()->with('error', "Requisição $idSolicitacao possui produtos pendentes de destinação, favor verificar!");
            }

            if (empty($observacao)) {
                return redirect()->back()->with('error', "Não é possível finalizar a solicitação. Justificativa de finalização não foi preenchida!");
            }

            // Atualiza a solicitação como FINALIZADA
            RelacaoSolicitacaoPeca::where('id_solicitacao_pecas', $idSolicitacao)
                ->update([
                    'situacao' => 'FINALIZADA',
                    'justificativa_de_finalizacao' => $observacao,
                    'id_usuario_estoque' => $userId
                ]);

            DB::commit();

            // Redireciona para a tela correta
            if (!empty($idOrdemServico)) {
                return redirect()->route('admin.saidaprodutosestoque.index')
                    ->with('success', 'Solicitação finalizada com sucesso.');
            } elseif ($requisicaoTi) {
                return redirect()->route('admin.saidaprodutosestoque.index')
                    ->with('success', 'Solicitação finalizada com sucesso.');
            } else {
                return redirect()->route('admin.saidaprodutosestoque.index')
                    ->with('success', 'Solicitação finalizada com sucesso.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao finalizar baixa: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Erro ao finalizar a baixa.');
        }
    }

    public function estornarBaixa(Request $request, $id)
    {
        $request->validate([
            'justificativa' => 'required|string|min:5|max:500',
        ], [
            'justificativa.required' => 'A justificativa é obrigatória.',
            'justificativa.min' => 'A justificativa deve ter pelo menos 5 caracteres.',
            'justificativa.max' => 'A justificativa não pode exceder 500 caracteres.',
        ]);

        DB::beginTransaction();

        try {
            $justificativa = $request->input('justificativa');
            $idUsuario = Auth::id();

            // Busca o registro da solicitação
            $produtoSolic = ProdutosSolicitacoes::with('produto', 'relacaoSolicitacoesPecas')
                ->find($id);

            if (!$produtoSolic) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Registro não encontrado.'], 404);
            }

            // Verificar se o item tem baixa para ser estornada
            if (!$produtoSolic->quantidade_baixa || $produtoSolic->quantidade_baixa <= 0) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Este item não possui baixa para ser estornada.'], 422);
            }

            $idProduto = $produtoSolic->id_protudos;
            $qtdBaixaAnterior = $produtoSolic->quantidade_baixa;
            $idFilial = Auth::user()->filial_id;

            // Restaurar estoque se houver baixa anterior
            if ($qtdBaixaAnterior > 0) {
                $estoque = ProdutosPorFilial::where('id_produto_unitop', $idProduto)
                    ->where('id_filial', $idFilial)
                    ->lockForUpdate()
                    ->first();

                if ($estoque) {
                    // Restaurar a quantidade no estoque
                    $novoSaldo = $estoque->quantidade_produto + $qtdBaixaAnterior;
                    $estoque->update(['quantidade_produto' => $novoSaldo]);

                    // Registrar no histórico
                    HistoricoMovimentacaoEstoque::create([
                        'data_inclusao' => now(),
                        'id_produto' => $idProduto,
                        'id_filial' => $idFilial,
                        'qtde_estoque' => $estoque->quantidade_produto, // Quantidade antes do estorno
                        'qtde_baixa' => -$qtdBaixaAnterior, // Negativo indica estorno
                        'saldo_total' => $novoSaldo,
                        'id_relacaosolicitacoespecas' => $produtoSolic->id_relacao_solicitacoes,
                        'tipo' => 'ESTORNO DE BAIXA',
                        'justificativa' => "Estorno - Justificativa: {$justificativa}",
                    ]);
                }
            }

            // Atualizar a solicitação - remover baixa e marcar como inativa
            $produtoSolic->update([
                'quantidade_baixa' => null,
                'data_baixa' => null,
                'situacao_pecas' => 'INATIVO',
                'justificativa' => "ESTORNADO - Justificativa: {$justificativa} - Usuário: " . Auth::user()->name . " - Data: " . now()->format('d/m/Y H:i:s'),
                'id_user' => $idUsuario,
                'data_alteracao' => now(),
            ]);

            // Se vinculado a OS, atualizar também
            $relacao = $produtoSolic->relacaoSolicitacoesPecas;
            if ($relacao && !empty($relacao->id_orderm_servico)) {
                OrdemServicoPecas::where('id_produto', $idProduto)
                    ->where('id_ordem_servico', $relacao->id_orderm_servico)
                    ->update([
                        'jasolicitada' => false,
                        'situacao_pecas' => 'ESTORNADA',
                        'justificativa' => "Estorno de baixa - {$justificativa}",
                    ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Baixa estornada com sucesso. O item foi marcado como inativo.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao estornar baixa: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'Erro ao estornar a baixa.'], 500);
        }
    }

    public function estornarTransferencia(Request $request, $id)
    {
        $request->validate([
            'justificativa' => 'required|string|min:5|max:500',
        ], [
            'justificativa.required' => 'A justificativa é obrigatória.',
            'justificativa.min' => 'A justificativa deve ter pelo menos 5 caracteres.',
            'justificativa.max' => 'A justificativa não pode exceder 500 caracteres.',
        ]);

        DB::beginTransaction();

        try {
            $justificativa = $request->input('justificativa');
            $idUsuario = Auth::id();

            // Busca o registro da solicitação
            $produtoSolic = ProdutosSolicitacoes::with('produto', 'relacaoSolicitacoesPecas')
                ->find($id);

            if (!$produtoSolic) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Registro não encontrado.'], 404);
            }

            // Verificar se o item tem baixa para ser estornada
            if (!$produtoSolic->quantidade_baixa || $produtoSolic->quantidade_baixa <= 0) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Este item não possui baixa para ser estornada.'], 422);
            }

            $idProduto = $produtoSolic->id_protudos;
            $qtdBaixaAnterior = $produtoSolic->quantidade_baixa;
            $idFilial = GetterFilial();
            $idRelacaoSolicitacao = $produtoSolic->id_relacao_solicitacoes;

            // Restaurar estoque se houver baixa anterior
            if ($qtdBaixaAnterior > 0) {
                $estoque = ProdutosPorFilial::where('id_produto_unitop', $idProduto)
                    ->where('id_filial', $idFilial)
                    ->lockForUpdate()
                    ->first();

                if ($estoque) {
                    // Restaurar a quantidade no estoque (quantidade_transferencia)
                    $qtdTransferenciaAtual = $estoque->quantidade_transferencia ?? 0;
                    $novoSaldo = $qtdTransferenciaAtual + $qtdBaixaAnterior;
                    $estoque->update(['quantidade_transferencia' => $novoSaldo]);

                    // Registrar no histórico
                    HistoricoMovimentacaoEstoque::create([
                        'data_inclusao' => now(),
                        'id_produto' => $idProduto,
                        'id_filial' => $idFilial,
                        'qtde_estoque' => $qtdTransferenciaAtual,
                        'qtde_baixa' => -$qtdBaixaAnterior,
                        'saldo_total' => $novoSaldo,
                        'id_relacaosolicitacoespecas' => $idRelacaoSolicitacao,
                        'tipo' => 'ESTORNO DE BAIXA',
                        'justificativa' => "Estorno - Justificativa: {$justificativa}",
                    ]);
                }
            }

            // Atualizar a solicitação - remover baixa e marcar como inativa
            $produtoSolic->update([
                'quantidade_baixa' => null,
                'data_baixa' => null,
                'justificativa' => "ESTORNADO - Justificativa: {$justificativa} - Usuário: " . Auth::user()->name . " - Data: " . now()->format('d/m/Y H:i:s'),
                'id_user' => $idUsuario,
                'data_alteracao' => now(),
            ]);

            // Verificar se existem outros itens na mesma relação que ainda possuem baixa
            $itensComBaixa = ProdutosSolicitacoes::where('id_relacao_solicitacoes', $idRelacaoSolicitacao)
                ->where('id_produtos_solicitacoes', '!=', $id) // Excluir o item atual que acabou de ser estornado
                ->whereNotNull('quantidade_baixa')
                ->where('quantidade_baixa', '>', 0)
                ->count();

            $relacao = $produtoSolic->relacaoSolicitacoesPecas;

            if ($relacao) {
                // Determinar a situação baseada na existência de outros itens com baixa
                $novaSituacao = $itensComBaixa > 0 ? 'ESTORNO PARCIAL' : 'ESTORNO DE TRANSFERENCIA';

                $relacao->update([
                    'data_alteracao' => now(),
                    'situacao' => $novaSituacao,
                ]);

                Log::info("Estorno de transferência processado", [
                    'id_relacao' => $idRelacaoSolicitacao,
                    'id_produto_estornado' => $id,
                    'itens_com_baixa_restantes' => $itensComBaixa,
                    'nova_situacao' => $novaSituacao
                ]);
            }

            if ($relacao && !empty($relacao->id_orderm_servico)) {
                OrdemServicoPecas::where('id_produto', $idProduto)
                    ->where('id_ordem_servico', $relacao->id_orderm_servico)
                    ->update([
                        'jasolicitada' => false,
                        'situacao_pecas' => 'ESTORNADA',
                        'justificativa' => "Estorno de baixa - {$justificativa}",
                    ]);
            }

            DB::commit();

            $messagemSucesso = $itensComBaixa > 0
                ? 'Baixa estornada com sucesso. Estorno parcial - ainda existem outros itens com baixa nesta solicitação.'
                : 'Baixa estornada com sucesso. Estorno completo - todos os itens desta solicitação foram estornados.';

            return response()->json([
                'status' => 'success',
                'message' => $messagemSucesso,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao estornar baixa: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'Erro ao estornar a baixa.'], 500);
        }
    }

    public function cancelarTransferencia(Request $request, $id)
    {
        // Debug: Log dos dados recebidos
        Log::info('Dados recebidos no cancelarTransferencia:', [
            'request_all' => $request->all(),
            'id' => $id,
            'justificativa' => $request->input('justificativa'),
        ]);

        $request->validate([
            'justificativa' => 'required|string|min:5|max:500',
        ], [
            'justificativa.required' => 'A justificativa é obrigatória.',
            'justificativa.min' => 'A justificativa deve ter pelo menos 5 caracteres.',
            'justificativa.max' => 'A justificativa não pode exceder 500 caracteres.',
        ]);

        $relacaoSolicitacao = RelacaoSolicitacaoPeca::with('produtosSolicitacoes')->find($id);

        if (!$relacaoSolicitacao) {
            return response()->json(['status' => 'error', 'message' => 'Registro nao encontrado.'], 404);
        }

        DB::beginTransaction();

        try {
            $idUsuario = Auth::id();
            $justificativa = $request->input('justificativa');

            // Buscar todos os produtos da solicitação que possuem baixa
            $produtosSolicitados = ProdutosSolicitacoes::with('produto', 'relacaoSolicitacoesPecas')
                ->where('id_relacao_solicitacoes', $id)
                ->whereNotNull('quantidade_baixa')
                ->where('quantidade_baixa', '>', 0)
                ->get();

            $idFilial = GetterFilial();

            // Estornar cada produto
            foreach ($produtosSolicitados as $produtoSolic) {
                $idProduto = $produtoSolic->id_protudos;
                $qtdBaixaAnterior = $produtoSolic->quantidade_baixa;

                // Restaurar estoque se houver baixa anterior
                if ($qtdBaixaAnterior > 0) {
                    $estoque = ProdutosPorFilial::where('id_produto_unitop', $idProduto)
                        ->where('id_filial', $idFilial)
                        ->lockForUpdate()
                        ->first();

                    if ($estoque) {
                        // Restaurar a quantidade no estoque (quantidade_transferencia)
                        $qtdTransferenciaAtual = $estoque->quantidade_transferencia ?? 0;
                        $novoSaldo = $qtdTransferenciaAtual + $qtdBaixaAnterior;
                        $estoque->update(['quantidade_transferencia' => $novoSaldo]);

                        // Registrar no histórico
                        HistoricoMovimentacaoEstoque::create([
                            'data_inclusao' => now(),
                            'id_produto' => $idProduto,
                            'id_filial' => $idFilial,
                            'qtde_estoque' => $qtdTransferenciaAtual,
                            'qtde_baixa' => -$qtdBaixaAnterior,
                            'saldo_total' => $novoSaldo,
                            'id_relacaosolicitacoespecas' => $id,
                            'tipo' => 'ESTORNO DE BAIXA',
                            'justificativa' => "Estorno - Justificativa: {$justificativa}",
                        ]);
                    }
                }

                // Atualizar a solicitação - remover baixa
                $produtoSolic->update([
                    'quantidade_baixa' => null,
                    'data_baixa' => null,
                    'justificativa' => "ESTORNADO - Justificativa: {$justificativa} - Usuário: " . Auth::user()->name . " - Data: " . now()->format('d/m/Y H:i:s'),
                    'id_user' => $idUsuario,
                    'data_alteracao' => now(),
                ]);
            }

            // Atualizar a relação principal - sempre será estorno completo pois estorna todos os itens
            $relacaoSolicitacao->update([
                'data_alteracao' => now(),
                'situacao' => 'CANCELADO',
            ]);

            // Se vinculado a OS, atualizar tambem
            if (!empty($relacaoSolicitacao->id_orderm_servico)) {
                foreach ($produtosSolicitados as $produtoSolic) {
                    OrdemServicoPecas::where('id_produto', $produtoSolic->id_protudos)
                        ->where('id_ordem_servico', $relacaoSolicitacao->id_orderm_servico)
                        ->update([
                            'jasolicitada' => false,
                            'situacao_pecas' => 'CANCELADA',
                            'justificativa' => "Cancelamento de baixa - {$justificativa}",
                        ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transferência cancelada com sucesso. Todos os itens foram estornados.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao estornar baixa: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'Erro ao estornar a baixa.'], 500);
        }
    }

    /**
     * Registra um item na tabela ItemCompra quando ele é direcionado para compras
     *
     * @param ProdutosSolicitacoes $produtoSolic
     * @param int $idUsuario
     * @param string $situacao
     * @param float $quantidadeCompra
     * @return void
     */
    private function registrarItemCompra(ProdutosSolicitacoes $produtoSolic, int $idUsuario, string $situacao, float $quantidadeCompra): void
    {
        try {
            ItemCompra::create([
                'data_inclusao' => now(),
                'data_alteracao' => now(),
                'id_produto' => $produtoSolic->id_protudos,
                'id_user' => $idUsuario,
                'situacao' => $situacao,
                'id_relacaosolicitacoespecas' => $produtoSolic->id_relacao_solicitacoes,
                'quantidade_compra' => $quantidadeCompra,
            ]);

            Log::info('Item registrado na tabela ItemCompra', [
                'id_produto' => $produtoSolic->id_protudos,
                'id_user' => $idUsuario,
                'situacao' => $situacao,
                'id_relacaosolicitacoespecas' => $produtoSolic->id_relacao_solicitacoes,
                'quantidade_compra' => $quantidadeCompra,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao registrar item na tabela ItemCompra', [
                'error' => $e->getMessage(),
                'id_produto' => $produtoSolic->id_protudos,
                'id_relacaosolicitacoespecas' => $produtoSolic->id_relacao_solicitacoes,
            ]);
        }
    }
}

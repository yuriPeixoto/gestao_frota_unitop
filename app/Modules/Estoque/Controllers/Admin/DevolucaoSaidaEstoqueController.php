<?php

namespace App\Modules\Estoque\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DevolucaoMateriais;
use App\Models\DevolucaoProdutoOrdem;
use App\Models\VFilial;
use App\Modules\Estoque\Models\HistoricoMovimentacaoEstoque;
use App\Modules\Manutencao\Models\OrdemServico;
use App\Modules\Manutencao\Models\OrdemServicoPecas;
use App\Models\Produto;
use App\Models\ProdutosPorFilial;
use App\Models\ProdutosSolicitacoes;
use App\Models\RelacaoSolicitacaoPeca;
use Illuminate\Http\Request;
use App\Traits\SanitizesMonetaryValues;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;



class DevolucaoSaidaEstoqueController extends Controller
{
    use SanitizesMonetaryValues;

    public function index(Request $request)
    {
        $devolucaoProdutos = $this->getDevolucaoProdutos($request);
        $devolucaoMateriais = $this->getDevolucaoMateriais($request);

        return view('admin.devolucaosaidaestoque.index', array_merge([
            'devolucaoProdutos' => $devolucaoProdutos,
            'devolucaoMateriais' => $devolucaoMateriais
        ]));
    }

    private function getDevolucaoProdutos(Request $request)
    {
        $query = DevolucaoProdutoOrdem::query()
            ->with('ordemServico.tipoOrdemServico', 'produto');

        $this->applyDevolucaoProdutosFilters($query, $request);

        return $query->latest('data_inclusao')->paginate(10);
    }

    private function applyDevolucaoProdutosFilters($query, Request $request)
    {
        // Filtro de data com tratamento de erro
        if ($request->filled('data_inclusao_inicial') && $request->filled('data_inclusao_final')) {
            try {
                $dataInicial = Carbon::createFromFormat('Y-m-d', $request->input('data_inclusao_inicial'))
                    ->startOfDay();
                $dataFinal = Carbon::createFromFormat('Y-m-d', $request->input('data_inclusao_final'))
                    ->endOfDay();

                $query->whereBetween('data_inclusao', [$dataInicial, $dataFinal]);
            } catch (\Exception $e) {
                Log::error('Erro no filtro de data: ' . $e->getMessage());
            }
        }

        $filters = [
            'id_devolucao_produtos' => 'id_devolucao_produtos',
            'id_ordem_servico' => 'id_ordem_servico'
        ];

        foreach ($filters as $requestField => $dbField) {
            if ($request->filled($requestField)) {
                $query->where($dbField, $request->input($requestField));
            }
        }
    }

    private function getDevolucaoMateriais(Request $request)
    {
        $query = DevolucaoMateriais::query()
            ->with('produto', 'filial');

        $this->applyDevolucaoMateriaisFilters($query, $request);

        return $query->latest('data_inclusao')->paginate(10);
    }

    private function applyDevolucaoMateriaisFilters($query, Request $request)
    {
        // Filtro de data com tratamento de erro
        if ($request->filled('data_inclusao_inicial') && $request->filled('data_inclusao_final')) {
            try {
                $dataInicial = Carbon::createFromFormat('Y-m-d', $request->input('data_inclusao_inicial'))
                    ->startOfDay();
                $dataFinal = Carbon::createFromFormat('Y-m-d', $request->input('data_inclusao_final'))
                    ->endOfDay();

                $query->whereBetween('data_inclusao', [$dataInicial, $dataFinal]);
            } catch (\Exception $e) {
                Log::error('Erro no filtro de data: ' . $e->getMessage());
            }
        }

        $filters = [
            'id_devolucao_materiais' => 'id_devolucao_materiais',
            'id_relacaosolicitacoespecas' => 'id_relacaosolicitacoespecas'
        ];

        foreach ($filters as $requestField => $dbField) {
            if ($request->filled($requestField)) {
                $query->where($dbField, $request->input($requestField));
            }
        }
    }

    public function edit($id)
    {

        $devolucaoProduto = DevolucaoProdutoOrdem::findOrFail($id);

        return view('admin.devolucaosaidaestoque.edit', compact('devolucaoProduto'));
    }

    public function createDevProdutos()
    {
        $filiais = $this->getFiliais();

        $ordem_servico = $this->getOrdemServico();

        $produtos = $this->getProdutos();

        return view('admin.devolucaosaidaestoque.create_devProdutos', compact('filiais', 'ordem_servico', 'produtos'));
    }

    private function getFiliais()
    {
        return VFilial::select('id as value', 'name as label')
            ->orderBy('name')
            ->get();
    }

    private function getOrdemServico()
    {
        return OrdemServico::join('tipo_ordem_servico as tos', 'tos.id_tipo_ordem_servico', '=', 'ordem_servico.id_tipo_ordem_servico')
            ->select(
                'ordem_servico.id_ordem_servico as value',
                DB::raw("CONCAT('Cód. ', ordem_servico.id_ordem_servico, ' - ', tos.descricao_tipo_ordem) as label")
            )
            ->where('ordem_servico.id_status_ordem_servico', '!=', 4)
            ->whereIn('ordem_servico.id_ordem_servico', function ($query) {
                $query->select('id_ordem_servico')
                    ->from('ordem_servico_pecas')
                    ->where('situacao_pecas', '!=', 'DEVOLVIDA');
            })
            ->get();
    }

    private function getProdutos()
    {
        return Produto::select('id_produto as value', 'descricao_produto as label')
            ->where('is_ativo', true)
            ->limit(20)
            ->orderBy('descricao_produto')
            ->get();
    }

    public function storeDevProdutos(Request $request)
    {
        //dd($request->all());
        $dados = $request->validate([
            'id_ordem_servico' => 'required',
            'id_filial' => 'required',
            'id_produto' => 'required',
            'quantidade' => 'required',
            'justificativa' => 'required'
        ], [
            'id_ordem_servico.required' => 'O campo Ordem de Serviço é obrigatório.',
            'id_filial.required' => 'O campo Filial é obrigatório.',
            'id_produto.required' => 'O campo Produto é obrigatório.',
            'quantidade.required' => 'O campo Quantidade é obrigatório.',
            'justificativa.required' => 'O campo Justificativa é obrigatório.'
        ]);

        try {
            DB::beginTransaction();

            $devolucaoProduto = new DevolucaoProdutoOrdem();

            $devolucaoProduto->data_inclusao = now();
            $devolucaoProduto->id_filial = $dados['id_filial'];
            $devolucaoProduto->id_ordem_servico = $dados['id_ordem_servico'];
            $devolucaoProduto->id_produto = $dados['id_produto'];
            $devolucaoProduto->quantidade = $dados['quantidade'];
            $devolucaoProduto->justificativa = $dados['justificativa'];

            $devolucaoProduto->save();

            // Registrar histórico de movimentação de estoque
            HistoricoMovimentacaoEstoque::create([
                'id_devolucao' => $devolucaoProduto->id_devolucao_materiais,
                'data_inclusao' => now(),
                'id_produto' => $request->id_produto,
                'quantidade' => $request->quantidade,
                'tipo_movimentacao' => 'entrada',
                'descricao' => "Devolução de produto da OS {$request->id_ordem_servico}",
                'data_movimentacao' => now()
            ]);

            $pecaOrdem = OrdemServicoPecas::where('id_ordem_servico', $request->id_ordem_servico)
                ->where('id_produto', $request->id_produto)
                ->first();

            if ($pecaOrdem) {
                // Subtrai a quantidade devolvida
                $pecaOrdem->quantidade -= $request->quantidade;

                // Evita valores negativos
                if ($pecaOrdem->quantidade <= 0) {
                    $pecaOrdem->quantidade = 0;
                    $pecaOrdem->situacao_pecas = 'DEVOLVIDA';
                } else {
                    $pecaOrdem->situacao_pecas = 'DEVOLVIDA PARCIALMENTE';
                }

                $pecaOrdem->save();
            } else {
                throw new Exception('Peça não encontrada na ordem de serviço.');
            }

            $retorno = $this->atualizarSituacao($request->id_produto, $request->id_ordem_servico, $request->quantidade, $request->id_filial);

            if (!$retorno) {
                throw new Exception('Erro ao atualizar situação do produto.');
            }

            DB::commit();

            return redirect()->route('admin.devolucaosaidaestoque.index')
                ->with('success', 'Devolução de produto registrada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao registrar devolução de produto: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Erro ao registrar devolução de produto.']);
        }
    }

    public function atualizarSituacao($idproduto, $quantidade, $idfilial, $idSolicitacao = 0, $idordem = 0,)
    {

        $saldo = 0;
        try {
            if (!empty($idproduto) && !empty($quantidade) && $idSolicitacao >= 0 && $idordem >= 0) {
                db::beginTransaction();

                $result = ProdutosPorFilial::where('id_produto_unitop', $idproduto)
                    ->where('id_filial', $idfilial)
                    ->first();

                $saldo = ($result->quantidade_produto + $quantidade);

                ProdutosPorFilial::where('id_produto_unitop', $idproduto)
                    ->where('id_filial', $idfilial)
                    ->update(['data_alteracao' => now(), 'quantidade_produto' => $saldo]);

                if (!empty($idordem) && $idordem != 0) {
                    OrdemServicoPecas::where('id_produto', $idproduto)
                        ->where('id_ordem_servico', $idordem)
                        ->update(['data_alteracao' => now(), 'situacao_pecas' => 'DEVOLVIDA', 'jasolicitada' => false]);
                }

                if (!empty($idSolicitacao) && $idSolicitacao != 0) {
                    ProdutosSolicitacoes::where('id_relacao_solicitacoes', $idSolicitacao)
                        ->where('id_protudos', $idproduto)
                        ->update(['data_alteracao' => now(), 'situacao_pecas' => 'DEVOLVIDA']);
                }

                db::commit();

                return true;
            }
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar situação: ' . $e->getMessage());
            db::rollBack();
            return false;
        }
    }


    public function getProdutosPorOrdemServico($id)
    {
        $pecas = OrdemServicoPecas::where('id_ordem_servico', $id)
            ->with('produto')
            ->get();

        // Transformar para o formato value/label
        $produtos = $pecas->map(function ($peca) {
            return [
                'value' => $peca->id_produto,
                'label' => $peca->produto->descricao_produto,
            ];
        });

        return response()->json($produtos);
    }

    public function onDeletePecas($id)
    {
        $parts = explode('&', $id);

        // Extrair o número (primeira parte)
        $key = intval($parts[0]); // ID extraido

        // Extrair o valor booleano
        $confirmed_part = $parts[1]; // "confirmed extraido"
        $confirmed_value = explode('=', $confirmed_part)[1];
        $confirmAction = ($confirmed_value === 'true'); //validando a confirmação

        $dev = explode('=', $parts[2])[1]; // ID extraido $parts[2]; 

        if ($confirmAction) {
            try {
                $excluido = false;
                db::beginTransaction();

                if ($dev != 'mat') {
                    LOG::INFO('Entrou no IF para devoulção de produto');
                    $object = DevolucaoProdutoOrdem::where('id_devolucao_produtos', $key)->first();

                    if ($object) {
                        $id_produto = $object->id_produto;
                        $quantidade = $object->quantidade;
                        $id_filial  = $object->id_filial;
                        $id         = $object->id_ordem_servico;
                    }
                }

                if ($dev == 'mat') {
                    LOG::INFO('Entrou no IF para devoulção de material');
                    $object = DevolucaoMateriais::where('id_devolucao_materiais', $key)->first();
                    log::debug($object);
                    if ($object) {
                        $id_produto = $object->id_produto;
                        $quantidade = $object->quantidade;
                        $id_filial  = $object->id_filial;
                        $id         = $object->id_relacaosolicitacoespecas;
                    }
                }
                LOG::DEBUG([
                    'id_produto' => $id_produto,
                    'quantidade' => $quantidade,
                    'id_filial' => $id_filial,
                    'id' => $id
                ]);
                // Pegar a Quantidade de produtos atual

                $object = ProdutosPorFilial::where('id_produto_unitop', $id_produto)->where('id_filial', $id_filial)->first();
                LOG::DEBUg($object);

                if ($object) {
                    $quantidade_estoque = $object->quantidade_produto;
                }

                if ($quantidade_estoque != 0) {

                    $saldo = ($quantidade_estoque - $quantidade);
                    LOG::DEBUG([
                        'QUATIDADE' => $quantidade,
                        'ESTOQUE' => $quantidade_estoque,
                        'saldo' => $saldo
                    ]);

                    $atualizaEstoque = ProdutosPorFilial::where('id_produto_unitop', $id_produto)
                        ->where('id_filial', $id_filial)
                        ->first();

                    $atualizaEstoque->data_alteracao = now();
                    $atualizaEstoque->quantidade_produto = $saldo;

                    $atualizaEstoque->save();

                    if ($dev != 'mat') {
                        OrdemServicoPecas::where('id_ordem_servico', $id)
                            ->where('id_produto', $id_produto)
                            ->update(['data_alteracao' => now(), 'situacao_pecas' => null, 'jasolicitada' => false]);
                    }

                    if ($dev == 'mat') { //Aqui estou garantido que faça a operação somente se a variavel $dev for 'mat'
                        ProdutosSolicitacoes::where('id_relacao_solicitacoes', $id)
                            ->where('id_protudos', $id_produto)
                            ->update(['data_alteracao' => now(), 'situacao_pecas' => null]);
                    }
                }

                if ($dev != 'mat') {
                    DevolucaoProdutoOrdem::where('id_devolucao_produtos', $key)->delete();
                    $excluido = true;
                }

                if ($dev == 'mat') { //Aqui estou garantido que faça a operação somente se a variavel $dev for 'mat'
                    DevolucaoMateriais::where('id_devolucao_materiais', $key)->delete();
                    $excluido = true;
                }

                if (!$excluido) {
                    throw new \Exception('Erro ao excluir devolução');
                }

                db::commit();

                return response()->json([
                    'success' => true,
                    'status' => 'success',
                    'message' => 'Devolução excluida com sucesso'
                ], 200);
            } catch (\Exception $e) // in case of exception
            {
                // shows the exception error message
                LOG::ERROR('erro ao excluir devolução: ' . $e->getMessage());
                // undo all pending operations
                db::rollback();
            }
        }
    }

    public function createDevMateriais()
    {
        $filiais = $this->getFiliais();
        $requisicoes = $this->getRequisicoes();

        return view('admin.devolucaosaidaestoque.create_devMateriais', compact('filiais', 'requisicoes'));
    }

    private function getRequisicoes()
    {
        $id_filial = GetterFilial();

        return RelacaoSolicitacaoPeca::select('id_solicitacao_pecas as value', DB::raw("CONCAT('Cód. ', id_solicitacao_pecas) as label"))
            ->where('id_filial', $id_filial)
            ->latest('id_solicitacao_pecas')
            ->limit(100)
            ->get();
    }

    public function getProdutosPorSolicitacao($id)
    {
        $pecas = ProdutosSolicitacoes::where('id_relacao_solicitacoes', $id)
            ->with('produto')
            ->get();

        // Transformar para o formato value/label
        $produtos = $pecas->map(function ($peca) {
            return [
                'value' => $peca->id_protudos,
                'label' => $peca->produto->descricao_produto,
            ];
        });

        return response()->json($produtos);
    }

    public function storeDevMateriais(Request $request)
    {
        $dados = $request->validate([
            'id_solicitacao_pecas' => 'required',
            'id_filial' => 'required',
            'id_produto' => 'required',
            'quantidade' => 'required',
            'justificativa' => 'required'
        ], [
            'id_solicitacao_pecas.required' => 'O campo Código Solicitação de Materiais é obrigatório.',
            'id_filial.required' => 'O campo Filial é obrigatório.',
            'id_produto.required' => 'O campo Produto é obrigatório.',
            'quantidade.required' => 'O campo Quantidade é obrigatório.',
            'justificativa.required' => 'O campo Justificativa é obrigatório.'
        ]);

        $usuario = Auth::user()->id;
        $permission = array(1, 10, 22, 25, 32, 85, 92, 318, 366, 1001);

        if (!in_array($usuario, $permission)) {
            return redirect()->back()->with('notification', [
                'type' => 'info',
                'title' => 'Atenção',
                'message' => 'Você não tem permissão para fazer devolução de produtos.',
                'duration' => 3000, // opcional (padrão: 5000ms)
            ]);
        }

        try {
            DB::beginTransaction();

            $devolucaoMaterial = new DevolucaoMateriais();

            $devolucaoMaterial->data_inclusao = now();
            $devolucaoMaterial->id_filial = $dados['id_filial'];
            $devolucaoMaterial->id_relacaosolicitacoespecas = $dados['id_solicitacao_pecas'];
            $devolucaoMaterial->id_produto = $dados['id_produto'];
            $devolucaoMaterial->quantidade = $dados['quantidade'];
            $devolucaoMaterial->justificativa = $dados['justificativa'];

            $devolucaoMaterial->save();

            // Registrar histórico de movimentação de estoque
            HistoricoMovimentacaoEstoque::create([
                'id_devolucao' => $devolucaoMaterial->id_devolucao_materiais,
                'data_inclusao' => now(),
                'id_produto' => $request->id_produto,
                'quantidade' => $request->quantidade,
                'tipo_movimentacao' => 'entrada',
                'descricao' => "Devolução de produto da solicitação {$request->id_solicitacao_pecas}",
                'data_movimentacao' => now()
            ]);

            $retorno = $this->atualizarSituacao($request->id_produto, $request->quantidade, $request->id_filial, $request->id_solicitacao_pecas);

            if (!$retorno) {
                throw new \Exception('Erro ao atualizar situação do produto.');
            }

            DB::commit();

            return redirect()->route('admin.devolucaosaidaestoque.index')
                ->with('success', 'Devolução de produto registrada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao registrar devolução de produto: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Erro ao registrar devolução de produto.']);
        }
    }
}

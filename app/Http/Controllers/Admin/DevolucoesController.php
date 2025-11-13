<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Estoque\Models\TransferenciaDiretaEstoque;
use App\Modules\Estoque\Models\TransferenciaDiretaEstoqueItens;
use App\Modules\Estoque\Models\DevolucaoTransferenciaEstoque;
use App\Modules\Estoque\Models\DevolucaoTransferenciaEstoqueRequisicao;
use App\Models\DevolucaoMatrizItens;
use App\Modules\Estoque\Models\TransferenciaEstoque;
use App\Modules\Estoque\Models\TransferenciaEstoqueItens;
use App\Modules\Estoque\Models\HistoricoMovimentacaoEstoque;
use App\Models\RelacaoSolicitacoesPecas;
use App\Models\Vfilial;
use App\Models\Departamento;
use App\Models\DevolucaoMatriz;
use App\Models\Produto;
use App\Models\ProdutosPorFilial;
use App\Models\ProdutosSolicitacoes;
use App\Models\RelacaoSolicitacaoPeca;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DevolucoesController extends Controller
{
    public function index(Request $request)
    {
        $transfDireta = $this->getTransferenciasDiretas($request);
        $devRequisicao = $this->getDevolucoesRequisicao($request);
        $devMatsMatriz = $this->getDevolucoesMateriaisMatriz($request);

        $departamentos = $this->getDepartamentos();
        $filiais = $this->getFiliais();
        $usuarios = $this->getUsuarios();

        return view('admin.devolucoes.index', compact('transfDireta', 'devRequisicao', 'departamentos', 'filiais', 'devMatsMatriz', 'usuarios'));
    }

    public function getTransferenciasDiretas(Request $request)
    {
        $query = TransferenciaDiretaEstoque::query()
            ->with(['devolucoes', 'usuario', 'departamento', 'filial'])
            ->where('status', 'FINALIZADA');

        $this->applyProdutosFilters($query, $request);

        return $query->latest('id_transferencia_direta_estoque')->paginate(10);
    }

    public function getDevolucoesRequisicao(Request $request)
    {
        $query = RelacaoSolicitacaoPeca::query()
            ->with(['departamentoPecas', 'filial', 'transferenciaEstoqueAux.filialSolicitante', 'devolucoes'])
            ->where('situacao', 'FINALIZADA')
            ->where('id_filial', GetterFilial());

        $this->applyRequisicoesFilters($query, $request);

        return $query->latest('id_solicitacao_pecas')->paginate(10);
    }

    private function getDevolucoesMateriaisMatriz(Request $request)
    {
        $query = TransferenciaEstoque::query()
            ->with(['filial', 'usuario', 'departamento'])
            ->where('aprovado', false)
            ->where('id_filial', GetterFilial());

        $this->applyDevolucoesMatsMatrizFilters($query, $request);

        return $query->latest('id_tranferencia')->paginate(10);
    }

    public function edit_devTransfDireta($id)
    {
        $transferencia = TransferenciaDiretaEstoque::findOrFail($id);
        $devTransfDiretaEstoque = TransferenciaDiretaEstoqueItens::where('id_transferencia_direta_estoque', $id)
            ->with('produto.unidadeProduto')
            ->get();

        $filiais = $this->getFiliais();
        $departamentos = $this->getDepartamentos();

        return view('admin.devolucoes.edit_devTransfDireta', compact('filiais', 'departamentos', 'transferencia', 'devTransfDiretaEstoque'));
    }

    public function update_devTransfDireta(Request $request, $id)
    {
        $transfItens = json_decode($request->input('devTransfDiretaEstoque'), true);
        $idsolicitacao = $id;

        foreach ($transfItens as $item) {
            if ($item['qtde_devolucao'] == 0) {
                continue;
            }

            $idproduto = $item['id_produto'];
            $quantidadebaixa = $item['qtde_devolucao'];
            $qtdBaixaAnterior = empty($this->verificarBaixaAnterior($idsolicitacao, $idproduto)) ? 0 : $this->verificarBaixaAnterior($idsolicitacao, $idproduto);

            if ($idsolicitacao != null || !empty($idsolicitacao)) {
                $this->inserir_itens_devolucao($idsolicitacao, $idproduto, $quantidadebaixa);
            }

            if ($this->verificarsaldo($idproduto, $quantidadebaixa, $qtdBaixaAnterior) == false && $qtdBaixaAnterior < $quantidadebaixa) {

                try {
                    db::beginTransaction();

                    $devolucaoItens = TransferenciaDiretaEstoqueItens::where('id_transferencia_direta_estoque', $idsolicitacao)
                        ->where('id_produto', $idproduto)
                        ->first();

                    $devolucaoItens->data_alteracao = now();
                    $devolucaoItens->qtde_devolucao = $quantidadebaixa;

                    $devolucaoItens->save();

                    $idfilial = GetterFilial();
                    $quantidade = ProdutosPorFilial::where('id_produto_unitop', $idproduto)
                        ->where('id_filial', $idfilial)
                        ->pluck('quantidade_produto')
                        ->first();

                    if (empty($this->verificarBaixaAnterior($idsolicitacao, $idproduto))) {

                        $saldo = $quantidade + $quantidadebaixa;
                    } else if ($qtdBaixaAnterior < $quantidadebaixa) {

                        $quantidadebaixa = $quantidadebaixa - $qtdBaixaAnterior;
                        $saldo = $quantidade + $quantidadebaixa;
                    } else if ($qtdBaixaAnterior > $quantidadebaixa) {

                        return redirect()->back()->with('notification', [
                            'type' => 'info',
                            'title' => 'Atenção',
                            'message' => 'A quantidade da baixa está menor que a baixa anterior, por favor verifique a quantidade a ser baixada pelo sistema.',
                            'duration' => 5000, // opcional (padrão: 5000ms)
                        ]);
                    }

                    $estoque = ProdutosPorFilial::where('id_produto_unitop', $idproduto)
                        ->where('id_filial', $idfilial)
                        ->first();

                    $estoque->data_alteracao = now();
                    $estoque->quantidade_produto = $saldo;

                    $estoque->save();

                    $saldototal = $quantidade + $quantidadebaixa;

                    $historico = new HistoricoMovimentacaoEstoque();

                    $historico->data_inclusao = now();
                    $historico->id_produto    = $idproduto;
                    $historico->id_filial     = $idfilial;
                    $historico->qtde_estoque  = $quantidade;
                    $historico->qtde_entrada  = $quantidadebaixa;
                    $historico->saldo_total   = $saldototal;
                    $historico->id_devolucao  = $devolucaoItens->id_transferencia_direta_estoque_itens;

                    $historico->save();

                    db::commit();
                    return redirect()->route('admin.devolucoes.index')->with('notification', [
                        'type' => 'success',
                        'title' => 'Devolução',
                        'message' => 'Devolução realizada com sucesso.',
                    ]);
                } catch (\Exception $e) {
                    db::rollBack();
                    LOG::ERROR('ERRO AO PROCESSAR DEVULUÇÕES: ' . $e->getMessage());
                }
            } else {
                return redirect()->route('admin.devolucoes.index')->with('notification', [
                    'type' => 'info',
                    'title' => 'ATENÇÃO',
                    'message' => 'Não foi possível fazer a baixa do produto.',
                ]);
            }
        }
    }

    public function edit_devRequisicaoPecas($id)
    {
        $solicitacao = RelacaoSolicitacaoPeca::where('id_solicitacao_pecas', $id)
            ->with('veiculo')
            ->first();

        $devRequisicaoPecas = ProdutosSolicitacoes::where('id_relacao_solicitacoes', $id)
            ->with('produto.unidadeProduto')
            ->get();

        $filiais = $this->getFiliais();
        $departamentos = $this->getDepartamentos();

        return view('admin.devolucoes.edit_devRequisicaoPecas', compact('solicitacao', 'filiais', 'departamentos', 'devRequisicaoPecas'));
    }

    public function update_devRequisicaoPecas(Request $request, $id)
    {
        $devRequisicaoPecas = json_decode($request->input('devRequisicaoPecas'), true);
        try {
            foreach ($devRequisicaoPecas as $item) {
                if ($item['quantidade_baixa'] == 0) {
                    continue;
                }

                $idproduto         = $item['id_protudos'];
                $idsolicitacao     = $item['id_relacao_solicitacoes'];
                $quantidadebaixa   = $item['quantidade_baixa'];
                $quantidade_requisitada = intval($item['quantidade']);
                $qtdBaixaAnterior  = empty($this->verificarBaixaAnterior($idsolicitacao, $idproduto)) ? 0 : $this->verificarBaixaAnterior($idsolicitacao, $idproduto);
                $quantidade = null;
                $saldo = null;
                $saldototal = null;

                if ($idsolicitacao != null || !empty($idsolicitacao)) {
                    $this->inserir_devolucao_requisicao($idsolicitacao, $idproduto);
                }

                if ($this->verificarsaldo($idproduto, $quantidadebaixa, $qtdBaixaAnterior) == false && $qtdBaixaAnterior < $quantidadebaixa) {

                    db::beginTransaction();

                    $devolucao = DevolucaoTransferenciaEstoqueRequisicao::where('id_relacao_solicitacoes', $idsolicitacao)
                        ->where('id_protudos', $idproduto)
                        ->first();

                    if ($devolucao) {
                        $devolucao->data_alteracao = now();
                        $devolucao->qtde_devolucao = $quantidadebaixa;
                        $devolucao->save();
                    } else {
                        // Se não existe, você precisa criar ou tratar o erro
                        Log::warning("Devolução não encontrada para id_solicitacao {$idsolicitacao} e produto {$idproduto}");

                        $devolucao = DevolucaoTransferenciaEstoqueRequisicao::create([
                            'id_relacao_solicitacoes' => $idsolicitacao,
                            'id_protudos' => $idproduto,
                            'qtde_devolucao' => $quantidadebaixa,
                            'data_alteracao' => now(),
                        ]);
                    }


                    $idfilial = GetterFilial();

                    $quantidade = ProdutosPorFilial::where('id_produto_unitop', $idproduto)
                        ->where('id_filial', $idfilial)
                        ->pluck('quantidade_produto')
                        ->first();

                    if (empty($this->verificarBaixaAnterior($idsolicitacao, $idproduto))) {

                        $saldo = $quantidade + $quantidadebaixa;
                    } else if ($qtdBaixaAnterior < $quantidadebaixa) {

                        $quantidadebaixa = $quantidadebaixa - $qtdBaixaAnterior;
                        $saldo = $quantidade + $quantidadebaixa;
                    } else if ($qtdBaixaAnterior > $quantidadebaixa) {

                        return redirect()->back()->with('notification', [
                            'type' => 'info',
                            'title' => 'Atenção',
                            'message' => 'A quantidade da baixa está menor que a baixa anterior, por favor verifique a quantidade a ser baixada pelo sistema.',
                            'duration' => 5000, // opcional (padrão: 5000ms)
                        ]);
                    }

                    $estoque = ProdutosPorFilial::where('id_produto_unitop', $idproduto)
                        ->where('id_filial', $idfilial)
                        ->first();

                    $estoque->data_alteracao     = now();
                    $estoque->quantidade_produto = $saldo;
                    $estoque->save();

                    $saldototal = $quantidade + $quantidadebaixa;

                    $historico = new HistoricoMovimentacaoEstoque();

                    $historico->data_inclusao = now();
                    $historico->id_produto    = $idproduto;
                    $historico->id_filial     = $idfilial;
                    $historico->qtde_estoque  = $quantidade;
                    $historico->qtde_entrada  = $quantidadebaixa;
                    $historico->saldo_total   = $saldototal;
                    $historico->id_devolucao  = $devolucao->id_devolucao_transferencia_estoque_requisicao;

                    $historico->save();



                    if ($this->verificarQtdTodosPecas($idsolicitacao) == TRUE) {
                        $sitPecas = DevolucaoTransferenciaEstoqueRequisicao::where('id_relacao_solicitacoes', $idsolicitacao)->first();
                        $sitPecas->situacao_pecas = 'DEVOLUCAO COMPLETA';
                        $sitPecas->save();
                    } else {
                        $sitPecas = DevolucaoTransferenciaEstoqueRequisicao::where('id_relacao_solicitacoes', $idsolicitacao)->first();
                        $sitPecas->situacao_pecas = 'DEVOLUCAO PARCIAL';
                        $sitPecas->save();
                    }

                    db::commit();

                    return redirect()->back()->with('notification', [
                        'type' => 'success',
                        'title' => 'Atenção',
                        'message' => 'baixa realizada com sucesso.',
                        'duration' => 5000, // opcional (padrão: 5000ms)
                    ]);
                } else {
                    return redirect()->back()->with('notification', [
                        'type' => 'info',
                        'title' => 'Atenção',
                        'message' => 'Atenção, não será possível fazer a baixa do produto.',
                        'duration' => 5000, // opcional (padrão: 5000ms)
                    ]);
                }
            }
        } catch (\Exception $e) {
            LOG::ERROR('ERRO AO PROCESSAR DEVOLUÇÕES DE REQUISIÇÕES DE PEÇAS: ' . $e->getMessage());
        }
    }

    public function edit_devMatsMatriz($id)
    {
        $devMatsMatriz = DevolucaoMatriz::where('id_transferencia_estoque', $id)
            ->with(['usuario'])
            ->first();

        if (empty($devMatsMatriz)) {
            return redirect()->back()->with('notification', [
                'type' => 'error',
                'title' => 'Atenção',
                'message' => 'Devolução não encontrada.',
            ]);
        }

        $devMatsMatrizItens = DevolucaoMatrizItens::where('id_devolucao_matriz', $devMatsMatriz->id)
            ->with(['produto.unidadeProduto', 'estoque'])
            ->get();

        return view('admin.devolucoes.edit_devMatsMatriz', compact('devMatsMatriz', 'devMatsMatrizItens'));
    }

    public function update_devMatsMatriz(Request $request, $id)
    {
        $devMatsMatrizItens = json_decode($request->input('devMatsMatrizItens'), true);
        foreach ($devMatsMatrizItens as $item) {

            try {
                db::beginTransaction();

                $devolucao = DevolucaoMatrizItens::where('id', $item['id'])
                    ->where('id_produto', $item['id_produto'])
                    ->first();

                $devolucao->data_alteracao = now();
                $devolucao->qtd_disponivel_envio = $item['qtd_disponivel_envio'];
                $devolucao->qtd_enviada = $item['qtd_enviada'];

                $devolucao->save();

                db::commit();
                return redirect()->route('admin.devolucoes.index')->with('notification', [
                    'type' => 'success',
                    'title' => 'Atenção',
                    'message' => 'Devolução de matriz atualizada com sucesso.',
                ]);
            } catch (\Exception $e) {
                db::rollBack();
                LOG::ERROR('Erro ao atualizar devolução de matriz: ' . $e->getMessage());
                return redirect()->back()->with('notification', [
                    'type' => 'error',
                    'title' => 'Atenção',
                    'message' => 'Erro ao atualizar devolução de matriz: ' . $e->getMessage(),
                ]);
            }
        }
    }

    //------------------------ CUSTOM FUNCTIONS ------------------------
    public function inserir_itens_devolucao($idsolicitacao, $id_produto, $qtd_devolucao)
    {
        try {
            $tem_registro = null;

            $tem_registro = DevolucaoTransferenciaEstoque::where('id_transferencia_direta_estoque', $idsolicitacao)
                ->where('id_produto', $id_produto)
                ->select('id_transferencia_direta_estoque_itens')
                ->first();

            if (!isset($tem_registro) || empty($tem_registro)) {
                db::begintransaction();

                $result = TransferenciaDiretaEstoqueItens::where('id_transferencia_direta_estoque', $idsolicitacao)
                    ->where('id_produto', $id_produto)
                    ->select('id_transferencia_direta_estoque_itens')
                    ->first();

                $devolucao = new DevolucaoTransferenciaEstoque();

                $devolucao->data_inclusao                         = now();
                $devolucao->id_transferencia_direta_estoque       = $idsolicitacao;
                $devolucao->id_transferencia_direta_estoque_itens = $result->id_transferencia_direta_estoque_itens;
                $devolucao->id_produto                            = $result->id_produto;
                $devolucao->stauts                                = 'DEVOLUCAO INICIADA';
                $devolucao->qtde_devolucao                        = $qtd_devolucao;
                $devolucao->qtd_baixa                             = $result->qtd_baixa;

                $devolucao->save();

                db::commit();
            };
        } catch (\Exception $e) {
            db::rollBack();
            LOG::ERROR('Erro ao inserir itens de devolução: ' . $e->getMessage());
        }
    }

    public function inserir_devolucao_requisicao($idsolicitacao, $id_produto)
    {
        try {
            $tem_registro = null;

            $tem_registro = DevolucaoTransferenciaEstoqueRequisicao::where('id_relacao_solicitacoes', $idsolicitacao)
                ->where('id_protudos', $id_produto)
                ->select('id_produtos_solicitacoes')
                ->first();

            if (!isset($tem_registro) || empty($tem_registro)) {
                $idProdutosSolicitacoes = ProdutosSolicitacoes::where('id_relacao_solicitacoes', $idsolicitacao)
                    ->where('id_protudos', $id_produto)
                    ->select('id_produtos_solicitacoes')
                    ->first();

                $devolucao = new DevolucaoTransferenciaEstoqueRequisicao();

                $devolucao->id_relacao_solicitacoes  = $idsolicitacao;
                $devolucao->id_produtos_solicitacoes = $idProdutosSolicitacoes->id_produtos_solicitacoes;
                $devolucao->situacao_pecas           = 'DEVOLUCAO INICIADA';
                $devolucao->id_protudos              = $id_produto;
                $devolucao->data_inclusao            = now();
                $devolucao->quantidade_baixa         = $idProdutosSolicitacoes->quantidade_baixa;

                $devolucao->save();

                db::commit();
            }
        } catch (\Exception $e) {
            db::rollBack();
            LOG::ERROR('Erro ao inserir devolução requisicao: ' . $e->getMessage());
        }
    }

    public function verificarsaldo($idproduto, $quantibaixa, $qtdBaixaAnterior)
    {
        $quantidadesaldo = null;
        $filial = GetterFilial();

        $quantibaixa = $quantibaixa - $qtdBaixaAnterior;

        $quantidadesaldo = ProdutosPorFilial::where('id_produto_unitop', $idproduto)
            ->where('id_filial', $filial)
            ->pluck('quantidade_produto')
            ->first();

        if ($quantidadesaldo < $quantibaixa) {
            return true;
        } else {
            return false;
        }
    }

    public function verificarBaixaAnterior($idSolicitacao, $idProduto)
    {
        $retorno = DevolucaoTransferenciaEstoque::where('id_transferencia_direta_estoque', $idSolicitacao)
            ->where('id_produto', $idProduto)
            ->select('qtd_baixa')
            ->first();

        return $retorno;
    }

    public function verificarQtdTodosPecas($idSolicitacao)
    {
        $objects = DB::connection('pgsql')->select("SELECT
                                (CASE WHEN (qtde_devolucao = quantidade_baixa) THEN TRUE
                                    ELSE FALSE END) AS verificar
                                    FROM devolucao_transferencia_estoque_requisicao
                                    WHERE id_relacao_solicitacoes = $idSolicitacao");

        if ($objects) {
            foreach ($objects as $object) {
                $retorno[] = $object->verificar;
            }
        }

        if (in_array(false, $retorno)) {
            return false;
        } else {
            return true;
        }
    }

    private function applyProdutosFilters($query, Request $request)
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
            'id_transferencia_direta_estoque' => 'id_transferencia_direta_estoque',
            'status' => 'status',
        ];

        foreach ($filters as $requestField => $dbField) {
            if ($request->filled($requestField)) {
                $query->where($dbField, $request->input($requestField));
            }
        }
    }

    public function applyRequisicoesFilters($query, $request)
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

        if ($request->filled('id_filial_solicitante')) {
            $query->whereHas('transferenciaEstoqueAux.filialSolicitante', function ($query) use ($request) {
                $query->where('id_filial_solicita', $request->id_filial_solicitante);
            });
        }

        $filters = [
            'id_solicitacao_pecas' => 'id_solicitacao_pecas',
            'id_departamento' => 'id_departamento',
        ];

        foreach ($filters as $requestField => $dbField) {
            if ($request->filled($requestField)) {
                $query->where($dbField, $request->input($requestField));
            }
        }
    }

    public function applyDevolucoesMatsMatrizFilters($query, $request)
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

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }

        if ($request->filled('id_departamento')) {
            $query->where('id_departamento', $request->input('id_departamento'));
        }
    }

    private function getFiliais()
    {
        return VFilial::select('id as value', 'name as label')
            ->orderBy('name')
            ->get();
    }

    private function getDepartamentos()
    {
        return Departamento::select('id_departamento as value', 'descricao_departamento as label')
            ->orderBy('descricao_departamento')
            ->get();
    }

    private function getUsuarios()
    {
        return User::where('is_ativo', true)
            ->select('id as value', 'name as label')
            ->orderBy('name')
            ->get();
    }

    public function onGerarDevolucao($id)
    {
        try {
            if (!empty($id)) {
                $idDevolucao = null;
                $devolucao = DevolucaoMatriz::where('id_transferencia_estoque', '=', $id)->first();
                $transferencia = TransferenciaEstoque::where('id_tranferencia', '=', $id)->first();

                if (isset($devolucao)) {
                    $idDevolucao = $devolucao->id;
                }

                log::debug($transferencia);
                if (!isset($idDevolucao)) {
                    try {
                        db::beginTransaction();
                        $object = new DevolucaoMatriz();

                        $object->data_inclusao            = now();
                        $object->id_transferencia_estoque = $id;
                        $object->id_filial                = $transferencia->id_filial;
                        $object->id_user_solicitante      = $transferencia->id_usuario;
                        $object->aprovado                 = false;
                        $object->liberado                 = false;
                        $object->observaocao              = $transferencia->observao_solicitacao;
                        $object->transferencia_feita      = false;
                        $object->id_departamento          = $transferencia->id_departamento;
                        $object->save();

                        db::commit();

                        $id_ = $object->id;
                    } catch (\Exception $e) {
                        db::rollBack();
                        Log::error('Erro ao gerar devolução: ' . $e->getMessage());
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Erro ao gerar devolução: ' . $e->getMessage(),
                        ]);
                    }
                    $itens = TransferenciaEstoqueItens::where('id_transferencia', $id)->get();

                    if ($itens && $id_) {
                        try {
                            db::beginTransaction();

                            foreach ($itens as $item) {

                                $object = new DevolucaoMatrizItens();

                                $object->id_devolucao_matriz  = $id_;
                                $object->id_produto           = $item->id_produto;
                                $object->qtd_enviada          = $item->quantidade_baixa ? $item->quantidade_baixa : 0;
                                $object->qtd_disponivel_envio = $item->quantidade ? $item->quantidade : 0;

                                $object->save();
                            }

                            db::commit();

                            return response()->json([
                                'success' => true,
                                'message' => 'Devolução de matriz gerada com sucesso.',
                                'id_devolucao' => $id_,
                            ]);
                        } catch (\Exception $e) {
                            db::rollBack();
                            Log::error('Erro ao gerar devolução de matriz: ' . $e->getMessage());

                            return response()->json([
                                'status' => 'error',
                                'message' => 'Erro ao gerar devolução de matriz: ' . $e->getMessage(),
                            ]);
                        }
                    }
                }
                return response()->json(['success' => true, 'message' => 'Devolução já existe para esta transferência.'], 200);
            }
        } catch (\Exception $e) {
            Log::error('error ao gerar devolucao de matriz', $e->getMessage());
        }
    }

    public function getDadosDevolucaoMatriz($id)
    {
        $devolucao = DevolucaoMatriz::where('id_transferencia_estoque', $id)->first();
        $devItens = DevolucaoMatrizItens::where('id_devolucao_matriz', $devolucao->id)
            ->with(['produto.unidadeProduto', 'estoque'])
            ->get();

        if (!$devolucao) {
            return response()->json(['success' => false, 'message' => 'Devolução não encontrada.'], 404);
        }

        return response()->json(['success' => true, 'devolucao' => $devItens]);
    }

    public function onGerarTransferencia(Request $request)
    {
        log::debug($request->all());
        try {
            if (!empty($request->itens)) {
                $produtos = [];
                foreach ($request->itens as $check_id) {
                    $produtos[] = $check_id;
                }

                $idProdParam = '{' . implode(',', $produtos) . '}';

                $key = $request->id_transferencia_estoque;

                $result = DB::select("SELECT * FROM fc_inserir_devolucao_matriz(?,(?))", [$key, $idProdParam]);

                return response()->json(['success' => true, 'message' => 'Registros atualizados!', 'result' => $result]);
            } else {
                return response()->json(['success' => false, 'message' => 'Nenhum item selecionado.']);
            }
        } catch (Exception $e) {
            Log::error('error', ['exception' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao gerar transferência.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}

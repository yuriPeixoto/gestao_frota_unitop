<?php

namespace App\Modules\Estoque\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Estoque\Models\AcertoEstoque;
use App\Modules\Configuracoes\Models\Filial;
use App\Models\Produto;
use App\Modules\Estoque\Models\Estoque;
use App\Modules\Estoque\Models\HistoricoMovimentacaoEstoque;
use App\Models\ProdutosPorFilial;
use App\Modules\Estoque\Models\TipoAcertoEstoque;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AjusteEstoqueController extends Controller
{
    public function index(Request $request)
    {
        $ajustes = $this->getAcertos($request);
        $filial = $this->getFilial();
        $produto = $this->getProduto();
        $tipoAcerto = $this->getTipoAcerto();

        return view('admin.ajusteEstoque.index', compact('ajustes', 'filial', 'produto', 'tipoAcerto'));
    }

    public function getAcertos(Request $request)
    {
        $query = AcertoEstoque::query()
            ->with('filial', 'estoque', 'produto', 'tipo_acerto');

        $this->applyAcertoEstoqueFilters($query, $request);

        return $query->latest('data_inclusao')->paginate(10);
    }

    public function applyAcertoEstoqueFilters($query, $request)
    {
        // Filtro de data com tratamento de erro
        if ($request->filled('data_inicial') && $request->filled('data_inicial')) {
            try {
                $dataInicial = Carbon::createFromFormat('Y-m-d', $request->input('data_inicial'))
                    ->startOfDay();
                $dataFinal = Carbon::createFromFormat('Y-m-d', $request->input('data_final'))
                    ->endOfDay();

                $query->whereBetween('data_inclusao', [$dataInicial, $dataFinal]);
            } catch (\Exception $e) {
                Log::error('Erro no filtro de data: ' . $e->getMessage());
            }
        }

        $filters = [
            'id_acerto_estoque' => 'id_acerto_estoque',
            'id_filial' => 'id_filial',
            'id_produto' => 'id_produto',
            'id_tipo_acerto' => 'id_tipo_acerto',
            'quantidade_acerto' => 'quantidade_acerto'
        ];

        foreach ($filters as $requestField => $dbField) {
            if ($request->filled($requestField)) {
                $query->where($dbField, $request->input($requestField));
            }
        }
    }


    public function create()
    {
        $tipoAcerto = $this->getTipoAcerto();
        $filiais = $this->getFilial();
        return view('admin.ajusteEstoque.create', compact('tipoAcerto', 'filiais'));
    }

    public function store(Request $request)
    {
        $dados = $this->validation($request);

        try {
            db::beginTransaction();

            $acerto = new AcertoEstoque();

            $acerto->data_inclusao     = now();
            $acerto->id_filial         = $request->id_filial;
            $acerto->id_estoque        = $request->id_estoque;
            $acerto->id_produto        = $dados['id_produto'];
            $acerto->id_tipo_acerto    = $dados['id_tipo_acerto'];
            $acerto->quantidade_acerto = $dados['quantidade_acerto'];
            $acerto->preco_medio       = $dados['preco_medio'] ?? 0;
            $acerto->data_acerto       = $dados['data_acerto'];
            $acerto->quantidade_atual  = $request->quantidade_atual;
            $acerto->id_usuario_acerto = Auth::user()->id;

            $acerto->save();

            //Insert no historico movimentação estoque
            $historico = new HistoricoMovimentacaoEstoque();

            $historico->data_inclusao = now();
            $historico->id_produto    = $dados['id_produto'];
            $historico->id_filial     = $request->id_filial;
            $historico->qtde_estoque  = $request->quantidade_atual;
            $historico->saldo_total   = $dados['quantidade_acerto'];
            $historico->id_acerto     = $acerto->id_acerto_estoque;

            $historico->save();

            //Ajusta estoque produto
            $estoque = ProdutosPorFilial::where('id_produto_unitop', $dados['id_produto'])->where('id_filial', $request->id_filial)->first();

            $estoque->data_alteracao     = now();
            $estoque->quantidade_produto = $dados['quantidade_acerto'];
            $estoque->valor_medio        = $dados['preco_medio'];

            $estoque->save();

            db::commit();

            return redirect()->route('admin.ajusteEstoque.index')->with('success', 'Acerto de estoque registrado com sucesso!');
        } catch (\Exception $e) {
            LOG::ERROR('Erro ao registrar acerto de estoque: ' . $e->getMessage());
            db::rollBack();
            return redirect()->back()->with('error', 'Erro ao registrar acerto de estoque: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $ajuste = AcertoEstoque::findOrFail($id);
        $tipoAcerto = $this->getTipoAcerto();
        $filiais = $this->getFilial();

        return view('admin.ajusteEstoque.edit', compact('ajuste', 'tipoAcerto', 'filiais'));
    }

    public function update(Request $request, $id)
    {
        $dados = $this->validation($request);

        try {
            db::beginTransaction();

            $acerto = AcertoEstoque::findOrFail($id);

            $acerto->data_inclusao     = now();
            $acerto->id_filial         = $request->id_filial;
            $acerto->id_estoque        = $request->id_estoque;
            $acerto->id_produto        = $dados['id_produto'];
            $acerto->id_tipo_acerto    = $dados['id_tipo_acerto'];
            $acerto->quantidade_acerto = $dados['quantidade_acerto'];
            $acerto->preco_medio       = $dados['preco_medio'] ?? 0;
            $acerto->data_acerto       = $dados['data_acerto'];
            $acerto->quantidade_atual  = $request->quantidade_atual;
            $acerto->id_usuario_acerto = Auth::user()->id;

            $acerto->save();

            //Insert no historico movimentação estoque
            $historico = HistoricoMovimentacaoEstoque::where('id_produto', $dados['id_produto'])
                ->where('id_filial', $request->id_filial)
                ->where('id_acerto', $acerto->id_acerto_estoque)
                ->first();

            $historico->data_alteracao = now();
            $historico->id_produto     = $dados['id_produto'];
            $historico->id_filial      = $request->id_filial;
            $historico->qtde_estoque   = $request->quantidade_atual;
            $historico->saldo_total    = $dados['quantidade_acerto'];
            $historico->id_acerto      = $acerto->id_acerto_estoque;

            $historico->save();

            //Ajusta estoque produto
            $estoque = ProdutosPorFilial::where('id_produto_unitop', $dados['id_produto'])->where('id_filial', $request->id_filial)->first();

            $estoque->data_alteracao     = now();
            $estoque->quantidade_produto = $dados['quantidade_acerto'];
            $estoque->valor_medio        = $dados['preco_medio'];

            $estoque->save();

            db::commit();
            return redirect()->route('admin.ajusteEstoque.index')->with('success', 'Acerto de estoque atualizado com sucesso!');
        } catch (\Exception $e) {
            LOG::ERROR('Erro ao atualizar acerto de estoque: ' . $e->getMessage());
            db::rollBack();
            return redirect()->back()->with('error', 'Erro ao atualizar acerto de estoque: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            db::beginTransaction();

            $ultimo_id = AcertoEstoque::max('id_acerto_estoque');
            $dados = AcertoEstoque::findOrFail($id);

            if ($ultimo_id != $dados->id_acerto_estoque) {
                return response()->json([
                    'notification' => [
                        'title'   => 'Atenção',
                        'type'    => 'error',
                        'message' => 'Só é permitido exclusão do ultimo acerto'
                    ]
                ]);
            } else {

                $estoque = ProdutosPorFilial::where('id_produto_unitop', $dados->id_produto)
                    ->where('id_filial', $dados->id_filial)
                    ->first();
                $historico = HistoricoMovimentacaoEstoque::where('id_produto', $dados->id_produto)
                    ->where('id_filial', $dados->id_filial)
                    ->where('id_acerto', $dados->id_acerto_estoque)
                    ->first();


                log::debug([
                    'quantidade_anterior' => $historico->qtde_estoque,
                    'quantidade_acerto' => $dados->quantidade_acerto,
                    'estoque' => $estoque->quantidade_produto
                ]);

                $estoque->data_alteracao     = now();
                $estoque->quantidade_produto = $historico->qtde_estoque;
                $estoque->valor_medio        = $dados->preco_medio;

                $estoque->save();

                $historico = new HistoricoMovimentacaoEstoque();

                $historico->data_inclusao = now();
                $historico->id_produto    = $dados->id_produto;
                $historico->id_filial     = $dados->id_filial;
                $historico->qtde_estoque  = $estoque->quantidade_produto;
                $historico->saldo_total   = $historico->qtde_estoque;
                $historico->id_acerto     = $dados->id_acerto_estoque;

                $historico->save();

                $dados->delete();

                db::commit();

                return response()->json([
                    'notification' => [
                        'title'   => 'Ajuste excluído!',
                        'type'    => 'success',
                        'message' => 'Ajuste excluído com sucesso'
                    ]
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir Ajuste: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o Ajuste: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function getFilial()
    {
        return Filial::select('id as value', 'name as label')->get()->toArray();
    }

    public function getProduto()
    {
        return Produto::select('id_produto as value', 'descricao_produto as label')->orderByDesc('descricao_produto')->limit(20)->get();
    }

    public function getTipoAcerto()
    {
        return TipoAcertoEstoque::select('id_tipo_acerto_estoque as value', 'descricao_tipo_acerto as label')->get();
    }

    public function getEstoque()
    {
        return Estoque::select('id_estoque as value', 'descricao_estoque as label')->get();
    }

    public function getEstoqueByFilial($id_filial)
    {
        return Estoque::select('id_estoque as value', 'descricao_estoque as label')->where('id_filial', $id_filial)->get();
    }

    public function getProdutoByEstoque($id_filial, $id_estoque)
    {
        return Produto::select('id_produto as value', 'descricao_produto as label')
            ->where('id_estoque_produto', $id_estoque)
            ->where('id_filial', $id_filial)
            ->where('is_ativo', true)
            ->get();
    }

    public function getEstoqueByProduto($id_filial, $id_produto)
    {
        return ProdutosPorFilial::select('quantidade_produto', 'valor_medio')
            ->where('id_produto_unitop', $id_produto)
            ->where('id_filial', $id_filial)
            ->where('is_ativo', true)
            ->get();
    }

    public function validation(Request $request)
    {
        return $request->validate([
            'data_acerto' => 'required|date',
            'id_tipo_acerto' => 'required',
            'id_estoque' => 'required',
            'id_produto' => 'required',
            'quantidade_acerto' => 'required',
            'preco_medio' => 'required'
        ], [
            'data_acerto.required' => 'A data do acerto é obrigatória.',
            'data_acerto.date' => 'A data do acerto deve ser uma data válida.',
            'id_tipo_acerto.required' => 'O tipo de acerto é obrigatório.',
            'id_estoque.required' => 'O estoque é obrigatório.',
            'id_produto.required' => 'O produto é obrigatório.',
            'quantidade_acerto.required' => 'A quantidade do acerto é obrigatória.',
            'preco_medio.required' => 'O preço médio é obrigatório.',
        ]);
    }
}

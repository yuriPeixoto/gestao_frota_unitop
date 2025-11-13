<?php

namespace App\Modules\Estoque\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Estoque\Models\Estoque;
use App\Models\Filial;
use App\Models\GrupoServico;
use App\Models\ModeloPneu;
use App\Models\ModeloVeiculo;
use App\Models\Produto;
use App\Models\ProdutoXaplicacao;
use App\Models\SubgrupoServico;
use App\Modules\Imobilizados\Models\TipoImobilizado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\UnidadeProduto;
use Illuminate\Http\Request;
use App\Models\ProdutosPorFilial;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Traits\SanitizesMonetaryValues;
use Illuminate\Support\Facades\Storage;


class CadastroProdutosEstoqueController extends Controller
{
    use SanitizesMonetaryValues;

    public function index(Request $request)
    {
        // Construção da query inicial com Eloquent
        $query = ProdutosPorFilial::query()
            ->with(['produto.usuarioEdicao', 'produto.usuarioCadastro'])
            ->where('produtos_por_filial.id_filial', '=', GetterFilial());

        // Aplicar filtros do formulário de busca
        if ($request->filled('id_produto')) {
            $query->where('id_produto_unitop', '=', $request->input('id_produto'));
        }

        if ($request->filled('descricao')) {
            $query->whereHas('produto', function ($query) use ($request) {
                // Correto
                $query->whereRaw('lower(descricao_produto) like ?', ['%' . Str::lower($request->descricao) . '%']);
            });
        }


        if ($request->filled('cod_fabricante_')) {
            $query->whereHas('produto', function ($query) use ($request) {
                $query->where('cod_fabricante_', 'like', '%' . $request->cod_fabricante_ . '%');
            });
        }

        if ($request->filled('cod_alternativo_1_')) {
            $query->whereHas('produto', function ($query) use ($request) {
                $query->where('cod_alternativo_1_', 'like', '%' . $request->cod_alternativo_1_ . '%');
            });
        }

        if ($request->filled('cod_alternativo_2_')) {
            $query->whereHas('produto', function ($query) use ($request) {
                $query->where('cod_alternativo_2_', 'like', '%' . $request->cod_alternativo_2_ . '%');
            });
        }



        // Paginação
        $cadastroProdutos = $query->orderByDesc('id_produto_unitop')->paginate(10);

        // Retornar a view com os dados
        return view('admin.cadastroprodutosestoque.index', compact('cadastroProdutos'));
    }

    public function create()
    {
        $formOptions = [
            'estoque'           => Estoque::select('descricao_estoque as label', 'id_estoque as value')->orderBy('label')->get()->toArray(),
            'grupoServico'      => GrupoServico::select('descricao_grupo as label', 'id_grupo as value')->orderBy('label')->get()->toArray(),
            'subgrupoServico'   => SubgrupoServico::select('descricao_subgrupo as label', 'id_subgrupo as value')->orderBy('label')->get()->toArray(),
            'filiais'           => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'modeloVeiculo'     => ModeloVeiculo::select('descricao_modelo_veiculo as label', 'id_modelo_veiculo as value')->where('ativo', true)->orderBy('label')->get()->toArray(),
            'unidadeProduto'    => UnidadeProduto::select('descricao_unidade as label', 'id_unidade_produto as value')->orderBy('label')->get()->toArray(),
            'tipoImobilizado'   => TipoImobilizado::select('descricao_tipo_imobilizados as label', 'id_tipo_imobilizados as value')->orderBy('label')->get()->toArray(),
            'modelopneu'        => ModeloPneu::select('descricao_modelo as label', 'id_modelo_pneu as value')->orderBy('label')->get()->toArray(),
        ];

        return view('admin.cadastroprodutosestoque.create', compact('formOptions'));
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $produtoDados = $request->validate([
            'id_filial'                 => 'required|integer',
            'descricao_produto'         => 'required|string|max:255',
            'marca'                     => 'required|string',
            'modelo'                    => 'required|string',
            'imagem_produto'            => 'nullable|image|mimes:jpeg,jpg,png|max:1024',
            'pre_cadastro'              => 'required',
            'id_unidade_produto'        => 'required',
            'id_grupo_servico'          => 'required',
            'quantidade_atual_produto'  => 'required',
            'is_ativo'                  => 'required',
        ], [
            'id_filial.required' => 'O campo Filial é obrigatório.',
            'descricao_produto.required' => 'O campo Descrição do Produto é obrigatório.',
            'marca.required' => 'O campo Marca é obrigatório.',
            'modelo.required' => 'O campo Modelo é obrigatório.',
            'pre_cadastro.required' => 'O campo PreCadastro é obrigatório.',
            'id_unidade_produto.required' => 'O campo Unidade de Produto é obrigatório.',
            'id_grupo_servico.required' => 'O campo Grupo de Serviço é obrigatório.',
            'id_tipo_imobilizados.required' => 'O campo Tipo de Imobilizado é obrigatório.',
            'quantidade_atual_produto.required' => 'O campo Quantidade Atual do Produto é obrigatório.',
            'is_ativo.required' => 'O campo Ativo é obrigatório.'
        ]);


        $arquivoProduto = null;
        if ($request->hasFile('imagem_produto')) {
            try {
                $arquivo = $request->file('imagem_produto');
                $arquivoProduto = $arquivo->store('produtos', 'public');
            } catch (\Exception $e) {
                Log::error('Erro ao processar arquivo do produtos: ' . $e->getMessage());
            }
        }

        $cadastroProdutoinput = json_decode($request->input('cad_produtos', '[]'), true);

        try {
            DB::beginTransaction();

            $produto = new Produto();

            $produto->id_filial                = $produtoDados['id_filial'];
            $produto->data_inclusao           = now();
            $produto->descricao_produto        = $produtoDados['descricao_produto'];
            $produto->is_original              = $request->is_original ?? false;
            $produto->curva_abc                = $request->curva_abc ?? null;
            $produto->tempo_garantia           = $request->tempo_garantia ?? null;
            $produto->id_unidade_produto       = $produtoDados['id_unidade_produto'];
            $produto->ncm                      = $request->ncm ?? null;
            $produto->estoque_minimo           = $request->estoque_minimo ?? null;
            $produto->estoque_maximo           = $request->estoque_maximo ?? null;
            $produto->localizacao_produto      = $request->localizacao_produto ?? null;
            $produto->quantidade_atual_produto = 0;

            // ← CORREÇÃO: Só atualizar imagem se houver arquivo novo
            if ($arquivoProduto !== null) {
                $produto->imagem_produto = $arquivoProduto;
            }

            $produto->id_estoque_produto       = $request->id_estoque_produto ?? null;
            $produto->id_grupo_servico         = $produtoDados['id_grupo_servico'];
            $produto->id_produto_subgrupo      = $request->id_produto_subgrupo ?? null;
            $produto->cod_fabricante_          = $request->cod_fabricante_ ?? null;
            $produto->cod_alternativo_1_       = $request->cod_alternativo_1_;
            $produto->cod_alternativo_2_       = $request->cod_alternativo_2_;
            $produto->cod_alternativo_3_       = $request->cod_alternativo_3_;
            $produto->id_modelo_pneu           = $request->id_modelo_pneu ?? null;
            $produto->is_ativo                 = $produtoDados['is_ativo'] ?? True;
            $produto->modelo                   = $produtoDados['modelo'];
            $produto->marca                    = $produtoDados['marca'];
            $produto->pre_cadastro             = $produtoDados['pre_cadastro'];
            $produto->id_user_cadastro           = Auth::user()->id;
            $produto->is_fracionado            = $request->is_fracionado ?? false;

            $produto->save();

            $idsModelosVeiculosAtuais = [];


            // Itera sobre o array de modelos de veículos
            foreach ($cadastroProdutoinput as $modeloVeiculo) {
                // Verifica se já existe o relacionamento
                $produtoXaplicacao = ProdutoXaplicacao::where('id_produto', $produto->id_produto)
                    ->where('id_modelo_veiculo', $modeloVeiculo['id_modelo_veiculo'])
                    ->first();

                if ($produtoXaplicacao) {
                    // Atualiza o registro existente
                    $produtoXaplicacao->update([
                        'data_alteracao' => now(),
                    ]);
                } else {
                    // Cria um novo relacionamento
                    ProdutoXaplicacao::create([
                        'id_produto' => $produto->id_produto,
                        'id_modelo_veiculo' => $modeloVeiculo['id_modelo_veiculo'],
                        'data_inclusao' => now(),
                        'data_alteracao' => now(),
                    ]);
                }

                $idsModelosVeiculosAtuais[] = $modeloVeiculo['id_modelo_veiculo'];
            }


            DB::commit();

            return redirect()
                ->route('admin.cadastroprodutosestoque.index')
                ->with([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Produto cadastrado com sucesso!',
                    'duration' => 3000, // opcional (padrão: 5000ms)
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            Log::error('Erro na criação de Produto:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.cadastroprodutosestoque.index')
                ->with([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível cadastrar o Produto.",
                    'duration' => 3000, // opcional (padrão: 5000ms)
                ]);
        }
    }


    public function edit($id)
    {
        $cadastroProdutos = Produto::where('id_produto', $id)->first();

        $qtdProduto = ProdutosPorFilial::select('quantidade_produto')->where('id_produto_unitop', $id)->where('id_filial', GetterFilial())->first();

        $produtoAplicacao = ProdutoXaplicacao::with('modelo')->where('id_produto', $id)->get();

        $formOptions = [
            'estoque'           => Estoque::select('descricao_estoque as label', 'id_estoque as value')->orderBy('label')->get()->toArray(),
            'grupoServico'      => GrupoServico::select('descricao_grupo as label', 'id_grupo as value')->orderBy('label')->get()->toArray(),
            'subgrupoServico'   => SubgrupoServico::select('descricao_subgrupo as label', 'id_subgrupo as value')->orderBy('label')->get()->toArray(),
            'filiais'           => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'modeloVeiculo'     => ModeloVeiculo::select('descricao_modelo_veiculo as label', 'id_modelo_veiculo as value')->where('ativo', true)->orderBy('label')->get()->toArray(),
            'unidadeProduto'    => UnidadeProduto::select('descricao_unidade as label', 'id_unidade_produto as value')->orderBy('label')->get()->toArray(),
            'tipoImobilizado'   => TipoImobilizado::select('descricao_tipo_imobilizados as label', 'id_tipo_imobilizados as value')->orderBy('label')->get()->toArray(),
            'modelopneu'        => ModeloPneu::select('descricao_modelo as label', 'id_modelo_pneu as value')->orderBy('label')->get()->toArray(),
        ];



        return view('admin.cadastroprodutosestoque.edit', compact('formOptions', 'cadastroProdutos', 'qtdProduto', 'produtoAplicacao'));
    }

    public function update(Request $request, $id)
    {
        $produtoDados = $request->validate([
            'id_filial'                 => 'required|integer',
            'descricao_produto'         => 'required|string|max:255',
            'marca'                     => 'required|string',
            'modelo'                    => 'required|string',
            'imagem_produto'            => 'nullable|image|mimes:jpeg,jpg,png|max:1024',
            'pre_cadastro'              => 'required',
            'id_unidade_produto'        => 'required',
            'id_grupo_servico'          => 'required',
            'quantidade_atual_produto'  => 'required',
            'is_ativo'                  => 'required',
        ], [
            'id_filial.required' => 'O campo Filial é obrigatório.',
            'descricao_produto.required' => 'O campo Descrição do Produto é obrigatório.',
            'marca.required' => 'O campo Marca é obrigatório.',
            'modelo.required' => 'O campo Modelo é obrigatório.',
            'pre_cadastro.required' => 'O campo PreCadastro é obrigatório.',
            'id_unidade_produto.required' => 'O campo Unidade de Produto é obrigatório.',
            'id_grupo_servico.required' => 'O campo Grupo de Serviço é obrigatório.',
            'id_tipo_imobilizados.required' => 'O campo Tipo de Imobilizado é obrigatório.',
            'quantidade_atual_produto.required' => 'O campo Quantidade Atual do Produto é obrigatório.',
            'is_ativo.required' => 'O campo Ativo é obrigatório.'
        ]);


        $arquivoProduto = null;
        if ($request->hasFile('imagem_produto')) {
            try {
                $produto = Produto::findOrFail($id); // Buscar antes para deletar imagem antiga

                // Deletar imagem antiga se existir
                if ($produto->imagem_produto) {
                    Storage::disk('public')->delete($produto->imagem_produto);
                }

                $arquivo = $request->file('imagem_produto');
                $arquivoProduto = $arquivo->store('produtos', 'public');
            } catch (\Exception $e) {
                Log::error('Erro ao processar arquivo do produtos: ' . $e->getMessage());
            }
        }

        $cadastroProdutoinput = json_decode($request->input('cad_produtos', '[]'), true);

        try {
            DB::beginTransaction();

            $produto = Produto::findOrFail($id);

            $produto->id_filial                = $produtoDados['id_filial'];
            $produto->data_alteracao           = now();
            $produto->descricao_produto        = $produtoDados['descricao_produto'];
            $produto->is_original              = $request->is_original ?? false;
            $produto->curva_abc                = $request->curva_abc ?? null;
            $produto->tempo_garantia           = $request->tempo_garantia ?? null;
            $produto->id_unidade_produto       = $produtoDados['id_unidade_produto'];
            $produto->ncm                      = $request->ncm ?? null;
            $produto->estoque_minimo           = $request->estoque_minimo ?? null;
            $produto->estoque_maximo           = $request->estoque_maximo ?? null;
            $produto->localizacao_produto      = $request->localizacao_produto ?? null;
            $produto->quantidade_atual_produto = 0;

            // ← CORREÇÃO: Só atualizar imagem se houver arquivo novo
            if ($arquivoProduto !== null) {
                $produto->imagem_produto = $arquivoProduto;
            }

            $produto->id_estoque_produto       = $request->id_estoque_produto ?? null;
            $produto->id_grupo_servico         = $produtoDados['id_grupo_servico'];
            $produto->id_produto_subgrupo      = $request->id_produto_subgrupo ?? null;
            $produto->cod_fabricante_          = $request->cod_fabricante_ ?? null;
            $produto->cod_alternativo_1_       = $request->cod_alternativo_1_;
            $produto->cod_alternativo_2_       = $request->cod_alternativo_2_;
            $produto->cod_alternativo_3_       = $request->cod_alternativo_3_;
            $produto->id_modelo_pneu           = $request->id_modelo_pneu ?? null;
            $produto->is_ativo                 = $produtoDados['is_ativo'] ?? True;
            $produto->modelo                   = $produtoDados['modelo'];
            $produto->marca                    = $produtoDados['marca'];
            $produto->pre_cadastro             = $produtoDados['pre_cadastro'];
            $produto->id_user_edicao           = Auth::user()->id;
            $produto->is_fracionado            = $request->is_fracionado ?? false;

            $produto->save();

            $idsModelosVeiculosAtuais = [];


            // Itera sobre o array de modelos de veículos
            foreach ($cadastroProdutoinput as $modeloVeiculo) {
                // Verifica se o relacionamento já existe
                $produtoXaplicacao = ProdutoXaplicacao::where('id_produto', $id)
                    ->where('id_modelo_veiculo', $modeloVeiculo['id_modelo_veiculo'])
                    ->first();

                if ($produtoXaplicacao) {
                    // Atualiza o registro existente
                    $produtoXaplicacao->update([
                        'data_alteracao' => now(),
                    ]);
                } else {
                    // Cria um novo registro
                    ProdutoXaplicacao::create([
                        'id_produto' => $id,
                        'id_modelo_veiculo' => $modeloVeiculo['id_modelo_veiculo'],
                        'data_inclusao' => now(),
                        'data_alteracao' => now(),
                    ]);
                }

                // Adiciona o ID do modelo de veículo ao array de IDs atuais
                $idsModelosVeiculosAtuais[] = $modeloVeiculo['id_modelo_veiculo'];
            }

            // Remove relacionamentos que não estão mais no array $cadastroProdutoinput
            ProdutoXaplicacao::where('id_produto', $id)
                ->whereNotIn('id_modelo_veiculo', $idsModelosVeiculosAtuais)
                ->delete();

            DB::commit();

            return redirect()
                ->route('admin.cadastroprodutosestoque.index')
                ->with([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Produto editado com sucesso!',
                    'duration' => 3000, // opcional (padrão: 5000ms)
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro na edição de Produto:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.cadastroprodutosestoque.index')
                ->with([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível editar o Produto.",
                    'duration' => 3000, // opcional (padrão: 5000ms)
                ]);
        }
    }

    public function destroy($id)
    {
        try {
            // Verifica se o produto existe
            $produto = Produto::findOrFail($id);

            // Verifica se o produto está vinculado a alguma filial
            $produtoPorFilial = ProdutosPorFilial::where('id_produto_unitop', $id)->get();
            $qtd_estoque = ProdutosPorFilial::where('id_produto_unitop', $id)
                ->sum('quantidade_produto');

            if ($qtd_estoque > 0) {
                return response()
                    ->json([
                        'status'  => 'error',
                        'message' => 'Não é possível excluir o produto, pois ele está vinculado a uma filial com quantidade em estoque.',
                    ], 400);
            }

            // Excluir o produto
            $produto->delete();
            foreach ($produtoPorFilial as $item) {
                $item->delete();
            }

            return redirect()->route('admin.cadastroprodutosestoque.index')->with('success', 'Cadastro de Produtos Excluído com Sucesso!');
        } catch (\Exception $e) {
            LOG::ERROR('Erro ao Inativar produto: ' . $e->getMessage());

            return redirect()->route('admin.cadastroprodutosestoque.index')->with('error', 'Não foi possível excluir produto ' . $e->getMessage());
        }
    }
}

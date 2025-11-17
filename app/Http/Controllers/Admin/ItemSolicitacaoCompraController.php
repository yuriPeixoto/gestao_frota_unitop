<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemSolicitacaoCompra;
use App\Models\SolicitacaoCompra;
use App\Models\Produto;
use App\Modules\Manutencao\Models\Servico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ItemSolicitacaoCompraController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $solicitacaoId)
    {
        $solicitacao = SolicitacaoCompra::findOrFail($solicitacaoId);

        // Verificação de permissão usando policy
        $this->authorize('view', $solicitacao);

        $itens = ItemSolicitacaoCompra::with(['produto', 'servico'])
            ->where('id_solicitacao', $solicitacaoId)
            ->get();

        if ($request->expectsJson()) {
            return response()->json(['itens' => $itens]);
        }

        return view('itens-solicitacao.index', compact('solicitacao', 'itens'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $solicitacaoId)
    {
        $solicitacao = SolicitacaoCompra::findOrFail($solicitacaoId);

        // Verificação de permissão usando policy
        $this->authorize('update', $solicitacao);

        // Verificar se a solicitação pode ser editada
        if (!$solicitacao->podeSerEditada()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta solicitação não pode ser editada.'
                ], 400);
            }

            return redirect()
                ->back()
                ->with('error', 'Esta solicitação não pode ser editada.');
        }

        // Validação dos dados
        $validated = $request->validate([
            'tipo' => 'required|in:produto,servico',
            'id_produto' => 'required_if:tipo,produto|nullable|exists:produtos,id_produto',
            'id_servico' => 'required_if:tipo,servico|nullable|exists:servicos,id_servico',
            'descricao' => 'required|string|max:255',
            'quantidade' => 'required|numeric|min:0.01',
            'unidade_medida' => 'required|string|max:10',
            'justificativa' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $item = new ItemSolicitacaoCompra();
            $item->id_solicitacao = $solicitacaoId;
            $item->tipo = $validated['tipo'];

            if ($validated['tipo'] == 'produto' && !empty($validated['id_produto'])) {
                $item->id_produto = $validated['id_produto'];
            }

            if ($validated['tipo'] == 'servico' && !empty($validated['id_servico'])) {
                $item->id_servico = $validated['id_servico'];
            }

            $item->descricao = $validated['descricao'];
            $item->quantidade = $validated['quantidade'];
            $item->unidade_medida = $validated['unidade_medida'];
            $item->status = 'pendente';
            $item->justificativa = $validated['justificativa'];

            $item->save();

            DB::commit();

            // Carregar relações
            $item->load(['produto', 'servico']);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item adicionado com sucesso!',
                    'item' => $item
                ]);
            }

            return redirect()
                ->route('solicitacoes.show', $solicitacaoId)
                ->with('success', 'Item adicionado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao adicionar item: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao adicionar item: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao adicionar item: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = ItemSolicitacaoCompra::findOrFail($id);
        $solicitacao = SolicitacaoCompra::findOrFail($item->id_solicitacao);

        // Verificação de permissão usando policy
        $this->authorize('update', $solicitacao);

        // Verificar se a solicitação pode ser editada
        if (!$solicitacao->podeSerEditada()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta solicitação não pode ser editada.'
                ], 400);
            }

            return redirect()
                ->back()
                ->with('error', 'Esta solicitação não pode ser editada.');
        }

        // Validação dos dados
        $validated = $request->validate([
            'tipo' => 'sometimes|required|in:produto,servico',
            'id_produto' => 'required_if:tipo,produto|nullable|exists:produtos,id_produto',
            'id_servico' => 'required_if:tipo,servico|nullable|exists:servicos,id_servico',
            'descricao' => 'sometimes|required|string|max:255',
            'quantidade' => 'sometimes|required|numeric|min:0.01',
            'unidade_medida' => 'sometimes|required|string|max:10',
            'justificativa' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Atualizar apenas os campos fornecidos
            if (isset($validated['tipo'])) {
                $item->tipo = $validated['tipo'];

                // Resetar os IDs não relacionados ao tipo
                if ($validated['tipo'] == 'produto') {
                    $item->id_servico = null;
                    if (!empty($validated['id_produto'])) {
                        $item->id_produto = $validated['id_produto'];
                    }
                } else {
                    $item->id_produto = null;
                    if (!empty($validated['id_servico'])) {
                        $item->id_servico = $validated['id_servico'];
                    }
                }
            }

            if (isset($validated['descricao'])) {
                $item->descricao = $validated['descricao'];
            }

            if (isset($validated['quantidade'])) {
                $item->quantidade = $validated['quantidade'];
            }

            if (isset($validated['unidade_medida'])) {
                $item->unidade_medida = $validated['unidade_medida'];
            }

            if (isset($validated['justificativa'])) {
                $item->justificativa = $validated['justificativa'];
            }

            $item->save();

            DB::commit();

            // Carregar relações
            $item->load(['produto', 'servico']);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item atualizado com sucesso!',
                    'item' => $item
                ]);
            }

            return redirect()
                ->route('solicitacoes.show', $item->id_solicitacao)
                ->with('success', 'Item atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar item: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar item: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar item: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $item = ItemSolicitacaoCompra::findOrFail($id);
        $solicitacao = SolicitacaoCompra::findOrFail($item->id_solicitacao);

        // Verificação de permissão usando policy
        $this->authorize('update', $solicitacao);

        // Verificar se a solicitação pode ser editada
        if (!$solicitacao->podeSerEditada()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta solicitação não pode ser editada.'
                ], 400);
            }

            return redirect()
                ->back()
                ->with('error', 'Esta solicitação não pode ser editada.');
        }

        try {
            // Verificar se o item já foi incluído em algum pedido
            if ($item->foiInclusoEmPedido()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Este item não pode ser excluído pois já está incluído em um pedido de compra.'
                    ], 400);
                }

                return redirect()
                    ->back()
                    ->with('error', 'Este item não pode ser excluído pois já está incluído em um pedido de compra.');
            }

            $item->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item excluído com sucesso!'
                ]);
            }

            return redirect()
                ->route('solicitacoes.show', $item->id_solicitacao)
                ->with('success', 'Item excluído com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir item: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao excluir item: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Erro ao excluir item: ' . $e->getMessage());
        }
    }

    /**
     * Buscar produtos para a API.
     */
    public function searchProdutos(Request $request)
    {

        $term = strtolower($request->get('term'));

        $produtos = Produto::where('is_ativo', true)
            ->whereRaw('LOWER(descricao_produto) LIKE ?', ["%{$term}%"])
            ->orderBy('descricao_produto')
            ->limit(30)
            ->get(['id_produto as value', 'descricao_produto as label']);

        return response()->json($produtos);
    }

    /**
     * Buscar serviços para a API.
     */
    public function searchServicos(Request $request)
    {
        // Verificação básica de permissão
        if (!Auth::check()) {
            return response()->json(['error' => 'Não autorizado'], 401);
        }

        $termo = $request->input('termo');

        if (empty($termo)) {
            return response()->json(['resultados' => []]);
        }

        $servicos = Servico::where('ativo', true)
            ->where(function ($query) use ($termo) {
                $query->where('codigo_servico', 'LIKE', "%{$termo}%")
                    ->orWhere('descricao_servico', 'LIKE', "%{$termo}%");
            })
            ->limit(10)
            ->get();

        $resultados = $servicos->map(function ($servico) {
            return [
                'id' => $servico->id_servico,
                'codigo' => $servico->codigo_servico,
                'descricao' => $servico->descricao_servico,
                'unidade' => $servico->unidade_medida ?? 'SERV',
                'tipo' => 'servico'
            ];
        });

        return response()->json(['resultados' => $resultados]);
    }

    /**
     * Buscar produtos e serviços para a API.
     */
    public function searchItens(Request $request)
    {
        // Verificação básica de permissão
        if (!Auth::check()) {
            return response()->json(['error' => 'Não autorizado'], 401);
        }

        $termo = $request->input('termo');
        $tipo = $request->input('tipo', 'ambos'); // produto, servico, ambos

        if (empty($termo)) {
            return response()->json(['resultados' => []]);
        }

        $resultados = [];

        // Buscar produtos
        if ($tipo == 'produto' || $tipo == 'ambos') {
            $produtos = Produto::where('ativo', true)
                ->where(function ($query) use ($termo) {
                    $query->where('codigo_produto', 'LIKE', "%{$termo}%")
                        ->orWhere('descricao_produto', 'LIKE', "%{$termo}%");
                })
                ->limit(10)
                ->get()
                ->map(function ($produto) {
                    return [
                        'id' => $produto->id_produto,
                        'codigo' => $produto->codigo_produto,
                        'descricao' => $produto->descricao_produto,
                        'unidade' => $produto->unidade_medida,
                        'tipo' => 'produto'
                    ];
                });

            $resultados = array_merge($resultados, $produtos->toArray());
        }

        // Buscar serviços
        if ($tipo == 'servico' || $tipo == 'ambos') {
            $servicos = Servico::where('ativo', true)
                ->where(function ($query) use ($termo) {
                    $query->where('codigo_servico', 'LIKE', "%{$termo}%")
                        ->orWhere('descricao_servico', 'LIKE', "%{$termo}%");
                })
                ->limit(10)
                ->get()
                ->map(function ($servico) {
                    return [
                        'id' => $servico->id_servico,
                        'codigo' => $servico->codigo_servico,
                        'descricao' => $servico->descricao_servico,
                        'unidade' => $servico->unidade_medida ?? 'SERV',
                        'tipo' => 'servico'
                    ];
                });

            $resultados = array_merge($resultados, $servicos->toArray());
        }

        // Limitar os resultados combinados a 10 itens
        $resultados = array_slice($resultados, 0, 10);

        return response()->json(['resultados' => $resultados]);
    }

    /**
     * Método para pré-cadastro de produtos
     */
    public function preCadastroProduto(Request $request)
    {
        try {
            // Validação dos dados
            $request->validate([
                'id_estoque_produto' => 'required|integer|exists:estoque,id_estoque',
                'id_filial' => 'required|integer|exists:filiais,id',
                'descricao_produto' => 'required|string|max:255',
                'id_unidade_produto' => 'required|integer|exists:unidadeproduto,id_unidade_produto',
                'id_grupo_servico' => 'required|integer|exists:grupo_servico,id_grupo',
            ]);

            // Criar o produto
            $produto = Produto::create([
                'id_estoque_produto' => $request->id_estoque_produto,
                'id_filial' => $request->id_filial,
                'descricao_produto' => $request->descricao_produto,
                'id_unidade_produto' => $request->id_unidade_produto,
                'id_grupo_servico' => $request->id_grupo_servico,
                'pre_cadastro' => true,
                'is_ativo' => true,
                'data_inclusao' => now(),
                'id_user_cadastro' => Auth::id(),
                'quantidade_atual_produto' => 0,
                'estoque_minimo' => 0,
                'estoque_maximo' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produto cadastrado com sucesso!',
                'produto' => [
                    'id' => $produto->id_produto,
                    'descricao' => $produto->descricao_produto,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao criar pré-cadastro de produto: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente.'
            ], 500);
        }
    }
}

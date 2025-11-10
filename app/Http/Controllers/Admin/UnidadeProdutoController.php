<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnidadeProduto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnidadeProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Query otimizada sem eager loading desnecessário
            $unidadeProdutos = UnidadeProduto::select([
                'id_unidade_produto',
                'descricao_unidade',
                'data_inclusao',
                'data_alteracao',
            ])
                ->orderBy('id_unidade_produto')
                ->paginate(15);

            Log::info('Listagem de unidades de produto carregada', [
                'total_registros' => $unidadeProdutos->total(),
                'usuario_id' => auth()->id(),
            ]);

            return view('admin.unidadeprodutos.index', compact('unidadeProdutos'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar listagem de unidades de produto', [
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id(),
            ]);

            return back()->with('notification', [
                'title' => 'Erro!',
                'type' => 'error',
                'message' => 'Erro ao carregar a listagem. Tente novamente.',
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.unidadeprodutos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validação com mensagens personalizadas em português
        $request->validate([
            'descricao_unidade' => [
                'required',
                'string',
                'max:500',
                'unique:unidadeproduto,descricao_unidade',
            ],
        ], [
            'descricao_unidade.required' => 'A descrição da unidade é obrigatória.',
            'descricao_unidade.string' => 'A descrição deve ser um texto válido.',
            'descricao_unidade.max' => 'A descrição não pode ter mais de 500 caracteres.',
            'descricao_unidade.unique' => 'Já existe uma unidade com esta descrição.',
        ]);

        try {
            DB::beginTransaction();

            $unidadeProduto = UnidadeProduto::create([
                'descricao_unidade' => trim($request->descricao_unidade),
                'data_inclusao' => now(),
                'data_alteracao' => null,
            ]);

            DB::commit();

            Log::info('Nova unidade de produto criada', [
                'id' => $unidadeProduto->id_unidade_produto,
                'descricao' => $unidadeProduto->descricao_unidade,
                'usuario_id' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.unidadeprodutos.index')
                ->with('notification', [
                    'title' => 'Sucesso!',
                    'type' => 'success',
                    'message' => 'Unidade de produto criada com sucesso!',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao criar unidade de produto', [
                'dados' => $request->only(['descricao_unidade']),
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('notification', [
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Erro ao criar unidade de produto. Tente novamente.',
                ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $unidadeProduto = UnidadeProduto::findOrFail($id);

            return view('admin.unidadeprodutos.show', compact('unidadeProduto'));
        } catch (\Exception $e) {
            Log::error('Erro ao visualizar unidade de produto', [
                'id' => $id,
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.unidadeprodutos.index')
                ->with('notification', [
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Unidade de produto não encontrada.',
                ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        try {
            $unidadeProduto = UnidadeProduto::findOrFail($id);

            return view('admin.unidadeprodutos.edit', compact('unidadeProduto'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição', [
                'id' => $id,
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.unidadeprodutos.index')
                ->with('notification', [
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Unidade de produto não encontrada para edição.',
                ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        // Validação com exclusão do próprio registro
        $request->validate([
            'descricao_unidade' => [
                'required',
                'string',
                'max:500',
                'unique:unidadeproduto,descricao_unidade,' . $id . ',id_unidade_produto',
            ],
        ], [
            'descricao_unidade.required' => 'A descrição da unidade é obrigatória.',
            'descricao_unidade.string' => 'A descrição deve ser um texto válido.',
            'descricao_unidade.max' => 'A descrição não pode ter mais de 500 caracteres.',
            'descricao_unidade.unique' => 'Já existe uma unidade com esta descrição.',
        ]);

        try {
            $unidadeProduto = UnidadeProduto::findOrFail($id);

            DB::beginTransaction();

            $dadosAntigos = [
                'descricao_anterior' => $unidadeProduto->descricao_unidade,
            ];

            $unidadeProduto->update([
                'descricao_unidade' => trim($request->descricao_unidade),
                'data_alteracao' => now(),
            ]);

            DB::commit();

            Log::info('Unidade de produto atualizada', [
                'id' => $unidadeProduto->id_unidade_produto,
                'dados_anteriores' => $dadosAntigos,
                'dados_novos' => [
                    'descricao_nova' => $unidadeProduto->descricao_unidade,
                ],
                'usuario_id' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.unidadeprodutos.index')
                ->with('notification', [
                    'title' => 'Sucesso!',
                    'type' => 'success',
                    'message' => 'Unidade de produto atualizada com sucesso!',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao atualizar unidade de produto', [
                'id' => $id,
                'dados' => $request->only(['descricao_unidade']),
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('notification', [
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Erro ao atualizar unidade de produto. Tente novamente.',
                ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $unidadeProduto = UnidadeProduto::findOrFail($id);

            // Verificar se a unidade está sendo usada
            // TODO: Implementar verificações de relacionamentos se necessário
            // Exemplo: $emUso = Produto::where('id_unidade_produto', $id)->exists();

            DB::beginTransaction();

            $dadosExcluidos = [
                'id' => $unidadeProduto->id_unidade_produto,
                'descricao' => $unidadeProduto->descricao_unidade,
            ];

            $unidadeProduto->delete();

            DB::commit();

            Log::info('Unidade de produto excluída', [
                'dados_excluidos' => $dadosExcluidos,
                'usuario_id' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Unidade Excluida com Sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao excluir unidade de produto', [
                'id' => $id,
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Unidade Excluida com Sucesso!');
        }
    }

    /**
     * API endpoint para busca de unidades (para autocomplete/select)
     */
    public function search(Request $request)
    {
        try {
            $term = $request->get('q', '');
            $limit = min($request->get('limit', 20), 50); // Máximo 50 resultados

            $query = UnidadeProduto::select([
                'id_unidade_produto as value',
                'descricao_unidade as label',
            ])
                ->orderBy('descricao_unidade');

            if (! empty($term)) {
                $query->where('descricao_unidade', 'ILIKE', '%' . $term . '%');
            }

            $unidades = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => $unidades,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro na busca de unidades de produto', [
                'termo_busca' => $request->get('q'),
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro na busca',
            ], 500);
        }
    }
}

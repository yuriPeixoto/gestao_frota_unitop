<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GrupoServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GrupoServicoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $grupoServicos = GrupoServico::select([
                'id_grupo',
                'descricao_grupo',
                'data_inclusao',
                'data_alteracao',
            ])
                ->orderBy('id_grupo')
                ->paginate(15);

            Log::info('Listagem de grupos de serviço carregada', [
                'total_registros' => $grupoServicos->total(),
                'usuario_id' => auth()->id(),
            ]);

            return view('admin.gruposervicos.index', compact('grupoServicos'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar listagem de grupos de serviço', [
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
        return view('admin.gruposervicos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao_grupo' => [
                'required',
                'string',
                'max:500',
                'unique:grupo_servico,descricao_grupo',
            ],
        ], [
            'descricao_grupo.required' => 'A descrição do grupo é obrigatória.',
            'descricao_grupo.string' => 'A descrição deve ser um texto válido.',
            'descricao_grupo.max' => 'A descrição não pode ter mais de 500 caracteres.',
            'descricao_grupo.unique' => 'Já existe um grupo com esta descrição.',
        ]);

        try {
            DB::beginTransaction();

            $grupoServico = GrupoServico::create([
                'descricao_grupo' => trim($request->descricao_grupo),
                'data_inclusao' => now(),
                'data_alteracao' => null,
            ]);

            DB::commit();

            Log::info('Novo grupo de serviço criado', [
                'id' => $grupoServico->id_grupo,
                'descricao' => $grupoServico->descricao_grupo,
                'usuario_id' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.gruposervicos.index')
                ->with('notification', [
                    'title' => 'Sucesso!',
                    'type' => 'success',
                    'message' => 'Grupo de serviço criado com sucesso!',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao criar grupo de serviço', [
                'dados' => $request->only(['descricao_grupo']),
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('notification', [
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Erro ao criar grupo de serviço. Tente novamente.',
                ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $grupoServico = GrupoServico::findOrFail($id);

            return view('admin.gruposervicos.show', compact('grupoServico'));
        } catch (\Exception $e) {
            Log::error('Erro ao visualizar grupo de serviço', [
                'id' => $id,
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.gruposervicos.index')
                ->with('notification', [
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Grupo de serviço não encontrado.',
                ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        try {
            $grupoServico = GrupoServico::findOrFail($id);

            return view('admin.gruposervicos.edit', compact('grupoServico'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição', [
                'id' => $id,
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.gruposervicos.index')
                ->with('notification', [
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Grupo de serviço não encontrado para edição.',
                ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'descricao_grupo' => [
                'required',
                'string',
                'max:500',
                'unique:grupo_servico,descricao_grupo,' . $id . ',id_grupo',
            ],
        ], [
            'descricao_grupo.required' => 'A descrição do grupo é obrigatória.',
            'descricao_grupo.string' => 'A descrição deve ser um texto válido.',
            'descricao_grupo.max' => 'A descrição não pode ter mais de 500 caracteres.',
            'descricao_grupo.unique' => 'Já existe um grupo com esta descrição.',
        ]);

        try {
            $grupoServico = GrupoServico::findOrFail($id);

            DB::beginTransaction();

            $dadosAntigos = [
                'descricao_anterior' => $grupoServico->descricao_grupo,
            ];

            $grupoServico->update([
                'descricao_grupo' => trim($request->descricao_grupo),
                'data_alteracao' => now(),
            ]);

            DB::commit();

            Log::info('Grupo de serviço atualizado', [
                'id' => $grupoServico->id_grupo,
                'dados_anteriores' => $dadosAntigos,
                'dados_novos' => [
                    'descricao_nova' => $grupoServico->descricao_grupo,
                ],
                'usuario_id' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.gruposervicos.index')
                ->with('notification', [
                    'title' => 'Sucesso!',
                    'type' => 'success',
                    'message' => 'Grupo de serviço atualizado com sucesso!',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao atualizar grupo de serviço', [
                'id' => $id,
                'dados' => $request->only(['descricao_grupo']),
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('notification', [
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Erro ao atualizar grupo de serviço. Tente novamente.',
                ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $grupoServico = GrupoServico::findOrFail($id);

            // Verificar se o grupo está being usado por subgrupos
            $emUso = \App\Models\SubgrupoServico::where('ig_grupo', $id)->exists();

            if ($emUso) {
                return redirect()->back()->with('error', 'Este grupo possui subgrupos associados e não pode ser excluído.!');
            }

            DB::beginTransaction();

            $dadosExcluidos = [
                'id' => $grupoServico->id_grupo,
                'descricao' => $grupoServico->descricao_grupo,
            ];

            $grupoServico->delete();

            DB::commit();

            Log::info('Grupo de serviço excluído', [
                'dados_excluidos' => $dadosExcluidos,
                'usuario_id' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Grupo excluído com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao excluir grupo de serviço', [
                'id' => $id,
                'erro' => $e->getMessage(),
                'usuario_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'notification' => [
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Erro ao excluir grupo de serviço: ' . $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * API endpoint para busca de grupos (para autocomplete/select)
     */
    public function search(Request $request)
    {
        try {
            $term = $request->get('q', '');
            $limit = min($request->get('limit', 20), 50);

            $query = GrupoServico::select([
                'id_grupo as value',
                'descricao_grupo as label',
            ])
                ->orderBy('descricao_grupo');

            if (! empty($term)) {
                $query->where('descricao_grupo', 'ILIKE', '%' . $term . '%');
            }

            $grupos = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => $grupos,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro na busca de grupos de serviço', [
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

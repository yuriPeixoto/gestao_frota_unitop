<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TipoImobilizado;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TipoImobilizadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = TipoImobilizado::query();

            if ($request->filled('id_tipo_imobilizados')) {
                $query->where('id_tipo_imobilizados', $request->id_tipo_imobilizados);
            }

            if ($request->filled('data_inclusao_inicial')) {
                $query->whereDate('data_inclusao', '>=', $request->data_inclusao_inicial);
            }

            if ($request->filled('data_inclusao_final')) {
                $query->whereDate('data_inclusao', '<=', $request->data_inclusao_final);
            }

            if ($request->filled('descricao_tipo_imobilizados')) {
                $query->where('descricao_tipo_imobilizados', 'ilike', '%' . $request->descricao_tipo_imobilizados . '%');
            }

            $tipo = TipoImobilizado::select('descricao_tipo_imobilizados as value', 'descricao_tipo_imobilizados as label')
                ->orderBy('descricao_tipo_imobilizados')
                ->get();

            $tipoImobilizado = $query->latest('id_tipo_imobilizados')
                ->paginate(15)
                ->appends($request->query());

            if ($request->header('HX-Request')) {
                return view('admin.tipoimobilizados._table', compact('tipoImobilizado', 'tipo'));
            }

            return view('admin.tipoimobilizados.index', compact('tipoImobilizado', 'tipo'));
        } catch (\Exception $e) {
            Log::error('Erro ao listar tipos de imobilizados: ' . $e->getMessage());
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Não foi possível carregar a lista de tipos de imobilizados.'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipoimobilizados.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao_tipo_imobilizados' => 'required|string|max:500',
        ]);
        try {
            $tipoimobilizados = new TipoImobilizado();

            $tipoimobilizados->data_inclusao = now();
            $tipoimobilizados->descricao_tipo_imobilizados = $request->descricao_tipo_imobilizados;

            $tipoimobilizados->save();

            return redirect()->to(route('admin.tipoimobilizados.index'))
                ->with('notification', [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Tipo de imobilizado adicionado com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao salvar o tipo de Imobilizado: ' . $e->getMessage());
            return back()->withInput()->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro ao salvar o tipo de Imobilizado: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoImobilizado $tipoimobilizados)
    {
        return view('admin.tipoimobilizados.edit', compact('tipoimobilizados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoImobilizado $tipoimobilizados)
    {
        try {
            $validated = $request->validate([
                'descricao_tipo_imobilizados' => 'required|string|max:255',
            ]);

            $updated = $tipoimobilizados->update([
                'descricao_tipo_imobilizados' => $validated['descricao_tipo_imobilizados'],
                'data_alteracao' => now()
            ]);

            if (!$updated) {
                return redirect()->to(route('admin.tipoimobilizados.index'))
                    ->with('notification', [
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possivel editar!'
                    ]);
            }

            return redirect()->to(route('admin.tipoimobilizados.index'))
                ->with('notification', [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Tipo de Imobilizado editado com sucesso!'
                ]);
        } catch (\Exception $e) {
            return back()->withInput()->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $idImobilizado)
    {
        try {
            // Verificar se o tipo de imobilizado está sendo usado na tabela produtos_imobilizados
            $emUso = DB::connection('pgsql')->table('produtos_imobilizados')
                ->where('id_tipo_imobilizados', $idImobilizado)
                ->exists();

            if ($emUso) {
                return response()->json([
                    'notification' => [
                        'title'   => 'Não é possível excluir',
                        'type'    => 'error',
                        'message' => 'Este tipo de imobilizado está sendo utilizado em produtos imobilizados e não pode ser excluído.'
                    ]
                ], 422);
            }

            $tipoimobilizado = TipoImobilizado::findOrFail($idImobilizado);
            $tipoimobilizado->delete();

            return response()->json([
                'notification' => [
                    'title'   => 'Sucesso',
                    'type'    => 'success',
                    'message' => 'Tipo de imobilizado excluído com sucesso!'
                ]
            ]);
        } catch (QueryException $e) {
            // Se ainda assim ocorrer uma violação de chave estrangeira (caso exista outra tabela relacionada)
            if (
                str_contains($e->getMessage(), 'violates foreign key constraint') ||
                str_contains($e->getMessage(), 'SQLSTATE[23503]')
            ) {

                Log::error('Violação de chave estrangeira ao excluir tipo de imobilizado: ' . $e->getMessage());

                return response()->json([
                    'notification' => [
                        'title'   => 'Não é possível excluir',
                        'type'    => 'error',
                        'message' => 'Este tipo de imobilizado está sendo utilizado em outros registros e não pode ser excluído.'
                    ]
                ], 422);
            }

            // Outros erros de consulta
            Log::error('Erro de banco de dados ao excluir tipo de imobilizado: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Erro de banco de dados ao excluir o tipo de imobilizado.'
                ]
            ], 500);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir tipo de imobilizado: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o tipo: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
}

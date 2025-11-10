<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TipoOcorrencia;
use Illuminate\Support\Facades\Log;


class TipoOcorrenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TipoOcorrencia::query();

        if ($request->filled('id_tipo_ocorrencia')) {
            $query->where('id_tipo_ocorrencia', $request->id_tipo_ocorrencia);
        }

        if ($request->filled('data_inclusao_inicial')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao_inicial);
        }

        if ($request->filled('data_inclusao_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_inclusao_final);
        }

        if ($request->filled('descricao_ocorrencia')) {
            $query->where('descricao_ocorrencia', 'ilike', '%' . $request->descricao_ocorrencia . '%');
        }

        $tipo = TipoOcorrencia::select('descricao_ocorrencia as value', 'descricao_ocorrencia as label')->orderBy('descricao_ocorrencia')->get();
        $tipoOcorrencia = $query->latest('id_tipo_ocorrencia')
            ->paginate(40)
            ->appends($request->query());

        if ($request->header('HX-Request')) {
            return view('admin.tipoocorrencias._table', compact('tipoOcorrencia', 'tipo'));
        }

        return view('admin.tipoocorrencias.index', compact('tipoOcorrencia', 'tipo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipoocorrencias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao_ocorrencia' => 'required|string|max:500',
        ]);
        $tipoocorrencias = new TipoOcorrencia();
        $tipoocorrencias->data_inclusao = now();
        $tipoocorrencias->descricao_ocorrencia = $request->descricao_ocorrencia;
        $tipoocorrencias->save();

        return redirect()->route('admin.tipoocorrencias.index')->withNotification([
            'title'   => 'Sucesso!',
            'type'    => 'success',
            'message' => 'Novo tipo de Ocorrência adicionado com sucesso!'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoOcorrencia $tipoocorrencias)
    {
        $tipoocorrencias = TipoOcorrencia::findOrFail($tipoocorrencias);
        return view('admin.tipoocorrencias.show', compact('tipoocorrencias'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoOcorrencia $tipoocorrencias)
    {
        return view('admin.tipoocorrencias.edit', compact('tipoocorrencias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoOcorrencia $tipoocorrencias)
    {
        $validated = $request->validate([
            'descricao_ocorrencia' => 'required|string|max:500'
        ]);

        try {
            $tipoocorrencias->descricao_ocorrencia = $validated['descricao_ocorrencia'];
            $tipoocorrencias->data_alteracao = now();
            $tipoocorrencias->update();

            if (!$tipoocorrencias) {
                return  redirect()
                    ->route('admin.tipoocorrencias.index')
                    ->withNotification([
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possivel editar o tipo de Ocorrência!'
                    ]);
            }

            return redirect()
                ->route('admin.tipoocorrencias.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Tipo de Ocorrência editado com sucesso!'
                ]);
        } catch (\Exception $e) {
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $tipocorrencia = TipoOcorrencia::findOrFail($id);
            $tipocorrencia->delete();

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            LOG::INFO('Erro ao excluir tipo de Ocorrência: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o tipo de Ocorrência: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
}

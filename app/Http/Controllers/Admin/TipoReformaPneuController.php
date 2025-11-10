<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TipoReformaPneu;
use Illuminate\Support\Facades\Log;

use function PHPSTORM_META\map;

class TipoReformaPneuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TipoReformaPneu::query();

        if ($request->filled('id_tipo_reforma')) {
            $query->where('id_tipo_reforma', $request->id_tipo_reforma);
        }

        if ($request->filled('data_inclusao_inicial')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao_inicial);
        }

        if ($request->filled('data_inclusao_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_inclusao_final);
        }

        if ($request->filled('descricao_tipo_reforma')) {
            $query->where('descricao_tipo_reforma', 'ilike', '%' . $request->descricao_tipo_reforma . '%');
        }

        $tipo = TipoReformaPneu::select('descricao_tipo_reforma as value', 'descricao_tipo_reforma as label')
            ->orderBy('descricao_tipo_reforma')

            ->get();
        $tipoReforma = $query->latest('id_tipo_reforma')
            ->paginate(40)
            ->appends($request->query());

        if ($request->header('HX-Request')) {
            return view('admin.tiporeformapneus._table', compact('tipoReforma', 'tipo'));
        }

        return view('admin.tiporeformapneus.index', compact('tipoReforma', 'tipo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tiporeformapneus.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao_tipo_reforma' => 'required|string|max:500'
        ]);

        $tiporeformapneus = new TipoReformaPneu();
        $tiporeformapneus->data_inclusao = now();
        $tiporeformapneus->descricao_tipo_reforma = $request->descricao_tipo_reforma;
        $tiporeformapneus->save();

        return redirect()->route('admin.tiporeformapneus.index')->withNotification([
            'title'   => 'Sucesso!',
            'type'    => 'success',
            'message' => 'Novo tipo de reforma adicionado com sucesso!'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoReformaPneu $tiporeformapneus)
    {
        $tiporeformapneus = TipoReformaPneu::findOrFail($tiporeformapneus);
        return view('admin.tiporeformapneus.show', compact('tiporeformapneus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoReformaPneu $tiporeformapneus)
    {
        return view('admin.tiporeformapneus.edit', compact('tiporeformapneus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoReformaPneu $tiporeformapneus)
    {
        $validated = $request->validate([
            'descricao_tipo_reforma' => 'required|string|max:500',
        ]);

        try {
            $updated = $tiporeformapneus->update([
                'descricao_tipo_reforma' => $validated['descricao_tipo_reforma'],
                'data_alteracao' => now()
            ]);

            if (!$updated) {
                return  redirect()
                    ->route('admin.tiporeformapneus.index')
                    ->withNotification([
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possivel editar o tipo de Reforma!'
                    ]);
            }

            return redirect()
                ->route('admin.tiporeformapneus.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Tipo de Reforma editado com sucesso!'
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
            $tiporeforma = TipoReformaPneu::findOrFail($id);
            $tiporeforma->delete();

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            LOG::INFO('Erro ao excluir o tipo de Reforma: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o tipo de Reforma: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function getTipoReforma($id)
    {
        try {
            $tiporeforma = TipoReformaPneu::findOrFail($id);

            $response = [
                'id_tipo_reforma' => $tiporeforma->id_tipo_reforma,
                'descricao_tipo_reforma' => $tiporeforma->descricao_tipo_reforma
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            Log::info('Erro ao buscar o tipo de Reforma: ' . $e->getMessage());

            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível encontrar o tipo de Reforma: ' . $e->getMessage()
                ]
            ], 404); // Status 404 é mais apropriado para "não encontrado"
        }
    }
}

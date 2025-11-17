<?php

namespace App\Modules\Manutencao\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TipoManutencao;
use Illuminate\Support\Facades\Log;

class TipoManutencaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TipoManutencao::query();

        if ($request->filled('id_tipo_manutencao')) {
            $query->where('id_tipo_manutencao', $request->id_tipo_manutencao);
        }

        if ($request->filled('data_inclusao_inicial')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao_inicial);
        }

        if ($request->filled('data_inclusao_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_inclusao_final);
        }

        if ($request->filled('tipo_manutencao_descricao')) {
            $query->where('tipo_manutencao_descricao', 'ilike', '%' . $request->tipo_manutencao_descricao . '%');
        }

        $tipo = TipoManutencao::select('tipo_manutencao_descricao as value', 'tipo_manutencao_descricao as label')
            ->distinct()
            ->get();

        $tipoManutencao = $query->latest('id_tipo_manutencao')
            ->paginate(40)
            ->appends($request->query());

        if ($request->header('HX-Request')) {
            return view('admin.tipomanutencoes._table', compact('tipoManutencao', 'tipo'));
        }

        return view('admin.tipomanutencoes.index', compact('tipoManutencao', 'tipo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipomanutencoes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo_manutencao_descricao' => 'required|string|max:500',
        ]);
        $tipomanutencao = new Tipomanutencao();
        $tipomanutencao->data_inclusao = now();
        $tipomanutencao->tipo_manutencao_descricao = $request->tipo_manutencao_descricao;
        $tipomanutencao->save();

        return redirect()
            ->route('admin.tipomanutencoes.index')
            ->withNotification([
                'title'   => 'Sucesso!',
                'type'    => 'success',
                'message' => 'Novo tipo de manutenção adicionado com sucesso!'
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoManutencao $tipomanutencoes)
    {
        $tipomanutencoes = TipoManutencao::findOrFail($tipomanutencoes);
        return view('admin.tipomanutencoes.show', compact('tipomanutencoes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoManutencao $tipomanutencoes)
    {
        return view('admin.tipomanutencoes.edit', compact('tipomanutencoes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoManutencao $tipomanutencoes)
    {
        $validated = $request->validate([
            'tipo_manutencao_descricao' => 'required|string|max:500'
        ]);

        try {
            $tipomanutencoes->tipo_manutencao_descricao = $validated['tipo_manutencao_descricao'];
            $tipomanutencoes->data_alteracao = now();
            $tipomanutencoes->update();

            if (!$tipomanutencoes) {
                return  redirect()
                    ->route('admin.tipomanutencoes.index')
                    ->withNotification([
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possivel editar a manutenção!'
                    ]);
            }

            return redirect()
                ->route('admin.tipomanutencoes.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Manutenção editada com sucesso!'
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
            $tipoManutencao = TipoManutencao::findOrFail($id);
            $tipoManutencao->delete();

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir a manutenção: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
}

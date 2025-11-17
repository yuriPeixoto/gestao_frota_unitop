<?php

namespace App\Modules\Pneus\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TipoBorrachaPneu;
use Illuminate\Support\Facades\Log;

class TipoBorrachaPneuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TipoBorrachaPneu::query();

        if ($request->filled('id_tipo_borracha')) {
            $query->where('id_tipo_borracha', $request->id_tipo_borracha);
        }

        if ($request->filled('data_inclusao_inicial')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao_inicial);
        }

        if ($request->filled('data_inclusao_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_inclusao_final);
        }

        if ($request->filled('descricao_tipo_borracha')) {
            $query->where('descricao_tipo_borracha', 'ilike', '%' . $request->descricao_tipo_borracha . '%');
        }
        $tipo = TipoBorrachaPneu::select('id_tipo_borracha as value', 'descricao_tipo_borracha as label')
            ->orderBy('descricao_tipo_borracha')
            ->get();

        $tipoBorrachaPneus = $query->latest('id_tipo_borracha')
            ->paginate(40)
            ->appends($request->query());

        if ($request->header('HX-Request')) {
            return view('admin.tipoborrachapneus._table', compact('tipoBorrachaPneus'));
        }

        return view('admin.tipoborrachapneus.index', compact('tipoBorrachaPneus', 'tipo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipoborrachapneus.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao_tipo_borracha' => 'required|string|max:500'
        ]);
        $tipoborrachapneus = new TipoBorrachaPneu();
        $tipoborrachapneus->data_inclusao = now();
        $tipoborrachapneus->descricao_tipo_borracha = $request->descricao_tipo_borracha;
        $tipoborrachapneus->save();


        return redirect()->route('admin.tipoborrachapneus.index')->withNotification([
            'title'   => 'Sucesso!',
            'type'    => 'success',
            'message' => 'Novo tipo de categoria adicionado com sucesso!'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoBorrachaPneu $tipoborrachapneus)
    {
        $tipoborrachapneus = TipoBorrachaPneu::findOrFail($tipoborrachapneus);
        return view('admin.tipoborrachapneus.show', compact('tipoborrachapneus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoBorrachaPneu $tipoborrachapneus)
    {
        return view('admin.tipoborrachapneus.edit', compact('tipoborrachapneus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoBorrachaPneu $tipoborrachapneus)
    {
        //dd($request->all());

        try {
            $updated = $tipoborrachapneus->update([
                'descricao_tipo_borracha' => $request->input('descricao_tipo_borracha'),
                'data_alteracao' => now()
            ]);

            if (!$updated) {
                return  redirect()
                    ->route('admin.tipoborrachapneus.index')
                    ->withNotification([
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possivel editar o tipo de Borracha!'
                    ]);
            }

            return redirect()->route('admin.tipoborrachapneus.index')->withNotification([
                'title'   => 'Sucesso!',
                'type'    => 'success',
                'message' => 'Tipo de categoria atualizada com sucesso!'
            ]);
        } catch (\Exception $e) {
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => $e->getMessage()
            ]);
        }
        return redirect()->route('admin.tipoborrachapneus.index')->withNotification([
            'title'   => 'Sucesso!',
            'type'    => 'success',
            'message' => 'Tipo de categoria atualizada com sucesso!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $tipoBorracha = TipoBorrachaPneu::findOrFail($id);
            $tipoBorracha->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            LOG::INFO('ERRO AO EXCLUI O REGISTRO: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o tipo de Borracha: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
}

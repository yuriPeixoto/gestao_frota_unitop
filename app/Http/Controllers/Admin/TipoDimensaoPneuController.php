<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TipoDimensaoPneu;
use Illuminate\Support\Facades\Log;

class TipoDimensaoPneuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $query = TipoDimensaoPneu::query();

        if ($request->filled('id_dimensao_pneu')) {
            $query->where('id_dimensao_pneu', $request->id_dimensao_pneu);
        }

        if ($request->filled('descricao_pneu')) {
            $query->where('descricao_pneu', $request->descricao_pneu);
        }

        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', $request->data_inclusao);
        }

        $tipodimensaopneus = $query->latest('id_dimensao_pneu')
            ->orderBy('id_dimensao_pneu', 'desc')
            ->paginate(15)
            ->appends($request->query());

        $descricao = $this->getDescricao();

        return view('admin.tipodimensaopneus.index', compact('tipodimensaopneus', 'descricao'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipodimensaopneus.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao_pneu' => 'required|string|max:500'
        ]);
        $data_inclusao = now();
        $tipodimensaopneus = new TipoDimensaoPneu();
        $tipodimensaopneus->data_inclusao = $data_inclusao;
        $tipodimensaopneus->descricao_pneu = $request->descricao_pneu;
        $tipodimensaopneus->save();

        return redirect()->route('admin.tipodimensaopneus.index')->withNotification([
            'title'   => 'Sucesso!',
            'type'    => 'success',
            'message' => 'Novo tipo de equipamento adicionado com sucesso!'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoDimensaoPneu $tipodimensaopneus)
    {
        $tipodimensaopneus = TipoDimensaoPneu::findOrFail($tipodimensaopneus);
        return view('admin.tipodimensaopneus.show', compact('tipodimensaopneus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoDimensaoPneu $tipodimensaopneus)
    {
        return view('admin.tipodimensaopneus.edit', compact('tipodimensaopneus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoDimensaoPneu $tipodimensaopneus)
    {
        $validated = $request->validate([
            'descricao_pneu' => 'required|string|max:500',
        ]);

        try {
            $updated = $tipodimensaopneus->update([
                'descricao_pneu' => $validated['descricao_pneu']
            ]);

            if (!$updated) {
                return  redirect()
                    ->route('admin.tipodimensaopneus.index')
                    ->withNotification([
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possivel editar o tipo de dimensão!'
                    ]);
            }

            return redirect()
                ->route('admin.tipodimensaopneus.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Tipo de dimensão editado com sucesso!'
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
            $tipodimensao = TipoDimensaoPneu::findOrFail($id);
            $tipodimensao->delete();

            return response()->json([
                'notification' => [
                    'title'   => 'Tipo excluído',
                    'type'    => 'success',
                    'message' => 'Tipo de Dimensão excluído com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o tipo de dimensão: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    private function getDescricao()
    {
        return TipoDimensaoPneu::select('descricao_pneu as value', 'descricao_pneu as label')
            ->orderBy('descricao_pneu', 'desc')
            ->get()
            ->toArray();
    }
}

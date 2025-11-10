<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoCombustivel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TipoCombustivelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TipoCombustivel::query();

        if ($request->filled('id_tipo_combustivel')) {
            $query->where('id_tipo_combustivel', $request->id_tipo_combustivel);
        }

        if ($request->filled('data_inclusao_inicial')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao_inicial);
        }

        if ($request->filled('data_inclusao_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_inclusao_final);
        }

        if ($request->filled('descricao')) {
            $query->where('descricao', 'ilike', '%' . $request->descricao . '%');
        }

        if ($request->filled('unidade_medida')) {
            $query->where('unidade_medida', 'ilike', '%' . $request->unidade_medida . '%');
        }

        if ($request->filled('ncm')) {
            $query->where('ncm', 'ilike', '%' . $request->ncm . '%');
        }

        $tipo = TipoCombustivel::select('descricao as value', 'descricao as label')
            ->orderBy('descricao')
            ->get();

        $tipoCombustivel = $query->latest('id_tipo_combustivel')
            ->paginate(40)
            ->appends($request->query());

        if ($request->header('HX-Request')) {
            return view('admin.tipocombustiveis._table', compact('tipoCombustivel'));
        }

        return view('admin.tipocombustiveis.index', compact('tipoCombustivel', 'tipo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipocombustiveis.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao' => 'required|string|max:500',
            'unidade_medida' => 'required|string|max:2',
            'ncm' => 'required|integer',
        ]);
        $data_inclusao = date('Y-m-d H:i:s');
        $tipocombustivel = new TipoCombustivel();
        $tipocombustivel->data_inclusao = $data_inclusao;
        $tipocombustivel->descricao = $request->descricao;
        $tipocombustivel->unidade_medida = $request->unidade_medida;
        $tipocombustivel->ncm = $request->ncm;
        $tipocombustivel->save();
        return redirect()->route('admin.tipocombustiveis.index')->withNotification([
            'title'   => 'Sucesso!',
            'type'    => 'success',
            'message' => 'Novo tipo de combustível adicionado com sucesso!'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoCombustivel $tipocombustivel)
    {
        $tipofornecedores = TipoCombustivel::findOrFail($tipocombustivel);
        return view('admin.tipocombustiveis.show', compact('tipocombustivel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoCombustivel $tipocombustivel)
    {
        return view('admin.tipocombustiveis.edit', compact('tipocombustivel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoCombustivel $tipocombustivel)
    {
        $validated = $request->validate([
            'descricao' => 'required|string|max:500',
            'unidade_medida' => 'nullable|string|max:100',
            'ncm' => 'nullable|integer',
        ]);

        try {
            $updated = $tipocombustivel->update([
                'descricao' => $validated['descricao'],
                'unidade_medida' => $validated['unidade_medida'],
                'ncm' => $validated['ncm'],
                'data_alteracao' => now()
            ]);

            if (!$updated) {
                return  redirect()
                    ->route('admin.tipocombustiveis.index')
                    ->withNotification([
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possivel editar o tipo de Combustivel!'
                    ]);
            }

            return redirect()
                ->route('admin.tipocombustiveis.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Tipo de Combustivel editado com sucesso!'
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
            $tipocombustivel = TipoCombustivel::findOrFail($id);
            $tipocombustivel->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            LOG::INFO('ERRO AO EXCLUI O REGISTRO: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o tipo de combustivel: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoAcertoEstoque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class TipoAcertoEstoqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TipoAcertoEstoque::query();

        if ($request->filled('id_tipo_acerto_estoque')) {
            $query->where('id_tipo_acerto_estoque', $request->id_tipo_acerto_estoque);
        }

        if ($request->filled('descricao_tipo_acerto')) {
            $query->where('descricao_tipo_acerto', $request->descricao_tipo_acerto);
        }

        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', $request->data_inclusao);
        }

        $tipoacertos = $query->orderBy('id_tipo_acerto_estoque', 'asc')->paginate(10);

        $descricao_tipo_acerto = TipoAcertoEstoque::select('descricao_tipo_acerto as label', 'descricao_tipo_acerto as value')
            ->orderBy('label')
            ->get()
            ->toArray();


        return view('admin.tipoacertoestoque.index', compact('tipoacertos', 'descricao_tipo_acerto'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipoacertoestoque.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao_tipo_acerto' => 'required|string|max:500'

        ]);

        $tipoacerto = new TipoAcertoEstoque();
        $tipoacerto->data_inclusao = now();
        $tipoacerto->descricao_tipo_acerto = $request->descricao_tipo_acerto;
        $tipoacerto->save();
        return redirect()->route('admin.tipoacertoestoque.index')
            ->with('success', 'Tipo criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoAcertoEstoque $tipoacerto)
    {
        $tipoacerto = TipoAcertoEstoque::findOrFail($tipoacerto);
        return view('admin.tipoacertoestoque.show', compact('tipoacerto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoAcertoEstoque $tipoacerto)
    {
        return view('admin.tipoacertoestoque.edit', compact('tipoacerto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoAcertoEstoque $tipoacerto)
    {
        $validated = $request->validate([
            'descricao_tipo_acerto' => 'required|string|max:500',
        ]);

        $updated = $tipoacerto->update([
            'descricao_tipo_acerto' => $validated['descricao_tipo_acerto'],
            'data_alteracao' => now()
        ]);

        if (!$updated) {
            return back()->withErrors('Não foi possível atualizar o registro.');
        }

        return redirect()->route('admin.tipoacertoestoque.index')
            ->with('success', 'Tipo atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoAcertoEstoque $tipoacerto)
    {
        $tipoacerto->delete();

        return redirect()->route('admin.tipoacertoestoque.index')
            ->with('success', 'Tipo excluído com sucesso!');
    }
}

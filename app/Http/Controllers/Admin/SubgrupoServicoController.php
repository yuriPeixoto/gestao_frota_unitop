<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GrupoServico;
use App\Models\SubgrupoServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubgrupoServicoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subgruposervicos = SubgrupoServico::with('grupoServico') // Carrega o relacionamento
            ->orderBy('id_subgrupo', 'asc')
            ->paginate(10);
        return view('admin.subgruposervicos.index', compact('subgruposervicos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $grupos = GrupoServico::select('id_grupo', 'descricao_grupo')->get();
        return view('admin.subgruposervicos.create', compact('grupos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao_subgrupo' => 'required|string|max:500',
            'id_grupo_servico' => 'required|integer|',
        ]);

        $subgruposervico = new SubgrupoServico();
        $subgruposervico->data_inclusao = now();
        $subgruposervico->descricao_subgrupo = $request['descricao_subgrupo'];
        $subgruposervico->id_grupo_servico = $request['id_grupo_servico'];
        $subgruposervico->save();
        return redirect()->route('admin.subgruposervicos.index')
            ->with('success', 'Subgrupo de serviço criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(SubgrupoServico $subgruposervico)
    {
        $subgruposervico = SubgrupoServico::findOrFail($subgruposervico);
        return view('admin.subgruposervicos.show', compact('subgruposervico'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubgrupoServico $subgruposervico)
    {
        $grupos = GrupoServico::select('id_grupo', 'descricao_grupo')->get();

        return view('admin.subgruposervicos.edit', compact('subgruposervico', 'grupos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubgrupoServico $subgruposervico)
    {

        $validated = $request->validate([
            'descricao_subgrupo' => 'required|string|max:500',
            'id_grupo_servico' => 'required|integer'
        ]);

        $updated = $subgruposervico->update([
            'descricao_subgrupo' => $validated['descricao_subgrupo'],
            'id_grupo_servico' => $validated['id_grupo_servico'],
            'data_alteracao' => now()
        ]);

        if (!$updated) {
            return back()->withErrors('Não foi possível atualizar o registro.');
        }

        return redirect()->route('admin.subgruposervicos.index')
            ->with('success', 'Subgrupo atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubgrupoServico $subgruposervico)
    {
        $subgruposervico->delete();



        return redirect()->route('admin.subgruposervicos.index')
            ->with('success', 'Subgrupo excluído com sucesso!');
    }
}

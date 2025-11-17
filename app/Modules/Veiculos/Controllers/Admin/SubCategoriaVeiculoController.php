<?php

namespace App\Modules\Veiculos\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubCategoriaVeiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubCategoriaVeiculoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SubCategoriaVeiculo::query();

        if ($request->filled('id_subcategoria')) {
            $query->where('id_subcategoria', $request->id_subcategoria);
        }

        if ($request->filled('data_inclusao_inicial')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao_inicial);
        }

        if ($request->filled('data_inclusao_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_inclusao_final);
        }

        if ($request->filled('descricao_subcategoria')) {
            $query->where('descricao_subcategoria', 'ilike', '%' . $request->descricao_subcategoria . '%');
        }

        $tipo = SubCategoriaVeiculo::select('id_subcategoria as value', 'descricao_subcategoria as label')
            ->orderBy('descricao_subcategoria')
            ->get();

        $subCategoria = $query->latest('id_subcategoria')
            ->paginate(40)
            ->appends($request->query());

        if ($request->header('HX-Request')) {
            return view('admin.subcategoriaveiculos._table', compact('subCategoria'));
        }

        return view('admin.subcategoriaveiculos.index', compact('subCategoria', 'tipo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.subcategoriaveiculos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        //dd($request->all());
        try {
            $subcategoriaveiculo = new SubCategoriaVeiculo();

            $subcategoriaveiculo->data_inclusao = now();
            $subcategoriaveiculo->descricao_subcategoria = $request->input('descricao_subcategoria');

            $subcategoriaveiculo->save();

            return redirect()
                ->route('admin.subcategoriaveiculos.index')
                ->withNotification([
                    'title'   => 'Subcategoria de veículo criada',
                    'type'    => 'success',
                    'message' => 'Subcategoria de veículo criada com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar subcategoria de veículo:', [
                'message' => $e->getMessage(),
                'data'    => $request->all()
            ]);

            return back()->withErrors('Ocorreu um erro ao criar a subcategoria de veículo: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SubCategoriaVeiculo $subCategoriaVeiculo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubCategoriaVeiculo $subcategoriaveiculo)
    {
        return view('admin.subcategoriaveiculos.edit', compact('subcategoriaveiculo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubCategoriaVeiculo $subcategoriaveiculo)
    {


        try {
            $updated = $subcategoriaveiculo->update([
                'descricao_subcategoria' => $request->input('descricao_subcategoria'),
                'data_alteracao' => now()
            ]);

            if (!$updated) {
                return back()->withErrors('Não foi possível atualizar o registro.');
            }

            return redirect()
                ->route('admin.subcategoriaveiculos.index')
                ->withNotification([
                    'title'   => 'Subcategoria de veículo atualizada',
                    'type'    => 'success',
                    'message' => 'Subcategoria de veículo atualizada com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro na atualização:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors('Ocorreu um erro ao atualizar o registro: ' . $e->getMessage());
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $subcategoriaVeiculo = SubCategoriaVeiculo::findOrFail($id);
            $subcategoriaVeiculo->delete();

            return redirect()
                ->route('admin.subcategoriaveiculos.index')
                ->with([
                    'title'   => 'Subcategoria de veículo Excluída',
                    'type'    => 'success',
                    'message' => 'Subcategoria de veículo Excluída com sucesso!'
                ]);
        } catch (\Exception $e) {
            LOG::INFO('ERRO AO EXCLUI O REGISTRO: ' . $e->getMessage());

            return back()->with('Ocorreu um erro ao atualizar o registro: ' . $e->getMessage());
        }
    }
}

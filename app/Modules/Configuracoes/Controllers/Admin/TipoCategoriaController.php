<?php

namespace App\Modules\Configuracoes\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoCategoria;
use App\Traits\Searchable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TipoCategoriaController extends Controller
{
    use Searchable;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TipoCategoria::query()->withCount('veiculo'); // Adiciona contagem de veículos

        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        if ($request->filled('data_inclusao_inicial')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao_inicial);
        }

        if ($request->filled('data_inclusao_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_inclusao_final);
        }

        if ($request->filled('descricao_categoria')) {
            $query->where('descricao_categoria', 'ilike', '%' . $request->descricao_categoria . '%');
        }

        $categoriaVeiculos = $query->latest('id_categoria')
            ->paginate(40)
            ->appends($request->query());

        if ($request->header('HX-Request')) {
            return view('admin.tipocategorias._table', compact('categoriaVeiculos'));
        }

        return view('admin.tipocategorias.index', compact('categoriaVeiculos'));
    }

    public function getSearchColumns()
    {
        return ['descricao_categoria'];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipocategorias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao_categoria' => 'required|string|max:500'

        ]);
        $tipocategoria = new TipoCategoria();
        $tipocategoria->data_inclusao = now();
        $tipocategoria->descricao_categoria = $request->descricao_categoria;
        $tipocategoria->save();
        return redirect()->route('admin.tipocategorias.index')->withNotification([
            'title'   => 'Sucesso!',
            'type'    => 'success',
            'message' => 'Novo tipo de categoria adicionado com sucesso!'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoCategoria $tipocategoria)
    {
        $tipofornecedores = TipoCategoria::findOrFail($tipocategoria);
        return view('admin.tipocategorias.show', compact('tipocategoria'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoCategoria $tipocategoria)
    {
        return view('admin.tipocategorias.edit', compact('tipocategoria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoCategoria $tipocategoria)
    {
        $validated = $request->validate([
            'descricao_categoria' => 'required|string|max:500',
        ]);

        try {
            $updated = $tipocategoria->update([
                'descricao_categoria' => $validated['descricao_categoria'],
                'data_alteracao' => now()
            ]);

            if (!$updated) {
                return  redirect()
                    ->route('admin.tipocategorias.index')
                    ->withNotification([
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possivel editar o tipo de Categoria!'
                    ]);
            }

            return redirect()
                ->route('admin.tipocategorias.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Tipo de Categoria editado com sucesso!'
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
            $tipocategoria = TipoCategoria::findOrFail($id);
            $tipocategoria->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            LOG::INFO('ERRO AO EXCLUI O REGISTRO: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o tipo de Categoria: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
}

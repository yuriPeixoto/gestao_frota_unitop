<?php

namespace App\Modules\Pneus\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoDescarte;
use Exception;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DescarteTipoController extends Controller
{
    public function index(Request $request)
    {
        $query = TipoDescarte::query();

        if ($request->filled('id_tipo_descarte')) {
            $query->where('id_tipo_descarte', $request->id_tipo_descarte);
        }
        if ($request->filled('descricao_tipo_descarte')) {
            $query->where('descricao_tipo_descarte', $request->descricao_tipo_descarte);
        }

        if ($request->filled('data_inclusao')) {
            $query->where('data_inclusao', $request->data_inclusao);
        }

        if ($request->filled('data_alteracao')) {
            $query->where('data_alteracao', $request->data_alteracao);
        }

        $descarte = $query->latest('id_tipo_descarte') // use o PK correto
            ->paginate(20)
            ->appends($request->query());

        $tipoDescarte = TipoDescarte::select('id_tipo_descarte as value', 'descricao_tipo_descarte as label')
            ->orderBy('descricao_tipo_descarte')
            ->get();

        return view('admin.descartetipopneu.index', compact('descarte', 'tipoDescarte'));
    }

    public function create()
    {
        $descarte = TipoDescarte::where('id_tipo_descarte')->first();

        return view('admin.descartetipopneu.create', compact('descarte'));
    }

    public function store(Request $request)
    {
        $descarte = $request->validate([
            'descricao_tipo_descarte' => 'required|string|max:100'
        ]);


        try {
            DB::beginTransaction();



            TipoDescarte::create([
                'data_inclusao' => now(),
                'descricao_tipo_descarte' => $descarte['descricao_tipo_descarte'],
            ]);

            DB::commit();

            return redirect()
                ->route('admin.descartetipopneu.index')
                ->with('success', 'Cadastro realizado com sucesso!');
        } catch (Exception  $e) {
            DB::rollBack();
            Log::INFO('Erro ao cadastrar o tipo descarte: ' . $e->getMessage());
            return redirect()->route('admin.descartetipopneu.index')->with(['error', 'Erro ao cadastrar tipo descarte: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $descarte = TipoDescarte::find($id);



        return view('admin.descartetipopneu.edit', compact('descarte'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'descricao_tipo_descarte' => 'required|string|max:100'
        ]);

        try {
            DB::beginTransaction();

            $descarte = TipoDescarte::findOrFail($id);

            $descarte->update([
                'data_alteracao' => now(),
                'descricao_tipo_descarte' => $data['descricao_tipo_descarte'],
            ]);

            DB::commit();

            return redirect()
                ->route('admin.descartetipopneu.index')
                ->with('success', 'Tipo descarte atualizado com sucesso!');
        } catch (Exception  $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar o tipo descarte: ' . $e->getMessage());

            return redirect()
                ->route('admin.descartetipopneu.index')
                ->with('error', 'Erro ao atualizar tipo descarte: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $descarte = TipoDescarte::findOrFail($id);
            $descarte->delete();

            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tipo descarte excluído com sucesso!'
                ]);
            }

            return redirect()
                ->route('admin.descartetipopneu.index')
                ->with('success', 'Tipo descarte excluído com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir o tipo descarte: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao excluir tipo descarte: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->route('admin.descartetipopneu.index')
                ->with('error', 'Erro ao excluir tipo descarte: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Modules\Pessoal\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Pessoal\Models\TipoPessoal;
use Illuminate\Support\Facades\Log;

class TipoPessoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $query = TipoPessoal::query();

        if ($request->filled('id_tipo_pessoal')) {
            $query->where('id_tipo_pessoal', $request->id_tipo_pessoal);
        }

        if ($request->filled('data_inclusao_inicial')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao_inicial);
        }

        if ($request->filled('data_inclusao_final')) {
            $query->whereDate('data_inclusao', '<=', $request->data_inclusao_final);
        }

        if ($request->filled('descricao_tipo')) {
            $query->where('descricao_tipo', 'ilike', '%' . $request->descricao_tipo . '%');
        }

        $tipoPessoal = $query->latest('id_tipo_pessoal')
            ->paginate(40)
            ->appends($request->query());

        $pessoal = TipoPessoal::select('descricao_tipo as value', 'descricao_tipo as label')->orderBy('descricao_tipo')->get();

        // Verifica se a requisição é feita via HTMX
        if ($request->header('HX-Request')) {
            return view('admin.tipopessoal._table', compact('tipoPessoal', 'pessoal'));
        }

        return view('admin.tipopessoal.index', compact('tipoPessoal', 'pessoal'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipopessoal.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao_tipo' => 'required|string|max:500',
        ]);

        try {
            $tipopessoal = new TipoPessoal();

            $tipopessoal->data_inclusao = now();
            $tipopessoal->descricao_tipo = $request->descricao_tipo;

            $teste = $tipopessoal->save();

            return redirect()->route('admin.tipopessoal.index')->withNotification([
                'title'   => 'Sucesso!',
                'type'    => 'success',
                'message' => 'Novo tipo de Pessoal adicionado com sucesso!'
            ]);
        } catch (\Exception $e) {
            LOG::INFO('ERRO AO GRAVAR O REGISTRO ' . $e->getMessage());
            return redirect()->route('admin.tipopessoal.index')->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro ao gravar o registro: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoPessoal $tipopessoal)
    {
        return view('admin.tipopessoal.edit', compact('tipopessoal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoPessoal $tipopessoal)
    {
        $validated = $request->validate([
            'descricao_tipo' => 'required|string|max:500'
        ]);

        try {
            $tipopessoal->descricao_tipo = $validated['descricao_tipo'];
            $tipopessoal->data_alteracao = now();
            $tipopessoal->update();

            if (!$tipopessoal) {
                return  redirect()
                    ->route('admin.tipopessoal.index')
                    ->withNotification([
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possivel editar o tipo de Pessoal!'
                    ]);
            }

            return redirect()
                ->route('admin.tipopessoal.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Tipo de Pessoal editado com sucesso!'
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
            $tipopessoal = TipoPessoal::findOrFail($id);
            $tipopessoal->delete();

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o tipo de Pessoal: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
}

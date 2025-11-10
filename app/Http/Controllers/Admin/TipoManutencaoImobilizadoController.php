<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TipoManutencaoImobilizado;
use Illuminate\Support\Facades\Log;

class TipoManutencaoImobilizadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tipoImobilizados = TipoManutencaoImobilizado::orderBy('id_tipo_manutencao_imobilizado')->get();

        $tipoManutencaoImobilizados = $tipoImobilizados->map(function ($tipo) {
            return [
                'id'              => $tipo->id_tipo_manutencao_imobilizado,
                'descricao'       => $tipo->descricao,
                'Data Inclusão'   => format_date($tipo->data_inclusao),
                'Data Alteração'  => $tipo->data_alteracao ? format_date($tipo->data_alteracao) : ''
            ];
        })->toArray();


        return view('admin.tipomanutencaoimobilizados.index', compact('tipoManutencaoImobilizados'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipomanutencaoimobilizados.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao' => 'required|string|max:255',
        ]);

        $tipoManutencao = new TipoManutencaoImobilizado();
        $tipoManutencao->data_inclusao = now();
        $tipoManutencao->descricao = $request->descricao;
        $tipoManutencao->save();

        return redirect()
            ->route('admin.tipomanutencaoimobilizados.index')
            ->withNotification([
                'title'   => 'Sucesso!',
                'type'    => 'success',
                'message' => 'Novo tipo de manutenção adicionado com sucesso!'
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoManutencaoImobilizado $tipoManutencaoImobilizado)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoManutencaoImobilizado $tipomanutencaoimobilizado)
    {
        return view('admin.tipomanutencaoimobilizados.edit', compact('tipomanutencaoimobilizado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoManutencaoImobilizado $tipomanutencaoimobilizado)
    {
        try {

            $validated = $request->validate([
                'descricao' => 'required|string|max:255',
            ]);

            $updated = $tipomanutencaoimobilizado->update([
                'descricao' => $validated['descricao'],
                'data_alteracao' => now()
            ]);

            if (!$updated) {
                return  redirect()
                    ->route('admin.tipomanutencaoimobilizados.index')
                    ->withNotification([
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possivel editar a manutenção!'
                    ]);
            }

            return redirect()
                ->route('admin.tipomanutencaoimobilizados.index')
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
    public function destroy(int $tipoManutencaoImobilizado)
    {
        try {
            $tipoManutencao = TipoManutencaoImobilizado::findOrFail($tipoManutencaoImobilizado);
            $tipoManutencao->delete();

            return response()->json([
                'notification' => [
                    'title'   => 'Manutenção excluída',
                    'type'    => 'success',
                    'message' => 'Tipo de Manutenção excluída com sucesso'
                ]
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

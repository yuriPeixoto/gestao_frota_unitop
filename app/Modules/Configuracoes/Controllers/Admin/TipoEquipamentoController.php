<?php

namespace App\Modules\Configuracoes\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoEquipamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TipoEquipamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TipoEquipamento::query();

        if ($request->filled('id_tipo_equipamento')) {
            $query->where('id_tipo_equipamento', $request->id_tipo_equipamento);
        }

        if ($request->filled('descricao_tipo')) {
            $query->where('descricao_tipo', $request->descricao_tipo);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('numero_eixos')) {
            $query->where('numero_eixos', $request->numero_eixos);
        }

        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', $request->data_inclusao);
        }

        $tipoEquipamentos = TipoEquipamento::select('descricao_tipo as value', 'descricao_tipo as label')
            ->orderBy('descricao_tipo')
            ->get();

        $eixos = TipoEquipamento::select('numero_eixos as value', 'numero_eixos as label')
            ->orderBy('numero_eixos')
            ->get();

        $tipoequipamentos = $query->latest('id_tipo_equipamento')
            ->paginate(40)
            ->appends($request->query());

        if ($request->header('HX-Request')) {
            return view('admin.tipoequiamentos._table', compact('tipoequipamentos', 'tipoEquipamentos', 'eixos'));
        }


        return view('admin.tipoequipamentos.index', compact('tipoequipamentos', 'tipoEquipamentos', 'eixos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('admin.tipoequipamentos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao_tipo' => 'required|string|max:500',
            'numero_eixos' => 'nullable|integer',
            'numero_pneus_eixo_1' => 'nullable|integer',
            'numero_pneus_eixo_2' => 'nullable|integer',
            'numero_pneus_eixo_3' => 'nullable|integer',
            'numero_pneus_eixo_4' => 'nullable|integer',
        ]);

        $tipoequipamentos = new TipoEquipamento();

        $tipoequipamentos->data_inclusao = now();
        $tipoequipamentos->descricao_tipo = $request->descricao_tipo;
        $tipoequipamentos->numero_eixos = $request->numero_eixos;
        $tipoequipamentos->numero_pneus_eixo_1 = $request->numero_pneus_eixo_1;
        $tipoequipamentos->numero_pneus_eixo_2 = $request->numero_pneus_eixo_2;
        $tipoequipamentos->numero_pneus_eixo_3 = $request->numero_pneus_eixo_3;
        $tipoequipamentos->numero_pneus_eixo_4 = $request->numero_pneus_eixo_4;
        $tipoequipamentos->save();

        return redirect()->route('admin.tipoequipamentos.index')->withNotification([
            'title'   => 'Sucesso!',
            'type'    => 'success',
            'message' => 'Novo tipo de equipamento adicionado com sucesso!'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $tipoequipamentos = TipoEquipamento::findOrFail($id);

        return view('admin.tipoequipamentos.edit', compact('tipoequipamentos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'descricao_tipo'      => 'required|string|max:500',
            'numero_eixos'        => 'nullable|integer',
            'numero_pneus_eixo_1' => 'nullable|integer',
            'numero_pneus_eixo_2' => 'nullable|integer',
            'numero_pneus_eixo_3' => 'nullable|integer',
            'numero_pneus_eixo_4' => 'nullable|integer',
        ]);

        try {
            $tipoequipamentos = TipoEquipamento::findOrFail($id);

            $updated = $tipoequipamentos->update([
                'descricao_tipo' => $validated['descricao_tipo'],
                'numero_eixos' => $validated['numero_eixos'],
                'numero_pneus_eixo_1' => $validated['numero_pneus_eixo_1'],
                'numero_pneus_eixo_2' => $validated['numero_pneus_eixo_2'],
                'numero_pneus_eixo_3' => $validated['numero_pneus_eixo_3'],
                'numero_pneus_eixo_4' => $validated['numero_pneus_eixo_4'],
                'data_alteracao' => now()
            ]);

            Log::info('Resultado da atualização:', [
                'updated' => $updated,
                'descricao_tipo' => $validated['descricao_tipo'],
                'numero_eixos' => $validated['numero_eixos'],
                'numero_pneus_eixo_1' => $validated['numero_pneus_eixo_1'],
                'numero_pneus_eixo_2' => $validated['numero_pneus_eixo_2'],
                'numero_pneus_eixo_3' => $validated['numero_pneus_eixo_3'],
                'numero_pneus_eixo_4' => $validated['numero_pneus_eixo_4'],
            ]);

            if (!$updated) {
                return  redirect()
                    ->route('admin.tipoequipamentos.index')
                    ->withNotification([
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possivel editar o tipo de equipamento!'
                    ]);
            }

            return redirect()
                ->route('admin.tipoequipamentos.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Tipo de equipamento editado com sucesso!'
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
    public function destroy(string $id)
    {

        try {
            DB::beginTransaction();

            $tipoequipamento = TipoEquipamento::findOrFail($id);

            if ($tipoequipamento->veiculos()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir porque há veículos vinculados a este tipo de equipamento.'
                ], 400);
            }

            if (isset($tipoequipamento)) {
                $tipoequipamento->delete();
                DB::commit();
                return response()->json([
                    'success' => true
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Tipo de equipamento não encontrado'
            ]);
        } catch (\Exception $e) {
            LOG::info($e->getMessage());

            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o tipo de equipamento: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
}

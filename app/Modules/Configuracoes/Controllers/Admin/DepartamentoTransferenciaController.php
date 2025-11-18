<?php

namespace App\Modules\Configuracoes\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Log;
use App\Models\DepartamentoTransferencia;
use Illuminate\Http\Request;

class DepartamentoTransferenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = DepartamentoTransferencia::query();

            if ($request->filled('id_departamento_transferencia')) {
                $query->where('id_departamento_transferencia', $request->id_departamento_transferencia);
            }

            if ($request->filled('data_inclusao_inicial')) {
                $query->whereDate('data_inclusao', '>=', $request->data_inclusao_inicial);
            }

            if ($request->filled('data_inclusao_final')) {
                $query->whereDate('data_inclusao', '<=', $request->data_inclusao_final);
            }

            if ($request->filled('departamento')) {
                $query->where('departamento', $request->departamento);
            }

            $departamentoTransferencia = $query->latest('id_departamento_transferencia')
                ->paginate(15)
                ->appends($request->query());

            return view('admin.departamentotransferencia.index', compact('departamentoTransferencia'));
        } catch (\Exception $e) {
            Log::error('Erro ao listar Departamento: ' . $e->getMessage());
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Não foi possível carregar a lista de Departamento.'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.departamentotransferencia.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'departamento' => 'required',
        ]);

        try {
            $departamentoTransferencia = new DepartamentoTransferencia();
            $departamentoTransferencia->departamento = $request->departamento;
            $departamentoTransferencia->data_inclusao = now();
            $departamentoTransferencia->save();

            return redirect()->to(route('admin.departamentotransferencia.index'))
                ->with('notification', [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Novo Departamento adicionado com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar Departamento de Transferencia: ' . $e->getMessage());
            return back()->withInput()->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro ao cadastrar o Departamento de Transferencia: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $departamentoTransferencia = DepartamentoTransferencia::findOrFail($id);
            return view('admin.departamentotransferencia.edit', compact('departamentoTransferencia'));
        } catch (\Exception $e) {
            Log::error('Erro ao editar Departamento: ' . $e->getMessage());
            return redirect()->route('admin.departamentotransferencia.index')->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Departamento não encontrado.'
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'departamento' => 'required',
            ]);

            $departamentoTransferencia = DepartamentoTransferencia::findOrFail($id);

            $updated = $departamentoTransferencia->update([
                'departamento' => $validated['departamento'],
                'data_alteracao' => now(),
            ]);

            Log::info('Resultado da atualização:', [
                'updated' => $updated,
                'departamento' => $validated['departamento']
            ]);

            if (!$updated) {
                return redirect()->to(route('admin.departamentotransferencia.index'))
                    ->with('notification', [
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possível editar o Departamento!'
                    ]);
            }

            return redirect()->to(route('admin.departamentotransferencia.index'))
                ->with('notification', [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Departamento de transferencia editado com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar o Departamento de transferencia: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return back()->withInput()->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro ao atualizar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $departamentoTransferencia = DepartamentoTransferencia::findOrFail($id);
            $departamentoTransferencia->delete();

            return response()->json([
                'notification' => [
                    'title'   => 'Departamento de transferencia excluída',
                    'type'    => 'success',
                    'message' => 'Departamento de transferencia excluída com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }
}

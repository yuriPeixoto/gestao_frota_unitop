<?php

namespace App\Modules\Configuracoes\Controllers\Admin;

use App\Helpers\PhoneHelper;
use App\Http\Controllers\Controller;
use App\Models\DepartamentoTransferencia;
use App\Models\TelefoneTransferencia;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class TelefoneTransferenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = TelefoneTransferencia::query();

            if ($request->filled('id_telefone_transferencia')) {
                $query->where('id_telefone_transferencia', $request->id_telefone_transferencia);
            }

            if ($request->filled('data_inclusao_inicial')) {
                $query->whereDate('data_inclusao', '>=', $request->data_inclusao_inicial);
            }

            if ($request->filled('data_inclusao_final')) {
                $query->whereDate('data_inclusao', '<=', $request->data_inclusao_final);
            }

            if ($request->filled('nome')) {
                $query->where('nome', $request->nome);
            }

            if ($request->filled('departamento')) {
                $query->where('departamento', $request->departamento);
            }

            $telefoneTransferencia = $query->latest('id_telefone_transferencia')
                ->paginate(15)
                ->appends($request->query());

            return view('admin.telefonetransferencia.index', compact('telefoneTransferencia'));
        } catch (\Exception $e) {
            Log::error('Erro ao listar os Telefones: ' . $e->getMessage());
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Não foi possível carregar a lista de Telefone.'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departamentoTransferencia = DepartamentoTransferencia::select('departamento as label', 'id_departamento_transferencia as value')
            ->orderBy('label')
            ->get()
            ->toArray();

        return view('admin.telefonetransferencia.create', compact('departamentoTransferencia'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required',
            'telefone' => 'required',
            'departamento' => 'required',
        ]);

        try {
            $telefone = PhoneHelper::sanitizePhone($request->telefone);

            $telefoneTransferencia = new TelefoneTransferencia();
            $telefoneTransferencia->nome = $request->nome;
            $telefoneTransferencia->telefone = $telefone;
            $telefoneTransferencia->departamento = $request->departamento;
            $telefoneTransferencia->data_inclusao = now();
            $telefoneTransferencia->save();

            return redirect()->to(route('admin.telefonetransferencia.index'))
                ->with('notification', [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Novo Telefone adicionado com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar Telefone: ' . $e->getMessage());
            return back()->withInput()->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro ao cadastrar o Telefone: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $telefoneTransferencia = TelefoneTransferencia::findOrFail($id);

        $departamentoTransferencia = DepartamentoTransferencia::select('departamento as label', 'id_departamento_transferencia as value')
            ->orderBy('label')
            ->get()
            ->toArray();

        return view('admin.telefonetransferencia.edit', compact('telefoneTransferencia', 'departamentoTransferencia'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'nome' => 'required',
                'telefone' => 'required',
                'departamento' => 'required',
            ]);

            $telefoneTransferencia = TelefoneTransferencia::findOrFail($id);

            $updated = $telefoneTransferencia->update([
                'nome'           => $validated['nome'],
                'telefone'       => $validated['telefone'],
                'departamento'   => $validated['departamento'],
                'data_alteracao' => now(),
            ]);

            if (!$updated) {
                return redirect()->to(route('admin.telefonetransferencia.index'))
                    ->with('notification', [
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possível editar o Telefone!'
                    ]);
            }

            return redirect()->to(route('admin.telefonetransferencia.index'))
                ->with('notification', [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Telefone editado com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar Status Telefone: ' . $e->getMessage());
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
            $telefoneTransferencia = TelefoneTransferencia::findOrFail($id);
            $telefoneTransferencia->delete();

            return response()->json([
                'notification' => [
                    'title'   => 'Telefone excluída',
                    'type'    => 'success',
                    'message' => 'Telefone excluída com sucesso'
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

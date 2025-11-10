<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Log;
use App\Models\TipoSolicitacao;
use Illuminate\Http\Request;

class TipoSolicitacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = TipoSolicitacao::query();

            if ($request->filled('id')) {
                $query->where('id', $request->id);
            }

            if ($request->filled('data_inclusao_inicial')) {
                $query->whereDate('data_inclusao', '>=', $request->data_inclusao_inicial);
            }

            if ($request->filled('data_inclusao_final')) {
                $query->whereDate('data_inclusao', '<=', $request->data_inclusao_final);
            }

            if ($request->filled('descricao')) {
                $query->where('descricao', $request->descricao);
            }

            $tipoSolicitacao = $query->latest('id')
                ->paginate(15)
                ->appends($request->query());

            return view('admin.tiposolicitacao.index', compact('tipoSolicitacao'));
        } catch (\Exception $e) {
            Log::error('Erro ao listar tipos de Solicitação: ' . $e->getMessage());
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Não foi possível carregar a lista de tipos de Solicitação.'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tiposolicitacao.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao' => 'required|string|max:500',
        ]);

        try {
            $tiposolicitacao = new TipoSolicitacao();
            $tiposolicitacao->descricao = $request->descricao;
            $tiposolicitacao->data_inclusao = now();
            $tiposolicitacao->save();

            return redirect()->to(route('admin.tiposolicitacao.index'))
                ->with('notification', [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Novo tipo de solicitação adicionado com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar tipo de solicitação: ' . $e->getMessage());
            return back()->withInput()->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro ao cadastrar o tipo de solicitação: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoSolicitacao $tipoSolicitacao)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $tipoSolicitacao = TipoSolicitacao::findOrFail($id);
            return view('admin.tiposolicitacao.edit', compact('tipoSolicitacao'));
        } catch (\Exception $e) {
            Log::error('Erro ao editar tipo de Solicitação: ' . $e->getMessage());
            return redirect()->route('admin.tiposolicitacao.index')->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Tipo de Solicitação não encontrado.'
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
                'descricao' => 'required|string|max:500',
            ]);

            $tiposolicitacao = TipoSolicitacao::findOrFail($id);

            $updated = $tiposolicitacao->update([
                'descricao' => $validated['descricao'],
                'data_alteracao' => now(),
            ]);

            Log::info('Resultado da atualização:', [
                'updated' => $updated,
                'descricao' => $validated['descricao']
            ]);

            if (!$updated) {
                return redirect()->to(route('admin.tiposolicitacao.index'))
                    ->with('notification', [
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possível editar o tipo de Solicitação!'
                    ]);
            }

            return redirect()->to(route('admin.tiposolicitacao.index'))
                ->with('notification', [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Tipo de Solicitação editado com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar tipo de Solicitação: ' . $e->getMessage());
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
            $tiposolicitacao = TipoSolicitacao::findOrFail($id);
            $tiposolicitacao->delete();

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

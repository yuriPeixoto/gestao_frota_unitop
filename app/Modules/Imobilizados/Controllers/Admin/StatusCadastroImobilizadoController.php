<?php

namespace App\Modules\Imobilizados\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Log;
use App\Modules\Imobilizados\Models\StatusCadastroImobilizado;
use Illuminate\Http\Request;

class StatusCadastroImobilizadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = StatusCadastroImobilizado::query();

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

            $statusCadastroImobilizado = $query->latest('id')
                ->paginate(15)
                ->appends($request->query());

            return view('admin.statuscadastroimobilizado.index', compact('statusCadastroImobilizado'));
        } catch (\Exception $e) {
            Log::error('Erro ao listar Status Cadastro Imobilizado: ' . $e->getMessage());
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Não foi possível carregar a lista de Status Cadastro Imobilizado.'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.statuscadastroimobilizado.create');
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
            $statusCadastroImobilizado = new StatusCadastroImobilizado();
            $statusCadastroImobilizado->descricao = $request->descricao;
            $statusCadastroImobilizado->data_inclusao = now();
            $statusCadastroImobilizado->save();

            return redirect()->to(route('admin.statuscadastroimobilizado.index'))
                ->with('notification', [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Novo Status Cadastro Imobilizado adicionado com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar Status Cadastro Imobilizado: ' . $e->getMessage());
            return back()->withInput()->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro ao cadastrar o Status Cadastro Imobilizado: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StatusCadastroImobilizado $statusCadastroImobilizado)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $statusCadastroImobilizado = StatusCadastroImobilizado::findOrFail($id);
            return view('admin.statuscadastroimobilizado.edit', compact('statusCadastroImobilizado'));
        } catch (\Exception $e) {
            Log::error('Erro ao editar Status Cadastro Imobilizado: ' . $e->getMessage());
            return redirect()->route('admin.statuscadastroimobilizado.index')->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Status Cadastro Imobilizado não encontrado.'
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

            $statusCadastroImobilizado = StatusCadastroImobilizado::findOrFail($id);

            $updated = $statusCadastroImobilizado->update([
                'descricao' => $validated['descricao'],
                'data_alteracao' => now(),
            ]);

            Log::info('Resultado da atualização:', [
                'updated' => $updated,
                'descricao' => $validated['descricao']
            ]);

            if (!$updated) {
                return redirect()->to(route('admin.statuscadastroimobilizado.index'))
                    ->with('notification', [
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possível editar o Status Cadastro Imobilizado!'
                    ]);
            }

            return redirect()->to(route('admin.statuscadastroimobilizado.index'))
                ->with('notification', [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Status Cadastro Imobilizado editado com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar Status Cadastro Imobilizado: ' . $e->getMessage());
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
            $statusCadastroImobilizado = StatusCadastroImobilizado::findOrFail($id);
            $statusCadastroImobilizado->delete();

            return response()->json([
                'notification' => [
                    'title'   => 'Requisição Imobilizado excluída',
                    'type'    => 'success',
                    'message' => 'Requisição Imobilizado excluída com sucesso'
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

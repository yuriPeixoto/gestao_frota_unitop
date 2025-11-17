<?php

namespace App\Modules\Veiculos\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoVeiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TipoVeiculoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = TipoVeiculo::query();

            if ($request->filled('id')) {
                $query->where('id', $request->id);
            }

            if ($request->filled('descricao')) {
                $query->where('descricao', $request->descricao);
            }

            if ($request->filled('data_inclusao')) {
                $query->whereDate('data_inclusao', $request->data_inclusao);
            }

            $tipoveiculos = $query->latest('id')
                ->paginate(15)
                ->appends($request->query());

            if ($request->header('HX-Request')) {
                return view('admin.tipoveiculos._table', compact('tipoveiculos'));
            }

            $descricao = TipoVeiculo::select('id as label', 'descricao as value')
                ->orderBy('descricao')
                ->get()
                ->toArray();

            return view('admin.tipoveiculos.index', compact(
                'tipoveiculos',
                'descricao'
            ));
        } catch (\Exception $e) {
            Log::error('Erro ao listar tipos de veículos: ' . $e->getMessage());
            return back()->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Ocorreu um erro ao carregar os tipos de veículos'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipoveiculos.create');
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
            $tipoveiculo = new TipoVeiculo();
            $tipoveiculo->descricao = $request->descricao;
            // $tipoveiculo->data_inclusao = now();
            $tipoveiculo->save();

            return redirect()->to(route('admin.tipoveiculos.index'))
                ->with('notification', [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Novo tipo de veículo adicionado com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar tipo de veículo: ' . $e->getMessage());
            return back()->withInput()->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro ao cadastrar o tipo de veículo: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        try {
            $tipoveiculos = TipoVeiculo::findOrFail($id);
            return view('admin.tipoveiculos.edit', compact('tipoveiculos'));
        } catch (\Exception $e) {
            Log::error('Erro ao editar tipo de veículo: ' . $e->getMessage());
            return redirect()->route('admin.tipoveiculos.index')->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Tipo de veículo não encontrado.'
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

            $tipoveiculo = TipoVeiculo::findOrFail($id);

            $updated = $tipoveiculo->update([
                'descricao' => $validated['descricao'],
                //'data_alteracao' => now(),
            ]);

            Log::info('Resultado da atualização:', [
                'updated' => $updated,
                'descricao' => $validated['descricao']
            ]);

            if (!$updated) {
                return redirect()->to(route('admin.tipoveiculos.index'))
                    ->with('notification', [
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possível editar o tipo de Veículo!'
                    ]);
            }

            return redirect()->to(route('admin.tipoveiculos.index'))
                ->with('notification', [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Tipo de Veículo editado com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar tipo de veículo: ' . $e->getMessage());
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
            // Verificar se o tipo de veículo está sendo usado na tabela veiculo
            $emUso = DB::connection('pgsql')->table('veiculo')
                ->where('id_tipo_veiculo', $id)
                ->exists();

            if ($emUso) {
                return response()->json([
                    'notification' => [
                        'title'   => 'Não é possível excluir',
                        'type'    => 'error',
                        'message' => 'Este tipo de veículo está sendo utilizado por um ou mais veículos e não pode ser excluído.'
                    ]
                ], 422);
            }

            $tipoveiculo = TipoVeiculo::findOrFail($id);
            $tipoveiculo->delete();

            return response()->json([
                'notification' => [
                    'title'   => 'Sucesso',
                    'type'    => 'success',
                    'message' => 'Tipo de veículo excluído com sucesso!'
                ]
            ]);
        } catch (QueryException $e) {
            // Verificar a mensagem de erro específica para chave estrangeira do PostgreSQL
            if (
                str_contains($e->getMessage(), 'violates foreign key constraint') ||
                str_contains($e->getMessage(), 'SQLSTATE[23503]')
            ) {

                // Extrair informações detalhadas do erro para um diagnóstico melhor
                $errorInfo = $e->errorInfo[2] ?? '';
                $table = '';

                // Tentar extrair o nome da tabela do erro
                if (preg_match('/table "(.*?)"/', $errorInfo, $matches)) {
                    $table = $matches[1];
                }

                $mensagem = 'Este tipo de veículo não pode ser excluído pois está sendo utilizado em outros registros';

                if (!empty($table)) {
                    $tabela = str_replace('_', ' ', $table);
                    $mensagem .= " da tabela {$tabela}";
                }

                $mensagem .= '.';

                return response()->json([
                    'notification' => [
                        'title'   => 'Não é possível excluir',
                        'type'    => 'error',
                        'message' => $mensagem
                    ]
                ], 422);
            }

            // Outros erros de consulta
            Log::error('Erro ao excluir tipo de veículo: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Erro de banco de dados ao excluir o tipo de veículo.'
                ]
            ], 500);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir tipo de veículo: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o tipo de veículo: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
}

<?php

namespace App\Modules\Compras\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoFornecedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TipoFornecedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = TipoFornecedor::query();

            if ($request->filled('id_tipo_fornecedor')) {
                $query->where('id_tipo_fornecedor', $request->id_tipo_fornecedor);
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

            $tipo = TipoFornecedor::select('descricao_tipo as value', 'descricao_tipo as label')
                ->orderBy('descricao_tipo')
                ->get();
            // Ordena em ordem decrescente de ID (últimos primeiro)
            $tipoFornecedor = $query->latest('id_tipo_fornecedor')
                ->paginate(15)
                ->appends($request->query());

            if ($request->header('HX-Request')) {
                return view('admin.tipofornecedores._table', compact('tipoFornecedor', 'tipo'));
            }

            return view('admin.tipofornecedores.index', compact('tipoFornecedor', 'tipo'));
        } catch (\Exception $e) {
            Log::error('Erro ao listar tipos de fornecedor: ' . $e->getMessage());
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Ocorreu um erro ao carregar os tipos de fornecedor'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipofornecedores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Iniciando store de TipoFornecedor');

        $request->validate([
            'descricao_tipo' => 'required|string|max:500',
        ]);

        try {
            $tipofornecedor = new TipoFornecedor();
            $tipofornecedor->data_inclusao = now();
            $tipofornecedor->descricao_tipo = $request->descricao_tipo;
            $tipofornecedor->save();

            // Forçar redirecionamento explícito com uma mensagem de sessão flash
            return redirect()->to(route('admin.tipofornecedores.index'))
                ->with('notification', [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Novo tipo de fornecedor adicionado com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('ERRO AO GRAVAR O REGISTRO ' . $e->getMessage());
            return back()->withInput()->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro ao cadastrar o tipo de fornecedor!'
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoFornecedor $tipofornecedor)
    {
        return view('admin.tipofornecedores.edit', compact('tipofornecedor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoFornecedor $tipofornecedor)
    {
        $validated = $request->validate([
            'descricao_tipo' => 'required|string|max:500',
        ]);

        try {
            $updated = $tipofornecedor->update([
                'descricao_tipo' => $validated['descricao_tipo'],
                'data_alteracao' => now()
            ]);

            if (!$updated) {
                return redirect()->to(route('admin.tipofornecedores.index'))
                    ->with('notification', [
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possivel editar o tipo de fornecedor!'
                    ]);
            }

            return redirect()->to(route('admin.tipofornecedores.index'))
                ->with('notification', [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Tipo de Fornecedor editado com sucesso!'
                ]);
        } catch (\Exception $e) {
            return back()->withInput()->with('notification', [
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
            // Verificar se o tipo fornecedor está sendo usado por algum fornecedor
            $emUso = DB::connection('pgsql')->table('fornecedor')
                ->where('id_tipo_fornecedor', $id)
                ->exists();

            if ($emUso) {
                return response()->json([
                    'notification' => [
                        'title'   => 'Não é possível excluir',
                        'type'    => 'error',
                        'message' => 'Este tipo de fornecedor está sendo utilizado por um ou mais fornecedores e não pode ser excluído.'
                    ]
                ], 422);
            }

            $tipofornecedor = TipoFornecedor::findOrFail($id);
            $tipofornecedor->delete();

            return response()->json([
                'notification' => [
                    'title'   => 'Sucesso',
                    'type'    => 'success',
                    'message' => 'Tipo de fornecedor excluído com sucesso'
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

                $mensagem = 'Este fornecedor não pode ser excluído pois está sendo utilizado em outros registros';

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
            Log::error('Erro ao excluir tipo de fornecedor: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Erro de banco de dados ao excluir o tipo de fornecedor.'
                ]
            ], 500);
        } catch (\Exception $e) {
            Log::error('ERRO AO EXCLUIR O REGISTRO: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o tipo de fornecedor: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
}

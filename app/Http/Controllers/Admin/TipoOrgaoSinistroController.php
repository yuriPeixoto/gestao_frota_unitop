<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TipoOrgaoSinistro;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TipoOrgaoSinistroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = TipoOrgaoSinistro::query();

            if ($request->filled('id_tipo_orgao')) {
                $query->where('id_tipo_orgao', $request->id_tipo_orgao);
            }

            if ($request->filled('descricao_tipo_orgao')) {
                $query->where('descricao_tipo_orgao', $request->descricao_tipo_orgao);
            }

            if ($request->filled('data_inclusao')) {
                $query->whereDate('data_inclusao', $request->data_inclusao);
            }

            // Obter os dados com paginação diretamente do banco
            $tipoorgaosinistros = $query->orderBy('id_tipo_orgao', 'desc')
                ->paginate(15)  // Define 15 registros por página
                ->through(function ($orgao) {
                    // Formatar cada item para exibição
                    return [
                        'id'              => $orgao->id_tipo_orgao,
                        'descricao'       => $orgao->descricao_tipo_orgao,
                        'Data Inclusão'   => format_date($orgao->data_inclusao),
                        'Data Alteração'  => $orgao->data_alteracao ? format_date($orgao->data_alteracao) : ''
                    ];
                });

            $descricao_tipo_orgao = TipoOrgaoSinistro::select('descricao_tipo_orgao as label', 'descricao_tipo_orgao as value')
                ->orderBy('label')
                ->get()
                ->toArray();

            return view('admin.tipoorgaosinistros.index', compact('tipoorgaosinistros', 'descricao_tipo_orgao'));
        } catch (\Exception $e) {
            Log::error('Erro ao listar tipos de órgão de sinistro: ' . $e->getMessage());
            return redirect()->back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Não foi possível carregar a lista de tipos de órgão de sinistro.'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipoorgaosinistros.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'descricao_tipo_orgao' => 'required|string|max:500',
            ]);

            $tipoorgaosinistros = new TipoOrgaoSinistro();
            $tipoorgaosinistros->descricao_tipo_orgao = $validated['descricao_tipo_orgao'];
            $tipoorgaosinistros->data_inclusao = now();
            $tipoorgaosinistros->save();

            return redirect()->to(route('admin.tipoorgaosinistros.index'))
                ->with('notification', [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Novo tipo de Órgão adicionado com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar tipo de órgão de sinistro: ' . $e->getMessage());
            return back()->withInput()->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Ocorreu um erro ao salvar o tipo de órgão: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $tipoorgaosinistros = TipoOrgaoSinistro::findOrFail($id);
            return view('admin.tipoorgaosinistros.show', compact('tipoorgaosinistros'));
        } catch (\Exception $e) {
            Log::error('Erro ao mostrar tipo de órgão de sinistro: ' . $e->getMessage());
            return redirect()->route('admin.tipoorgaosinistros.index')->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Tipo de órgão não encontrado.'
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoOrgaoSinistro $tipoorgaosinistros)
    {
        return view('admin.tipoorgaosinistros.edit', compact('tipoorgaosinistros'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoOrgaoSinistro $tipoorgaosinistros)
    {
        try {
            $validated = $request->validate([
                'descricao_tipo_orgao' => 'required|string|max:500'
            ]);

            $tipoorgaosinistros->descricao_tipo_orgao = $validated['descricao_tipo_orgao'];
            $tipoorgaosinistros->data_alteracao = now();
            $updated = $tipoorgaosinistros->update();

            if (!$updated) {
                return redirect()->to(route('admin.tipoorgaosinistros.index'))
                    ->with('notification', [
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possivel editar o tipo de Órgão!'
                    ]);
            }

            return redirect()->to(route('admin.tipoorgaosinistros.index'))
                ->with('notification', [
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Tipo de Órgão editado com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar tipo de órgão de sinistro: ' . $e->getMessage());
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
    public function destroy(string $id)
    {
        try {
            // Verificar se o tipo de órgão está sendo usado na tabela sinistro
            $emUso = DB::connection('pgsql')->table('sinistro')
                ->where('id_tipo_orgao', $id)
                ->exists();

            if ($emUso) {
                return response()->json([
                    'notification' => [
                        'title'   => 'Não é possível excluir',
                        'type'    => 'error',
                        'message' => 'Este tipo de órgão está sendo utilizado em registros de sinistro e não pode ser excluído.'
                    ]
                ], 422);
            }

            $tipoorgao = TipoOrgaoSinistro::findOrFail($id);
            $descricao = $tipoorgao->descricao_tipo_orgao;

            // Tenta excluir o registro
            $tipoorgao->delete();

            // Retorna resposta de sucesso para a requisição Ajax
            return response()->json([
                'notification' => [
                    'title'   => 'Tipo excluído',
                    'type'    => 'success',
                    'message' => 'Tipo de Órgão excluído com sucesso'
                ]
            ]);
        } catch (QueryException $e) {
            // Verifica se é um erro de restrição de chave estrangeira
            if (
                str_contains($e->getMessage(), 'foreign key constraint fails') ||
                str_contains($e->getMessage(), 'SQLSTATE[23503]')
            ) {

                // Extrair informações detalhadas do erro para um diagnóstico melhor
                $errorInfo = $e->errorInfo[2] ?? '';
                $table = '';

                // Tentar extrair o nome da tabela do erro
                if (preg_match('/table "(.*?)"/', $errorInfo, $matches)) {
                    $table = $matches[1];
                }

                $mensagem = 'Este tipo de órgão não pode ser excluído pois está sendo utilizado em outros registros';

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
            Log::error('Erro ao excluir tipo de órgão de sinistro: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Erro de banco de dados ao excluir o tipo de órgão.'
                ]
            ], 500);
        } catch (\Exception $e) {
            // Log de erro para debugging
            Log::error('Erro ao excluir tipo de órgão de sinistro: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // Retorna resposta de erro para a requisição Ajax
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o tipo de órgão: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
}

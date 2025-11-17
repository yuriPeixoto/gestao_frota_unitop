<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DepartamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Departamento::query();

            if ($request->filled('id_departamento')) {
                $query->where('id_departamento', $request->id_departamento);
            }

            if ($request->filled('data_inclusao_inicial')) {
                $query->whereDate('data_inclusao', '>=', $request->data_inclusao_inicial);
            }

            if ($request->filled('data_inclusao_final')) {
                $query->whereDate('data_inclusao', '<=', $request->data_inclusao_final);
            }

            if ($request->filled('descricao_departamento')) {
                $query->where('descricao_departamento', 'ilike', '%' . $request->descricao_departamento . '%');
            }

            if ($request->filled('sigla')) {
                $query->where('sigla', 'ilike', '%' . $request->sigla . '%');
            }

            $departamento = Departamento::select('id_departamento as value', 'descricao_departamento as label')->orderBy('descricao_departamento')->get();

            $sigla = Departamento::select('id_departamento as value', 'sigla as label')->orderBy('descricao_departamento')->get();


            $tipoDepartamento = $query->latest('id_departamento')
                ->paginate(15)
                ->appends($request->query());

            if ($request->header('HX-Request')) {
                return view('admin.departamentos._table', compact('tipoDepartamento'));
            }

            return view('admin.departamentos.index', compact('tipoDepartamento', 'departamento', 'sigla'));
        } catch (\Exception $e) {
            Log::error('Erro ao listar departamentos: ' . $e->getMessage());
            return back()->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Ocorreu um erro ao carregar a lista de departamentos.'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.departamentos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descricao_departamento' => 'required|string|max:500',
            'sigla' => 'required|string|max:10',
            'ativo' => 'required|in:1,0'
        ]);

        try {
            $departamento = new Departamento();
            $departamento->descricao_departamento = $request->descricao_departamento;
            $departamento->sigla = $request->sigla;
            $departamento->data_inclusao = now();
            $departamento->ativo = $request->ativo === '1' ? true : false;
            $departamento->save();

            return redirect()->to(route('admin.departamentos.index'))
                ->with('notification', [
                    'title'   => 'Departamento criado',
                    'type'    => 'success',
                    'message' => 'Departamento criado com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar departamento: ' . $e->getMessage());
            return back()->withInput()->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro ao cadastrar o departamento: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Departamento $departamento)
    {
        try {
            return view('admin.departamentos.show', compact('departamento'));
        } catch (\Exception $e) {
            Log::error('Erro ao mostrar departamento: ' . $e->getMessage());
            return redirect()->route('admin.departamentos.index')->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Departamento não encontrado.'
            ]);
        }
    }

    public function findAll()
    {
        $departamentos = Departamento::orderBy('id_departamento', 'asc')->get();
        return response()->json($departamentos);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Departamento $departamento)
    {
        try {
            return view('admin.departamentos.edit', compact('departamento'));
        } catch (\Exception $e) {
            Log::error('Erro ao editar departamento: ' . $e->getMessage());
            return redirect()->route('admin.departamentos.index')->with('notification', [
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Departamento não encontrado.'
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Departamento $departamento)
    {
        $validated = $request->validate([
            'descricao_departamento' => 'required|string|max:500',
            'sigla' => 'required|string|max:10',
            'ativo' => 'required|in:1,0'
        ]);

        try {
            $updated = $departamento->update([
                'descricao_departamento' => $validated['descricao_departamento'],
                'sigla' => $validated['sigla'],
                'ativo' => $validated['ativo'] === '1' ? true : false,
                'data_alteracao' => now()
            ]);

            if (!$updated) {
                return redirect()->to(route('admin.departamentos.index'))
                    ->with('notification', [
                        'title'   => 'Erro!',
                        'type'    => 'error',
                        'message' => 'Não foi possível atualizar o departamento!'
                    ]);
            }

            // Limpar cache relacionado a este departamento
            $this->limparCacheDepartamento($departamento->id_departamento);

            return redirect()->to(route('admin.departamentos.index'))
                ->with('notification', [
                    'title'   => 'Departamento alterado',
                    'type'    => 'success',
                    'message' => 'Departamento alterado com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar departamento: ' . $e->getMessage());
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
            // Verificar se o departamento tem veículos associados
            $emUsoVeiculo = DB::connection('pgsql')->table('veiculo')
                ->where('id_departamento', $id)
                ->exists();

            // Verificar se o departamento tem usuários associados
            $emUsoUsuarios = DB::connection('pgsql')->table('users')
                ->where('departamento_id', $id)
                ->exists();

            // Verificar se o departamento tem solicitações de compra associadas
            $emUsoSolicitacoes = DB::connection('pgsql')->table('solicitacoescompras')
                ->where('id_departamento', $id)
                ->exists();

            // Verificar se o departamento tem pessoal associado
            $emUsoPessoal = DB::connection('pgsql')->table('pessoal')
                ->where('id_departamento', $id)
                ->exists();

            if ($emUsoVeiculo || $emUsoUsuarios || $emUsoSolicitacoes || $emUsoPessoal) {
                $mensagem = 'Este departamento não pode ser excluído pois está sendo utilizado';
                if ($emUsoVeiculo) $mensagem .= ' em veículos';
                if ($emUsoUsuarios) $mensagem .= ($emUsoVeiculo ? ', ' : ' ') . 'por usuários';
                if ($emUsoSolicitacoes) $mensagem .= (($emUsoVeiculo || $emUsoUsuarios) ? ', ' : ' ') . 'em solicitações de compra';
                if ($emUsoPessoal) $mensagem .= (($emUsoVeiculo || $emUsoUsuarios || $emUsoSolicitacoes) ? ', ' : ' ') . 'por pessoal';
                $mensagem .= '.';

                return response()->json([
                    'notification' => [
                        'title'   => 'Não é possível excluir',
                        'type'    => 'error',
                        'message' => $mensagem
                    ]
                ], 422);
            }

            $departamento = Departamento::findOrFail($id);

            // Excluir o departamento
            $departamento->delete();

            // Limpar cache relacionado a este departamento
            $this->limparCacheDepartamento($id);

            return response()->json([
                'notification' => [
                    'title'   => 'Sucesso',
                    'type'    => 'success',
                    'message' => 'Departamento excluído com sucesso!'
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

                $mensagem = 'Este departamento não pode ser excluído pois está sendo utilizado em outros registros';

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
            Log::error('Erro ao excluir departamento: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Erro de banco de dados ao excluir o departamento.'
                ]
            ], 500);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir departamento: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o departamento: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $term = strtolower($request->get('term'));

        // Cache para melhorar performance
        $departamentos = Cache::remember('departamentos_search_' . $term, now()->addMinutes(30), function () use ($term) {
            return Departamento::when(is_numeric($term), function ($q) use ($term) {
                $q->where('id_departamento', $term);
            })
                ->orWhere('descricao_departamento', 'ILIKE', "%{$term}%")
                ->orderBy('id_departamento')
                ->limit(30)
                ->get(['id_departamento as value', 'descricao_departamento as label']);
        });

        return response()->json($departamentos);
    }

    public function getById($id)
    {
        // Cache para melhorar performance
        $departamento = Cache::remember('departamento_' . $id, now()->addHours(24), function () use ($id) {
            return Departamento::findOrFail($id);
        });

        return response()->json([
            'value' => $departamento->id_departamento,
            'label' => $departamento->descricao_departamento
        ]);
    }

    /**
     * Limpa o cache relacionado a um departamento
     */
    private function limparCacheDepartamento($id)
    {
        Cache::forget('departamento_' . $id);

        // Limpar também caches de pesquisa que possam conter esse departamento
        $keys = Cache::get('departamentos_search_keys', []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}

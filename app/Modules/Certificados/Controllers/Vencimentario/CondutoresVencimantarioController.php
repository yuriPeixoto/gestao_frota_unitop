<?php

namespace App\Modules\Certificados\Controllers\Vencimentario;

use App\Http\Controllers\Controller;

use App\Models\SmartecCnh;
use App\Services\IntegradorSmartecService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CondutoresVencimantarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $query = SmartecCnh::query();

            if ($request->filled('cnh')) {
                $query->where('cnh', $request->cnh);
            }

            if ($request->filled('nome')) {
                $query->where('nome', $request->nome);
            }

            $smartecCnh = $query->latest('id_smartec_cnh')
                ->paginate(15)
                ->appends($request->query());

            $cnh = $this->getCnh();

            $nome = $this->getNome();

            return view('admin.condutores.index', compact('smartecCnh', 'cnh', 'nome'), [
                'smartecCnh' => $smartecCnh,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar condutores: ' . $e->getMessage());
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Não foi possível carregar a lista de condutores.'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.condutores.create', [
            'smartecCnh' => null, // ou new SmartecCnh() se quiser um objeto vazio
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, IntegradorSmartecService $smartecService)
    {

        // Validação mais robusta
        $validatedData = $request->validate([
            'nome'                 => 'required',
            'cpf'                  => 'required',
            'cnh'                  => 'required',
            'uf'                   => 'required',
            'renach'               => 'required',
            'vencimento'           => 'required',
            'data_nascimento'      => 'nullable',
            'cedula'               => 'nullable',
            'data1habilitacao'     => 'nullable',
            'rg'                   => 'nullable',
            'uf_nascimento'        => 'nullable',
            'municipio_nascimento' => 'nullable',
            'municipio'            => 'nullable',
            'cod_seguranca'        => 'nullable',
            'categoria_cnh'        => 'nullable',
            'grupo_condutor'       => 'nullable',
            'apelido'              => 'required',
        ]);


        try {
            DB::beginTransaction();

            SmartecCnh::create([
                'nome'                  => $validatedData['nome'],
                'cpf'                   => $validatedData['cpf'],
                'cnh'                   => $validatedData['cnh'],
                'uf'                    => $validatedData['uf'],
                'renach'                => $validatedData['renach'],
                'vencimento'            => $validatedData['vencimento'],
                'data_nascimento'       => $validatedData['data_nascimento'],
                'cedula'                => $validatedData['cedula'],
                'data1habilitacao'      => $validatedData['data1habilitacao'],
                'rg'                    => $validatedData['rg'],
                'uf_nascimento'         => $validatedData['uf_nascimento'],
                'municipio_nascimento'  => $validatedData['municipio_nascimento'],
                'municipio'             => $validatedData['municipio'],
                'cod_seguranca'         => $validatedData['cod_seguranca'],
                'categoria_cnh'         => $validatedData['categoria_cnh'],
                'grupo_condutor'        => $validatedData['grupo_condutor'],
                'apelido'               => $validatedData['apelido'],
                'data_inclusao'         => now(),
            ]);

            $dadosParaApi = [
                'Nome'                    => $validatedData['nome'],
                'Cpf'                     => $validatedData['cpf'],
                'Cnh'               => $validatedData['cnh'],
                'Uf'                      => $validatedData['uf'],
                'RenaCh'            => $validatedData['renach'],
                'Validade'            => $validatedData['vencimento'],
                'DataNascimento'          => $validatedData['data_nascimento'],
                'Cedula'                  => $validatedData['cedula'],
                'Data1Habilitacao' => $validatedData['data1habilitacao'],
                'Rg'                      => $validatedData['rg'],
                'UfNascimento'            => $validatedData['uf_nascimento'],
                'MunicipioNascimento'     => $validatedData['municipio_nascimento'],
                'Municipio'               => $validatedData['municipio'],
                'CodigoSeguranca'         => $validatedData['cod_seguranca'],
                'Categoria'            => $validatedData['categoria_cnh'],
                'Grupo'           => $validatedData['grupo_condutor'],
                'Apelido'           => $validatedData['apelido'],
            ];


            // Chama o service para cadastrar via API
            $retornoApi = $smartecService->cadastrarCnh($dadosParaApi, 'CADASTRAR');


            DB::commit();

            Log::info('Resposta da API ao cadastrar CNH:', (array) $retornoApi);


            return redirect()
                ->route('admin.condutores.index')
                ->with('success', 'Condutor cadastrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar Cadastro condutor: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Não foi possível cadastrar o condutor. ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $smartecCnh = SmartecCnh::find($id);

        return view(
            'admin.condutores.edit',
            compact('smartecCnh')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        // Validação mais robusta
        $validatedData = $request->validate([
            'nome'                 => 'required',
            'cpf'                  => 'required',
            'cnh'                  => 'required',
            'uf'                   => 'nullable',
            'renach'               => 'nullable',
            'vencimento'           => 'required',
            'data_nascimento'      => 'nullable',
            'cedula'               => 'nullable',
            'data1habilitacao'     => 'nullable',
            'rg'                   => 'required',
            'uf_nascimento'        => 'nullable',
            'municipio_nascimento' => 'nullable',
            'municipio'            => 'nullable',
            'cod_seguranca'        => 'nullable',
            'categoria_cnh'        => 'nullable',
            'grupo_condutor'       => 'nullable',
            'apelido'              => 'nullable',
        ]);

        try {
            DB::beginTransaction();

            $smartecCnh = SmartecCnh::findOrFail($id);
            $smartecCnh->update([
                'nome'                  => $validatedData['nome'],
                'cpf'                   => $validatedData['cpf'],
                'cnh'                   => $validatedData['cnh'],
                'uf'                    => $validatedData['uf'],
                'renach'                => $validatedData['renach'],
                'vencimento'            => $validatedData['vencimento'],
                'data_nascimento'       => $validatedData['data_nascimento'],
                'cedula'                => $validatedData['cedula'],
                'data1habilitacao'      => $validatedData['data1habilitacao'],
                'rg'                    => $validatedData['rg'],
                'uf_nascimento'         => $validatedData['uf_nascimento'],
                'municipio_nascimento'  => $validatedData['municipio_nascimento'],
                'municipio'             => $validatedData['municipio'],
                'cod_seguranca'         => $validatedData['cod_seguranca'],
                'categoria_cnh'         => $validatedData['categoria_cnh'],
                'grupo_condutor'        => $validatedData['grupo_condutor'],
                'apelido'               => $validatedData['apelido'],
                'data_alteracao'        => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.condutores.index')
                ->with('success', 'Cadastro condutor cadastrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar Cadastro condutor: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Não foi possível cadastrar o Cadastro condutor. ' . $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, IntegradorSmartecService $smartecService)
    {
        try {
            DB::beginTransaction();

            $smartecCnh = SmartecCnh::findOrFail($id);
            $cpf = $smartecCnh->cpf;


            // Chamada para excluir no sistema externo
            $smartecService->excluirCnh($cpf);
            $retornoCnh = $smartecService->consultarCnh($cpf);

            if (is_array($retornoCnh)) {
                $retornoCnh = $retornoCnh[0] ?? null;
            }

            if ($retornoCnh && isset($retornoCnh->IdErro) && $retornoCnh->IdErro == 2000) {
                $smartecCnh->delete();
                DB::commit();
                return response()->json([
                    'notification' => [
                        'title' => 'Sucesso',
                        'type' => 'success',
                        'message' => 'Condutor removido com sucesso!'
                    ]
                ]);
            } else {
                DB::rollback();

                return response()->json([
                    'notification' => [
                        'title' => 'Erro',
                        'type' => 'error',
                        'message' => 'A exclusão não foi confirmada pela API.'
                    ]
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao excluri CNH: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => $e->getMessage()
                ]
            ]);
        }
    }


    public function getCnh()
    {
        return SmartecCnh::orderBy('cnh', 'asc')
            ->limit(30)
            ->get(['cnh as label', 'cnh as value']);
    }

    public function getNome()
    {
        return SmartecCnh::orderBy('nome', 'asc')
            ->limit(30)
            ->get(['nome as label', 'nome as value']);
    }
}

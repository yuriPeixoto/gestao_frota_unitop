<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Telefone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TelefoneController extends Controller
{
    /**
     * Obter todos os telefones de um fornecedor
     */
    public function index(Request $request)
    {
        $idFornecedor = $request->id_fornecedor;

        if (!$idFornecedor) {
            return response()->json([
                'success' => false,
                'message' => 'ID do fornecedor não informado'
            ], 400);
        }

        $telefones = Telefone::where('id_fornecedor', $idFornecedor)
            ->orderBy('id_telefone', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'telefones' => $telefones
        ]);
    }

    /**
     * Obter um telefone específico
     */
    public function show($id)
    {
        $telefone = Telefone::findOrFail($id);

        return response()->json($telefone);
    }

    /**
     * Salvar um novo telefone
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_fornecedor' => 'required|exists:fornecedor,id_fornecedor',
                'telefone_fixo' => 'nullable|string|max:20',
                'telefone_celular' => 'nullable|string|max:20',
                'telefone_contato' => 'nullable|string',
                'contato_comercial' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar se ao menos um telefone foi informado
            if (empty($request->telefone_fixo) && empty($request->telefone_celular) && empty($request->telefone_contato)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Informe pelo menos um telefone'
                ], 422);
            }

            DB::beginTransaction();

            // Criar um novo telefone
            $telefone = new Telefone();
            $telefone->id_fornecedor = $request->id_fornecedor;
            $telefone->telefone_fixo = $request->telefone_fixo;
            $telefone->telefone_celular = $request->telefone_celular;
            $telefone->telefone_contato = $request->telefone_contato;
            $telefone->contato_comercial = $request->contato_comercial;
            $telefone->data_inclusao = now();
            $telefone->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Telefone cadastrado com sucesso',
                'telefone' => $telefone
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar telefone: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar telefone: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar um telefone existente
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'telefone_fixo' => 'nullable|string|max:20',
                'telefone_celular' => 'nullable|string|max:20',
                'telefone_contato' => 'nullable|string',
                'contato_comercial' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar se ao menos um telefone foi informado
            if (empty($request->telefone_fixo) && empty($request->telefone_celular) && empty($request->telefone_contato)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Informe pelo menos um telefone'
                ], 422);
            }

            DB::beginTransaction();

            // Buscar e atualizar o telefone
            $telefone = Telefone::findOrFail($id);
            $telefone->telefone_fixo = $request->telefone_fixo;
            $telefone->telefone_celular = $request->telefone_celular;
            $telefone->telefone_contato = $request->telefone_contato;
            $telefone->contato_comercial = $request->contato_comercial;
            $telefone->data_alteracao = now();
            $telefone->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Telefone atualizado com sucesso',
                'telefone' => $telefone
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar telefone: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar telefone: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Excluir um telefone
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $telefone = Telefone::findOrFail($id);
            $telefone->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Telefone excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir telefone: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir telefone: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter telefones temporários da sessão.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getTempTelefones(Request $request)
    {
        // Obter telefones da sessão
        $telefones = $request->session()->get('temp_telefones', []);

        return response()->json([
            'telefones' => $telefones
        ]);
    }

    /**
     * Armazenar telefones temporários na sessão.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeTempTelefones(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telefones' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        // Armazenar telefones na sessão
        $request->session()->put('temp_telefones', $request->telefones);

        return response()->json([
            'success' => true,
            'message' => 'Telefones temporários armazenados com sucesso'
        ]);
    }
}

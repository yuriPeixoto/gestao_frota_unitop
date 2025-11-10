<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoCertificado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TipoCertificadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TipoCertificado::query();

        // Aplicar filtros de busca
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($query) use ($searchTerm) {
                $query->where('descricao_certificado', 'like', "%{$searchTerm}%")
                    ->orWhere('orgao_certificado', 'like', "%{$searchTerm}%")
                    ->orWhere('id_tipo_certificado', 'like', "%{$searchTerm}%");
            });
        }

        // Ordenação
        $query->orderBy('id_tipo_certificado', 'desc');

        // Paginação
        $tipocertificados = $query->paginate(10)->withQueryString();

        if ($request->header('HX-Request')) {
            return view('admin.tipocertificados._table', compact('tipocertificados'));
        }

        return view('admin.tipocertificados.index', compact('tipocertificados'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipocertificados.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validação dos dados
            $validated = $request->validate([
                'descricao_certificado' => 'required|max:255',
                'orgao_certificado' => 'required|max:255',
            ]);

            DB::beginTransaction();

            // Criar novo tipo de certificado
            $tipoCertificado = new TipoCertificado();
            $tipoCertificado->descricao_certificado = $validated['descricao_certificado'];
            $tipoCertificado->orgao_certificado = $validated['orgao_certificado'];
            $tipoCertificado->data_inclusao = now();
            $tipoCertificado->save();

            DB::commit();

            return redirect()
                ->route('admin.tipocertificados.index')
                ->with('success', 'Tipo de certificado cadastrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cadastrar tipo de certificado: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar tipo de certificado: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $tipoCertificado = TipoCertificado::findOrFail($id);
        return view('admin.tipocertificados.show', compact('tipoCertificado'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tipoCertificado = TipoCertificado::findOrFail($id);
        return view('admin.tipocertificados.edit', compact('tipoCertificado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validação dos dados
            $validated = $request->validate([
                'descricao_certificado' => 'required|max:255',
                'orgao_certificado' => 'required|max:255',
            ]);

            DB::beginTransaction();

            // Atualizar tipo de certificado
            $tipoCertificado = TipoCertificado::findOrFail($id);
            $tipoCertificado->descricao_certificado = $validated['descricao_certificado'];
            $tipoCertificado->orgao_certificado = $validated['orgao_certificado'];
            $tipoCertificado->data_alteracao = now();
            $tipoCertificado->save();

            DB::commit();

            return redirect()
                ->route('admin.tipocertificados.index')
                ->with('success', 'Tipo de certificado atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar tipo de certificado: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar tipo de certificado: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Verificar se o tipo de certificado existe
            $tipoCertificado = TipoCertificado::findOrFail($id);

            // Verificar se há certificados associados
            if ($tipoCertificado->certificados()->count() > 0) {
                return response()->json([
                    'notification' => [
                        'title' => 'Erro',
                        'type' => 'error',
                        'message' => 'Não é possível excluir este tipo de certificado pois existem certificados associados.'
                    ]
                ], 422);
            }

            // Excluir o tipo de certificado
            $tipoCertificado->delete();

            DB::commit();

            return response()->json([
                'notification' => [
                    'title' => 'Tipo de certificado excluído',
                    'type' => 'success',
                    'message' => 'Tipo de certificado excluído com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao excluir tipo de certificado: ' . $e->getMessage());

            $message = 'Erro ao excluir tipo de certificado';

            // Verificar se é um erro de violação de chave estrangeira
            if ($e->getCode() == 23503) {
                $message = 'Não é possível excluir este tipo de certificado pois existem registros dependentes';
            }

            return response()->json([
                'notification' => [
                    'title' => 'Erro',
                    'type' => 'error',
                    'message' => $message
                ]
            ], 500);
        }
    }
}

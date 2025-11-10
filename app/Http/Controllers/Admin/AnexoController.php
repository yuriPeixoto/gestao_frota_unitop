<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Anexo;
use Exception;

class AnexoController extends Controller
{
    /**
     * Faz o upload de um arquivo e o associa a uma entidade.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        // Validação da requisição
        $request->validate([
            'arquivo' => 'required|file|max:10240', // Max 10MB
            'entidade_tipo' => 'required|string',
            'entidade_id' => 'nullable|integer',
        ]);

        try {
            // Obter o arquivo da requisição
            $file = $request->file('arquivo');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $size = $file->getSize();

            // Gerar nome único para o arquivo
            $uniqueName = Str::uuid() . '.' . $extension;

            // Determinar o caminho onde o arquivo será armazenado
            $entidadeTipo = $request->input('entidade_tipo');
            $path = "anexos/{$entidadeTipo}";

            // Salvar o arquivo no disco
            $filePath = $file->storeAs($path, $uniqueName, 'public');

            if (!$filePath) {
                throw new Exception('Falha ao salvar o arquivo.');
            }

            // Criar o registro de anexo no banco de dados
            $anexo = new Anexo();
            $anexo->entidade_tipo = $entidadeTipo;
            $anexo->entidade_id = $request->input('entidade_id');
            $anexo->arquivo_nome = $originalName;
            $anexo->arquivo_path = $filePath;
            $anexo->arquivo_tipo = $extension;
            $anexo->tamanho = $size;
            $anexo->usuario_id = Auth::id();
            $anexo->save();

            // Adicionar informações de URL para acesso ao arquivo
            $anexo->url = Storage::url($filePath);
            $anexo->url_download = route('admin.anexos.download', $anexo->id);

            return response()->json([
                'success' => true,
                'message' => 'Arquivo enviado com sucesso!',
                'anexo' => $anexo
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao fazer upload de anexo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar arquivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibe um anexo específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $anexo = Anexo::findOrFail($id);

            // Verificar se o usuário tem permissão para visualizar este anexo
            // Implementar lógica de permissão aqui, se necessário

            // Verificar se o arquivo existe
            if (!Storage::disk('public')->exists($anexo->arquivo_path)) {
                throw new Exception('Arquivo não encontrado.');
            }

            // Retornar o arquivo para exibição
            return Storage::disk('public')->response($anexo->arquivo_path, $anexo->arquivo_nome);
        } catch (Exception $e) {
            Log::error('Erro ao exibir anexo: ' . $e->getMessage());
            abort(404, 'Anexo não encontrado.');
        }
    }

    /**
     * Faz o download de um anexo específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function download($id)
    {
        try {
            $anexo = Anexo::findOrFail($id);

            // Verificar se o usuário tem permissão para baixar este anexo
            // Implementar lógica de permissão aqui, se necessário

            // Verificar se o arquivo existe
            if (!Storage::disk('public')->exists($anexo->arquivo_path)) {
                throw new Exception('Arquivo não encontrado.');
            }

            // Retornar o arquivo para download
            return Storage::disk('public')->download($anexo->arquivo_path, $anexo->arquivo_nome);
        } catch (Exception $e) {
            Log::error('Erro ao fazer download de anexo: ' . $e->getMessage());
            abort(404, 'Anexo não encontrado.');
        }
    }

    /**
     * Remove um anexo específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $anexo = Anexo::findOrFail($id);

            // Verificar se o usuário tem permissão para excluir este anexo
            // Implementar lógica de permissão aqui, se necessário

            // Excluir o arquivo do disco
            if (Storage::disk('public')->exists($anexo->arquivo_path)) {
                Storage::disk('public')->delete($anexo->arquivo_path);
            }

            // Excluir o registro do banco de dados
            $anexo->delete();

            // Verificar se é uma requisição AJAX
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Anexo excluído com sucesso!'
                ]);
            }

            return redirect()->back()->with('success', 'Anexo excluído com sucesso!');
        } catch (Exception $e) {
            Log::error('Erro ao excluir anexo: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao excluir anexo: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Erro ao excluir anexo: ' . $e->getMessage());
        }
    }

    /**
     * Lista os anexos de uma entidade específica.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function listByEntity(Request $request)
    {
        $request->validate([
            'entidade_tipo' => 'required|string',
            'entidade_id' => 'required|integer',
        ]);

        try {
            $anexos = Anexo::where('entidade_tipo', $request->input('entidade_tipo'))
                ->where('entidade_id', $request->input('entidade_id'))
                ->get();

            // Adicionar URLs para cada anexo
            foreach ($anexos as $anexo) {
                $anexo->url = Storage::url($anexo->arquivo_path);
                $anexo->url_download = route('admin.anexos.download', $anexo->id);
            }

            return response()->json([
                'success' => true,
                'anexos' => $anexos
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao listar anexos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar anexos: ' . $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SinistroDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * @var SinistroDocumentService
     */
    protected $documentService;

    /**
     * Construtor
     *
     * @param SinistroDocumentService $documentService
     */
    public function __construct(SinistroDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Upload de arquivo temporário
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeTemp(Request $request)
    {
        try {
            // Validar o pedido
            if (!$request->hasFile('documento')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Nenhum arquivo enviado.'
                ]);
            }

            $file = $request->file('documento');

            // Se um ID de sinistro for fornecido, salvar diretamente na pasta do sinistro
            if ($request->filled('sinistro_id')) {
                $result = $this->documentService->uploadToSinistro($file, $request->sinistro_id);
            } else {
                // Caso contrário, salvar na pasta temporária
                $result = $this->documentService->uploadTemp($file);
            }

            // Retornar o resultado do upload
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Erro ao processar upload', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao processar o upload: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mover arquivo temporário para a pasta de sinistro
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function moveToSinistro(Request $request)
    {
        try {
            $validated = $request->validate([
                'file_name' => 'required|string',
                'sinistro_id' => 'required|integer'
            ]);

            $result = $this->documentService->moveFromTemp(
                $validated['file_name'],
                $validated['sinistro_id']
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Erro ao mover arquivo para sinistro', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao mover arquivo: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Excluir um arquivo
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteFile(Request $request)
    {
        try {
            $path = $request->input('path');

            if (empty($path)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Caminho do arquivo não especificado.'
                ]);
            }

            $deleted = $this->documentService->deleteFile($path);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Arquivo excluído com sucesso.'
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Não foi possível excluir o arquivo.'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir arquivo', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao excluir arquivo: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Recuperar um arquivo
     *
     * @param string $path
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function getFile($path)
    {
        try {
            $decodedPath = base64_decode($path);

            if ($this->documentService->fileExists($decodedPath)) {
                return Storage::disk('sinistros')->response($decodedPath);
            }

            return response()->json([
                'success' => false,
                'error' => 'Arquivo não encontrado.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erro ao acessar arquivo', [
                'error' => $e->getMessage(),
                'path' => $path
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao acessar arquivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpar arquivos temporários antigos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cleanupTempFiles()
    {
        try {
            $count = $this->documentService->cleanupTempFiles(24);

            return response()->json([
                'success' => true,
                'message' => "Limpeza concluída: {$count} arquivo(s) removido(s)."
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao limpar arquivos temporários', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao limpar arquivos temporários: ' . $e->getMessage()
            ], 500);
        }
    }
}

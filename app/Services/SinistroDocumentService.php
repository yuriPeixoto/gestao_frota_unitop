<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SinistroDocumentService
{
    /**
     * Disco usado para armazenar documentos de sinistros
     *
     * @var string
     */
    protected $disk = 'sinistros';

    /**
     * Upload de arquivo temporário (antes de associar a um sinistro)
     *
     * @param UploadedFile $file
     * @return array
     */
    public function uploadTemp(UploadedFile $file)
    {
        // Verificar se o diretório temporário existe, senão criar
        if (!Storage::disk($this->disk)->exists('temp')) {
            Storage::disk($this->disk)->makeDirectory('temp');
        }

        try {
            // Verificar se o arquivo é válido
            if (!$file->isValid()) {
                return [
                    'success' => false,
                    'error' => 'Arquivo inválido'
                ];
            }

            // Gerar nome seguro para o arquivo
            $originalName = $file->getClientOriginalName();
            $safeFileName = $this->generateSafeFileName($file);
            $path = 'temp/' . $safeFileName;

            // Fazer o upload
            $stream = fopen($file->getRealPath(), 'r');
            $result = Storage::disk($this->disk)->put($path, $stream);
            fclose($stream);

            if (!$result) {
                return [
                    'success' => false,
                    'error' => 'Falha ao salvar o arquivo'
                ];
            }

            // Verificar se o arquivo existe após upload
            $exists = Storage::disk($this->disk)->exists($path);

            return [
                'success' => true,
                'file_name' => $safeFileName,
                'original_name' => $originalName,
                'path' => $path,
                'full_url' => $this->getFileUrl($path)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erro ao processar o arquivo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mover um arquivo da pasta temporária para a pasta do sinistro
     *
     * @param string $tempFileName
     * @param int $sinistroId
     * @return array
     */
    public function moveFromTemp($tempFileName, $sinistroId)
    {
        try {
            $tempPath = 'temp/' . $tempFileName;

            // Verificar se o arquivo existe na pasta temporária
            if (!Storage::disk($this->disk)->exists($tempPath)) {
                return [
                    'success' => false,
                    'error' => 'Arquivo temporário não encontrado'
                ];
            }

            // Criar pasta do sinistro se não existir
            $sinistroPath = $sinistroId;
            if (!Storage::disk($this->disk)->exists($sinistroPath)) {
                Storage::disk($this->disk)->makeDirectory($sinistroPath);
            }

            // Definir caminho de destino
            $targetPath = $sinistroPath . '/' . $tempFileName;

            // Mover o arquivo
            if (Storage::disk($this->disk)->copy($tempPath, $targetPath)) {
                // Opcionalmente, excluir o arquivo temporário
                Storage::disk($this->disk)->delete($tempPath);

                return [
                    'success' => true,
                    'path' => $targetPath,
                    'full_url' => $this->getFileUrl($targetPath)
                ];
            }

            return [
                'success' => false,
                'error' => 'Falha ao mover o arquivo'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erro: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload direto para a pasta do sinistro
     *
     * @param UploadedFile $file
     * @param int $sinistroId
     * @return array
     */
    public function uploadToSinistro(UploadedFile $file, $sinistroId)
    {
        // Verificar se a pasta do sinistro existe, senão criar
        if (!Storage::disk($this->disk)->exists($sinistroId)) {
            Storage::disk($this->disk)->makeDirectory($sinistroId);
        }

        try {
            // Verificar se o arquivo é válido
            if (!$file->isValid()) {
                return [
                    'success' => false,
                    'error' => 'Arquivo inválido'
                ];
            }

            // Gerar nome seguro para o arquivo
            $originalName = $file->getClientOriginalName();
            $safeFileName = $this->generateSafeFileName($file);
            $path = $sinistroId . '/' . $safeFileName;

            // Fazer o upload
            $stream = fopen($file->getRealPath(), 'r');
            $result = Storage::disk($this->disk)->put($path, $stream);
            fclose($stream);

            if (!$result) {
                return [
                    'success' => false,
                    'error' => 'Falha ao salvar o arquivo'
                ];
            }

            return [
                'success' => true,
                'file_name' => $safeFileName,
                'original_name' => $originalName,
                'path' => $path,
                'full_url' => $this->getFileUrl($path)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erro ao processar o arquivo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Apagar um arquivo
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile($path)
    {
        try {
            if (Storage::disk($this->disk)->exists($path)) {
                return Storage::disk($this->disk)->delete($path);
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verificar se um arquivo existe
     *
     * @param string $path
     * @return bool
     */
    public function fileExists($path)
    {
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Limpar arquivos temporários
     *
     * @param int $hoursOld Horas para considerar arquivo como antigo
     * @return int Número de arquivos excluídos
     */
    public function cleanupTempFiles($hoursOld = 24)
    {
        $count = 0;
        $timeLimit = now()->subHours($hoursOld)->timestamp;

        try {
            $files = Storage::disk($this->disk)->files('temp');

            foreach ($files as $file) {
                $lastModified = Storage::disk($this->disk)->lastModified($file);

                if ($lastModified < $timeLimit) {
                    if (Storage::disk($this->disk)->delete($file)) {
                        $count++;
                    }
                }
            }

            return $count;
        } catch (\Exception $e) {
            return $count;
        }
    }

    /**
     * Gerar nome de arquivo seguro
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function generateSafeFileName(UploadedFile $file)
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);

        // Remover caracteres especiais e slug
        $cleanName = Str::slug($baseName);

        // Adicionar timestamp para garantir unicidade
        return $cleanName . '_' . time() . '.' . $extension;
    }

    /**
     * Obter URL pública para um arquivo
     *
     * @param string $path
     * @return string
     */
    public function getFileUrl($path)
    {
        return Storage::disk($this->disk)->url($path);
    }
}

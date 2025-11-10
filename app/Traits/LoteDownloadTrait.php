<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use ZipArchive;
use Exception;

trait LoteDownloadTrait
{
    /**
     * Gera e retorna diretamente um ZIP de documentos a partir do banco de dados.
     *
     * @param array $config Configuração do processo de download
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     * @throws Exception
     */
    public function gerarZipDeArquivos(array $config)
    {
        try {
            // === Configuração ===
            $tabela = $config['tabela'] ?? null;
            $colunaUrl = $config['coluna_url'] ?? 'url';
            $colunaNomeArquivo = $config['coluna_nome'] ?? 'nome';
            $filtros = $config['filtros'] ?? [];
            $prefixo = $config['prefixo'] ?? 'documento';
            $extensao = $config['extensao'] ?? 'pdf';

            if (!$tabela || !$colunaUrl || !$colunaNomeArquivo) {
                return back()->with([
                    'error' => 'Parâmetros obrigatórios ausentes: tabela, coluna_url, coluna_nome.',
                    'export_error' => true
                ]);
            }

            // === Query dinâmica ===
            $query = DB::table($tabela)
                ->select($colunaNomeArquivo, $colunaUrl)
                ->whereNotNull($colunaUrl);

            foreach ($filtros as $coluna => $valor) {
                if (is_int($coluna) && is_array($valor) && count($valor) === 3) {
                    [$campo, $operador, $val] = $valor;
                    $query->where($campo, $operador, $val);
                } elseif (is_array($valor)) {
                    $query->whereIn($coluna, $valor);
                } else {
                    $query->where($coluna, $valor);
                }
            }

            $registros = $query->get();

            if ($registros->isEmpty()) {
                return back()->with([
                    'error' => 'Nenhum arquivo encontrado com os filtros informados.',
                    'export_error' => true
                ]);
            }

            // === Cria diretório e arquivo ZIP temporário ===
            $tempDir = storage_path('app/temp_' . uniqid());
            mkdir($tempDir, 0755, true);
            $zipPath = $tempDir . '.zip';

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
                throw new Exception("Erro ao criar arquivo ZIP.");
            }

            // === Baixa e adiciona arquivos ao ZIP ===
            foreach ($registros as $item) {
                $url = $item->$colunaUrl;
                $nome = preg_replace('/[^A-Za-z0-9_\-]/', '_', $item->$colunaNomeArquivo);
                $arquivoTemp = $tempDir . '/' . $nome . '.' . $extensao;

                $conteudo = @file_get_contents($url);
                if ($conteudo !== false) {
                    file_put_contents($arquivoTemp, $conteudo);
                    $zip->addFile($arquivoTemp, basename($arquivoTemp));
                }
            }

            $zip->close();

            if (!file_exists($zipPath)) {
                throw new Exception("Falha ao gerar o arquivo ZIP.");
            }

            $nomeFinal = $prefixo . '_' . date('Ymd_His') . '.zip';

            // === Retorna como download direto (streamed response) ===
            return response()->download($zipPath, $nomeFinal)->deleteFileAfterSend(true);
        } catch (Exception $e) {
            Log::error('Erro ao gerar ZIP: ' . $e->getMessage());
            return back()->with([
                'error' => 'Erro ao gerar ZIP: ' . $e->getMessage(),
                'export_error' => true
            ]);
        }
    }
}

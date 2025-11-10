<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioQuantidadePneusPorFilial extends Controller
{
    public function index(Request $request)
    {

        $filiais = \App\Models\VFilial::select('id as value', 'name as label')->orderBy('name')->limit(30)->get();


        return view('admin.relatorioquantidadepneusporfilial.index', [
            'filiais' => $filiais,
        ]);
    }

    public function gerarPdf(Request $request)
    {
        $filial = empty($request['id']) ? "0" : $request['id'];

        if (empty($request['id'])) {
            $in_filial  = '!=';
            $id_filial  = '0';
        } else {
            $in_filial  = 'IN';
            $id_filial  = $request['id'];
        }



        $parametros = array(
            'P_in_filial' => $in_filial,
            'P_id_filial' => $id_filial,

        );

        $name       = 'quantidade_pneu_filial';
        $agora = now()->format('d-m-Y_H-i');
        $relatorio = "{$name}_{$agora}.pdf";

        $host = $request->getHost();
        $pathrel = explode('.', $host);
        $dominio = $pathrel[0];

        Log::info('Configurações do servidor:', [
            'host' => $host,
            'dominio' => $dominio,
            'relatorio' => $relatorio
        ]);

        if ($dominio == '127' || $dominio == 'localhost' || strpos($host, '127.0.0.1') !== false) {
            $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
            $pastarelatorio = '/reports/carvalima/' . $name;

            Log::info('Usando servidor de homologação');
        } elseif ($dominio == 'lcarvalima') {
            $jasperserver = 'http://10.10.1.8:8080/jasperserver';
            $input = '/reports/carvalima/' . $name;

            // Verificar se o diretório existe antes de tentar chmod
            if (is_dir($input)) {
                chmod($input, 0777);
                Log::info('Permissões do diretório alteradas: ' . $input);
            } else {
                Log::warning('Diretório não encontrado: ' . $input);
            }

            $pastarelatorio = $input;

            Log::info('Usando servidor de produção');
        } else {
            $jasperserver = 'http://10.10.1.8:8080/jasperserver';
            $input = '/reports/' . $dominio . '/' . $name;

            // Verificar se o diretório existe antes de tentar chmod
            if (is_dir($input)) {
                chmod($input, 0777);
                Log::info('Permissões do diretório alteradas: ' . $input);
            } else {
                Log::warning('Diretório não encontrado: ' . $input);
            }

            $pastarelatorio = $input;

            Log::info('Usando servidor de produção');
        }

        Log::info("Gerando PDF: {$relatorio}");
        Log::info("Servidor: {$jasperserver}, Caminho: {$pastarelatorio}");
        Log::info("Parâmetros:", $parametros);

        try {
            $jsi = new TraitsJasperServerIntegration(
                $jasperserver,
                $pastarelatorio,
                'pdf',
                'unitop',
                'unitop2022',
                $parametros
            );

            $data = $jsi->execute();

            // Verifica se retorno está vazio ou muito pequeno
            if (empty($data) || strlen($data) < 100) {
                Log::error("Relatório PDF gerado vazio ou muito pequeno: tamanho " . strlen($data));
                return response()->json(['message' => 'O relatório retornou vazio ou inválido.'], 500);
            }

            // Salva local para debug (opcional)
            file_put_contents(storage_path("app/public/{$relatorio}"), $data);

            return response($data, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$relatorio}\"",
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao gerar PDF: " . $e->getMessage());
            return response()->json(['message' => 'Erro ao gerar o relatório PDF.'], 500);
        }
    }

    public function gerarExcel(Request $request)
    {
        $filial = empty($request['id']) ? "0" : $request['id'];

        if (empty($request['id'])) {
            $in_filial  = '!=';
            $id_filial  = '0';
        } else {
            $in_filial  = 'IN';
            $id_filial  = $request['id'];
        }



        $parametros = array(
            'P_in_filial' => $in_filial,
            'P_id_filial' => $id_filial,

        );

        $name       = 'quantidade_pneu_filial_v2';

        $agora = now()->format('d-m-Y_H-i');
        $relatorio = "{$name}_{$agora}.xls";

        $host = parse_url(request()->url(), PHP_URL_HOST);
        $dominio = explode('.', $host)[0];


        if ($dominio == '127' || $dominio == 'localhost') {
            $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
            $pastarelatorio = "/reports/carvalima/{$name}";
        } else {
            $jasperserver = 'http://10.10.1.8:8080/jasperserver';
            $pastarelatorio = "/reports/{$dominio}/{$name}";
        }

        Log::info("Gerando PDF: {$relatorio}");
        Log::info("Servidor: {$jasperserver}, Caminho: {$pastarelatorio}");
        Log::info("Parâmetros:", $parametros);

        $jsi = new TraitsJasperServerIntegration(
            $jasperserver,
            $pastarelatorio,
            'xls',
            'jasperadmin',
            'unitop2022',
            $parametros
        );

        try {
            $data = $jsi->execute();

            return response($data)
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', "attachment; filename={$relatorio}");
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao gerar relatório: ' . $e->getMessage()], 500);
        }
    }
}

<?php

namespace App\Modules\Pneus\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioInventarioPneus extends Controller
{
    public function index()
    {
        return view('admin.relatorioinventariopneus.index');
    }

    public function gerarExcel(Request $request)
    {

        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300); // segurança extra

        $name = 'Relatorio_inventario_pneus_aplicados_v9';
        $agora = now()->format('d-m-Y_H-i');
        $relatorio = "{$name}_{$agora}.xls";

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

        $jsi = new TraitsJasperServerIntegration(
            $jasperserver,
            $pastarelatorio,
            'xls',
            'jasperadmin',
            'unitop2022',
            // $parametros
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

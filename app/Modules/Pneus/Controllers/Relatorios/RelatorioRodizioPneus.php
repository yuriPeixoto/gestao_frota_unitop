<?php

namespace App\Modules\Pneus\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\Pneu;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;
use Exception;

class RelatorioRodizioPneus extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = Pneu::query();

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->input('id_veiculo'));
        }

        $placa = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        return view('admin.relatoriorodiziopneus.index', compact('placa'));
    }

    public function gerarExcel(Request $request)
    {
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->array());


        $id_veiculo          = empty($request['id_veiculo']) ? "0" : $request['id_veiculo'];

        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

            if (empty($request['id_veiculo'])) {
                $in_placa  = '!=';
                $id_placa  = '0';
            } else {
                $in_placa  = 'IN';
                $id_placa  = $request['id_veiculo'];
            }

            if (!empty($request['data_inclusao'])) {
                $datainicial = $request['data_inclusao'];
            } else {
                $datainicial = "2020-01-01";
            }

            if (!empty($request['data_final'])) {
                $datafinal = $request['data_final'];
            } else {
                $datafinal = date('Y-m-d');
            }


            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal   = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

            $parametros = array(
                'P_data_inicio' => $datainicial,
                'P_date_fim' => $datafinal,
                'P_in_veiculo' => $in_placa,
                'P_id_veiculo' => $id_placa,

            );

            $name  = 'relatorio_rodizio_pneu_v2';
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

            Log::info("Gerando EXCEL: {$relatorio}");
            Log::info("Servidor: {$jasperserver}, Caminho: {$pastarelatorio}");
            Log::info("Parâmetros:", $parametros);

            try {
                $jsi = new TraitsJasperServerIntegration(
                    $jasperserver,
                    $pastarelatorio,
                    'xls',
                    'unitop',
                    'unitop2022',
                    $parametros
                );

                $data = $jsi->execute();

                // Verifica se retorno está vazio ou muito pequeno
                if (empty($data) || strlen($data) < 100) {
                    Log::error("Relatório EXCEL gerado vazio ou muito pequeno: tamanho " . strlen($data));
                    return response()->json(['message' => 'O relatório retornou vazio ou inválido.'], 500);
                }

                // Salva local para debug (opcional)
                file_put_contents(storage_path("app/public/{$relatorio}"), $data);

                return response($data, 200, [
                    'Content-Type' => 'application/xls',
                    'Content-Disposition' => "attachment; filename=\"{$relatorio}\"",
                ]);
            } catch (\Exception $e) {
                Log::error("Erro ao gerar EXCEL,: " . $e->getMessage());
                return response()->json(['message' => 'Erro ao gerar o relatório EXCEL.'], 500);
            }
        }

        return response()->json(['message' => 'Informe a data inicial e final para emissão do relatório.'], 400);
    }
}

<?php

namespace App\Modules\RelatoriosGerenciais\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Filial;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioCustosVariaveisPorDepartamento extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = Veiculo::query();

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }

        if ($request->filled('id_veiculo')) {
            $query->wher('id_veiculo', $request->input('id_veiculo'));
        }
        if ($request->filled('id_departamento')) {
            $query->wher('id_departamento', $request->input('id_departamento'));
        }
        if ($request->filled('id_filial')) {
            $query->wher('id_filial', $request->input('id_filial'));
        }

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->get();

        $placa = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        $departamento = Departamento::select('id_departamento as value', 'descricao_departamento as label')
            ->orderBy('descricao_departamento')
            ->get();

        return view('admin.relatoriocustospordepartamento.index', compact('filial', 'placa', 'departamento'));
    }

    public function gerarPdf(Request $request)
    {
        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300); // segurança extra

        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->post());

        // $filial       = empty($request['id_filial']) ? "0" : $request['id_filial'];
        // $tipo         = empty($request['id_tipo_equipamento']) ? "0" : $request['id_tipo_equipamento'];

        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

            if (empty($request['id_filial'])) {
                $id_filial_inicial = 0;
                $id_filial_final  = 999999;
            } else {
                $id_filial_inicial = $request['id_filial'];
                $id_filial_final  = $request['id_filial'];
            }

            if (empty($request['id_departamento'])) {
                $id_departamento_inicial = 0;
                $id_departamento_final  = 999999;
            } else {
                $id_departamento_inicial = $request['id_departamento'];
                $id_departamento_final  = $request['id_departamento'];
            }
            if (empty($request['id_veiculo'])) {
                $id_veiculo_inicial = 0;
                $id_veiculo_final  = 999999;
            } else {
                $id_veiculo_inicial = $request['id_veiculo'];
                $id_veiculo_final  = $request['id_veiculo'];
            }


            $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
            $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_id_filial_inicial' => $id_filial_inicial,
                'P_id_filial_final' => $id_filial_final,
                'P_id_departamento_inicial' => $id_departamento_inicial,
                'P_id_departamento_final' => $id_departamento_final,
                'P_id_veiculo_inicial' => $id_veiculo_inicial,
                'P_id_veiculo_final' => $id_veiculo_final
            );


            $name = 'custos_variaveis_placa_detalhado';
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

            $jsi = new TraitsJasperServerIntegration(
                $jasperserver,
                $pastarelatorio,
                'pdf',
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

    public function gerarExcel(Request $request)
    {
        ini_set('max_execution_time', 300); // 300 segundos = 5 minutos
        set_time_limit(300); // segurança extra

        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->post());

        $filial       = empty($request['id_filial']) ? "0" : $request['id_filial'];
        $placa         = empty($request['id_veiculo']) ? "0" : $request['id_veiculo'];
        $departamento       = empty($request['id_departamento']) ? "0" : $request['id_departamento'];

        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

            if (empty($request['id_filial'])) {
                $id_filial_inicial = 0;
                $id_filial_final  = 999999;
            } else {
                $id_filial_inicial = $request['id_filial'];
                $id_filial_final  = $request['id_filial'];
            }

            if (empty($request['id_departamento'])) {
                $id_departamento_inicial = 0;
                $id_departamento_final  = 999999;
            } else {
                $id_departamento_inicial = $request['id_departamento'];
                $id_departamento_final  = $request['id_departamento'];
            }
            if (empty($request['id_veiculo'])) {
                $id_veiculo_inicial = 0;
                $id_veiculo_final  = 999999;
            } else {
                $id_veiculo_inicial = $request['id_veiculo'];
                $id_veiculo_final  = $request['id_veiculo'];
            }


            $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
            $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_id_filial_inicial' => $id_filial_inicial,
                'P_id_filial_final' => $id_filial_final,
                'P_id_departamento_inicial' => $id_departamento_inicial,
                'P_id_departamento_final' => $id_departamento_final,
                'P_id_veiculo_inicial' => $id_veiculo_inicial,
                'P_id_veiculo_final' => $id_veiculo_final
            );

            $name = 'custos_variaveis_placa_v2';
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
}

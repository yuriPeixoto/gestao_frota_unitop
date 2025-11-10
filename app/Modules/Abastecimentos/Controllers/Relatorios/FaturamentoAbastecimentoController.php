<?php

namespace App\Modules\Abastecimentos\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Modules\Abastecimentos\Models\AbastecimentoManual;
use App\Models\Fornecedor;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use App\Traits\JasperServerIntegration;
use Illuminate\Support\Facades\Log;

class FaturamentoAbastecimentoController extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $abastecimentos = AbastecimentoManual::query();

        $veiculos = $this->getVeiculo();

        $fornecedores = $this->getVeiculo();

        return view('admin.faturamentoabastecimento.index', compact(
            'veiculos',
            'fornecedores',
        ));
    }


    public function onImprimir(Request $request)
    {
        Log::info('=== DEBUG COMPLETO ===');
        Log::info('Método HTTP: ' . $request->method());
        Log::info('Todos os inputs: ', $request->all());

        try {
            // Receber como arrays ou converter para arrays se necessário
            $veiculo = $request->input('id_veiculo', []);
            $posto = $request->input('id_fornecedor', []);

            // Converter strings em arrays se necessário
            if (empty($posto)) {
                $in_posto  = '!=';
                $id_posto  = "'0'";
            } else {
                $in_posto  = '=';
                $id_posto  = "'$posto'";
            }
            if (empty($param['veiculo']) || $veiculo == null) {
                $in_placa  = '!=';
                $id_placa  = '0';
            } else {
                $in_placa  = '=';
                $id_placa  = $veiculo;
            }

            // Verificar se as datas foram informadas
            if (!$request->input('data_inclusao') || !$request->input('data_final_abastecimento')) {
                return back()->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Atenção: Informe a data inicial e final para emissão do relatório.'
                ]);
            }

            // Processar datas
            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final_abastecimento'))->format('Y-m-d');

            $parametros = array(
                'P_data_inicial'    => $datainicial,
                'P_data_final'  => $datafinal,
                'P_in_posto'        => $in_posto,
                'P_id_posto'    => $id_posto,
                'P_in_placa'        => $in_placa,
                'P_id_placa'    => $id_placa
            );

            Log::info('Parâmetros processados: ', $parametros);

            // Resto da lógica do relatório...
            $name = 'relatorio_faturamento_abastecimento_pdf';
            $agora = date('d-m-YH:i');
            $tipo = '.pdf';
            $relatorio = $name . $agora . $tipo;

            $partes = parse_url('http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
            $host = $partes['host'] . PHP_EOL;
            $pathrel = (explode('.', $host));
            $dominio = $pathrel[0];

            if ($dominio == '127' || $dominio == 'localhost' || strpos($host, '127.0.0.1') !== false) {
                $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
                $pastarelatorio = '/reports/homologacao/' . $name;

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

            $jsi = new jasperserverintegration(
                $jasperserver,
                $pastarelatorio,
                'pdf',
                'unitop',
                'unitop2022',
                $parametros
            );

            try {
                $data = $jsi->execute();
                return response($data, 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="' . $relatorio . '"');
            } catch (\Exception $e) {
                Log::error('Erro ao gerar relatório: ' . $e->getMessage());
                return back()->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Não foi possível gerar o relatório. ' . $e->getMessage()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro geral: ' . $e->getMessage());
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro inesperado: ' . $e->getMessage()
            ]);
        }
    }

    public function onImprimirExcel(Request $request)
    {
        Log::info('=== DEBUG COMPLETO ===');
        Log::info('Método HTTP: ' . $request->method());
        Log::info('Todos os inputs: ', $request->all());

        try {
            // Receber como arrays ou converter para arrays se necessário
            $veiculo = $request->input('id_veiculo', []);
            $posto = $request->input('id_fornecedor', []);

            // Converter strings em arrays se necessário
            if (empty($posto)) {
                $in_posto  = '!=';
                $id_posto  = "'0'";
            } else {
                $in_posto  = '=';
                $id_posto  = "'$posto'";
            }
            if (empty($param['veiculo']) || $veiculo == null) {
                $in_placa  = '!=';
                $id_placa  = '0';
            } else {
                $in_placa  = '=';
                $id_placa  = $veiculo;
            }

            // Verificar se as datas foram informadas
            if (!$request->input('data_inclusao') || !$request->input('data_final_abastecimento')) {
                return back()->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Atenção: Informe a data inicial e final para emissão do relatório.'
                ]);
            }

            // Processar datas
            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final_abastecimento'))->format('Y-m-d');

            $parametros = array(
                'P_data_inicial'    => $datainicial,
                'P_data_final'  => $datafinal,
                'P_in_posto'        => $in_posto,
                'P_id_posto'    => $id_posto,
                'P_in_placa'        => $in_placa,
                'P_id_placa'    => $id_placa
            );

            Log::info('Parâmetros processados: ', $parametros);

            // Resto da lógica do relatório...
            $name = 'relatorio_faturamento_abastecimento_2';
            $agora = date('d-m-YH:i');
            $tipo = '.xls';
            $relatorio = $name . $agora . $tipo;

            $partes = parse_url('http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
            $host = $partes['host'] . PHP_EOL;
            $pathrel = (explode('.', $host));
            $dominio = $pathrel[0];

            if ($dominio == '127' || $dominio == 'localhost' || strpos($host, '127.0.0.1') !== false) {
                $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
                $pastarelatorio = '/reports/homologacao/' . $name;

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

            $jsi = new jasperserverintegration(
                $jasperserver,
                $pastarelatorio,
                'xls',
                'unitop',
                'unitop2022',
                $parametros
            );

            try {
                $data = $jsi->execute();
                Log::info('Parâmetros executados: ', $parametros);

                return response($data, 200, [
                    'Content-Type' => 'application/vnd.ms-excel',
                    'Content-Disposition' => 'attachment; filename="' . $relatorio . '"',
                    'Content-Length' => strlen($data),
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0'
                ]);
            } catch (\Exception $e) {
                Log::error('Erro ao gerar relatório: ' . $e->getMessage());
                return back()->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Não foi possível gerar o relatório. ' . $e->getMessage()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro geral: ' . $e->getMessage());
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro inesperado: ' . $e->getMessage()
            ]);
        }
    }

    public function getVeiculo()
    {
        return Veiculo::select('id_veiculo as value', 'placa as label')
            ->limit(30)
            ->orderBy('placa', 'asc')
            ->get()
            ->toArray();
    }

    public function getFornecedor()
    {
        return Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
            ->limit(30)
            ->orderBy('nome_fornecedor', 'asc')
            ->get()
            ->toArray();
    }
}

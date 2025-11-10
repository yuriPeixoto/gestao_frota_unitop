<?php

namespace App\Modules\Checklist\Controllers\Relatorios;

use Illuminate\Http\Request;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;
use Illuminate\Support\Facades\Log; // Se quiser logar exceções
use App\Helpers\TDate;
use App\Http\Controllers\Controller;
use App\Modules\Checklist\Models\CheckList;
use App\Models\Filial;
use App\Models\Veiculo;

class RelatorioChecklistController extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $request->input('id_veiculo');
        $request->input('id_filial');

        $veiculos = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();

        return view('admin.relatoriochecklist.index', [
            'veiculos' => $veiculos,
            'filial' => $filial,
        ]);
    }

    public function gerarPdf(Request $request)
    {
        // Permite até 5 minutos de execução para o Jasper gerar o PDF
        set_time_limit(300);
        Log::info('Dados brutos recebidos:', $request->all());
        if ($request->filled(['data_inclusao', 'data_final'])) {

            $param = [
                'data_inclusao' => $request->data_inclusao,
                'data_final' => $request->data_final,
                'id_veiculo' => $request->id_veiculo,
                'id_filial' => $request->id_filial
            ];

            if (empty($param['id_veiculo'])) {
                $in_placa = '!=';
                $id_placa   = '0';
            } else {
                $in_placa = 'IN';
                $id_placa = $param['id_veiculo'];
            }

            if (empty($param['id_filial'])) {
                $id_filial_inicial = '!=';
                $id_filial_final   = '0';
            } else {
                $id_filial_inicial = 'IN';
                $id_filial_final   = $param['id_filial'];
            }

            Log::info('Valores de filtragem:', [
                'id_filial_inicial' => $id_filial_inicial,
                'id_filial_final'   => $id_filial_final,
                'in_placa'          => $in_placa,
                'id_placa'          => $id_placa
            ]);



            // Formata datas corretamente — de dd/mm/yyyy para yyyy-mm-dd
            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');


            // Log para verificação
            Log::info("Datas convertidas: $datainicial até $datafinal");

            $parametros = [
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_veiculo' => $in_placa,
                'P_id_veiculo' => $id_placa,
                'P_in_filial' => $id_filial_inicial,
                'P_id_filial' => $id_filial_final,
            ];


            $name = 'tabela_checklist_v1';
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

        return response()->json(['message' => 'Informe a data inicial e final para emissão do relatório.'], 400);
    }





    public function gerarExcel(Request $request)
    {
        // Permite até 5 minutos de execução para o Jasper gerar o PDF
        set_time_limit(300);
        Log::info('Dados brutos recebidos:', $request->all());
        if ($request->filled(['data_inclusao', 'data_final'])) {

            $param = [
                'data_inclusao' => $request->data_inclusao,
                'data_final' => $request->data_final,
                'id_veiculo' => $request->id_veiculo,
                'id_filial' => $request->id_filial
            ];

            if (empty($param['id_veiculo'])) {
                $in_placa = '!=';
                $id_placa   = '0';
            } else {
                $in_placa = 'IN';
                $id_placa = $param['id_veiculo'];
            }

            if (empty($param['id_filial'])) {
                $id_filial_inicial = '!=';
                $id_filial_final   = '0';
            } else {
                $id_filial_inicial = 'IN';
                $id_filial_final   = $param['id_filial'];
            }

            Log::info('Valores de filtragem:', [
                'id_filial_inicial' => $id_filial_inicial,
                'id_filial_final'   => $id_filial_final,
                'in_placa'          => $in_placa,
                'id_placa'          => $id_placa
            ]);



            // Formata datas corretamente — de dd/mm/yyyy para yyyy-mm-dd
            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');


            // Log para verificação
            Log::info("Datas convertidas: $datainicial até $datafinal");

            $parametros = [
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_veiculo' => $in_placa,
                'P_id_veiculo' => $id_placa,
                'P_in_filial' => $id_filial_inicial,
                'P_id_filial' => $id_filial_final,
            ];


            $name = 'tabela_checklist_v1';
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
        } else {
            return response()->json(['message' => 'Informe a data inicial e final para emissão do relatório.'], 422);
        }
    }
}

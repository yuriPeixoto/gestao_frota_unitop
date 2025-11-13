<?php

namespace App\Modules\Premios\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\CalcularPremioMensaleRV;
use App\Models\Motorista;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioConferenciaPremioRvMensal extends Controller
{
    public function index(Request $request)
    {

        $query = CalcularPremioMensaleRV::query();

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }
        if ($request->filled('id_mot_unitop')) {
            $query->where('id_mot_unitop', $request->input('id_mot_unitop'));
        }
        if ($request->filled('id_veiculo_unitop')) {
            $query->where('id_veiculo_unitop', $request->input('id_veiculo_unitop'));
        }

        $motorista = Motorista::select('idobtermotorista as value', 'nome as label')
            ->orderBy('nome')
            ->limit(30)
            ->get();

        $placa = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();


        return view('admin.relatorioconferenciapremiorvmensal.index', compact('motorista', 'placa'));
    }

    public function gerarPdf(Request $request)
    {

        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

            if (empty($request['id_mot_unitop'])) {
                $in_equipamento  = '!=';
                $id_equipamento  = '0';
            } else {
                $in_equipamento  = '=';
                $id_equipamento  = $request['id_mot_unitop'];
            }

            if (empty($request['id_veiculo'])) {
                $in_veiculo     = '!=';
                $id_veiculo     = '0';
            } else {
                $in_veiculo     = '=';
                $id_veiculo     = $request['id_veiculo'];
            }

            if (empty($request['id_filial'])) {
                $in_filial      = '!=';
                $id_filial      = '0';
            } else {
                $in_filial      = '=';
                $id_filial      = $request['id_filial'];
            }

            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

            $parametros = array(
                'P_data_inicial'    => $datainicial,
                'P_data_final'      => $datafinal,
                'P_in_veiculo'      => $in_veiculo,
                'P_id_veiculo'      => $id_veiculo,
                'P_in_motorista'    => $in_equipamento,
                'P_id_motorista'    => $id_equipamento,
                'P_in_filial'       => $in_filial,
                'P_id_filial'       => $id_filial
            );

            $name       = 'premio_gerencia_motorista';
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
        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

            if (empty($request['id_mot_unitop'])) {
                $in_equipamento  = '!=';
                $id_equipamento  = '0';
            } else {
                $in_equipamento  = '=';
                $id_equipamento  = $request['id_mot_unitop'];
            }

            if (empty($request['id_veiculo'])) {
                $in_veiculo     = '!=';
                $id_veiculo     = '0';
            } else {
                $in_veiculo     = '=';
                $id_veiculo     = $request['id_veiculo'];
            }

            if (empty($request['id_filial'])) {
                $in_filial      = '!=';
                $id_filial      = '0';
            } else {
                $in_filial      = '=';
                $id_filial      = $request['id_filial'];
            }

            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

            $parametros = array(
                'P_data_inicial'    => $datainicial,
                'P_data_final'      => $datafinal,
                'P_in_veiculo'      => $in_veiculo,
                'P_id_veiculo'      => $id_veiculo,
                'P_in_motorista'    => $in_equipamento,
                'P_id_motorista'    => $id_equipamento,
                'P_in_filial'       => $in_filial,
                'P_id_filial'       => $id_filial
            );

            $name       = 'premio_gerencia_motorista';
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

            Log::info("Gerando PDF: {$relatorio}");
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
                    Log::error("Relatório XLS gerado vazio ou muito pequeno: tamanho " . strlen($data));
                    return response()->json(['message' => 'O relatório retornou vazio ou inválido.'], 500);
                }

                // Salva local para debug (opcional)
                file_put_contents(storage_path("app/public/{$relatorio}"), $data);

                return response($data, 200, [
                    'Content-Type' => 'application/xls',
                    'Content-Disposition' => "attachment; filename=\"{$relatorio}\"",
                ]);
            } catch (\Exception $e) {
                Log::error("Erro ao gerar XLS: " . $e->getMessage());
                return response()->json(['message' => 'Erro ao gerar o relatório XLS.'], 500);
            }
        }

        return response()->json(['message' => 'Informe a data inicial e final para emissão do relatório.'], 400);
    }

    public function gerarConferencialMensal(Request $request)
    {

        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

            if (empty($request['id_mot_unitop'])) {
                $in_equipamento  = '!=';
                $id_equipamento  = '0';
            } else {
                $in_equipamento  = '=';
                $id_equipamento  = $request['id_mot_unitop'];
            }

            if (empty($request['id_veiculo'])) {
                $in_veiculo     = '!=';
                $id_veiculo     = '0';
            } else {
                $in_veiculo     = '=';
                $id_veiculo     = $request['id_veiculo'];
            }

            if (empty($request['id_filial'])) {
                $in_filial      = '!=';
                $id_filial      = '0';
            } else {
                $in_filial      = '=';
                $id_filial      = $request['id_filial'];
            }

            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

            $parametros = array(
                'P_data_inicial'    => $datainicial,
                'P_data_final'      => $datafinal,
                'P_in_veiculo'      => $in_veiculo,
                'P_id_veiculo'      => $id_veiculo,
                'P_in_motorista'    => $in_equipamento,
                'P_id_motorista'    => $id_equipamento,
                'P_in_filial'       => $in_filial,
                'P_id_filial'       => $id_filial
            );

            $name       = 'new_conferencia_premio_mensal';
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

            Log::info("Gerando PDF: {$relatorio}");
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
                    Log::error("Relatório XLS gerado vazio ou muito pequeno: tamanho " . strlen($data));
                    return response()->json(['message' => 'O relatório retornou vazio ou inválido.'], 500);
                }

                // Salva local para debug (opcional)
                file_put_contents(storage_path("app/public/{$relatorio}"), $data);

                return response($data, 200, [
                    'Content-Type' => 'application/xls',
                    'Content-Disposition' => "attachment; filename=\"{$relatorio}\"",
                ]);
            } catch (\Exception $e) {
                Log::error("Erro ao gerar XLS: " . $e->getMessage());
                return response()->json(['message' => 'Erro ao gerar o relatório XLS.'], 500);
            }
        }

        return response()->json(['message' => 'Informe a data inicial e final para emissão do relatório.'], 400);
    }

    public function gerarConferenciaMensalERV(Request $request)
    {

        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

            if (empty($request['id_mot_unitop'])) {
                $in_equipamento  = '!=';
                $id_equipamento  = '0';
            } else {
                $in_equipamento  = '=';
                $id_equipamento  = $request['id_mot_unitop'];
            }

            if (empty($request['id_veiculo'])) {
                $in_veiculo     = '!=';
                $id_veiculo     = '0';
            } else {
                $in_veiculo     = '=';
                $id_veiculo     = $request['id_veiculo'];
            }

            if (empty($request['id_filial'])) {
                $in_filial      = '!=';
                $id_filial      = '0';
            } else {
                $in_filial      = '=';
                $id_filial      = $request['id_filial'];
            }

            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

            $parametros = array(
                'P_data_inicial'    => $datainicial,
                'P_data_final'      => $datafinal,
                'P_in_veiculo'      => $in_veiculo,
                'P_id_veiculo'      => $id_veiculo,
                'P_in_motorista'    => $in_equipamento,
                'P_id_motorista'    => $id_equipamento,
                'P_in_filial'       => $in_filial,
                'P_id_filial'       => $id_filial
            );

            $name       = 'conferenciaMensalERv_v2';
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

            Log::info("Gerando PDF: {$relatorio}");
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
                    Log::error("Relatório XLS gerado vazio ou muito pequeno: tamanho " . strlen($data));
                    return response()->json(['message' => 'O relatório retornou vazio ou inválido.'], 500);
                }

                // Salva local para debug (opcional)
                file_put_contents(storage_path("app/public/{$relatorio}"), $data);

                return response($data, 200, [
                    'Content-Type' => 'application/xls',
                    'Content-Disposition' => "attachment; filename=\"{$relatorio}\"",
                ]);
            } catch (\Exception $e) {
                Log::error("Erro ao gerar XLS: " . $e->getMessage());
                return response()->json(['message' => 'Erro ao gerar o relatório XLS.'], 500);
            }
        }

        return response()->json(['message' => 'Informe a data inicial e final para emissão do relatório.'], 400);
    }
}

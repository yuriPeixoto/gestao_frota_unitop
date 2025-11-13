<?php

namespace App\Modules\Multas\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Modules\Pessoal\Models\Pessoal;
use App\Models\Veiculo;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RelatorioMultas extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = Veiculo::query();

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final'),
            ]);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->input('id_veiculo'));
        }
        if ($request->filled('id_pessoal')) {
            $query->where('id_pessoal', $request->input('id_pessoal'));
        }

        $placa = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        $condutor = Pessoal::select('id_pessoal as value', 'nome as label')
            ->orderBy('nome')
            ->limit(30)
            ->get();

        return view('admin.relatoriomultas.index', compact('placa', 'condutor'));
    }

    public function gerarPdf(Request $request)
    {
        try {
            Log::info('Dados brutos recebidos:', $request->all());

            // Veículo
            if (empty($request['id_veiculo'])) {
                $id_veiculo_inicial = 0;
                $id_veiculo_final = 99999999;
            } else {
                $id_veiculo_inicial = $request['id_veiculo'];
                $id_veiculo_final = $request['id_veiculo'];
            }

            if (empty($request['id_pessoal'])) {
                $id_condutor_inicial = 0;
                $id_condutor_final = 99999999;
            } else {
                $id_condutor_inicial = $request['id_pessoal'];
                $id_condutor_final = $request['id_pessoal'];
            }

            $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

            $parametros = [
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_id_veiculo_inicial' => $id_veiculo_inicial,
                'P_id_veiculo_final' => $id_veiculo_final,
                'P_id_condutor_inicial' => $id_condutor_inicial,
                'P_id_condutor_final' => $id_condutor_final,
            ];

            Log::info('Parâmetros Jasper:', $parametros);

            $name = 'motivo_multa';
            $agora = now()->format('d-m-Y_H-i');
            $relatorio = "{$name}_{$agora}.pdf";

            $host = $request->getHost();
            $pathrel = explode('.', $host);
            $dominio = $pathrel[0];

            Log::info('Configurações do servidor:', [
                'host' => $host,
                'dominio' => $dominio,
                'relatorio' => $relatorio,
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

            $jsi = new TraitsJasperServerIntegration(
                $jasperserver,
                $pastarelatorio,
                'pdf',
                'unitop',
                'unitop2022',
                $parametros
            );

            $data = $jsi->execute();

            if (empty($data) || strlen($data) < 100) {
                Log::error('Relatório PDF gerado vazio ou muito pequeno: tamanho ' . strlen($data));

                return response()->json(['error' => true, 'message' => 'O relatório retornou vazio ou inválido.'], 500);
            }

            file_put_contents(storage_path("app/public/{$relatorio}"), $data);

            return response($data, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$relatorio}\"",
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());

            return response()->json(['error' => true, 'message' => 'Erro ao gerar o relatório PDF.'], 500);
        }
    }

    public function gerarExcel(Request $request)
    {
        try {
            Log::info('Dados brutos recebidos:', $request->all());

            // Veículo
            if (empty($request['id_veiculo'])) {
                $id_veiculo_inicial = 0;
                $id_veiculo_final = 99999999;
            } else {
                $id_veiculo_inicial = $request['id_veiculo'];
                $id_veiculo_final = $request['id_veiculo'];
            }

            if (empty($request['id_pessoal'])) {
                $id_condutor_inicial = 0;
                $id_condutor_final = 99999999;
            } else {
                $id_condutor_inicial = $request['id_pessoal'];
                $id_condutor_final = $request['id_pessoal'];
            }

            $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

            $parametros = [
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_id_veiculo_inicial' => $id_veiculo_inicial,
                'P_id_veiculo_final' => $id_veiculo_final,
                'P_id_condutor_inicial' => $id_condutor_inicial,
                'P_id_condutor_final' => $id_condutor_final,
            ];

            Log::info('Parâmetros Jasper:', $parametros);

            $name = 'motivo_multa';
            $agora = now()->format('d-m-Y_H-i');
            $relatorio = "{$name}_{$agora}.xls";

            $host = $request->getHost();
            $pathrel = explode('.', $host);
            $dominio = $pathrel[0];

            Log::info('Configurações do servidor:', [
                'host' => $host,
                'dominio' => $dominio,
                'relatorio' => $relatorio,
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

            Log::info("Gerando xls: {$relatorio}");
            Log::info("Servidor: {$jasperserver}, Caminho: {$pastarelatorio}");

            $jsi = new TraitsJasperServerIntegration(
                $jasperserver,
                $pastarelatorio,
                'xls',
                'unitop',
                'unitop2022',
                $parametros
            );

            $data = $jsi->execute();

            if (empty($data) || strlen($data) < 100) {
                Log::error('Relatório xls gerado vazio ou muito pequeno: tamanho ' . strlen($data));

                return response()->json(['error' => true, 'message' => 'O relatório retornou vazio ou inválido.'], 500);
            }

            file_put_contents(storage_path("app/public/{$relatorio}"), $data);

            return response($data, 200, [
                'Content-Type' => 'application/xls',
                'Content-Disposition' => "attachment; filename=\"{$relatorio}\"",
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar xls: ' . $e->getMessage());

            return response()->json(['error' => true, 'message' => 'Erro ao gerar o relatório xls.'], 500);
        }
    }
}

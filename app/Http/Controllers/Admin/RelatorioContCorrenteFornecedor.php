<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fornecedor;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioContCorrenteFornecedor extends Controller
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
            $query->where('id_veiculo', $request->input('id_veiculo'));
        }
        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->input('id_fornecedor'));
        }

        $fornecedor = Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
            ->orderBy('nome_fornecedor')
            ->limit(30)
            ->get();

        $placa = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        return view('admin.relatoriocontacorrentefornecedor.index', compact('fornecedor', 'placa'));
    }

    public function gerarPdf(Request $request)
    {
        try {
            Log::info('Dados brutos recebidos:', $request->all());



            if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {
                // Veículo
                if (empty($request['id_veiculo'])) {
                    $inVeiculo = 0;
                    $idVeiculo = 999999999;
                } else {
                    $inVeiculo = $request['id_veiculo'];
                    $idVeiculo = $request['id_veiculo'];
                }

                // Fornecedor
                if (empty($request['id_fornecedor'])) {
                    $inFornecedor = 0;
                    $idFornecedor = 999999999;
                } else {
                    $inFornecedor = $request['id_fornecedor'];
                    $idFornecedor = $request['id_fornecedor'];
                }

                $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
                $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

                // Parâmetros Jasper
                $parametros = [
                    'P_data_inicial'       => $datainicial,
                    'P_data_final'         => $datafinal,
                    'P_in_veiculo_inicial' => $inVeiculo,
                    'P_id_veiculo_final'   => $idVeiculo,
                    'P_in_fornecedor'      => $inFornecedor,
                    'P_id_fornecedor'      => $idFornecedor,
                ];

                Log::info("Parâmetros Jasper:", $parametros);

                $name = 'conta_corrente_fornecedor';
                $agora      = now()->format('d-m-Y_H-i');
                $relatorio  = "{$name}_{$agora}.pdf";

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
                    Log::error("Relatório PDF gerado vazio ou muito pequeno: tamanho " . strlen($data));
                    return response()->json(['error' => true, 'message' => 'O relatório retornou vazio ou inválido.'], 500);
                }

                file_put_contents(storage_path("app/public/{$relatorio}"), $data);

                return response($data, 200, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => "attachment; filename=\"{$relatorio}\"",
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Erro ao gerar PDF: " . $e->getMessage());
            return response()->json(['error' => true, 'message' => 'Erro ao gerar o relatório PDF.'], 500);
        }
    }


    public function gerarExcel(Request $request)
    {
        try {
            Log::info('Dados brutos recebidos:', $request->all());


            // Produtos
            if ($request->filled(['data_inclusao', 'data_final'])) {

                if (empty($request['id_veiculo'])) {
                    $inVeiculo = 0;
                    $idVeiculo   = 99999999;
                } else {
                    $inVeiculo = $request['id_veiculo'];
                    $idVeiculo   = $request['id_veiculo'];
                }

                if (empty($request['id_fornecedor'])) {
                    $inFornecedor = 0;
                    $idFornecedor   = 99999999;
                } else {
                    $inFornecedor = $request['id_fornecedor'];
                    $idFornecedor   = $request['id_fornecedor'];
                }

                $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
                $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

                // Parâmetros Jasper
                $parametros = [
                    'P_data_inicial'  => $datainicial,
                    'P_data_final'  => $datafinal,
                    'P_in_veiculo_inicial'    => $inVeiculo,
                    'P_id_veiculo_final'    => $idVeiculo,
                    'P_in_fornecedor'   => $inFornecedor,
                    'P_id_fornecedor'   => $idFornecedor,

                ];

                Log::info("Parâmetros Jasper:", $parametros);

                $name       = 'conta_corrente_fornecedor';
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
                Log::info("Gerando xls: {$relatorio}");
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
                        Log::error("Relatório xls gerado vazio ou muito pequeno: tamanho " . strlen($data));
                        return response()->json(['message' => 'O relatório retornou vazio ou inválido.'], 500);
                    }

                    // Salva local para debug (opcional)
                    file_put_contents(storage_path("app/public/{$relatorio}"), $data);

                    return response($data, 200, [
                        'Content-Type' => 'application/xls',
                        'Content-Disposition' => "attachment; filename=\"{$relatorio}\"",
                    ]);
                } catch (\Exception $e) {
                    Log::error("Erro ao gerar xls: " . $e->getMessage());
                    return response()->json(['message' => 'Erro ao gerar o relatório xls.'], 500);
                }
            }
        } catch (\Exception $e) {
            Log::error("Erro ao gerar xls: " . $e->getMessage());
            return response()->json(['message' => 'Erro ao gerar o relatório xls.'], 500);
        }
    }
}

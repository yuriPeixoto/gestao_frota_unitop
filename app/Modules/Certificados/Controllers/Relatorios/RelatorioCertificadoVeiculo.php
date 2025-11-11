<?php

namespace App\Modules\Certificados\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Modules\Certificados\Models\TipoCertificado;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioCertificadoVeiculo extends Controller
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

        // filtro por placa (id_veiculo)
        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->input('id_veiculo'));
        }

        // filtro por certificado (também é id_veiculo)
        if ($request->filled('id_tipo_certificado')) {
            $query->where('id_veiculo', $request->input('id_tipo_certificado'));
        }

        $placa = $query->select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        $certificado = TipoCertificado::select(
            'id_tipo_certificado as value',
            'descricao_certificado as label'
        )
            ->orderBy('descricao_certificado')
            ->limit(30)
            ->get();

        // envie também $certificado para a view se precisar exibir o select
        return view(
            'admin.relatoriocertificadoveiculo.index',
            compact('placa', 'certificado')
        );
    }

    public function gerarPdf(Request $request)
    {
        try {
            if ($request->filled('data_inclusao') && $request->filled('data_final')) {

                // Produtos
                if (empty($request['id_veiculo'])) {
                    $in_veiculo  = '!=';
                    $id_veiculo  = '0';
                } else {
                    $in_veiculo  = 'IN';
                    $id_veiculo  = $request['id_veiculo'];
                }

                if (empty($request['id_tipo_certificado'])) {
                    $in_tipo  = '!=';
                    $id_tipo  = '0';
                } else {
                    $in_tipo  = 'IN';
                    $id_tipo  = $request['id_tipo_certificado'];
                }

                $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
                $datafinal   = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

                $parametros = array(
                    'P_data_inicial' => $datainicial,
                    'P_data_final' => $datafinal,
                    'P_in_veiculo' => $in_veiculo,
                    'P_id_veiculo' => $id_veiculo,
                    'P_in_tipo' => $in_tipo,
                    'P_id_tipo' => $id_tipo
                );
                Log::info("Parâmetros Jasper:", $parametros);

                $name       = 'relatorio_certificados';
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
            } else {
                return response()->json(['message' => 'Informe a data inicial e final para emissão do relatório.'], 400);
            }
        } catch (\Exception $e) {
            Log::error("Erro ao gerar PDF: " . $e->getMessage());
            return response()->json(['message' => 'Erro ao gerar o relatório PDF.'], 500);
        }
    }

    public function gerarExcel(Request $request)
    {
        try {
            if ($request->filled('data_inclusao') && $request->filled('data_final')) {

                // Produtos
                if (empty($request['id_veiculo'])) {
                    $in_veiculo  = '!=';
                    $id_veiculo  = '0';
                } else {
                    $in_veiculo  = 'IN';
                    $id_veiculo  = $request['id_veiculo'];
                }

                if (empty($request['id_tipo_certificado'])) {
                    $in_tipo  = '!=';
                    $id_tipo  = '0';
                } else {
                    $in_tipo  = 'IN';
                    $id_tipo  = $request['id_tipo_certificado'];
                }

                $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
                $datafinal   = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

                $parametros = array(
                    'P_data_inicial' => $datainicial,
                    'P_data_final' => $datafinal,
                    'P_in_veiculo' => $in_veiculo,
                    'P_id_veiculo' => $id_veiculo,
                    'P_in_tipo' => $in_tipo,
                    'P_id_tipo' => $id_tipo
                );
                Log::info("Parâmetros Jasper:", $parametros);

                $name       = 'relatorio_certificados_v2';
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
            } else {
                return response()->json(['message' => 'Informe a data inicial e final para emissão do relatório.'], 400);
            }
        } catch (\Exception $e) {
            Log::error("Erro ao gerar xls: " . $e->getMessage());
            return response()->json(['message' => 'Erro ao gerar o relatório xls.'], 500);
        }
    }
}

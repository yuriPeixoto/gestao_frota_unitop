<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaVeiculo;
use App\Models\Filial;
use App\Models\IpvaVeiculo;
use App\Models\LicenciamentoVeiculo;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioIpvaLicenciamentoVeiculo extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = Veiculo::query();

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->input('id_veiculo'));
        }
        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }
        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->input('id_categoria'));
        }
        if ($request->filled('data_base_vencimento')) {
            $query->where('ano_licenciamento', $request->input('data_base_vencimento'));
        }


        $placa = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();
        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();
        $categoria = CategoriaVeiculo::select('id_categoria as value', 'descricao_categoria as label')
            ->orderBy('descricao_categoria')
            ->get();

        $anos = IpvaVeiculo::selectRaw('DISTINCT EXTRACT(YEAR FROM data_base_vencimento) as ano')
            ->orderBy('ano', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->ano,
                    'label' => $item->ano
                ];
            })
            ->toArray();


        return view('admin.relatorioipvalicenciamento.index', compact('placa', 'filial', 'anos', 'categoria'));
    }

    public function gerarPdf(Request $request)
    {
        try {
            Log::info('Dados brutos recebidos:', $request->all());



            // Veículo
            if (empty($request['id_veiculo'])) {
                $id_veiculo_inicial = 0;
                $id_veiculo_final   = 99999999;
            } else {
                $id_veiculo_inicial = $request['id_veiculo'];
                $id_veiculo_final   = $request['id_veiculo'];
            }

            if (empty($request['id_filial'])) {
                $id_filial_inicial = 0;
                $id_filial_final   = 99999999;
            } else {
                $id_filial_inicial = $request['id_filial'];
                $id_filial_final   = $request['id_filial'];
            }

            if (empty($request['id_categoria'])) {
                $id_categoria_inicial = 0;
                $id_categoria_final   = 99999999;
            } else {
                $id_categoria_inicial = $request['id_categoria'];
                $id_categoria_final   = $request['id_categoria'];
            }

            if (empty($request['data_base_vencimento'])) {
                $in_ano = 'IS';
                $id_ano = 'NOT NULL';
            } else {
                $in_ano = 'IN';
                $id_ano = '(' . $request['data_base_vencimento'] . ')'; // já é string
            }
            $parametros = array(
                'P_id_veiculo_inicial' => $id_veiculo_inicial,
                'P_id_veiculo_final' => $id_veiculo_final,
                'P_id_filial_inicial' => $id_filial_inicial,
                'P_id_filial_final' => $id_filial_final,
                'P_id_categoria_inicial' => $id_categoria_inicial,
                'P_id_categoria_final' => $id_categoria_final,
                'P_in_ano' => $in_ano,
                'P_id_ano' => $id_ano
            );

            Log::info("Parâmetros Jasper:", $parametros);

            $name       = 'venc_ipva_licen';
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
        } catch (\Exception $e) {
            Log::error("Erro ao gerar PDF: " . $e->getMessage());
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
                $id_veiculo_final   = 99999999;
            } else {
                $id_veiculo_inicial = $request['id_veiculo'];
                $id_veiculo_final   = $request['id_veiculo'];
            }

            if (empty($request['id_filial'])) {
                $id_filial_inicial = 0;
                $id_filial_final   = 99999999;
            } else {
                $id_filial_inicial = $request['id_filial'];
                $id_filial_final   = $request['id_filial'];
            }

            if (empty($request['id_categoria'])) {
                $id_categoria_inicial = 0;
                $id_categoria_final   = 99999999;
            } else {
                $id_categoria_inicial = $request['id_categoria'];
                $id_categoria_final   = $request['id_categoria'];
            }

            if (empty($request['data_base_vencimento'])) {
                $in_ano = 'IS';
                $id_ano = 'NOT NULL';
            } else {
                $in_ano = 'IN';
                $id_ano = '(' . implode(',', $request['data_base_vencimento']) . ')';
            }

            $parametros = array(
                'P_id_veiculo_inicial' => $id_veiculo_inicial,
                'P_id_veiculo_final' => $id_veiculo_final,
                'P_id_filial_final' => $id_filial_final,
                'P_id_filial_inicial' => $id_filial_inicial,
                'P_id_categoria_inicial' => $id_categoria_inicial,
                'P_id_categoria_final' => $id_categoria_final,
                'P_in_ano' => $in_ano,
                'P_id_ano' => $id_ano
            );

            Log::info("Parâmetros Jasper:", $parametros);

            $name       = 'venc_ipva_licen_v2';
            $agora      = now()->format('d-m-Y_H-i');
            $relatorio  = "{$name}_{$agora}.xls";

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
                Log::error("Relatório xls gerado vazio ou muito pequeno: tamanho " . strlen($data));
                return response()->json(['error' => true, 'message' => 'O relatório retornou vazio ou inválido.'], 500);
            }

            file_put_contents(storage_path("app/public/{$relatorio}"), $data);

            return response($data, 200, [
                'Content-Type'        => 'application/xls',
                'Content-Disposition' => "attachment; filename=\"{$relatorio}\"",
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao gerar xls: " . $e->getMessage());
            return response()->json(['error' => true, 'message' => 'Erro ao gerar o relatório xls.'], 500);
        }
    }
}

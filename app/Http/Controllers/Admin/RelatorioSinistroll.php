<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filial;
use App\Models\Motorista;
use App\Models\Sinistro;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Se quiser logar exceções
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioSinistroll extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = Sinistro::query();

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }
        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->input('id_veiculo'));
        }
        if ($request->filled('id_sinistro')) {
            $query->where('id_sinistro', $request->input('id_sinistro'));
        }
        if ($request->filled('id_motorista')) {
            $query->where('id_motorista', $request->input('id_motorista'));
        }
        if ($request->filled('situacao_sinistro_processo')) {
            $query->where('situacao_sinistro_processo', $request->input('situacao_sinistro_processo'));
        }

        $status = Sinistro::whereIn('situacao_sinistro_processo', ['FINALIZADO', 'EM ANDAMENTO'])
            ->distinct('situacao_sinistro_processo')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id_sinistro,
                    'label' => $item->situacao_sinistro_processo
                ];
            })
            ->toArray();

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();

        $placa = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        $motorista = Motorista::select('idobtermotorista as value', 'nome as label')
            ->orderBy('nome')
            ->limit(30)
            ->get();

        $sinistro = Sinistro::select('id_sinistro as value', 'id_sinistro as label')
            ->orderBy('id_sinistro')
            ->limit(30)
            ->get();

        return view('admin.relatoriosinistroll.index', compact('filial', 'placa', 'motorista', 'sinistro', 'status'));
    }

    public function gerarPdf(Request $request)
    {
        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

            if (empty($request['id_sinistro']) || $request['id_sinistro'] == null) {
                $id_sinistro_inicial    = 0;
                $id_sinistro_final      = 999999;
            } else {
                $id_sinistro_inicial = $request['id_sinistro'];
                $id_sinistro_final   = $request['id_sinistro'];
            }

            if (empty($request['situacao_sinistro_processo']) || $request['situacao_sinistro_processo'] == null) {
                $status_inicial    = 'IS';
                $status_final      = "NOT NULL";
            } else {
                $status_inicial = '=';
                $status_final   = "'" . $request['situacao_sinistro_processo'] . "'";
            }

            if (empty($request['id_motorista']) || $request['id_motorista'] == null) {
                $id_motorista_inicial   = 0;
                $id_motorista_final     = 999999;
            } else {
                $id_motorista_inicial = $request['id_motorista'];
                $id_motorista_final   = $request['id_motorista'];
            }

            if (empty($request['id_veiculo']) || $request['id_veiculo'] == null) {
                $placa_inicial   = 0;
                $placa_final     = 999999;
            } else {
                $placa_inicial   = $request['id_veiculo'];
                $placa_final     = $request['id_veiculo'];
            }

            if (empty($request['id_filial'])  || $request['id_filial'] == null) {
                $id_filial_inicial  = 0;
                $id_filial_final    = 999999;
            } else {
                $id_filial_inicial  = $request['id_filial'];
                $id_filial_final    = $request['id_filial'];
            }

            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

            $parametros = array(
                'P_data_inicial'            => $datainicial,
                'P_data_final'           => $datafinal,
                'P_id_sinistro_inicial'     => $id_sinistro_inicial,
                'P_id_sinistro_final'    => $id_sinistro_final,
                'P_id_motorista_inicial'    => $id_motorista_inicial,
                'P_id_motorista_final'   => $id_motorista_final,
                'P_idveiculo_inicial'       => $placa_inicial,
                'P_idveiculo_final'      => $placa_final,
                'P_id_filial_inicial'       => $id_filial_inicial,
                'P_id_filial_final'      => $id_filial_final,
                'P_in_status'               => $status_inicial,
                'P_id_status'            => $status_final
            );
            //== define pararemetros relatorios  
            $name = 'Sinistro_v2022_4';
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

            if (empty($request['id_sinistro']) || $request['id_sinistro'] == null) {
                $id_sinistro_inicial    = 0;
                $id_sinistro_final      = 999999;
            } else {
                $id_sinistro_inicial = $request['id_sinistro'];
                $id_sinistro_final   = $request['id_sinistro'];
            }

            if (empty($request['situacao_sinistro_processo']) || $request['situacao_sinistro_processo'] == null) {
                $status_inicial    = 'IS';
                $status_final      = "NOT NULL";
            } else {
                $status_inicial = '=';
                $status_final   = "'" . $request['situacao_sinistro_processo'] . "'";
            }

            if (empty($request['id_motorista']) || $request['id_motorista'] == null) {
                $id_motorista_inicial   = 0;
                $id_motorista_final     = 999999;
            } else {
                $id_motorista_inicial = $request['id_motorista'];
                $id_motorista_final   = $request['id_motorista'];
            }

            if (empty($request['id_veiculo']) || $request['id_veiculo'] == null) {
                $placa_inicial   = 0;
                $placa_final     = 999999;
            } else {
                $placa_inicial   = $request['id_veiculo'];
                $placa_final     = $request['id_veiculo'];
            }

            if (empty($request['id_filial'])  || $request['id_filial'] == null) {
                $id_filial_inicial  = 0;
                $id_filial_final    = 999999;
            } else {
                $id_filial_inicial  = $request['id_filial'];
                $id_filial_final    = $request['id_filial'];
            }

            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

            $parametros = array(
                'P_data_inicial'            => $datainicial,
                'P_data_final'           => $datafinal,
                'P_id_sinistro_inicial'     => $id_sinistro_inicial,
                'P_id_sinistro_final'    => $id_sinistro_final,
                'P_id_motorista_inicial'    => $id_motorista_inicial,
                'P_id_motorista_final'   => $id_motorista_final,
                'P_idveiculo_inicial'       => $placa_inicial,
                'P_idveiculo_final'      => $placa_final,
                'P_id_filial_inicial'       => $id_filial_inicial,
                'P_id_filial_final'      => $id_filial_final,
                'P_in_status'               => $status_inicial,
                'P_id_status'            => $status_final
            );
            //== define pararemetros relatorios  
            $name = 'Sinistro_v2022_5';
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

        return response()->json(['message' => 'Informe a data inicial e final para emissão do relatório.'], 400);
    }
}

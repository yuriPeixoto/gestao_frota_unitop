<?php

namespace App\Modules\Sinistros\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\Filial;
use App\Modules\Sinistros\Models\Sinistro;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioSinistro extends Controller
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
        if ($request->filled('situacao_sinistro_processo')) {
            $query->where('situacao_sinistro_processo', $request->input('situacao_sinistro_processo'));
        }
        if ($request->filled('setor')) {
            $query->where('setor', $request->input('setor'));
        }

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();

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


        $placa = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        $setor = Sinistro::whereIn('setor', ['CGB', 'CGR', 'FROTA', 'JVE', 'RH', 'ROO', 'SAO', 'SEGURADORA', 'TERCEIRO', 'TRAFEGO'])
            ->distinct('setor')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id_sinistro,
                    'label' => $item->setor
                ];
            })
            ->toArray();

        return view('admin.relatoriosinistro.index', compact('filial', 'setor', 'placa', 'status'));
    }

    public function gerarPdf(Request $request)
    {
        try {
            Log::info('Dados brutos recebidos:', $request->all());




            if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

                if ($request['situacao_sinistro_processo']) {
                    foreach ($request['situacao_sinistro_processo'] as $r) {
                        $status_[]    = "'" . $r . "'";
                    }
                }

                if (empty($request['situacao_sinistro_processo'])) {
                    $in_status = 'IS';
                    $id_status = 'NOT NULL';
                } else {
                    $in_status  = 'IN';
                    $status     = '(' . implode(',', $request['situacao_sinistro_processo']) . ')';
                    $id_status  = $status;
                }

                if (empty($request['id_veiculo'])) {
                    $in_veiculo = '!=';
                    $id_veiculo = '0';
                } else {
                    $in_veiculo  = 'IN';
                    $id_veiculo  = $request['id_veiculo'];
                }


                $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
                $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

                // Parâmetros Jasper
                $parametros = array(
                    'P_data_inicial' => $datainicial,
                    'P_data_final' => $datafinal,
                    'P_in_veiculo' => $in_veiculo,
                    'P_id_veiculo' => $id_veiculo,
                    'P_in_status' => $in_status,
                    'P_id_status' => $id_status
                );

                Log::info("Parâmetros Jasper:", $parametros);

                $name       = 'tabela_sinistro_v1';
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
        } catch (\Exception $e) {
            Log::error("Erro ao gerar PDF: " . $e->getMessage());
            return response()->json(['message' => 'Erro ao gerar o relatório PDF.'], 500);
        }
    }

    public function gerarExcel(Request $request)
    {
        try {
            Log::info('Dados brutos recebidos:', $request->all());




            if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

                if ($request['situacao_sinistro_processo']) {
                    foreach ($request['situacao_sinistro_processo'] as $r) {
                        $status_[]    = "'" . $r . "'";
                    }
                }

                if (empty($request['situacao_sinistro_processo'])) {
                    $in_status = 'IS';
                    $id_status = 'NOT NULL';
                } else {
                    $in_status  = 'IN';
                    $status     = '(' . implode(',', $request['situacao_sinistro_processo']) . ')';
                    $id_status  = $status;
                }

                if (empty($request['id_veiculo'])) {
                    $in_veiculo = '!=';
                    $id_veiculo = '0';
                } else {
                    $in_veiculo  = 'IN';
                    $id_veiculo  = $request['id_veiculo'];
                }


                $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
                $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

                // Parâmetros Jasper
                $parametros = array(
                    'P_data_inicial' => $datainicial,
                    'P_data_final' => $datafinal,
                    'P_in_veiculo' => $in_veiculo,
                    'P_id_veiculo' => $id_veiculo,
                    'P_in_status' => $in_status,
                    'P_id_status' => $id_status
                );

                Log::info("Parâmetros Jasper:", $parametros);

                $name       = 'tabela_sinistro_v2';
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

    public function gerarPdfTotalizado(Request $request)
    {
        try {
            Log::info('Dados brutos recebidos:', $request->all());

            if ($request['situacao_sinistro_processo']) {
                foreach ($request['situacao_sinistro_processo'] as $r) {
                    $status_[]    = "'" . $r . "'";
                }
            }

            if ($request['setor']) {
                foreach ($request['setor'] as $r) {
                    $setor_[]    = "'" . $r . "'";
                }
            }

            if (empty($request['situacao_sinistro_processo'])) {
                $in_status = 'IS';
                $id_status = 'NOT NULL';
            } else {
                $in_status  = 'IN';
                $status     = '(' . implode(',', $request['situacao_sinistro_processo']) . ')';
                $id_status  = $status;
            }

            if (empty($request['setor'])) {
                $in_setor = 'IS';
                $id_setor = 'NOT NULL';
            } else {
                $in_setor  = 'IN';
                $setor     = '(' . implode(',', $request['setor']) . ')';
                $id_setor  = $setor;
            }

            if (empty($request['id_filial'])) {
                $in_filial = '!=';
                $id_filial = '0';
            } else {
                $in_filial  = 'IN';
                $id_filial  = implode(',', $request['id_filial']);
            }


            // $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
            // $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

            // Parâmetros Jasper
            $parametros = array(
                'P_in_setor' => $in_setor,
                'P_id_setor' => $id_setor,
                'P_in_filial' => $in_filial,
                'P_id_filial' => $id_filial,
                'P_in_status' => $in_status,
                'P_id_status' => $id_status
            );

            Log::info("Parâmetros Jasper:", $parametros);

            $name       = 'sinistro_totalizado_v1';
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
        } catch (\Exception $e) {
            Log::error("Erro ao gerar PDF: " . $e->getMessage());
            return response()->json(['message' => 'Erro ao gerar o relatório PDF.'], 500);
        }
    }
}

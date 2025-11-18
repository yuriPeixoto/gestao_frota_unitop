<?php

namespace App\Modules\Sinistros\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Filial;
use App\Models\Motorista;
use App\Modules\Sinistros\Models\Sinistro;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioSinistroGeral extends Controller
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

        return view('admin.relatoriogeralsinistro.index', compact('filial', 'placa', 'motorista', 'sinistro'));
    }

    public function gerarPdf(Request $request)
    {
        try {
            Log::info('Dados brutos recebidos:', $request->all());


            // Produtos
            if ($request->filled(['data_inclusao', 'data_final'])) {

                $idsinistro = empty($request['id_sinistro']) ? "0" : $request['id_sinistro'];
                $idveiculo  = empty($request['id_veiculo']) ? "0" : $request['id_veiculo'];
                $idfilial   = empty($request['id_filial']) ? "0" : $request['id_filial'];

                if (empty($request['id_sinistro'])) {
                    $in_idsinistro  = '!=';
                    $id_idsinistro  = '0';
                } else {
                    $in_idsinistro  = 'IN';
                    $id_idsinistro  = $request['id_sinistro'];
                }
                if (empty($request['id_veiculo'])) {
                    $in_idveiculo  = '!=';
                    $id_idveiculo  = '0';
                } else {
                    $in_idveiculo  = 'IN';
                    $id_idveiculo  = $request['id_veiculo'];
                }
                if (empty($request['id_filial'])) {
                    $in_idfilial  = '!=';
                    $id_idfilial  = '0';
                } else {
                    $in_idfilial  = 'IN';
                    $id_idfilial  = $request['id_filial'];
                }
                if (empty($request['id_motorista']) || $request['id_motorista'] == null) {
                    $id_motorista_inicial = 0;
                    $id_motorista_final  = 999999;
                } else {
                    $id_motorista_inicial = $request['id_motorista'];
                    $id_motorista_final   = $request['id_motorista'];
                }

                $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
                $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

                $parametros = array(
                    'P_data_inicial' => $datainicial,
                    'P_data_final' => $datafinal,
                    'P_in_sinistro'  => $in_idsinistro,
                    'P_id_sinistro'  => $id_idsinistro,
                    'P_id_motorista_inicial' => $id_motorista_inicial,
                    'P_id_motorista_final' => $id_motorista_final,
                    'P_in_idveiculo' => $in_idveiculo,
                    'P_id_idveiculo' => $id_idveiculo,
                    'P_in_filial'    => $in_idfilial,
                    'P_id_filial'    => $id_idfilial
                );


                Log::info("Parâmetros Jasper:", $parametros);

                $name       = 'sinistro_geral';
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

                Log::info("Gerando pdf: {$relatorio}");
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
                        Log::error("Relatório pdf gerado vazio ou muito pequeno: tamanho " . strlen($data));
                        return response()->json(['message' => 'O relatório retornou vazio ou inválido.'], 500);
                    }

                    // Salva local para debug (opcional)
                    file_put_contents(storage_path("app/public/{$relatorio}"), $data);

                    return response($data, 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => "attachment; filename=\"{$relatorio}\"",
                    ]);
                } catch (\Exception $e) {
                    Log::error("Erro ao gerar pdf: " . $e->getMessage());
                    return response()->json(['message' => 'Erro ao gerar o relatório pdf.'], 500);
                }
            }
        } catch (\Exception $e) {
            Log::error("Erro ao gerar pdf: " . $e->getMessage());
            return response()->json(['message' => 'Erro ao gerar o relatório pdf.'], 500);
        }
    }

    public function gerarExcel(Request $request)
    {
        try {
            Log::info('Dados brutos recebidos:', $request->all());


            // Produtos
            if ($request->filled(['data_inclusao', 'data_final'])) {

                $idsinistro = empty($request['id_sinistro']) ? "0" : $request['id_sinistro'];
                $idveiculo  = empty($request['id_veiculo']) ? "0" : $request['id_veiculo'];
                $idfilial   = empty($request['id_filial']) ? "0" : $request['id_filial'];

                if (empty($request['id_sinistro'])) {
                    $in_idsinistro  = '!=';
                    $id_idsinistro  = '0';
                } else {
                    $in_idsinistro  = 'IN';
                    $id_idsinistro  = $request['id_sinistro'];
                }
                if (empty($request['id_veiculo'])) {
                    $in_idveiculo  = '!=';
                    $id_idveiculo  = '0';
                } else {
                    $in_idveiculo  = 'IN';
                    $id_idveiculo  = $request['id_veiculo'];
                }
                if (empty($request['id_filial'])) {
                    $in_idfilial  = '!=';
                    $id_idfilial  = '0';
                } else {
                    $in_idfilial  = 'IN';
                    $id_idfilial  = $request['id_filial'];
                }
                if (empty($request['id_motorista']) || $request['id_motorista'] == null) {
                    $id_motorista_inicial = 0;
                    $id_motorista_final  = 999999;
                } else {
                    $id_motorista_inicial = $request['id_motorista'];
                    $id_motorista_final   = $request['id_motorista'];
                }

                $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
                $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

                $parametros = array(
                    'P_data_inicial' => $datainicial,
                    'P_data_final' => $datafinal,
                    'P_in_sinistro'  => $in_idsinistro,
                    'P_id_sinistro'  => $id_idsinistro,
                    'P_id_motorista_inicial' => $id_motorista_inicial,
                    'P_id_motorista_final' => $id_motorista_final,
                    'P_in_idveiculo' => $in_idveiculo,
                    'P_id_idveiculo' => $id_idveiculo,
                    'P_in_filial'    => $in_idfilial,
                    'P_id_filial'    => $id_idfilial
                );


                Log::info("Parâmetros Jasper:", $parametros);

                $name       = 'sinistro_geral_v2';
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

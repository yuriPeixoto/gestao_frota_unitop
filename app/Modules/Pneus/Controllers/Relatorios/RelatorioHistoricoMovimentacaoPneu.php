<?php

namespace App\Modules\Pneus\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\ControleVidaPneus;
use App\Models\Departamento;
use App\Models\Filial;
use App\Models\Pneu;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioHistoricoMovimentacaoPneu extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = Pneu::query();

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->input('id_veiculo'));
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }

        if ($request->filled('id_controle_vida_pneu')) {
            $query->where('id_controle_vida_pneu', $request->input('id_controle_vida_pneu'));
        }

        if ($request->filled('id_departamento')) {
            $query->where('id_departamento', $request->input('id_departamento'));
        }

        if ($request->filled('id_pneu')) {
            $query->where('id_pneu', $request->input('id_pneu'));
        }

        $departamento = Departamento::select('id_departamento as value', 'descricao_departamento as label')
            ->orderBy('descricao_departamento')
            ->limit(30)
            ->get();

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();

        $placas = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        $pneu = Pneu::select('id_pneu as value', 'id_pneu as label')
            ->orderBy('id_pneu')
            ->limit(30)
            ->get();

        $vidaPneu = ControleVidaPneus::whereIn('descricao_vida_pneu', ['1', '2', '3', 'IMPORTACAO'])
            ->distinct('descricao_vida_pneu')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id_controle_vida_pneu,
                    'label' => $item->descricao_vida_pneu
                ];
            })
            ->toArray();

        return view('admin.relatoriodehistoricomovimentacaopneus.index', compact(
            'departamento',
            'placas',
            'vidaPneu',
            'pneu',
            'filial'
        ));
    }

    public function gerarPdf(Request $request)
    {
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->array());


        $placa = empty($request['id_veiculo']) ? "0" : $request['id_veiculo'];
        $filial = empty($request['id_filial']) ? "0" : $request['id_filial'];
        $departamento = empty($request['id_departamento']) ? "0" : $request['id_departamento'];
        $vidaPneu = empty($request['id_controle_vida_pneu']) ? "0" : $request['id_controle_vida_pneu'];
        $pneu = empty($request['id_pneu']) ? "0" : $request['id_pneu'];


        if (($request['data_inclusao'] != null && !empty($request['data_inclusao'])) && ($request['data_final'] != null && !empty($request['data_final']))) {
            if (empty($request['id_veiculo'])) {
                $in_veiculo  = '!=';
                $id_veiculo  = '0';
            } else {
                $in_veiculo  = 'IN';
                $id_veiculo  = $request('id_veiculo');
            }

            if (empty($request['id_filial'])) {
                $in_filial  = '!=';
                $id_filial  = '0';
            } else {
                $in_filial  = 'IN';
                $id_filial  = $request('id_filial');
            }

            if (empty($request['id_departamento'])) {
                $in_departamento  = '!=';
                $id_departamento  = '0';
            } else {
                $in_departamento  = 'IN';
                $in_departamento  = $request('id_departamento');
            }

            if (empty($request['id_controle_vida_pneu'])) {
                $in_vida  = '!=';
                $vida     = "'0'";
            } else {
                $in_vida  = 'IN';
                $vida     = $request('id_controle_vida_pneu');
                //$vida_2 =substr("','".$vida_1."',",1);
                //var_dump($vida);
            }
            if (empty($request['id_pneu'])) {
                $in_fogo  = '!=';
                $fogo     = "'0'";
            } else {
                $in_fogo  = 'IN';
                $fogo     = $request('id_pneu');
            }

            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal   = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

            $parametros = array(
                'P_in_veiculo' => $in_veiculo,
                'P_id_veiculo' => $id_veiculo,
                'P_in_filial' => $in_filial,
                'P_id_filial' => $id_filial,
                'P_in_departamento' => $in_departamento,
                'P_id_departamento' => $id_departamento,
                'P_in_vida_pneu' => $in_vida,
                'P_id_vida_pneu' => $vida,
                'P_in_pneu' => $in_fogo,
                'P_id_pneu' => $fogo,
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal
            );



            $name       = 'Pneus_Aplicados_Historico';
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
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->array());


        $placa = empty($request['id_veiculo']) ? "0" : $request['id_veiculo'];
        $filial = empty($request['id_filial']) ? "0" : $request['id_filial'];
        $departamento = empty($request['id_departamento']) ? "0" : $request['id_departamento'];
        $vidaPneu = empty($request['id_controle_vida_pneu']) ? "0" : $request['id_controle_vida_pneu'];
        $pneu = empty($request['id_pneu']) ? "0" : $request['id_pneu'];


        if (($request['data_inclusao'] != null && !empty($request['data_inclusao'])) && ($request['data_final'] != null && !empty($request['data_final']))) {
            if (empty($request['id_veiculo'])) {
                $in_veiculo  = '!=';
                $id_veiculo  = '0';
            } else {
                $in_veiculo  = 'IN';
                $id_veiculo  = $request('id_veiculo');
            }

            if (empty($request['id_filial'])) {
                $in_filial  = '!=';
                $id_filial  = '0';
            } else {
                $in_filial  = 'IN';
                $id_filial  = $request('id_filial');
            }

            if (empty($request['id_departamento'])) {
                $in_departamento  = '!=';
                $id_departamento  = '0';
            } else {
                $in_departamento  = 'IN';
                $in_departamento  = $request('id_departamento');
            }

            if (empty($request['id_controle_vida_pneu'])) {
                $in_vida  = '!=';
                $vida     = "'0'";
            } else {
                $in_vida  = 'IN';
                $vida     = $request('id_controle_vida_pneu');
                //$vida_2 =substr("','".$vida_1."',",1);
                //var_dump($vida);
            }
            if (empty($request['id_pneu'])) {
                $in_fogo  = '!=';
                $fogo     = "'0'";
            } else {
                $in_fogo  = 'IN';
                $fogo     = $request('id_pneu');
            }

            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal   = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

            $parametros = array(
                'P_in_veiculo' => $in_veiculo,
                'P_id_veiculo' => $id_veiculo,
                'P_in_filial' => $in_filial,
                'P_id_filial' => $id_filial,
                'P_in_departamento' => $in_departamento,
                'P_id_departamento' => $id_departamento,
                'P_in_vida_pneu' => $in_vida,
                'P_id_vida_pneu' => $vida,
                'P_in_pneu' => $in_fogo,
                'P_id_pneu' => $fogo,
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal
            );



            $name       = 'Pneus_Aplicados_Historico_v2';
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

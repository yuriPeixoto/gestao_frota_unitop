<?php

namespace App\Modules\Sinistros\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\VFilial;
use App\Models\Veiculo;
use App\Models\Pessoal;
use Illuminate\Http\Request;
use App\Traits\JasperServerIntegration;
use Illuminate\Support\Facades\Log;

class SinistroRelatorioController extends Controller
{
    public function index()
    {
        $filiais = $this->getFilial();
        $veiculos = $this->getPlacas();

        return view('admin.relatorios.sinistro.index', compact('filiais', 'veiculos'));
    }

    public function onGeneratePdf(Request $request)
    {
        if (!empty($request->dataInicio) && !empty($request->dataFim)) {
            if ($request->filled('status')) {
                foreach ($request->status as $r) {
                    $status_[]    = "'" . $r . "'";
                }
            }

            if (empty($request->status)) {
                $in_status = 'IS';
                $id_status = 'NOT NULL';
            } else {
                $in_status  = 'IN';
                $status     = '(' . implode(',', $status_) . ')';
                $id_status  = $status;
            }


            if (empty($request->idVeiculo)) {
                $in_veiculo = '!=';
                $id_veiculo = '0';
            } else {
                $in_veiculo  = 'IN';
                $id_veiculo  = implode(',', $request->idVeiculo);
            }

            $datainicial =  $request->dataInicio;
            $datafinal   =  $request->dataFim;

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_veiculo' => $in_veiculo,
                'P_id_veiculo' => $id_veiculo,
                'P_in_status' => $in_status,
                'P_id_status' => $id_status
            );

            $name       = 'tabela_sinistro_v1';
            $agora      = date('d-m-YH:i');
            $tipo       = '.pdf';
            $relatorio  = $name . $agora . $tipo;
            $barra      = '/';
            $partes = parse_url('http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
            $host = $partes['host'] . PHP_EOL;
            $pathrel = (explode('.', $host));
            $dominio = $pathrel[0];

            if ($dominio == '127' || $dominio == 'localhost' || strpos($host, '127.0.0.1') !== false) {
                $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
                $pastarelatorio = '/reports/homologacao/' . $name;

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

            $jsi = new jasperserverintegration(
                $jasperserver,
                $pastarelatorio,                        // Report Unit Path
                'pdf',                                  // Tipo da exportação do relatório
                'unitop',                               // Usuário com acesso ao relatório
                'unitop2022',                           // Senha do usuário
                $parametros                             // Conteudo do Array
            );

            try {
                $data = $jsi->execute();
                return response($data, 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="' . $relatorio . '"');
            } catch (\Exception $e) {
                Log::error('Erro ao gerar relatório: ' . $e->getMessage());
                return back()->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Não foi possível gerar o relatório. ' . $e->getMessage()
                ]);
            }
        } else {
            return response()->json(['info', "Atenção:Infome a data inicial e final para emissão do relatório."]);
        }
    }


    public function onGenerateTotalizador(Request $request)
    {
        try {
            if ($request->status) {
                foreach ($request->status as $r) {
                    $status_[]    = "'" . $r . "'";
                }
            }

            if ($request->setor) {
                foreach ($request->setor as $r) {
                    $setor_[]    = "'" . $r . "'";
                }
            }

            if (empty($request->setor)) {
                $in_status = 'IS';
                $id_status = 'NOT NULL';
            } else {
                $in_status  = 'IN';
                $status     = '(' . implode(',', $status_) . ')';
                $id_status  = $status;
            }

            if (empty($request->setor)) {
                $in_setor = 'IS';
                $id_setor = 'NOT NULL';
            } else {
                $in_setor  = 'IN';
                $setor     = '(' . implode(',', $setor_) . ')';
                $id_setor  = $setor;
            }

            if (empty($request->idFilial)) {
                $in_filial = '!=';
                $id_filial = '0';
            } else {
                $in_filial  = 'IN';
                $id_filial  = implode(',', $request->idFilial);
            }

            $parametros = array(
                'P_in_setor' => $in_setor,
                'P_id_setor' => $id_setor,
                'P_in_filial' => $in_filial,
                'P_id_filial' => $id_filial,
                'P_in_status' => $in_status,
                'P_id_status' => $id_status
            );

            $name       = 'sinistro_totalizado_v1';
            $agora      = date('d-m-YH:i');
            $tipo       = '.pdf';
            $relatorio  = $name . $agora . $tipo;
            $barra      = '/';
            $partes = parse_url('http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
            $host = $partes['host'] . PHP_EOL;
            $pathrel = (explode('.', $host));
            $dominio = $pathrel[0];

            if ($dominio == '127' || $dominio == 'localhost' || strpos($host, '127.0.0.1') !== false) {
                $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
                $pastarelatorio = '/reports/homologacao/' . $name;

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

            $jsi = new jasperserverintegration(
                $jasperserver,
                $pastarelatorio,                        // Report Unit Path
                'pdf',                                  // Tipo da exportação do relatório
                'unitop',                               // Usuário com acesso ao relatório
                'unitop2022',                           // Senha do usuário
                $parametros                             // Conteudo do Array
            );

            try {
                $data = $jsi->execute();
                return response($data, 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="' . $relatorio . '"');
            } catch (\Exception $e) {
                Log::error('Erro ao gerar relatório: ' . $e->getMessage());
                return back()->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Não foi possível gerar o relatório. ' . $e->getMessage()
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['error', $e->getMessage()]);
        }
    }

    public function onGenerateXls(Request $request)
    {
        if (!empty($request->dataInicio) && !empty($request->dataFim)) {
            if ($request->status) {
                foreach ($request->status as $r) {
                    $status_[]    = "'" . $r . "'";
                }
            }

            if (empty($request->status)) {
                $in_status = 'IS';
                $id_status = 'NOT NULL';
            } else {
                $in_status  = 'IN';
                $status     = '(' . implode(',', $status_) . ')';
                $id_status  = $status;
            }

            if (empty($request->idVeiculo)) {
                $in_veiculo = '!=';
                $id_veiculo = '0';
            } else {
                $in_veiculo  = 'IN';
                $id_veiculo  = implode(',', $request->idVeiculo);
            }

            $datainicial    =  $request->dataInicio;
            $datafinal      =  $request->dataFim;

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_veiculo' => $in_veiculo,
                'P_id_veiculo' => $id_veiculo,
                'P_in_status' => $in_status,
                'P_id_status' => $id_status
            );

            $name       = 'tabela_sinistro_v2';
            $agora      = date('d-m-YH:i');
            $tipo       = '.xls';
            $relatorio  = $name . $agora . $tipo;
            $barra      = '/';
            $partes = parse_url('http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
            $host = $partes['host'] . PHP_EOL;
            $pathrel = (explode('.', $host));
            $dominio = $pathrel[0];

            if ($dominio == '127' || $dominio == 'localhost' || strpos($host, '127.0.0.1') !== false) {
                $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
                $pastarelatorio = '/reports/homologacao/' . $name;

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

            $jsi = new jasperserverintegration(
                $jasperserver,
                $pastarelatorio,
                'xls',
                'unitop',
                'unitop2022',
                $parametros
            );

            try {
                $data = $jsi->execute();
                Log::info('Parâmetros executados: ', $parametros);

                return response($data, 200, [
                    'Content-Type' => 'application/vnd.ms-excel',
                    'Content-Disposition' => 'attachment; filename="' . $relatorio . '"',
                    'Content-Length' => strlen($data),
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0'
                ]);
            } catch (\Exception $e) {
                Log::error('Erro ao gerar relatório: ' . $e->getMessage());
                return back()->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Não foi possível gerar o relatório. ' . $e->getMessage()
                ]);
            }
        } else {
            return response()->json(['info', "Atenção:Infome a data inicial e final para emissão do relatório."]);
        }
    }

    public function getFilial()
    {
        return VFilial::select('id as value', 'name as label')->get()->toArray();
    }

    public function getPlacas()
    {
        return Veiculo::select('placa as label', 'id_veiculo as value')
            ->where('situacao_veiculo', true)
            ->orderBy('label')
            ->get()
            ->toArray();
    }
}

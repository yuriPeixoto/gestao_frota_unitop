<?php

namespace App\Modules\Sinistros\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\VFilial;
use App\Models\Veiculo;
use App\Models\Pessoal;
use App\Modules\Sinistros\Models\Sinistro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration;

class SinistroRelController extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $filiais = $this->getFilial();
        $veiculos = $this->getPlacas();
        $sinistros = $this->getSinistros();
        $motoristas = $this->getMotoristas();

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

        return view('admin.relatorios.relatoriosinistro.index', compact('filiais', 'veiculos', 'sinistros', 'motoristas', 'input'));
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

    public function getMotoristas()
    {
        return Pessoal::select('id_pessoal as value', 'nome as label')
            ->where('ativo', true)
            ->orderBy('nome')
            ->get()
            ->toArray();
    }

    public function getSinistros()
    {
        return Sinistro::select('id_sinistro as value', 'id_sinistro as label')
            ->orderBy('id_sinistro', 'desc')
            ->get()
            ->toArray();
    }

    public function onGeneratePdf(Request $request)
    {
        Log::info('Iniciando exportação de PDF', $request->all());

        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

            if ($request['status_']) {
                foreach ($request['status_'] as $r) {
                    $status_[]    = "'" . $r . "'";
                }
            }

            if (empty($request['status_'])) {
                $in_status = 'IS';
                $id_status = 'NOT NULL';
            } else {
                $in_status  = 'IN';
                $status     = '(' . implode(',', $status_) . ')';
                $id_status  = $status;
            }

            if (empty($request['id_veiculo'])) {
                $in_veiculo = '!=';
                $id_veiculo = '0';
            } else {
                $in_veiculo  = 'IN';
                $id_veiculo  = $request['id_veiculo'];
            }

            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal   = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

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

    public function onGenerateXls(Request $request)
    {
        LOG::DEBUG($request->all());
        if (!empty($request->dataInicio) && !empty($request->dataFim)) {
            if (empty($request->idSinitro) || $request->idSinitro == null) {
                $id_sinistro_inicial    = 0;
                $id_sinistro_final      = 999999;
            } else {
                $id_sinistro_inicial = $request->idSinitro;
                $id_sinistro_final   = $request->idSinitro;
            }

            if (empty($request->status) || $request->status == null) {
                $status_inicial    = 'IS';
                $status_final      = "NOT NULL";
            } else {
                $status_inicial = '=';
                $status_final   = "'" . $request->status . "'";
            }

            if (empty($request->idMotorista) || $request->idMotorista == null) {
                $id_motorista_inicial   = 0;
                $id_motorista_final     = 999999;
            } else {
                $id_motorista_inicial = $request->idMotorista;
                $id_motorista_final   = $request->idMotorista;
            }

            if (empty($request->idVeiculo) || $request->idVeiculo == null) {
                $placa_inicial   = 0;
                $placa_final     = 999999;
            } else {
                $placa_inicial   = $request->idVeiculo;
                $placa_final     = $request->idVeiculo;
            }

            if (empty($request->idFilial)  || $request->idFilial == null) {
                $id_filial_inicial  = 0;
                $id_filial_final    = 999999;
            } else {
                $id_filial_inicial  = $request->idFilial;
                $id_filial_final    = $request->idFilial;
            }

            $datainicial    =  $request->dataInicio;
            $datafinal      =  $request->dataFim;

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

            $name       = 'Sinistro_v2022_5';
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
}

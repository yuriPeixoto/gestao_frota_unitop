<?php

namespace App\Modules\Sinistros\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\Pessoal;
use App\Modules\Sinistros\Models\Sinistro;
use App\Models\Veiculo;
use App\Models\VFilial;
use Illuminate\Http\Request;
use App\Traits\JasperServerIntegration;
use Illuminate\Support\Facades\Log;

class SinistroGeralRelatorioController extends Controller
{
    public function index()
    {
        $motoristas = $this->getMotoristas();
        $veiculos = $this->getPlacas();
        $filiais = $this->getFilial();
        $sinistros = $this->getSinistros();

        return view('admin.relatorios.sinistrogeral.index', compact('motoristas', 'veiculos', 'filiais', 'sinistros'));
    }

    public function onGenerateGeralPdf(Request $request)
    {
        if (!empty($request->dataInicio) && !empty($request->dataFim)) {
            $idsinistro = empty($request->idSinistro) ? "0" : $request->idSinistro;
            $idveiculo  = empty($request->idVeiculo) ? "0" : $request->idVeiculo;
            $idfilial   = empty($request->idFilial) ? "0" : $request->idFilial;

            if (empty($request->idSinistro)) {
                $in_idsinistro  = '!=';
                $id_idsinistro  = 0;
            } else {
                $in_idsinistro  = 'IN';
                $id_idsinistro  = implode(",", $idsinistro);
            }
            if (empty($request->idVeiculo)) {
                $in_idveiculo  = '!=';
                $id_idveiculo  = 0;
            } else {
                $in_idveiculo  = 'IN';
                $id_idveiculo  = implode(",", $idveiculo);
            }
            if (empty($request->idFilial)) {
                $in_idfilial  = '!=';
                $id_idfilial  = 0;
            } else {
                $in_idfilial  = 'IN';
                $id_idfilial  = implode(",", $idfilial);
            }
            if (empty($request->idMotorista) || $request->idMotorista == null) {
                $id_motorista_inicial = 0;
                $id_motorista_final  = 999999;
            } else {
                $id_motorista_inicial = $request->idMotorista;
                $id_motorista_final   = $request->idMotorista;
            }

            $datainicial    =  $request->dataInicio;
            $datafinal      =  $request->dataFim;

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

            //== define pararemetros relatorios  
            $name  = 'sinistro_geral';
            $agora = date('d-m-YH:i');
            $tipo  = '.pdf';
            $relatorio = $name . $agora . $tipo;
            $barra = '/';
            //== pegar url tranformar em caminho do relatorio
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
            return response()->json(['info', "Atenção:Infome a data inicial e final para emissão do relatório."], 200);
        }
    }

    public function onGenerateXls(Request $request)
    {
        log::debug($request->all());
        if (!empty($request->dataInicio) && !empty($request->dataFim)) {

            $idsinistro = empty($request->idSinistro) ? "0" : $request->idSinistro;
            $idveiculo  = empty($request->idVeiculo) ? "0" : $request->idVeiculo;
            $idfilial   = empty($request->idFilial) ? "0" : $request->idFilial;

            if (empty($request->idSinistro)) {
                $in_idsinistro  = '!=';
                $id_idsinistro  = '0';
            } else {
                $in_idsinistro  = 'IN';
                $id_idsinistro  = implode(",", $idsinistro);
            }
            if (empty($request->idVeiculo)) {
                $in_idveiculo  = '!=';
                $id_idveiculo  = '0';
            } else {
                $in_idveiculo  = 'IN';
                $id_idveiculo  = implode(",", $idveiculo);
            }
            if (empty($request->idFilial)) {
                $in_idfilial  = '!=';
                $id_idfilial  = '0';
            } else {
                $in_idfilial  = 'IN';
                $id_idfilial  = implode(",", $idfilial);
            }
            if (empty($request->idMotorista) || $request->idMotorista == null) {
                $id_motorista_inicial = 0;
                $id_motorista_final  = 999999;
            } else {
                $id_motorista_inicial = $request->idMotorista;
                $id_motorista_final   = $request->idMotorista;
            }

            $datainicial    =  $request->dataInicio;
            $datafinal      =  $request->dataFim;

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

            //== define pararemetros relatorios  
            $name  = 'sinistro_geral_v2';
            $agora = date('d-m-YH:i');
            $tipo  = '.xls';
            $relatorio = $name . $agora . $tipo;
            $barra = '/';
            //== pegar url tranformar em caminho do relatorio
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
            return response()->json(['info', "Atenção:Infome a data inicial e final para emissão do relatório."], 200);
        }
    }

    public function getMotoristas()
    {
        return Pessoal::select('id_pessoal as value', 'nome as label')
            ->where('ativo', true)
            ->orderBy('nome')
            ->get()
            ->toArray();
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

    public function getSinistros()
    {
        return Sinistro::select('id_sinistro as value', 'id_sinistro as label')
            ->orderBy('id_sinistro', 'desc')
            ->get()
            ->toArray();
    }
}

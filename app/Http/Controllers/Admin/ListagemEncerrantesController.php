<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbastecimentoManual;
use App\Models\Bomba;
use App\Models\CategoriaVeiculo;
use App\Models\Departamento;
use App\Models\Filial;
use App\Models\Fornecedor;
use App\Models\TipoCombustivel;
use App\Models\TipoEquipamento;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use App\Traits\JasperServerIntegration;
use Illuminate\Support\Facades\Log;

class ListagemEncerrantesController extends Controller
{
    public function index(Request $request)
    {

        $abastecimentos = AbastecimentoManual::query();;

        $filiais = $this->getFiliais();

        $bomba = $this->getBomba();

        $tipoCombustivel = $this->getTipoCombustivel();

        return view('admin.listagemencerrantes.index', compact(
            'filiais',
            'bomba',
            'tipoCombustivel',
        ));
    }


    public function onImprimir(Request $request)
    {
        Log::info('=== DEBUG COMPLETO ===');
        Log::info('Método HTTP: ' . $request->method());
        Log::info('Todos os inputs: ', $request->all());

        try {
            // Receber como arrays ou converter para arrays se necessário
            $filial = $request->input('id_filial', []);
            $bomba = $request->input('id_bomba', []);
            $tipoCombustivel = $request->input('id_tipo_combustivel', []);

            // Converter strings em arrays se necessário
            if (!is_array($filial) && !empty($filial)) {
                $id_filial_inicio = 0;
                $id_filial_final  = 999999;
            } else {
                $id_filial_inicio = $filial;
                $id_filial_final  = $filial;
            }

            if (!is_array($bomba) && !empty($bomba)) {
                $id_bomba_inicial = 0;
                $id_bomba_final  = 999999;
            } else {
                $id_bomba_inicial = $bomba;
                $id_bomba_final  = $bomba;
            }


            if (!is_array($tipoCombustivel) && !empty($tipoCombustivel)) {
                $tipo_combustivel_inicial = 0;
                $tipo_combustivel_final   = 999999;
            } else {
                $tipo_combustivel_inicial = $tipoCombustivel;
                $tipo_combustivel_final   = $tipoCombustivel;
            }



            // Verificar se as datas foram informadas
            if (!$request->input('data_inclusao') || !$request->input('data_final_abastecimento')) {
                return back()->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Atenção: Informe a data inicial e final para emissão do relatório.'
                ]);
            }

            // Processar datas
            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final_abastecimento'))->format('Y-m-d');

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_id_filial_final' => $id_filial_final,
                'P_id_filial_inicio' => $id_filial_inicio,
                'P_id_bomba_inicial' => $id_bomba_inicial,
                'P_id_bomba_final' => $id_bomba_final,
                'P_id_comb_inicial' => $tipo_combustivel_inicial,
                'P_id_comb_final' => $tipo_combustivel_final
            );


            Log::info('Parâmetros processados: ', $parametros);

            // Resto da lógica do relatório...
            $name = 'listagem_encerrantes';
            $agora = date('d-m-YH:i');
            $tipo = '.pdf';
            $relatorio = $name . $agora . $tipo;

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
                'pdf',
                'unitop',
                'unitop2022',
                $parametros
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
            Log::error('Erro geral: ' . $e->getMessage());
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro inesperado: ' . $e->getMessage()
            ]);
        }
    }

    public function onImprimirExcel(Request $request)
    {
        Log::info('=== DEBUG COMPLETO ===');
        Log::info('Método HTTP: ' . $request->method());
        Log::info('Todos os inputs: ', $request->all());

        try {
            // Receber como arrays ou converter para arrays se necessário
            $filial = $request->input('id_filial', []);
            $bomba = $request->input('id_bomba', []);
            $tipoCombustivel = $request->input('id_tipo_combustivel', []);

            // Converter strings em arrays se necessário
            if (!is_array($filial) && !empty($filial)) {
                $id_filial_inicio = 0;
                $id_filial_final  = 999999;
            } else {
                $id_filial_inicio = $filial;
                $id_filial_final  = $filial;
            }

            if (!is_array($bomba) && !empty($bomba)) {
                $id_bomba_inicial = 0;
                $id_bomba_final  = 999999;
            } else {
                $id_bomba_inicial = $bomba;
                $id_bomba_final  = $bomba;
            }


            if (!is_array($tipoCombustivel) && !empty($tipoCombustivel)) {
                $tipo_combustivel_inicial = 0;
                $tipo_combustivel_final   = 999999;
            } else {
                $tipo_combustivel_inicial = $tipoCombustivel;
                $tipo_combustivel_final   = $tipoCombustivel;
            }



            // Verificar se as datas foram informadas
            if (!$request->input('data_inclusao') || !$request->input('data_final_abastecimento')) {
                return back()->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Atenção: Informe a data inicial e final para emissão do relatório.'
                ]);
            }

            // Processar datas
            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final_abastecimento'))->format('Y-m-d');

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_id_filial_final' => $id_filial_final,
                'P_id_filial_inicio' => $id_filial_inicio,
                'P_id_bomba_inicial' => $id_bomba_inicial,
                'P_id_bomba_final' => $id_bomba_final,
                'P_id_comb_inicial' => $tipo_combustivel_inicial,
                'P_id_comb_final' => $tipo_combustivel_final
            );


            Log::info('Parâmetros processados: ', $parametros);

            // Resto da lógica do relatório...
            $name = 'listagem_encerrantes_v2';
            $agora = date('d-m-YH:i');
            $tipo = '.xls';
            $relatorio = $name . $agora . $tipo;

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
        } catch (\Exception $e) {
            Log::error('Erro geral: ' . $e->getMessage());
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro inesperado: ' . $e->getMessage()
            ]);
        }
    }


    public function getFiliais()
    {
        return Filial::select('id as value', 'name as label')
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();
    }


    public function getBomba()
    {
        return Bomba::select('id_bomba as value', 'descricao_bomba as label')
            ->orderBy('descricao_bomba', 'asc')
            ->get()
            ->toArray();
    }

    public function getTipoCombustivel()
    {
        return TipoCombustivel::select('id_tipo_combustivel as value', 'descricao as label')
            ->orderBy('descricao', 'asc')
            ->get()
            ->toArray();
    }
}

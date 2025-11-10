<?php

namespace App\Modules\Abastecimentos\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Modules\Abastecimentos\Models\AbastecimentoManual;
use App\Models\CategoriaVeiculo;
use App\Models\Departamento;
use App\Models\Filial;
use App\Modules\Abastecimentos\Models\TipoCombustivel;
use App\Models\TipoEquipamento;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use App\Traits\JasperServerIntegration;
use Illuminate\Support\Facades\Log;

class FechamentoAbastecimentoMediaController extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $abastecimentos = AbastecimentoManual::query();;

        $filiais = $this->getFiliais();

        $categorias = $this->getCategorias();

        $tipoEquipamento = $this->getTipoEquipamento();

        $veiculos = $this->getVeiculos();

        $tipoCombustivel = $this->getTipoCombustivel();

        $departamento = $this->getDepartamento();

        return view('admin.fechamentoabastecimentomedia.index', compact(
            'filiais',
            'categorias',
            'tipoEquipamento',
            'veiculos',
            'tipoCombustivel',
            'departamento',
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
            $categoria = $request->input('id_categoria', []);
            $equipamento = $request->input('id_tipo_equipamento', []);
            $veiculo = $request->input('id_veiculo', []);
            $combustivel = $request->input('id_tipo_combustivel', []);
            $departamento = $request->input('id_departamento');

            // Converter strings em arrays se necessário
            if (!is_array($filial) && !empty($filial)) {
                $filial = [$filial];
            }
            if (!is_array($categoria) && !empty($categoria)) {
                $categoria = [$categoria];
            }
            if (!is_array($equipamento) && !empty($equipamento)) {
                $equipamento = [$equipamento];
            }
            if (!is_array($veiculo) && !empty($veiculo)) {
                $veiculo = [$veiculo];
            }
            if (!is_array($combustivel) && !empty($combustivel)) {
                $combustivel = [$combustivel];
            }
            if (!is_array($departamento) && !empty($departamento)) {
                $departamento = [$departamento];
            }

            // Verificar se as datas foram informadas
            if (!$request->input('data_inclusao') || !$request->input('data_final_abastecimento')) {
                return back()->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Atenção: Informe a data inicial e final para emissão do relatório.'
                ]);
            }

            // Processar filtros
            if (empty($filial)) {
                $in_filial  = '!=';
                $id_filial  = '0';
            } else {
                $in_filial  = 'IN';
                $id_filial  = implode(",", $filial);
            }
            if (empty($categoria)) {
                $in_categoria  = '!=';
                $id_categoria  = '0';
            } else {
                $in_categoria  = 'IN';
                $id_categoria  = implode(",", $categoria);
            }
            if (empty($equipamento)) {
                $in_equipamento  = '!=';
                $id_equipamento  = '0';
            } else {
                $in_equipamento  = 'IN';
                $id_equipamento  = implode(",", $equipamento);
            }
            if (empty($veiculo)) {
                $in_veiculo  = '!=';
                $id_veiculo  = '0';
            } else {
                $in_veiculo  = 'IN';
                $id_veiculo  = implode(",", $veiculo);
            }
            if (empty($departamento)) {
                $id_departamento_inicial = 0;
                $id_departamento_final   = 999999;
            } else {
                $id_departamento_inicial = $departamento;
                $id_departamento_final   = $departamento;
            }
            if (empty($combustivel) || $combustivel == null) {
                $tipo_tipo_combustivel_inicial  = 0;
                $tipo_tipo_combustivel_final    = 9999999;
            } else {
                $tipo_tipo_combustivel_inicial  = $combustivel;
                $tipo_tipo_combustivel_final    = $combustivel;
            }

            // Processar datas
            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final_abastecimento'))->format('Y-m-d');



            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_filial' => $in_filial,
                'P_id_filial' => $id_filial,
                'P_id_departamento_inicial' => $id_departamento_inicial,
                'P_id_departamento_final' => $id_departamento_final,
                'P_in_categoria' => $in_categoria,
                'P_id_categoria' => $id_categoria,
                'P_in_veiculo' => $in_veiculo,
                'P_id_veiculo' => $id_veiculo,
                'P_in_tipo' => $in_equipamento,
                'P_id_tipo' => $id_equipamento,
                'P_tipo_combustivel_inicial' => $tipo_tipo_combustivel_inicial,
                'P_tipo_combustivel_final' => $tipo_tipo_combustivel_final
            );

            Log::info('Parâmetros processados: ', $parametros);

            // Resto da lógica do relatório...
            $name = 'fechamento_abastecimento_media_v1';
            $agora = date('d-m-YH:i');
            $tipo = '.pdf';
            $relatorio = $name . $agora . $tipo;

            $partes = parse_url('http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
            $host = $partes['host'] . PHP_EOL;
            $pathrel = (explode('.', $host));
            $dominio = $pathrel[0];

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
            $categoria = $request->input('id_categoria', []);
            $equipamento = $request->input('id_tipo_equipamento', []);
            $veiculo = $request->input('id_veiculo', []);
            $combustivel = $request->input('id_tipo_combustivel', []);
            $departamento = $request->input('id_departamento');

            // Converter strings em arrays se necessário
            if (!is_array($filial) && !empty($filial)) {
                $filial = [$filial];
            }
            if (!is_array($categoria) && !empty($categoria)) {
                $categoria = [$categoria];
            }
            if (!is_array($equipamento) && !empty($equipamento)) {
                $equipamento = [$equipamento];
            }
            if (!is_array($veiculo) && !empty($veiculo)) {
                $veiculo = [$veiculo];
            }
            if (!is_array($combustivel) && !empty($combustivel)) {
                $combustivel = [$combustivel];
            }
            if (!is_array($departamento) && !empty($departamento)) {
                $departamento = [$departamento];
            }

            // Verificar se as datas foram informadas
            if (!$request->input('data_inclusao') || !$request->input('data_final_abastecimento')) {
                return back()->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Atenção: Informe a data inicial e final para emissão do relatório.'
                ]);
            }

            // Processar filtros
            if (empty($filial)) {
                $in_filial  = '!=';
                $id_filial  = '0';
            } else {
                $in_filial  = 'IN';
                $id_filial  = implode(",", $filial);
            }
            if (empty($categoria)) {
                $in_categoria  = '!=';
                $id_categoria  = '0';
            } else {
                $in_categoria  = 'IN';
                $id_categoria  = implode(",", $categoria);
            }
            if (empty($equipamento)) {
                $in_equipamento  = '!=';
                $id_equipamento  = '0';
            } else {
                $in_equipamento  = 'IN';
                $id_equipamento  = implode(",", $equipamento);
            }
            if (empty($veiculo)) {
                $in_veiculo  = '!=';
                $id_veiculo  = '0';
            } else {
                $in_veiculo  = 'IN';
                $id_veiculo  = implode(",", $veiculo);
            }
            if (empty($departamento)) {
                $id_departamento_inicial = 0;
                $id_departamento_final   = 999999;
            } else {
                $id_departamento_inicial = $departamento;
                $id_departamento_final   = $departamento;
            }
            if (empty($combustivel) || $combustivel == null) {
                $tipo_tipo_combustivel_inicial  = 0;
                $tipo_tipo_combustivel_final    = 9999999;
            } else {
                $tipo_tipo_combustivel_inicial  = $combustivel;
                $tipo_tipo_combustivel_final    = $combustivel;
            }

            // Processar datas
            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final_abastecimento'))->format('Y-m-d');



            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_filial' => $in_filial,
                'P_id_filial' => $id_filial,
                'P_id_departamento_inicial' => $id_departamento_inicial,
                'P_id_departamento_final' => $id_departamento_final,
                'P_in_categoria' => $in_categoria,
                'P_id_categoria' => $id_categoria,
                'P_in_veiculo' => $in_veiculo,
                'P_id_veiculo' => $id_veiculo,
                'P_in_tipo' => $in_equipamento,
                'P_id_tipo' => $id_equipamento,
                'P_tipo_combustivel_inicial' => $tipo_tipo_combustivel_inicial,
                'P_tipo_combustivel_final' => $tipo_tipo_combustivel_final
            );

            Log::info('Parâmetros processados: ', $parametros);

            // Resto da lógica do relatório...
            $name = 'fechamento_abastecimento_media_v2';
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


    public function getVeiculos()
    {
        return Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa', 'asc')
            ->limit(30)
            ->get()
            ->toArray();
    }

    public function getCategorias()
    {
        return CategoriaVeiculo::select('id_categoria as value', 'descricao_categoria as label')
            ->orderBy('descricao_categoria', 'asc')
            ->get()
            ->toArray();
    }

    public function getTipoEquipamento()
    {
        return TipoEquipamento::select('id_tipo_equipamento as value', 'descricao_tipo as label')
            ->orderBy('descricao_tipo', 'asc')
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

    public function getFiliais()
    {
        return Filial::select('id as value', 'name as label')
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();
    }

    public function getDepartamento()
    {
        return Departamento::select('id_departamento as value', 'descricao_departamento as label')
            ->orderBy('descricao_departamento', 'asc')
            ->get()
            ->toArray();
    }
}

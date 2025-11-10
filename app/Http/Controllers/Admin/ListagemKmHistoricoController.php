<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbastecimentoManual;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use App\Traits\JasperServerIntegration;
use Illuminate\Support\Facades\Log;

class ListagemKmHistoricoController extends Controller
{
    public function index(Request $request)
    {

        $abastecimentos = AbastecimentoManual::query();;

        $veiculos = $this->getVeiculo();

        return view('admin.listagemkmhistorico.index', compact(
            'veiculos',
        ));
    }

    public function onImprimir(Request $request)
    {
        Log::info('=== DEBUG COMPLETO ===');
        Log::info('Método HTTP: ' . $request->method());
        Log::info('Todos os inputs: ', $request->all());

        // Aumentar tempo limite de execução para 10 minutos
        set_time_limit(600);

        // Aumentar limite de memória se necessário
        ini_set('memory_limit', '512M');

        try {
            // Verificar se as datas foram informadas primeiro
            if (!$request->input('data_inclusao') || !$request->input('data_final_abastecimento')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Atenção: Informe a data inicial e final para emissão do relatório.'
                ], 400);
            }

            // Verificar se o período não é muito longo (opcional - para evitar relatórios muito grandes)
            $dataInicial = \Carbon\Carbon::parse($request->input('data_inclusao'));
            $dataFinal = \Carbon\Carbon::parse($request->input('data_final_abastecimento'));
            $diffInDays = $dataInicial->diffInDays($dataFinal);

            if ($diffInDays > 365) {
                return response()->json([
                    'success' => false,
                    'message' => 'Período muito longo. Por favor, selecione um período de até 1 ano para garantir melhor performance.'
                ], 400);
            }

            // Receber dados do request
            $veiculo = $request->input('id_veiculo', '');
            $tipo = $request->input('tipo', '');

            // Processar veículo
            if (empty($veiculo)) {
                $in_veiculo = '!=';
                $id_veiculo = '0';
            } else {
                $in_veiculo = '=';
                $id_veiculo = $veiculo;
            }

            // Processar tipo
            if (empty($tipo)) {
                $in_tipo = '!=';
                $id_tipo = '0';
            } else {
                $in_tipo = 'IN';
                $id_tipo = $tipo;
            }

            // Configurações padrão para outros filtros
            $in_tipo_combustivel = '!=';
            $id_tipo_combustivel = '0';

            $in_filial = '!=';
            $id_filial = '0';

            $in_departamento = '!=';
            $id_departamento = '0';

            $in_categoria = '!=';
            $id_categoria = '0';

            // Processar datas
            $datainicial = $dataInicial->format('Y-m-d');
            $datafinal = $dataFinal->format('Y-m-d');

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_id_departamento_inicial' => $in_departamento,
                'P_id_departamento_final' => $id_departamento,
                'P_tipo_combustivel_inicial' => $in_tipo_combustivel,
                'P_tipo_combustivel_final' => $id_tipo_combustivel,
                'P_in_filial' => $in_filial,
                'P_id_filial' => $id_filial,
                'P_in_veiculo' => $in_veiculo,
                'P_id_veiculo' => $id_veiculo,
                'P_in_categoria' => $in_categoria,
                'P_id_categoria' => $id_categoria,
                'P_in_tipo' => $in_tipo,
                'P_id_tipo' => $id_tipo,
            );

            Log::info('Parâmetros processados: ', $parametros);

            // Configuração do relatório
            $name = 'listagem_km';
            $agora = date('d-m-Y_H-i-s');
            $tipo_arquivo = '.xls';
            $relatorio = $name . '_' . $agora . $tipo_arquivo;

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

            Log::info('Iniciando geração do relatório...');
            Log::info('Servidor Jasper: ' . $jasperserver);
            Log::info('Pasta do relatório: ' . $pastarelatorio);

            try {
                $jsi = new jasperserverintegration(
                    $jasperserver,
                    $pastarelatorio,
                    'xls',
                    'unitop',
                    'unitop2022',
                    $parametros
                );

                Log::info('Executando relatório...');
                $data = $jsi->execute();

                if (empty($data)) {
                    throw new \Exception('Dados do relatório estão vazios');
                }

                Log::info('Relatório gerado com sucesso. Tamanho: ' . strlen($data) . ' bytes');

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
                Log::error('Stack trace: ' . $e->getTraceAsString());

                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível gerar o relatório. Erro: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Erro geral: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Erro inesperado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getVeiculo()
    {
        return Veiculo::select('id_veiculo as value', 'placa as label')
            ->limit(30)
            ->orderBy('placa', 'asc')
            ->get()
            ->toArray();
    }
}

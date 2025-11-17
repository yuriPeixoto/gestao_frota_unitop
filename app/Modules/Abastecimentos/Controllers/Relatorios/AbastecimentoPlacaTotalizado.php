<?php

namespace App\Modules\Abastecimentos\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Modules\Abastecimentos\Models\AbastecimentoManual;
use App\Models\CategoriaVeiculo;
use App\Modules\Configuracoes\Models\Departamento;
use App\Modules\Configuracoes\Models\Filial;
use App\Modules\Abastecimentos\Models\TipoCombustivel;
use App\Modules\Configuracoes\Models\TipoEquipamento;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use App\Traits\JasperServerIntegration;
use Illuminate\Support\Facades\Log;

class AbastecimentoPlacaTotalizado extends Controller
{

    public function index(Request $request)
    {

        $abastecimentos = AbastecimentoManual::query();;

        $filiais = $this->getFilial();

        $veiculosFrequentes = $this->getVeiculosFrequentes();

        $tipoEquipamento = $this->getTipoEquipamento();

        $categoriaVeiculo = $this->getCategoriaVeiculo();


        $departamento = $this->getDepartamento();

        return view('admin.abastecimentoplacatotalizado.index', compact(
            'abastecimentos',
            'filiais',
            'veiculosFrequentes',
            'tipoEquipamento',
            'categoriaVeiculo',
            'departamento'
        ));
    }
    public function onImprimir(Request $request)
    {
        Log::info('=== DEBUG COMPLETO ===');
        Log::info('Método HTTP: ' . $request->method());
        Log::info('Content-Type: ' . $request->header('Content-Type'));
        Log::info('Todos os inputs: ', $request->all());
        Log::info('Body bruto: ' . $request->getContent());

        try {
            // Verificar se o request é JSON
            if ($request->isJson()) {
                $data = $request->json()->all();
                Log::info('Dados JSON recebidos: ', $data);
            } else {
                $data = $request->all();
                Log::info('Dados form recebidos: ', $data);
            }

            // Receber os dados (compatível com JSON e form-data)
            $veiculo = $request->input('id_veiculo', []);
            $filial = $request->input('id_filial', []);
            $categoria = $request->input('id_categoria', []);
            $equipamento = $request->input('id_tipo_equipamento', []);
            $departamento = $request->input('id_departamento');

            Log::info('Valores recebidos antes do processamento:', [
                'veiculo' => $veiculo,
                'filial' => $filial,
                'categoria' => $categoria,
                'equipamento' => $equipamento,
                'departamento' => $departamento
            ]);

            // Array com os campos que precisam ser convertidos para array
            $campos_array = ['filial', 'categoria', 'equipamento', 'veiculo'];

            // Converter strings em arrays se necessário
            foreach ($campos_array as $campo) {
                // Usar referência para modificar a variável original
                $valor = $$campo;

                if (!is_array($valor) && !empty($valor)) {
                    $$campo = [$valor];
                } elseif (empty($valor)) {
                    $$campo = [];
                }
            }

            Log::info('Valores após conversão para array:', [
                'veiculo' => $veiculo,
                'filial' => $filial,
                'categoria' => $categoria,
                'equipamento' => $equipamento
            ]);

            // Verificar se as datas foram informadas
            $data_inclusao = $request->input('data_inclusao');
            $data_final = $request->input('data_final_abastecimento');

            if (!$data_inclusao || !$data_final) {
                Log::warning('Datas não informadas', [
                    'data_inclusao' => $data_inclusao,
                    'data_final_abastecimento' => $data_final
                ]);

                return response()->json([
                    'success' => false,
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Atenção: Informe a data inicial e final para emissão do relatório.'
                ], 400);
            }

            // Configuração dos filtros usando função helper
            $filtros_config = [
                ['array' => $filial, 'in_var' => 'in_filial', 'id_var' => 'id_filial'],
                ['array' => $categoria, 'in_var' => 'in_categoria', 'id_var' => 'id_categoria'],
                ['array' => $equipamento, 'in_var' => 'in_equipamento', 'id_var' => 'id_equipamento'],
                ['array' => $veiculo, 'in_var' => 'in_veiculo', 'id_var' => 'id_veiculo']
            ];

            // Processar filtros dinamicamente
            foreach ($filtros_config as $config) {
                $array = $config['array'];
                $in_var = $config['in_var'];
                $id_var = $config['id_var'];

                if (empty($array) || in_array('', $array) || in_array('0', $array)) {
                    $$in_var = '!=';
                    $$id_var = '0';
                } else {
                    $$in_var = 'IN';
                    $$id_var = implode(",", array_filter($array));
                }
            }

            // DEPARTAMENTO (tratamento especial)
            if (empty($departamento) || $departamento == '0' || $departamento == '') {
                $id_departamento_inicial = 0;
                $id_departamento_final = 999999;
            } else {
                $id_departamento_inicial = $departamento;
                $id_departamento_final = $departamento;
            }

            // Processar datas com validação
            try {
                $datainicial = \Carbon\Carbon::parse($data_inclusao)->format('Y-m-d');
                $datafinal = \Carbon\Carbon::parse($data_final)->format('Y-m-d');
            } catch (\Exception $e) {
                Log::error('Erro ao processar datas: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Formato de data inválido.'
                ], 400);
            }

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
                'P_id_tipo' => $id_equipamento
            );

            Log::info('Parâmetros processados: ', $parametros);

            // Resto da lógica do relatório...
            $name = 'relatorio_abastecimento_placa';
            $agora = date('d-m-YH:i');
            $tipo = '.pdf';
            $relatorio = $name . $agora . $tipo;

            // Melhorar a obtenção do host
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

            Log::info('Configurações do Jasper:', [
                'jasperserver' => $jasperserver,
                'pastarelatorio' => $pastarelatorio
            ]);


            $jsi = new jasperserverintegration(
                $jasperserver,
                $pastarelatorio,
                'pdf',
                'unitop',
                'unitop2022',
                $parametros
            );

            try {
                Log::info('Executando relatório...');
                $data = $jsi->execute();

                if (!$data) {
                    Log::error('Relatório retornou dados vazios');
                    return response()->json([
                        'success' => false,
                        'title' => 'Erro!',
                        'type' => 'error',
                        'message' => 'Relatório gerado está vazio.'
                    ], 500);
                }

                Log::info('Relatório gerado com sucesso, tamanho: ' . strlen($data) . ' bytes');

                return response($data, 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="' . $relatorio . '"');
            } catch (\Exception $e) {
                Log::error('Erro ao gerar relatório: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());

                return response()->json([
                    'success' => false,
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Não foi possível gerar o relatório: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Erro geral: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'title' => 'Erro!',
                'type' => 'error',
                'message' => 'Erro inesperado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function onImprimirExcel(Request $request)
    {
        Log::info('=== DEBUG COMPLETO ===');
        Log::info('Método HTTP: ' . $request->method());
        Log::info('Content-Type: ' . $request->header('Content-Type'));
        Log::info('Todos os inputs: ', $request->all());
        Log::info('Body bruto: ' . $request->getContent());

        try {
            // Verificar se o request é JSON
            if ($request->isJson()) {
                $data = $request->json()->all();
                Log::info('Dados JSON recebidos: ', $data);
            } else {
                $data = $request->all();
                Log::info('Dados form recebidos: ', $data);
            }

            // Receber os dados (compatível com JSON e form-data)
            $veiculo = $request->input('id_veiculo', []);
            $filial = $request->input('id_filial', []);
            $categoria = $request->input('id_categoria', []);
            $equipamento = $request->input('id_tipo_equipamento', []);
            $departamento = $request->input('id_departamento');

            Log::info('Valores recebidos antes do processamento:', [
                'veiculo' => $veiculo,
                'filial' => $filial,
                'categoria' => $categoria,
                'equipamento' => $equipamento,
                'departamento' => $departamento
            ]);

            // Array com os campos que precisam ser convertidos para array
            $campos_array = ['filial', 'categoria', 'equipamento', 'veiculo'];

            // Converter strings em arrays se necessário
            foreach ($campos_array as $campo) {
                // Usar referência para modificar a variável original
                $valor = $$campo;

                if (!is_array($valor) && !empty($valor)) {
                    $$campo = [$valor];
                } elseif (empty($valor)) {
                    $$campo = [];
                }
            }

            Log::info('Valores após conversão para array:', [
                'veiculo' => $veiculo,
                'filial' => $filial,
                'categoria' => $categoria,
                'equipamento' => $equipamento
            ]);

            // Verificar se as datas foram informadas
            $data_inclusao = $request->input('data_inclusao');
            $data_final = $request->input('data_final_abastecimento');

            if (!$data_inclusao || !$data_final) {
                Log::warning('Datas não informadas', [
                    'data_inclusao' => $data_inclusao,
                    'data_final_abastecimento' => $data_final
                ]);

                return response()->json([
                    'success' => false,
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Atenção: Informe a data inicial e final para emissão do relatório.'
                ], 400);
            }

            // Configuração dos filtros usando função helper
            $filtros_config = [
                ['array' => $filial, 'in_var' => 'in_filial', 'id_var' => 'id_filial'],
                ['array' => $categoria, 'in_var' => 'in_categoria', 'id_var' => 'id_categoria'],
                ['array' => $equipamento, 'in_var' => 'in_equipamento', 'id_var' => 'id_equipamento'],
                ['array' => $veiculo, 'in_var' => 'in_veiculo', 'id_var' => 'id_veiculo']
            ];

            // Processar filtros dinamicamente
            foreach ($filtros_config as $config) {
                $array = $config['array'];
                $in_var = $config['in_var'];
                $id_var = $config['id_var'];

                if (empty($array) || in_array('', $array) || in_array('0', $array)) {
                    $$in_var = '!=';
                    $$id_var = '0';
                } else {
                    $$in_var = 'IN';
                    $$id_var = implode(",", array_filter($array));
                }
            }

            // DEPARTAMENTO (tratamento especial)
            if (empty($departamento) || $departamento == '0' || $departamento == '') {
                $id_departamento_inicial = 0;
                $id_departamento_final = 999999;
            } else {
                $id_departamento_inicial = $departamento;
                $id_departamento_final = $departamento;
            }

            // Processar datas com validação
            try {
                $datainicial = \Carbon\Carbon::parse($data_inclusao)->format('Y-m-d');
                $datafinal = \Carbon\Carbon::parse($data_final)->format('Y-m-d');
            } catch (\Exception $e) {
                Log::error('Erro ao processar datas: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Formato de data inválido.'
                ], 400);
            }

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
                'P_id_tipo' => $id_equipamento
            );

            Log::info('Parâmetros processados: ', $parametros);

            // Resto da lógica do relatório...
            $name = 'relatorio_abastecimento_placa_v2';
            $agora = date('d-m-YH:i');
            $tipo = '.xls';
            $relatorio = $name . $agora . $tipo;

            // Melhorar a obtenção do host
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

            Log::info('Configurações do Jasper:', [
                'jasperserver' => $jasperserver,
                'pastarelatorio' => $pastarelatorio
            ]);


            $jsi = new jasperserverintegration(
                $jasperserver,
                $pastarelatorio,
                'xls',
                'unitop',
                'unitop2022',
                $parametros
            );

            try {
                Log::info('Executando relatório...');
                $data = $jsi->execute();

                if (!$data) {
                    Log::error('Relatório retornou dados vazios');
                    return response()->json([
                        'success' => false,
                        'title' => 'Erro!',
                        'type' => 'error',
                        'message' => 'Relatório gerado está vazio.'
                    ], 500);
                }

                Log::info('Relatório gerado com sucesso, tamanho: ' . strlen($data) . ' bytes');

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
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Não foi possível gerar o relatório: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Erro geral: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'title' => 'Erro!',
                'type' => 'error',
                'message' => 'Erro inesperado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getFilial()
    {
        return Filial::select('id as value', 'name as label')
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
    }

    public function getVeiculosFrequentes()
    {
        return Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('id_veiculo', 'desc')
            ->limit(30)
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

    public function getCategoriaVeiculo()
    {
        return CategoriaVeiculo::select('id_categoria as value', 'descricao_categoria as label')
            ->orderBy('descricao_categoria', 'asc')
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

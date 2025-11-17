<?php

namespace App\Modules\Abastecimentos\Controllers;

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

class ConsultarLancamentosKmManualController extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $abastecimentos = AbastecimentoManual::query();;

        $veiculosFrequentes = $this->getVeiculosFrequentes();

        return view('admin.consultarlancamentoskmmanual.index', compact(
            'veiculosFrequentes',
        ));
    }

    public function onImprimir(Request $request)
    {
        Log::info('=== DEBUG COMPLETO - CONSULTA KM MANUAL ===');
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

            // Capturar os dados de entrada
            $data_inicial = $request->input('data_inclusao');
            $data_final = $request->input('data_final_abastecimento');
            $placa = $request->input('placa') ?? null;

            Log::info('Dados processados:', [
                'data_inicial' => $data_inicial,
                'data_final' => $data_final,
                'placa' => $placa
            ]);

            // Verificar se as datas foram informadas
            if (empty($data_inicial) || empty($data_final)) {
                Log::warning('Datas não informadas', [
                    'data_inicial' => $data_inicial,
                    'data_final' => $data_final
                ]);

                return response()->json([
                    'success' => false,
                    'title' => 'Atenção!',
                    'type' => 'info',
                    'message' => 'Informe a data inicial e final para emissão do relatório.'
                ], 400);
            }

            // Processar placa/veículo
            if (empty($placa)) {
                $id_veiculo_inicial = 0;
                $id_veiculo_final = 99999999;
                Log::info('Todas as placas serão incluídas');
            } else {
                $id_veiculo_inicial = $placa;
                $id_veiculo_final = $placa;
                Log::info('Placa específica selecionada: ' . $placa);
            }

            // Processar datas com validação
            try {
                $datainicial = \Carbon\Carbon::parse($data_inicial)->format('Y-m-d');
                $datafinal = \Carbon\Carbon::parse($data_final)->format('Y-m-d');

                Log::info('Datas processadas:', [
                    'data_inicial' => $datainicial,
                    'data_final' => $datafinal
                ]);
            } catch (\Exception $e) {
                Log::error('Erro ao processar datas: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Formato de data inválido.'
                ], 400);
            }

            // Montar parâmetros do relatório
            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_id_veiculo_inicial' => $id_veiculo_inicial,
                'P_id_veiculo_final' => $id_veiculo_final
            );

            Log::info('Parâmetros do relatório: ', $parametros);

            // Definir parâmetros do relatório
            $name = 'consulta_km_manual';
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

            // Configurar servidor Jasper baseado no domínio
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
                'pastarelatorio' => $pastarelatorio,
            ]);


            // Criar o objeto do JasperServerIntegration
            $jsi = new jasperserverintegration(
                $jasperserver,
                $pastarelatorio,    // Report Unit Path
                'pdf',              // Tipo da exportação do relatório
                'unitop',           // Usuário com acesso ao relatório
                'unitop2022',       // Senha do usuário
                $parametros         // Conteúdo do Array
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

                // Retornar o PDF diretamente para download
                return response($data, 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="' . $relatorio . '"')
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', '0');
            } catch (\Exception $e) {
                Log::error('Erro ao gerar relatório JasperServer: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());

                // Verificar se é erro de conexão ou credenciais
                $errorMessage = $e->getMessage();
                if (strpos($errorMessage, 'Connection') !== false) {
                    $userMessage = 'Erro de conexão com o servidor de relatórios.';
                } elseif (strpos($errorMessage, 'Authentication') !== false || strpos($errorMessage, 'Login') !== false) {
                    $userMessage = 'Erro de autenticação no servidor de relatórios.';
                } elseif (strpos($errorMessage, 'Report') !== false) {
                    $userMessage = 'Relatório não encontrado no servidor.';
                } else {
                    $userMessage = 'Não foi possível gerar o relatório: ' . $errorMessage;
                }

                return response()->json([
                    'success' => false,
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => $userMessage
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Erro geral na função onImprimir: ' . $e->getMessage());
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
        Log::info('=== DEBUG COMPLETO - CONSULTA KM MANUAL ===');
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

            // Capturar os dados de entrada
            $data_inicial = $request->input('data_inclusao');
            $data_final = $request->input('data_final_abastecimento');
            $placa = $request->input('placa') ?? null;

            Log::info('Dados processados:', [
                'data_inicial' => $data_inicial,
                'data_final' => $data_final,
                'placa' => $placa
            ]);

            // Verificar se as datas foram informadas
            if (empty($data_inicial) || empty($data_final)) {
                Log::warning('Datas não informadas', [
                    'data_inicial' => $data_inicial,
                    'data_final' => $data_final
                ]);

                return response()->json([
                    'success' => false,
                    'title' => 'Atenção!',
                    'type' => 'info',
                    'message' => 'Informe a data inicial e final para emissão do relatório.'
                ], 400);
            }

            // Processar placa/veículo
            if (empty($placa)) {
                $id_veiculo_inicial = 0;
                $id_veiculo_final = 99999999;
                Log::info('Todas as placas serão incluídas');
            } else {
                $id_veiculo_inicial = $placa;
                $id_veiculo_final = $placa;
                Log::info('Placa específica selecionada: ' . $placa);
            }

            // Processar datas com validação
            try {
                $datainicial = \Carbon\Carbon::parse($data_inicial)->format('Y-m-d');
                $datafinal = \Carbon\Carbon::parse($data_final)->format('Y-m-d');

                Log::info('Datas processadas:', [
                    'data_inicial' => $datainicial,
                    'data_final' => $datafinal
                ]);
            } catch (\Exception $e) {
                Log::error('Erro ao processar datas: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Formato de data inválido.'
                ], 400);
            }

            // Montar parâmetros do relatório
            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_id_veiculo_inicial' => $id_veiculo_inicial,
                'P_id_veiculo_final' => $id_veiculo_final
            );

            Log::info('Parâmetros do relatório: ', $parametros);

            // Definir parâmetros do relatório
            $name = 'consulta_km_manual_v2';
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

            // Configurar servidor Jasper baseado no domínio
            if ($dominio == '127' || $dominio == 'localhost' || strpos($host, '127.0.0.1') !== false) {
                $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
                $pastarelatorio = '/reports/homologacao/' . $name;
                $imprime = 'homologacao';

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
                $imprime = $dominio;

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
                $imprime = $dominio;

                Log::info('Usando servidor de produção');
            }

            Log::info('Configurações do Jasper:', [
                'jasperserver' => $jasperserver,
                'pastarelatorio' => $pastarelatorio,
                'imprime' => $imprime
            ]);


            // Criar o objeto do JasperServerIntegration
            $jsi = new jasperserverintegration(
                $jasperserver,
                $pastarelatorio,    // Report Unit Path
                'xls',              // Tipo da exportação do relatório
                'unitop',           // Usuário com acesso ao relatório
                'unitop2022',       // Senha do usuário
                $parametros         // Conteúdo do Array
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
                Log::error('Erro ao gerar relatório JasperServer: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());

                // Verificar se é erro de conexão ou credenciais
                $errorMessage = $e->getMessage();
                if (strpos($errorMessage, 'Connection') !== false) {
                    $userMessage = 'Erro de conexão com o servidor de relatórios.';
                } elseif (strpos($errorMessage, 'Authentication') !== false || strpos($errorMessage, 'Login') !== false) {
                    $userMessage = 'Erro de autenticação no servidor de relatórios.';
                } elseif (strpos($errorMessage, 'Report') !== false) {
                    $userMessage = 'Relatório não encontrado no servidor.';
                } else {
                    $userMessage = 'Não foi possível gerar o relatório: ' . $errorMessage;
                }

                return response()->json([
                    'success' => false,
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => $userMessage
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Erro geral na função onImprimir: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'title' => 'Erro!',
                'type' => 'error',
                'message' => 'Erro inesperado: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getVeiculosFrequentes()
    {
        return Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('id_veiculo', 'desc')
            ->limit(30)
            ->get()
            ->toArray();
    }
}

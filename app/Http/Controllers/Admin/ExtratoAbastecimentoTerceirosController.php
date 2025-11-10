<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbastecimentoManual;
use App\Models\CategoriaVeiculo;
use App\Models\Departamento;
use App\Models\Filial;
use App\Models\TipoCombustivel;
use App\Models\TipoEquipamento;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use App\Traits\JasperServerIntegration;
use Illuminate\Support\Facades\Log;

class ExtratoAbastecimentoTerceirosController extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $abastecimentos = AbastecimentoManual::query();;

        $veiculos = $this->getVeiculos();

        return view('admin.extratoabastecimentoterceiros.index', compact(
            'veiculos',
        ));
    }


    public function onImprimir(Request $request)
    {
        Log::info('=== DEBUG COMPLETO - ABASTECIMENTOS TERCEIRO ===');
        Log::info('Método HTTP: ' . $request->method());
        Log::info('Content-Type: ' . $request->header('Content-Type'));
        Log::info('Todos os inputs: ', $request->all());
        Log::info('Body bruto: ' . $request->getContent());

        try {
            // Verificar se o request é JSON
            if ($request->isJson()) {
                $data = $request->json()->all();
                $param = $data; // Compatibilidade com o código original
                Log::info('Dados JSON recebidos: ', $data);
            } else {
                $data = $request->all();
                $param = $data; // Compatibilidade com o código original
                Log::info('Dados form recebidos: ', $data);
            }

            // Capturar os dados de entrada com múltiplas formas de nomenclatura
            $data_inicial = $request->input('data_inicial') ??
                $request->input('data_inclusao');

            $data_final = $request->input('data_final') ??
                $request->input('data_final_abastecimento');

            $placa_input = $request->input('placa') ??
                $request->input('id_veiculo');

            // Processar placa - pode ser array ou string
            $placa = empty($placa_input) ? "0" : $placa_input;

            Log::info('Dados processados:', [
                'data_inicial' => $data_inicial,
                'data_final' => $data_final,
                'placa_input' => $placa_input,
                'placa_processada' => $placa
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

            // Processar lógica de veículos
            if (empty($placa_input)) {
                $in_veiculo = '!=';
                $id_veiculo = '0';
                Log::info('Todos os veículos serão incluídos');
            } else {
                $in_veiculo = 'IN';
                // Se placa for array, usar implode; se for string, usar como está
                if (is_array($placa)) {
                    $id_veiculo = implode(",", array_filter($placa));
                } else {
                    $id_veiculo = $placa;
                }
                Log::info('Veículos específicos selecionados: ' . $id_veiculo);
            }

            // Processar datas com validação
            try {
                // Usando Carbon como alternativa
                $data_inicial_formatted = \Carbon\Carbon::parse($data_inicial)->format('Y-m-d');
                $data_final_formatted = \Carbon\Carbon::parse($data_final)->format('Y-m-d');

                Log::info('Datas processadas:', [
                    'data_inicial_original' => $data_inicial,
                    'data_final_original' => $data_final,
                    'data_inicial_formatted' => $data_inicial_formatted,
                    'data_final_formatted' => $data_final_formatted
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
                'P_data_inicial' => $data_inicial_formatted,
                'P_data_final' => $data_final_formatted,
                'P_in_veiculo' => $in_veiculo,
                'P_id_veiculo' => $id_veiculo
            );

            Log::info('Parâmetros do relatório: ', $parametros);

            // Definir parâmetros do relatório
            $name = 'abastecimentos_terceiro';
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
                'unitop',           // Usuário com acesso ao relatório (jasperadmin)
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

                // Verificar se é erro de conexão, credenciais ou relatório
                $errorMessage = $e->getMessage();
                if (strpos($errorMessage, 'Connection') !== false || strpos($errorMessage, 'connect') !== false) {
                    $userMessage = 'Erro de conexão com o servidor de relatórios.';
                } elseif (
                    strpos($errorMessage, 'Authentication') !== false ||
                    strpos($errorMessage, 'Login') !== false ||
                    strpos($errorMessage, 'jasperadmin') !== false
                ) {
                    $userMessage = 'Erro de autenticação no servidor de relatórios.';
                } elseif (strpos($errorMessage, 'Report') !== false || strpos($errorMessage, 'abastecimentos_terceiro') !== false) {
                    $userMessage = 'Relatório "abastecimentos_terceiro" não encontrado no servidor.';
                } elseif (strpos($errorMessage, 'errorCode') !== false) {
                    // Tratar erro no formato original: "Erro - $e->errorCode: $e->errorMessage"
                    $userMessage = 'Erro do JasperServer: ' . $errorMessage;
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
            Log::error('Erro geral na função onGeneratePdf: ' . $e->getMessage());
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
        Log::info('=== DEBUG COMPLETO - ABASTECIMENTOS TERCEIRO ===');
        Log::info('Método HTTP: ' . $request->method());
        Log::info('Content-Type: ' . $request->header('Content-Type'));
        Log::info('Todos os inputs: ', $request->all());
        Log::info('Body bruto: ' . $request->getContent());

        try {
            // Verificar se o request é JSON
            if ($request->isJson()) {
                $data = $request->json()->all();
                $param = $data; // Compatibilidade com o código original
                Log::info('Dados JSON recebidos: ', $data);
            } else {
                $data = $request->all();
                $param = $data; // Compatibilidade com o código original
                Log::info('Dados form recebidos: ', $data);
            }

            // Capturar os dados de entrada com múltiplas formas de nomenclatura
            $data_inicial = $request->input('data_inicial') ??
                $request->input('data_inclusao');

            $data_final = $request->input('data_final') ??
                $request->input('data_final_abastecimento');

            $placa_input = $request->input('placa') ??
                $request->input('id_veiculo');

            // Processar placa - pode ser array ou string
            $placa = empty($placa_input) ? "0" : $placa_input;

            Log::info('Dados processados:', [
                'data_inicial' => $data_inicial,
                'data_final' => $data_final,
                'placa_input' => $placa_input,
                'placa_processada' => $placa
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

            // Processar lógica de veículos
            if (empty($placa_input)) {
                $in_veiculo = '!=';
                $id_veiculo = '0';
                Log::info('Todos os veículos serão incluídos');
            } else {
                $in_veiculo = 'IN';
                // Se placa for array, usar implode; se for string, usar como está
                if (is_array($placa)) {
                    $id_veiculo = implode(",", array_filter($placa));
                } else {
                    $id_veiculo = $placa;
                }
                Log::info('Veículos específicos selecionados: ' . $id_veiculo);
            }

            // Processar datas com validação
            try {
                // Usando Carbon como alternativa
                $data_inicial_formatted = \Carbon\Carbon::parse($data_inicial)->format('Y-m-d');
                $data_final_formatted = \Carbon\Carbon::parse($data_final)->format('Y-m-d');

                Log::info('Datas processadas:', [
                    'data_inicial_original' => $data_inicial,
                    'data_final_original' => $data_final,
                    'data_inicial_formatted' => $data_inicial_formatted,
                    'data_final_formatted' => $data_final_formatted
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
                'P_data_inicial' => $data_inicial_formatted,
                'P_data_final' => $data_final_formatted,
                'P_in_veiculo' => $in_veiculo,
                'P_id_veiculo' => $id_veiculo
            );

            Log::info('Parâmetros do relatório: ', $parametros);

            // Definir parâmetros do relatório
            $name = 'abastecimentos_terceiro_v2';
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
                'xls',              // Tipo da exportação do relatório
                'unitop',           // Usuário com acesso ao relatório (jasperadmin)
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

                // Verificar se é erro de conexão, credenciais ou relatório
                $errorMessage = $e->getMessage();
                if (strpos($errorMessage, 'Connection') !== false || strpos($errorMessage, 'connect') !== false) {
                    $userMessage = 'Erro de conexão com o servidor de relatórios.';
                } elseif (
                    strpos($errorMessage, 'Authentication') !== false ||
                    strpos($errorMessage, 'Login') !== false ||
                    strpos($errorMessage, 'jasperadmin') !== false
                ) {
                    $userMessage = 'Erro de autenticação no servidor de relatórios.';
                } elseif (strpos($errorMessage, 'Report') !== false || strpos($errorMessage, 'abastecimentos_terceiro') !== false) {
                    $userMessage = 'Relatório "abastecimentos_terceiro" não encontrado no servidor.';
                } elseif (strpos($errorMessage, 'errorCode') !== false) {
                    // Tratar erro no formato original: "Erro - $e->errorCode: $e->errorMessage"
                    $userMessage = 'Erro do JasperServer: ' . $errorMessage;
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
            Log::error('Erro geral na função onGeneratePdf: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'title' => 'Erro!',
                'type' => 'error',
                'message' => 'Erro inesperado: ' . $e->getMessage()
            ], 500);
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
}

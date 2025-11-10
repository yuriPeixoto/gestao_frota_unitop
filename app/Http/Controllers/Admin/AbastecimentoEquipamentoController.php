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

class AbastecimentoEquipamentoController extends Controller
{
    public function index(Request $request)
    {

        $abastecimentos = AbastecimentoManual::query();;

        $filiais = $this->getFiliais();
        $departamentos = $this->getDepartamentos();
        $tiposEquipamento = $this->getTiposEquipamento();

        return view('admin.abastecimentoequipamento.index', compact(
            'filiais',
            'departamentos',
            'tiposEquipamento',
        ));
    }

    public function onImprimir(Request $request)
    {
        Log::info('=== DEBUG COMPLETO - CONTROLE ABASTECIMENTO ===');
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

            // Capturar os dados de entrada com compatibilidade
            $datainicial = $request->input('datainicial') ??
                $request->input('data_inicial') ??
                $request->input('data_inclusao');

            $datafinal = $request->input('datafinal') ??
                $request->input('data_final') ??
                $request->input('data_final_abastecimento');

            // Capturar arrays de filtros
            $filial = $request->input('filial') ??
                $request->input('id_filial');

            $departamento = $request->input('departamento') ??
                $request->input('id_departamento');

            $equipamento = $request->input('tipo_equipamento') ??
                $request->input('id_tipo_equipamento');

            Log::info('Dados capturados:', [
                'datainicial' => $datainicial,
                'datafinal' => $datafinal,
                'filial' => $filial,
                'departamento' => $departamento,
                'equipamento' => $equipamento
            ]);

            // Verificar se as datas foram informadas
            if (empty($datainicial) || empty($datafinal)) {
                Log::warning('Datas não informadas', [
                    'datainicial' => $datainicial,
                    'datafinal' => $datafinal
                ]);

                return response()->json([
                    'success' => false,
                    'title' => 'Atenção!',
                    'type' => 'info',
                    'message' => 'Informe a data inicial e final para emissão do relatório.'
                ], 400);
            }

            // Processar filtros - garantir que sejam arrays
            $campos_array = ['filial', 'departamento', 'equipamento'];
            foreach ($campos_array as $campo) {
                $valor = $$campo;
                if (!is_array($valor)) {
                    if (!empty($valor) && $valor !== '0') {
                        $$campo = [$valor];
                    } else {
                        $$campo = [];
                    }
                }
                // Filtrar valores vazios e zeros
                $$campo = array_filter($$campo, function ($item) {
                    return !empty($item) && $item !== '0';
                });
            }

            Log::info('Arrays processados:', [
                'filial' => $filial,
                'departamento' => $departamento,
                'equipamento' => $equipamento
            ]);

            // Processar filtros condicionais
            if (empty($filial)) {
                $in_filial = '!=';
                $id_filial = '0';
            } else {
                $in_filial = 'IN';
                $id_filial = implode(",", $filial);
            }

            if (empty($departamento)) {
                $in_departamento = '!=';
                $id_departamento = '0';
            } else {
                $in_departamento = 'IN';
                $id_departamento = implode(",", $departamento);
            }

            if (empty($equipamento)) {
                $in_equipamento = '!=';
                $id_equipamento = '0';
            } else {
                $in_equipamento = 'IN';
                $id_equipamento = implode(",", $equipamento);
            }

            Log::info('Filtros processados:', [
                'in_filial' => $in_filial,
                'id_filial' => $id_filial,
                'in_departamento' => $in_departamento,
                'id_departamento' => $id_departamento,
                'in_equipamento' => $in_equipamento,
                'id_equipamento' => $id_equipamento
            ]);

            // Processar datas com validação
            try {
                // Verificar se é formato brasileiro (dd/mm/yyyy) ou internacional (yyyy-mm-dd)
                if (strpos($datainicial, '/') !== false) {
                    // Formato brasileiro dd/mm/yyyy
                    $datainicial = \Carbon\Carbon::createFromFormat('d/m/Y', $datainicial)->format('Y-m-d');
                } else {
                    // Formato internacional ou já processado
                    $datainicial = \Carbon\Carbon::parse($datainicial)->format('Y-m-d');
                }

                if (strpos($datafinal, '/') !== false) {
                    // Formato brasileiro dd/mm/yyyy
                    $datafinal = \Carbon\Carbon::createFromFormat('d/m/Y', $datafinal)->format('Y-m-d');
                } else {
                    // Formato internacional ou já processado
                    $datafinal = \Carbon\Carbon::parse($datafinal)->format('Y-m-d');
                }

                Log::info('Datas processadas:', [
                    'datainicial' => $datainicial,
                    'datafinal' => $datafinal
                ]);
            } catch (\Exception $e) {
                Log::error('Erro ao processar datas: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Formato de data inválido. Use dd/mm/yyyy ou yyyy-mm-dd.'
                ], 400);
            }

            // Montar parâmetros do relatório
            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_filial' => $in_filial,
                'P_id_filial' => $id_filial,
                'P_in_equipamento' => $in_equipamento,
                'P_id_equipamento' => $id_equipamento,
                'P_in_departamento' => $in_departamento,
                'P_id_departamento' => $id_departamento
            );

            Log::info('Parâmetros do relatório: ', $parametros);

            // Definir parâmetros do relatório
            $name = 'controle_abastecimento';
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
        Log::info('=== DEBUG COMPLETO - CONTROLE ABASTECIMENTO ===');
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

            // Capturar os dados de entrada com compatibilidade
            $datainicial = $request->input('datainicial') ??
                $request->input('data_inicial') ??
                $request->input('data_inclusao');

            $datafinal = $request->input('datafinal') ??
                $request->input('data_final') ??
                $request->input('data_final_abastecimento');

            // Capturar arrays de filtros
            $filial = $request->input('filial') ??
                $request->input('id_filial');

            $departamento = $request->input('departamento') ??
                $request->input('id_departamento');

            $equipamento = $request->input('tipo_equipamento') ??
                $request->input('id_tipo_equipamento');

            Log::info('Dados capturados:', [
                'datainicial' => $datainicial,
                'datafinal' => $datafinal,
                'filial' => $filial,
                'departamento' => $departamento,
                'equipamento' => $equipamento
            ]);

            // Verificar se as datas foram informadas
            if (empty($datainicial) || empty($datafinal)) {
                Log::warning('Datas não informadas', [
                    'datainicial' => $datainicial,
                    'datafinal' => $datafinal
                ]);

                return response()->json([
                    'success' => false,
                    'title' => 'Atenção!',
                    'type' => 'info',
                    'message' => 'Informe a data inicial e final para emissão do relatório.'
                ], 400);
            }

            // Processar filtros - garantir que sejam arrays
            $campos_array = ['filial', 'departamento', 'equipamento'];
            foreach ($campos_array as $campo) {
                $valor = $$campo;
                if (!is_array($valor)) {
                    if (!empty($valor) && $valor !== '0') {
                        $$campo = [$valor];
                    } else {
                        $$campo = [];
                    }
                }
                // Filtrar valores vazios e zeros
                $$campo = array_filter($$campo, function ($item) {
                    return !empty($item) && $item !== '0';
                });
            }

            Log::info('Arrays processados:', [
                'filial' => $filial,
                'departamento' => $departamento,
                'equipamento' => $equipamento
            ]);

            // Processar filtros condicionais
            if (empty($filial)) {
                $in_filial = '!=';
                $id_filial = '0';
            } else {
                $in_filial = 'IN';
                $id_filial = implode(",", $filial);
            }

            if (empty($departamento)) {
                $in_departamento = '!=';
                $id_departamento = '0';
            } else {
                $in_departamento = 'IN';
                $id_departamento = implode(",", $departamento);
            }

            if (empty($equipamento)) {
                $in_equipamento = '!=';
                $id_equipamento = '0';
            } else {
                $in_equipamento = 'IN';
                $id_equipamento = implode(",", $equipamento);
            }

            Log::info('Filtros processados:', [
                'in_filial' => $in_filial,
                'id_filial' => $id_filial,
                'in_departamento' => $in_departamento,
                'id_departamento' => $id_departamento,
                'in_equipamento' => $in_equipamento,
                'id_equipamento' => $id_equipamento
            ]);

            // Processar datas com validação
            try {
                // Verificar se é formato brasileiro (dd/mm/yyyy) ou internacional (yyyy-mm-dd)
                if (strpos($datainicial, '/') !== false) {
                    // Formato brasileiro dd/mm/yyyy
                    $datainicial = \Carbon\Carbon::createFromFormat('d/m/Y', $datainicial)->format('Y-m-d');
                } else {
                    // Formato internacional ou já processado
                    $datainicial = \Carbon\Carbon::parse($datainicial)->format('Y-m-d');
                }

                if (strpos($datafinal, '/') !== false) {
                    // Formato brasileiro dd/mm/yyyy
                    $datafinal = \Carbon\Carbon::createFromFormat('d/m/Y', $datafinal)->format('Y-m-d');
                } else {
                    // Formato internacional ou já processado
                    $datafinal = \Carbon\Carbon::parse($datafinal)->format('Y-m-d');
                }

                Log::info('Datas processadas:', [
                    'datainicial' => $datainicial,
                    'datafinal' => $datafinal
                ]);
            } catch (\Exception $e) {
                Log::error('Erro ao processar datas: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Formato de data inválido. Use dd/mm/yyyy ou yyyy-mm-dd.'
                ], 400);
            }

            // Montar parâmetros do relatório
            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_filial' => $in_filial,
                'P_id_filial' => $id_filial,
                'P_in_equipamento' => $in_equipamento,
                'P_id_equipamento' => $id_equipamento,
                'P_in_departamento' => $in_departamento,
                'P_id_departamento' => $id_departamento
            );

            Log::info('Parâmetros do relatório: ', $parametros);

            // Definir parâmetros do relatório
            $name = 'controle_abastecimento_v2';
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


    public function getFiliais()
    {
        return Filial::select('id as value', 'name as label')
            ->orderBy('name', 'desc')
            ->get()
            ->toArray();
    }

    public function getDepartamentos()
    {
        return Departamento::select('id_departamento as value', 'descricao_departamento as label')
            ->orderBy('descricao_departamento', 'desc')
            ->get()
            ->toArray();
    }

    public function getTiposEquipamento()
    {
        return TipoEquipamento::select('id_tipo_equipamento as value', 'descricao_tipo as label')
            ->orderBy('descricao_tipo', 'desc')
            ->get()
            ->toArray();
    }
}

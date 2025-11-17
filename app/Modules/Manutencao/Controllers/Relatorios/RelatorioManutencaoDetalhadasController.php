<?php

namespace App\Modules\Manutencao\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioManutencaoDetalhadasController extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = OrdemServico::with([
            'filial',
            'departamento',
            'veiculo',
            'manutencao',
            'tipoOrdemServico',
        ]);

        // Filtros do formulário
        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }

        if ($request->filled('id_departamento')) {
            $query->where('id_departamento', $request->input('id_departamento'));
        }

        if ($request->filled('id_manutencao')) {
            $query->where('id_manutencao', $request->input('id_manutencao'));
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->input('id_veiculo'));
        }

        if ($request->filled('id_tipo_ordem_servico')) {
            $query->where('id_tipo_ordem_servico', $request->input('id_tipo_ordem_servico'));
        }

        if ($request->filled('id_ordem_servico')) {
            $query->where('id_ordem_servico', $request->input('id_ordem_servico'));
        }

        // Aqui você pode usar paginação se desejar
        // $ordens = $query->paginate(20);
        // ou
        // $ordens = $query->get();

        // Popula os filtros do formulário
        $filiais = \App\Models\VFilial::select('id as value', 'name as label')->orderBy('name')->get();
        $departamentos = \App\Models\Departamento::select('id_departamento as value', 'descricao_departamento as label')->orderBy('descricao_departamento')->get();
        $veiculos = \App\Models\Veiculo::select('id_veiculo as value', 'placa as label')->orderBy('placa')->limit(30)->get();
        $categorias = \App\Models\CategoriaVeiculo::select('id_categoria as value', 'descricao_categoria as label')->orderBy('descricao_categoria')->get();
        $manutencoes = \App\Models\Manutencao::select('id_manutencao as value', 'descricao_manutencao as label')->orderBy('descricao_manutencao')->get();
        $tiposOS = \App\Models\TipoOrdemServico::select('id_tipo_ordem_servico as value', 'descricao_tipo_ordem as label')->orderBy('descricao_tipo_ordem')->get();
        $ordensServico = \App\Models\OrdemServico::select('id_ordem_servico as value', 'id_ordem_servico as label')->orderBy('id_ordem_servico', 'desc')->limit(50)->get();

        return view('admin.relatoriomanutencaodetalhada.index', [
            'nf' => $ordensServico,
            'filiais' => $filiais,
            'departamentos' => $departamentos,
            'veiculos' => $veiculos,
            'categorias' => $categorias,
            'manutencoes' => $manutencoes,
            'tiposOS' => $tiposOS,
            'ordemServico' => $request->input('id_ordem_servico'),
        ]);
    }

    public function gerarPdf(Request $request)
    {
        $mp = $request['id_manutencao'];
        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

            if (empty($request['id_filial'])) {
                $id_filial_inicial  = 0;
                $id_filial_final    = 999999;
            } else {
                $id_filial_inicial  = $request['id_filial'];
                $id_filial_final    = $request['id_filial'];
            }

            if (empty($request['id_departamento'])) {
                $id_departamento_inicial    = 0;
                $id_departamento_final      = 999999;
            } else {
                $id_departamento_inicial    = $request['id_departamento'];
                $id_departamento_final      = $request['id_departamento'];
            }

            if (empty($request['id_veiculo']) || $request['id_veiculo'] == NULL) {
                $placa_inicial = 0;
                $placa_final   = 999999;
            } else {
                $placa_inicial  = $request['id_veiculo'];
                $placa_final    = $request['id_veiculo'];
            }

            if (empty($request['id_categoria'])) {
                $id_categoria_inicial = 0;
                $id_categoria_final   = 999999;
            } else {
                $id_categoria_inicial = $request['id_categoria'];
                $id_categoria_final   = $request['id_categoria'];
            }

            if (empty($request['id_manutencao'])) {
                $mp_inicial = '!=';
                $mp_final   = '0';
            } else {

                $mp_inicial  = 'IN';
                $mp_final  = implode(",", $mp);
            }

            if (empty($request['id_tipo_ordem_servico']) || $request['id_tipo_ordem_servico'] == null) {
                $tipo_os_inicial    = 0;
                $tipo_os_final      = 999999;
            } else {
                $tipo_os_inicial    = $request['id_tipo_ordem_servico'];
                $tipo_os_final      = $request['id_tipo_ordem_servico'];
            }

            if (empty($request['id_ordem_servico'])) {
                $p_id_os_inicial    = 0;
                $p_id_os_final      = 99999;
            } else {
                $p_id_os_inicial  = $request['id_ordem_servico'];
                $p_id_os_final    = $request['id_ordem_servico'];
            }

            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_id_filial_inicial' => $id_filial_inicial,
                'P_id_filial_final' => $id_filial_final,
                'P_id_departamento_inicial' => $id_departamento_inicial,
                'P_id_departamento_final' => $id_departamento_final,
                'P_id_veiculo_inicial' => $placa_inicial,
                'P_id_veiculo_final' => $placa_final,
                'P_id_categoria_inicial' => $id_categoria_inicial,
                'P_id_categoria_final' => $id_categoria_final,
                'P_id_tipo_inicial' => $tipo_os_inicial,
                'P_id_tipo_final' => $tipo_os_final,
                'P_id_os_inicial' => $p_id_os_inicial,
                'P_id_os_final' => $p_id_os_final,
                'P_in_mp' => $mp_inicial,
                'P_id_mp' => $mp_final
            );
            //== define pararemetros relatorios  
            $name = 'manutencoes_detalhadas_v3';
            $agora = now()->format('d-m-Y_H-i');
            $relatorio = "{$name}_{$agora}.pdf";

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

            Log::info("Gerando PDF: {$relatorio}");
            Log::info("Servidor: {$jasperserver}, Caminho: {$pastarelatorio}");
            Log::info("Parâmetros:", $parametros);

            try {
                $jsi = new TraitsJasperServerIntegration(
                    $jasperserver,
                    $pastarelatorio,
                    'pdf',
                    'unitop',
                    'unitop2022',
                    $parametros
                );

                $data = $jsi->execute();

                // Verifica se retorno está vazio ou muito pequeno
                if (empty($data) || strlen($data) < 100) {
                    Log::error("Relatório PDF gerado vazio ou muito pequeno: tamanho " . strlen($data));
                    return response()->json(['message' => 'O relatório retornou vazio ou inválido.'], 500);
                }

                // Salva local para debug (opcional)
                file_put_contents(storage_path("app/public/{$relatorio}"), $data);

                return response($data, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => "attachment; filename=\"{$relatorio}\"",
                ]);
            } catch (\Exception $e) {
                Log::error("Erro ao gerar PDF: " . $e->getMessage());
                return response()->json(['message' => 'Erro ao gerar o relatório PDF.'], 500);
            }
        }

        return response()->json(['message' => 'Informe a data inicial e final para emissão do relatório.'], 400);
    }

    public function gerarExcel(Request $request)
    {
        $mp = $request['id_manutencao'];
        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {
            if (empty($request['id_filial'])) {
                $id_filial_inicial  = 0;
                $id_filial_final    = 999999;
            } else {
                $id_filial_inicial  = $request['id_filial'];
                $id_filial_final    = $request['id_filial'];
            }

            if (empty($request['id_departamento'])) {
                $id_departamento_inicial    = 0;
                $id_departamento_final      = 999999;
            } else {
                $id_departamento_inicial    = $request['id_departamento'];
                $id_departamento_final      = $request['id_departamento'];
            }

            if (empty($request['id_veiculo']) || $request['id_veiculo'] == NULL) {
                $placa_inicial = 0;
                $placa_final   = 999999;
            } else {
                $placa_inicial  = $request['id_veiculo'];
                $placa_final    = $request['id_veiculo'];
            }

            if (empty($request['id_categoria'])) {
                $id_categoria_inicial = 0;
                $id_categoria_final   = 999999;
            } else {
                $id_categoria_inicial = $request['id_categoria'];
                $id_categoria_final   = $request['id_categoria'];
            }

            if (empty($request['id_manutencao'])) {
                $mp_inicial = '!=';
                $mp_final   = '0';
            } else {

                $mp_inicial  = 'IN';
                $mp_final  = implode(",", $mp);
            }

            if (empty($request['id_tipo_ordem_servico']) || $request['id_tipo_ordem_servico'] == null) {
                $tipo_os_inicial    = 0;
                $tipo_os_final      = 999999;
            } else {
                $tipo_os_inicial    = $request['id_tipo_ordem_servico'];
                $tipo_os_final      = $request['id_tipo_ordem_servico'];
            }

            if (empty($request['id_ordem_servico'])) {
                $p_id_os_inicial    = 0;
                $p_id_os_final      = 99999;
            } else {
                $p_id_os_inicial  = $request['id_ordem_servico'];
                $p_id_os_final    = $request['id_ordem_servico'];
            }

            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_id_filial_inicial' => $id_filial_inicial,
                'P_id_filial_final' => $id_filial_final,
                'P_id_departamento_inicial' => $id_departamento_inicial,
                'P_id_departamento_final' => $id_departamento_final,
                'P_id_veiculo_inicial' => $placa_inicial,
                'P_id_veiculo_final' => $placa_final,
                'P_id_categoria_inicial' => $id_categoria_inicial,
                'P_id_categoria_final' => $id_categoria_final,
                'P_id_tipo_inicial' => $tipo_os_inicial,
                'P_id_tipo_final' => $tipo_os_final,
                'P_id_os_inicial' => $p_id_os_inicial,
                'P_id_os_final' => $p_id_os_final,
                'P_in_mp' => $mp_inicial,
                'P_id_mp' => $mp_final
            );
            //== define pararemetros relatorios  
            $name       = 'manutencoes_detalhadas_v4';
            $agora = now()->format('d-m-Y_H-i');
            $relatorio = "{$name}_{$agora}.xls";

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

            $jsi = new TraitsJasperServerIntegration(
                $jasperserver,
                $pastarelatorio,
                'xls',
                'jasperadmin',
                'unitop2022',
                $parametros
            );

            try {
                $data = $jsi->execute();

                return response($data)
                    ->header('Content-Type', 'application/vnd.ms-excel')
                    ->header('Content-Disposition', "attachment; filename={$relatorio}");
            } catch (\Exception $e) {
                return response()->json(['message' => 'Erro ao gerar relatório: ' . $e->getMessage()], 500);
            }
        } else {
            return response()->json(['message' => 'Informe a data inicial e final para emissão do relatório.'], 400);
        }
        //</autoCode>
    } //</end>
}

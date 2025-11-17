<?php

namespace App\Modules\Compras\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Manutencao\Models\ServicoFornecedor;
use App\Models\Fornecedor;
use App\Modules\Manutencao\Models\Servico;
use App\Helpers\TDate;
use App\Services\jasperserverintegration;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;
use Illuminate\Support\Facades\Log; // Se quiser logar exceções

class FornecedorComissionadosRelController extends Controller
{
    public function index(Request $request)
    {
        $dataInicial = $request->input('data_inclusao');
        $dataFinal = $request->input('data_final');
        $fornecedorId = $request->input('nome_fornecedor');
        $servicoId = $request->input('id_servico');

        $query = ServicoFornecedor::with(['fornecedor', 'servico'])->validos();

        if ($dataInicial && $dataFinal) {
            $query->whereBetween('data_inclusao', [$dataInicial, $dataFinal]);
        }

        if ($fornecedorId) {
            $query->where('id_fornecedor', $fornecedorId);
        }

        if ($servicoId) {
            $query->where('id_servico', $servicoId);
        }

        $resultados = $query->get();

        $fornecedores = Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
            ->orderBy('nome_fornecedor')
            ->limit(30)
            ->get();

        $servicos = Servico::select('id_servico as value', 'descricao_servico as label')
            ->orderBy('descricao_servico')
            ->limit(30)
            ->get();

        return view('admin.fornecedorcomissionadorelatorio.index', [
            'resultados' => $resultados,
            'fornecedores' => $fornecedores,
            'servicos' => $servicos,
        ]);
    }



    public function gerarPdf(Request $request)
    {
        // Permite tempo extra para execução do relatório
        set_time_limit(300);

        if ($request->filled(['data_inclusao', 'data_final'])) {

            $param = [
                'data_inclusao' => $request->data_inclusao,
                'data_final' => $request->data_final,
                'id_fornecedor' => $request->id_fornecedor,
                'descricao_servico' => $request->descricao_servico,
            ];

            // Ajuste para valores default
            $id_fornecedor_inicial = !empty($param['nome_fornecedor']) ? $param['nome_fornecedor'] : 0;
            $id_fornecedor_final = !empty($param['nome_fornecedor']) ? $param['nome_fornecedor'] : 999999;

            $id_servicos_inicial = !empty($param['descricao_servico']) ? $param['descricao_servico'] : 0;
            $id_servicos_final = !empty($param['descricao_servico']) ? $param['descricao_servico'] : 999999;

            // Converte data do formato dd/mm/yyyy para yyyy-mm-dd
            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

            $parametros = [
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_id_fornecedor_inicial' => $id_fornecedor_inicial,
                'P_id_fornecedor_final' => $id_fornecedor_final,
                'P_id_servicos_inicial' => $id_servicos_inicial,
                'P_id_servicos_final' => $id_servicos_final,
            ];

            ////////////////////////////////////////////////////////////////
            $name = 'relatorio_comissao_fornecedores';
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
        $param = $request->all();

        if (!empty($param['data_inclusao']) && !empty($param['data_final'])) {
            $id_fornecedor_inicial = $param['id_fornecedor'] ?? 0;
            $id_fornecedor_final   = $param['id_fornecedor'] ?? 999999;

            $id_servicos_inicial   = $param['descricao_servico'] ?? 0;
            $id_servicos_final     = $param['descricao_servico'] ?? 999999;

            // Converte data do formato dd/mm/yyyy para yyyy-mm-dd
            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');


            $parametros = [
                'P_data_inicial'         => $datainicial,
                'P_data_final'           => $datafinal,
                'P_id_fornecedor_inicial' => $id_fornecedor_inicial,
                'P_id_fornecedor_final'  => $id_fornecedor_final,
                'P_id_servicos_inicial'  => $id_servicos_inicial,
                'P_id_servicos_final'    => $id_servicos_final,
            ];

            $name = 'relatorio_comissao_fornecedores_2';
            $agora = date('d-m-Y_H-i');
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
            return response()->json(['message' => 'Informe a data inicial e final para emissão do relatório.'], 422);
        }
    }
}

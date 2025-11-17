<?php

namespace App\Modules\RelatoriosGerenciais\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Modules\Compras\Models\Fornecedor;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioFornecedorSemNF extends Controller
{
    public function index(Request $request)
    {
        $query = Veiculo::query();

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->input('id_fornecedor'));
        }

        if ($request->filled('id_ordem_servico')) {
            $query->whereHas('ordemServico', function ($q) use ($request) {
                $q->where('id_ordem_servico', $request->input('id_ordem_servico'));
            });
        }

        $fornecedor = Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')->orderBy('nome_fornecedor')->limit(30)->get();

        return view('admin.relatoriofornecedorsemnf.index', compact('fornecedor'));
    }

    public function gerarPdf(Request $request)
    {
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->post());

        $filial       = empty($request['id_filial']) ? "0" : $request['id_filial'];
        $tipo         = empty($request['id_tipo_equipamento']) ? "0" : $request['id_tipo_equipamento'];

        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

            if (empty($request['id_fornecedor'])) {
                $id_fornecedor_inicial = 0;
                $id_fornecedor_final  = 99999999;
            } else {
                $id_fornecedor_inicial = $request['id_fornecedor'];
                $id_fornecedor_final  = $request['id_fornecedor'];
            }
            if (empty($request['id_ordem_servico'])) {
                $id_ordem_inicial = 0;
                $id_ordem_final   = 99999999;
            } else {
                $id_ordem_inicial = $request['id_ordem_servico'];
                $id_ordem_final   = $request['id_ordem_servico'];
            }


            $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
            $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_id_fornecedor_inicial' => $id_fornecedor_inicial,
                'P_id_fornecedor_final' => $id_fornecedor_final,
                'P_id_ordem_inicial' => $id_ordem_inicial,
                'P_id_ordem_final' => $id_ordem_final
            );


            $name = 'fornecedor_sem_nf';
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

            $jsi = new TraitsJasperServerIntegration(
                $jasperserver,
                $pastarelatorio,
                'pdf',
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
        }
    }

    public function gerarExcel(Request $request)
    {
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->post());

        $filial       = empty($request['id_filial']) ? "0" : $request['id_filial'];
        $tipo         = empty($request['id_tipo_equipamento']) ? "0" : $request['id_tipo_equipamento'];

        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

            if (empty($request['id_fornecedor'])) {
                $id_fornecedor_inicial = 0;
                $id_fornecedor_final  = 99999999;
            } else {
                $id_fornecedor_inicial = $request['id_fornecedor'];
                $id_fornecedor_final  = $request['id_fornecedor'];
            }
            if (empty($request['id_ordem_servico'])) {
                $id_ordem_inicial = 0;
                $id_ordem_final   = 99999999;
            } else {
                $id_ordem_inicial = $request['id_ordem_servico'];
                $id_ordem_final   = $request['id_ordem_servico'];
            }


            $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
            $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_id_fornecedor_inicial' => $id_fornecedor_inicial,
                'P_id_fornecedor_final' => $id_fornecedor_final,
                'P_id_ordem_inicial' => $id_ordem_inicial,
                'P_id_ordem_final' => $id_ordem_final
            );


            $name = 'fornecedor_sem_nf_v2';
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
        }
    }
}

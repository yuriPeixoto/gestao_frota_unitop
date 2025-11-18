<?php

namespace App\Modules\Manutencao\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Departamento;
use App\Modules\Configuracoes\Models\Filial;
use App\Modules\Compras\Models\Fornecedor;
use App\Models\NfOrdemServico;
use App\Models\OrdemServico;
use App\Modules\Abastecimentos\Models\Tanque;
use App\Modules\Veiculos\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioNfsManutencaoRealizadas extends Controller
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

        if ($request->filled('id_nf_ordem')) {
            $query->whereHas('ordemServico', function ($q) use ($request) {
                $q->where('id_ordem_servico', $request->input('id_nf_ordem'));
            });
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->input('id_fornecedor'));
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }


        $fornecedor = Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')->orderBy('nome_fornecedor')->limit(30)->get();
        $filial = Filial::select('id as value', 'name as label')->orderBy('name')->limit(30)->get();
        $nfs    =   NfOrdemServico::select('id_nf_ordem as value', 'numero_nf as label')->orderBy('numero_nf')->limit(30)->get();
        $departamento = Departamento::select('id_departamento as value', 'descricao_departamento as label')->orderBy('descricao_departamento')->limit(30)->get();


        return view('admin.relatorionfsmanutencaorealizadas.index', compact('fornecedor', 'filial', 'nfs', 'departamento'));
    }

    public function gerarPdf(Request $request)
    {
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->post());

        $filial       = empty($request['id_filial']) ? "0" : $request['id_filial'];
        $departamento       = empty($request['id_departamento']) ? "0" : $request['id_departamento'];
        $nf         = empty($request['id_nf_ordem']) ? "0" : $request['id_nf_ordem'];
        $fornecedor         = empty($request['id_fornecedor']) ? "0" : $request['id_fornecedor'];

        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

            if (empty($request['id_departamento'])) {
                $in_departamento = '!=';
                $id_departamento = '0';
            } else {
                $in_departamento = 'IN';
                $id_departamento = $request['id_departamento'];
            }
            if (empty($request['id_filial'])) {
                $in_filial = '!=';
                $id_filial = '0';
            } else {
                $in_filial = 'IN';
                $id_filial = $request['id_filial'];
            }
            if (empty($request['id_nf_ordem'])) {
                $in_nf = '!=';
                $id_nf = '0';
            } else {
                $in_nf = 'IN';
                $id_nf = $request['id_nf_ordem'];
            }
            if (empty($request['id_fornecedor'])) {
                $id_fornecedor_inicial = 0;
                $id_fornecedor_final   = 99999999;
            } else {
                $id_fornecedor_inicial = $request['id_fornecedor'];
                $id_fornecedor_final   = $request['id_fornecedor'];
            }


            $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
            $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_fornecedor' => $id_fornecedor_inicial,
                'P_id_fornecedor' => $id_fornecedor_final,
                'P_in_nf' => $in_nf,
                'P_id_nf' => $id_nf,
                'P_in_filial' => $in_filial,
                'P_id_filial' => $id_filial,
                'P_in_departamento' => $in_departamento,
                'P_id_departamento' => $id_departamento
            );



            $name = 'manutencoesrealizadas_nf';
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
        $departamento       = empty($request['id_departamento']) ? "0" : $request['id_departamento'];
        $nf         = empty($request['id_nf_ordem']) ? "0" : $request['id_nf_ordem'];
        $fornecedor         = empty($request['id_fornecedor']) ? "0" : $request['id_fornecedor'];

        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

            if (empty($request['id_departamento'])) {
                $in_departamento = '!=';
                $id_departamento = '0';
            } else {
                $in_departamento = 'IN';
                $id_departamento = $request['id_departamento'];
            }
            if (empty($request['id_filial'])) {
                $in_filial = '!=';
                $id_filial = '0';
            } else {
                $in_filial = 'IN';
                $id_filial = $request['id_filial'];
            }
            if (empty($request['id_nf_ordem'])) {
                $in_nf = '!=';
                $id_nf = '0';
            } else {
                $in_nf = 'IN';
                $id_nf = $request['id_nf_ordem'];
            }
            if (empty($request['id_fornecedor'])) {
                $id_fornecedor_inicial = 0;
                $id_fornecedor_final   = 99999999;
            } else {
                $id_fornecedor_inicial = $request['id_fornecedor'];
                $id_fornecedor_final   = $request['id_fornecedor'];
            }


            $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
            $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_fornecedor' => $id_fornecedor_inicial,
                'P_id_fornecedor' => $id_fornecedor_final,
                'P_in_nf' => $in_nf,
                'P_id_nf' => $id_nf,
                'P_in_filial' => $in_filial,
                'P_id_filial' => $id_filial,
                'P_in_departamento' => $in_departamento,
                'P_id_departamento' => $id_departamento
            );



            $name = 'manutencoesrealizadas_nf';
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

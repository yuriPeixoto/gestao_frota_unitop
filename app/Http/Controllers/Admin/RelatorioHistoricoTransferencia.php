<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Filial;
use App\Modules\Compras\Models\RelacaoSolicitacaoPeca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioHistoricoTransferencia extends Controller
{
    public function index(Request $request)
    {
        $query = RelacaoSolicitacaoPeca::query();

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }

        if ($request->filled('id_solicitacao_pecas')) {
            $query->where('id_solicitacao_pecas', $request->input('id_solicitacao_pecas'));
        }
        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }


        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();

        $rfilialm = $query->select('id_solicitacao_pecas as value', 'id_solicitacao_pecas as label')
            ->orderBy('id_solicitacao_pecas')
            ->limit(30)
            ->get();
        $rfilial = $query->select('id_solicitacao_pecas as value', 'id_solicitacao_pecas as label')
            ->orderBy('id_solicitacao_pecas')
            ->limit(30)
            ->get();

        return view('admin.relatoriohistoricotransferencia.index', compact('filial', 'rfilialm', 'rfilial'));
    }

    public function gerarPdf(Request $request)
    {
        // Permite até 5 minutos de execução para o Jasper gerar o PDF
        //set_time_limit(300);
        Log::info('Dados brutos recebidos:', $request->all());

        $req_filial     = empty($request['id_solicitacao_pecas']) ? "0" : $request['id_solicitacao_pecas'];
        $req_matriz     = empty($request['id_solicitacao_pecas']) ? "0" : $request['id_solicitacao_pecas'];
        $filial         = empty($request['id_filial']) ? "0" : $request['id_filial'];

        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

            if (empty($request['id_solicitacao_pecas'])) {
                $in_req_matriz  = '!=';
                $id_req_matriz  = '0';
            } else {
                $in_req_matriz  = 'IN';
                $id_req_matriz  = $request['id_solicitacao_pecas'];
            }
            if (empty($request['id_solicitacao_pecas'])) {
                $in_req_filial  = '!=';
                $id_req_filial  = '0';
            } else {
                $in_req_filial  = 'IN';
                $id_req_filial  = $request['id_solicitacao_pecas'];
            }
            if (empty($request['id_filial'])) {
                $in_filial  = '!=';
                $id_filial  = '0';
            } else {
                $in_filial  = 'IN';
                $id_filial  = $request['id_filial'];
            }


            // Formata datas corretamente — de dd/mm/yyyy para yyyy-mm-dd
            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');


            // Log para verificação
            Log::info("Datas convertidas: $datainicial até $datafinal");

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_relacao_novo' => $in_req_matriz,
                'P_id_relacao_novo' => $id_req_matriz,
                'P_in_relacao_antigo' => $in_req_filial,
                'P_id_relacao_antigo' => $id_req_filial,
                'P_in_filial' => $in_filial,
                'P_id_filial' => $id_filial
            );



            $name = 'historico_transferencia_materiais_v1';
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
        // Permite até 5 minutos de execução para o Jasper gerar o PDF
        //set_time_limit(300);
        Log::info('Dados brutos recebidos:', $request->all());

        $req_filial     = empty($request['id_solicitacao_pecas']) ? "0" : $request['id_solicitacao_pecas'];
        $req_matriz     = empty($request['id_solicitacao_pecas']) ? "0" : $request['id_solicitacao_pecas'];
        $filial         = empty($request['id_filial']) ? "0" : $request['id_filial'];

        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

            if (empty($request['id_solicitacao_pecas'])) {
                $in_req_matriz  = '!=';
                $id_req_matriz  = '0';
            } else {
                $in_req_matriz  = 'IN';
                $id_req_matriz  = $request['id_solicitacao_pecas'];
            }
            if (empty($request['id_solicitacao_pecas'])) {
                $in_req_filial  = '!=';
                $id_req_filial  = '0';
            } else {
                $in_req_filial  = 'IN';
                $id_req_filial  = $request['id_solicitacao_pecas'];
            }
            if (empty($request['id_filial'])) {
                $in_filial  = '!=';
                $id_filial  = '0';
            } else {
                $in_filial  = 'IN';
                $id_filial  = $request['id_filial'];
            }


            // Formata datas corretamente — de dd/mm/yyyy para yyyy-mm-dd
            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');


            // Log para verificação
            Log::info("Datas convertidas: $datainicial até $datafinal");

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_relacao_novo' => $in_req_matriz,
                'P_id_relacao_novo' => $id_req_matriz,
                'P_in_relacao_antigo' => $in_req_filial,
                'P_id_relacao_antigo' => $id_req_filial,
                'P_in_filial' => $in_filial,
                'P_id_filial' => $id_filial
            );



            $name = 'historico_transferencia_materiais_v1';
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

            Log::info("Gerando xls: {$relatorio}");
            Log::info("Servidor: {$jasperserver}, Caminho: {$pastarelatorio}");
            Log::info("Parâmetros:", $parametros);

            try {
                $jsi = new TraitsJasperServerIntegration(
                    $jasperserver,
                    $pastarelatorio,
                    'xls',
                    'unitop',
                    'unitop2022',
                    $parametros
                );

                $data = $jsi->execute();

                // Verifica se retorno está vazio ou muito pequeno
                if (empty($data) || strlen($data) < 100) {
                    Log::error("Relatório xls gerado vazio ou muito pequeno: tamanho " . strlen($data));
                    return response()->json(['message' => 'O relatório retornou vazio ou inválido.'], 500);
                }

                // Salva local para debug (opcional)
                file_put_contents(storage_path("app/public/{$relatorio}"), $data);

                return response($data, 200, [
                    'Content-Type' => 'application/xls',
                    'Content-Disposition' => "attachment; filename=\"{$relatorio}\"",
                ]);
            } catch (\Exception $e) {
                Log::error("Erro ao gerar xls: " . $e->getMessage());
                return response()->json(['message' => 'Erro ao gerar o relatório xls.'], 500);
            }
        }

        return response()->json(['message' => 'Informe a data inicial e final para emissão do relatório.'], 400);
    }
}

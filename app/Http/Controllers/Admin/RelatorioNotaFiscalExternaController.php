<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Fornecedor;
use App\Modules\Manutencao\Models\NfOrdemServico;
use App\Modules\Manutencao\Models\OrdemServico;
use Illuminate\Support\Facades\Log; // Se quiser logar exceções
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioNotaFiscalExternaController extends Controller
{
    public function index(Request $request)
    {
        // Coleta dos filtros do formulário
        $dataInicial     = $request->input('data_inclusao');
        $dataFinal       = $request->input('data_final');
        $ordemServicoId  = $request->input('id_tipo_ordem_servico');
        $nfId            = $request->input('id_nf_ordem');
        $fornecedorId    = $request->input('id_fornecedor');

        // Inicia a query com os relacionamentos corretos
        $query = OrdemServico::with(['notasFiscais', 'fornecedor']);

        // Filtro por data de inclusão
        if ($dataInicial && $dataFinal) {
            $query->whereBetween('data_inclusao', [$dataInicial, $dataFinal]);
        }

        // Filtro por número da nota fiscal
        if (!empty($nfId)) {
            $query->whereHas('notasFiscais', function ($q) use ($nfId) {
                $q->where('id_nf_ordem', $nfId);
            });
        }

        // Filtro por número da ordem de serviço
        if (!empty($ordemServicoId)) {
            $query->where('id_ordem_servico', $ordemServicoId);
        }

        // Filtro por fornecedor
        if (!empty($fornecedorId)) {
            $query->where('id_fornecedor', $fornecedorId);
        }

        // Executa a query apenas se necessário, por exemplo para paginação
        // $ordens = $query->get();

        // Popula os selects do formulário
        $nf = NfOrdemServico::select('id_nf_ordem as value', 'id_nf_ordem as label')
            ->orderBy('id_nf_ordem')
            ->limit(30)
            ->get();

        $fornecedor = Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
            ->orderBy('nome_fornecedor')
            ->limit(30)
            ->get();

        //dd($nf);
        return view('admin.relatorionotafiscalexterna.index', [
            'nf'             => $nf,
            'ordemServico'   => $ordemServicoId, // este é só o valor selecionado
            'fornecedor'     => $fornecedor,
            // 'ordens'      => $ordens // descomente se quiser mostrar os dados filtrados na view
        ]);
    }


    public function gerarPdf(Request $request)
    {
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
            if (empty($request['id_nf_ordem'])) {
                $id_nf_inicial = 0;
                $id_nf_final   = 99999999;
            } else {
                $id_nf_inicial = $request['id_nf_ordem'];
                $id_nf_final   = $request['id_nf_ordem'];
            }

            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_id_fornecedor_inicial' => $id_fornecedor_inicial,
                'P_id_fornecedor_final' => $id_fornecedor_final,
                'P_id_ordem_inicial' => $id_ordem_inicial,
                'P_id_ordem_final' => $id_ordem_final,
                'P_nf_inicial' => $id_nf_inicial,
                'P_nf_final' => $id_nf_final
            );

            //== define pararemetros relatorios  
            $name = 'relatorio_nf';
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
            if (empty($request['id_nf_ordem'])) {
                $id_nf_inicial = 0;
                $id_nf_final   = 99999999;
            } else {
                $id_nf_inicial = $request['id_nf_ordem'];
                $id_nf_final   = $request['id_nf_ordem'];
            }

            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_id_fornecedor_inicial' => $id_fornecedor_inicial,
                'P_id_fornecedor_final' => $id_fornecedor_final,
                'P_id_ordem_inicial' => $id_ordem_inicial,
                'P_id_ordem_final' => $id_ordem_final,
                'P_nf_inicial' => $id_nf_inicial,
                'P_nf_final' => $id_nf_final
            );

            //== define pararemetros relatorios  
            $name = 'relatorio_nf_2';
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

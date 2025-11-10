<?php

namespace App\Modules\Checklist\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\Fornecedor;
use Illuminate\Http\Request;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;
use Illuminate\Support\Facades\Log; // Se quiser logar exceções

class RelatorioCheckListFornecedor extends Controller
{
    public function index(Request $request)
    {
        $query = Fornecedor::query();

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->input('id_fornecedor'));
        }


        $fornecedor = Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
            ->orderBy('nome_fornecedor')
            ->limit(30)
            ->get();
        return view('admin.relatoriochecklistfornecedor.index', compact('fornecedor'));
    }

    public function gerarPdf(Request $request)
    {
        // Permite até 5 minutos de execução para o Jasper gerar o PDF
        //set_time_limit(300);
        Log::info('Dados brutos recebidos:', $request->all());

        if ($request->filled(['data_inclusao', 'data_final'])) {

            if (empty($request['id_fornecedor'])) {
                $in_fornecedor = '!=';
                $id_fornecedor = '0';
            } else {
                $in_fornecedor = 'IN';
                $id_fornecedor = $request['id_fornecedor'];
            }


            // Formata datas corretamente — de dd/mm/yyyy para yyyy-mm-dd
            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');


            // Log para verificação
            Log::info("Datas convertidas: $datainicial até $datafinal");

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_fornecedor' => $in_fornecedor,
                'P_id_fornecedor' => $id_fornecedor
            );


            $name = 'checklist_forncedor';
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

        if ($request->filled(['data_inclusao', 'data_final'])) {

            if (empty($request['id_fornecedor'])) {
                $in_fornecedor = '!=';
                $id_fornecedor = '0';
            } else {
                $in_fornecedor = 'IN';
                $id_fornecedor = $request['id_fornecedor'];
            }


            // Formata datas corretamente — de dd/mm/yyyy para yyyy-mm-dd
            $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');


            // Log para verificação
            Log::info("Datas convertidas: $datainicial até $datafinal");

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_fornecedor' => $in_fornecedor,
                'P_id_fornecedor' => $id_fornecedor
            );


            $name = 'checklist_forncedor';
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

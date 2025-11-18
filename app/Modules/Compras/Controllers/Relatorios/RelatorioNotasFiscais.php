<?php

namespace App\Modules\Compras\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Filial;
use App\Modules\Compras\Models\Fornecedor;
use App\Models\NotaFiscalEntrada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;


class RelatorioNotasFiscais extends Controller
{
    public function index(Request $request)
    {
        $query = NotaFiscalEntrada::query();
        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }

        if ($request->filled('id_nota_fiscal_entrada')) {
            $query->where('id_nota_fiscal_entrada', $request->input('id_nota_fiscal_entrada'));
        }
        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->input('id_fornecedor'));
        }
        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();

        $notafiscal = NotaFiscalEntrada::select('id_nota_fiscal_entrada as value', 'id_nota_fiscal_entrada as label')
            ->orderBy('id_nota_fiscal_entrada')
            ->limit(30)
            ->get();

        $fornecedor = Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
            ->orderBy('nome_fornecedor')
            ->limit(30)
            ->get();

        return view('admin.relatorioentradanotasfiscais.index', compact('filial', 'notafiscal', 'fornecedor'));
    }

    public function gerarPdf(Request $request)
    {
        try {
            Log::info('Dados brutos recebidos:', $request->all());


            $fornecedor = empty($request['id_fornecedor']) ? "0" : $request['id_fornecedor'];
            $filial     = empty($request['id_filial']) ? "0" : $request['id_filial'];
            $numero_nf     = empty($request['id_nota_fiscal_entrada']) ? "0" : $request['id_nota_fiscal_entrada'];

            if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

                if (empty($request['id_filial'])) {
                    $in_filial  = '!=';
                    $id_filial  = '0';
                } else {
                    $in_filial  = 'IN';
                    $id_filial  = $request['id_filial'];
                }
                if (empty($request['id_fornecedor'])) {
                    $in_fornecedor  = '!=';
                    $id_fornecedor  = '0';
                } else {
                    $in_fornecedor  = 'IN';
                    $id_fornecedor  = $request['id_fornecedor'];
                }
                if (empty($request['id_nota_fiscal_entrada'])) {
                    $in_numero_nf  = '!=';
                    $id_numero_nf  = '0';
                } else {
                    $in_numero_nf  = 'IN';
                    $id_numero_nf  = $request['id_nota_fiscal_entrada'];
                }



                $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
                $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

                // Parâmetros Jasper
                $parametros = array(
                    'P_data_inicial' => $datainicial,
                    'P_data_final' => $datafinal,
                    'P_in_fornecedor' => $in_fornecedor,
                    'P_id_fornecedor' => $id_fornecedor,
                    'P_in_filial' => $in_filial,
                    'P_id_filial' => $id_filial,
                    'P_in_numero_nf' => $in_numero_nf,
                    'P_id_numero_nf' => $id_numero_nf
                );

                Log::info("Parâmetros Jasper:", $parametros);

                $name       = 'notas_fiscais_entrada_v1';
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
        } catch (\Exception $e) {
            Log::error("Erro ao gerar PDF: " . $e->getMessage());
            return response()->json(['message' => 'Erro ao gerar o relatório PDF.'], 500);
        }
    }

    public function gerarExcel(Request $request)
    {
        try {
            Log::info('Dados brutos recebidos:', $request->all());


            $fornecedor = empty($request['id_fornecedor']) ? "0" : $request['id_fornecedor'];
            $filial     = empty($request['id_filial']) ? "0" : $request['id_filial'];
            $numero_nf     = empty($request['id_nota_fiscal_entrada']) ? "0" : $request['id_nota_fiscal_entrada'];

            if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

                if (empty($request['id_filial'])) {
                    $in_filial  = '!=';
                    $id_filial  = '0';
                } else {
                    $in_filial  = 'IN';
                    $id_filial  = $request['id_filial'];
                }
                if (empty($request['id_fornecedor'])) {
                    $in_fornecedor  = '!=';
                    $id_fornecedor  = '0';
                } else {
                    $in_fornecedor  = 'IN';
                    $id_fornecedor  = $request['id_fornecedor'];
                }
                if (empty($request['id_nota_fiscal_entrada'])) {
                    $in_numero_nf  = '!=';
                    $id_numero_nf  = '0';
                } else {
                    $in_numero_nf  = 'IN';
                    $id_numero_nf  = $request['id_nota_fiscal_entrada'];
                }



                $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
                $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

                // Parâmetros Jasper
                $parametros = array(
                    'P_data_inicial' => $datainicial,
                    'P_data_final' => $datafinal,
                    'P_in_fornecedor' => $in_fornecedor,
                    'P_id_fornecedor' => $id_fornecedor,
                    'P_in_filial' => $in_filial,
                    'P_id_filial' => $id_filial,
                    'P_in_numero_nf' => $in_numero_nf,
                    'P_id_numero_nf' => $id_numero_nf
                );

                Log::info("Parâmetros Jasper:", $parametros);

                $name       = 'notas_fiscais_entrada_v2';
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
        } catch (\Exception $e) {
            Log::error("Erro ao gerar xls: " . $e->getMessage());
            return response()->json(['message' => 'Erro ao gerar o relatório xls.'], 500);
        }
    }
}

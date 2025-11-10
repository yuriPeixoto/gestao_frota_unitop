<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filial;
use App\Models\GrupoServico;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioFichaControleEstoque extends Controller
{
    public function index(Request $request)
    {
        $query = Produto::query();

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }

        if ($request->filled('id_grupo_servico')) {
            $query->where('id_grupo_servico', $request->input('id_grupo_servico'));
        }
        if ($request->filled('id_produto')) {
            $query->where('id_produto', $request->input('id_produto'));
        }
        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();

        $produto = Produto::select('id_produto as value', 'descricao_produto as label')
            ->orderBy('descricao_produto')
            ->limit(30)
            ->get();

        $grupo = GrupoServico::select('id_grupo as value', 'descricao_grupo as label')
            ->orderBy('descricao_grupo')
            ->get();

        return view('admin.relatoriofichacontroleestoque.index', compact('filial', 'produto', 'grupo'));
    }

    public function gerarPdf(Request $request)
    {
        try {
            Log::info('Dados brutos recebidos:', $request->all());


            // Produtos
            if ($request->filled(['data_inclusao', 'data_final'])) {

                if (empty($request['id_produto'])) {
                    $id_produto_inicial = '0';
                    $id_produto_final   = '99999';
                } else {
                    $id_produto_inicial = $request['id_produto'];
                    $id_produto_final   = $request['id_produto'];
                }

                if (empty($request['id_grupo'])) {
                    $id_grupo_inicial = '0';
                    $id_grupo_final   = '99999';
                } else {
                    $id_grupo_inicial = $request['id_grupo'];
                    $id_grupo_final   = $request['id_grupo'];
                }

                if (empty($request['id_filial'])) {
                    $id_filial_inicial = '0';
                    $id_filial_final   = '99999';
                } else {
                    $id_filial_inicial = $request['id_filial'];
                    $id_filial_final   = $request['id_filial'];
                }

                $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
                $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

                $parametros = array(
                    'P_data_inicial'        => $datainicial,
                    'P_data_final'              => $datafinal,
                    'P_id_produto_final'    => $id_produto_final,
                    'P_id_produto_inicial'      => $id_produto_inicial,
                    'P_id_grupo_inicial'    => $id_grupo_inicial,
                    'P_id_grupo_final'          => $id_grupo_final,
                    'P_id_filial_inicial'   => $id_filial_inicial,
                    'P_id_filial_final'         => $id_filial_final
                );

                Log::info("Parâmetros Jasper:", $parametros);

                $name       = 'controle_de_estoque';
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

                Log::info("Gerando pdf: {$relatorio}");
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
                        Log::error("Relatório pdf gerado vazio ou muito pequeno: tamanho " . strlen($data));
                        return response()->json(['message' => 'O relatório retornou vazio ou inválido.'], 500);
                    }

                    // Salva local para debug (opcional)
                    file_put_contents(storage_path("app/public/{$relatorio}"), $data);

                    return response($data, 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => "attachment; filename=\"{$relatorio}\"",
                    ]);
                } catch (\Exception $e) {
                    Log::error("Erro ao gerar pdf: " . $e->getMessage());
                    return response()->json(['message' => 'Erro ao gerar o relatório pdf.'], 500);
                }
            }
        } catch (\Exception $e) {
            Log::error("Erro ao gerar pdf: " . $e->getMessage());
            return response()->json(['message' => 'Erro ao gerar o relatório pdf.'], 500);
        }
    }

    public function gerarExcel(Request $request)
    {
        try {
            Log::info('Dados brutos recebidos:', $request->all());


            // Produtos
            if ($request->filled(['data_inclusao', 'data_final'])) {

                if (empty($request['id_produto'])) {
                    $id_produto_inicial = '0';
                    $id_produto_final   = '99999';
                } else {
                    $id_produto_inicial = $request['id_produto'];
                    $id_produto_final   = $request['id_produto'];
                }

                if (empty($request['id_grupo'])) {
                    $id_grupo_inicial = '0';
                    $id_grupo_final   = '99999';
                } else {
                    $id_grupo_inicial = $request['id_grupo'];
                    $id_grupo_final   = $request['id_grupo'];
                }

                if (empty($request['id_filial'])) {
                    $id_filial_inicial = '0';
                    $id_filial_final   = '99999';
                } else {
                    $id_filial_inicial = $request['id_filial'];
                    $id_filial_final   = $request['id_filial'];
                }

                $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
                $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

                $parametros = array(
                    'P_data_inicial'        => $datainicial,
                    'P_data_final'              => $datafinal,
                    'P_id_produto_final'    => $id_produto_final,
                    'P_id_produto_inicial'      => $id_produto_inicial,
                    'P_id_grupo_inicial'    => $id_grupo_inicial,
                    'P_id_grupo_final'          => $id_grupo_final,
                    'P_id_filial_inicial'   => $id_filial_inicial,
                    'P_id_filial_final'         => $id_filial_final
                );

                Log::info("Parâmetros Jasper:", $parametros);

                $name       = 'controle_de_estoque';
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

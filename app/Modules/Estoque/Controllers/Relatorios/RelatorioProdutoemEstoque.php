<?php

namespace App\Modules\Estoque\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Modules\Estoque\Models\Estoque;
use App\Models\Filial;
use App\Models\GrupoServico;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;
use Exception;

class RelatorioProdutoemEstoque extends Controller
{
    public function index(Request $request)
    {
        $query = Produto::query();

        if ($request->filled('id_produto')) {
            $query->where('id_produto', $request->input('id_produto'));
        }
        if ($request->filled('id_grupo_servico')) {
            $query->where('id_grupo_servico', $request->input('id_grupo_servico'));
        }
        if ($request->filled('id_estoque')) {
            $query->where('id_estoque', $request->input('id_estoque'));
        }
        if ($request->filled('id_estoque_produto')) {
            $query->where('id_estoque_produto', $request->input('id_estoque_produto'));
        }

        $produto = Produto::select('id_produto as value', 'descricao_produto as label')
            ->orderBy('descricao_produto')
            ->limit(30)
            ->get();

        $grupo = GrupoServico::select('id_grupo as value', 'descricao_grupo as label')
            ->orderBy('descricao_grupo')
            ->limit(30)
            ->get();
        $estoque = Estoque::select('id_estoque as value', 'descricao_estoque as label')
            ->orderBy('descricao_estoque')
            ->limit(30)
            ->get();
        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();
        return view('admin.relatorioprodutoemestoque.index', compact('produto', 'grupo', 'estoque', 'filial'));
    }

    public function gerarPdf(Request $request)
    {
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->array());


        $id_produto_    = empty($request['id_produto']) ? "0" : $request['id_produto'];
        $estoque_       = empty($request['id_estoque']) ? "0" : $request['id_estoque'];
        $idfilial       = empty($request['id_filial']) ? "0" : $request['id_filial'];
        $idgrupo        = empty($request['id_grupo']) ? "0" : $request['id_grupo'];

        if (empty($request['id_produto'])) {
            $in_produto = '!=';
            $id_produto = '0';
        } else {
            $in_produto = 'IN';
            $id_produto = $request['id_produto'];
        }

        if (empty($request['id_estoque'])) {
            $in_estoque = '!=';
            $id_estoque = '0';
        } else {
            $in_estoque = 'IN';
            $id_estoque = $request['id_estoque'];
        }

        if (empty($request['id_filial'])) {
            $in_filial = '!=';
            $id_filial = '0';
        } else {
            $in_filial  = 'IN';
            $id_filial  = $request['id_filial'];
        }

        if (empty($request['id_grupo'])) {
            $in_grupo = '!=';
            $id_grupo = '0';
        } else {
            $in_grupo = 'IN';
            $id_grupo = $request['id_grupo'];
        }

        $parametros = array(
            'P_in_produto' => $in_produto,
            'P_id_produto' => $id_produto,
            'P_in_grupo_servico' => $in_grupo,
            'P_id_grupo_servico' => $id_grupo,
            'P_in_estoque' => $in_estoque,
            'P_id_estoque' => $id_estoque,
            'P_in_filial' => $in_filial,
            'P_id_filial' => $id_filial,
        );

        $name       = 'produtos_em_estoque_v1';
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


        return response()->json(['message' => 'Informe a data inicial e final para emissão do relatório.'], 400);
    }

    public function gerarExcel(Request $request)
    {
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->array());


        $id_produto_    = empty($request['id_produto']) ? "0" : $request['id_produto'];
        $estoque_       = empty($request['id_estoque']) ? "0" : $request['id_estoque'];
        $idfilial       = empty($request['id_filial']) ? "0" : $request['id_filial'];
        $idgrupo        = empty($request['id_grupo']) ? "0" : $request['id_grupo'];

        if (empty($request['id_produto'])) {
            $in_produto = '!=';
            $id_produto = '0';
        } else {
            $in_produto = 'IN';
            $id_produto = $request['id_produto'];
        }

        if (empty($request['id_estoque'])) {
            $in_estoque = '!=';
            $id_estoque = '0';
        } else {
            $in_estoque = 'IN';
            $id_estoque = $request['id_estoque'];
        }

        if (empty($request['id_filial'])) {
            $in_filial = '!=';
            $id_filial = '0';
        } else {
            $in_filial  = 'IN';
            $id_filial  = $request['id_filial'];
        }

        if (empty($request['id_grupo'])) {
            $in_grupo = '!=';
            $id_grupo = '0';
        } else {
            $in_grupo = 'IN';
            $id_grupo = $request['id_grupo'];
        }

        $parametros = array(
            'P_in_produto' => $in_produto,
            'P_id_produto' => $id_produto,
            'P_in_grupo_servico' => $in_grupo,
            'P_id_grupo_servico' => $id_grupo,
            'P_in_estoque' => $in_estoque,
            'P_id_estoque' => $id_estoque,
            'P_in_filial' => $in_filial,
            'P_id_filial' => $id_filial,
        );

        $name       = 'produtos_em_estoque_v1';
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


        return response()->json(['message' => 'Informe a data inicial e final para emissão do relatório.'], 400);
    }
}

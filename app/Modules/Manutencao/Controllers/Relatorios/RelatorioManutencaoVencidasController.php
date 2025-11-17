<?php

namespace App\Modules\Manutencao\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioManutencaoVencidasController extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $filiais = \App\Models\VFilial::select('id as value', 'name as label')->orderBy('name')->limit(30)->get();
        $veiculos = \App\Models\Veiculo::select('id_veiculo as value', 'placa as label')->orderBy('placa')->limit(30)->get();
        $categorias = \App\Models\CategoriaVeiculo::select('id_categoria as value', 'descricao_categoria as label')->orderBy('descricao_categoria')->limit(30)->get();
        $manutencoes = \App\Models\Manutencao::select('id_manutencao as value', 'descricao_manutencao as label')->orderBy('descricao_manutencao')->limit(30)->get();


        return view('admin.relatoriomanutencaovencidas.index', [
            'filiais' => $filiais,
            'veiculos' => $veiculos,
            'categorias' => $categorias,
            'manutencoes' => $manutencoes,
        ]);
    }

    public function gerarPdf(Request $request)
    {
        if (empty($request['id'])) {
            $id_filial_inicio = 0;
            $id_filial_final  = 999999;
        } else {
            $id_filial_inicio = $request['id'];
            $id_filial_final  = $request['id'];
        }

        if (empty($request['id_veiculo']) || $request['id_veiculo'] == null) {
            $placa_inicial   = 0;
            $placa_final     = 999999;
        } else {
            $placa_inicial   = $request['id_veiculo'];
            $placa_final     = $request['id_veiculo'];
        }

        if (empty($request['id_manutencao'])) {
            $manutencao_inicial = 0;
            $manutencao_final   = 999999;
        } else {
            $manutencao_inicial = $request['id_manutencao'];
            $manutencao_final  = $request['id_manutencao'];
        }

        if (empty($request['id_categoria'])) {
            $categoria_inicial  = 0;
            $categoria_final    = 999999;
        } else {
            $categoria_inicial  = $request['id_categoria'];
            $categoria_final    = $request['id_categoria'];
        }

        $parametros = array(
            'P_id_filial_inicial' => $id_filial_inicio,
            'P_id_filial_final' => $id_filial_final,
            'P_id_veiculo_inicial' => $placa_inicial,
            'P_id_veiculo_final' => $placa_final,
            'P_id_manutencao_inicial' => $manutencao_inicial,
            'P_id_manutencao_final' => $manutencao_final,
            'P_id_categoria_inicial' => $categoria_inicial,
            'P_id_categoria_final' => $categoria_final,
        );

        $name       = 'manutencoes_vencidas_v3';
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

    public function gerarExcel(Request $request)
    {
        if (empty($request['id_filial'])) {
            $id_filial_inicio = 0;
            $id_filial_final  = 999999;
        } else {
            $id_filial_inicio = $request['id_filial'];
            $id_filial_final  = $request['id_filial'];
        }

        if (empty($request['id_veiculo']) || $request['id_veiculo'] == null) {
            $placa_inicial   = 0;
            $placa_final     = 999999;
        } else {
            $placa_inicial   = $request['id_veiculo'];
            $placa_final     = $request['id_veiculo'];
        }

        if (empty($request['id_manutencao'])) {
            $manutencao_inicial = 0;
            $manutencao_final   = 999999;
        } else {
            $manutencao_inicial = $request['id_manutencao'];
            $manutencao_final  = $request['id_manutencao'];
        }

        if (empty($request['id_categoria'])) {
            $categoria_inicial  = 0;
            $categoria_final    = 999999;
        } else {
            $categoria_inicial  = $request['id_categoria'];
            $categoria_final    = $request['id_categoria'];
        }

        $parametros = array(
            'P_id_filial_inicial' => $id_filial_inicio,
            'P_id_filial_final' => $id_filial_final,
            'P_id_veiculo_inicial' => $placa_inicial,
            'P_id_veiculo_final' => $placa_final,
            'P_id_manutencao_inicial' => $manutencao_inicial,
            'P_id_manutencao_final' => $manutencao_final,
            'P_id_categoria_inicial' => $categoria_inicial,
            'P_id_categoria_final' => $categoria_final,
        );
        $name = 'manutencoes_vencidas_v4';
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

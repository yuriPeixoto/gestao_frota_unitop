<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\ModeloPneu;
use App\Models\Pneu;
use App\Models\VFilial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioControleeMovimentacaoEstoqueDosPneus extends Controller
{
    public function index(Request $request)
    {
        $query = Pneu::query();

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }

        if ($request->filled('id_pneu')) {
            $query->where('id_pneu', $request->input('id_pneu'));
        }

        if ($request->filled('id_modelo_pneu')) {
            $query->where('id_modelo_pneu', $request->input('id_modelo_pneu'));
        }

        if ($request->filled('id_departamento')) {
            $query->where('id_departamento', $request->input('id_departamento'));
        }

        $pneus = Pneu::select('id_pneu as  value', 'id_pneu as label')->orderBy('id_pneu')->limit(30)->get();
        $filiais = VFilial::select('id as value', 'name as label')->orderBy('name')->limit(30)->get();
        $modelo = ModeloPneu::select('id_modelo_pneu as value', 'descricao_modelo as label')->orderBy('descricao_modelo')->limit(30)->get();
        $departamento = Departamento::select('id_departamento as value', 'descricao_departamento as label')->orderBy('descricao_departamento')->limit(30)->get();


        return view('admin.relatoriocontroleemovimentacaodeestoquedospneus.index', [
            'filiais' => $filiais,
            'modelo'  => $modelo,
            'departamento' => $departamento,
            'pneus'     => $pneus
        ]);
    }

    public function gerarPdf(Request $request)
    {
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->post());

        $pneu         = empty($request['id_pneu']) ? "0" : $request['id_pneu'];
        $modelo       = empty($request['id_modelo_pneu']) ? "0" : $request['id_modelo_pneu'];
        $departamento = empty($request['id_departamento']) ? "0" : $request['id_departamento'];
        $filial       = empty($request['id_filial']) ? "0" : $request['id_filial'];


        if (empty($request['id_filial'])) {
            $in_filial  = '!=';
            $id_filial  = '0';
        } else {
            $in_filial  = 'IN';
            $id_filial  = $request['id_filial'];
        }



        if (empty($request['id_pneu'])) {
            $in_pneu  = '!=';
            $id_pneu  = '0';
        } else {
            $in_pneu  = 'IN';
            $id_pneu  = $request['id_pneu'];
        }

        if (empty($request['id_modelo_pneu'])) {
            $in_pneu_modelo  = '!=';
            $id_pneu_modelo  = '0';
        } else {
            $in_pneu_modelo  = 'IN';
            $id_pneu_modelo  = $request['id_modelo_pneu'];
        }

        if (empty($request['id_departamento'])) {
            $in_departamento  = '!=';
            $id_departamento  = '0';
        } else {
            $in_departamento  = 'IN';
            $id_departamento  = $request['id_departamento'];
        }


        $parametros = array(

            'P_in_pneu' =>  $in_pneu,
            'P_id_pneu' =>  $id_pneu,

            'P_in_modelo' =>  $in_pneu_modelo,
            'P_id_modelo' =>  $id_pneu_modelo,

            'P_in_departamento' =>  $in_departamento,
            'P_id_departamento' =>  $id_departamento,

            'P_in_filial' => $in_filial,
            'P_id_filial' => $id_filial,

        );

        $name       = 'controle_movimentacao_estoque_pneu_v1';
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
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->post());

        $pneu         = empty($request['id_pneu']) ? "0" : $request['id_pneu'];
        $modelo       = empty($request['id_modelo_pneu']) ? "0" : $request['id_modelo_pneu'];
        $departamento = empty($request['id_departamento']) ? "0" : $request['id_departamento'];
        $filial       = empty($request['id_filial']) ? "0" : $request['id_filial'];


        if (empty($request['id_filial'])) {
            $in_filial  = '!=';
            $id_filial  = '0';
        } else {
            $in_filial  = 'IN';
            $id_filial  = $request['id_filial'];
        }



        if (empty($request['id_pneu'])) {
            $in_pneu  = '!=';
            $id_pneu  = '0';
        } else {
            $in_pneu  = 'IN';
            $id_pneu  = $request['id_pneu'];
        }

        if (empty($request['id_modelo_pneu'])) {
            $in_pneu_modelo  = '!=';
            $id_pneu_modelo  = '0';
        } else {
            $in_pneu_modelo  = 'IN';
            $id_pneu_modelo  = $request['id_modelo_pneu'];
        }

        if (empty($request['id_departamento'])) {
            $in_departamento  = '!=';
            $id_departamento  = '0';
        } else {
            $in_departamento  = 'IN';
            $id_departamento  = $request['id_departamento'];
        }


        $parametros = array(

            'P_in_pneu' =>  $in_pneu,
            'P_id_pneu' =>  $id_pneu,

            'P_in_modelo' =>  $in_pneu_modelo,
            'P_id_modelo' =>  $id_pneu_modelo,

            'P_in_departamento' =>  $in_departamento,
            'P_id_departamento' =>  $id_departamento,

            'P_in_filial' => $in_filial,
            'P_id_filial' => $id_filial,

        );

        $name = 'controle_movimentacao_estoque_pneu_v2';
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

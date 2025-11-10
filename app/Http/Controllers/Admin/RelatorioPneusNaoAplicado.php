<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Pneu;
use App\Models\Veiculo;
use App\Models\VFilial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioPneusNaoAplicado extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = Pneu::query();

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->input('id_veiculo'));
        }

        if ($request->filled('id_controle_vida_pneu')) {
            $query->where('id_controle_vida_pneu', $request->input('id_controle_vida_pneu'));
        }

        $filiais = VFilial::select('id as value', 'name as label')

            ->orderBy('name')
            ->limit(30)
            ->get();

        $departamento = Departamento::select('id_departamento as value', 'descricao_departamento as label')
            ->orderBy('descricao_departamento')
            ->limit(30)
            ->get();

        $placa = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();


        return view('admin.relatoriopneusnaoaplicado.index', [
            'filiais' => $filiais,
            'departamento'  => $departamento,
            'placa'     => $placa
        ]);
    }

    public function gerarPdf(Request $request)
    {
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->array());


        $filial         = empty($request['id_filial']) ? "0" : $request['id_filial'];
        $placa          = empty($request['id_veiculo']) ? "0" : $request['id_veiculo'];
        $departamento   = empty($request['id_departamento']) ? "0" : $request['id_departamento'];

        if (empty($request['id_veiculo'])) {
            $in_pneu  = '!=';
            $id_pneu  = '0';
        } else {
            $in_pneu  = 'IN';
            $id_pneu  = $request['id_veiculo'];
        }
        if (empty($request['id_filial'])) {
            $in_filial  = '!=';
            $id_filial  = '0';
        } else {
            $in_filial  = 'IN';
            $id_filial  = $request['id_filial'];
        }

        if (empty($request['id_departamento'])) {
            $in_departamento  = '!=';
            $id_departamento  = '0';
        } else {
            $in_departamento  = 'IN';
            $id_departamento  = $request['id_departamento'];
        }



        $parametros = array(

            'P_in_veiculo' =>  $in_pneu,
            'P_id_veiculo' =>  $id_pneu,
            'P_in_filial' => $in_filial,
            'P_id_filial' => $id_filial,
            'P_in_departamento' => $in_departamento,
            'P_id_departamento' => $id_departamento

        );

        $name       = 'Pneus_nao_Aplicados';
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
        Log::info('Post params:', $request->array());


        $filial         = empty($request['id_filial']) ? "0" : $request['id_filial'];
        $placa          = empty($request['id_veiculo']) ? "0" : $request['id_veiculo'];
        $departamento   = empty($request['id_departamento']) ? "0" : $request['id_departamento'];

        if (empty($request['id_veiculo'])) {
            $in_pneu  = '!=';
            $id_pneu  = '0';
        } else {
            $in_pneu  = 'IN';
            $id_pneu  = $request['id_veiculo'];
        }
        if (empty($request['id_filial'])) {
            $in_filial  = '!=';
            $id_filial  = '0';
        } else {
            $in_filial  = 'IN';
            $id_filial  = $request['id_filial'];
        }

        if (empty($request['id_departamento'])) {
            $in_departamento  = '!=';
            $id_departamento  = '0';
        } else {
            $in_departamento  = 'IN';
            $id_departamento  = $request['id_departamento'];
        }



        $parametros = array(

            'P_in_veiculo' =>  $in_pneu,
            'P_id_veiculo' =>  $id_pneu,
            'P_in_filial' => $in_filial,
            'P_id_filial' => $id_filial,
            'P_in_departamento' => $in_departamento,
            'P_id_departamento' => $id_departamento

        );

        $name = 'Pneus_nao_Aplicados_v2';
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

<?php

namespace App\Modules\Pneus\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\ModeloPneu;
use App\Models\Pneu;
use App\Models\VFilial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;
use Exception;

class RelatorioPneusPorStatus extends Controller
{
    public function index(Request $request)
    {
        $query = Pneu::query();

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }

        if ($request->filled('id_modelo_pneu')) {
            $query->where('id_modelo_pneu', $request->input('id_modelo_pneu'));
        }
        if ($request->filled('status_pneu')) {
            $query->where('status_pneu', $request->input('status_pneu'));
        }

        if ($request->filled('id_pneu')) {
            $query->where('id_pneu', $request->input('id_pneu'));
        }

        $pneu = Pneu::select('id_pneu as value', 'id_pneu as label')
            ->orderBy('id_pneu')
            ->limit(30)
            ->get();


        $filiais = VFilial::select('id as value', 'name as label')

            ->orderBy('name')
            ->limit(30)
            ->get();

        $modelo = ModeloPneu::select('id_modelo_pneu as value', 'descricao_modelo as label')
            ->orderBy('id_modelo_pneu')
            ->limit(30)
            ->get();

        $status = Pneu::whereIn('status_pneu', ['ESTOQUE', 'DESCARTE', 'NÃO APLICADO', 'AGUARDANDO LAUDO', 'TRANSFERÊNCIA', 'EM MANUTENÇÃO', 'APLICADO', 'TERCEIRO'])
            ->distinct('status_pneu')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id_pneu,
                    'label' => $item->status_pneu
                ];
            })
            ->toArray();

        return view('admin.relatoriopneusstatus.index', [
            'filiais' => $filiais,
            'status' => $status,
            'modelo'    => $modelo,
            'pneu'  => $pneu
        ]);
    }

    public function gerarPdf(Request $request)
    {
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->array());


        $idd_filial          = empty($request['id_filial']) ? "0" : $request['id_filial'];
        $idd_pneu          = empty($request['id_pneu']) ? "0" : $request['id_pneu'];
        $id_modelo_pneu          = empty($request['id_modelo_pneu']) ? "0" : $request['id_modelo_pneu'];
        $status_pneu          = empty($request['status_pneu']) ? "0" : $request['status_pneu'];



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
            $in_modelo  = '!=';
            $id_modelo  = '0';
        } else {
            $in_modelo  = 'IN';
            $id_modelo  = $request['id_modelo_pneu'];
        }

        if (empty($request['status_pneu'])) {
            $in_status  = '!=';
            $id_status  = '0';
        } else {
            $in_status  = 'IN';
            $id_status  = $request['status_pneu'];
        }



        $parametros = array(
            'P_in_pneu'         => $in_pneu,
            'P_id_pneu'         => $id_pneu,
            'P_in_filial'       => $in_filial,
            'P_id_filial'       => $id_filial,
            'P_in_status'       => $in_status,
            'P_id_status'       => $id_status,
            'P_in_modelo'       => $in_modelo,
            'P_id_modelo'       => $id_modelo
        );

        $name  = 'pneus_por_status';
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


        return response()->json(['message' => 'Informe a data inicial e final para emissão do relatório.'], 400);
    }

    public function gerarExcel(Request $request)
    {
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->array());


        $idd_filial          = empty($request['id_filial']) ? "0" : $request['id_filial'];
        $idd_pneu          = empty($request['id_pneu']) ? "0" : $request['id_pneu'];
        $id_modelo_pneu          = empty($request['id_modelo_pneu']) ? "0" : $request['id_modelo_pneu'];
        $status_pneu          = empty($request['status_pneu']) ? "0" : $request['status_pneu'];



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
            $in_modelo  = '!=';
            $id_modelo  = '0';
        } else {
            $in_modelo  = 'IN';
            $id_modelo  = $request['id_modelo_pneu'];
        }

        if (empty($request['status_pneu'])) {
            $in_status  = '!=';
            $id_status  = '0';
        } else {
            $in_status  = 'IN';
            $id_status  = $request['status_pneu'];
        }



        $parametros = array(
            'P_in_pneu'         => $in_pneu,
            'P_id_pneu'         => $id_pneu,
            'P_in_filial'       => $in_filial,
            'P_id_filial'       => $id_filial,
            'P_in_status'       => $in_status,
            'P_id_status'       => $id_status,
            'P_in_modelo'       => $in_modelo,
            'P_id_modelo'       => $id_modelo
        );

        $name  = 'pneus_por_status_xls';
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

        Log::info("Gerando EXCEL: {$relatorio}");
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
                Log::error("Relatório EXCEL gerado vazio ou muito pequeno: tamanho " . strlen($data));
                return response()->json(['message' => 'O relatório retornou vazio ou inválido.'], 500);
            }

            // Salva local para debug (opcional)
            file_put_contents(storage_path("app/public/{$relatorio}"), $data);

            return response($data, 200, [
                'Content-Type' => 'application/xls',
                'Content-Disposition' => "attachment; filename=\"{$relatorio}\"",
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao gerar EXCEL: " . $e->getMessage());
            return response()->json(['message' => 'Erro ao gerar o relatório EXCEL.'], 500);
        }


        return response()->json(['message' => 'Informe a data inicial e final para emissão do relatório.'], 400);
    }
}

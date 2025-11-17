<?php

namespace App\Modules\Pneus\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\ControleVidaPneus;
use App\Models\Departamento;
use App\Models\ModeloPneu;
use App\Models\Pneu;
use App\Models\VFilial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioEntradaManutencaoPneus extends Controller
{
    public function index(Request $request)
    {
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

        if ($request->filled('id_controle_vida_pneu')) {
            $query->where('id_controle_vida_pneu', $request->input('id_controle_vida_pneu'));
        }

        $filiais = VFilial::select('id as value', 'name as label')

            ->orderBy('name')
            ->limit(30)
            ->get();

        $vidaPneu = ControleVidaPneus::whereIn('descricao_vida_pneu', ['1', '2', '3', 'IMPORTACAO'])
            ->distinct('descricao_vida_pneu')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id_controle_vida_pneu,
                    'label' => $item->descricao_vida_pneu
                ];
            })
            ->toArray();


        return view('admin.relatorioentradadepneumanutencao.index', [
            'filiais' => $filiais,
            'vidaPneu' => $vidaPneu
        ]);
    }

    public function gerarPdf(Request $request)
    {
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->post());

        if ($request->filled(['data_inclusao', 'data_final'])) {

            $filial         = empty($request['id_filial']) ? "0" : $request['id_filial'];
            $vida       = empty($request['id_controle_vida_pneu']) ? "0" : $request['id_controle_vida_pneu'];

            if (empty($request['id_filial'])) {
                $in_filial  = '!=';
                $id_filial  = '0';
            } else {
                $in_filial  = 'IN';
                $id_filial  = $request['id_filial'];
            }
            if (empty($request['id_controle_vida_pneu'])) {
                $in_pneu  = '!=';
                $id_pneu  = '0';
            } else {
                $in_pneu  = 'IN';
                $id_pneu  = $request['id_controle_vida_pneu'];
            }

            $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
            $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

            $parametros = array(

                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_vida_pneu' =>  $in_pneu,
                'P_id_vida_pneu' =>  $id_pneu,
                'P_in_filial' => $in_filial,
                'P_id_filial' => $id_filial,

            );

            $name       = 'entrada_pneu_manutencao_v1';
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
                $pastarelatorio = '/reports/homologacao/' . $name;

                Log::info('Usando servidor de homologação');
            } elseif ($dominio == 'lcarvalima') {
                $jasperserver = 'http://10.10.1.8:8080/jasperserver';
                $input = '/reports/homologacao/' . $name;

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
    }

    public function gerarExcel(Request $request)
    {
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->post());

        if ($request->filled(['data_inclusao', 'data_final'])) {

            $filial         = empty($request['id_filial']) ? "0" : $request['id_filial'];
            $vida       = empty($request['id_controle_vida_pneu']) ? "0" : $request['id_controle_vida_pneu'];



            if (empty($request['id_filial'])) {
                $in_filial  = '!=';
                $id_filial  = '0';
            } else {
                $in_filial  = 'IN';
                $id_filial  = $request['id_filial'];
            }
            if (empty($request['id_controle_vida_pneu'])) {
                $in_pneu  = '!=';
                $id_pneu  = '0';
            } else {
                $in_pneu  = 'IN';
                $id_pneu  = $request['id_controle_vida_pneu'];
            }

            $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
            $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

            $parametros = array(

                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_vida_pneu' =>  $in_pneu,
                'P_id_vida_pneu' =>  $id_pneu,
                'P_in_filial' => $in_filial,
                'P_id_filial' => $id_filial,

            );

            $name = 'entrada_pneu_manutencao_v2';
            $agora = now()->format('d-m-Y_H-i');
            $relatorio = "{$name}_{$agora}.xls";

            $host = parse_url(request()->url(), PHP_URL_HOST);
            $dominio = explode('.', $host)[0];

            if ($dominio == '127' || $dominio == 'localhost') {
                $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
                $pastarelatorio = "/reports/homologacao/{$name}";
            } else {
                $jasperserver = 'http://10.10.1.8:8080/jasperserver';
                $pastarelatorio = "/reports/{$dominio}/{$name}";
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
}

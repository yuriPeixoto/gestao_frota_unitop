<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filial;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioUltimaMovimentacaoDespesas extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = Veiculo::query();

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

        $placa = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->get();

        return view('admin.relatorioultimamovimentacaodespesas.index', compact('placa', 'filial'));
    }

    public function gerarPdf(Request $request)
    {
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->post());

        $filial = $request['id_filial'] ?? '';
        $placa = $request['id_veiculo'] ?? '';

        if (empty($request['id_filial'])) {
            $P_in_id_nome  = '!=';
            $P_id_id_nome  = '0';
        } else {
            $P_in_id_nome  = '=';
            $P_id_id_nome  = $request['id_filial'];
        }

        $P_in_placa = '!=';
        $P_id_placa = '0';

        if (!empty($request['id_veiculo'])) {
            if (is_array($request['id_veiculo'])) {
                $P_in_placa  = 'IN';
                $P_id_placa = implode(',', $request['id_veiculo']);
            } else {
                $P_in_placa  = '=';
                $P_id_placa  = $request['id_veiculo'];
            }
        }

        $parametros = array(
            'P_in_filial' => $P_in_id_nome,
            'busca_filial' => $P_id_id_nome,
            'P_in_placa' => $P_in_placa,
            'busca_placa' => $P_id_placa
        );



        $name = 'relatorio_movimentacoes_despesas_parte_03';
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

        $jsi = new TraitsJasperServerIntegration(
            $jasperserver,
            $pastarelatorio,
            'pdf',
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

    public function gerarExcel(Request $request)
    {
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->post());

        $filial = $request['id_filial'] ?? '';
        $placa = $request['id_veiculo'] ?? '';

        if (empty($request['id_filial'])) {
            $P_in_id_nome  = '!=';
            $P_id_id_nome  = '0';
        } else {
            $P_in_id_nome  = '=';
            $P_id_id_nome  = $request['id_filial'];
        }

        $P_in_placa = '!=';
        $P_id_placa = '0';

        if (!empty($request['id_veiculo'])) {
            if (is_array($request['id_veiculo'])) {
                $P_in_placa  = 'IN';
                $P_id_placa = implode(',', $request['id_veiculo']);
            } else {
                $P_in_placa  = '=';
                $P_id_placa  = $request['id_veiculo'];
            }
        }

        $parametros = array(
            'P_in_filial' => $P_in_id_nome,
            'busca_filial' => $P_id_id_nome,
            'P_in_placa' => $P_in_placa,
            'busca_placa' => $P_id_placa
        );


        $name = 'relatorio_movimentacoes_despesas_parte_03';
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

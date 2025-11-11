<?php

namespace App\Modules\Checklist\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Modules\Checklist\Models\CheckList;
use App\Models\Motorista;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioGeralChecklistController extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        // Filtros
        $dataInicial     = $request->input('data_realizacao');
        $dataFinal       = $request->input('data_final');

        // Filtros do formulário
        $query = CheckList::query();

        if ($request->filled('data_realizacao') && $request->filled('data_final')) {
            $query->whereBetween('data_realizacao', [
                $request->input('data_realizacao'),
                $request->input('data_final')
            ]);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->input('id_veiculo'));
        }

        // Filtro por data de inclusão
        if ($dataInicial && $dataFinal) {
            $query->whereBetween('data_realizacao', [$dataInicial, $dataFinal]);
        }

        $veiculos = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        $motorista = Motorista::select('idobtermotorista as value', 'nome as label')
            ->orderBy('nome')
            ->limit(30)
            ->get();

        return view('admin.relatoriogeralchecklist.index', [
            'veiculos' => $veiculos,
            'motorista' => $motorista,
        ]);
    }

    public function gerarPdf(Request $request)
    {
        Log::info('Dados brutos recebidos:', $request->all());

        if ($request->filled(['data_realizacao', 'data_final'])) {
            $dados = [
                'data_realizacao' => $request->data_realizacao,
                'data_final' => $request->data_final,
                'id_veiculo' => $request->id_veiculo,
                'idobtermotorista' => $request->idobtermotorista,
                'tipo' => $request->tipo,
            ];

            $tipoEntrada = ($dados['tipo'] === 'Ambos') ? "'Recebimento','Entrega'" : "'{$dados['tipo']}'";

            if (empty($dados['id_veiculo'])) {
                $in_placa = '!=';
                $id_placa = '0';
            } else {
                $in_placa = 'IN';
                $id_placa = $dados['id_veiculo'];
            }

            if (empty($dados['idobtermotorista'])) {
                $in_nome = '!=';
                $id_nome = '0';
            } else {
                $in_nome = 'IN';
                $id_nome = $dados['idobtermotorista'];
            }

            $datainicial = \Carbon\Carbon::parse($dados['data_realizacao'])->format('Y-m-d');
            $datafinal   = \Carbon\Carbon::parse($dados['data_final'])->format('Y-m-d');


            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_veiculo' => $in_placa,
                'P_id_veiculo' => $id_placa,
                'P_in_motorista' => $in_nome,
                'P_id_motorista' => $id_nome,
                'P_entrada' => $tipoEntrada,
            );

            $name = 'checklist_geral';
            $agora      = date('d-m-YH:i');
            $tipo       = '.pdf';
            $relatorio = "{$name}_{$agora}.{$tipo}";

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
    }


    public function gerarExcel(Request $request)
    {
        $param = [
            'data_realizacao' => $request->data_realizacao,
            'data_final' => $request->data_final,
            'id_veiculo' => $request->id_veiculo,
            'idobtermotorista' => $request->idobtermotorista,
            'tipo' => $request->tipo,
        ];

        // Corrigir valor do tipo
        $tipoEntrada = ($param['tipo'] === 'Ambos') ? "'Recebimento','Entrega'" : "'{$param['tipo']}'";

        if (!empty($request['data_realizacao']) && !empty($request['data_final'])) {
            if (empty($request['id_veiculo'])) {
                $in_placa  = '!=';
                $id_placa  = '0';
            } else {
                $in_placa  = 'IN';
                $id_placa  = $param('id_veiculo');
            }
            if (empty($request['idobtermotorista'])) {
                $in_nome  = '!=';
                $id_nome  = '0';
            } else {
                $in_nome  = 'IN';
                $id_nome  = $param('idobtermotorista');
            }

            $datainicial = \Carbon\Carbon::parse($request->input('data_realizacao'))->format('Y-m-d');
            $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

            $parametros = array(
                'P_data_inicial' => $datainicial,
                'P_data_final' => $datafinal,
                'P_in_veiculo' => $in_placa,
                'P_id_veiculo' => $id_placa,
                'P_in_motorista' => $in_nome,
                'P_id_motorista' => $id_nome,
                'P_entrada' => $tipoEntrada,
            );
            $name = 'checklist_geral_v2';
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
}

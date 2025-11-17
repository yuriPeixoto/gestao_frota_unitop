<?php

namespace App\Modules\Manutencao\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Modules\Compras\Models\Fornecedor;
use App\Models\NfOrdemServico;
use App\Models\NotaFiscal;
use App\Models\OrdemServico;
use App\Modules\Veiculos\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;
use TDate; // Certifique-se de que essa classe esteja corretamente importada

class RelatorioSinteticoNfOsController extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        // Filtros
        $dataInicial     = $request->input('data_inclusao');
        $dataFinal       = $request->input('data_final');
        $ordemServicoId  = $request->input('id_ordem_servico');
        $numeroNota = $request->input('numero_nf');
        $fornecedorId    = $request->input('id_fornecedor');
        $veiculoId       = $request->input('id_veiculo'); // Adicionado para evitar undefined
        $numeroNotaFiltro = $request->input('numero_nf'); // valor digitado/selecionado


        // Query com relacionamentos
        $query = OrdemServico::with(['notasFiscais', 'fornecedor', 'veiculo']);

        if ($dataInicial && $dataFinal) {
            $query->whereBetween('data_inclusao', [$dataInicial, $dataFinal]);
        }

        if (!empty($numeroNota)) {
            $query->whereHas('notasFiscais', function ($q) use ($numeroNota) {
                $q->where('numero_nf', $numeroNota);
            });
        }

        if (!empty($ordemServicoId)) {
            $query->where('id_ordem_servico', $ordemServicoId);
        }

        if (!empty($fornecedorId)) {
            $query->where('id_fornecedor', $fornecedorId);
        }

        if (!empty($veiculoId)) {
            $query->where('id_veiculo', $veiculoId);
        }

        if (!empty($numeroNotaFiltro)) {
            $query->whereHas('notasFiscais', function ($q) use ($numeroNotaFiltro) {
                $q->where('numero_nf', $numeroNotaFiltro);
            });
        }
        // Lista para os filtros do formulário
        //$nf = NotaFiscal::select('id_nota_fiscal as value', 'numero_nota as label');
        $numerosNota = NfOrdemServico::select('numero_nf as value', 'numero_nf as label')
            ->whereNotNull('numero_nf')
            ->groupBy('numero_nf')
            ->orderBy('numero_nf')
            ->limit(50)
            ->get();

        $nfO = NfOrdemServico::select('id_ordem_servico as value', 'id_ordem_servico as label')
            ->orderBy('id_ordem_servico')
            ->limit(30)
            ->get();

        $fornecedor = Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
            ->orderBy('nome_fornecedor')
            ->limit(30)
            ->get();

        $veiculo = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        return view('admin.relatoriosinteticonfos.index', [
            'numeroNota'     => $numerosNota,       // lista de opções para o select
            'numeroNotaFiltro' => $numeroNotaFiltro, // valor selecionado
            'ordemServicoId' => $ordemServicoId,
            'nfO'            => $nfO,
            'fornecedor'     => $fornecedor,
            'veiculo'        => $veiculo,
        ]);
    }


    public function gerarPdf(Request $request)
    {

        if (empty($request['id_veiculo'])) {
            $id_veiculo_inicial = 0;
            $id_veiculo_final   = 99999999;
        } else {
            $id_veiculo_inicial = $request['id_veiculo'];
            $id_veiculo_final   = $request['id_veiculo'];
        }

        if (empty($request['id_ordem_servico'])) {
            $id_ordem_inicial = 0;
            $id_ordem_final   = 99999999;
        } else {
            $id_ordem_inicial = $request['id_ordem_servico'];
            $id_ordem_final   = $request['id_ordem_servico'];
        }

        if (empty($request['numero_nf'])) {
            $id_nf_inicial = 0;
            $id_nf_final   = 99999999;
        } else {
            $id_nf_inicial = $request['numero_nf'];
            $id_nf_final   = $request['numero_nf'];
        }

        if (empty($request['fornecedor'])) {
            $id_fornecedor_inicial = 0;
            $id_fornecedor_final   = 99999999;
        } else {
            $id_fornecedor_inicial = $request['id_fornecedor'];
            $id_fornecedor_final   = $request['id_fornecedor'];
        }

        // Formata datas corretamente
        $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
        $datafinal   = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

        $parametros = [
            'P_data_inicial'         => $datainicial,
            'P_data_final'           => $datafinal,

            'P_id_veiculo_inicial'   => $id_veiculo_inicial,
            'P_id_veiculo_final'     => $id_veiculo_final,

            'P_id_ordem_inicial'     => $id_ordem_inicial,
            'P_id_ordem_final'       => $id_ordem_final,

            'P_nf_inicial'           => $id_nf_inicial,
            'P_nf_final'             => $id_nf_final,

            'P_fornecedor_inicial'   => $id_fornecedor_inicial,
            'P_fornecedor_final'     => $id_fornecedor_final,
        ];

        $name  = 'notas_fiscais_detalhado';
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
        if (empty($request['id_veiculo'])) {
            $id_veiculo_inicial = 0;
            $id_veiculo_final   = 99999999;
        } else {
            $id_veiculo_inicial = $request['id_veiculo'];
            $id_veiculo_final   = $request['id_veiculo'];
        }

        if (empty($request['id_ordem_servico'])) {
            $id_ordem_inicial = 0;
            $id_ordem_final   = 99999999;
        } else {
            $id_ordem_inicial = $request['id_ordem_servico'];
            $id_ordem_final   = $request['id_ordem_servico'];
        }

        if (empty($request['numero_nf'])) {
            $id_nf_inicial = 0;
            $id_nf_final   = 99999999;
        } else {
            $id_nf_inicial = $request['numero_nf'];
            $id_nf_final   = $request['numero_nf'];
        }

        if (empty($request['fornecedor'])) {
            $id_fornecedor_inicial = 0;
            $id_fornecedor_final   = 99999999;
        } else {
            $id_fornecedor_inicial = $request['id_fornecedor'];
            $id_fornecedor_final   = $request['id_fornecedor'];
        }

        // Formata datas corretamente — de dd/mm/yyyy para yyyy-mm-dd
        $datainicial = \Carbon\Carbon::parse($request->input('data_inclusao'))->format('Y-m-d');
        $datafinal = \Carbon\Carbon::parse($request->input('data_final'))->format('Y-m-d');

        $parametros = [
            'P_data_inicial'         => $datainicial,
            'P_data_final'           => $datafinal,
            'P_id_veiculo_inicial'   => $id_veiculo_inicial,
            'P_id_veiculo_final'     => $id_veiculo_final,
            'P_id_ordem_inicial'     => $id_ordem_inicial,
            'P_id_ordem_final'       => $id_ordem_final,
            'P_nf_inicial'           => $id_nf_inicial,
            'P_nf_final'             => $id_nf_final,
            'P_fornecedor_inicial'   => $id_fornecedor_inicial,
            'P_fornecedor_final'     => $id_fornecedor_final,
        ];
        $name = 'notas_fiscais_detalhado_v2';
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

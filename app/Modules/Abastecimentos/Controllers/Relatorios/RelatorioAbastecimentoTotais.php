<?php

namespace App\Modules\Abastecimentos\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Departamento;
use App\Modules\Configuracoes\Models\Filial;
use App\Modules\Abastecimentos\Models\TipoCombustivel;
use App\Modules\Configuracoes\Models\TipoEquipamento;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioAbastecimentoTotais extends Controller
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

        if ($request->filled('id_tipo_combustivel')) {
            $query->where('id_tipo_combustivel', $request->input('id_tipo_combustivel'));
        }
        if ($request->filled('id_departamento')) {
            $query->where('id_departamento', $request->input('id_departamento'));
        }
        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->input('id_veiculo'));
        }
        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }

        $tipo = TipoCombustivel::select('id_tipo_combustivel as value', 'descricao as label')->orderBy('descricao')->get();
        $placa = Veiculo::select('id_veiculo as value', 'placa as label')->orderBy('placa')->get();
        $departamento = Departamento::select('id_departamento as value', 'descricao_departamento as label')->orderBy('descricao_departamento')->get();
        $filial = Filial::select('id as value', 'name as label')->orderBy('name')->get();

        return view('admin.relatorioabastecimentototais.index', compact('tipo', 'placa', 'departamento', 'filial', 'placa'));
    }

    public function gerarExcel(Request $request)
    {
        Log::info('Request completo:', $request->all());
        Log::info('Query params:', $request->query());
        Log::info('Post params:', $request->post());

        $filial       = empty($request['id_filial']) ? "0" : $request['id_filial'];
        $tipo         = empty($request['id_tipo_combustivel']) ? "0" : $request['id_tipo_combustivel'];
        $departamento       = empty($request['id_departamento']) ? "0" : $request['id_departamento'];
        $placa         = empty($request['id_veiculo']) ? "0" : $request['id_veiculo'];
        $tipoabs         = empty($request['id_tipo_abastecimento']) ? "0" : $request['id_tipo_abastecimento'];

        if (!empty($request['data_inclusao']) && !empty($request['data_final'])) {

            if (empty($request['id_filial'])) {
                $in_filial  = '!=';
                $id_filial  = '0';
            } else {
                $in_filial  = 'IN';
                $id_filial  = $request['id_filial'];
            }
            if (empty($request['id_tipo_combustivel'])) {
                $in_combustivel  = '!=';
                $id_combustivel  = '0';
            } else {
                $in_combustivel  = 'IN';
                $id_combustivel  = $request['id_tipo_combustivel'];
            }
            if (empty($request['id_veiculo'])) {
                $in_veiculo  = '!=';
                $id_veiculo  = '0';
            } else {
                $in_veiculo  = 'IN';
                $id_veiculo  = $request['id_veiculo'];
            }
            if (empty($request['id_departamento'])) {
                $in_departamento  = '!=';
                $id_departamento  = '0';
            } else {
                $in_departamento  = 'IN';
                $id_departamento  = $request['id_departamento'];
            }
            if (empty($request['id_tipo_abastecimento'])) {
                $in_tipo  = '!=';
                $id_tipo  = '0';
            } else {
                $in_tipo  = 'IN';
                $id_tipo  = $request['id_tipo_abastecimento'];
            }


            $datainicial = \Carbon\Carbon::parse($request['data_inclusao'])->format('Y-m-d');
            $datafinal   = \Carbon\Carbon::parse($request['data_final'])->format('Y-m-d');

            $parametros = array(
                'P_data_inicial'      => $datainicial,
                'P_data_final'         => $datafinal,
                'P_in_filial'          => $in_filial,
                'P_id_filial'          => $id_filial,
                'P_in_departamento'    => $in_departamento,
                'P_id_departamento'    => $id_departamento,
                'P_in_tipo'            => $in_tipo,
                'P_id_tipo'            => $id_tipo,
                'P_in_placa'           => $in_veiculo,
                'P_id_placa'           => $id_veiculo,
                'P_in_tipocombustivel' => $in_combustivel,
                'P_id_tipocombustivel' => $id_combustivel
            );


            $name = 'relatorio_abastecimento_gerencial';
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

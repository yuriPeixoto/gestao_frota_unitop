<?php

namespace App\Modules\Veiculos\Controllers\Admin;


use App\Models\KmComotado;
use App\Models\Veiculo;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Traits\ExportableTrait;

class ManutencaoKmVeiculoComodatoController extends Controller
{
    use ExportableTrait;

    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = KmComotado::query();


        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', Carbon::createFromFormat('Y-m-d', $request->data_inclusao));
        }

        if ($request->filled('data_alteracao')) {
            $query->whereDate('data_alteracao', Carbon::createFromFormat('Y-m-d', $request->data_alteracao));
        }

        if ($request->filled('data_realizacao')) {
            $query->whereDate('data_realizacao', Carbon::createFromFormat('Y-m-d', $request->data_realizacao));
        }

        if ($request->filled('id_km_comodato')) {
            $query->where('id_km_comodato', $request->id_km_comodato);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('km_realizacao')) {
            $query->where('km_realizacao', $request->km_realizacao);
        }

        if ($request->filled('horimetro')) {
            $query->where('horimetro', $request->horimetro);
        }

        $manutancaoKm = $query->latest('id_km_comodato')
            ->paginate(10);

        if ($request->header('HX-Request')) {
            return view('admin.manutencaokmveiculocomodato._table', compact('manutancaoKm'));
        }

        $veiculosFrequentes = Cache::remember('veiculos_frequentes', now()->addHours(12), function () {
            return Veiculo::select('id_veiculos as value', 'placa as label')
                ->orderBy('id_veiculo')
                ->limit(20)
                ->get();
        });

        return view('admin.manutencaokmveiculocomodato.index', compact('veiculosFrequentes', 'manutancaoKm'));
    }

    public function create()
    {
        $veiculosFrequentes = Cache::remember('veiculos_frequentes', now()->addHours(12), function () {
            return Veiculo::select('id_veiculos as value', 'placa as label')
                ->orderBy('id_veiculo')
                ->limit(20)
                ->get();
        });


        return view('admin.manutencaokmveiculocomodato.create', compact(
            'veiculosFrequentes',
        ));
    }

    public function store(Request $request)
    {
        $resultado = self::validarKmAbastecimento($request);

        if ($resultado && $resultado['error']) {
            return redirect()
                ->back()
                ->with('error', $resultado['message'])->withInput($request->all());
        }

        $kmComodato = $request->validate([
            'id_veiculo'        => 'required|string',
            'data_realizacao'   => 'required|string',
            'km_realizacao'     => 'required|string',
            'horimetro'         => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $kmComodato['data_inclusao'] = now();

            KmComotado::create($kmComodato);

            DB::commit();

            return redirect()
                ->route('admin.manutencaokmveiculocomodato.index')
                ->with('success', 'Km Comodato cadastrado com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            log::error('Erro ao cadastrar Km Comodato: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar Km Comodato: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $kmComotado = KmComotado::where('id_km_comodato', $id)->first();
            $kmComotado->delete();

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public static function validarKmAbastecimento(Request $request)
    {
        try {
            log::debug('validarKmAbastecimento');
            log::debug($request->all());
            $kmAtual = $request->km_realizacao;
            $idVeiculo = $request->id_veiculo;
            $dataAbertura = $request->data_realizacao;
            log::debug(['kmAtual' => $kmAtual, 'idVeiculo' => $idVeiculo, 'dataAbertura' => $dataAbertura]);

            if (!self::isVeiculoTerceiro($idVeiculo) && !empty($kmAtual)) {
                $kmAbastecimento = self::buscarKmAbastecimento($idVeiculo, $dataAbertura);

                if ($kmAtual < $kmAbastecimento) {
                    // Retornar array em vez de Response
                    return [
                        'error' => true,
                        'message' => "Atenção: O Km {$kmAtual} é menor que do último abastecimento registrado, por isso, não será permitido a abertura desta ordem de serviço. Verifique o Km.",
                        'data' => ['km_atual' => $kmAbastecimento]
                    ];
                }

                // Verificar se há uma diferença muito grande no KM (mais de 5000)
                $retornoKm = $kmAtual - $kmAbastecimento;
                if ($retornoKm > 5000 && self::veiculoTemTracao($idVeiculo)) {
                    return [
                        'error' => true,
                        'message' => "Atenção: O Sistema não permitirá a inclusão de km superior à 5.000 KM do último abastecimento. Notamos que há uma inconsistência no que diz respeito ao Km. Por gentileza, ajuste o km do veículo antes de incluir a O.S.",
                        'data' => null
                    ];
                }
            }

            // Retornar sucesso quando não há erros
            return [
                'error' => false,
                'message' => 'Validação realizada com sucesso',
                'data' => null
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao validar KM: ' . $e->getMessage());

            return [
                'error' => true,
                'message' => 'Erro ao validar KM: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Método para buscar o KM do último abastecimento
     *
     * @param int $idVeiculo
     * @param string $dataAbertura
     * @return int
     */
    public static function buscarKmAbastecimento($idVeiculo, $dataAbertura)
    {
        // Converter data para formato padrão se necessário
        if (strpos($dataAbertura, '/') !== false) {
            $dataAbertura = \DateTime::createFromFormat('d/m/Y H:i', $dataAbertura)
                ->format('Y-m-d H:i:s');
        }

        // Buscar o KM do último abastecimento antes da data de abertura
        $kmAbastecimento = DB::connection('pgsql')->table('v_abastecimento_listar_todos AS lt')
            ->join('veiculo AS v', 'lt.placa', '=', 'v.placa')
            ->where('v.id_veiculo', $idVeiculo)
            ->where('lt.data_inicio', '<=', $dataAbertura)
            ->orderBy('lt.data_inicio', 'desc')
            ->value('lt.km_abastecimento');

        return $kmAbastecimento ?? 0;
    }

    /**
     * Verifica se o veículo é terceiro
     *
     * @param int $idVeiculo
     * @return bool
     */
    public static function isVeiculoTerceiro($idVeiculo)
    {
        // Verificar se é um veículo de terceiro
        return DB::connection('pgsql')->table('veiculo')
            ->where('id_veiculo', $idVeiculo)
            ->where('is_terceiro', true)
            ->exists();
    }

    /**
     * Verifica se o veículo tem tração (para validação de KM)
     *
     * @param int $idVeiculo
     * @return bool
     */
    public static function  veiculoTemTracao($idVeiculo)
    {
        // Consulta no banco para verificar se o veículo tem tração
        $tracao = DB::connection('pgsql')->table('veiculo')
            ->where('id_veiculo', $idVeiculo)
            ->value('is_possui_tracao');

        return (bool) $tracao;
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = $this->buildExportQuery($request);
            LOG::DEBUG('Query: ' . $query->toSql());

            // Se a exportação direta pelo trait não funcionar, tente um método alternativo
            log::debug($this->hasAnyFilter($request, $this->getValidExportFilters()));
            if (!$this->hasAnyFilter($request, $this->getValidExportFilters())) {
                return redirect()->back()->with([
                    'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                    'export_error' => true
                ]);
            }

            if ($request->has('confirmed') || !$this->exceedsExportLimit($query, 500)) {
                $data = $query->get();

                // Configurar opções do PDF
                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');

                // Carregar a view
                $pdf->loadView('admin.manutencaokmveiculocomodato.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('KM_COMODATO_' . date('Y-m-d_His') . '.pdf');
            } else {
                // Confirmação para grande volume
                $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

                return redirect()->back()->with([
                    'warning' => "Você está tentando exportar mais de 500 registros, o que pode levar mais tempo.",
                    'export_confirmation' => true,
                    'export_url' => $currentUrl
                ]);
            }
        } catch (\Exception $e) {
            // Log detalhado do erro
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()->with([
                'error' => 'Erro ao gerar o PDF: ' . $e->getMessage(),
                'export_error' => true
            ]);
        }
    }

    public function buildExportQuery($request)
    {
        $query = KmComotado::query()->with('veiculo', 'veiculo.filial');

        if ($request->filled('id_veiculo')) {
            $query->whereHas('veiculo', function ($q) use ($request) {
                $q->where('id_veiculo', $request->id_veiculo);
            });
        }

        if ($request->filled('km_realizacao')) {
            $query->where('km_realizacao', $request->filial);
        }

        if ($request->filled('data_inclusao_inicial')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao_inicial);
        }

        if ($request->filled('data_inclusao_fim')) {
            $query->whereDate('data_inclusao', '<=', $request->data_inclusao_fim);
        }

        if ($request->filled('data_alteracao_inicial')) {
            $query->whereDate('data_alteracao', '>=', $request->data_alteracao_inicial);
        }

        if ($request->filled('data_alteracao_fim')) {
            $query->whereDate('data_alteracao', '<=', $request->data_alteracao_fim);
        }

        return $query;
    }

    protected function getValidExportFilters()
    {
        return [
            'id_veiculo',
            'km_realizacao',
            'data_inclusao_inicial',
            'data_inclusao_fim',
            'data_alteracao_fim'
        ];
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_km_comodato' => 'Código',
            'data_inclusao' => 'Data de Inclusão',
            'data_alteracao' => 'Data de Alteração',
            'veiculo.placa' => 'Placa',
            'data_de_realizacao' => 'Data da Realização',
            'km_realizacao' => 'KM Realização',
            'horimetro' => 'Horimetro',
        ];

        return $this->exportToExcel($request, $query, $columns, 'KM_Comodato', $this->getValidExportFilters());
    }

    /**
     * Exportar para CSV
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportCsv(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_km_comodato' => 'Codigo',
            'data_inclusao' => 'Data de Inclusao',
            'data_alteracao' => 'Data de Alteracao',
            'veiculo.placa' => 'Placa',
            'data_de_realizacao' => 'Data da Realizacao',
            'km_realizacao' => 'KM Realizacao',
            'horimetro' => 'Horimetro',
        ];

        return $this->exportToCsv($request, $query, $columns, 'KM_Comodato', $this->getValidExportFilters());
    }

    /**
     * Exportar para XML
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'id' => 'id_km_comodato',
            'Data_Inclusao' => 'data_inclusao',
            'Data_Alteracao' => 'data_alteracao',
            'Placa' => 'veiculo.placa',
            'Data_de_Realizacao' => 'data_de_realizacao',
            'KM_Realizacao' => 'km_realizacao',
            'Horimetro' => 'horimetro'
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'KM_Comodatos',
            'KM_Comodato',
            'KM_Comodatos',
            $this->getValidExportFilters()
        );
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbastecimentoAtsTruckpagManual;
use App\Models\TipoCategoria;
use App\Models\TipoEquipamento;
use App\Models\Veiculo;
use App\Models\VFilial;
use App\Traits\ExportableTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AbastecimentoAtsTruckpagManualController extends Controller
{
    use ExportableTrait;

    public function index(Request $request)
    {
        // Inicia a query vazia
        $query = AbastecimentoAtsTruckpagManual::query()->orderBy('id', 'desc');

        // Verifica se algum filtro foi aplicado
        $hasFilters = $request->filled('id') ||
            $request->filled('data_inicio') ||
            $request->filled('data_final') ||
            $request->filled('placa') ||
            $request->filled('id_tipo_combustivel') ||
            $request->filled('id_categoria') ||
            $request->filled('id_tipo_equipamento') ||
            $request->filled('id_filial');

        // Se não houver filtros, retorna uma query vazia (sem resultados)
        if (! $hasFilters) {
            $abastecimentos = $query->whereRaw('1 = 0')->paginate(40);

            // Se for uma requisição HTMX, retorna apenas a tabela vazia
            if ($request->header('HX-Request')) {
                return view('admin.abastecimentosatstruckpagmanual._table', compact('abastecimentos'));
            }

            // Obter dados de referência (com cache)
            $referenceDatas = $this->getReferenceDatas();

            // Obter informações de processamento (com cache)
            $ultimosProcessamentos = $this->getUltimosProcessamentos();

            return view('admin.abastecimentosatstruckpagmanual.index', array_merge(
                compact('abastecimentos', 'ultimosProcessamentos'),
                $referenceDatas
            ));
        }

        // Aplica os filtros (se existirem)
        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        if ($request->filled('data_inicio')) {
            $dataInicio = Carbon::createFromFormat('Y-m-d', $request->data_inicio)->startOfDay();
            $query->whereDate('data_inicio', '>=', $dataInicio);
        }

        if ($request->filled('data_final')) {
            $dataFinal = Carbon::createFromFormat('Y-m-d', $request->data_final)->endOfDay();
            $query->whereDate('data_inicio', '<=', $dataFinal);
        }

        if ($request->filled('placa')) {
            $query->whereIn('placa', (array) $request->placa);
        }

        if ($request->filled('id_tipo_combustivel')) {
            $query->whereIn('id_tipo_combustivel', (array) $request->id_tipo_combustivel);
        }

        if ($request->filled('id_categoria')) {
            $query->whereIn('id_categoria', (array) $request->id_categoria);
        }

        if ($request->filled('id_tipo_equipamento')) {
            $query->whereIn('id_tipo_equipamento', (array) $request->id_tipo_equipamento);
        }

        if ($request->filled('id_filial')) {
            $query->whereIn('id_filial', (array) $request->id_filial);
        }

        // Executar a consulta com paginação
        $abastecimentos = $query->paginate(40)->appends($request->query());

        // Se for uma requisição HTMX, retorna apenas a tabela
        if ($request->header('HX-Request')) {
            return view('admin.abastecimentosatstruckpagmanual._table', compact('abastecimentos'));
        }

        // Obter dados de referência (com cache)
        $referenceDatas = $this->getReferenceDatas();

        // Obter informações de processamento (com cache)
        $ultimosProcessamentos = $this->getUltimosProcessamentos();

        // Retorna a view completa com todos os dados
        return view('admin.abastecimentosatstruckpagmanual.index', array_merge(
            compact('abastecimentos', 'ultimosProcessamentos'),
            $referenceDatas
        ));
    }

    protected function buildQuery(Request $request)
    {
        $query = AbastecimentoAtsTruckpagManual::query();

        // Apply filters
        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        if ($request->filled('data_inicio')) {
            $dataInicio = Carbon::createFromFormat('Y-m-d', $request->data_inicio)->startOfDay();
            $query->whereDate('data_inicio', '>=', $dataInicio);
        }

        if ($request->filled('data_final')) {
            $dataFinal = Carbon::createFromFormat('Y-m-d', $request->data_final)->endOfDay();
            $query->whereDate('data_inicio', '<=', $dataFinal);
        }

        if ($request->filled('placa')) {
            $query->whereIn('placa', (array) $request->placa);
        }

        if ($request->filled('id_tipo_combustivel')) {
            $query->whereIn('id_tipo_combustivel', (array) $request->id_tipo_combustivel);
        }

        if ($request->filled('id_categoria')) {
            $query->whereIn('id_categoria', (array) $request->id_categoria);
        }

        if ($request->filled('id_tipo_equipamento')) {
            $query->whereIn('id_tipo_equipamento', (array) $request->id_tipo_equipamento);
        }

        if ($request->filled('id_filial')) {
            $query->whereIn('id_filial', (array) $request->id_filial);
        }

        // Se nenhum filtro foi aplicado, aplica um limite de registros ou define um período padrão
        $hasFilters = $request->filled([
            'id',
            'data_inicio',
            'data_final',
            'placa',
            'id_tipo_combustivel',
            'id_categoria',
            'id_tipo_equipamento',
            'id_filial',
        ]);

        if (! $hasFilters) {
            // Opção 1: Limitar aos registros mais recentes
            $query->latest('data_inicio')->limit(40);

            // Opção 2: Limitar a um período recente (últimos 7 dias)
            // $query->whereDate('data_inicio', '>=', now()->subDays(7));
        }

        return $query;
    }

    protected function getReferenceDatas()
    {
        return Cache::remember('abastecimento_ats_reference_datas', now()->addHours(12), function () {
            try {
                // Veículos (limitado a 100 para performance)
                $veiculos = Veiculo::where('situacao_veiculo', true)
                    ->orderBy('placa')
                    ->limit(100)
                    ->get()
                    ->map(function ($veiculo) {
                        return [
                            'value' => $veiculo->placa,
                            'label' => $veiculo->placa,
                        ];
                    })->toArray();

                // Categorias (limitado a 100 para performance)
                $categorias = TipoCategoria::getActiveForSelectPaginated(100);

                // Tipos de equipamento (limitado a 100 para performance)
                $tiposEquipamento = TipoEquipamento::orderBy('descricao_tipo')
                    ->limit(100)
                    ->get()
                    ->map(function ($tipo) {
                        return [
                            'value' => $tipo->id_tipo_equipamento,
                            'label' => $tipo->descricao_tipo,
                        ];
                    })->toArray();

                // Filiais (limitado a 100 para performance)
                $filiais = VFilial::orderBy('name')
                    ->limit(100)
                    ->get()
                    ->map(function ($filial) {
                        return [
                            'value' => $filial->id,
                            'label' => $filial->name,
                        ];
                    })->toArray();

                // Tipos de combustível (array estático)
                $tiposCombustivel = collect([
                    1 => 'GASOLINA',
                    2 => 'ALCOOL',
                    3 => 'GNV',
                    4 => 'DIESEL',
                    5 => 'DIESEL COMUM',
                    20 => 'ARLA',
                ])->map(function ($label, $value) {
                    return [
                        'value' => $value,
                        'label' => $label,
                    ];
                })->values()->toArray();

                return [
                    'veiculos' => $veiculos,
                    'categorias' => $categorias,
                    'tiposEquipamento' => $tiposEquipamento,
                    'filiais' => $filiais,
                    'tiposCombustivel' => $tiposCombustivel,
                ];
            } catch (\Exception $e) {
                Log::error('Erro ao obter dados de referência: ' . $e->getMessage());

                return [
                    'veiculos' => [],
                    'categorias' => [],
                    'tiposEquipamento' => [],
                    'filiais' => [],
                    'tiposCombustivel' => [],
                ];
            }
        });
    }

    protected function getUltimosProcessamentos()
    {
        return Cache::remember('ultimos_processamentos_ats', now()->addMinutes(30), function () {
            try {
                $truckpag = DB::connection('pgsql')->selectOne('SELECT MAX(datatransacao) as ultimo_abastecimento_truck_pag FROM abastecimento_truck_pag');
                $integracao = DB::connection('pgsql')->selectOne('SELECT MAX(data_inclusao) as ultimo_abastecimento_integracao FROM abastecimento_integracao');

                return (object) [
                    'ultimo_abastecimento_truck_pag' => $truckpag->ultimo_abastecimento_truck_pag ?? null,
                    'ultimo_abastecimento_integracao' => $integracao->ultimo_abastecimento_integracao ?? null,
                ];
            } catch (Exception $e) {
                Log::error('Erro ao buscar últimos processamentos: ' . $e->getMessage());

                return null;
            }
        });
    }

    public function clearCache()
    {
        Cache::forget('abastecimento_ats_reference_datas');
        Cache::forget('ultimos_processamentos_ats');

        return redirect()->back()->with('success', 'Cache limpo com sucesso!');
    }

    public function enviarInconsistencia($id)
    {
        try {
            $abastecimento = AbastecimentoAtsTruckpagManual::findOrFail($id);

            if ($abastecimento->tipo == 'ABASTECIMENTO VIA ATS') {
                DB::connection('pgsql')
                    ->table('abastecimento_integracao')
                    ->where('id_abastecimento_integracao', $id)
                    ->update([
                        'km_abastecimento' => 0,
                        'km_anterior' => 0,
                        'tratado' => false,
                    ]);
            } elseif ($abastecimento->tipo == 'ABASTECIMENTO VIA TRUCKPAG') {
                DB::connection('pgsql')
                    ->table('abastecimento_truck_pag')
                    ->where('transacao', $id)
                    ->update([
                        'hodometro' => '0',
                        'km_anterior' => 0,
                        'tratado' => false,
                    ]);
            }

            // Limpar o cache dos últimos processamentos
            Cache::forget('ultimos_processamentos_ats');

            return response()->json([
                'success' => true,
                'message' => 'Abastecimento enviado para inconsistência com sucesso!',
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao enviar para inconsistência: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar para inconsistência: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function buildExportQuery(Request $request)
    {
        return $this->buildQuery($request);
    }

    protected function getValidExportFilters()
    {
        return [
            'id',
            'data_inicio',
            'data_final',
            'placa',
            'id_tipo_combustivel',
            'id_categoria',
            'id_tipo_equipamento',
            'id_filial',
        ];
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = $this->buildExportQuery($request);

            if (! $this->hasAnyFilter($request, $this->getValidExportFilters())) {
                return redirect()->back()->with([
                    'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                    'export_error' => true,
                ]);
            }

            if ($request->has('confirmed') || ! $this->exceedsExportLimit($query, 500)) {
                $data = $query->get();

                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');
                $pdf->loadView('admin.abastecimentosatstruckpagmanual.pdf', compact('data'));

                return $pdf->download('abastecimentos_ats_' . date('Y-m-d_His') . '.pdf');
            } else {
                $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

                return redirect()->back()->with([
                    'warning' => 'Você está tentando exportar mais de 500 registros, o que pode levar mais tempo.',
                    'export_confirmation' => true,
                    'export_url' => $currentUrl,
                ]);
            }
        } catch (Exception $e) {
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()->with([
                'error' => 'Erro ao gerar o PDF: ' . $e->getMessage(),
                'export_error' => true,
            ]);
        }
    }

    public function exportCsv(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id' => 'Código',
            'descricao_bomba' => 'Bomba/Posto',
            'tipocombustivel' => 'Combustível',
            'placa' => 'Placa',
            'data_inicio' => 'Data Abastecimento',
            'volume' => 'Volume (L)',
            'km_anterior' => 'Km Anterior',
            'km_abastecimento' => 'Km Abastecimento',
            'km_rodado' => 'Km Rodado',
            'media' => 'Média (Km/L)',
            'valor_litro' => 'Valor por Litro',
            'valor_total' => 'Valor Total',
            'nome_filial' => 'Filial',
            'descricao_departamento' => 'Departamento',
            'tipo' => 'Origem',
        ];

        return $this->exportToCsv($request, $query, $columns, 'abastecimentos_ats', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id' => 'Código',
            'descricao_bomba' => 'Bomba/Posto',
            'tipocombustivel' => 'Combustível',
            'placa' => 'Placa',
            'data_inicio' => 'Data Abastecimento',
            'volume' => 'Volume (L)',
            'km_anterior' => 'Km Anterior',
            'km_abastecimento' => 'Km Abastecimento',
            'km_rodado' => 'Km Rodado',
            'media' => 'Média (Km/L)',
            'valor_litro' => 'Valor por Litro',
            'valor_total' => 'Valor Total',
            'nome_filial' => 'Filial',
            'descricao_departamento' => 'Departamento',
            'tipo' => 'Origem',
        ];

        return $this->exportToExcel($request, $query, $columns, 'abastecimentos_ats', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'id' => 'id',
            'bomba' => 'descricao_bomba',
            'combustivel' => 'tipocombustivel',
            'placa' => 'placa',
            'data_abastecimento' => 'data_inicio',
            'volume' => 'volume',
            'km_anterior' => 'km_anterior',
            'km_abastecimento' => 'km_abastecimento',
            'km_rodado' => 'km_rodado',
            'media' => 'media',
            'valor_litro' => 'valor_litro',
            'valor_total' => 'valor_total',
            'filial' => 'nome_filial',
            'departamento' => 'descricao_departamento',
            'origem' => 'tipo',
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'abastecimentos_ats',
            'abastecimento',
            'abastecimentos',
            $this->getValidExportFilters()
        );
    }
}

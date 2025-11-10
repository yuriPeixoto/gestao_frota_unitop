<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AjusteKmAbastecimento;
use App\Models\Veiculo;
use App\Models\PermissaoKmManual;
use App\Models\TipoCombustivel;
use App\Traits\ExportableTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AjusteKmAbastecimentoController extends Controller
{
    use ExportableTrait;

    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = AjusteKmAbastecimento::query()
            ->with('veiculo');

        if ($request->filled('id_ajuste_km_abastecimento')) {
            $query->where('id_ajuste_km_abastecimento', $request->id_ajuste_km_abastecimento);
        }

        if ($request->filled('data_inicial')) {
            $query->whereDate('data_abastecimento', '>=', Carbon::parse($request->data_inicial));
        }

        if ($request->filled('data_final')) {
            $query->whereDate('data_abastecimento', '<=', Carbon::parse($request->data_final));
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('tipo_combustivel')) {
            $query->where('tipo_combustivel', $request->tipo_combustivel);
        }

        $ajustes = $query->orderBy('id_ajuste_km_abastecimento', 'desc')
            ->paginate(20)
            ->appends($request->query());

        if ($request->header('HX-Request')) {
            return view('admin.ajustekm._table', compact('ajustes'));
        }

        $referenceDatas = $this->getReferenceDatas();

        return view('admin.ajustekm.index', array_merge(
            compact('ajustes'),
            $referenceDatas
        ));
    }

    public function getReferenceDatas()
    {
        return Cache::remember('ajustekm_reference_datas', now()->addHours(12), function () {
            return [
                'veiculosFrequentes' => Veiculo::where('situacao_veiculo', true)
                    ->orderBy('placa')
                    ->limit(20)
                    ->get(['id_veiculo as value', 'placa as label']),

                'tiposCombustivel' => TipoCombustivel::orderBy('descricao')
                    ->get(),

                'permissoesKm' => PermissaoKmManual::orderBy('id_permissao_km_manual', 'desc')
                    ->limit(20)
                    ->get()
            ];
        });
    }

    public function create()
    {
        // Obter os veículos frequentes no mesmo formato usado no index
        $veiculosFrequentes = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->limit(20)
            ->get(['id_veiculo as value', 'placa as label']);

        $tiposCombustivel = TipoCombustivel::orderBy('descricao')->get();

        return view('admin.ajustekm.create', compact('veiculosFrequentes', 'tiposCombustivel'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'data_abastecimento' => 'required|date',
                'id_veiculo' => 'required|exists:veiculo,id_veiculo',
                'km_abastecimento' => 'required|numeric|min:0',
                'arla' => 'required|in:1,2',
            ]);

            // Verificar KM anterior para validação de autonomia (2800 km)
            $idVeiculo = $validated['id_veiculo'];
            $kmAbastecimento = $validated['km_abastecimento'];

            // Obter o KM anterior do veículo
            $kmAnterior = $this->getUltimoKmVeiculo($idVeiculo);
            $departamento = $this->getDepartamentoVeiculo($idVeiculo);

            // Validação de departamento (Presidência = 20 é exceção)
            if ($departamento != 20) {
                // Verificar autonomia somente se tiver KM anterior
                if (!empty($kmAnterior) && (($kmAbastecimento - $kmAnterior) >= 2800)) {
                    return back()
                        ->withInput()
                        ->with('error', 'Atenção: KM do Abastecimento menos o KM anterior é maior que a autonomia do veículo');
                }
            }

            DB::beginTransaction();

            // Adicionar campos obrigatórios
            $validated['data_inclusao'] = now();
            $validated['tipo_combustivel'] = 'DIESEL';

            // Criar o registro
            $ajuste = AjusteKmAbastecimento::create([
                'data_inclusao' => now(),
                'data_abastecimento' => $validated['data_abastecimento'],
                'id_veiculo' => $validated['id_veiculo'],
                'km_abastecimento' => $validated['km_abastecimento'],
                'tipo_combustivel' => 'DIESEL',
            ]);

            // Se a opção "Sim" foi selecionada, adicionar registro para ARLA
            if ($validated['arla'] == '1') {
                AjusteKmAbastecimento::create([
                    'data_inclusao' => now(),
                    'data_abastecimento' => $validated['data_abastecimento'],
                    'id_veiculo' => $validated['id_veiculo'],
                    'km_abastecimento' => $validated['km_abastecimento'],
                    'tipo_combustivel' => 'ARLA',
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.ajustekm.index')
                ->with('success', 'Ajuste de KM cadastrado com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cadastrar ajuste de KM: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar ajuste de KM: ' . $e->getMessage());
        }
    }

    // Nova função para a página "Informar o KM do Abastecimento"
    public function informarKm()
    {
        // Obter os veículos frequentes no mesmo formato usado no index
        $veiculosFrequentes = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->limit(20)
            ->get(['id_veiculo as value', 'placa as label']);

        $tiposCombustivel = TipoCombustivel::orderBy('descricao')->get();

        return view('admin.ajustekm.informar-km', compact('veiculosFrequentes', 'tiposCombustivel'));
    }

    // Nova função para processar o formulário de "Informar o KM do Abastecimento"
    public function salvarKm(Request $request)
    {
        return $this->store($request);
    }

    public function edit(AjusteKmAbastecimento $ajusteKm)
    {
        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->get();

        $tiposCombustivel = TipoCombustivel::orderBy('descricao')->get();

        return view('admin.ajustekm.edit', compact('ajusteKm', 'veiculos', 'tiposCombustivel'));
    }

    public function update(Request $request, AjusteKmAbastecimento $ajusteKm)
    {
        try {
            $validated = $this->validateAjusteKm($request);

            DB::beginTransaction();

            $validated['data_alteracao'] = now();
            $ajusteKm->update($validated);

            DB::commit();

            return redirect()
                ->route('admin.ajustekm.index')
                ->with('success', 'Ajuste de KM atualizado com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar ajuste de KM: ' . $e->getMessage());
        }
    }

    protected function validateAjusteKm(Request $request)
    {
        return $request->validate([
            'data_abastecimento' => 'required|date',
            'id_veiculo' => 'required|exists:veiculo,id_veiculo',
            'km_abastecimento' => 'required|integer|min:1',
            'tipo_combustivel' => 'required|string',
            'id_permissao_km_manual' => 'nullable|exists:permissaokmmanual,id_permissao_km_manual',
        ]);
    }

    // Função para obter o último KM do veículo
    protected function getUltimoKmVeiculo($idVeiculo)
    {
        try {
            $result = DB::connection('pgsql')
                ->table('v_veiculo_historico_km')
                ->where('id_veiculo', $idVeiculo)
                ->orderByDesc(DB::connection('pgsql')->raw('data_registro::DATE'))
                ->limit(1)
                ->first();

            return $result ? $result->km : null;
        } catch (Exception $e) {
            Log::error('Erro ao obter último KM do veículo: ' . $e->getMessage());
            return null;
        }
    }

    // Função para obter o departamento do veículo
    protected function getDepartamentoVeiculo($idVeiculo)
    {
        try {
            $result = DB::connection('pgsql')
                ->table('veiculo')
                ->where('id_veiculo', $idVeiculo)
                ->limit(1)
                ->first();

            return $result ? $result->id_departamento : null;
        } catch (Exception $e) {
            Log::error('Erro ao obter departamento do veículo: ' . $e->getMessage());
            return null;
        }
    }

    protected function buildExportQuery(Request $request)
    {
        $query = AjusteKmAbastecimento::query()
            ->with('veiculo');

        if ($request->filled('id_ajuste_km_abastecimento')) {
            $query->where('id_ajuste_km_abastecimento', $request->id_ajuste_km_abastecimento);
        }

        if ($request->filled('data_inicial')) {
            $query->whereDate('data_abastecimento', '>=', Carbon::parse($request->data_inicial));
        }

        if ($request->filled('data_final')) {
            $query->whereDate('data_abastecimento', '<=', Carbon::parse($request->data_final));
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('tipo_combustivel')) {
            $query->where('tipo_combustivel', $request->tipo_combustivel);
        }

        return $query->orderBy('id_ajuste_km_abastecimento', 'desc');
    }

    protected function getValidExportFilters()
    {
        return [
            'id_ajuste_km_abastecimento',
            'data_inicial',
            'data_final',
            'id_veiculo',
            'tipo_combustivel'
        ];
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = $this->buildExportQuery($request);

            if (!$this->hasAnyFilter($request, $this->getValidExportFilters())) {
                return redirect()->back()->with([
                    'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                    'export_error' => true
                ]);
            }

            if ($request->has('confirmed') || !$this->exceedsExportLimit($query, 500)) {
                $data = $query->get();

                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');
                $pdf->loadView('admin.ajustekm.pdf', compact('data'));

                return $pdf->download('ajustes_km_' . date('Y-m-d_His') . '.pdf');
            } else {
                $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

                return redirect()->back()->with([
                    'warning' => "Você está tentando exportar mais de 500 registros, o que pode levar mais tempo.",
                    'export_confirmation' => true,
                    'export_url' => $currentUrl
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());

            return redirect()->back()->with([
                'error' => 'Erro ao gerar o PDF: ' . $e->getMessage(),
                'export_error' => true
            ]);
        }
    }

    public function exportCsv(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_ajuste_km_abastecimento' => 'Código',
            'veiculo.placa' => 'Placa',
            'data_inclusao' => 'Data Inclusão',
            'data_alteracao' => 'Data Alteração',
            'data_abastecimento' => 'Data Abastecimento',
            'km_abastecimento' => 'KM',
            'tipo_combustivel' => 'Diesel e Arla',
            'id_abastecimento_ats' => 'Cód. Abastecimento ATS'
        ];

        return $this->exportToCsv($request, $query, $columns, 'ajustes_km', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_ajuste_km_abastecimento' => 'Código',
            'veiculo.placa' => 'Placa',
            'data_inclusao' => 'Data Inclusão',
            'data_alteracao' => 'Data Alteração',
            'data_abastecimento' => 'Data Abastecimento',
            'km_abastecimento' => 'KM',
            'tipo_combustivel' => 'Diesel e Arla',
            'id_abastecimento_ats' => 'Cód. Abastecimento ATS'
        ];

        return $this->exportToExcel($request, $query, $columns, 'ajustes_km', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'id' => 'id_ajuste_km_abastecimento',
            'data_inclusao' => 'data_inclusao',
            'data_alteracao' => 'data_alteracao',
            'data_abastecimento' => 'data_abastecimento',
            'placa' => 'veiculo.placa',
            'km_abastecimento' => 'km_abastecimento',
            'permissao_km_manual' => 'id_permissao_km_manual',
            'diesel_e_arla' => 'tipo_combustivel',
            'id_abastecimento_ats' => 'Cód. Abastecimento ATS'
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'ajustes_km',
            'ajuste',
            'ajustes_km',
            $this->getValidExportFilters()
        );
    }

    public function getDadosVeiculo($id)
    {
        $veiculo = Veiculo::findOrFail($id);
        return response()->json([
            'km_atual' => $veiculo->km_atual
        ]);
    }
}

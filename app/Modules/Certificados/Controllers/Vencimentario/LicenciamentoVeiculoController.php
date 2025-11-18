<?php

namespace App\Modules\Certificados\Controllers\Vencimentario;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Filial;
use App\Modules\Certificados\Models\LicenciamentoVeiculo;
use App\Models\Veiculo;
use App\Traits\ExportableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class LicenciamentoVeiculoController extends Controller
{

    use ExportableTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $pageSize = $request->input('pageSize', 10);

        $query = LicenciamentoVeiculo::query();

        if ($request->filled('id_licenciamento')) {
            $query->where('id_licenciamento', $request->id_licenciamento);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('id_licenciamento')) {
            $query->where('id_licenciamento', $request->id_licenciamento);
        }

        if ($request->filled('data_emissao_crlv')) {
            $query->where('data_emissao_crlv', $request->data_emissao_crlv);
        }

        if ($request->filled('crlv')) {
            $query->where('crlv', $request->crlv);
        }

        if ($request->filled('data_vencimento')) {
            $query->where('data_vencimento', $request->data_vencimento);
        }

        if ($request->filled('ano_licenciamento')) {
            $query->where('ano_licenciamento', $request->ano_licenciamento);
        }

        if ($request->filled('situacao')) {
            $query->where('situacao', $request->situacao);
        }

        if ($request->filled('status')) {
            if ($request->status === 'ativo') {
                $query->ativos();
            } else {
                $query->inativos();
            }
        } else {
            $query->todos();
        }

        $licenciamentoVeiculos = $query->latest('id_licenciamento')
            ->paginate($pageSize)
            ->appends($request->query());


        $totalRegistros = $licenciamentoVeiculos->total();

        $veiculosFrequentes = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->limit(20)
            ->get(['id_veiculo as value', 'placa as label']);

        $crlv = LicenciamentoVeiculo::whereNotNull('crlv')
            ->orderBy('id_licenciamento', 'desc')
            ->limit(20)
            ->get(['crlv as value', 'crlv as label']);

        $ano_licenciamento = LicenciamentoVeiculo::whereNotNull('ano_licenciamento')
            ->selectRaw('ano_licenciamento as label, MIN(ano_licenciamento) as value')
            ->groupBy('ano_licenciamento')
            ->orderBy('label', 'desc')
            ->limit(20)
            ->get();

        return view('admin.licenciamentoveiculos.index', compact(
            'licenciamentoVeiculos',
            'totalRegistros',
            'veiculosFrequentes',
            'crlv',
            'ano_licenciamento'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $placasData = Veiculo::select('placa as label', 'id_veiculo as value')
            ->where('placa', '!=', null)
            ->where('situacao_veiculo', '=', 'True')
            ->orderBy('placa')->get()->toArray();

        return view('admin.licenciamentoveiculos.create', compact('placasData'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        $licenciamentoVeiculosData = $request->validate([
            'id_veiculo'               => ['required'],
            'ano_licenciamento'        => ['integer', 'min:4'],
            'crlv'                     => ['integer', 'min:12'],
            'valor_previsto_valor'     => ['string'],
            'valor_pago_licenciamento' => ['string'],
        ]);

        try {
            $situacao = 'A Vencer';
            if ($request->filled('valor_pago_licenciamento')) {
                $situacao = 'Quitado';
            }

            if ($request->filled('id_veiculo')) {
                $placa = Veiculo::where('id_veiculo', $request->id_veiculo)->first()->placa;
            }


            DB::beginTransaction();
            $licenciamentoVeiculos = new LicenciamentoVeiculo();

            $licenciamentoVeiculos->data_inclusao            = now();
            $licenciamentoVeiculos->id_veiculo               = $licenciamentoVeiculosData['id_veiculo'];
            $licenciamentoVeiculos->ano_licenciamento        = $licenciamentoVeiculosData['ano_licenciamento'];
            $licenciamentoVeiculos->data_emissao_crlv        = $request->data_emissao_crlv;
            $licenciamentoVeiculos->crlv                     = $licenciamentoVeiculosData['crlv'];
            $licenciamentoVeiculos->data_vencimento          = $request->data_emissao_crlv;
            $licenciamentoVeiculos->valor_previsto_valor     = SanitizeToDouble($licenciamentoVeiculosData['valor_previsto_valor']);
            $licenciamentoVeiculos->valor_pago_licenciamento = SanitizeToDouble($licenciamentoVeiculosData['valor_pago_licenciamento']);
            $licenciamentoVeiculos->situacao                 = $situacao;
            $licenciamentoVeiculos->placa                    = $placa;

            $licenciamentoVeiculos->save();

            DB::commit();

            // Adicionando notificação de sucesso
            return redirect()->route('admin.licenciamentoveiculos.index')->with('notification', [
                'title' => 'Sucesso!',
                'message' => 'Licenciamento de veículo cadastrado com sucesso!',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar licenciamento veiculo: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('notification', [
                'title' => 'Erro!',
                'message' => 'Erro ao salvar licenciamento de veículo.',
                'type' => 'error'
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LicenciamentoVeiculo $licenciamentoVeiculos)
    {
        $licenciamentoVeiculos = LicenciamentoVeiculo::findOrFail($licenciamentoVeiculos);
        return view('admin.licenciamentoveiculos.show', compact('licenciamentoVeiculos'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $licenciamentoveiculos = LicenciamentoVeiculo::findOrFail($id);
        // dd($licenciamentoveiculos);

        $placasData = Veiculo::select('placa as label', 'id_veiculo as value')
            ->where('placa', '!=', null)
            ->where('situacao_veiculo', '=', 'True')
            ->orderBy('placa')->get()->toArray();

        $placaSelecionada = Veiculo::where('id_veiculo', $licenciamentoveiculos->id_veiculo)
            ->select('placa as label', 'id_veiculo as value')
            ->first();

        return view('admin.licenciamentoveiculos.edit', compact('licenciamentoveiculos', 'placasData', 'placaSelecionada'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $licenciamentoVeiculos)
    {

        $licenciamentoVeiculosRules = [
            'id_veiculo'               => 'required',
            'ano_licenciamento'        => 'integer|min:4',
            'crlv'                     => 'integer|min:12',
        ];

        // $dataFormatada = Carbon::createFromFormat('d/m/Y', $dataBr)->format('Y-m-d');
        $licenciamentoVeiculosData = $request->validate($licenciamentoVeiculosRules);

        $licenciamentoVeiculosData['situacao'] = 'A Vencer';
        if ($request->filled('valor_pago_licenciamento')) {
            $licenciamentoVeiculosData['situacao'] = 'Quitado';
        }

        if ($request->filled('id_veiculo')) {
            $licenciamentoVeiculosData['placa'] = Veiculo::where('id_veiculo', $request->id_veiculo)->first()->placa;
        }

        if (isset($request['valor_previsto_valor'])) {
            $licenciamentoVeiculosData['valor_previsto_valor'] = SanitizeToDouble($request['valor_previsto_valor']);
        }

        if (isset($request['valor_pago_licenciamento'])) {
            $licenciamentoVeiculosData['valor_pago_licenciamento'] = SanitizeToDouble($request['valor_pago_licenciamento']);
        }

        $licenciamentoVeiculosData['data_alteracao'] = now();

        try {
            DB::beginTransaction();

            $licenciamentoVeiculosupdate = LicenciamentoVeiculo::findOrFail($licenciamentoVeiculos);
            $licenciamentoVeiculosupdate->update($licenciamentoVeiculosData);

            DB::commit();

            // Adicionando notificação de sucesso
            return redirect()->route('admin.licenciamentoveiculos.index')->with('notification', [
                'title' => 'Sucesso!',
                'message' => 'Licenciamento de veículo atualizado com sucesso!',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na atualização:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withInput()->with('notification', [
                'title' => 'Erro!',
                'message' => 'Erro ao atualizar licenciamento de veículo.',
                'type' => 'error'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $licenciamentoVeiculos = LicenciamentoVeiculo::findOrFail($id);
        $licenciamentoVeiculos->delete();

        return redirect()->route('admin.licenciamentoveiculos.index')->with('success', 'Licenciamento desativado com sucesso!');
    }


    public function getVehicleData(Request $request)
    {
        try {
            $veiculo = Veiculo::select('id_veiculo', 'id_departamento', 'id_filial', 'id_base_veiculo')
                ->with([
                    'departamentoVeiculo:id_departamento,descricao_departamento',
                    'filial:id,name',
                    'baseVeiculo:id_base_veiculo,descricao_base'
                ])
                ->where('id_veiculo', $request->placa)
                ->firstOrFail();

            return response()->json([
                'departamento' => $veiculo->departamentoVeiculo->descricao_departamento ?? 'Não informado',
                'filial' => $veiculo->filial->name ?? 'Não informado',
                'locacao' => $veiculo->baseVeiculo->descricao_base ?? 'Não informado',
                'id_departamento' => $veiculo->id_departamento,
                'id_filial' => $veiculo->id_filial
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do veículo: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar dados do veículo'], 500);
        }
    }

    protected function buildExportQuery(Request $request)
    {

        $query = LicenciamentoVeiculo::query()
            ->with('veiculo');


        // dd($query->veiculo);

        if ($request->filled('id_licenciamento')) {
            $query->where('id_licenciamento', $request->id_licenciamento);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('id_licenciamento')) {
            $query->where('id_licenciamento', $request->id_licenciamento);
        }

        if ($request->filled('data_emissao_crlv')) {
            $query->where('data_emissao_crlv', $request->data_emissao_crlv);
        }

        if ($request->filled('crlv')) {
            $query->where('crlv', $request->crlv);
        }

        if ($request->filled('data_vencimento')) {
            $query->where('data_vencimento', $request->data_vencimento);
        }

        if ($request->filled('ano_licenciamento')) {
            $query->where('ano_licenciamento', $request->ano_licenciamento);
        }

        // Se nenhum filtro foi aplicado, aplica um limite de registros ou define um período padrão
        $hasFilters = $request->filled([
            'id_licenciamento',
            'id_veiculo',
            'id_licenciamento',
            'data_emissao_crlv',
            'crlv',
            'data_vencimento',
            'ano_licenciamento',
            'placa'
        ]);

        if (!$hasFilters) {
            // Opção 1: Limitar aos registros mais recentes
            $query->latest('data_emissao_crlv')->limit(40);

            $query->latest('data_vencimento')->limit(40);

            // Opção 2: Limitar a um período recente (últimos 7 dias)
            // $query->whereDate('data_inicio', '>=', now()->subDays(7));
        }

        return $query;
    }

    protected function getValidExportFilters()
    {
        return [
            'id_licenciamento',
            'id_veiculo',
            'id_licenciamento',
            'data_emissao_crlv',
            'crlv',
            'data_vencimento',
            'ano_licenciamento',
        ];
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = $this->buildExportQuery($request);

            // Se a exportação direta pelo trait não funcionar, tente um método alternativo
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
                $pdf->loadView('admin.licenciamentoveiculos.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('licenciamentoveiculos_' . date('Y-m-d_His') . '.pdf');
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

    public function exportCsv(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_licenciamento' => 'Código',
            'veiculo.placa' => 'Placa',
            'ano_licenciamento' => 'Ano Licenciamento',
            'data_emissao_crlv' => 'Data Emissão CRLV',
            'crlv' => 'CRLV',
            'data_vencimento' => 'Data Vencimento',
            'situacao' => 'Situação'
        ];

        return $this->exportToCsv($request, $query, $columns, 'licenciamentoveiculos', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [

            'id_licenciamento' => 'Código',
            'veiculo.placa' => 'Placa',
            'ano_licenciamento' => 'Ano Licenciamento',
            'data_emissao_crlv' => 'Data Emissão CRLV',
            'crlv' => 'CRLV',
            'data_vencimento' => 'Data Vencimento',
            'situacao' => 'Situação'
        ];

        return $this->exportToExcel($request, $query, $columns, 'licenciamentoveiculos', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'codigo' => 'id_licenciamento',
            'placa' => 'veiculo.placa',
            'ano_licenciamento' => 'ano_licenciamento',
            'data_emissão_crlv' => 'data_emissao_crlv',
            'crlv' => 'crlv',
            'data_vencimento' => 'data_vencimento',
            'situacao' => 'situacao',
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'licenciamentoveiculos',
            'licenciamentoveiculo',
            'licenciamentoveiculos',
            $this->getValidExportFilters()
        );
    }

    public function cloneLicenciamento(string $id)
    {
        LOG::DEBUG('Replicando Licenciamento com ID: ' . $id);

        try {
            DB::beginTransaction();
            $originalLic = LicenciamentoVeiculo::findOrFail($id);

            $clonedLic = $originalLic->replicate();
            $clonedLic->data_inclusao = now();
            $clonedLic->placa = 'clone_' . time() . '_' . $originalLic->placa;
            $clonedLic->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Registro replicado com sucesso, o novo regsitro tem a placa: ' . $clonedLic->placa
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao clonar licenciamento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

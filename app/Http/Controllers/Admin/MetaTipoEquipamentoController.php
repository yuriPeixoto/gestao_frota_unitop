<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MetaPorTipoEquipamento;
use App\Models\TipoEquipamento;
use App\Models\VFilial;
use App\Traits\ExportableTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MetaTipoEquipamentoController extends Controller
{
    use ExportableTrait;

    public function index(Request $request)
    {
        // Validar datas antes de processar a consulta
        $dataInicial = null;
        $dataFinal = null;

        // Validar data inicial, se fornecida
        if ($request->filled('data_inicial')) {
            try {
                $dataInicial = Carbon::parse($request->data_inicial)->format('Y-m-d');
            } catch (\Exception $e) {
                // Log do erro para diagnóstico
                Log::warning('Data inicial inválida fornecida: ' . $request->data_inicial);

                // Se estamos respondendo a uma requisição AJAX/HTMX, retornar resposta JSON
                if ($request->expectsJson() || $request->header('HX-Request')) {
                    return response()->json([
                        'error' => 'Data inicial inválida'
                    ], 422);
                }

                // Para requisições normais, redirecionar com mensagem de erro
                return redirect()->back()
                    ->withInput($request->except(['data_inicial']))
                    ->with('error', 'Data inicial inválida. Por favor, utilize o formato correto.');
            }
        }

        // Validar data final, se fornecida
        if ($request->filled('data_final')) {
            try {
                $dataFinal = Carbon::parse($request->data_final)->format('Y-m-d');
            } catch (\Exception $e) {
                // Log do erro para diagnóstico
                Log::warning('Data final inválida fornecida: ' . $request->data_final);

                // Se estamos respondendo a uma requisição AJAX/HTMX, retornar resposta JSON
                if ($request->expectsJson() || $request->header('HX-Request')) {
                    return response()->json([
                        'error' => 'Data final inválida'
                    ], 422);
                }

                // Para requisições normais, redirecionar com mensagem de erro
                return redirect()->back()
                    ->withInput($request->except(['data_final']))
                    ->with('error', 'Data final inválida. Por favor, utilize o formato correto.');
            }
        }

        // Verificar se a data inicial é posterior à data final
        if ($dataInicial && $dataFinal && $dataInicial > $dataFinal) {
            if ($request->expectsJson() || $request->header('HX-Request')) {
                return response()->json([
                    'error' => 'A data inicial não pode ser posterior à data final'
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'A data inicial não pode ser posterior à data final.');
        }

        // Construir a consulta com as datas validadas
        $query = MetaPorTipoEquipamento::with(['filial', 'tipoEquipamento']);

        if ($request->filled('id_meta')) {
            $query->where('id_meta', $request->id_meta);
        }

        if ($dataInicial) {
            $query->whereDate('data_inicial', '>=', $dataInicial);
        }

        if ($dataFinal) {
            $query->whereDate('data_final', '<=', $dataFinal);
        }

        if ($request->filled('vlr_meta')) {
            $query->where('vlr_meta', $request->vlr_meta);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('id_equipamento')) {
            $query->where('id_equipamento', $request->id_equipamento);
        }

        $metaTipoEquipamentos = $query->latest('id_meta')
            ->paginate(10)
            ->appends($request->except(['data_inicial', 'data_final'])); // Reaplica parâmetros exceto datas inválidas

        // Adiciona as datas validadas para manter a consistência na paginação
        if ($dataInicial) {
            $metaTipoEquipamentos->appends(['data_inicial' => $dataInicial]);
        }

        if ($dataFinal) {
            $metaTipoEquipamentos->appends(['data_final' => $dataFinal]);
        }

        if ($request->header('HX-Request')) {
            return view('admin.metatipoequipamentos._table', compact('metaTipoEquipamentos'));
        }

        $filiais = Cache::remember('filiais_metaportipoequipamento', now()->addHours(12), function () {
            return VFilial::orderBy('name')
                ->get(['id as value', 'name as label']);
        });

        $tiposEquipamento = Cache::remember('tipos_equipamento_metaportipoequipamento', now()->addHours(12), function () {
            return TipoEquipamento::orderBy('descricao_tipo')
                ->get(['id_tipo_equipamento as value', 'descricao_tipo as label']);
        });

        return view('admin.metatipoequipamentos.index', compact(
            'metaTipoEquipamentos',
            'filiais',
            'tiposEquipamento'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $filiais = VFilial::orderBy('name')
            ->get(['id as value', 'name as label']);

        $tiposEquipamento = TipoEquipamento::orderBy('descricao_tipo')
            ->get(['id_tipo_equipamento as value', 'descricao_tipo as label']);

        return view('admin.metatipoequipamentos.create', compact('filiais', 'tiposEquipamento'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'data_inicial' => 'nullable|date',
                'data_final' => 'nullable|date',
                'id_filial' => 'nullable|integer',
                'vlr_meta' => 'nullable|numeric',
                'ativo' => 'boolean',
                'id_equipamento' => 'nullable|integer',
            ]);

            DB::beginTransaction();

            $metaTipoEquipamento = new MetaPorTipoEquipamento();

            $metaTipoEquipamento->data_inclusao  = now();
            $metaTipoEquipamento->data_inicial   = $validated['data_inicial'];
            $metaTipoEquipamento->data_final     = $validated['data_final'];
            $metaTipoEquipamento->id_filial      = $validated['id_filial'];
            $metaTipoEquipamento->vlr_meta       = $validated['vlr_meta'];
            $metaTipoEquipamento->ativo          = $validated['ativo'];
            $metaTipoEquipamento->id_equipamento = $validated['id_equipamento'];

            $metaTipoEquipamento->save();

            DB::commit();

            return redirect()
                ->route('admin.metatipoequipamentos.index')
                ->with('success', 'Meta por tipo de equipamento cadastrada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cadastrar a meta por tipo de equipamento: ' . $e->getMessage());

            return redirect()
                ->route('admin.metatipoequipamentos.create')
                ->withInput()
                ->with('error', 'Erro ao cadastrar a meta por tipo de equipamento: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $metaTipoEquipamento = MetaPorTipoEquipamento::with(['filial', 'tipoEquipamento'])
            ->findOrFail($id);

        return view('admin.metatipoequipamentos.show', compact('metaTipoEquipamento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $metaTipoEquipamentos = MetaPorTipoEquipamento::findOrFail($id);

        $filiais = VFilial::orderBy('name')
            ->get(['id as value', 'name as label']);

        $tiposEquipamento = TipoEquipamento::orderBy('descricao_tipo')
            ->get(['id_tipo_equipamento as value', 'descricao_tipo as label']);

        return view('admin.metatipoequipamentos.edit', compact(
            'metaTipoEquipamentos',
            'filiais',
            'tiposEquipamento'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'data_inicial' => 'nullable|date',
                'data_final' => 'nullable|date',
                'id_filial' => 'nullable|integer',
                'vlr_meta' => 'nullable|numeric',
                'ativo' => 'boolean',
                'id_equipamento' => 'nullable|integer',
            ]);

            DB::beginTransaction();

            $metaTipoEquipamento = MetaPorTipoEquipamento::findOrFail($id);

            $metaTipoEquipamento->data_alteracao = now();
            $metaTipoEquipamento->data_inicial   = $validated['data_inicial'];
            $metaTipoEquipamento->data_final     = $validated['data_final'];
            $metaTipoEquipamento->id_filial      = $validated['id_filial'];
            $metaTipoEquipamento->vlr_meta       = $validated['vlr_meta'];
            $metaTipoEquipamento->ativo          = $validated['ativo'];
            $metaTipoEquipamento->id_equipamento = $validated['id_equipamento'];

            $metaTipoEquipamento->save();

            DB::commit();

            return redirect()
                ->route('admin.metatipoequipamentos.index')
                ->with('success', 'Meta por tipo de equipamento atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar a meta por tipo de equipamento: ' . $e->getMessage());

            return redirect()
                ->route('admin.metatipoequipamentos.edit', $id)
                ->withInput()
                ->with('error', 'Erro ao atualizar a meta por tipo de equipamento: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $metaTipoEquipamento = MetaPorTipoEquipamento::findOrFail($id);
            $metaTipoEquipamento->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Meta por tipo de equipamento excluída com sucesso!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir a meta por tipo de equipamento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir a meta por tipo de equipamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build query for export functions
     */
    protected function buildExportQuery(Request $request)
    {
        // Base query com seleção de dados adequada para exportação
        $query = MetaPorTipoEquipamento::query()
            ->select(
                'meta_por_tipo_equipamento.*',
                'filiais.name as nome_filial',
                'tipoequipamento.descricao_tipo'
            )
            ->leftJoin('filiais', 'meta_por_tipo_equipamento.id_filial', '=', 'filiais.id')
            ->leftJoin('tipoequipamento', 'meta_por_tipo_equipamento.id_equipamento', '=', 'tipoequipamento.id_tipo_equipamento');

        // Aplicar filtros
        if ($request->filled('id_meta')) {
            $query->where('id_meta', $request->id_meta);
        }

        if ($request->filled('data_inicial')) {
            $query->whereDate('meta_por_tipo_equipamento.data_inicial', '>=', $request->data_inicial);
        }

        if ($request->filled('data_final')) {
            $query->whereDate('meta_por_tipo_equipamento.data_final', '<=', $request->data_final);
        }

        if ($request->filled('vlr_meta')) {
            $query->where('vlr_meta', $request->vlr_meta);
        }

        if ($request->filled('id_filial')) {
            $query->where('meta_por_tipo_equipamento.id_filial', $request->id_filial);
        }

        if ($request->filled('id_equipamento')) {
            $query->where('meta_por_tipo_equipamento.id_equipamento', $request->id_equipamento);
        }

        // Ordenação padrão
        return $query->orderBy('meta_por_tipo_equipamento.id_meta', 'desc');
    }

    protected function getValidExportFilters()
    {
        return [
            'id_meta',
            'data_inicial',
            'data_final',
            'vlr_meta',
            'id_filial',
            'id_equipamento'
        ];
    }

    /**
     * Export data to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $query = $this->buildExportQuery($request);

            // Verificar se há algum filtro aplicado
            if (!$this->hasAnyFilter($request, $this->getValidExportFilters())) {
                return redirect()->back()->with([
                    'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                    'export_error' => true
                ]);
            }

            // Verificar se o volume de dados é muito grande
            if ($request->has('confirmed') || !$this->exceedsExportLimit($query, 500)) {
                $data = $query->get();

                // Configurar opções do PDF
                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');

                // Carregar a view
                $pdf->loadView('admin.metatipoequipamentos.pdf', compact('data'));

                // Forçar download
                return $pdf->download('metas_por_tipo_equipamento_' . date('Y-m-d_His') . '.pdf');
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
            \Illuminate\Support\Facades\Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());

            return redirect()->back()->with([
                'error' => 'Erro ao gerar o PDF: ' . $e->getMessage(),
                'export_error' => true
            ]);
        }
    }

    /**
     * Export data to CSV
     */
    public function exportCsv(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_meta' => 'Código',
            'data_inclusao' => 'Data Inclusão',
            'data_inicial' => 'Data Inicial',
            'data_final' => 'Data Final',
            'vlr_meta' => 'Valor Meta',
            'nome_filial' => 'Filial',
            'descricao_tipo' => 'Tipo Equipamento',
            'ativo' => 'Ativo'
        ];

        return $this->exportToCsv($request, $query, $columns, 'metas_por_tipo_equipamento', $this->getValidExportFilters());
    }

    /**
     * Export data to Excel
     */
    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_meta' => 'Código',
            'data_inclusao' => 'Data Inclusão',
            'data_inicial' => 'Data Inicial',
            'data_final' => 'Data Final',
            'vlr_meta' => 'Valor Meta',
            'nome_filial' => 'Filial',
            'descricao_tipo' => 'Tipo Equipamento',
            'ativo' => 'Ativo'
        ];

        return $this->exportToExcel($request, $query, $columns, 'metas_por_tipo_equipamento', $this->getValidExportFilters());
    }

    /**
     * Export data to XML
     */
    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'id' => 'id_meta',
            'data_inclusao' => 'data_inclusao',
            'data_inicial' => 'data_inicial',
            'data_final' => 'data_final',
            'valor_meta' => 'vlr_meta',
            'filial' => 'nome_filial',
            'tipo_equipamento' => 'descricao_tipo',
            'ativo' => 'ativo'
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'metas_por_tipo_equipamento',
            'meta',
            'metas',
            $this->getValidExportFilters()
        );
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SeguroObrigatorio;
use App\Models\Veiculo;
use App\Models\VFilial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Traits\ExportableTrait;

class SeguroObrigatorioController extends Controller
{
    use ExportableTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = SeguroObrigatorio::query()
            ->with('veiculo');

        if ($request->filled('id_seguro_obrigatorio_veiculo')) {
            $query->where('id_seguro_obrigatorio_veiculo', $request->id_seguro_obrigatorio_veiculo);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('filial_veiculo')) {
            $query->whereHas('veiculo.filial', function ($query) use ($request) {
                $query->where('id', $request->filial_veiculo);
            });
        }

        if ($request->filled('numero_bilhete')) {
            $query->where('numero_bilhete', 'like', '%' . $request->numero_bilhete . '%');
        }

        if ($request->filled('data_vencimento_inicio')) {
            $query->whereDate('data_vencimento', '>=', $request->data_vencimento_inicio);
        }

        if ($request->filled('data_vencimento_fim')) {
            $query->whereDate('data_vencimento', '<=', $request->data_vencimento_fim);
        }

        if ($request->filled('ano_validade')) {
            $query->where('ano_validade', $request->ano_validade);
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

        $segurosObrigatorios = $query->latest('id_seguro_obrigatorio_veiculo')
            ->paginate(15)
            ->appends($request->query());


        if ($request->header('HX-Request')) {
            return view('admin.seguroobrigatorio._table', compact('segurosObrigatorios'));
        }

        $referenceDatas = $this->getReferenceDatas();

        return view('admin.seguroobrigatorio.index', array_merge(
            compact('segurosObrigatorios'),
            $referenceDatas
        ));
    }

    /**
     * Obter dados de referência para os formulários
     */
    public function getReferenceDatas()
    {
        return Cache::remember('seguro_obrigatorio_reference_datas', now()->addHours(12), function () {
            return [
                'veiculosFrequentes' => Veiculo::where('situacao_veiculo', true)
                    ->orderBy('placa')
                    ->limit(20)
                    ->get(['id_veiculo as value', 'placa as label']),

                'filiais' => VFilial::orderBy('name')
                    ->get(['id as value', 'name as label']),
            ];
        });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->get(['id_veiculo as value', 'placa as label']);

        $filiais = VFilial::orderBy('name')
            ->get(['id as value', 'name as label']);

        return view('admin.seguroobrigatorio.create', compact('veiculos', 'filiais'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validateSeguroObrigatorio($request);

            DB::beginTransaction();

            $seguroObrigatorio = new SeguroObrigatorio();
            $seguroObrigatorio->fill($validated);
            $seguroObrigatorio->data_inclusao = now();
            $seguroObrigatorio->save();

            DB::commit();

            return redirect()
                ->route('admin.seguroobrigatorio.index')
                ->with('success', 'Seguro obrigatório cadastrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error message for debugging
            Log::error('Erro ao cadastrar seguro obrigatório: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar seguro obrigatório: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $seguroObrigatorio = SeguroObrigatorio::findOrFail($id);

        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->get(['id_veiculo as value', 'placa as label']);

        $filiais = VFilial::orderBy('name')
            ->get(['id as value', 'name as label']);

        return view('admin.seguroobrigatorio.edit', compact(
            'seguroObrigatorio',
            'veiculos',
            'filiais'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $this->validateSeguroObrigatorio($request);

            DB::beginTransaction();

            $seguroObrigatorio = SeguroObrigatorio::findOrFail($id);
            $seguroObrigatorio->fill($validated);
            $seguroObrigatorio->data_alteracao = now();
            $seguroObrigatorio->save();

            DB::commit();

            return redirect()
                ->route('admin.seguroobrigatorio.index')
                ->with('success', 'Seguro obrigatório atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar seguro obrigatório: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $ipva = SeguroObrigatorio::findOrFail($id);

            if (!empty($ipva)) {
                $ipva->delete();
            }

            DB::commit();

            return response()->json([
                'notification' => [
                    'title' => 'Seguro Obrigatório Desativado',
                    'type' => 'success',
                    'message' => 'Seguro Obrigatório desativado com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $errorCode = $e->getCode();
            $mensagem = $e->getMessage();
            Log::error('Erro ao desativar Seguro Obrigatório: ' . $mensagem);

            if ($errorCode == 23503) {
                $mensagem = 'Não foi possível desativar a Seguro Obrigatório. Ela está sendo utilizada em outro registro.';
            }

            return response()->json([
                'notification' => [
                    'title' => 'Erro',
                    'type' => 'error',
                    'message' => $mensagem
                ]
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Validar os dados do seguro obrigatório
     */
    private function validateSeguroObrigatorio(Request $request)
    {
        return $request->validate([
            'id_veiculo' => 'required|exists:veiculo,id_veiculo',
            'numero_bilhete' => 'required',
            'ano_validade' => 'required|integer|min:2000',
            'valor_seguro_previsto' => 'required|min:0',
            'valor_seguro_pago' => 'required|min:0',
            'data_vencimento' => 'required|date',
            'data_pagamento' => 'nullable|date',
            'situacao' => 'required|string|max:255',
        ]);
    }

    /**
     * Obtém os dados de um veículo específico para AJAX
     */
    public function getDadosVeiculo($id)
    {
        $veiculo = Veiculo::findOrFail($id);
        return response()->json([
            'placa' => $veiculo->placa,
            'tipo' => $veiculo->tipo_veiculo->descricao_tipo ?? 'Não informado'
        ]);
    }

    public function exportValidatedInfo(Request $request)
    {
        $validated = $this->validateSeguroObrigatorio($request);

        return response()->json([
            'success' => true,
            'data' => $validated,
            'message' => 'Seguro obrigatório validado com sucesso!'
        ]);
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

                // Configurar opções do PDF de forma mais simples
                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');

                // Carregar a view
                $pdf->loadView('admin.seguroobrigatorio.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('seguro_obrigatorio_' . date('Y-m-d_His') . '.pdf');
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

    protected function buildExportQuery(Request $request)
    {
        $query = SeguroObrigatorio::query()
            ->with('veiculo');

        if ($request->filled('id_seguro_obrigatorio_veiculo')) {
            $query->where('id_seguro_obrigatorio_veiculo', $request->id_seguro_obrigatorio_veiculo);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('filial_veiculo')) {
            $query->whereHas('veiculo.filial', function ($query) use ($request) {
                $query->where('id', $request->filial_veiculo);
            });
        }

        if ($request->filled('numero_bilhete')) {
            $query->where('numero_bilhete', 'like', '%' . $request->numero_bilhete . '%');
        }

        if ($request->filled('data_vencimento_inicio')) {
            $query->whereDate('data_vencimento', '>=', $request->data_vencimento_inicio);
        }

        if ($request->filled('data_vencimento_fim')) {
            $query->whereDate('data_vencimento', '<=', $request->data_vencimento_fim);
        }

        if ($request->filled('ano_validade')) {
            $query->where('ano_validade', $request->ano_validade);
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

        return $query->latest('id_seguro_obrigatorio_veiculo');
    }

    protected function getValidExportFilters()
    {
        return [
            'id_seguro_obrigatorio_veiculo',
            'id_veiculo',
            'filial_veiculo',
            'numero_bilhete',
            'data_vencimento_inicio',
            'data_vencimento_fim',
            'ano_validade',
            'status'
        ];
    }

    public function exportCsv(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_seguro_obrigatorio_veiculo' => 'Código',
            'veiculo.placa' => 'Placa',
            'veiculo.filial.name' => 'Filial do Veiculo',
            'numero_bilhete' => 'Número do Bilhete',
            'ano_validade' => 'Ano de Validade',
            'data_vencimento' => 'Data Vencimento',
            'valor_seguro_previsto' => 'Valor Previsto',
            'valor_seguro_pago' => 'Valor Pago',
            'data_pagamento' => 'Data Pagamento',
            'situacao' => 'Situação',
            'is_ativo' => 'Ativo/Inativo'
        ];

        return $this->exportToCsv($request, $query, $columns, 'seguro_obrigatorio', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_seguro_obrigatorio_veiculo' => 'Código',
            'veiculo.placa' => 'Placa',
            'veiculo.filial.name' => 'Filial do Veiculo',
            'numero_bilhete' => 'Número do Bilhete',
            'ano_validade' => 'Ano de Validade',
            'data_vencimento' => 'Data Vencimento',
            'valor_seguro_previsto' => 'Valor Previsto',
            'valor_seguro_pago' => 'Valor Pago',
            'data_pagamento' => 'Data Pagamento',
            'situacao' => 'Situação',
            'is_ativo' => 'Ativo/Inativo'
        ];

        return $this->exportToExcel($request, $query, $columns, 'seguro_obrigatorio', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'Código' => 'id_seguro_obrigatorio_veiculo',
            'Placa' => 'veiculo.placa',
            'Filial do Veiculo' => 'veiculo.filial.name',
            'Número do Bilhete' => 'numero_bilhete',
            'Ano de Validade' => 'ano_validade',
            'Data Vencimento' => 'data_vencimento',
            'Valor Previsto' => 'valor_seguro_previsto',
            'Data Pagamento' => 'data_pagamento',
            'situacao' => 'Situação',
            'Ativo/Inativo' => 'is_ativo'
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'seguro_obrigatorios',
            'seguro_obrigatorio',
            'seguro_obrigatorios',
            $this->getValidExportFilters()
        );
    }
}

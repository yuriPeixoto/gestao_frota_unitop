<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bomba;
use App\Models\Tanque;
use App\Models\VFilial;
use App\Traits\ExportableTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BombaController extends Controller
{
    use ExportableTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Bomba::query()->with('filial', 'tanque');

        // Filtros
        if ($request->filled('id_bomba')) {
            $query->where('id_bomba', $request->id_bomba);
        }

        if ($request->filled('descricao_bomba')) {
            $query->where('descricao_bomba', $request->descricao_bomba);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('id_tanque')) {
            $query->where('id_tanque', $request->id_tanque);
        }

        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao);
        }

        if ($request->filled('data_final_inclusao')) {
            $query->whereDate('data_inclusao', '<=', $request->data_final_inclusao);
        }

        // Novo filtro para status
        if ($request->filled('status')) {
            if ($request->status === 'ativo') {
                $query->where('is_ativo', true);
            } elseif ($request->status === 'inativo') {
                $query->where('is_ativo', false);
            }
        }

        $bombas = $query->orderBy('id_bomba', 'desc')
            ->paginate(40)
            ->appends($request->query());

        if ($request->header('HX-Request')) {
            return view('admin.bombas._table', compact('bombas'));
        }

        $filial = VFilial::orderBy('name')
            ->get(['id as value', 'name as label']);

        $tanque = Tanque::orderBy('tanque')
            ->get(['id_tanque as value', 'tanque as label']);

        $descricao_bomba = Bomba::orderBy('descricao_bomba')
            ->get(['id_bomba as value', 'descricao_bomba as label']);

        // Opções de status para o select
        $status_options = [
            ['value' => 'ativo', 'label' => 'Ativo'],
            ['value' => 'inativo', 'label' => 'Inativo'],
        ];

        $referenceDatas = $this->getReferenceDatas();

        return view('admin.bombas.index', array_merge(
            compact('bombas', 'tanque', 'filial', 'descricao_bomba', 'status_options'),
            $referenceDatas
        ));
    }

    /**
     * Toggle status da bomba (ativar/desativar)
     */
    public function toggleStatus(int $id)
    {
        try {
            DB::beginTransaction();

            $bomba = Bomba::findOrFail($id);
            $statusAnterior = $bomba->is_ativo;
            $bomba->toggleStatus();

            // Invalidar cache após atualizar status da bomba
            Cache::forget('bombas_descricoes_unicas');
            Cache::forget('bombas_reference_datas');

            DB::commit();

            $statusNovo = $statusAnterior ? 'desativada' : 'ativada';

            return response()->json([
                'success' => true,
                'is_ativo' => $bomba->is_ativo,
                'notification' => [
                    'title'   => 'Status alterado',
                    'type'    => 'success',
                    'message' => "Bomba {$statusNovo} com sucesso!"
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao alterar status da bomba: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'notification' => [
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Erro ao alterar status da bomba: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function getReferenceDatas()
    {
        return Cache::remember('bombas_reference_datas', now()->addHours(12), function () {
            return [
                'filiais' => VFilial::orderBy('name')
                    ->get(['id as value', 'name as label']),

                'tanques' => Tanque::orderBy('tanque')
                    ->get(['id_tanque as value', 'tanque as label']),
            ];
        });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $filial = VFilial::orderBy('name')
            ->get(['id as value', 'name as label']);

        $tanque = Tanque::orderBy('tanque')
            ->get(['id_tanque as value', 'tanque as label']);

        return view('admin.bombas.create', compact('filial', 'tanque'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'descricao_bomba' => 'required|string|max:100',
                'tamanho_maximo_encerrante' => 'required|integer',
                'id_filial' => 'required',
                'id_tanque' => 'required',
                'bomba_ctf' => 'nullable|string|max:255',
                'bomba_ctf_2_bico' => 'nullable|string|max:255' // Nome usado no formulário
            ]);

            DB::beginTransaction();

            DB::connection('pgsql')->table('bomba')->insertGetId([
                'data_inclusao' => now(),
                'descricao_bomba' => $validated['descricao_bomba'],
                'tamanho_maximo_encerrante' => $validated['tamanho_maximo_encerrante'],
                'id_filial' => $validated['id_filial'],
                'id_tanque' => $validated['id_tanque'],
                'bomba_ctf' => $validated['bomba_ctf'] ?? null,
                'boma_ctf_2_bico' => $validated['bomba_ctf_2_bico'] ?? null, // Nome correto da coluna no banco
                'is_ativo' => true, // Nova bomba é ativa por padrão
            ], 'id_bomba');

            // Invalidar cache após criar nova bomba
            Cache::forget('bombas_descricoes_unicas');
            Cache::forget('bombas_reference_datas');

            DB::commit();

            return redirect()->route('admin.bombas.index')
                ->with('success', 'Bomba cadastrada com sucesso!')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Nova bomba adicionada com sucesso!'
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar bomba: ' . $e->getMessage())
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Erro ao cadastrar bomba: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $bomba = Bomba::with('filial', 'tanque')->findOrFail($id);
        return view('admin.bombas.show', compact('bomba'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $bomba = Bomba::findOrFail($id);

        $filial = VFilial::orderBy('name')
            ->get(['id as value', 'name as label']);

        $tanque = Tanque::orderBy('tanque')
            ->get(['id_tanque as value', 'tanque as label']);

        // Adiciona log para debug
        Log::debug('Dados da bomba para edição', [
            'id_bomba' => $bomba->id_bomba,
            'descricao_bomba' => $bomba->descricao_bomba,
            'id_filial' => $bomba->id_filial,
            'id_tanque' => $bomba->id_tanque,
            'bomba_ctf' => $bomba->bomba_ctf,
            'boma_ctf_2_bico' => $bomba->boma_ctf_2_bico,
            'is_ativo' => $bomba->is_ativo
        ]);

        return view('admin.bombas.edit', compact('bomba', 'filial', 'tanque'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        try {
            Log::debug('Dados recebidos para atualização da bomba', $request->all());

            $validated = $request->validate([
                'descricao_bomba' => 'required|string|max:500',
                'tamanho_maximo_encerrante' => 'required|integer',
                'id_filial' => 'required',
                'id_tanque' => 'required',
                'bomba_ctf' => 'nullable|string|max:255',
                'bomba_ctf_2_bico' => 'nullable|string|max:255', // Nome usado no formulário
                'is_ativo' => 'sometimes|boolean'
            ]);

            Log::debug('Dados validados', $validated);

            DB::beginTransaction();

            $bomba = Bomba::findOrFail($id);
            $bomba->descricao_bomba = $validated['descricao_bomba'];
            $bomba->tamanho_maximo_encerrante = $validated['tamanho_maximo_encerrante'];
            $bomba->id_filial = $validated['id_filial'];
            $bomba->id_tanque = $validated['id_tanque'];
            $bomba->bomba_ctf = $validated['bomba_ctf'] ?? null;
            $bomba->boma_ctf_2_bico = $validated['bomba_ctf_2_bico'] ?? null; // Nome correto da coluna no banco
            $bomba->data_alteracao = now();

            // Atualiza status se fornecido
            if (isset($validated['is_ativo'])) {
                $bomba->is_ativo = $validated['is_ativo'];
            }

            Log::debug('Bomba antes de salvar', [
                'id_bomba' => $bomba->id_bomba,
                'descricao_bomba' => $bomba->descricao_bomba,
                'id_filial' => $bomba->id_filial,
                'id_tanque' => $bomba->id_tanque,
                'boma_ctf_2_bico' => $bomba->boma_ctf_2_bico,
                'is_ativo' => $bomba->is_ativo
            ]);

            $bomba->save();

            // Invalidar cache após atualizar bomba
            Cache::forget('bombas_descricoes_unicas');
            Cache::forget('bombas_reference_datas');

            DB::commit();

            return redirect()->route('admin.bombas.index')
                ->with('success', 'Bomba atualizada com sucesso!')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Bomba atualizada com sucesso!'
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar bomba: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar bomba: ' . $e->getMessage())
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Erro ao atualizar bomba: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Remove the specified resource from storage. (Soft Delete)
     */
    public function destroy(int $id)
    {
        try {
            DB::beginTransaction();

            $bomba = Bomba::findOrFail($id);
            $bomba->delete(); // Agora usa soft delete com toggle automático de is_ativo

            // Invalidar cache após excluir bomba
            Cache::forget('bombas_descricoes_unicas');
            Cache::forget('bombas_reference_datas');

            DB::commit();

            return response()->json([
                'success' => true,
                'notification' => [
                    'title'   => 'Bomba desativada',
                    'type'    => 'success',
                    'message' => 'Bomba desativada com sucesso!'
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'notification' => [
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Erro ao desativar bomba: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    // Implementação dos métodos de exportação
    protected function buildExportQuery(Request $request)
    {
        $query = Bomba::query()->with('filial', 'tanque');

        if ($request->filled('id_bomba')) {
            $query->where('id_bomba', $request->id_bomba);
        }

        if ($request->filled('descricao_bomba')) {
            $query->where('descricao_bomba', 'like', '%' . $request->descricao_bomba . '%');
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('id_tanque')) {
            $query->where('id_tanque', $request->id_tanque);
        }

        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao);
        }

        if ($request->filled('data_final_inclusao')) {
            $query->whereDate('data_inclusao', '<=', $request->data_final_inclusao);
        }

        // Novo filtro para exportação por status
        if ($request->filled('status')) {
            if ($request->status === 'ativo') {
                $query->where('is_ativo', true);
            } elseif ($request->status === 'inativo') {
                $query->where('is_ativo', false);
            }
        }

        return $query->orderBy('id_bomba', 'desc');
    }

    protected function getValidExportFilters()
    {
        return [
            'id_bomba',
            'descricao_bomba',
            'id_filial',
            'id_tanque',
            'data_inclusao',
            'data_final_inclusao',
            'status' // Novo filtro
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
                $pdf->loadView('admin.bombas.pdf', compact('data'));

                return $pdf->download('bombas_' . date('Y-m-d_His') . '.pdf');
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
            'id_bomba' => 'Código',
            'descricao_bomba' => 'Descrição',
            'filial_name' => 'Filial',
            'tanque_nome' => 'Tanque',
            'data_inclusao' => 'Data Inclusão',
            'data_alteracao' => 'Data Alteração',
            'bomba_ctf' => 'Bico 1',
            'bomba_ctf_2_bico' => 'Bico 2',
            'tamanho_maximo_encerrante' => 'Tamanho Máx. Encerrante',
            'status_formatado' => 'Status' // Novo campo formatado
        ];

        return $this->exportToCsv($request, $query, $columns, 'bombas', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_bomba' => 'Código',
            'descricao_bomba' => 'Descrição',
            'filial_name' => 'Filial',
            'tanque_nome' => 'Tanque',
            'data_inclusao' => 'Data Inclusão',
            'data_alteracao' => 'Data Alteração',
            'bomba_ctf' => 'Bico 1',
            'bomba_ctf_2_bico' => 'Bico 2',
            'tamanho_maximo_encerrante' => 'Tamanho Máx. Encerrante',
            'status_formatado' => 'Status' // Novo campo formatado
        ];

        return $this->exportToExcel($request, $query, $columns, 'bombas', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'id' => 'id_bomba',
            'descricao' => 'descricao_bomba',
            'filial' => 'filial_name',
            'tanque' => 'tanque_nome',
            'data_inclusao' => 'data_inclusao',
            'data_alteracao' => 'data_alteracao',
            'bico_1' => 'bomba_ctf',
            'bico_2' => 'bomba_ctf_2_bico',
            'tamanho_maximo' => 'tamanho_maximo_encerrante',
            'status' => 'status_formatado' // Novo campo formatado
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'bombas',
            'bomba',
            'bombas',
            $this->getValidExportFilters()
        );
    }
}

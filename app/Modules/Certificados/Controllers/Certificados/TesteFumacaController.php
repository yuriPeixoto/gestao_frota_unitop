<?php

namespace App\Modules\Certificados\Controllers\Certificados;

use App\Http\Controllers\Controller;
use App\Modules\Certificados\Models\TesteFumaca;
use App\Models\Veiculo;
use App\Models\VFilial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Traits\ExportableTrait;

class TesteFumacaController extends Controller
{
    use ExportableTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = TesteFumaca::query()
            ->with('veiculo', 'veiculo.filial');

        if ($request->filled('id_veiculo')) {
            $query->whereHas('veiculo', function ($q) use ($request) {
                $q->where('id_veiculo', $request->id_veiculo);
            });
        }

        if ($request->filled('filial')) {
            $query->whereHas('veiculo.filial', function ($q) use ($request) {
                $q->where('id_filial', $request->filial);
            });
        }

        if ($request->filled('data_inicial')) {
            $query->whereDate('teste_fumaca.data_de_realizacao', '>=', $request->data_inicial);
        }

        if ($request->filled('data_final')) {
            $query->whereDate('teste_fumaca.data_de_realizacao', '<=', $request->data_final);
        }

        if ($request->filled('vencimento_inicial')) {
            $query->whereDate('teste_fumaca.data_de_vencimento', '>=', $request->vencimento_inicial);
        }

        if ($request->filled('vencimento_final')) {
            $query->whereDate('teste_fumaca.data_de_vencimento', '<=', $request->vencimento_final);
        }

        if ($request->filled('resultado')) {
            $query->where('teste_fumaca.resultado', 'ilike', '%' . $request->resultado . '%');
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

        $veiculosFrequentes = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->limit(20)
            ->get(['id_veiculo as value', 'placa as label']);

        $filiais = VFilial::select('id as value', 'name as label')->orderBy('name')->get();


        $testesFumaca = $query->latest('id_teste_fumaca')->paginate(15);

        return view('admin.testefumacas.index', compact('testesFumaca', 'veiculosFrequentes', 'filiais'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->get(['id_veiculo as value', 'placa as label']);

        return view('admin.testefumacas.create', compact('veiculos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_veiculo' => 'required|exists:veiculo,id_veiculo',
            'prefixo' => 'nullable|string|max:512',
            'data_de_realizacao' => 'required|date',
            'data_de_vencimento' => 'required|date',
            'kmaximo' => 'required|string|max:512',
            'kmedido' => 'required|string|max:512',
            'resultado' => 'required|string|max:512',
            'transportador' => 'required|string|max:512',
            'tecnico' => 'required|string|max:512',
            'anexo_laudo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->filled('id_veiculo')) {
            $veiculo = Veiculo::findOrFail($request->id_veiculo);
            $validated['placa'] = $veiculo->placa;
        }

        // Upload do anexo
        $anexoPath = null;
        if ($request->hasFile('anexo_laudo') && $request->file('anexo_laudo')->isValid()) {
            $anexoPath = $request->file('anexo_laudo')->store('laudos/teste-fumaca', 'public');
        }

        $valoresPermitidos = ['A vencer', 'Cancelado', 'Vencido'];
        $situacao = $request->situacao;
        if (!in_array($situacao, $valoresPermitidos)) {
            $situacao = 'A vencer'; // padrão
        }

        try {
            DB::beginTransaction();

            $testeOpacidade = new TesteFumaca();

            $testeOpacidade->data_inclusao      = now();
            $testeOpacidade->id_veiculo         = $validated['id_veiculo'];
            $testeOpacidade->placa              = $validated['placa'];
            $testeOpacidade->data_de_realizacao = $validated['data_de_realizacao'];
            $testeOpacidade->data_de_vencimento = $validated['data_de_vencimento'];
            $testeOpacidade->kmaximo            = $validated['kmaximo'];
            $testeOpacidade->kmedido            = $validated['kmedido'];
            $testeOpacidade->resultado          = $validated['resultado'];
            $testeOpacidade->transportador      = $validated['transportador'];
            $testeOpacidade->tecnico            = $validated['tecnico'];
            $testeOpacidade->anexo_laudo        = $anexoPath;
            $testeOpacidade->$situacao;

            $testeOpacidade->save();


            DB::commit();


            return redirect()
                ->route('admin.testefumacas.index')
                ->with('success', 'Teste de Fumaça cadastrado com sucesso!')
                ->withNotification([
                    'title'   => 'Incluído com sucesso',
                    'type'    => 'success',
                    'message' => 'Teste de Fumaça cadastrado com sucesso'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar teste de fumaça: ' . $e->getMessage());
            $errorCode = $e->getCode();
            $mensagem = $e->getMessage();


            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao salvar teste de fumaça: ' . $e->getMessage())
                ->withNotification([
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => $mensagem
                ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $testeFumaca = TesteFumaca::findOrFail($id);
        return view('admin.testefumacas.show', compact('testeFumaca'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $testeFumaca = TesteFumaca::findOrFail($id);

        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->get(['id_veiculo as value', 'placa as label']);

        return view('admin.testefumacas.edit', compact('testeFumaca', 'veiculos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'id_veiculo' => 'required|exists:veiculo,id_veiculo',
                'data_de_realizacao' => 'required|date',
                'data_de_vencimento' => 'required|date',
                'kmaximo' => 'required|string|max:512',
                'kmedido' => 'required|string|max:512',
                'resultado' => 'required|string|max:512',
                'transportador' => 'required|string|max:512',
                'tecnico' => 'required|string|max:512',
                'anexo_laudo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);

            DB::beginTransaction();

            $testeFumaca = TesteFumaca::findOrFail($id);

            if ($request->filled('id_veiculo')) {
                $veiculo = Veiculo::findOrFail($request->id_veiculo);
                $validated['placa'] = $veiculo->placa;
            }

            // Upload do anexo se um novo arquivo foi enviado
            if ($request->hasFile('anexo_laudo') && $request->file('anexo_laudo')->isValid()) {
                // Remove o anexo anterior se existir
                if ($testeFumaca->anexo_laudo) {
                    Storage::disk('public')->delete($testeFumaca->anexo_laudo);
                }

                $anexoPath = $request->file('anexo_laudo')->store('laudos/teste-fumaca', 'public');
                $testeFumaca->anexo_laudo = $anexoPath;
            }


            // Atualiza os outros campos
            $testeFumaca->data_alteracao     = now();
            $testeFumaca->id_veiculo         = $validated['id_veiculo'];
            $testeFumaca->placa              = $validated['placa'];
            $testeFumaca->data_de_realizacao = $validated['data_de_realizacao'];
            $testeFumaca->data_de_vencimento = $validated['data_de_vencimento'];
            $testeFumaca->kmaximo            = $validated['kmaximo'];
            $testeFumaca->kmedido            = $validated['kmedido'];
            $testeFumaca->resultado          = $validated['resultado'];
            $testeFumaca->transportador      = $validated['transportador'];
            $testeFumaca->tecnico            = $validated['tecnico'];

            $testeFumaca->save();

            DB::commit();

            return redirect()
                ->route('admin.testefumacas.index')
                ->with('success', 'Teste de Fumaça atualizado com sucesso!')
                ->withNotification([
                    'title'   => 'Alterado com sucesso',
                    'type'    => 'success',
                    'message' => 'Teste de Fumaça alterado com sucesso'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar teste de fumaça: ' . $e->getMessage());

            $mensagem = $e->getMessage();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar teste de fumaça: ' . $e->getMessage())
                ->withNotification([
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => $mensagem
                ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $testeFumaca = TesteFumaca::findOrFail($id);

            // Remove o anexo se existir
            if ($testeFumaca->anexo_laudo) {
                Storage::disk('public')->delete($testeFumaca->anexo_laudo);
            }

            $testeFumaca->delete();

            DB::commit();

            return response()->json([
                'notification' => [
                    'title'   => 'Teste de Opacidade',
                    'type'    => 'success',
                    'message' => 'Teste de Opacidade foi desativada com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao desativada teste de fumaça: ' . $e->getMessage());

            $mensagem = $e->getMessage();

            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => $mensagem
                ]
            ], 500, [], JSON_UNESCAPED_UNICODE); // JSON_UNESCAPED_UNICODE para retorntar os acentos
        }
    }

    /**
     * Retorna a filial de um veículo com base no seu ID
     *
     * @param string $id ID do veículo
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFilial(string $id)
    {
        $filiais = Veiculo::where('id_veiculo', $id)
            ->with('filial')
            ->first()->filial->name;
        return response()->json($filiais);
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
                $pdf->loadView('admin.testefumacas.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('testeOpacidade_' . date('Y-m-d_His') . '.pdf');
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
        $query = TesteFumaca::query()->with('veiculo', 'veiculo.filial');

        if ($request->filled('id_veiculo')) {
            $query->whereHas('veiculo', function ($q) use ($request) {
                $q->where('id_veiculo', $request->id_veiculo);
            });
        }

        if ($request->filled('filial')) {
            $query->whereHas('veiculo.filial', function ($q) use ($request) {
                $q->where('id_filial', $request->filial);
            });
        }

        if ($request->filled('data_inicial')) {
            $query->whereDate('teste_fumaca.data_de_realizacao', '>=', $request->data_inicial);
        }

        if ($request->filled('data_final')) {
            $query->whereDate('teste_fumaca.data_de_realizacao', '<=', $request->data_final);
        }

        if ($request->filled('vencimento_inicial')) {
            $query->whereDate('teste_fumaca.data_de_vencimento', '>=', $request->vencimento_inicial);
        }

        if ($request->filled('vencimento_final')) {
            $query->whereDate('teste_fumaca.data_de_vencimento', '<=', $request->vencimento_final);
        }

        if ($request->filled('resultado')) {
            $query->where('teste_fumaca.resultado', 'ilike', '%' . $request->resultado . '%');
        }

        if ($request->filled('status')) {
            if ($request->status === 'ativo') {
                $query->ativos();
            } else {
                $query->inativos();
            }
        } else {
            $query->ativos();
        }

        return $query;
    }

    protected function getValidExportFilters()
    {
        return [
            'id_teste_fumaca',
            'id_veiculo',
            'placa',
            'data_de_realizacao',
            'data_de_vencimento',
            'kmaximo',
            'kmedido',
            'resultado',
            'is_ativo',
            'situacao',
            'status',
            'data_inicial',
            'data_final',
            'vencimento_inicial',
            'vencimento_final',
            'filial'
        ];
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_teste_fumaca' => 'Código',
            'veiculo.placa' => 'Placa',
            'veiculo.filial.name' => 'Filial do Veiculo',
            'data_de_realizacao' => 'Data da Realização',
            'data_de_vencimento' => 'Data do Vencimento',
            'situacao' => 'Situação',
            'kmaximo' => 'K Maximo',
            'kmedido' => 'K Medido',
            'resultado' => 'Resultado',
            'is_ativo' => 'Status'
        ];

        return $this->exportToExcel($request, $query, $columns, 'testeOpacidade', $this->getValidExportFilters());
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
            'id_teste_fumaca' => 'Código',
            'veiculo.placa' => 'Placa',
            'veiculo.filial.name' => 'Filial do Veiculo',
            'data_de_realizacao' => 'Data da Realização',
            'data_de_vencimento' => 'Data do Vencimento',
            'situacao' => 'Situação',
            'kmaximo' => 'K Maximo',
            'kmedido' => 'K Medido',
            'resultado' => 'Resultado',
            'is_ativo' => 'Status'
        ];

        return $this->exportToCsv($request, $query, $columns, 'testeOpacidade', $this->getValidExportFilters());
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
            'id' => 'id_teste_fumaca',
            'Placa' => 'veiculo.placa',
            'Filial do Veiculo' => 'veiculo.filial.name',
            'Data da Realização' => 'data_de_realizacao',
            'Data Vencimento' => 'data_vencimento',
            'Situacao' => 'situacao',
            'K_Maximo' => 'kmaximo',
            'K_Medido' => 'kmedido',
            'Resultado' => 'resultado',
            'Status' => 'is_ativo'

        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'testeOpacidades',
            'testeOpacidade',
            'testeOpacidades',
            $this->getValidExportFilters()
        );
    }

    public function cloneCertificado(string $id)
    {
        LOG::DEBUG('Replicando Certificado com ID: ' . $id);
        DB::beginTransaction();

        try {
            // Encontra o usuário original
            $originalCert = TesteFumaca::findOrFail($id);

            // Cria um novo usuário com os mesmos dados (exceto email e senha)
            $clonedCert = $originalCert->replicate();
            $clonedCert->placa = 'clone_' . time() . '_' . $originalCert->placa;
            $clonedCert->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Registro replicado com sucesso, o novo regsitro tem a placa: ' . $clonedCert->placa
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao clonar usuário: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

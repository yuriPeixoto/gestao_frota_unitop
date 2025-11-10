<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificadoVeiculos;
use App\Models\Estado;
use App\Models\TipoCertificado;
use App\Models\Veiculo;
use App\Models\VFilial;
use App\Traits\ExportableTrait;
use App\Traits\SanitizesMonetaryValues;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutorizacoesEspTransitoController extends Controller
{
    use ExportableTrait;
    use SanitizesMonetaryValues;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = CertificadoVeiculos::query()
            ->select('certificadoveiculo.*')
            ->with('veiculo')
            ->with('tipocertificado')
            ->whereIn('id_tipo_certificado', [4, 5, 6, 7, 8])
            ->distinct();

        if ($request->filled('id_certificado_veiculo')) {
            $query->where('id_certificado_veiculo', $request->id_certificado_veiculo);
        }

        if ($request->filled('numero_certificado')) {
            $query->where('numero_certificado', $request->numero_certificado);
        }

        if ($request->filled('search')) {
            $query->whereHas('veiculo', function ($q) use ($request) {
                $q->where('placa', $request->search);
            });
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('filial_veiculo')) {
            $query->whereHas('veiculo', function ($q) use ($request) {
                $q->where('id_filial', $request->filial_veiculo);
            });
        }

        if ($request->filled('tipo_certificado')) {
            $query->where('tipo_certificado', $request->tipo_certificado);
        }

        if ($request->filled('data_vencimento_inicio') && $request->filled('data_vencimento_fim')) {
            $query->whereRaw('data_vencimento::date BETWEEN ? AND ?', [
                $request->data_vencimento_inicio,
                $request->data_vencimento_fim,
            ]);
        }

        if ($request->filled('situacao')) {
            $query->where('situacao', $request->situacao);
        }

        if ($request->filled('status')) {
            if ($request->status === 'ativo') {
                $query->ativos();
            } elseif ($request->status === 'inativo') {
                $query->withTrashed()->inativos();
            }
        } else {
            $query->todos();
        }

        $query->orderBy('id_certificado_veiculo', 'desc');

        $autorizacoesesptransitos = $query->paginate();

        $tiposCertificados = TipoCertificado::whereIn('id_tipo_certificado', [4, 5, 6, 7, 8])
            ->orderBy('descricao_certificado')
            ->get()->toArray();

        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->limit(20)
            ->get(['id_veiculo as value', 'placa as label']);

        $tiposCertificados = TipoCertificado::whereIn('id_tipo_certificado', [4, 5, 6, 7, 8])
            ->orderBy('descricao_certificado')
            ->select('descricao_certificado as label', 'id_tipo_certificado as value')
            ->get()->toArray();

        $filialVeiculos = VFilial::select('id as value', 'name as label')->get();

        return view('admin.autorizacoesesptransitos.index', compact(
            'autorizacoesesptransitos',
            'tiposCertificados',
            'veiculos',
            'filialVeiculos'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $veiculosFrequentes = Cache::remember('veiculos_frequentes', now()->addHours(12), function () {
            return Veiculo::where('situacao_veiculo', true)
                ->orderBy('chassi')
                ->limit(20)
                ->get([
                    'id_veiculo as value',  // ID do veículo
                    'placa as label',       // Placa do veículo
                    'chassi as chassi',     // Chassi do veículo
                    'renavam',
                ])
                ->map(fn($veiculo) => [
                    'value' => $veiculo->value,
                    'label' => $veiculo->label,
                    'chassi' => $veiculo->chassi,
                    'renavam' => $veiculo->renavam,
                ]);
        });

        // Obter tipos de certificados específicos (4, 5, 6, 7, 8)
        $tiposCertificados = TipoCertificado::select('descricao_certificado as label', 'id_tipo_certificado as value')
            ->whereIn('id_tipo_certificado', [4, 5, 6, 7, 8])
            ->orderBy('descricao_certificado')
            ->select('descricao_certificado as label', 'id_tipo_certificado as value')
            ->get()->toArray();

        $veiculos = Veiculo::select('placa as label', 'id_veiculo as value')
            ->where('placa', '!=', null)
            ->where('situacao_veiculo', '=', 'True')
            ->orderBy('placa')->get()->toArray();

        $estados = Estado::select('uf as label', 'id_uf as value')->orderBy('uf', 'asc')->get()->toArray();

        return view('admin.autorizacoesesptransitos.create', compact(
            'veiculosFrequentes',
            'tiposCertificados',
            'veiculos',
            'estados'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            if ($request->data_vencimento <= $request->data_certificacao) {
                return back()->with('notification', [
                    'type' => 'error', // Mudei para 'error' já que é um caso de erro
                    'title' => 'Erro de validação',
                    'message' => 'A data de vencimento não pode ser igual ou anterior à data de certificação',
                    'duration' => 5000,
                ])->withInput();
            }

            // -- Inicio gravação Autorização Especial Veiculo
            $autorizacaoEspTransito_insert = $request->validate([
                'id_veiculo' => 'required|integer',
                'id_tipo_certificado' => 'required|integer',
                'data_vencimento' => 'required|date|after_or_equal:today',
                'data_certificacao' => 'required|date|before_or_equal:today',
                'numero_certificado' => 'required|string|max:50',
                'valor_certificado' => 'nullable',
                'caminho_arquivo' => 'nullable|file|mimes:pdf|max:2048',
            ]);

            if (! empty($request->id_uf)) {
                $estado = Estado::where('id_uf', $request->id_uf)->first();
                $uf = $estado->uf;
            }

            // Valores permitidos para 'situacao'
            $valoresPermitidos = ['A vencer', 'Cancelado', 'Vencido'];
            $situacao = $request->situacao;
            if (!in_array($situacao, $valoresPermitidos)) {
                $situacao = 'A vencer'; // padrão
            }

            // Upload do arquivo
            $arquivoPath = null;
            if ($request->hasFile('caminho_arquivo') && $request->file('caminho_arquivo')->isValid()) {
                $arquivoPath = $request->file('caminho_arquivo')->store('laudos', 'public');
            }

            db::beginTransaction();
            $autorizacaoEspTransito = new CertificadoVeiculos;

            $autorizacaoEspTransito->data_inclusao = now();
            $autorizacaoEspTransito->id_tipo_certificado = $autorizacaoEspTransito_insert['id_tipo_certificado'] ?? null;
            $autorizacaoEspTransito->id_veiculo = $autorizacaoEspTransito_insert['id_veiculo'] ?? null;
            $autorizacaoEspTransito->chassi = $autorizacaoEspTransito_insert['chassi'] ?? null;
            $autorizacaoEspTransito->renavam = $autorizacaoEspTransito_insert['renavam'] ?? null;
            $autorizacaoEspTransito->id_uf = $request->id_uf ?? null;
            $autorizacaoEspTransito->uf = $uf;
            $autorizacaoEspTransito->data_vencimento = $autorizacaoEspTransito_insert['data_vencimento'] ?? null;
            $autorizacaoEspTransito->data_certificacao = $autorizacaoEspTransito_insert['data_certificacao'] ?? null;
            $autorizacaoEspTransito->numero_certificado = $autorizacaoEspTransito_insert['numero_certificado'] ?? null;
            $autorizacaoEspTransito->valor_certificado = $request->valor_certificado;
            $autorizacaoEspTransito->situacao = $situacao;
            $autorizacaoEspTransito->caminho_arquivo = $arquivoPath;

            $autorizacaoEspTransito->save();
            db::commit();
            // -- Fim gravação Autorização Especial Veiculo

            return redirect()
                ->route('admin.autorizacoesesptransitos.index')
                ->withNotification([
                    'title' => 'Autorização Cadastrada',
                    'type' => 'success',
                    'message' => 'Autorização foi cadastrada com sucesso!',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao gravar o registro: ' . $e->getMessage());

            return redirect()
                ->route('admin.autorizacoesesptransitos.index')
                ->withNotification([
                    'title' => 'Erro',
                    'type' => 'error',
                    'message' => 'Erro ao gravar o registro!',
                ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $autorizacao = CertificadoVeiculos::with(['veiculo', 'tipocertificado'])->findOrFail($id);

        return view('admin.autorizacoesesptransitos.show', compact('autorizacao'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $veiculosFrequentes = Cache::remember('veiculos_frequentes', now()->addHours(12), function () {
            return Veiculo::where('situacao_veiculo', true)
                ->orderBy('chassi')
                ->get([
                    'id_veiculo as value',  // ID do veículo
                    'placa as label',       // Placa do veículo
                    'chassi as chassi',     // Chassi do veículo
                    'renavam',
                ])
                ->map(fn($veiculo) => [
                    'value' => $veiculo->value,
                    'label' => $veiculo->label,
                    'chassi' => $veiculo->chassi,
                    'renavam' => $veiculo->renavam,
                ]);
        });

        $autorizacao = CertificadoVeiculos::findOrFail($id);

        $tiposCertificados = TipoCertificado::select('descricao_certificado as label', 'id_tipo_certificado as value')
            ->whereIn('id_tipo_certificado', [4, 5, 6, 7, 8])
            ->orderBy('descricao_certificado')
            ->get()->toArray();

        $veiculos = Veiculo::select('placa as label', 'id_veiculo as value')
            ->where('placa', '!=', null)
            ->where('situacao_veiculo', '=', 'True')
            ->orderBy('placa')->get()->toArray();

        $estados = Estado::select('uf as label', 'id_uf as value')->orderBy('uf', 'asc')->get()->toArray();

        return view('admin.autorizacoesesptransitos.edit', compact(
            'veiculosFrequentes',
            'autorizacao',
            'tiposCertificados',
            'veiculos',
            'estados'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            if ($request->data_vencimento <= $request->data_certificacao) {
                return back()->with('notification', [
                    'type' => 'error',
                    'title' => 'Erro de validação',
                    'message' => 'A data de vencimento não pode ser igual ou anterior à data de certificação',
                    'duration' => 5000,
                ])->withInput();
            }

            // Mesclar `selected_caminho_arquivo` em `caminho_arquivo`
            $request->merge([
                'caminho_arquivo' => $request->input('selected_caminho_arquivo', $request->file('caminho_arquivo')),
            ]);

            DB::beginTransaction();

            $autorizacaoEspTransito_insert = $request->validate([
                'id_veiculo' => 'required|integer',
                'id_tipo_certificado' => 'required|integer',
                'data_vencimento' => 'required|date|after_or_equal:today',
                'data_certificacao' => 'required|date|before_or_equal:today',
                'numero_certificado' => 'required|string|max:50',
                'caminho_arquivo' => 'nullable|file|mimes:pdf|max:2048',
            ]);

            if (!empty($request->id_uf)) {
                $estado = Estado::where('id_uf', $request->id_uf)->first();
                $uf = $estado->uf ?? null;
            }

            $autorizacaoEspTransito = CertificadoVeiculos::findOrFail($id);

            // Upload do arquivo
            $fotoPath = $autorizacaoEspTransito->caminho_arquivo; // padrão
            if ($request->hasFile('caminho_arquivo') && $request->file('caminho_arquivo')->isValid()) {
                $fotoPath = $request->file('caminho_arquivo')->store('laudos', 'public');
            }

            // Valores permitidos para 'situacao'
            $valoresPermitidos = ['A vencer', 'Cancelado', 'Vencido'];
            $situacao = $request->situacao;
            if (!in_array($situacao, $valoresPermitidos)) {
                $situacao = 'A vencer'; // padrão
            }

            // Atualização dos campos
            $autorizacaoEspTransito->data_alteracao = now();
            $autorizacaoEspTransito->id_tipo_certificado = $autorizacaoEspTransito_insert['id_tipo_certificado'] ?? null;
            $autorizacaoEspTransito->id_veiculo = $autorizacaoEspTransito_insert['id_veiculo'] ?? null;
            $autorizacaoEspTransito->chassi = $autorizacaoEspTransito_insert['chassi'] ?? null;
            $autorizacaoEspTransito->renavam = $autorizacaoEspTransito_insert['renavam'] ?? null;
            $autorizacaoEspTransito->id_uf = $request->id_uf ?? null;
            $autorizacaoEspTransito->uf = $uf ?? null;
            $autorizacaoEspTransito->data_vencimento = $autorizacaoEspTransito_insert['data_vencimento'] ?? null;
            $autorizacaoEspTransito->data_certificacao = $autorizacaoEspTransito_insert['data_certificacao'] ?? null;
            $autorizacaoEspTransito->numero_certificado = $autorizacaoEspTransito_insert['numero_certificado'] ?? null;
            $autorizacaoEspTransito->valor_certificado = $request->valor_certificado;
            $autorizacaoEspTransito->situacao = $situacao;
            $autorizacaoEspTransito->caminho_arquivo = $fotoPath ?? null;

            $autorizacaoEspTransito->update();
            DB::commit();

            return redirect()
                ->route('admin.autorizacoesesptransitos.index')
                ->withNotification([
                    'title' => 'Autorização Atualizado',
                    'type' => 'success',
                    'message' => 'Autorização foi atualizada com sucesso!',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar o registro: ' . $e->getMessage());

            return redirect()
                ->route('admin.autorizacoesesptransitos.index')
                ->withNotification([
                    'title' => 'Erro ao atualizar o registro',
                    'type' => 'error',
                    'message' => 'Erro ao atualizar a autorização!',
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
            $autorizacaoEspTransito = CertificadoVeiculos::findOrFail($id);

            if (! empty($autorizacaoEspTransito)) {
                $autorizacaoEspTransito->delete();
            }

            // $autorizacao->delete();

            DB::commit();

            return response()->json([
                'notification' => [
                    'title' => 'Autorização Desativada',
                    'type' => 'success',
                    'message' => 'Autorização desativada com sucesso',
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $errorCode = $e->getCode();
            $mensagem = $e->getMessage();
            Log::error('Erro ao desativar Autorização: ' . $mensagem);

            if ($errorCode == 23503) {
                $mensagem = 'Não foi possível desativar a Autorização. Ela está sendo utilizada em outro registro.';
            }

            return response()->json([
                'notification' => [
                    'title' => 'Erro',
                    'type' => 'error',
                    'message' => $mensagem,
                ],
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function pegaRenavamData(Request $request)
    {
        try {
            $veiculo = Veiculo::select(['renavam', 'chassi', 'id_filial'])
                ->where('id_veiculo', $request->placa)
                ->firstOrFail();

            return response()->json([
                'renavam' => $veiculo->renavam ?? 'Não informado',
                'chassi' => $veiculo->chassi ?? 'NÃO INFORMADO',
                'filial' => $veiculo->filialVeiculo->name ?? 'NÃO INFORMADO',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do veículo: ' . $e->getMessage());

            return response()->json(['error' => 'Erro ao buscar dados do veículo'], 500);
        }
    }

    public function replicar($id)
    {
        $veiculosFrequentes = Cache::remember('veiculos_frequentes', now()->addHours(12), function () {
            return Veiculo::where('situacao_veiculo', true)
                ->orderBy('chassi')
                ->get([
                    'id_veiculo as value',  // ID do veículo
                    'placa as label',       // Placa do veículo
                    'chassi as chassi',     // Chassi do veículo
                    'renavam',
                ])
                ->map(fn($veiculo) => [
                    'value' => $veiculo->value,
                    'label' => $veiculo->label,
                    'chassi' => $veiculo->chassi,
                    'renavam' => $veiculo->renavam,
                ]);
        });

        $autorizacao = CertificadoVeiculos::findOrFail($id);

        $dataOriginal = Carbon::parse($autorizacao->data_vencimento);
        $autorizacao->data_vencimento = $dataOriginal->addYear();

        $tiposCertificados = TipoCertificado::select('descricao_certificado as label', 'id_tipo_certificado as value')
            ->whereIn('id_tipo_certificado', [4, 5, 6, 7, 8])
            ->orderBy('descricao_certificado')
            ->get()->toArray();

        $veiculos = Veiculo::select('placa as label', 'id_veiculo as value')
            ->where('placa', '!=', null)
            ->where('situacao_veiculo', '=', 'True')
            ->orderBy('placa')->get()->toArray();

        $estados = Estado::select('uf as label', 'id_uf as value')->orderBy('uf', 'asc')->get()->toArray();

        return view('admin.autorizacoesesptransitos.replicar', compact(
            'veiculosFrequentes',
            'autorizacao',
            'tiposCertificados',
            'veiculos',
            'estados'
        ));
    }

    public function replicarUpdate(Request $request)
    {
        try {

            $this->sanitizeMonetaryValues($request, [
                'valor_certificado',
            ]);

            db::beginTransaction();
            // -- Inicio gravação Autorização Especial Veiculo
            $autorizacaoEspTransito_insert = $request->validate([
                'id_tipo_certificado' => 'required',
                'id_veiculo' => 'required',
                'chassi' => 'nullable',
                'renavam' => 'nullable',
                'id_uf' => 'nullable',
                'data_vencimento' => 'date',
                'data_certificacao' => 'date',
                'numero_certificado' => 'required',
                'valor_certificado' => 'required',
                'caminho_arquivo' => 'nullable|mimes:pdf|max:1024',
            ]);

            if (! empty($request->id_uf)) {
                $estado = Estado::where('id_uf', $request->id_uf)->first();
                $uf = $estado->uf;
            }

            $autorizacaoEspTransito = new CertificadoVeiculos;

            // Upload do arquivo
            if ($request->hasFile('caminho_arquivo') && $request->file('caminho_arquivo')->isValid()) {
                $fotoPath = $request->file('caminho_arquivo')->store('laudos', 'public');
            } else {
                $fotoPath = $autorizacaoEspTransito->caminho_arquivo;
            }

            $autorizacaoEspTransito->data_inclusao = now();
            $autorizacaoEspTransito->id_tipo_certificado = $autorizacaoEspTransito_insert['id_tipo_certificado'] ?? null;
            $autorizacaoEspTransito->id_veiculo = $autorizacaoEspTransito_insert['id_veiculo'] ?? null;
            $autorizacaoEspTransito->chassi = $autorizacaoEspTransito_insert['chassi'] ?? null;
            $autorizacaoEspTransito->renavam = $autorizacaoEspTransito_insert['renavam'] ?? null;
            $autorizacaoEspTransito->id_uf = $autorizacaoEspTransito_insert['id_uf'] ?? null;
            $autorizacaoEspTransito->uf = $uf;
            $autorizacaoEspTransito->data_vencimento = $autorizacaoEspTransito_insert['data_vencimento'] ?? null;
            $autorizacaoEspTransito->data_certificacao = $autorizacaoEspTransito_insert['data_certificacao'] ?? null;
            $autorizacaoEspTransito->numero_certificado = $autorizacaoEspTransito_insert['numero_certificado'] ?? null;
            $autorizacaoEspTransito->valor_certificado = $autorizacaoEspTransito_insert['valor_certificado'] ?? null;
            $autorizacaoEspTransito->caminho_arquivo = $fotoPath ?? null;

            $autorizacaoEspTransito->save();
            db::commit();
            // -- Fim gravação Autorização Especial Veiculo

            return redirect()
                ->route('admin.autorizacoesesptransitos.index')
                ->withNotification([
                    'title' => 'Autorização Cadastrada',
                    'type' => 'success',
                    'message' => 'Autorização foi cadastrada com sucesso!',
                ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
            DB::rollBack();
            Log::error('Erro ao gravar o registro: ' . $e->getMessage());

            return redirect()
                ->route('admin.autorizacoesesptransitos.index')
                ->withNotification([
                    'title' => 'Erro',
                    'type' => 'error',
                    'message' => 'Erro ao gravar o registro!',
                ]);
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = $this->buildExportQuery($request);

            // Se a exportação direta pelo trait não funcionar, tente um método alternativo
            if (! $this->hasAnyFilter($request, $this->getValidExportFilters())) {
                return redirect()->back()->with([
                    'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                    'export_error' => true,
                ]);
            }

            if ($request->has('confirmed') || ! $this->exceedsExportLimit($query, 500)) {
                $data = $query->get();

                // Configurar opções do PDF
                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');

                // Carregar a view
                $pdf->loadView('admin.autorizacoesesptransitos.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('autorizacaos_esp_transito_' . date('Y-m-d_His') . '.pdf');
            } else {
                // Confirmação para grande volume
                $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

                return redirect()->back()->with([
                    'warning' => 'Você está tentando exportar mais de 500 registros, o que pode levar mais tempo.',
                    'export_confirmation' => true,
                    'export_url' => $currentUrl,
                ]);
            }
        } catch (\Exception $e) {
            // Log detalhado do erro
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()->with([
                'error' => 'Erro ao gerar o PDF: ' . $e->getMessage(),
                'export_error' => true,
            ]);
        }
    }

    public function buildExportQuery($request)
    {
        $query = CertificadoVeiculos::query()
            ->select('certificadoveiculo.*')
            ->with('veiculo')
            ->with('tipocertificado')
            ->whereIn('id_tipo_certificado', [4, 5, 6, 7, 8])
            ->distinct();

        if ($request->filled('id_certificado_veiculo')) {
            $query->where('id_certificado_veiculo', $request->id_certificado_veiculo);
        }

        if ($request->filled('numero_certificado')) {
            $query->where('numero_certificado', $request->numero_certificado);
        }

        if ($request->filled('search')) {
            $query->whereHas('veiculo', function ($q) use ($request) {
                $q->where('placa', $request->search);
            });
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('filial_veiculo')) {
            $query->whereHas('veiculo', function ($q) use ($request) {
                $q->where('id_filial', $request->filial_veiculo);
            });
        }

        if ($request->filled('tipo_certificado')) {
            $query->where('id_tipo_certificado', $request->tipo_certificado);
        }

        if ($request->filled('data_vencimento_inicio') && $request->filled('data_vencimento_fim')) {
            $query->whereRaw('data_vencimento::date BETWEEN ? AND ?', [
                $request->data_vencimento_inicio,
                $request->data_vencimento_fim,
            ]);
        }

        if ($request->filled('situacao')) {
            $query->where('situacao', $request->situacao);
        }

        if ($request->filled('status')) {
            if ($request->status === 'ativo') {
                $query->ativos();
            } elseif ($request->status === 'inativo') {
                $query->withTrashed()->inativos();
            }
        } else {
            $query->todos();
        }

        $query->orderBy('id_certificado_veiculo', 'desc');

        return $query;
    }

    protected function getValidExportFilters()
    {
        return [
            'id_certificado_veiculo',
            'numero_certificado',
            'id_veiculo',
            'data_certificacao',
            'data_vencimento',
            'status',
            'situacao',
            'search',
            'filial_veiculo',
            'tipo_certificado',
            'data_vencimento_inicio',
            'data_vencimento_fim',
        ];
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_certificado_veiculo' => 'Código',
            'veiculo.placa' => 'Placa',
            'veiculo.filial.name' => 'Filial do Veiculo',
            'tipocertificado.descricao_certificado' => 'Tipo Certificado',
            'numero_certificado' => 'Numero do Certificado',
            'data_certificacao' => 'Data Certificação',
            'data_vencimento' => 'Data Vencimento',
            'situacao' => 'Situação',
            'is_ativo' => 'Status',
        ];

        return $this->exportToExcel($request, $query, $columns, 'autorizacaos_esp_transito_', $this->getValidExportFilters());
    }

    /**
     * Exportar para CSV
     *
     * @return \Illuminate\Http\Response
     */
    public function exportCsv(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_certificado_veiculo' => 'Código',
            'veiculo.placa' => 'Placa',
            'veiculo.filial.name' => 'Filial do Veiculo',
            'tipocertificado.descricao_certificado' => 'Tipo Certificado',
            'numero_certificado' => 'Numero do Certificado',
            'data_certificacao' => 'Data Certificação',
            'data_vencimento' => 'Data Vencimento',
            'situacao' => 'Situação',
            'is_ativo' => 'Status',
        ];

        return $this->exportToCsv($request, $query, $columns, 'autorizacaos_esp_transito_', $this->getValidExportFilters());
    }

    /**
     * Exportar para XML
     *
     * @return \Illuminate\Http\Response
     */
    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'id' => 'id_certificado_veiculo',
            'Placa' => 'veiculo.placa',
            'Filial do Veiculo' => 'veiculo.filial.name',
            'Tipo Certificado' => 'tipocertificado.descricao_certificado',
            'Numero do Certificado' => 'numero_certificado',
            'Data Certificação' => 'data_certificacao',
            'Data Vencimento' => 'data_vencimento',
            'Situação' => 'situacao',
            'Status' => 'is_ativo',
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'autorizacaos_esp_transitos',
            'autorizacaos_esp_transito',
            'autorizacaos_esp_transitos',
            $this->getValidExportFilters()
        );
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

use App\Models\CertificadoVeiculos;
use App\Models\TipoCertificado;
use App\Models\Veiculo;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\SanitizesMonetaryValues;
use App\Traits\ExportableTrait;



class CronotacografoController extends Controller
{
    use SanitizesMonetaryValues;
    use ExportableTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = CertificadoVeiculos::query()
            ->with(['veiculo', 'veiculo.filial', 'tipocertificado'])
            ->where('id_tipo_certificado', 2)
            ->distinct();

        if ($request->filled('id_certificado_veiculo')) {
            $query->where('id_certificado_veiculo', $request->id_certificado_veiculo);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('situacao')) {
            $query->where('situacao', $request->situacao);
        }

        if ($request->filled('numero_certificado')) {
            $query->where('numero_certificado', $request->numero_certificado);
        }

        if ($request->filled('data_vencimento_inicio') && $request->filled('data_vencimento_final')) {
            $query->whereRaw("data_vencimento::date BETWEEN ? AND ?", [
                $request->data_vencimento_inicio,
                $request->data_vencimento_final,
            ]);
        }

        if ($request->filled('status')) {
            if ($request->status === '1') {
                $query->ativos();
            } else {
                $query->inativos();
            }
        } else {
            $query->todos();
        }

        $query->orderBy('id_certificado_veiculo', 'desc');

        $cronotacografos = $query->paginate(40);

        if ($request->header('HX-Request')) {
            return view('admin.cronotacografos._table', compact('cronotacografos', 'veiculos'));
        }

        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->limit(20)
            ->get(['id_veiculo as value', 'placa as label']);

        // Retornar a view
        return view('admin.cronotacografos.index', compact(
            'cronotacografos',
            'veiculos'
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
                ->get([
                    'id_veiculo as value',  // ID do veículo
                    'placa as label',       // Placa do veículo
                    'chassi as chassi',     // Chassi do veículo
                    'renavam'
                ])
                ->map(fn($veiculo) => [
                    'value' => $veiculo->value,
                    'label' => $veiculo->label,
                    'chassi' => $veiculo->chassi,
                    'renavam' => $veiculo->renavam,
                ]);
        });

        // Obter tipo de certificado específico para cronotacógrafo (id 2)
        $tiposCertificado = TipoCertificado::where('id_tipo_certificado', 2)
            ->get(['id_tipo_certificado as value', 'descricao_certificado as label']);

        // Obter veículos ativos
        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->get(['id_veiculo as value', 'placa as label']);

        // Obter estados
        $estados = Estado::orderBy('uf')
            ->get(['id_uf as value', 'uf as label']);

        return view('admin.cronotacografos.create', compact(
            'veiculosFrequentes',
            'tiposCertificado',
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
                    'message' => 'A data de vencimento não pode ser anterior ou igual à data de certificação',
                    'duration' => 5000,
                ])->withInput();
            }

            // Sanitize values
            $this->sanitizeMonetaryValues($request, [
                'valor_certificado'
            ]);



            // Validação dos dados com tratamento para casos específicos
            $validated = $request->validate([
                'id_tipo_certificado' => ['required', 'exists:tipocertificado,id_tipo_certificado'],
                'id_veiculo' => ['required', 'exists:veiculo,id_veiculo'],
                'chassi' => ['nullable', 'string', 'max:500'],
                'renavam' => ['nullable'], // Removido 'numeric' para permitir texto
                'id_uf' => ['nullable', 'exists:estado,id_uf'],
                'data_vencimento' => ['required', 'date'],
                'data_certificacao' => ['required', 'date'],
                'numero_certificado' => ['required', 'string', 'max:255'],
                'valor_certificado' => ['required', 'numeric'],
                'caminho_arquivo' => ['nullable', 'file', 'max:1024'],
            ]);

            // Log para diagnóstico
            Log::info('Dados validados para criação de cronotacógrafo:', $validated);

            // Recuperar a UF se informada
            if (!empty($validated['id_uf'])) {
                $estado = Estado::findOrFail($validated['id_uf']);
                $validated['uf'] = $estado->uf;
            }

            // Tratar o upload do arquivo
            if ($request->hasFile('caminho_arquivo') && $request->file('caminho_arquivo')->isValid()) {
                $validated['caminho_arquivo'] = $request->file('caminho_arquivo')->store('laudos', 'public');
            }

            // Valores permitidos para 'situacao'
            $valoresPermitidos = ['A vencer', 'Cancelado', 'Vencido'];
            $situacao = $request->situacao;
            if (!in_array($situacao, $valoresPermitidos)) {
                $situacao = 'A vencer'; // padrão
            }

            // Iniciar transação
            DB::beginTransaction();

            // Criar o registro de certificado
            $cronotacografo = new CertificadoVeiculos();
            $cronotacografo->data_inclusao = now();
            $cronotacografo->id_tipo_certificado = $validated['id_tipo_certificado'];
            $cronotacografo->id_veiculo = $validated['id_veiculo'];
            $cronotacografo->chassi = $validated['chassi'] ?? null;
            $cronotacografo->renavam = $validated['renavam'] ?? null;
            $cronotacografo->id_uf = $validated['id_uf'] ?? null;
            $cronotacografo->uf = $validated['uf'] ?? null;
            $cronotacografo->data_vencimento = $validated['data_vencimento'];
            $cronotacografo->data_certificacao = $validated['data_certificacao'];
            $cronotacografo->numero_certificado = $validated['numero_certificado'];
            $cronotacografo->valor_certificado = $validated['valor_certificado'];
            $cronotacografo->caminho_arquivo = $validated['caminho_arquivo'] ?? null;
            $cronotacografo->situacao = $situacao;


            $cronotacografo->save();

            // Log após salvar
            Log::info('Cronotacógrafo salvo com sucesso, ID: ' . $cronotacografo->id_certificado_veiculo);

            // Confirmar transação
            DB::commit();

            return redirect()
                ->route('admin.cronotacografos.index')
                ->with('success', 'Cronotacógrafo cadastrado com sucesso!')
                ->withNotification([
                    'title'   => 'Incluído com sucesso',
                    'type'    => 'success',
                    'message' => 'Cronotacógrafo cadastrado com sucesso'
                ]);
        } catch (\Exception $e) {
            // Rollback em caso de erro
            DB::rollBack();
            // Log detalhado do erro
            Log::error('Erro ao gravar o cronotacógrafo: ' . $e->getMessage(), [
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $mensagem = $e->getMessage();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao salvar o cronotacógrafo: ' . $e->getMessage())
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
        $cronotacografo = CertificadoVeiculos::with([
            'veiculo',
            'tipocertificado',
            'uf'
        ])->findOrFail($id);

        return view('admin.cronotacografos.show', compact('cronotacografo'));
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
                    'renavam'
                ])
                ->map(fn($veiculo) => [
                    'value' => $veiculo->value,
                    'label' => $veiculo->label,
                    'chassi' => $veiculo->chassi,
                    'renavam' => $veiculo->renavam,
                ]);
        });

        $cronotacografo = CertificadoVeiculos::findOrFail($id);

        // Obter tipo de certificado específico para cronotacógrafo (id 2)
        $tiposCertificado = TipoCertificado::where('id_tipo_certificado', 2)
            ->get(['id_tipo_certificado as value', 'descricao_certificado as label']);

        // Obter veículos ativos
        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->get(['id_veiculo as value', 'placa as label']);

        // Obter estados
        $estados = Estado::orderBy('uf')
            ->get(['id_uf as value', 'uf as label']);

        return view('admin.cronotacografos.edit', compact(
            'veiculosFrequentes',
            'cronotacografo',
            'tiposCertificado',
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
                    'type' => 'error', // Mudei para 'error' já que é um caso de erro
                    'title' => 'Erro de validação',
                    'message' => 'A data de vencimento não pode ser anterior ou igual à data de certificação',
                    'duration' => 5000,
                ])->withInput();
            }

            // Buscar o registro existente
            $cronotacografo = CertificadoVeiculos::findOrFail($id);

            $this->sanitizeMonetaryValues($request, [
                'valor_certificado'
            ]);

            // Validação dos dados com tratamento para casos específicos
            $validated = $request->validate([
                'id_tipo_certificado' => ['required', 'exists:tipocertificado,id_tipo_certificado'],
                'id_veiculo' => ['required', 'exists:veiculo,id_veiculo'],
                'chassi' => ['nullable', 'string', 'max:500'],
                'renavam' => ['nullable'], // Removido 'numeric' para permitir texto
                'id_uf' => ['nullable', 'exists:estado,id_uf'],
                'data_vencimento' => ['required', 'date'],
                'data_certificacao' => ['required', 'date'],
                'numero_certificado' => ['required', 'string', 'max:255'],
                'valor_certificado' => ['required', 'numeric'],
                'caminho_arquivo' => ['nullable', 'file', 'max:1024'],
            ]);

            // Log para diagnóstico
            Log::info('Dados validados para atualização de cronotacógrafo, ID: ' . $id, $validated);

            // Recuperar a UF se informada
            if (!empty($validated['id_uf'])) {
                $estado = Estado::findOrFail($validated['id_uf']);
                $validated['uf'] = $estado->uf;
            }

            // Tratar o upload do arquivo
            if ($request->hasFile('caminho_arquivo') && $request->file('caminho_arquivo')->isValid()) {
                $validated['caminho_arquivo'] = $request->file('caminho_arquivo')->store('laudos', 'public');
            } else {
                // Manter o arquivo existente
                $validated['caminho_arquivo'] = $cronotacografo->caminho_arquivo;
            }

            $valoresPermitidos = ['A vencer', 'Cancelado', 'Vencido'];
            $situacao = $request->situacao;
            if (!in_array($situacao, $valoresPermitidos)) {
                $situacao = 'A vencer'; // padrão
            }

            // Iniciar transação
            DB::beginTransaction();

            // Atualizar o registro
            $cronotacografo->data_alteracao = now();
            $cronotacografo->id_tipo_certificado = $validated['id_tipo_certificado'];
            $cronotacografo->id_veiculo = $validated['id_veiculo'];
            $cronotacografo->chassi = $validated['chassi'] ?? $cronotacografo->chassi;
            $cronotacografo->renavam = $validated['renavam'] ?? $cronotacografo->renavam;
            $cronotacografo->id_uf = $validated['id_uf'] ?? $cronotacografo->id_uf;
            $cronotacografo->uf = $validated['uf'] ?? $cronotacografo->uf;
            $cronotacografo->data_vencimento = $validated['data_vencimento'];
            $cronotacografo->data_certificacao = $validated['data_certificacao'];
            $cronotacografo->numero_certificado = $validated['numero_certificado'];
            $cronotacografo->valor_certificado = $validated['valor_certificado'];
            $cronotacografo->situacao = $situacao;
            // Somente atualiza o caminho_arquivo se um novo arquivo foi enviado
            if (isset($validated['caminho_arquivo'])) {
                $cronotacografo->caminho_arquivo = $validated['caminho_arquivo'];
            }

            // Log para diagnóstico antes de salvar
            Log::info('Objeto cronotacógrafo antes de atualizar:', [
                'id_veiculo' => $cronotacografo->id_veiculo,
                'chassi' => $cronotacografo->chassi,
                'renavam' => $cronotacografo->renavam,
                'data_vencimento' => $cronotacografo->data_vencimento,
                'data_certificacao' => $cronotacografo->data_certificacao,
            ]);

            $cronotacografo->save();

            // Log após salvar
            Log::info('Cronotacógrafo atualizado com sucesso, ID: ' . $cronotacografo->id_certificado_veiculo);

            // Confirmar transação
            DB::commit();

            return redirect()
                ->route('admin.cronotacografos.index')
                ->with('success', 'Cronotacógrafo atualizado com sucesso!')
                ->withNotification([
                    'title'   => 'Alterado com sucesso',
                    'type'    => 'success',
                    'message' => 'Cronotacógrafo alterado com sucesso'
                ]);
        } catch (\Exception $e) {
            // Rollback em caso de erro
            DB::rollBack();

            // Log detalhado do erro
            Log::error('Erro ao atualizar o cronotacógrafo: ' . $e->getMessage(), [
                'id' => $id,
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $mensagem = $e->getMessage();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar Cronotacógrafo: ' . $e->getMessage())
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
            $cronotacografo = CertificadoVeiculos::findOrFail($id);
            $cronotacografo->delete();
            DB::commit();
            return response()->json([
                'notification' => [
                    'title'   => 'Cronotacógrafo desativado',
                    'type'    => 'success',
                    'message' => 'Cronotacógrafo desativado com sucesso!'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao desativar Cronotacógrafo: ' . $e->getMessage());

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
     * Obtém dados do veículo para preenchimento automático
     */
    public function getDadosVeiculo($id)
    {
        try {
            $veiculo = Veiculo::with('filial')->findOrFail($id);

            // Log para diagnóstico
            Log::info('Dados do veículo recuperados, ID: ' . $id, [
                'chassi' => $veiculo->chassi,
                'renavam' => $veiculo->renavam,
            ]);

            return response()->json([
                'chassi' => $veiculo->chassi ?? '',
                'renavam' => $veiculo->renavam ?? ''
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do veículo: ' . $e->getMessage(), [
                'id' => $id
            ]);

            return response()->json([
                'error' => 'Veículo não encontrado',
                'message' => $e->getMessage()
            ], 404);
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
                    'renavam'
                ])
                ->map(fn($veiculo) => [
                    'value' => $veiculo->value,
                    'label' => $veiculo->label,
                    'chassi' => $veiculo->chassi,
                    'renavam' => $veiculo->renavam,
                ]);
        });

        $cronotacografo = CertificadoVeiculos::findOrFail($id);

        $dataOriginal = Carbon::parse($cronotacografo->data_vencimento);
        $cronotacografo->data_vencimento = $dataOriginal->addYear();

        $tiposCertificado = TipoCertificado::where('id_tipo_certificado', 2)
            ->get(['id_tipo_certificado as value', 'descricao_certificado as label']);

        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->get(['id_veiculo as value', 'placa as label']);

        $estados = Estado::orderBy('uf')
            ->get(['id_uf as value', 'uf as label']);

        return view('admin.cronotacografos.replicar', compact(
            'veiculosFrequentes',
            'cronotacografo',
            'tiposCertificado',
            'veiculos',
            'estados'
        ));
    }

    public function replicarUpdate(CertificadoVeiculos $id, Request $request)
    {
        try {
            // Iniciar transação
            DB::beginTransaction();

            $this->sanitizeMonetaryValues($request, [
                'valor_certificado'
            ]);

            // Validação dos dados com tratamento para casos específicos
            $validated = $request->validate([
                'id_tipo_certificado' => ['required', 'exists:tipocertificado,id_tipo_certificado'],
                'id_veiculo' => ['required', 'exists:veiculo,id_veiculo'],
                'chassi' => ['nullable', 'string', 'max:500'],
                'renavam' => ['nullable'], // Removido 'numeric' para permitir texto
                'id_uf' => ['nullable', 'exists:estado,id_uf'],
                'data_vencimento' => ['required', 'date'],
                'data_certificacao' => ['required', 'date'],
                'numero_certificado' => ['required', 'string', 'max:255'],
                'valor_certificado' => ['required', 'numeric'],
                'caminho_arquivo' => ['nullable', 'file', 'max:1024'],
            ]);

            // Log para diagnóstico
            Log::info('Dados validados para criação de cronotacógrafo:', $validated);

            // Recuperar a UF se informada
            if (!empty($validated['id_uf'])) {
                $estado = Estado::findOrFail($validated['id_uf']);
                $validated['uf'] = $estado->uf;
            }

            // Tratar o upload do arquivo
            if ($request->hasFile('caminho_arquivo') && $request->file('caminho_arquivo')->isValid()) {
                $validated['caminho_arquivo'] = $request->file('caminho_arquivo')->store('laudos', 'public');
            }


            $valoresPermitidos = ['A vencer', 'Cancelado', 'Vencido'];
            $situacao = $request->situacao;
            if (!in_array($situacao, $valoresPermitidos)) {
                $situacao = 'A vencer'; // padrão
            }

            // Criar o registro de certificado
            $cronotacografo = new CertificadoVeiculos();
            $cronotacografo->data_inclusao = now();
            $cronotacografo->id_tipo_certificado = $validated['id_tipo_certificado'];
            $cronotacografo->id_veiculo = $validated['id_veiculo'];
            $cronotacografo->chassi = $validated['chassi'] ?? null;
            $cronotacografo->renavam = $validated['renavam'] ?? null;
            $cronotacografo->id_uf = $validated['id_uf'] ?? null;
            $cronotacografo->uf = $validated['uf'] ?? null;
            $cronotacografo->data_vencimento = $validated['data_vencimento'];
            $cronotacografo->data_certificacao = $validated['data_certificacao'];
            $cronotacografo->numero_certificado = $validated['numero_certificado'];
            $cronotacografo->valor_certificado = $validated['valor_certificado'];
            $cronotacografo->caminho_arquivo = $validated['caminho_arquivo'] ?? null;
            $cronotacografo->situacao = $situacao;


            // Log para diagnóstico antes de salvar
            Log::info('Objeto cronotacógrafo antes de salvar:', [
                'id_veiculo' => $cronotacografo->id_veiculo,
                'chassi' => $cronotacografo->chassi,
                'renavam' => $cronotacografo->renavam,
                'data_vencimento' => $cronotacografo->data_vencimento,
                'data_certificacao' => $cronotacografo->data_certificacao,
            ]);

            $cronotacografo->save();

            // Log após salvar
            Log::info('Cronotacógrafo salvo com sucesso, ID: ' . $cronotacografo->id_certificado_veiculo);

            // Confirmar transação
            DB::commit();

            return redirect()
                ->route('admin.cronotacografos.index')
                ->with('success', 'Cronotacógrafo cadastrado com sucesso!')
                ->withNotification([
                    'title'   => 'Replicado com sucesso',
                    'type'    => 'success',
                    'message' => 'Cronotacógrafo cadastrado com sucesso'
                ]);
        } catch (\Exception $e) {
            // Rollback em caso de erro
            DB::rollBack();
            // Log detalhado do erro
            Log::error('Erro ao gravar o cronotacógrafo: ' . $e->getMessage(), [
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $mensagem = $e->getMessage();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao salvar o cronotacógrafo: ' . $e->getMessage())
                ->withNotification([
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => $mensagem
                ]);
        }
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
                $pdf->loadView('admin.cronotacografos.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('cronotacografo_' . date('Y-m-d_His') . '.pdf');
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
        $query = CertificadoVeiculos::query()
            ->with(['veiculo', 'veiculo.filial', 'tipocertificado'])
            ->where('id_tipo_certificado', 2)
            ->distinct();

        if ($request->filled('id_certificado_veiculo')) {
            $query->where('id_certificado_veiculo', $request->id_certificado_veiculo);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('situacao')) {
            $query->where('situacao', $request->situacao);
        }

        if ($request->filled('numero_certificado')) {
            $query->where('numero_certificado', $request->numero_certificado);
        }

        if ($request->filled('data_vencimento_inicio') && $request->filled('data_vencimento_final')) {
            $query->whereRaw("data_vencimento::date BETWEEN ? AND ?", [
                $request->data_vencimento_inicio,
                $request->data_vencimento_final,
            ]);
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
            'situacao'
        ];
    }

    /*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Exporta os crontacografos para um arquivo Excel, com op o de filtrar
     * os resultados.
     *
     * O par metro $request pode conter os seguintes campos para filtrar os
     * resultados:
     *
     * - id_certificado_veiculo: C digo do crontac grafo
     * - id_veiculo: C digo do ve culo
     * - status: Status do crontac grafo (ativo ou inativo)
     * - data_certificacao: Data de certifica o do crontac grafo
     * - data_vencimento: Data de vencimento do crontac grafo
     * - situacao: Situa o do crontac grafo
     *
     * Os resultados s o exportados para um arquivo Excel, com as colunas
     * especificadas no array $columns.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    /*******  31712e9b-57a5-452e-8251-5c2f43b90abb  *******/
    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_certificado_veiculo' => 'Código',
            'numero_certificado' => 'Numero do Certificado',
            'veiculo.placa' => 'Placa',
            'veiculo.filial.name' => 'Filial do Veiculo',
            'tipocertificado.descricao_certificado' => 'Tipo Certificado',
            'situacao' => 'Situação',
            'data_certificacao' => 'Data Certificação',
            'data_vencimento' => 'Data Vencimento',
            'Status' => 'Status'
        ];

        return $this->exportToExcel($request, $query, $columns, 'cronotacografos', $this->getValidExportFilters());
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
            'id_certificado_veiculo' => 'Código',
            'numero_certificado' => 'Numero do Certificado',
            'veiculo.placa' => 'Placa',
            'veiculo.filial.name' => 'Filial do Veiculo',
            'tipocertificado.descricao_certificado' => 'Tipo Certificado',
            'situacao' => 'Situação',
            'data_certificacao' => 'Data Certificação',
            'data_vencimento' => 'Data Vencimento',
            'Status' => 'Status'
        ];

        return $this->exportToCsv($request, $query, $columns, 'cronotacografos', $this->getValidExportFilters());
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
            'id' => 'id_certificado_veiculo',
            'Numero do Certificado' => 'numero_certificado',
            'Placa' => 'veiculo.placa',
            'Filial do Veiculo' => 'veiculo.filial.name',
            'Tipo Certificado' => 'tipocertificado.descricao_certificado',
            'Situação' => 'situacao',
            'Data Certificação' => 'data_certificacao',
            'Data Vencimento' => 'data_vencimento'
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'cronotacografos',
            'cronotacografo',
            'cronotacografos',
            $this->getValidExportFilters()
        );
    }
}
